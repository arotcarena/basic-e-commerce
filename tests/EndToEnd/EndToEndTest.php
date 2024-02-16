<?php
namespace App\Tests\EndToEnd;

use App\Tests\Utils\FixturesTrait;
use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\PantherTestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

abstract class EndToEndTest extends PantherTestCase
{
    use FixturesTrait;

    
    protected Client $client;
    
    protected UrlGeneratorInterface $urlGenerator;
    
    
    public function setUp(): void
    {
        parent::setUp();
    
        $this->client = static::createPantherClient();
    
        $this->urlGenerator = static::getContainer()->get(UrlGeneratorInterface::class);
    }
}