<?php
namespace App\Tests\IntegrationOnly\Service;

use App\DataFixtures\Tests\CategoryTestFixtures;
use App\Entity\Category;
use App\Entity\SubCategory;
use App\Repository\CategoryRepository;
use App\Repository\SubCategoryRepository;
use App\Service\UniqueListPositionVerificator;
use App\Tests\Utils\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @group Integration
 */
class UniqueListPositionVerificatorTest extends KernelTestCase
{
    use FixturesTrait;

    private UniqueListPositionVerificator $verificator;

    public function setUp(): void
    {
        parent::setUp();

        self::bootKernel();

        $subCategoryRepository = static::getContainer()->get(SubCategoryRepository::class);

        $this->verificator = new UniqueListPositionVerificator($subCategoryRepository);

        $this->loadFixtures([CategoryTestFixtures::class]);
    }

    public function testExistantListPositionWithSameParentCategory()
    {
         /** @var Category */
         $parentCategory = $this->findEntity(CategoryRepository::class, ['slug' => 'categorie-3']);
    
         $subCategory = (new SubCategory)
                         ->setParentCategory($parentCategory)
                         ->setListPosition(2)   // existant dans Catégorie 3
                         ;
        
        $this->assertFalse($this->verificator->verifySubCategory($subCategory));
    }
    public function testExistantListPositionWithDifferentParentCategory()
    {
        /** @var Category */
        $parentCategory = $this->findEntity(CategoryRepository::class, ['slug' => 'categorie-3']);
    
        $subCategory = (new SubCategory)
                        ->setParentCategory($parentCategory)
                        ->setListPosition(3)   // inexistant dans Catégorie 3, maix existant dans d'autres catégories
                        ;
        
        $this->assertTrue($this->verificator->verifySubCategory($subCategory));
    }
    public function testOriginalListPosition()
    {
        /** @var Category */
        $parentCategory = $this->findEntity(CategoryRepository::class, ['slug' => 'categorie-3']);
    
        $subCategory = (new SubCategory)
                        ->setParentCategory($parentCategory)
                        ->setListPosition(150)
                        ;
        
        $this->assertTrue($this->verificator->verifySubCategory($subCategory));
    }
}