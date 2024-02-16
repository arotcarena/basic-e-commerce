<?php
namespace App\Tests\UnitAndIntegration\Repository;

use App\Tests\Utils\FixturesTrait;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\SubCategoryRepository;
use App\DataFixtures\Tests\CategoryTestFixtures;
use App\Entity\SubCategory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @group Repository
 */
class SubCategoryRepositoryTest extends KernelTestCase
{
    use FixturesTrait;

    private SubCategoryRepository $subCategoryRepository;

    public function setUp(): void 
    {
        parent::setUp();

        self::bootKernel();

        $this->subCategoryRepository = static::getContainer()->get(SubCategoryRepository::class);
        $this->loadFixtures([CategoryTestFixtures::class]);
    }

    public function testfindByBothSlugsWithIncorrectBoth()
    {
        $subCategory = $this->subCategoryRepository->findByBothSlugs('slugquiexistepas', 'autreslugquiexistevraimentpas');
        $this->assertNull($subCategory);
    }
    public function testfindByBothSlugsWithOneIncorrect()
    {
        $subCategory = $this->subCategoryRepository->findOneBy([]);
        $categorySlug = $subCategory->getParentCategory()->getSlug();
        $subCategorySlug = $subCategory->getSlug();
        
        $subCategory = $this->subCategoryRepository->findByBothSlugs('slugquiexistevraimentpas', $subCategorySlug);
        $this->assertNull($subCategory);
        
        $subCategory = $this->subCategoryRepository->findByBothSlugs($categorySlug, 'slugquiexistevraimentpas');
        $this->assertNull($subCategory);
    }
    public function testfindByBothSlugsWithCorrectBoth()
    {
        $subCategory = $this->subCategoryRepository->findOneBy([]);
        $categorySlug = $subCategory->getParentCategory()->getSlug();
        $subCategorySlug = $subCategory->getSlug();
        
        $result = $this->subCategoryRepository->findByBothSlugs($categorySlug, $subCategorySlug);
        $this->assertEquals($subCategory->getId(), $result->getId());
    }
    public function testfindByBothSlugsReturnSubCategory()
    {
        $subCategory = $this->subCategoryRepository->findOneBy([]);
        $categorySlug = $subCategory->getParentCategory()->getSlug();
        $subCategorySlug = $subCategory->getSlug();
        
        $result = $this->subCategoryRepository->findByBothSlugs($categorySlug, $subCategorySlug);
        $this->assertInstanceOf(
            SubCategory::class,
            $result
        );
    }
    public function testSlugExistsWithParentCategoryReturnBool()
    {
        $subCategory = $this->subCategoryRepository->findOneBy([]);
        $category = $subCategory->getParentCategory();
        $this->assertIsBool(
            $this->subCategoryRepository->slugExistsWithParentCategory($category, 'slug')
        );
    }
    public function testSlugExistsWithParentCategoryReturnFalseIfDontExist()
    {
        $subCategory = $this->subCategoryRepository->findOneBy([]);
        $category = $subCategory->getParentCategory();

        $this->assertFalse(
            $this->subCategoryRepository->slugExistsWithParentCategory(
                $category, 
                'slugquinexistevraimentpas'
            )
        );
    }
    public function testSlugExistsWithParentCategoryReturnTrueIfExist()
    {
        $subCategory = $this->subCategoryRepository->findOneBy([]);
        $category = $subCategory->getParentCategory();

        $subCategorySlug = $subCategory->getSlug();
        $this->assertTrue(
            $this->subCategoryRepository->slugExistsWithParentCategory(
                $category,
                $category->getSubCategories()[0]->getSlug()
            )
        );
    }
}