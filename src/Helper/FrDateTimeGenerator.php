<?php
namespace App\Helper;

use DateTime;
use DateTimeImmutable;
use DateTimeZone;

class FrDateTimeGenerator
{
    public function generate(string $datetime = 'now'): DateTime
    {
        return new DateTime($datetime, new DateTimeZone('Europe/Paris'));
    }
    public function generateImmutable(string $datetime = 'now'): DateTimeImmutable
    {
        return new DateTimeImmutable($datetime, new DateTimeZone('Europe/Paris'));
    }
}