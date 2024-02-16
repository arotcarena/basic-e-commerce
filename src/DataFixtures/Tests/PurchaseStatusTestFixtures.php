<?php
namespace App\DataFixtures\Tests;

use Faker\Factory;
use App\Entity\User;
use Faker\Generator;
use DateTimeImmutable;
use App\Entity\Purchase;
use App\Config\SiteConfig;
use App\Config\TextConfig;
use App\Entity\PostalDetail;
use App\Repository\UserRepository;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use App\DataFixtures\Tests\UserTestFixtures;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class PurchaseStatusTestFixtures extends Fixture implements DependentFixtureInterface
{
    private Generator $faker;

    public function __construct(
        private UserRepository $userRepository,
    )
    {
        $this->faker = Factory::create();
    }


    public function load(ObjectManager $manager)
    {
        $users = $this->userRepository->findAll();
        
        //15 purchases without status
        $purchases = [];
        for ($i=0; $i < 15; $i++) { 

            $user = $this->faker->randomElement($users);
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
                            ->setTotalPrice(5)
                            ->setCreatedAt($createdAt)
                        ;
            
            $purchases[] = $purchase;
            $manager->persist($purchase);
        }
        
        //10 purchases in process
        //3 pending
        foreach([0, 1, 2] as $i)
        {
            $purchases[$i]->setStatus(
                $this->faker->randomElement([
                    SiteConfig::STATUS_PENDING  
                ]) 
            );
        }
        //2 paid
        foreach([3, 4] as $i)
        {
            $purchases[$i]->setStatus(
                $this->faker->randomElement([
                    SiteConfig::STATUS_PAID  
                ]) 
            );
        }
        //5 sent
        foreach([5, 6, 7, 8, 9] as $i)
        {
            $purchases[$i]->setStatus(
                $this->faker->randomElement([
                    SiteConfig::STATUS_SENT  
                ]) 
            );
        }
        //5 purchases processed
        //4 delivered
        foreach([10, 11, 12, 13] as $i)
        {
            $purchases[$i]->setStatus(
                $this->faker->randomElement([
                    SiteConfig::STATUS_DELIVERED  
                ]) 
            );
        }
        //1 canceled
        $purchases[14]->setStatus(
            $this->faker->randomElement([
                SiteConfig::STATUS_CANCELED  
            ]) 
        );

        $manager->flush();
    }

    public function getDependencies()
    {
        return [UserTestFixtures::class];
    }
}