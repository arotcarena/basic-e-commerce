<?php
namespace App\DataFixtures\Tests;

use Faker\Factory;
use App\Entity\User;
use Faker\Generator;
use DateTimeImmutable;
use App\Entity\Purchase;
use App\Config\SiteConfig;
use App\Config\TextConfig;
use App\Convertor\PurchaseLineProductConvertor;
use App\Entity\PostalDetail;
use App\Entity\PurchaseLine;
use App\Repository\UserRepository;
use App\Repository\ProductRepository;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use App\DataFixtures\Tests\UserTestFixtures;
use App\DataFixtures\Tests\ProductTestFixtures;
use App\Entity\Cart;
use App\Entity\CartLine;
use App\Entity\Product;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class PurchaseTestFixtures extends Fixture implements DependentFixtureInterface
{
    private Generator $faker;

    public function __construct(
        private UserRepository $userRepository,
        private ProductRepository $productRepository,
        private PurchaseLineProductConvertor $purchaseLineProductConvertor
    )
    {
        $this->faker = Factory::create('fr_FR');
    }

    public function load(ObjectManager $manager)
    {
        $users = $this->userRepository->findAll();
        $products = $this->productRepository->findAll();


        /** @var Purchase[] */
        $purchases = [];
        for ($i=0; $i < 20; $i++) { 
            /** @var User */
            $user = $this->faker->randomElement($users);
            $user->setCivility($this->faker->randomElement([TextConfig::CIVILITY_F, TextConfig::CIVILITY_M]))
                ->setFirstName($this->faker->firstName())
                ->setLastName($this->faker->lastName())
                ;
            $createdAt = new DateTimeImmutable(($this->faker->dateTime('now', 'Europe/Paris'))->format('Y:m:d H:h:i'));
            $postalDetail = (new PostalDetail)
                                ->setCivility($this->faker->randomElement([TextConfig::CIVILITY_M, TextConfig::CIVILITY_F]))
                                ->setFirstName($this->faker->firstName())
                                ->setLastName($this->faker->lastName())
                                ->setLineOne($this->faker->streetName())
                                ->setCity($this->faker->city())
                                ->setPostcode($this->faker->postcode())
                                ->setCountry($this->faker->country())
                                ->setCreatedAt($createdAt)
                            ;

            $purchase = (new Purchase)
                            ->setRef(substr(str_shuffle(str_repeat('azerttyuiopqsdfghjklmwxcvbn0123456789', 5)), 0, 8))
                            ->setUser($user)
                            ->setDeliveryDetail($postalDetail)
                            ->setInvoiceDetail($postalDetail)
                            ->setStatus(
                                $this->faker->randomElement([
                                    SiteConfig::STATUS_PENDING, SiteConfig::STATUS_PAID, SiteConfig::STATUS_SENT, SiteConfig::STATUS_DELIVERED, SiteConfig::STATUS_CANCELED
                                ])
                            )
                            ->setCreatedAt($createdAt)
                        ;
            if($purchase->getStatus() !== SiteConfig::STATUS_PENDING)
            {
                $purchase->setPaidAt(new DateTimeImmutable());
            }
            $purchases[] = $purchase;
            $manager->persist($purchase);
        }

        //ajout d'une purchaseLine pour chaque Purchase
        foreach($purchases as $purchase)
        {
            $product = $this->faker->randomElement($products);
            $quantity = random_int(1, 3);

            $purchaseLine = (new PurchaseLine)
                                ->setProduct(
                                    $this->purchaseLineProductConvertor->convert($product)
                                )
                                ->setQuantity($quantity)
                                ->setTotalPrice($product->getPrice() * $quantity)
                            ;
            $purchase->addPurchaseLine($purchaseLine)
                    ->setTotalPrice($purchase->getTotalPrice() + $purchaseLine->getTotalPrice())
                    ;
        }
        // ajout aléatoire de purchaseLines
        for ($i=0; $i < 10; $i++) { 
            $product = $this->faker->randomElement($products);
            $quantity = random_int(1, 3);

            $purchaseLine = (new PurchaseLine)
                                ->setProduct(
                                    $this->purchaseLineProductConvertor->convert($product)
                                )
                                ->setQuantity($quantity)
                                ->setTotalPrice($product->getPrice() * $quantity)
                            ;
            /** @var Purchase */
            $purchase = $this->faker->randomElement($purchases);
            $purchase->addPurchaseLine($purchaseLine)
                    ->setTotalPrice($purchase->getTotalPrice() + $purchaseLine->getTotalPrice())
                    ;
        }


        //purchase récente avec status paid pour test duplicate
        /** @var User */
        $user = $this->faker->randomElement($users);
        $user->setCivility($this->faker->randomElement([TextConfig::CIVILITY_F, TextConfig::CIVILITY_M]))
            ->setFirstName($this->faker->firstName())
            ->setLastName($this->faker->lastName())
            ;
        $postalDetail = (new PostalDetail)
                            ->setCivility($this->faker->randomElement([TextConfig::CIVILITY_M, TextConfig::CIVILITY_F]))
                            ->setFirstName($this->faker->firstName())
                            ->setLastName($this->faker->lastName())
                            ->setLineOne($this->faker->streetName())
                            ->setCity($this->faker->city())
                            ->setPostcode($this->faker->postcode())
                            ->setCountry($this->faker->country())
                            ->setCreatedAt(new DateTimeImmutable())
                        ;
        $product = $this->faker->randomElement($products);
        $product2 = $this->faker->randomElement($products);

        $purchase = (new Purchase)
                        ->setRef('purchase_test_duplicate')
                        ->setUser($user)
                        ->setDeliveryDetail($postalDetail)
                        ->setInvoiceDetail($postalDetail)
                        ->setStatus(SiteConfig::STATUS_PAID)
                        ->setCreatedAt(new DateTimeImmutable())
                        ->setTotalPrice((2 * $product->getPrice()) + $product2->getPrice())
                        ->addPurchaseLine(
                            (new PurchaseLine)
                            ->setProduct($this->purchaseLineProductConvertor->convert($product))
                            ->setQuantity(2)
                            ->setTotalPrice(2 * $product->getPrice())
                        )
                        ->addPurchaseLine(
                            (new PurchaseLine)
                            ->setProduct($this->purchaseLineProductConvertor->convert($product2))
                            ->setQuantity(1)
                            ->setTotalPrice($product->getPrice())
                        )
                    ;
        $manager->persist($purchase);

         //purchase récente avec status pending pour test duplicate
        /** @var User */
        $user = $this->faker->randomElement($users);
        $user->setCivility($this->faker->randomElement([TextConfig::CIVILITY_F, TextConfig::CIVILITY_M]))
            ->setFirstName($this->faker->firstName())
            ->setLastName($this->faker->lastName())
            ;
        $postalDetail = (new PostalDetail)
                            ->setCivility($this->faker->randomElement([TextConfig::CIVILITY_M, TextConfig::CIVILITY_F]))
                            ->setFirstName($this->faker->firstName())
                            ->setLastName($this->faker->lastName())
                            ->setLineOne($this->faker->streetName())
                            ->setCity($this->faker->city())
                            ->setPostcode($this->faker->postcode())
                            ->setCountry($this->faker->country())
                            ->setCreatedAt(new DateTimeImmutable())
                        ;

        $product = $this->faker->randomElement($products);

        $purchase = (new Purchase)
                        ->setRef('purchase_test_duplicate_pending')
                        ->setUser($user)
                        ->setDeliveryDetail($postalDetail)
                        ->setInvoiceDetail($postalDetail)
                        ->setStatus(SiteConfig::STATUS_PENDING)
                        ->setCreatedAt(new DateTimeImmutable())
                        ->setTotalPrice(4 * $product->getPrice())
                        ->addPurchaseLine(
                            (new PurchaseLine)
                            ->setProduct($this->purchaseLineProductConvertor->convert($product))
                            ->setQuantity(2)
                            ->setTotalPrice(4 * $product->getPrice())
                        )
                    ;
        $manager->persist($purchase);

         //purchases avec quantity over stock pour test ApiPurchaseController.lastVerificationBeforePayment
         // purchase avec un product over stock dont le stock est à 0
        /** @var User */
        $user = $this->faker->randomElement($users);
        $user->setCivility($this->faker->randomElement([TextConfig::CIVILITY_F, TextConfig::CIVILITY_M]))
            ->setFirstName($this->faker->firstName())
            ->setLastName($this->faker->lastName())
            ;
        $postalDetail = (new PostalDetail)
                            ->setCivility($this->faker->randomElement([TextConfig::CIVILITY_M, TextConfig::CIVILITY_F]))
                            ->setFirstName($this->faker->firstName())
                            ->setLastName($this->faker->lastName())
                            ->setLineOne($this->faker->streetName())
                            ->setCity($this->faker->city())
                            ->setPostcode($this->faker->postcode())
                            ->setCountry($this->faker->country())
                            ->setCreatedAt(new DateTimeImmutable())
                        ;
                        
        $productWithoutStock = $this->productRepository->findOneBy(['stock' => 0]);

        $purchase = (new Purchase)
                        ->setRef('purchase_test_all_over_stock')
                        ->setUser($user)
                        ->setDeliveryDetail($postalDetail)
                        ->setInvoiceDetail($postalDetail)
                        ->setStatus(SiteConfig::STATUS_PENDING)
                        ->setCreatedAt(new DateTimeImmutable())
                        ->setTotalPrice(2000)
                        ->addPurchaseLine(
                            (new PurchaseLine)
                            ->setProduct(
                                $this->purchaseLineProductConvertor->convert($productWithoutStock)
                            )
                            ->setQuantity(10) // quantity over stock
                            ->setTotalPrice(2000)
                        )
                    ;
        $manager->persist($purchase);

        //purchase avec 2 products dont un seul over stock
        /** @var User */
        $user = $this->faker->randomElement($users);
        $user->setCivility($this->faker->randomElement([TextConfig::CIVILITY_F, TextConfig::CIVILITY_M]))
            ->setFirstName($this->faker->firstName())
            ->setLastName($this->faker->lastName())
            ;
        $postalDetail = (new PostalDetail)
                            ->setCivility($this->faker->randomElement([TextConfig::CIVILITY_M, TextConfig::CIVILITY_F]))
                            ->setFirstName($this->faker->firstName())
                            ->setLastName($this->faker->lastName())
                            ->setLineOne($this->faker->streetName())
                            ->setCity($this->faker->city())
                            ->setPostcode($this->faker->postcode())
                            ->setCountry($this->faker->country())
                            ->setCreatedAt(new DateTimeImmutable())
                        ;

        $productOverStock = $this->faker->randomElement($products);
        $productOk = $this->productRepository->findOneByStock(1);

        $purchase = (new Purchase)
                        ->setRef('purchase_test_one_product_over_stock')
                        ->setUser($user)
                        ->setDeliveryDetail($postalDetail)
                        ->setInvoiceDetail($postalDetail)
                        ->setStatus(SiteConfig::STATUS_PENDING)
                        ->setCreatedAt(new DateTimeImmutable())
                        ->setTotalPrice(($productOverStock->getPrice() * 1000) + ($productOk->getPrice()))
                        ->addPurchaseLine(
                            (new PurchaseLine)
                            ->setProduct(
                                $this->purchaseLineProductConvertor->convert($productOverStock)
                            )
                            ->setQuantity(1000) // quantity over stock
                            ->setTotalPrice($productOverStock->getPrice() * 1000)
                        )
                        ->addPurchaseLine(
                            (new PurchaseLine)
                            ->setProduct(
                                $this->purchaseLineProductConvertor->convert($productOk)
                            )
                            ->setQuantity(1) // quantity ok
                            ->setTotalPrice($productOk->getPrice())
                        )
                    ;
        $manager->persist($purchase);



         //purchase pour cart update test : avec un cart persisté et correspondant
        $user = (new User)
        ->setCivility(TextConfig::CIVILITY_M)
        ->setFirstName('jean')
        ->setLastName('claude')
        ->setEmail('jeanclaude@gmail.com')
        ->setPassword('jfkdsqklfdshfjds')
        ->setRoles(['ROLE_USER'])
        ->setCreatedAt(new DateTimeImmutable())
        ;

        $manager->persist($user);

        $postalDetail = (new PostalDetail)
                            ->setCivility($this->faker->randomElement([TextConfig::CIVILITY_M, TextConfig::CIVILITY_F]))
                            ->setFirstName($this->faker->firstName())
                            ->setLastName($this->faker->lastName())
                            ->setLineOne($this->faker->streetName())
                            ->setCity($this->faker->city())
                            ->setPostcode($this->faker->postcode())
                            ->setCountry($this->faker->country())
                            ->setCreatedAt(new DateTimeImmutable())
                        ;

        $productWithStockOne = $this->productRepository->findOneByStock(1);

        $purchase = (new Purchase)
                        ->setRef('purchase_for_cart_update_test')
                        ->setUser($user)
                        ->setDeliveryDetail($postalDetail)
                        ->setInvoiceDetail($postalDetail)
                        ->setStatus(SiteConfig::STATUS_PENDING)
                        ->setCreatedAt(new DateTimeImmutable())
                        ->setTotalPrice(10 * $productWithStockOne->getPrice())
                        ->addPurchaseLine(
                            (new PurchaseLine)
                            ->setProduct(
                                $this->purchaseLineProductConvertor->convert($productWithStockOne)
                            )
                            ->setQuantity(10) // quantity over stock
                            ->setTotalPrice(10 * $productWithStockOne->getPrice())
                        )
                    ;
        $manager->persist($purchase);


        $cart = (new Cart)
                ->setUser($user)
                ->setTotalPrice(10 * $productWithStockOne->getPrice())
                ->setCount(10)
                ->addCartLine(
                    (new CartLine)
                    ->setProduct($productWithStockOne)
                    ->setQuantity(10) // quantity over stock
                    ->setTotalPrice(10 * $productWithStockOne->getPrice())
                )
                ->setUpdatedAt(new DateTimeImmutable())
                ;

        $manager->persist($cart);

        //purchase valide
        /** @var User */
        $user = $this->faker->randomElement($users);
        $user->setCivility($this->faker->randomElement([TextConfig::CIVILITY_F, TextConfig::CIVILITY_M]))
            ->setFirstName($this->faker->firstName())
            ->setLastName($this->faker->lastName())
            ;
        $postalDetail = (new PostalDetail)
                            ->setCivility($this->faker->randomElement([TextConfig::CIVILITY_M, TextConfig::CIVILITY_F]))
                            ->setFirstName($this->faker->firstName())
                            ->setLastName($this->faker->lastName())
                            ->setLineOne($this->faker->streetName())
                            ->setCity($this->faker->city())
                            ->setPostcode($this->faker->postcode())
                            ->setCountry($this->faker->country())
                            ->setCreatedAt(new DateTimeImmutable())
                        ;

        $purchase = (new Purchase)
                        ->setRef('valid_purchase')
                        ->setUser($user)
                        ->setDeliveryDetail($postalDetail)
                        ->setInvoiceDetail($postalDetail)
                        ->setStatus(SiteConfig::STATUS_PENDING)
                        ->setCreatedAt(new DateTimeImmutable())
                        ->setTotalPrice(3000)
                        ->addPurchaseLine(
                            (new PurchaseLine)
                            ->setProduct(
                                $this->purchaseLineProductConvertor->convert($this->productRepository->findOneByStock(1))
                            )
                            ->setQuantity(1)
                            ->setTotalPrice(2000)
                        )
                    ;
        $manager->persist($purchase);

        //purchase valide avec deux products
        /** @var User */
        $user = $this->faker->randomElement($users);
        $user->setCivility($this->faker->randomElement([TextConfig::CIVILITY_F, TextConfig::CIVILITY_M]))
            ->setFirstName($this->faker->firstName())
            ->setLastName($this->faker->lastName())
            ;
        $postalDetail = (new PostalDetail)
                            ->setCivility($this->faker->randomElement([TextConfig::CIVILITY_M, TextConfig::CIVILITY_F]))
                            ->setFirstName($this->faker->firstName())
                            ->setLastName($this->faker->lastName())
                            ->setLineOne($this->faker->streetName())
                            ->setCity($this->faker->city())
                            ->setPostcode($this->faker->postcode())
                            ->setCountry($this->faker->country())
                            ->setCreatedAt(new DateTimeImmutable())
                        ;

        $purchase = (new Purchase)
                        ->setRef('valid_purchase_with_two_products')
                        ->setUser($user)
                        ->setDeliveryDetail($postalDetail)
                        ->setInvoiceDetail($postalDetail)
                        ->setStatus(SiteConfig::STATUS_PENDING)
                        ->setCreatedAt(new DateTimeImmutable())
                        ->setTotalPrice(3000)
                        ->addPurchaseLine(
                            (new PurchaseLine)
                            ->setProduct(
                                $this->purchaseLineProductConvertor->convert($this->productRepository->findOneByStock(100))
                            )
                            ->setQuantity(3)
                            ->setTotalPrice(2000)
                        )
                        ->addPurchaseLine(
                            (new PurchaseLine)
                            ->setProduct(
                                $this->purchaseLineProductConvertor->convert($this->productRepository->findOneByStock(1))
                            )
                            ->setQuantity(1)
                            ->setTotalPrice(2000)
                        )
                    ;
        $manager->persist($purchase);

        //purchase avec 1 product à stock zéro
         //purchase valide
        /** @var User */
        $user = $this->faker->randomElement($users);
        $user->setCivility($this->faker->randomElement([TextConfig::CIVILITY_F, TextConfig::CIVILITY_M]))
            ->setFirstName($this->faker->firstName())
            ->setLastName($this->faker->lastName())
            ;
        $postalDetail = (new PostalDetail)
                            ->setCivility($this->faker->randomElement([TextConfig::CIVILITY_M, TextConfig::CIVILITY_F]))
                            ->setFirstName($this->faker->firstName())
                            ->setLastName($this->faker->lastName())
                            ->setLineOne($this->faker->streetName())
                            ->setCity($this->faker->city())
                            ->setPostcode($this->faker->postcode())
                            ->setCountry($this->faker->country())
                            ->setCreatedAt(new DateTimeImmutable())
                        ;

        $purchase = (new Purchase)
                        ->setRef('purchase_test_zero_stock')
                        ->setUser($user)
                        ->setDeliveryDetail($postalDetail)
                        ->setInvoiceDetail($postalDetail)
                        ->setStatus(SiteConfig::STATUS_PENDING)
                        ->setCreatedAt(new DateTimeImmutable())
                        ->setTotalPrice(500)
                        ->addPurchaseLine(
                            (new PurchaseLine)
                            ->setProduct(
                                $this->purchaseLineProductConvertor->convert($this->productRepository->findOneByStock(0))
                            )
                            ->setQuantity(1)
                            ->setTotalPrice(500)
                        )
                    ;
        $manager->persist($purchase);

        //purchase avec 1 product à stock zéro et 1 avec stock ok
         //purchase valide
        /** @var User */
        $user = $this->faker->randomElement($users);
        $user->setCivility($this->faker->randomElement([TextConfig::CIVILITY_F, TextConfig::CIVILITY_M]))
            ->setFirstName($this->faker->firstName())
            ->setLastName($this->faker->lastName())
            ;
        $postalDetail = (new PostalDetail)
                            ->setCivility($this->faker->randomElement([TextConfig::CIVILITY_M, TextConfig::CIVILITY_F]))
                            ->setFirstName($this->faker->firstName())
                            ->setLastName($this->faker->lastName())
                            ->setLineOne($this->faker->streetName())
                            ->setCity($this->faker->city())
                            ->setPostcode($this->faker->postcode())
                            ->setCountry($this->faker->country())
                            ->setCreatedAt(new DateTimeImmutable())
                        ;

        $purchase = (new Purchase)
                        ->setRef('purchase_test_one_zero_stock_and_one_ok')
                        ->setUser($user)
                        ->setDeliveryDetail($postalDetail)
                        ->setInvoiceDetail($postalDetail)
                        ->setStatus(SiteConfig::STATUS_PENDING)
                        ->setCreatedAt(new DateTimeImmutable())
                        ->setTotalPrice(1100)
                        ->addPurchaseLine(
                            (new PurchaseLine)
                            ->setProduct(
                                $this->purchaseLineProductConvertor->convert($this->productRepository->findOneByStock(0))
                            )
                            ->setQuantity(2)
                            ->setTotalPrice(500)
                        )
                        ->addPurchaseLine(
                            (new PurchaseLine)
                            ->setProduct(
                                $this->purchaseLineProductConvertor->convert($this->productRepository->findOneByStock(1))
                            )
                            ->setQuantity(1)
                            ->setTotalPrice(100)
                        )
                    ;
        $manager->persist($purchase);

        //purchase for db queries count test
         /** @var User */
         $user = $this->faker->randomElement($users);
         $user->setCivility($this->faker->randomElement([TextConfig::CIVILITY_F, TextConfig::CIVILITY_M]))
             ->setFirstName($this->faker->firstName())
             ->setLastName($this->faker->lastName())
             ;
         $postalDetail = (new PostalDetail)
                             ->setCivility($this->faker->randomElement([TextConfig::CIVILITY_M, TextConfig::CIVILITY_F]))
                             ->setFirstName($this->faker->firstName())
                             ->setLastName($this->faker->lastName())
                             ->setLineOne($this->faker->streetName())
                             ->setCity($this->faker->city())
                             ->setPostcode($this->faker->postcode())
                             ->setCountry($this->faker->country())
                             ->setCreatedAt(new DateTimeImmutable())
                         ;

         $purchase = (new Purchase)
                         ->setRef('purchase_test_db_queries')
                         ->setUser($user)
                         ->setDeliveryDetail($postalDetail)
                         ->setInvoiceDetail($postalDetail)
                         ->setStatus(SiteConfig::STATUS_PENDING)
                         ->setCreatedAt(new DateTimeImmutable())
                         ->setTotalPrice(3000)
                         ->addPurchaseLine(
                             (new PurchaseLine)
                             ->setProduct(
                                $this->purchaseLineProductConvertor->convert($this->faker->randomElement($products))
                             )
                             ->setQuantity(1000) // quantity over stock
                             ->setTotalPrice(2000)
                         )
                         ->addPurchaseLine(
                             (new PurchaseLine)
                             ->setProduct(
                                $this->purchaseLineProductConvertor->convert($this->productRepository->findOneByStock(1))
                             )
                             ->setQuantity(1) // quantity ok
                             ->setTotalPrice(100)
                         )
                         ->addPurchaseLine(
                             (new PurchaseLine)
                             ->setProduct(
                                $this->purchaseLineProductConvertor->convert($this->faker->randomElement($products))
                             )
                             ->setQuantity(1) // quantity ok
                             ->setTotalPrice(100)
                         )
                         ->addPurchaseLine(
                             (new PurchaseLine)
                             ->setProduct(
                                $this->purchaseLineProductConvertor->convert($this->faker->randomElement($products))
                             )
                             ->setQuantity(1) // quantity ok
                             ->setTotalPrice(100)
                         )
                         ->addPurchaseLine(
                            (new PurchaseLine)
                            ->setProduct(
                               $this->purchaseLineProductConvertor->convert($this->faker->randomElement($products))
                            )
                            ->setQuantity(1) // quantity ok
                            ->setTotalPrice(100)
                        )
                        ->addPurchaseLine(
                            (new PurchaseLine)
                            ->setProduct(
                               $this->purchaseLineProductConvertor->convert($this->faker->randomElement($products))
                            )
                            ->setQuantity(1) // quantity ok
                            ->setTotalPrice(100)
                        )
                     ;
         $manager->persist($purchase);

        //purchase vide
        /** @var User */
        $user = $this->faker->randomElement($users);
        $user->setCivility($this->faker->randomElement([TextConfig::CIVILITY_F, TextConfig::CIVILITY_M]))
            ->setFirstName($this->faker->firstName())
            ->setLastName($this->faker->lastName())
            ;
        $postalDetail = (new PostalDetail)
                            ->setCivility($this->faker->randomElement([TextConfig::CIVILITY_M, TextConfig::CIVILITY_F]))
                            ->setFirstName($this->faker->firstName())
                            ->setLastName($this->faker->lastName())
                            ->setLineOne($this->faker->streetName())
                            ->setCity($this->faker->city())
                            ->setPostcode($this->faker->postcode())
                            ->setCountry($this->faker->country())
                            ->setCreatedAt(new DateTimeImmutable())
                        ;

        $purchase = (new Purchase)
                        ->setRef('purchase_test_empty')
                        ->setUser($user)
                        ->setDeliveryDetail($postalDetail)
                        ->setInvoiceDetail($postalDetail)
                        ->setStatus(SiteConfig::STATUS_PENDING)
                        ->setCreatedAt(new DateTimeImmutable())
                        ->setTotalPrice(0)
                    ;
        $manager->persist($purchase);


        $manager->flush();
    }

    public function getDependencies()
    {
        return [UserTestFixtures::class, ProductTestFixtures::class, ProductWithOrWithoutStockTestFixtures::class];
    }

}