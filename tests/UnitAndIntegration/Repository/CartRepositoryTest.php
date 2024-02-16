<?php
namespace App\Tests\UnitAndIntegration\Repository;

use App\DataFixtures\Tests\CartTestFixtures;
use App\DataFixtures\Tests\ProductTestFixtures;
use App\Entity\Product;
use App\Repository\CartRepository;
use App\Tests\Utils\FixturesTrait;
use App\Repository\PictureRepository;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @group Repository
 */
class CartRepositoryTest extends KernelTestCase
{
    use FixturesTrait;

    private CartRepository $cartRepository;

    public function setUp(): void 
    {
        parent::setUp();

        self::bootKernel();

        $this->loadFixtures([CartTestFixtures::class]);

        $this->cartRepository = static::getContainer()->get(CartRepository::class);
    }

    public function testfindOneByUserHydratedWithProducts()
    {
        $user = $this->findEntity(UserRepository::class);
        $cart = $this->cartRepository->findOneBy(['user' => $user]);

        $this->assertEquals(
            $cart->getId(),
            $this->cartRepository->findOneByUserHydratedWithProducts($user)->getId()
        );
    }
}