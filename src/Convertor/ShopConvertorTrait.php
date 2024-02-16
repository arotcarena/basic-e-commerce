<?php
namespace App\Convertor;

use App\Service\PicturePathResolver;
use App\Service\ProductShowUrlResolver;
use App\Twig\Runtime\PriceFormaterExtensionRuntime;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

trait ShopConvertorTrait
{
    public function __construct(
        protected UrlGeneratorInterface $urlGenerator,
        protected PicturePathResolver $picturePathResolver,
        protected PriceFormaterExtensionRuntime $priceFormater,
        protected ProductShowUrlResolver $productShowUrlResolver
    )
    {

    }
}

