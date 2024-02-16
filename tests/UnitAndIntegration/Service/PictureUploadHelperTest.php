<?php
namespace App\Tests\UnitAndIntegration\Service;

use App\Entity\Product;
use App\Entity\Category;
use App\Entity\SubCategory;
use PHPUnit\Framework\TestCase;
use App\Helper\FrDateTimeGenerator;
use App\Service\PictureUploadHelper;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @group Service
 */
class PictureUploadHelperTest extends TestCase
{
    private FrDateTimeGenerator $frDateTimeGenerator;

    private PictureUploadHelper $pictureUploadHelper;

    private array $filesByPosition;

    public function setUp(): void 
    {
        $this->frDateTimeGenerator = new FrDateTimeGenerator();

        $this->pictureUploadHelper = new PictureUploadHelper($this->frDateTimeGenerator);

        $this->filesByPosition = [
            1 => [
                'file' => new File('file-1.jpg', false),
                'alt' => 'texte alternatif 1' 
            ],
            2 => [
                'file' => new File('file-2.jpg', false),
                'alt' => 'texte alternatif 2' 
            ],
            3 => [
                'file' => new File('file-3.jpg', false),
                'alt' => 'texte alternatif 3' 
            ],
            4 => [
                'file' => new File('file-4.jpg', false),
                'alt' => 'texte alternatif 4' 
            ]
        ];
    }

    public function testUploadProductPicturesCorrectCount()
    {
        $product = new Product;
        $this->pictureUploadHelper->uploadProductPictures($this->filesByPosition, $product);
        $this->assertCount(4, $product->getPictures());
    }
    
    public function testUploadProductPicturesCorrectOrder()
    {
        $product = new Product;
        $this->pictureUploadHelper->uploadProductPictures($this->filesByPosition, $product);
        foreach($product->getPictures() as $picture)
        {
            $this->assertEquals(
                'texte alternatif '.$picture->getListPosition(),
                $picture->getAlt()
            );
        }
    }
    
    public function testUploadProductPicturesCorrectFiles()
    {
        $product = new Product;
        $this->pictureUploadHelper->uploadProductPictures($this->filesByPosition, $product);
        foreach($product->getPictures() as $picture)
        {
            $this->assertEquals(
                'file-'.$picture->getListPosition().'.jpg',
                $picture->getFile()->getPathname()
            );
        }
    }

    public function testUploadCategoryPicture()
    {
        $category = new Category;
        $this->pictureUploadHelper->uploadCategoryPicture(
            [
                'file' => new File('file.jpg', false), 
                'alt' => 'texte alternatif'
            ],
            $category
        );
        $this->assertEquals('file.jpg', $category->getPicture()->getFile()->getPathname());
        $this->assertEquals('texte alternatif', $category->getPicture()->getAlt());
    }

    public function testUploadSubCategoryPicture()
    {
        $subCategory = new SubCategory;
        $this->pictureUploadHelper->uploadCategoryPicture(
            [
                'file' => new File('file.jpg', false), 
                'alt' => 'texte alternatif'
            ],
            $subCategory
        );
        $this->assertEquals('file.jpg', $subCategory->getPicture()->getFile()->getPathname());
        $this->assertEquals('texte alternatif', $subCategory->getPicture()->getAlt());
    }
}