<?php
namespace App\Tests\UnitAndIntegration\Convertor;

use DateTimeImmutable;
use App\Entity\Category;
use App\Convertor\ObjectToArrayConvertor;
use PHPUnit\Framework\TestCase;

/**
 * @group Convertor
 */
class ObjectToArrayConvertorTest extends TestCase
{
    public function testCorrectValuesArePassed()
    {
        $category = $this->createCategory();
        $convertor = new ObjectToArrayConvertor();
        $array = $convertor->convert($category, ['name', 'createdAt']);
        $this->assertEquals($category->getName(), $array['name']);
        $this->assertEquals($category->getCreatedAt(), $array['createdAt']);
    }
    public function testOnlyNecessariesValuesArePassed()
    {
        $category = $this->createCategory();
        $convertor = new ObjectToArrayConvertor();
        $array = $convertor->convert($category, ['createdAt']);
        $this->assertTrue(!isset($array['name']));
    }
    public function testConvertArrayOfObjects()
    {
        $categories = [
            $this->createCategory()->setName('category1'),
            $this->createCategory()->setName('category2')
        ];
        $convertor = new ObjectToArrayConvertor();
        $data = $convertor->convert($categories, ['name', 'createdAt']);
        $this->assertEquals($data[0]['name'], 'category1');
        $this->assertEquals($data[1]['name'], 'category2');
    }
    
    private function createCategory(): Category
    {
        return (new Category)
                ->setName('category')
                ->setCreatedAt(new DateTimeImmutable())
                ;
    }
}