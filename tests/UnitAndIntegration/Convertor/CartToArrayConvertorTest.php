<?php
namespace App\Tests\UnitAndIntegration\Convertor;

use App\Entity\CartLine;
use App\Repository\CartRepository;
use App\Tests\Utils\FixturesTrait;
use App\Service\PicturePathResolver;
use App\Repository\ProductRepository;
use App\Convertor\CartToArrayConvertor;
use App\Convertor\ProductToArrayConvertor;
use App\DataFixtures\Tests\CartTestFixtures;
use App\DataFixtures\Tests\ProductTestFixtures;
use App\Twig\Runtime\PriceFormaterExtensionRuntime;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @group Convertor
 */
class CartToArrayConvertorTest extends KernelTestCase
{
    use FixturesTrait;

    private UrlGeneratorInterface $urlGenerator;

    private PicturePathResolver $picturePathResolver;

    private PriceFormaterExtensionRuntime $priceFormater;

    private CartToArrayConvertor $cartConvertor;

    private CartRepository $cartRepository;


    public function setUp(): void
    {
        $this->cartRepository = static::getContainer()->get(CartRepository::class);
        $this->priceFormater = static::getContainer()->get(PriceFormaterExtensionRuntime::class);
        $this->urlGenerator = static::getContainer()->get(UrlGeneratorInterface::class);
        $this->picturePathResolver = static::getContainer()->get(PicturePathResolver::class);
        $this->cartConvertor = static::getContainer()->get(CartToArrayConvertor::class);

        $this->loadFixtures([CartTestFixtures::class]);
    }

    public function testCartContainsCorrectKeys()
    {
        $cart = $this->cartRepository->findOneBy([]);
        $returnCart = $this->cartConvertor->convert($cart);

        $this->assertEquals(
            ['id', 'cartLines', 'totalPrice', 'count', 'updatedAt'], 
            array_keys($returnCart)
        );
    }
    public function testCartContainsCorrectId()
    {
        $cart = $this->cartRepository->findOneBy([]);
        $returnCart = $this->cartConvertor->convert($cart);

        $this->assertEquals(
            $cart->getId(), 
            $returnCart['id']
        );
    }
    public function testCartLinesContainsCorrectKeys()
    {
        $cart = $this->cartRepository->findOneBy([]);
        $returnCart = $this->cartConvertor->convert($cart);
        $returnCartLine = $returnCart['cartLines'][0];

        $this->assertEquals(
            ['id', 'product', 'quantity', 'totalPrice'], 
            array_keys($returnCartLine)
        );
    }
    public function testCartLinesContainsCorrectId()
    {
        $cart = $this->cartRepository->findOneBy([]);
        /** @var CartLine */
        $cartLine = $cart->getCartLines()->get(0);
        $returnCart = $this->cartConvertor->convert($cart);
        $returnCartLine = $returnCart['cartLines'][0];

        $this->assertEquals(
            $cartLine->getId(), 
            $returnCartLine['id']
        );
    }
   
} 
