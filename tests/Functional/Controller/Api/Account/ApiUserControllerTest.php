<?php
namespace App\Tests\Functional\Controller\Api\Account;

use App\Config\TextConfig;
use App\DataFixtures\Tests\UserTestFixtures;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Tests\Functional\FunctionalTest;
use App\Tests\Functional\LoginUserTrait;
use Doctrine\Bundle\DoctrineBundle\DataCollector\DoctrineDataCollector;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group FunctionalApi
 */
class ApiUserControllerTest extends FunctionalTest
{
    use LoginUserTrait;

    private User $user;

    public function setUp(): void 
    {
        parent::setUp();

        $this->loadFixtures([UserTestFixtures::class]); // depends on UserTestFixtures & ProductTestFixtures

        /** @var User */
        $user = $this->findEntity(UserRepository::class, ['email' => 'confirmed_user@gmail.com']);
        $user->setCivility(TextConfig::CIVILITY_F)
            ->setFirstName('testFirstName')
            ->setLastName('testLastName')
            ;
        /** @var EntityManagerInterface */
        $em = $this->client->getContainer()->get(EntityManagerInterface::class);
        $em->flush();
        
        $this->loginUser($user);

        $this->user = $user;
    }

    //auth
    // on ne teste pas auth ici car ça n'a pas vraiment d'importance (s'il n'y a pas de user connecté et qu'on a oublié le IsGranted il y aura simplement une erreur dans le controller)

    public function testGetCivilStateReturnsArrayWithCorrectKeys()
    {
        $this->client->request('GET', $this->urlGenerator->generate('api_user_getCivilState'));
        $data = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals(
            ['email', 'civility', 'firstName', 'lastName'],
            array_keys(get_object_vars($data))
        );
    }

    public function testGetCivilStateReturnsCorrectValues()
    {
        $this->client->request('GET', $this->urlGenerator->generate('api_user_getCivilState'));
        $data = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals(
            TextConfig::CIVILITY_F,
            $data->civility
        );
        $this->assertEquals(
            'testFirstName',
            $data->firstName
        );
        $this->assertEquals(
            'testLastName',
            $data->lastName
        );
    }

    public function testSetCivilStateWithIncorrectCivilityData()
    {
        $this->client->request('POST', $this->urlGenerator->generate('api_user_setCivilState'), [], [], [], json_encode([
            'civility' => 'inexistant_civility',
            'firstName' => 'jean',
            'lastName' => 'claude'
        ]));
        $this->assertResponseStatusCodeSame(Response::HTTP_INTERNAL_SERVER_ERROR);
        $this->assertNotNull(json_decode($this->client->getResponse()->getContent())->errors);
    }

    public function testSetCivilStateWithIncorrectFirstNameValue()
    {
        $this->client->request('POST', $this->urlGenerator->generate('api_user_setCivilState'), [], [], [], json_encode([
            'civility' => TextConfig::CIVILITY_M,
            'firstName' => ['salut', 'salut'],
            'lastName' => 'claude'
        ]));
        $this->assertResponseStatusCodeSame(Response::HTTP_INTERNAL_SERVER_ERROR);
        $this->assertNotNull(json_decode($this->client->getResponse()->getContent())->errors);
    }

    public function testSetCivilStateCorrectPersist()
    {
        $id = $this->user->getId();
        $this->client->request('POST', $this->urlGenerator->generate('api_user_setCivilState'), [], [], [], json_encode([
            'civility' => TextConfig::CIVILITY_M,
            'firstName' => 'Jules',
            'lastName' => 'César'
        ]));
        $this->assertResponseIsSuccessful();
        $updatedUser = $this->findEntity(UserRepository::class, ['id' => $id]);
        $this->assertNotNull($updatedUser);
        $this->assertEquals(
            TextConfig::CIVILITY_M, $updatedUser->getCivility()
        );
        $this->assertEquals(
            'Jules', $updatedUser->getFirstName()
        );
        $this->assertEquals(
            'César', $updatedUser->getLastName()
        );
    }

    public function testGetCivilStateDatabaseQueries()
    {
        $this->client->enableProfiler();

        $this->client->request('GET', $this->urlGenerator->generate('api_user_getCivilState'));

        $profiler = $this->client->getProfile();
        /** @var DoctrineDataCollector */
        $dbCollector = $profiler->getCollector('db');
        $this->assertLessThanOrEqual(5, $dbCollector->getQueryCount());
    }
}