<?php
namespace App\Tests\UnitAndIntegration\Entity;

use App\Entity\User;
use DateTimeImmutable;
use App\Entity\Purchase;
use App\Config\SiteConfig;
use App\DataFixtures\Tests\PurchaseTestFixtures;
use App\Entity\PostalDetail;
use App\Entity\PurchaseLine;
use App\Repository\PurchaseRepository;
use App\Tests\UnitAndIntegration\Entity\EntityTest;
use App\Tests\Utils\FixturesTrait;

/**
 * @group Entity
 */
class PurchaseTest extends EntityTest
{
    use FixturesTrait;

    public function testValidPurchase()
    {
        $this->assertHasErrors(0, $this->createValidPurchase());
    }

    public function testInvalidBlankRef()
    {
        $this->assertHasErrors(
            1,
            $this->createValidPurchase()->setRef('')
        );
    }
    public function testInvalidTooLongRef()
    {
        $this->assertHasErrors(
            1,
            $this->createValidPurchase()->setRef($this->moreThan200Caracters)
        );
    }
    public function testInvalidNoPurchaseLines()
    {
        $purchase = (new Purchase)
                    ->setRef('ab1234')
                    ->setUser(new User)
                    ->setDeliveryDetail(new PostalDetail)
                    ->setInvoiceDetail(new PostalDetail)
                    ->setTotalPrice(200)
                    ->setStatus(SiteConfig::STATUS_PENDING)
                    ->setCreatedAt(new DateTimeImmutable())
                    ;
        $this->assertHasErrors(1, $purchase);
    }
    public function testInvalidNegativeOrZeroTotalPrice()
    {
        $this->assertHasErrors(
            1, 
            $this->createValidPurchase()->setTotalPrice(-4)
        );
        $this->assertHasErrors(
            1, 
            $this->createValidPurchase()->setTotalPrice(0)
        );
    }
    public function testInvalidStatus()
    {
        $this->assertHasErrors(
            1, 
            $this->createValidPurchase()->setStatus('STATUS_NONEXISTINGSTATUS')
        );
        $this->assertHasErrors(
            1, 
            $this->createValidPurchase()->setStatus('')
        );
    }
    public function testInvalidExistingRef()
    {
        $this->loadFixtures([PurchaseTestFixtures::class]);
        $purchase = $this->findEntity(PurchaseRepository::class);
        $this->assertHasErrors(
            1,
            $this->createValidPurchase()->setRef($purchase->getRef())
        );
    }

    private function createValidPurchase(): Purchase
    {
        return (new Purchase)
                ->setRef('ab1234')
                ->addPurchaseLine(new PurchaseLine)
                ->setTotalPrice(200)
                ->setStatus(SiteConfig::STATUS_PENDING)
                ->setCreatedAt(new DateTimeImmutable())
                ;
    }
}