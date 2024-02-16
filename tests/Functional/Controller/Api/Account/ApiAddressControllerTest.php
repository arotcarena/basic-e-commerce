<?php
namespace App\Tests\Functional\Controller\Api\Account;

use App\Config\TextConfig;
use App\Repository\AddressRepository;
use App\Tests\Functional\FunctionalTest;
use App\Tests\Functional\LoginUserTrait;
use Symfony\Component\HttpFoundation\Response;
use App\DataFixtures\Tests\AddressTestFixtures;
use App\Repository\UserRepository;
use Doctrine\Bundle\DoctrineBundle\DataCollector\DoctrineDataCollector;



/**
 * @group FunctionalApi
 */
class ApiAddressControllerTest extends FunctionalTest
{
    use LoginUserTrait;

    public function setUp(): void
    {
        parent::setUp();

        $this->loadFixtures([AddressTestFixtures::class]);  // fixtures dependent de UserTestFixtures
    }

    //auth
    public function testNotLoggedUserCannotAccess()
    {
        $id = $this->findEntity(AddressRepository::class)->getId();

        //create
        $this->client->request('POST', $this->urlGenerator->generate('api_address_create'), [], [], [], json_encode($this->createValidAddressData()));
        $this->assertResponseRedirects($this->urlGenerator->generate('security_login'));
        //update
        $this->client->request('POST', $this->urlGenerator->generate('api_address_update', ['id' => $id]), [], [], [], json_encode($this->createValidAddressData()));
        $this->assertResponseRedirects($this->urlGenerator->generate('security_login'));
        //delete
        $this->client->request('POST', $this->urlGenerator->generate('api_address_delete'), [], [], [], json_encode($id));
        $this->assertResponseRedirects($this->urlGenerator->generate('security_login'));
    }
    public function testUserCanAccess()
    {
        $this->loginUser();
        $id = $this->findEntity(AddressRepository::class)->getId();

        //create
        $this->client->request('POST', $this->urlGenerator->generate('api_address_create'), [], [], [], json_encode($this->createValidAddressData()));
        $this->assertResponseIsSuccessful();
        //update
        $this->client->request('POST', $this->urlGenerator->generate('api_address_update', ['id' => $id]), [], [], [], json_encode($this->createValidAddressData()));
        $this->assertResponseIsSuccessful();
        //delete
        $this->client->request('POST', $this->urlGenerator->generate('api_address_delete'), [], [], [], json_encode($id));
        $this->assertResponseIsSuccessful();
    }

    //index
    public function testIndexReturnAddressesWithCorrectKeys()
    {
        $this->loginUser();

        $this->client->request('GET', $this->urlGenerator->generate('api_address_index'));
        $this->assertEquals(
            ['id', 'civility', 'firstName', 'lastName', 'lineOne', 'lineTwo', 'postcode', 'city', 'country'],
            array_keys(get_object_vars(json_decode($this->client->getResponse()->getContent())[0]))
        );
    }
    public function testIndexContainsCorrectCount()
    {
        $user = $this->findEntity(UserRepository::class);
        $this->loginUser($user);

        $this->client->request('GET', $this->urlGenerator->generate('api_address_index'));
        $this->assertResponseIsSuccessful();
        $this->assertCount(
            $this->client->getContainer()->get(AddressRepository::class)->count(['user' => $user]),
            json_decode($this->client->getResponse()->getContent())
        );
    }
    public function testIndexReturnsCorrectValues()
    {
        $this->loginUser();

        $this->client->request('GET', $this->urlGenerator->generate('api_address_index'));
        $address = $this->client->getContainer()->get(AddressRepository::class)->findAll()[0];
        $data = json_decode($this->client->getResponse()->getContent())[0];
        $this->assertEquals(
            $address->getId(),
            $data->id
        );
        $this->assertEquals(
            $address->getFirstName(),
            $data->firstName
        );
        $this->assertEquals(
            $address->getCity(),
            $data->city
        );
    }
    public function testIndexDatabaseQueries()
    {
        $this->client->enableProfiler();
        $this->loginUser();

        $this->client->request('GET', $this->urlGenerator->generate('api_address_index'));
        $profiler = $this->client->getProfile();
        /** @var DoctrineDataCollector */
        $dbCollector = $profiler->getCollector('db');
        $this->assertLessThan(5, $dbCollector->getQueryCount());
    }

