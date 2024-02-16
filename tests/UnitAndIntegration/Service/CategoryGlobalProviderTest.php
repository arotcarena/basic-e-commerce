<?php
namespace App\Tests\UnitAndIntegration\Service;

use stdClass;
use App\Entity\Category;
use PHPUnit\Framework\TestCase;
use App\Tests\Utils\FixturesTrait;
use App\Repository\CategoryRepository;
use App\Service\CategoryGlobalProvider;
use App\Convertor\CategoryToArrayConvertor;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @group Service
 */
class CategoryGlobalProviderTest extends TestCase
{
    use FixturesTrait;

    public function testReturnFormatIsJson()
    {
        $categoryRepository = $this->createMock(CategoryRepository::class);
        $categoryConvertor = $this->createMock(CategoryToArrayConvertor::class);

        $categoryGlobalProvider = new CategoryGlobalProvider($categoryRepository, $categoryConvertor);
        $this->assertJson(
            $categoryGlobalProvider->getJsonMenuList()
        );
    }

    public function testUseCorrectlyCategoryRepository()
    {
        $categoryRepository = $this->createMock(CategoryRepository::class);
        $categoryConvertor = $this->createMock(CategoryToArrayConvertor::class);

        $categoryGlobalProvider = new CategoryGlobalProvider($categoryRepository, $categoryConvertor);

        /** @var MockObject $categoryRepository */
        $categoryRepository->expects($this->once())
                            ->method('findAllOrderedForMenuList')
                            ;
        
        $categoryGlobalProvider->getJsonMenuList();
    }

    public function testUseCorrectlyCategoryConvertor()
    {
        $categoryRepository = $this->createMock(CategoryRepository::class);
        $categoryConvertor = $this->createMock(CategoryToArrayConvertor::class);

        $categoryGlobalProvider = new CategoryGlobalProvider($categoryRepository, $categoryConvertor);

        /** @var MockObject $categoryConvertor */
        $categoryConvertor->expects($this->once())
                            ->method('convert')
                            ;
        
        $categoryGlobalProvider->getJsonMenuList();
    }

    public function testCorrectSequenceOfFunctions()
    {
       $categoryRepository = $this->createMock(CategoryRepository::class);
       $categoryToArrayConvertor = $this->createMock(CategoryToArrayConvertor::class);
       $categoryGlobalProvider = new CategoryGlobalProvider($categoryRepository, $categoryToArrayConvertor);
       
       $category = new Category;
       /** @var MockObject $categoryRepository */
       $categoryRepository->expects($this->once())
                            ->method('findAllOrderedForMenuList')
                            ->willReturn($category)
                            ;
        /** @var MockObject $categoryToArrayConvertor */
        $categoryToArrayConvertor->expects($this->once())
                                    ->method('convert')
                                    ->with($category)
                                    ->willReturn(['test'])
                                    ;
        $this->assertEquals(json_encode(['test']), $categoryGlobalProvider->getJsonMenuList());
    }

    public function testCorrectReturnValue()
    {
        $categoryRepository = $this->createMock(CategoryRepository::class);
        $categoryToArrayConvertor = $this->createMock(CategoryToArrayConvertor::class);
        $categoryGlobalProvider = new CategoryGlobalProvider($categoryRepository, $categoryToArrayConvertor);
        
         /** @var MockObject $categoryToArrayConvertor */
         $categoryToArrayConvertor->expects($this->once())
                                     ->method('convert')
                                     ->willReturn([
                                        ['id' => 1, 'name' => 'catégorie1'],
                                        ['id' => 2, 'name' => 'catégorie2']
                                     ])
                                     ;
        $data = $categoryGlobalProvider->getJsonMenuList();
        $this->assertInstanceOf(
            stdClass::class,
            json_decode($data)[0]
        );
        $this->assertEquals('catégorie1', json_decode($data)[0]->name);
    }

}

