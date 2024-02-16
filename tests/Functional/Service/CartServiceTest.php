<?php
namespace App\Tests\Service;

use App\DataFixtures\Tests\CartTestFixtures;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Tests\Functional\FunctionalTest;
use App\Tests\Functional\LoginUserTrait;
use Doctrine\Bundle\DoctrineBundle\DataCollector\DoctrineDataCollector;

/**
 * @group FunctionalService
 */
class CartServiceTest extends FunctionalTest
{
    use LoginUserTrait;

    public function testOnLoginUpdateDatabaseQueriesCount()
    {
        $this->loadFixtures([CartTestFixtures::class]);
        /** @var User */
        $userWith3CartLines = $this->findEntity(UserRepository::class, ['email' => 'user@gmail.com']); 
        $this->loginUser($userWith3CartLines);
        $this->client->enableProfiler();
        $this->client->request('GET', $this->urlGenerator->generate('tests_cartService_onLoginUpdate'));

        $profile = $this->client->getProfile();
        /** @var DoctrineDataCollector */
        $dbCollector = $profile->getCollector('db');
        $this->assertLessThan(15, $dbCollector->getQueryCount());
    }
}