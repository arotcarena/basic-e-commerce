<?php
namespace App\Tests\UnitAndIntegration\Service;

use App\Entity\Category;
use App\Entity\SubCategory;
use App\Repository\SubCategoryRepository;
use PHPUnit\Framework\TestCase;
use App\Service\UniqueListPositionVerificator;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @group Service
 */
class UniqueListPositionVerificatorTest extends TestCase
{
    private MockObject $subCategoryRepository;

    private UniqueListPositionVerificator $verificator;

    public function setUp(): void 
    {
        $this->subCategoryRepository = $this->createMock(SubCategoryRepository::class);

        $this->verificator = new UniqueListPositionVerificator($this->subCategoryRepository);
    }

    public function testExistantListPosition()
    {
        $subCategory = (new SubCategory)
                        ->setParentCategory(new Category)
                        ->setListPosition(2)
                        ;
        $this->subCategoryRepository->expects($this->once())
                                    ->method('findOneBy')
                                    ->with(['parentCategory' => $subCategory->getParentCategory(), 'listPosition' => 2])
                                    ->willReturn(new SubCategory)
                                    ;
        
        $this->assertFalse($this->verificator->verifySubCategory($subCategory));
    }
    public function testValidListPosition()
    {
        $subCategory = (new SubCategory)
                        ->setParentCategory(new Category)
                        ->setListPosition(2)
                        ;
        $this->subCategoryRepository->expects($this->once())
                                    ->method('findOneBy')
                                    ->with(['parentCategory' => $subCategory->getParentCategory(), 'listPosition' => 2])
                                    ->willReturn(null)
                                    ;
        
        $this->assertTrue($this->verificator->verifySubCategory($subCategory));
    }

}