<?php
namespace App\Tests\Functional;

use App\Tests\Utils\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

abstract class FunctionalTest extends WebTestCase 
{
    use FixturesTrait;

    protected KernelBrowser $client;

    protected UrlGeneratorInterface $urlGenerator;


    public function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();

        $this->urlGenerator = $this->client->getContainer()->get(UrlGeneratorInterface::class);
    }
}