    //create
    public function testCreateWithInvalidDataContainingIncorrectKeys()
    {
        $this->loginUser();

        $this->client->request('POST', $this->urlGenerator->generate('api_address_create'), [], [], [], json_encode([
            'invalidKey' => 'invalidData',
            'lineOne' => 'rue de la jungle',
            'city' => 'Issy Les Moulineaux'
        ]));
        $this->assertResponseStatusCodeSame(Response::HTTP_INTERNAL_SERVER_ERROR);
        $this->assertNotNull(
            json_decode($this->client->getResponse()->getContent())->errors
        );
    }
    public function testCreateWithValidDataReturnId()
    {
        $this->loginUser();

        $this->client->request('POST', $this->urlGenerator->generate('api_address_create'), [], [], [], json_encode($this->createValidAddressData()));
        $this->assertResponseIsSuccessful();
        $this->assertIsInt(
            json_decode($this->client->getResponse()->getContent())
        );
    }
    public function testCreateWithValidDataCorrectPersist()
    {
        $this->loginUser();

        $this->client->request('POST', $this->urlGenerator->generate('api_address_create'), [], [], [], json_encode($this->createValidAddressData()));
        $this->assertResponseIsSuccessful();
        $id =  json_decode($this->client->getResponse()->getContent());

        $createdAddress = $this->findEntity(AddressRepository::class, ['id' => $id]);
        $this->assertNotNull($createdAddress);
        $this->assertEquals('Justine', $createdAddress->getFirstName());
        $this->assertEquals('22 route de l\'avenir', $createdAddress->getLineOne());
        $this->assertEquals('13300', $createdAddress->getPostcode());
    }

    //update
    public function testUpdateWithInexistantIdParam()
    {
        $this->loginUser();

        $this->client->request('POST', $this->urlGenerator->generate('api_address_update', ['id' => '123456789456']), [], [], [], json_encode([]));
        $this->assertResponseStatusCodeSame(Response::HTTP_INTERNAL_SERVER_ERROR);
        $this->assertNotNull(
            json_decode($this->client->getResponse()->getContent())->errors
        );
    }
    public function testUpdateWithInvalidDataContainingIncorrectKeys()
    {
        $this->loginUser();
        $id = $this->findEntity(AddressRepository::class)->getId();

        $this->client->request('POST', $this->urlGenerator->generate('api_address_update', ['id' => $id]), [], [], [], json_encode([
            'invalidKey' => 'invalidData',
            'lineOne' => 'rue de la jungle',
            'city' => 'Issy Les Moulineaux'
        ]));
        $this->assertResponseStatusCodeSame(Response::HTTP_INTERNAL_SERVER_ERROR);
        $this->assertNotNull(
            json_decode($this->client->getResponse()->getContent())->errors
        );
    }
    public function testUpdateWithValidDataCorrectPersist()
    {
        $this->loginUser();
        $id = $this->findEntity(AddressRepository::class)->getId();

        $this->client->request('POST', $this->urlGenerator->generate('api_address_update', ['id' => $id]), [], [], [], json_encode($this->createValidAddressData()));
        $this->assertResponseIsSuccessful();
        $updatedAddress = $this->findEntity(AddressRepository::class, ['id' => $id]);
        $this->assertEquals('Justine', $updatedAddress->getFirstName());
        $this->assertEquals('22 route de l\'avenir', $updatedAddress->getLineOne());
        $this->assertEquals('13300', $updatedAddress->getPostcode());
    }

    //delete
    public function testDeleteWithInexistantIdParam()
    {
        $this->loginUser();
        $this->client->request('POST', $this->urlGenerator->generate('api_address_delete'), [], [], [], json_encode(123456789456));
        $this->assertResponseStatusCodeSame(Response::HTTP_INTERNAL_SERVER_ERROR);
        $this->assertNotNull(
            json_decode($this->client->getResponse()->getContent())->errors
        );
    }
    public function testDeleteCorrectlyDelete()
    {
        $this->loginUser();
        $id = $this->findEntity(AddressRepository::class)->getId();

        $this->client->request('POST', $this->urlGenerator->generate('api_address_delete'), [], [], [], json_encode($id));

        $this->assertResponseIsSuccessful();
        $this->assertNull(
            $this->findEntity(AddressRepository::class, ['id' => $id])
        );
    }
    public function testDeleteDontDeleteUser()
    {
        $this->loginUser();
        $address = $this->findEntity(AddressRepository::class);
        $id = $address->getId();
        $userId = $address->getUser()->getId();

        $this->client->request('POST', $this->urlGenerator->generate('api_address_delete'), [], [], [], json_encode($id));
        
        $this->assertResponseIsSuccessful();
        $this->assertNotNull(
            $this->findEntity(UserRepository::class, ['id' => $userId])
        );
    }


    private function createValidAddressData(): array 
    {
        return [
            'civility' => TextConfig::CIVILITY_F,
            'firstName' => 'Justine',
            'lastName' => 'Trudo',
            'lineOne' => '22 route de l\'avenir',
            'lineTwo' => '',
            'postcode' => '13300',
            'city' => 'Chalons de Provenche',
            'country' => 'Franche'
        ];
    }
}