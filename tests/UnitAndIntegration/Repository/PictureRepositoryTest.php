<?php
namespace App\Tests\UnitAndIntegration\Repository;

use App\DataFixtures\Tests\ProductTestFixtures;
use App\Entity\Product;
use App\Tests\Utils\FixturesTrait;
use App\Repository\PictureRepository;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @group Repository
 */
class PictureRepositoryTest extends KernelTestCase
{
    use FixturesTrait;

    private PictureRepository $pictureRepository;

    public function setUp(): void 
    {
        parent::setUp();

        self::bootKernel();

        $this->pictureRepository = static::getContainer()->get(PictureRepository::class);
    }

    public function testHydrateProductsWithFirstPicturesSetCorrectPicture()
    {
        $this->loadFixtures([ProductTestFixtures::class]);
        /** @var Product[] */
        $products = static::getContainer()->get(ProductRepository::class)->findAll();
        $this->pictureRepository->hydrateProductsWithFirstPicture($products);
        $i = 0;
        foreach($products as $product)
        {
            $this->assertEquals(
                $product->getId(),
                $product->getFirstPicture()->getProduct()->getId()
            );
            $this->assertEquals(
                1,
                $product->getFirstPicture()->getListPosition()
            );
            $i++;
            if($i > 4)
            {
                break;
            }
        }
    }
}