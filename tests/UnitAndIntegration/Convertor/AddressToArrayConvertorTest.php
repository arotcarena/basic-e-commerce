<?php
namespace App\Tests\UnitAndIntegration\Convertor;

use App\Convertor\AddressToArrayConvertor;
use App\DataFixtures\Tests\AddressTestFixtures;
use App\Tests\Utils\FixturesTrait;
use App\Repository\AddressRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @group Convertor
 */
class AddressToArrayConvertorTest extends KernelTestCase
{
    use FixturesTrait;

    private AddressRepository $addressRepository;

    private AddressToArrayConvertor $addressConvertor;


    public function setUp(): void
    {
        $this->addressRepository = static::getContainer()->get(AddressRepository::class);
        $this->addressConvertor = static::getContainer()->get(AddressToArrayConvertor::class);

        $this->loadFixtures([AddressTestFixtures::class]);
    }

    public function testContainsCorrectKeysWhenConvertOne()
    {
        $address = $this->addressRepository->findOneBy([]);
        $returnAddress = $this->addressConvertor->convert($address);

        $this->assertEquals(
            ['id', 'civility', 'firstName', 'lastName', 'lineOne', 'lineTwo', 'postcode', 'city', 'country'], 
            array_keys($returnAddress)
        );
    }

    public function testContainsCorrectKeysWhenConvertAll()
    {
        $addresses = $this->addressRepository->findAll();
        $returnAddress = $this->addressConvertor->convert($addresses)[0];

        $this->assertEquals(
            ['id', 'civility', 'firstName', 'lastName', 'lineOne', 'lineTwo', 'postcode', 'city', 'country'], 
            array_keys($returnAddress)
        );
    }
  
    public function testReturnCorrectAddressesCount()
    {
        $addresses = $this->addressRepository->findAll();
        $data = $this->addressConvertor->convert($addresses);

        $this->assertCount(
            count($addresses), 
            $data   
        );
    }
    public function testContainsCorrectId()
    {
        $address = $this->addressRepository->findOneBy([]);
        $returnAddress = $this->addressConvertor->convert($address);
        $this->assertEquals(
            $address->getId(), 
            $returnAddress['id']
        );
    }
    public function testContainsCorrectFirstName()
    {
        $address = $this->addressRepository->findOneBy([]);
        $returnAddress = $this->addressConvertor->convert($address);
        $this->assertEquals(
            $address->getFirstName(), 
            $returnAddress['firstName']
        );
    }
    public function testContainsCorrectLastName()
    {
        $address = $this->addressRepository->findOneBy([]);
        $returnAddress = $this->addressConvertor->convert($address);
        $this->assertEquals(
            $address->getLastName(), 
            $returnAddress['lastName']
        );
    }
    public function testContainsCorrectLineOne()
    {
        $address = $this->addressRepository->findOneBy([]);
        $returnAddress = $this->addressConvertor->convert($address);
        $this->assertEquals(
            $address->getLineOne(), 
            $returnAddress['lineOne']
        );
    }
    public function testContainsCorrectLineTwo()
    {
        $address = $this->addressRepository->findOneBy([]);
        $returnAddress = $this->addressConvertor->convert($address);
        $this->assertEquals(
            $address->getLineTwo(), 
            $returnAddress['lineTwo']
        );
    }
    public function testContainsCorrectPostcode()
    {
        $address = $this->addressRepository->findOneBy([]);
        $returnAddress = $this->addressConvertor->convert($address);
        $this->assertEquals(
            $address->getPostcode(), 
            $returnAddress['postcode']
        );
    }
    public function testContainsCorrectCity()
    {
        $address = $this->addressRepository->findOneBy([]);
        $returnAddress = $this->addressConvertor->convert($address);
        $this->assertEquals(
            $address->getCity(), 
            $returnAddress['city']
        );
    }
    public function testContainsCorrectCountry()
    {
        $address = $this->addressRepository->findOneBy([]);
        $returnAddress = $this->addressConvertor->convert($address);
        $this->assertEquals(
            $address->getCountry(), 
            $returnAddress['country']
        );
    }


}

