<?php
namespace App\Tests\UnitAndIntegration\DataTransformer;

use App\DataTransformer\PriceTransformer;
use PHPUnit\Framework\TestCase;

/**
 * @group DataTransformer
 */
class PriceTransformerTest extends TestCase
{
    private PriceTransformer $priceTransformer;

    public function setUp(): void 
    {
        $this->priceTransformer = new PriceTransformer();
    }

    public function testTransform()
    {
        $this->assertEquals(
            10.5,
            $this->priceTransformer->transform(1050)
        );
    }
    public function testTransformNullParam()
    {   
        $this->assertNull($this->priceTransformer->transform(null));
    }
    public function testTransformStringParam()
    {   
        $this->assertEquals(
            4,
            $this->priceTransformer->transform('400')
        );
    }
    public function testReverseTransform()
    {
        $this->assertEquals(
            1020,
            $this->priceTransformer->reverseTransform(10.2)
        );
    }
    public function testReverseTransformNullParam()
    {
        $this->assertNull($this->priceTransformer->reverseTransform(null));
    }
    public function testReverseTransformStringParam()
    {   
        $this->assertEquals(
            400,
            $this->priceTransformer->reverseTransform('4')
        );
    }
}