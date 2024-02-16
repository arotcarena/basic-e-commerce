<?php
namespace App\Helper;

class DevReplaceSpecialCars
{
    private $replace = [
        'é' => 'e',
        'è' => 'e',
        'à' => 'a',
        'ù' => 'u',
        'û' => 'u',
        'ê' => 'e',
        'ô' => 'o',
        'â' => 'a',
        'î' => 'i',
        'ä' => 'a',
        'ë' => 'e',
        'ï' => 'i',
        'ö' => 'o',
        'ü' => 'u'
    ];

    public function replace(string $text)
    {
        return str_replace(array_keys($this->replace), array_values($this->replace), $text);
    }
}