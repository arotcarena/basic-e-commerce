<?php
namespace App\DataFixtures\Shop;

use Faker\Factory;
use Faker\Generator;
use App\Entity\Review;
use DateTimeImmutable;
use App\Config\SiteConfig;
use App\DataFixtures\User\UserFixtures;
use App\Repository\UserRepository;
use App\Repository\ProductRepository;
use Doctrine\Persistence\ObjectManager;
use App\DataFixtures\Shop\ProductFixtures;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class ReviewFixtures extends Fixture implements DependentFixtureInterface
{
    private Generator $faker;

    public function __construct(
        private UserRepository $userRepository,
        private ProductRepository $productRepository,
    )
    {
        $this->faker = Factory::create('fr_FR');
    }

    public function load(ObjectManager $manager)
    {
        $products = $this->productRepository->findAll();
        $users = $this->userRepository->findAll();

        for ($i=0; $i < 150; $i++) { 
            $review = (new Review)
                        ->setUser($this->faker->randomElement($users))
                        ->setProduct($this->faker->randomElement($products))
                        ->setUser($this->faker->randomElement($users))
                        ->setFullName($this->faker->name())
                        ->setRate(random_int(1, 5))
                        ->setComment($this->faker->paragraph())
                        ->setCreatedAt(new DateTimeImmutable(($this->faker->dateTimeBetween())->format('Y:m:d H:h:i')))
                    ;
            if(random_int(0, 9) < 7)
            {
                $review
                    ->setModerationStatus($this->faker->randomElement([SiteConfig::MODERATION_STATUS_ACCEPTED, SiteConfig::MODERATION_STATUS_REFUSED]));
            }
            $manager->persist($review);
        }
        $manager->flush();
    }

    public function getDependencies()
    {
        return [UserFixtures::class, ProductFixtures::class];
    }
}