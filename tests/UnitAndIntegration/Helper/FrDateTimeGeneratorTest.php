<?php
namespace App\Tests\UnitAndIntegration\Helper;

use App\Helper\FrDateTimeGenerator;
use PHPUnit\Framework\TestCase;

/**
 * @group Helper
 */
class FrDateTimeGeneratorTest extends TestCase
{
    public function testFunctionGenerate()
    {
        $dateTimeString = '2020:10:05 12:10:55';
        $dateTime = (new FrDateTimeGenerator())->generate($dateTimeString);
        $this->assertEquals($dateTimeString, $dateTime->format('Y:m:d H:i:s'), 'La fonction generate() ne renvoie pas la bonne date');
        $this->assertEquals('Europe/Paris', $dateTime->getTimezone()->getName(), 'la fonction generate renvoie une date avec une timezone différente de "Europe/Paris"');
    }
    public function testFunctionGenerateImmutable()
    {
        $dateTimeString = '2020:10:05 12:10:55';
        $dateTimeImmutable = (new FrDateTimeGenerator())->generateImmutable($dateTimeString);
        $this->assertEquals($dateTimeString, $dateTimeImmutable->format('Y:m:d H:i:s'), 'La fonction generate() ne renvoie pas la bonne date');
        $this->assertEquals('Europe/Paris', $dateTimeImmutable->getTimezone()->getName(), 'la fonction generate renvoie une date avec une timezone différente de "Europe/Paris"');
    }
}