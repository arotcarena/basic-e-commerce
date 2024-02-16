<?php
namespace App\Tests\Utils;

use Symfony\Component\DomCrawler\Crawler;

trait FormTrait 
{
    protected function assertSelectContainsChoices(array $choices, string $name, Crawler $crawler): void 
    {
        $i = 0;
        foreach($choices as $label => $value) { 
            $i++;
            $option = $crawler->filter('[name='.$name.'] option:nth-child('.$i.')');
            $this->assertEquals($label, $option->text());
            $this->assertEquals($value, $option->attr('value'));
        }
    }
}