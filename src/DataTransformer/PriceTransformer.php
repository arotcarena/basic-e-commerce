<?php
namespace App\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

class PriceTransformer implements DataTransformerInterface
{
    public function transform($price): ?float
    {
        if($price === null) {
            return null;
        }
        return (int)$price / 100;
    }

    public function reverseTransform($price): ?int
    {
        if($price === null) {
            return null;
        }
        return round((float)$price * 100);
    }
}