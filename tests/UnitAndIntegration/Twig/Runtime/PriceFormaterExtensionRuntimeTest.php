<?php
namespace App\Tests\UnitAndIntegration\Twig\Runtime;

use PHPUnit\Framework\TestCase;
use App\Twig\Runtime\PriceFormaterExtensionRuntime;

/**
 * @group Twig
 */
class PriceFormaterExtensionRuntimeTest extends TestCase
{
    public function testCorrectlyFormat()
    {
        $priceFormater = new PriceFormaterExtensionRuntime();
        $this->assertEquals(
            $priceFormater->format(20000),
            '200,00 €'
        );
        $this->assertEquals(
            $priceFormater->format(200000),
            '2 000,00 €'
        );
    }
}