<?php
namespace App\Tests\UnitAndIntegration\Entity;

use App\DataFixtures\Tests\CategoryTestFixtures;
use DateTimeImmutable;
use App\Entity\Category;
use App\Repository\CategoryRepository;
use App\Tests\UnitAndIntegration\Entity\EntityTest;
use App\Tests\Utils\FixturesTrait;

/**
 * @group Entity
 */
class CategoryTest extends EntityTest
{

    use FixturesTrait;


    public function testValidCategory()
    {
        $this->assertHasErrors(0, $this->createValidCategory());
    }

    public function testInvalidBlankName()
    {
        $this->assertHasErrors(
            1,
            $this->createValidCategory()->setName('')
        );
    }
    public function testInvalidTooLongName()
    {
        $this->assertHasErrors(
            1,
            $this->createValidCategory()->setName($this->moreThan200Caracters)
        );
    }
    public function testInvalidBlankSlug()
    {
        $this->assertHasErrors(
            1,
            $this->createValidCategory()->setSlug('')
        );
    }
    public function testInvalidTooLongSlug()
    {
        $this->assertHasErrors(
            1,
            $this->createValidCategory()->setSlug(str_repeat('too-long-valid-sluggg', 10))  // > 200 car.
        );
    }
    public function testInvalidSlugFormat()
    {
        $this->assertHasErrors(
            1, 
            $this->createValidCategory()->setSlug('Unsluginvalide')
        );
        $this->assertHasErrors(
            1, 
            $this->createValidCategory()->setSlug('unslug invalide')
        );
        $this->assertHasErrors(
            1, 
            $this->createValidCategory()->setSlug('unslug-inval.de')
        );
    }
    public function testInvalidNegativeOrZeroListPosition()
    {
        $this->assertHasErrors(
            1, 
            $this->createValidCategory()->setListPosition(-4)
        );
        $this->assertHasErrors(
            1, 
            $this->createValidCategory()->setListPosition(0)
        );
    }
    public function testInvalidExistingCategorySlug()
    {
        $this->loadFixtures([CategoryTestFixtures::class]);
        $existingCategorySlug = $this->findEntity(CategoryRepository::class)->getSlug();

        $this->assertHasErrors(
            1, 
            $this->createValidCategory()->setSlug($existingCategorySlug)
        );
    }
    public function testInvalidExistingListPosition()
    {
        $this->loadFixtures([CategoryTestFixtures::class]);
        $existingListPosition = $this->findEntity(CategoryRepository::class)->getListPosition();

        $this->assertHasErrors(
            1, 
            $this->createValidCategory()->setListPosition($existingListPosition)
        );
    }

    private function createValidCategory(): Category
    {
        return (new Category)
                ->setName('Nom de catégorie valide')
                ->setSlug('slug-de-categorie-valide')
                ->setCreatedAt(new DateTimeImmutable())
                ;
    }
}