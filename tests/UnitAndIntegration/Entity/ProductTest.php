<?php
namespace App\Tests\UnitAndIntegration\Entity;

use DateTimeImmutable;
use App\Entity\Picture;
use App\Entity\Product;
use App\Tests\Utils\FixturesTrait;
use App\Repository\ProductRepository;
use App\DataFixtures\Tests\ProductTestFixtures;
use App\Tests\UnitAndIntegration\Entity\EntityTest;

/**
 * @group Entity
 */
class ProductTest extends EntityTest
{
    use FixturesTrait;


    public function testValidProduct()
    {
        $this->assertHasErrors(0, $this->createValidProduct());
    }

    public function testInvalidBlankPublicRef()
    {
        $this->assertHasErrors(
            1,
            $this->createValidProduct()->setPublicRef('')
        );
    }

    public function testInvalidTooLengthPublicRef()
    {
        $this->assertHasErrors(
            1,
            $this->createValidProduct()->setPublicRef($this->moreThan200Caracters)
        );
    }

    public function testInvalidTooLongPrivateRef()
    {
        $this->assertHasErrors(
            1,
            $this->createValidProduct()->setPrivateRef($this->moreThan200Caracters)
        );
    }

    public function testInvalidExistingPublicRef()
    {
        $this->loadFixtures([ProductTestFixtures::class]);
        /** @var Product */
        $product = $this->findEntity(ProductRepository::class);
        $this->assertHasErrors(
            1,
            $this->createValidProduct()->setPublicRef($product->getPublicRef())
        );
    }

    public function testInvalidExistingPrivateRef()
    {
        $this->loadFixtures([ProductTestFixtures::class]);
        /** @var Product */
        $product = $this->findEntity(ProductRepository::class);
        $this->assertHasErrors(
            1,
            $this->createValidProduct()->setPrivateRef($product->getPrivateRef())
        );
    }

    public function testInvalidBlankDesignation()
    {
        $this->assertHasErrors(
            1,
            $this->createValidProduct()->setDesignation('')
        );
    }

    public function testInvalidTooLongDesignation()
    {
        $this->assertHasErrors(
            1,
            $this->createValidProduct()->setDesignation($this->moreThan200Caracters)
        );
    }

    public function testInvalidBlankSlug()
    {
        $this->assertHasErrors(
            1,
            $this->createValidProduct()->setSlug('')
        );
    }
    public function testInvalidTooLongSlug()
    {
        $this->assertHasErrors(
            1,
            $this->createValidProduct()->setSlug(str_repeat('too-long-valid-sluggg', 10))  // > 200 car.
        );
    }
    public function testInvalidSlugFormat()
    {
        $this->assertHasErrors(
            1, 
            $this->createValidProduct()->setSlug('Unsluginvalide')
        );
        $this->assertHasErrors(
            1, 
            $this->createValidProduct()->setSlug('unslug invalide')
        );
        $this->assertHasErrors(
            1, 
            $this->createValidProduct()->setSlug('unslug-inval.de')
        );
    }
    public function testInvalidNegativeOrZeroPrice()
    {
        $this->assertHasErrors(
            1, 
            $this->createValidProduct()->setPrice(-4)
        );
        $this->assertHasErrors(
            1, 
            $this->createValidProduct()->setPrice(0)
        );
    }

    public function testInvalidNegativeStock()
    {
        $this->assertHasErrors(
            1, 
            $this->createValidProduct()->setStock(-4)
        );
    }

    public function testValidZeroStock()
    {
        $this->assertHasErrors(
            0, 
            $this->createValidProduct()->setStock(0)
        );
    }

    private function createValidProduct(): Product
    {
        return (new Product)
                ->setPublicRef('ab1234')
                ->setPrivateRef('ab123456')
                ->setDesignation('nom du produit')
                ->setSlug('un-slug-valide')
                ->setPrice(200)
                ->setStock(4)
                ->addPicture(new Picture)
                ->addPicture(new Picture)
                ->setCreatedAt(new DateTimeImmutable())
                ;
    }
}