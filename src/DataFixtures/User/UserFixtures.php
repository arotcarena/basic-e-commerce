<?php

namespace App\DataFixtures\User;

use Faker\Factory;
use App\Entity\User;
use DateTimeImmutable;
use App\Config\SiteConfig;
use App\Config\TextConfig;
use App\Helper\FrDateTimeGenerator;
use App\Helper\DevReplaceSpecialCars;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $hasher,
        private FrDateTimeGenerator $frDateTimeGenerator,
        private DevReplaceSpecialCars $devReplaceSpecialCars
    )
    {

    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');


        
        // 5 not confirmed users
        for ($i=0; $i < 5; $i++) { 
            $civility = $faker->randomElement([TextConfig::CIVILITY_M, TextConfig::CIVILITY_F]);
            $firstName = $civility === TextConfig::CIVILITY_M ? $faker->firstNameMale(): $faker->firstNameFemale();
            $lastName = $faker->lastName();
            $email = $this->createEmail($firstName, $lastName, $faker);

            $user = new User;
            $user 
                ->setEmail($email)
                ->setPassword(
                    $this->hasher->hashPassword($user, 'password')
                )
                ->setRoles([SiteConfig::ROLE_USER])
                ->setCivility($civility)
                ->setFirstName($firstName)
                ->setLastName($lastName)
                ->setConfirmed(false)
                ->setCreatedAt($this->frDateTimeGenerator->generateImmutable($faker->date('Y:m:d') . ' ' .$faker->time('H:i:s')))
                ;
            $manager->persist($user);
        }

        // 5 confirmed users
        for ($i=0; $i < 5; $i++) { 
            $civility = $faker->randomElement([TextConfig::CIVILITY_M, TextConfig::CIVILITY_F]);
            $firstName = $civility === TextConfig::CIVILITY_M ? $faker->firstNameMale(): $faker->firstNameFemale();
            $lastName = $faker->lastName();
            $email = $firstName . $faker->randomElement(['', '.', '-']) . $lastName . $faker->randomElement(['', $faker->randomDigit()]) .'@'. $faker->randomElement(['sfr.fr', 'gmail.com', 'orange.fr', 'wanadoo.fr']);

            $user = new User;
            $user 
                ->setEmail($email)
                ->setPassword(
                    $this->hasher->hashPassword($user, 'password')
                )
                ->setRoles([SiteConfig::ROLE_USER])
                ->setCivility($civility)
                ->setFirstName($firstName)
                ->setLastName($lastName)
                ->setConfirmed(true)
                ->setCreatedAt($this->frDateTimeGenerator->generateImmutable($faker->date('Y:m:d') . ' ' .$faker->time('H:i:s')))
                ;
            $manager->persist($user);
        }

        //restricted_user
        $restrictedUser = new User;
        $restrictedUser
            ->setEmail('restricted.user@gmail.com')
            ->setPassword(
                $this->hasher->hashPassword($user, 'password')
            )
            ->setRoles([SiteConfig::ROLE_USER_RESTRICTED])
            ->setCivility(TextConfig::CIVILITY_M)
            ->setFirstName('jean')
            ->setLastName('restreint')
            ->setConfirmed(true)
            ->setCreatedAt($this->frDateTimeGenerator->generateImmutable('2012:12:15 12:10:05'))
            ;
        $manager->persist($restrictedUser);
        
        //admin
        $admin = new User;
        $admin
            ->setEmail('admin@mail.fr')
            ->setPassword(
                $this->hasher->hashPassword($admin, 'password')
            )
            ->setRoles([SiteConfig::ROLE_USER, SiteConfig::ROLE_ADMIN])
            ->setCivility(TextConfig::CIVILITY_M)
            ->setFirstName('admin')
            ->setLastName('admin')
            ->setConfirmed(true)
            ->setCreatedAt($this->frDateTimeGenerator->generateImmutable('2012:12:15 12:10:05'))
            ;
        $manager->persist($admin);


        //test user
        $confirmedUser = new User;
        $confirmedUser
            ->setEmail('confirmed.user@gmail.com')
            ->setPassword(
                $this->hasher->hashPassword($confirmedUser, 'password')
            )
            ->setRoles([SiteConfig::ROLE_USER])
            ->setConfirmed(true)
            ->setCreatedAt(new DateTimeImmutable())
            ;
        $manager->persist($confirmedUser);

        //test user
        $user = new User;
        $user
            ->setEmail('special@gmail.com')
            ->setPassword(
                $this->hasher->hashPassword($user, 'password')
            )
            ->setRoles([SiteConfig::ROLE_USER])
            ->setConfirmed(true)
            ->setCreatedAt(new DateTimeImmutable())
            ;
        $manager->persist($user);

        $manager->flush();
    }


    private function createEmail(string $firstName, string $lastName, $faker): string
    {
        return  
                $this->devReplaceSpecialCars->replace(strtolower(trim($firstName))) . 
                $faker->randomElement(['', '.', '-']) . 
                $this->devReplaceSpecialCars->replace(strtolower(trim($lastName))) . 
                $faker->randomElement(['', $faker->randomDigit()]) .
                '@'. 
                $faker->randomElement(['sfr.fr', 'gmail.com', 'orange.fr', 'wanadoo.fr'])
                ;

    }
}
