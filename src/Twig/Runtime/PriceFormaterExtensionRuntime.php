<?php

namespace App\Twig\Runtime;

use Twig\Extension\RuntimeExtensionInterface;

class PriceFormaterExtensionRuntime implements RuntimeExtensionInterface
{
    public function __construct()
    {
        // Inject dependencies if needed
    }

    public function format(int $price)
    {
        return number_format($price / 100, 2, ',', ' ') . ' €';
    }
}
