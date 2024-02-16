<?php
namespace App\Helper;

class PurchaseRefGenerator
{
    public function generate(): string 
    {
        return substr(str_shuffle(str_repeat('AZERTYUIOPQSDFGHJKLMWXCVBNazertyuiopqsdfghjklmwxcvbn0123456789', 10)), 0, 10);
    }
}