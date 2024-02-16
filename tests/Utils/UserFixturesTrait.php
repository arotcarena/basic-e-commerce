<?php
namespace App\Tests\Utils;

use App\Entity\User;
use App\Repository\UserRepository;
use App\DataFixtures\Tests\UserWithTokenTestFixtures;
use App\Tests\Utils\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

trait UserFixturesTrait
{
    use FixturesTrait;

    public function findUser(array $criteria = [], ?KernelBrowser $client = null): ?User
    {
        if($client) 
        {
            /** @var UserRepository */
            $userRepository = $client->getContainer()->get(UserRepository::class);
        }
        else
        {
            /** @var UserRepository */
            $userRepository = static::getContainer()->get(UserRepository::class);
        }
        return $userRepository->findOneBy($criteria);
    }

    public function findUserByEmail(string $email, ?KernelBrowser $client = null): ?User
    {
        return $this->findUser(['email' => $email], $client);
    }

    public function getUserWithValidToken(?KernelBrowser $client = null, $loadFixtures = true)
    {
        if($loadFixtures)
        {
            $this->loadFixtures([UserWithTokenTestFixtures::class], $client);
        }
        return $this->findUserByEmail('user_with_valid_token@gmail.com', $client);
    }

    public function getUserWithExpiredToken(?KernelBrowser $client = null, $loadFixtures = true)
    {
        if($loadFixtures)
        {
            $this->loadFixtures([UserWithTokenTestFixtures::class], $client);
        }
        return $this->findUserByEmail('user_with_expired_token@gmail.com', $client);
    }

}