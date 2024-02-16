<?php
namespace App\DataFixtures\Tests;

use App\Entity\Cart;
use App\Entity\CartLine;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use DateTimeImmutable;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class CartTestFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(
        private UserRepository $userRepository,
        private ProductRepository $productRepository
    )
    {

    }

    public function load(ObjectManager $manager)
    {
        $user = $this->userRepository->findOneBy(['email' => 'user@gmail.com']);
        $confirmedUser = $this->userRepository->findOneBy(['email' => 'confirmed_user@gmail.com']);


        $product1 = $this->productRepository->findOneBy(['slug' => 'objet']);
        $product2 = $this->productRepository->findOneBy(['slug' => 'mon-objet']);
        $product3 = $this->productRepository->findOneBy(['slug' => 'public-ref-test']);
        


        $cart = new Cart;
        $cart   
            ->setUser($user)
            ->addCartLine(
                (new CartLine)
                ->setProduct($product1)
                ->setQuantity(3)
                ->setTotalPrice($product1->getPrice() * 3)
            )
            ->addCartLine(
                (new CartLine)
                ->setProduct($product3)
                ->setQuantity(2)
                ->setTotalPrice($product3->getPrice() * 2)
            )
            ->addCartLine(
                (new CartLine)
                ->setProduct($product2)
                ->setQuantity(1)
                ->setTotalPrice($product2->getPrice())
            )
            ->setTotalPrice(($product1->getPrice() * 3) + ($product3->getPrice() * 2) + $product2->getPrice())
            ->setCount(6)
            ->setUpdatedAt(new DateTimeImmutable())
            ;
            
        $manager->persist($cart);

        $cart = new Cart;
        $cart   
            ->setUser($confirmedUser)
            ->addCartLine(
                (new CartLine)
                ->setProduct($product1)
                ->setQuantity(4)
                ->setTotalPrice($product1->getPrice() * 4)
            )
            ->addCartLine(
                (new CartLine)
                ->setProduct($product2)
                ->setQuantity(1)
                ->setTotalPrice($product2->getPrice())
            )
            ->setTotalPrice(($product1->getPrice() * 4) + $product2->getPrice())
            ->setCount(5)
            ->setUpdatedAt(new DateTimeImmutable())
            ;

        $manager->persist($cart);


        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            ProductTestFixtures::class,
            UserTestFixtures::class
        ];
    }
}