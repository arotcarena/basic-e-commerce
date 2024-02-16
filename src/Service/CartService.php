<?php
namespace App\Service;

use Exception;
use App\Entity\Cart;
use App\Entity\User;
use App\Entity\Product;
use App\Entity\CartLine;
use App\Repository\CartRepository;
use App\Helper\FrDateTimeGenerator;
use App\Service\ProductCountService;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\CustomException\NotEnoughException;
use App\CustomException\OverStockException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

class CartService
{
    public const STOCK_UPDATED = 'stock_updated';
    public const CART_REMOVED = 'cart_removed';
    public const STOCK_SUFFICIENT = 'stock_sufficient';


    public function __construct(
        private RequestStack $requestStack,
        private ProductRepository $productRepository,
        private CartRepository $cartRepository,
        private EntityManagerInterface $em,
        private Security $security,
        private FrDateTimeGenerator $frDateTimeGenerator,
        private ProductCountService $productCountService
    )
    {

    }

    public function add(int $productId, int $quantity = 1)
    {
        $product = $this->productRepository->find($productId);
        //on vérifie que le product existe
        if(!$product)
        {
            throw new NotFoundResourceException('le product avec l\'id "'.$productId.'" n\'existe pas');
        }

        //si product déjà dans panier, on ajoute la quantity
        $sessionCart = $this->getSessionCart();
        if(array_key_exists($productId, $sessionCart))
        {
            $sessionCart[$productId] += $quantity;
        }
        //sinon on ajoute le product avec sa quantity
        else
        {
            $sessionCart[$productId] = $quantity;
            //et on ajoute un cart au product si nécessaire
            $this->productCountService->countCart($product);
        }

        //on vérifie le stock
        if($sessionCart[$productId] > $product->getStock())
        {
            $sessionCart[$productId] = $product->getStock();
            //si le stock est 0, on supprime carrément la ligne
            if($product->getStock() === 0)
            {
                unset($sessionCart[$productId]);
            }
            $this->setSessionCart($sessionCart);
            $this->persistCart();
            throw new OverStockException();
        }

        $this->setSessionCart($sessionCart);
        $this->persistCart();
    }

    public function remove(int $productId)
    {
        $sessionCart = $this->getSessionCart();
        if(!array_key_exists($productId, $sessionCart))
        {
            throw new NotFoundResourceException('le product avec l\'id'.$productId.' n\'existe pas en session dans "cart"');
        }
        unset($sessionCart[$productId]);
        $this->setSessionCart($sessionCart);
        $this->persistCart();
    }

    public function less(int $productId, int $quantity)
    {
        $sessionCart = $this->getSessionCart();
        if(!array_key_exists($productId, $sessionCart))
        {
            throw new NotFoundResourceException('le product avec l\'id'.$productId.' n\'existe pas en session dans "cart"');
        }
        $sessionCart[$productId] -= $quantity;
        
        //on vérifie que quantity demeure >= 1
        if($sessionCart[$productId] < 1)
        {
            $sessionCart[$productId] = 1;
            $this->setSessionCart($sessionCart);
            $this->persistCart();
            throw new NotEnoughException();
        }
        $this->setSessionCart($sessionCart);
        $this->persistCart();
    }

    /**
     * Update le stock au passage
     *
     * @return array [Cart, stockStatus]
     */
    public function getFullCart(): array
    {
        $productsById = $this->productRepository->findByIdGroup(array_keys($this->getSessionCart()));

        $stockStatus = $this->updateStock($productsById);

        $cart = $this->createFullCart($productsById);

        return [$cart, $stockStatus];
    }

    /**
     * N'update pas le stock
     *
     * @return array [count, totalPrice]
     */
    public function getLightCart(): array
    {
        $sessionCart = $this->getSessionCart();
        $products = $this->productRepository->findByIdGroup(array_keys($sessionCart));
        return $this->createLightCart($sessionCart, $products);
    }

    public function count(): int 
    {
        $sessionCart = $this->getSessionCart();
        $count = 0;
        foreach($sessionCart as $productId => $quantity)
        {
            $count += $quantity;
        }
        return $count;
    }

    public function empty()
    {
        $this->setSessionCart([]);
        if($user = $this->security->getUser())
        {
            /** @var User $user */
            $cart = $user->getCart();
            $this->em->remove($cart);
            $this->em->flush();
        }
    }

    public function onLoginUpdate(User $user)
    {
        $dbCart = $this->cartRepository->findOneByUserHydratedWithProducts($user);
        if($dbCart)
        {
            $this->mergeCarts($dbCart);
        }
        else
        {
            $this->persistCart($user);
        }
    }

  
   


    private function getSessionCart(): array
    {
        return $this->requestStack->getSession()->get('cart', []);
    }

    private function setSessionCart(array $cart)
    {
        $this->requestStack->getSession()->set('cart', $cart);
    }

