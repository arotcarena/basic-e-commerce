<?php
namespace App\DataFixtures\Customer;

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
use App\DataFixtures\User\UserFixtures;
use App\Repository\UserRepository;
use App\Repository\ProductRepository;
use Doctrine\Persistence\ObjectManager;
use App\DataFixtures\Shop\ProductFixtures;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class PurchaseFixtures extends Fixture implements DependentFixtureInterface
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
        for ($i=0; $i < 100; $i++) { 
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
        }

        //ajout d'une purchaseLine pour chaque Purchase
        foreach($purchases as $purchase)
        {
            $product = $this->faker->randomElement($products);
            $quantity = random_int(1, 3);

            $purchaseLine = (new PurchaseLine)
                                ->setProduct($this->purchaseLineProductConvertor->convert($product))
                                ->setQuantity($quantity)
                                ->setTotalPrice($product->getPrice() * $quantity)
                            ;
            $purchase->addPurchaseLine($purchaseLine)
                    ->setTotalPrice($purchase->getTotalPrice() + $purchaseLine->getTotalPrice())
                    ;
            $manager->persist($purchase);
        }
        // ajout aléatoire de purchaseLines
        for ($i=0; $i < 10; $i++) { 
            $product = $this->faker->randomElement($products);
            $quantity = random_int(1, 3);

            $purchaseLine = (new PurchaseLine)
                                ->setProduct($this->purchaseLineProductConvertor->convert($product))
                                ->setQuantity($quantity)
                                ->setTotalPrice($product->getPrice() * $quantity)
                            ;
            /** @var Purchase */
            $purchase = $this->faker->randomElement($purchases);
            $purchase->addPurchaseLine($purchaseLine)
                    ->setTotalPrice($purchase->getTotalPrice() + $purchaseLine->getTotalPrice())
                    ;
        }



        $manager->flush();
    }

    public function getDependencies()
    {
        return [UserFixtures::class, ProductFixtures::class];
    }
}