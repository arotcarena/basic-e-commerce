<?php
namespace App\Tests\Functional\Service;

use App\Service\StockService;
use App\Repository\PurchaseRepository;
use App\Tests\Functional\FunctionalTest;
use App\DataFixtures\Tests\PurchaseTestFixtures;
use Doctrine\Bundle\DoctrineBundle\DataCollector\DoctrineDataCollector;


/**
 * @group FunctionalService
 */
class StockServiceFunctionalTest extends FunctionalTest
{
    private StockService $stockService;


    public function setUp(): void
    {
        parent::setUp();

        $this->stockService = $this->client->getContainer()->get(StockService::class);

        $this->loadFixtures([PurchaseTestFixtures::class]);
    }

    public function testVerifyAndUpdateStocksDatabaseQueryCount()
    {
        $purchase = $this->findEntity(PurchaseRepository::class, ['ref' => 'valid_purchase_with_two_products']); // 2 products
        
        $this->client->enableProfiler();

        $this->client->request('GET', $this->urlGenerator->generate('tests_stockService_verifyAndUpdateStocks', [
            'purchaseId' => $purchase->getId()
        ]));

        $this->assertTrue(json_decode($this->client->getResponse()->getContent()), 'attention le test queryCount n\'est peut-être pas probant, car la function n\'a pas retourné un résultat correct');

        /** @var DoctrineDataCollector */
        $dbCollector = $this->client->getProfile()->getCollector('db');
        $this->assertLessThan(10, $dbCollector->getQueryCount());
    }
}