    private function persistCart(User $user = null): void
    {
        $user = $user ?? $this->security->getUser();
        if($user === null)
        {
            return;
        }

        $dbCart = $this->cartRepository->findOneByUserHydratedWithProducts($user);
        if($dbCart)
        {
            $this->updateDbCart($dbCart);
        }
        else
        {
            $this->em->persist(
                $this->createFullCart()
            );
        }
        $this->em->flush();
    }

 
    private function updateStock($productsById = null): string 
    {
        $sessionCart = $this->getSessionCart();
        if(!$productsById) 
        {
            $productsById = $this->productRepository->findByIdGroup(array_keys($sessionCart));
        }

        $stockStatus = self::STOCK_SUFFICIENT;

        foreach($sessionCart as $productId => $quantity)
        {
            $product = $productsById[$productId] ?? null;
            // si le product a été supprimé ou si le stock est à zéro, on supprime la ligne
            if(!$product || $product->getStock() === 0)
            {
                $stockStatus = self::STOCK_UPDATED;
                unset($sessionCart[$productId]);
            }
            elseif($quantity > $product->getStock())
            {
                $stockStatus = self::STOCK_UPDATED;
                $sessionCart[$productId] = $product->getStock();
            }
        }
        //si le cart se retrouve vidé complétement
        if(count($sessionCart) === 0)
        {
            $stockStatus = self::CART_REMOVED;
        }

        $this->setSessionCart($sessionCart);
        $this->persistCart();

        return $stockStatus;
    }

    private function createLightCart(array $sessionCart, $products): array 
    {
        $totalPrice = 0;
        $count = 0;
        foreach($products as $product)
        {
            $quantity = $sessionCart[$product->getId()];
            $lineTotalPrice = $product->getPrice() * $quantity;

            $totalPrice += $lineTotalPrice;
            $count += $quantity;
        }

        return [
            'count' => $count,
            'totalPrice' => $totalPrice
        ];
    }

    /**
     * Update le stock
     *
     * @param array $sessionCart
     * @return Cart
     */
    private function createFullCart($productsById = null): Cart
    {
        $cart = new Cart;
        $sessionCart = $this->getSessionCart();

        if(!$productsById)
        {
            $productsById = $this->productRepository->findByIdGroup(array_keys($sessionCart));
        }
        
        $totalPrice = 0;
        $count = 0;
        foreach($sessionCart as $productId => $quantity)
        {
            if(!isset($productsById[$productId]))
            {
                throw new Exception('erreur au niveau de updateStock dans CartService : ne supprime pas correctement les lignes dont le produit a été supprimé de la db');
            }
            $product = $productsById[$productId];
            $lineTotalPrice = $product->getPrice() * $quantity;

            $cart->addCartLine(
                (new CartLine)
                ->setProduct($product)
                ->setQuantity($quantity)
                ->setTotalPrice($lineTotalPrice)
            );
            $totalPrice += $lineTotalPrice;
            $count += $quantity;
        }
        $cart
            ->setCount($count)
            ->setTotalPrice($totalPrice)
            ->setUpdatedAt($this->frDateTimeGenerator->generateImmutable());

        if($this->security->getUser())
        {
            $cart->setUser($this->security->getUser());
        }


        return $cart;
    }

    private function updateDbCart(Cart $dbCart)
    {
        $newCart = $this->createFullCart();
        $dbCart->setTotalPrice($newCart->getTotalPrice())
                ->setUpdatedAt($newCart->getUpdatedAt())
                ->setCount($newCart->getCount())
                ;
        foreach($dbCart->getCartLines() as $cartLine)
        {
            $dbCart->removeCartLine($cartLine);
        }
        foreach($newCart->getCartLines() as $cartLine)
        {
            $dbCart->addCartLine($cartLine);
        }
    }

    /**
     * Récupère le cart en database, et fait un merge avec le cart en session : on en profite pour vérifier le stock
     *
     * @param Cart $dbCart
     * @param array $sessionCart
     * @return void
     */
    private function mergeCarts(Cart $dbCart)
    {
        $sessionCart = $this->getSessionCart();
        $newSessionCart = [];
        //on insère d'abord les lines du dbCart
        foreach($dbCart->getCartLines() as $cartLine)
        {
            $product = $cartLine->getProduct();
            if(array_key_exists($product->getId(), $sessionCart))
            {
                $newSessionCart[$product->getId()] = $sessionCart[$product->getId()] + $cartLine->getQuantity();
            }
            else
            {
                $newSessionCart[$product->getId()] = $cartLine->getQuantity();
            }
            //on vérifie le stock
            if($newSessionCart[$product->getId()] > $product->getStock())
            {
                //CAS OU L ADMIN A MODIFIE LE STOCK DEPUIS NOTRE DERNIERE CONNEXION ET QU ON SE RETROUVE EN OVERSTOCK
                // Si on veut avertir l'utilisateur c'est d'ici qu'il faudra initier une action

                $newSessionCart[$product->getId()] = $product->getStock();
                // si le nouveau stock est 0, alors on supprime la ligne
                if($product->getStock() === 0)
                {
                    unset($newSessionCart[$product->getId()]);
                }
            }
        }
        //puis celles uniquement dans sessionCart
        //le stock est déjà vérifié
        foreach($sessionCart as $productId => $quantity)
        {
            if(!array_key_exists($productId, $newSessionCart))
            {
                $newSessionCart[$productId] = $quantity;
            }
        }
        
        $this->setSessionCart($newSessionCart);
        $this->persistCart();
    }
   
}