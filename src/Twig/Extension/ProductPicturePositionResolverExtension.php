<?php

namespace App\Twig\Extension;

use App\Twig\Runtime\ProductPicturePositionResolverExtensionRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class ProductPicturePositionResolverExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            // If your filter generates SAFE HTML, you should add a third
            // parameter: ['is_safe' => ['html']]
            // Reference: https://twig.symfony.com/doc/3.x/advanced.html#automatic-escaping
            new TwigFilter('product_picture_position_resolver', [ProductPicturePositionResolverExtensionRuntime::class, 'getPictureAtPosition']),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('product_picture_position_resolver', [ProductPicturePositionResolverExtensionRuntime::class, 'getPictureAtPosition']),
        ];
    }
}
