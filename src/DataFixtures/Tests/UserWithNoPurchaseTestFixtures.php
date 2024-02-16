<?php
namespace App\DataFixtures\Tests;

use App\Entity\User;
use DateTimeImmutable;
use App\Entity\Product;
use App\Entity\Purchase;
use App\Config\SiteConfig;
use App\Config\TextConfig;
use App\Convertor\PurchaseLineProductConvertor;
use App\Entity\PostalDetail;
use App\Entity\PurchaseLine;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserWithNoPurchaseTestFixtures extends Fixture 
{
    public function __construct(
        private UserPasswordHasherInterface $hasher,
        private PurchaseLineProductConvertor $purchaseLineProductConvertor
    )
    {

    }

    public function load(ObjectManager $manager)
    {
        $user = new User;
        $user
        ->setEmail('user_with_no_purchase@gmail.com')
        ->setPassword(
            $this->hasher->hashPassword($user, 'password')
        )
        ->setRoles([SiteConfig::ROLE_USER])
        ->setConfirmed(true)
        ->setCreatedAt(new DateTimeImmutable())
        ;
        $manager->persist($user);



        //user with specific product bought
        $user = new User;
        $user
        ->setEmail('user_with_specific_purchase@gmail.com')
        ->setPassword(
            $this->hasher->hashPassword($user, 'password')
        )
        ->setRoles([SiteConfig::ROLE_USER])
        ->setConfirmed(true)
        ->setCreatedAt(new DateTimeImmutable())
        ;
        $manager->persist($user);


        $product = (new Product)
                    ->setPublicRef('jfkldsjklfdjsfdlsj')
                    ->setDesignation('product for specific purchase user')
                    ->setSlug('product-for-specific-purchase-user')
                    ->setPrice(500)
                    ->setStock(2)
                    ->setCreatedAt(new DateTimeImmutable())
                    ;
        $manager->persist($product);
        //juste pour le suggestedProduct qui est obligatoire pour les tests
        $product2 = (new Product)
                    ->setPublicRef('testestestes')
                    ->setDesignation('test test test')
                    ->setSlug('test-test-tes-tes-tes')
                    ->setPrice(500)
                    ->setStock(2)
                    ->setCreatedAt(new DateTimeImmutable())
                    ->addSuggestedProduct($product)
                    ;
        $manager->persist($product2);
        $product->addSuggestedProduct($product2);
        //

        
        $postalDetail = (new PostalDetail)
                        ->setCivility(TextConfig::CIVILITY_M)
                        ->setFirstName('Ginette')
                        ->setLastName('Gertrude')
                        ->setLineOne('rue des noms pourris')
                        ->setCity('Trognon')
                        ->setPostcode('01520')
                        ->setCountry('Pomme')
                        ->setCreatedAt(new DateTimeImmutable())
                    ;

        $specificPurchase = (new Purchase)
                            ->setRef('dfsfds123456789')
                            ->addPurchaseLine(
                                (new PurchaseLine)
                                ->setProduct($this->purchaseLineProductConvertor->convert($product))
                                ->setQuantity(3)
                                ->setTotalPrice($product->getPrice() * 3)
                            )
                            ->setTotalPrice($product->getPrice() * 3)
                            ->setCreatedAt(new DateTimeImmutable())
                            ->setUser($user)
                            ->setDeliveryDetail($postalDetail)
                            ->setInvoiceDetail($postalDetail)
                            ;
        $manager->persist($specificPurchase);

        $manager->flush();

    }
}