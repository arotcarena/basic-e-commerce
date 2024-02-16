<?php

namespace App\DataFixtures\Tests;

use App\Config\SiteConfig;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserTestFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $hasher,
    )
    {

    }
    public function load(ObjectManager $manager)
    {
        $user = new User;
        $user
            ->setEmail('user@gmail.com')
            ->setPassword(
                $this->hasher->hashPassword($user, 'password')
            )
            ->setRoles([SiteConfig::ROLE_USER])
            ->setCreatedAt(new DateTimeImmutable())
            ;
        $manager->persist($user);

        $confirmedUser = new User;
        $confirmedUser
            ->setEmail('confirmed_user@gmail.com')
            ->setPassword(
                $this->hasher->hashPassword($confirmedUser, 'password')
            )
            ->setRoles([SiteConfig::ROLE_USER])
            ->setConfirmed(true)
            ->setCreatedAt(new DateTimeImmutable())
            ;
        $manager->persist($confirmedUser);

        $restrictedUser = new User;
        $restrictedUser
            ->setEmail('restricted_user@gmail.com')
            ->setPassword(
                $this->hasher->hashPassword($restrictedUser, 'password')
            )
            ->setRoles([SiteConfig::ROLE_USER_RESTRICTED])
            ->setConfirmed(true)
            ->setCreatedAt(new DateTimeImmutable())
            ;
        $manager->persist($restrictedUser);

        $admin = new User;
        $admin
            ->setEmail('admin@gmail.com')
            ->setPassword(
                $this->hasher->hashPassword($admin, 'password')
            )
            ->setRoles([SiteConfig::ROLE_USER, SiteConfig::ROLE_ADMIN])
            ->setConfirmed(true)
            ->setCreatedAt(new DateTimeImmutable())
            ;
        $manager->persist($admin);

        $manager->flush();
    }
}