<?php
namespace App\Tests\UnitAndIntegration\Repository;

use App\DataFixtures\Tests\CategoryTestFixtures;
use App\Entity\Category;
use App\Repository\CategoryRepository;
use App\Tests\Utils\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @group Repository
 */
class CategoryRepositoryTest extends KernelTestCase
{
    use FixturesTrait;

    private CategoryRepository $categoryRepository;

    public function setUp(): void 
    {
        parent::setUp();

        self::bootKernel();

        $this->categoryRepository = static::getContainer()->get(CategoryRepository::class);
        $this->loadFixtures([CategoryTestFixtures::class]);
    }

    public function testFindAllOrderedForMenuListReturnArrayOfCategories()
    {
        $categories = $this->categoryRepository->findAllOrderedForMenuList();
        $this->assertInstanceOf(
            Category::class, 
            $categories[0]
        );
    }
    public function testFindAllOrderedForMenuListReturnCorrectCount()
    {
        $categories = $this->categoryRepository->findAllOrderedForMenuList();
        $count = $this->categoryRepository->count([]);

        $this->assertCount($count, $categories);
    }
    public function testFindAllOrderedForMenuListReturnCorrectOrderedCategories()
    {
        $categories = $this->categoryRepository->findAllOrderedForMenuList();
        $prevPosition = 0;
        foreach($categories as $category)
        {
            $this->assertTrue($prevPosition < $category->getListPosition());
            $prevPosition = $category->getListPosition();
        }
    }
    public function testFindAllOrderedForMenuListReturnCorrectOrderedSubCategories()
    {
        $categories = $this->categoryRepository->findAllOrderedForMenuList();
        $prevPosition = 0;
        foreach($categories[1]->getSubCategories() as $subCategory)
        {
            $this->assertTrue($prevPosition < $subCategory->getListPosition());
            $prevPosition = $subCategory->getListPosition();
        }
    }

    
    public function testFindOneOrderedReturnNullIfInCorrectIdIsPassed()
    {
        $result = $this->categoryRepository->findOneOrdered(451235456);
        $this->assertNull($result);
    }

    public function testFindOneOrderedReturnCorrectCategoryIfCorrectIdIsPassed()
    {
        $id = $this->categoryRepository->findOneBy([])->getId();
        $category = $this->categoryRepository->findOneOrdered($id);
        $this->assertInstanceOf(
            Category::class, 
            $category
        );
    }
    public function testFindOneOrderedReturnCorrectOrderedSubCategories()
    {
        $id = $this->categoryRepository->findOneBy(['slug' => 'categorie-1'])->getId(); // catégorie avec 3 sous-catégories entrées dans le désordre (2, 3, 1)
        $orderedCategory = $this->categoryRepository->findOneOrdered($id);

        $prevPosition = 0;
        foreach($orderedCategory->getSubCategories() as $subCategory)
        {
            $this->assertTrue($prevPosition < $subCategory->getListPosition());
            $prevPosition = $subCategory->getListPosition();
        }
    }

}