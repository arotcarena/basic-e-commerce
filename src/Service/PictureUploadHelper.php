<?php
namespace App\Service;

use App\Entity\Product;
use App\Entity\Category;
use App\Entity\Picture;
use App\Entity\SubCategory;
use App\Helper\FrDateTimeGenerator;

class PictureUploadHelper
{
    public function __construct(
        private FrDateTimeGenerator $frDateTimeGenerator
    )
    {

    }
    /**
     * @param array $filesByPosition [1 => ['file' => file, 'alt' => 'texte alternatif'], 2 => ]
     * @param Product $product
     */
    public function uploadProductPictures(array $filesByPosition, $product): void 
    {
        foreach($filesByPosition as $position => $data)
        {
            if($data['file'] !== null)
            {
                $picture = new Picture;
                $picture->setFile($data['file'])
                        ->setListPosition($position)
                        ->setCreatedAt($this->frDateTimeGenerator->generateImmutable())
                        ;
                if($data['alt'] !== null)
                {
                    $picture->setAlt($data['alt']);
                }
                //si il existe déjà une Picture avec le même listPosition, on la supprime
                foreach($product->getPictures() as $existingPicture)
                {
                    if($existingPicture->getListPosition() === $picture->getListPosition())
                    {
                        $product->removePicture($existingPicture);
                    }
                }

                $product->addPicture($picture);
            }
            
        }
    }

    /**
     * Undocumented function
     *
     * @param array $data ['file' => file, 'alt' => alt]
     * @param Category $category
     * @return void
     */
    public function uploadCategoryPicture($data, $category): void 
    {
        $picture = new Picture;
        $picture->setFile($data['file'])
                ->setCreatedAt($this->frDateTimeGenerator->generateImmutable())
                ;
        if($data['alt'] !== null)
        {
            $picture->setAlt($data['alt']);
        }
        $category->setPicture($picture);
    }

    /**
     * Undocumented function
     *
     * @param array $data ['file' => file, 'alt' => alt]
     * @param SubCategory $category
     * @return void
     */
    public function uploadSubCategoryPicture($data, $subCategory): void 
    {
        $picture = new Picture;
        $picture->setFile($data['file'])
                ->setCreatedAt($this->frDateTimeGenerator->generateImmutable())
                ;
        if($data['alt'] !== null)
        {
            $picture->setAlt($data['alt']);
        }
        $subCategory->setPicture($picture);
    }
}