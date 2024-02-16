<?php
namespace App\Tests\Functional\Controller;

use App\Tests\Functional\FunctionalTest;
use Doctrine\Bundle\DoctrineBundle\DataCollector\DoctrineDataCollector;

/**
 * @group FunctionalHome
 */
class HomeControllerTest extends FunctionalTest
{
    public function testHomePageRender()
    {
        $this->client->request('GET', $this->urlGenerator->generate('home'));
        $this->assertResponseIsSuccessful('response status de la page home != 200');
    }
    public function testDatabaseQueriesCount()
    {
        $this->client->enableProfiler();

        $this->client->request('GET', $this->urlGenerator->generate('home'));
        /** @var DoctrineDataCollector */
        $dbCollector = $this->client->getProfile()->getCollector('db');
        $this->assertLessThanOrEqual(2, $dbCollector->getQueryCount());
    }
}