<?php
namespace App\Tests\Functional;

use App\DataFixtures\Tests\UserTestFixtures;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Tests\Utils\FixturesTrait;

trait LoginUserTrait
{

    public function loginUser(User $user = null, $loadFixtures = false): void 
    {
        if($loadFixtures) 
        {
            $this->loadFixtures([UserTestFixtures::class]);
        }

        if(!$user) 
        {
            $user = $this->findEntity(UserRepository::class);
        }
        $this->client->loginUser($user);
    }
}