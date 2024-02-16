<?php

namespace App\DataFixtures\Tests;

use App\Config\SiteConfig;
use App\Entity\User;
use App\Helper\FrDateTimeGenerator;
use DateTimeImmutable;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

class UserWithTokenTestFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $hasher,
        private TokenGeneratorInterface $tokenGenerator,
        private FrDateTimeGenerator $frDateTimeGenerator
    )
    {

    }
    public function load(ObjectManager $manager)
    {
        $user = new User;
        $user
            ->setEmail('user_with_valid_token@gmail.com')
            ->setPassword(
                $this->hasher->hashPassword($user, 'password')
            )
            ->setRoles([SiteConfig::ROLE_USER])
            ->setConfirmationToken($this->tokenGenerator->generateToken())
            ->setConfirmationTokenExpireAt(time() + (3600 * 24 * 365 * 10)) // 10 ans pour être tranquille pour les tests (car parfois le time donné ici est antérieur au vrai time)
            ->setResetPasswordToken($this->tokenGenerator->generateToken())
            ->setResetPasswordTokenExpireAt(time() + (3600 * 24 * 365 * 10)) // 10 ans pour être tranquille pour les tests (car parfois le time donné ici est antérieur au vrai time)
            ->setCreatedAt(new DateTimeImmutable())
            ;
        $manager->persist($user);


        $userWithExpiredToken = new User;
        $userWithExpiredToken
            ->setEmail('user_with_expired_token@gmail.com')
            ->setPassword(
                $this->hasher->hashPassword($userWithExpiredToken, 'password')
            )
            ->setRoles([SiteConfig::ROLE_USER])
            ->setConfirmationToken($this->tokenGenerator->generateToken())
            ->setConfirmationTokenExpireAt(time() - 1)
            ->setResetPasswordToken($this->tokenGenerator->generateToken())
            ->setResetPasswordTokenExpireAt(time() - 1)
            ->setCreatedAt(new DateTimeImmutable())
            ;
        $manager->persist($userWithExpiredToken);

        $manager->flush();
    }
}