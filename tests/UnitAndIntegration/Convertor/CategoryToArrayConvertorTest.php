<?php
namespace App\Tests\UnitAndIntegration\Convertor;

use App\Convertor\CategoryToArrayConvertor;
use App\Tests\Utils\FixturesTrait;
use App\Service\PicturePathResolver;
use App\Repository\CategoryRepository;
use App\DataFixtures\Tests\CategoryTestFixtures;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @group Convertor
 */
class CategoryToArrayConvertorTest extends KernelTestCase
{
    use FixturesTrait;

    private CategoryRepository $categoryRepository;

    private UrlGeneratorInterface $urlGenerator;

    private PicturePathResolver $picturePathResolver;

    private CategoryToArrayConvertor $categoryConvertor;


    public function setUp(): void
    {
        $this->categoryRepository = static::getContainer()->get(CategoryRepository::class);
        $this->urlGenerator = static::getContainer()->get(UrlGeneratorInterface::class);
        $this->picturePathResolver = static::getContainer()->get(PicturePathResolver::class);
        $this->categoryConvertor = static::getContainer()->get(CategoryToArrayConvertor::class);

        $this->loadFixtures([CategoryTestFixtures::class]);
    }

    public function testContainsCorrectKeysWhenConvertOne()
    {
        $category = $this->categoryRepository->findOneBy([]);
        $returnCategory = $this->categoryConvertor->convert($category);

        $this->assertEquals(
            ['id', 'name', 'target', 'listPosition', 'subCategories'], 
            array_keys($returnCategory)
        );
    }

    public function testContainsCorrectKeysWhenConvertAll()
    {
        $categories = $this->categoryRepository->findAll();
        $returnCategory = $this->categoryConvertor->convert($categories)[0];

        $this->assertEquals(
            ['id', 'name', 'target', 'listPosition', 'subCategories'], 
            array_keys($returnCategory)
        );
    }
  
    public function testReturnCorrectCategoriesCount()
    {
        $categories = $this->categoryRepository->findAll();
        $data = $this->categoryConvertor->convert($categories);

        $this->assertCount(
            count($categories), 
            $data   
        );
    }
    public function testContainsCorrectCategoryName()
    {
        $category = $this->categoryRepository->findOneBy([]);
        $returnCategory = $this->categoryConvertor->convert($category);
        $this->assertEquals(
            $category->getName(), 
            str_replace('_', ' ', $returnCategory['name'])
        );
    }
    public function testContainsCorrectCategoryUrl()
    {
        $category = $this->categoryRepository->findOneBy([]);
        $url = $this->urlGenerator->generate('category_show', [
            'slug' => $category->getSlug()
        ]);
        $returnCategory = $this->categoryConvertor->convert($category);

        $this->assertEquals(
            $url, 
            $returnCategory['target']
        );
    }
    public function testContainsCorrectSubCategoriesCount()
    {
        $category = $this->categoryRepository->findOneBy([]);
        $returnCategory = $this->categoryConvertor->convert($category);

        $this->assertCount(
            count($category->getSubCategories()),
            $returnCategory['subCategories']  
        );
    }
    public function testContainsCorrectSubCategoryKeys()
    {
        $category = $this->categoryRepository->findOneBy([]);
        $returnCategory = $this->categoryConvertor->convert($category);
        $returnSubCategory = $returnCategory['subCategories'][0];

        $this->assertEquals([
            'id', 'name', 'target', 'picture', 'listPosition'
        ], array_keys($returnSubCategory));

        
        $this->assertEquals([
            'path', 'alt'
        ], array_keys($returnSubCategory['picture']));
    }
    public function testContainsCorrectSubCategoryName()
    {
        $category = $this->categoryRepository->findOneBy([]);

        $returnCategory = $this->categoryConvertor->convert($category);
        $returnSubCategory = $returnCategory['subCategories'][0]; 
        
        $this->assertEquals(
            $category->getSubCategories()->get(0)->getName(), 
            str_replace('_', ' ', $returnSubCategory['name'])
        );
    }
    public function testContainsCorrectSubCategoryUrl()
    {
        $category = $this->categoryRepository->findOneBy([]);

        $returnCategory = $this->categoryConvertor->convert($category);
        $returnSubCategory = $returnCategory['subCategories'][0]; 

        $url = $this->urlGenerator->generate('subCategory_show', [
            'categorySlug' => $category->getSlug(),
            'subCategorySlug' => $category->getSubCategories()->get(0)->getSlug()
        ]);
        $this->assertEquals(
            $url, 
            $returnSubCategory['target']
        );
    }
    public function testContainsCorrectSubCategoryPicturePath()
    {
        $category = $this->categoryRepository->findOneBy([]);
        $returnCategory = $this->categoryConvertor->convert($category);
        $returnSubCategory = $returnCategory['subCategories'][0];

        $picturePath = $this->picturePathResolver->getPath(
            $category->getSubCategories()[0]->getPicture()
        );

        $this->assertEquals(
            $picturePath,
            $returnSubCategory['picture']['path']
        );
    }

}

