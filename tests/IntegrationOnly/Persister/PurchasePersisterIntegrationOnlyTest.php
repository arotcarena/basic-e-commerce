<?php
namespace App\Tests\IntegrationOnly\Persister;

use stdClass;
use TypeError;
use App\Entity\Cart;
use DateTimeImmutable;
use App\Entity\CartLine;
use App\Entity\Purchase;
use App\Config\SiteConfig;
use App\Config\TextConfig;
use App\Entity\PostalDetail;
use App\Entity\PurchaseLine;
use App\Repository\CartRepository;
use App\Tests\Utils\FixturesTrait;
use App\Helper\FrDateTimeGenerator;
use App\Persister\PurchasePersister;
use App\Repository\ProductRepository;
use App\Repository\PurchaseRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\DataFixtures\Tests\CartTestFixtures;
use App\DataFixtures\Tests\PurchaseTestFixtures;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @group Persister
 */
class PurchasePersisterIntegrationOnlyTest extends KernelTestCase
{
    use FixturesTrait;

    private PurchasePersister $purchasePersister;

    private FrDateTimeGenerator $frDateTimeGenerator;

    private EntityManagerInterface $em;

    public function setUp(): void 
    {
        parent::setUp();

        self::bootKernel();

        $this->purchasePersister = static::getContainer()->get(PurchasePersister::class);

        $this->frDateTimeGenerator = static::getContainer()->get(FrDateTimeGenerator::class);

        $this->em = static::getContainer()->get(EntityManagerInterface::class);

        $this->loadFixtures([CartTestFixtures::class, PurchaseTestFixtures::class]); //depends on UserTestFixtures & ProductTestFixtures
    }


    public function testPersistWithInvalidCheckoutDeliveryParam()
    {
        $cart = $this->findEntity(CartRepository::class);
        $checkoutData = $this->createValidCheckoutData();
        $checkoutData->deliveryAddress->firstName = '';
        $this->assertFalse(
            $this->purchasePersister->persist(new Purchase, $cart, $checkoutData)
        );
    }
    public function testPersistWithInvalidCheckoutInvoiceParam()
    {
        $cart = $this->findEntity(CartRepository::class);
        $checkoutData = $this->createValidCheckoutData();
        $checkoutData->invoiceAddress->city = '';
        $this->assertFalse(
            $this->purchasePersister->persist(new Purchase, $cart, $checkoutData)
        );
    }
    public function testPersistWithValidParamsReturnTrue()
    {
        $cart = $this->findEntity(CartRepository::class);
        $this->assertTrue(
            $this->purchasePersister->persist(new Purchase, $cart, $this->createValidCheckoutData())
        );
    }
    public function testPersistWithValidParamsCorrectPersist()
    {
        $purchase = new Purchase;
        $this->em->persist($purchase);
        $this->em->flush();
        $id = $purchase->getId();
        
        $cart = $this->findEntity(CartRepository::class);

        $this->purchasePersister->persist($purchase, $cart, $this->createValidCheckoutData());

        $updatedPurchase = $this->findEntity(PurchaseRepository::class, ['id' => $id]);

        $this->assertNotNull($updatedPurchase->getRef());
    }


    private function createHydratedPurchase(): Purchase
    {
        return (new Purchase)
                ->setRef('')
                ->addPurchaseLine(
                    (new PurchaseLine)
                    ->setProduct([
                        'id' => null,
                        'publicRef' => 'ref',
                        'privateRef' => 'ref',
                        'designation' => 'produit',
                        'price' => 100
                    ])
                    ->setTotalPrice(100)
                    ->setQuantity(1)
                )
                ->setDeliveryDetail(
                    (new PostalDetail)
                    ->setCivility(TextConfig::CIVILITY_M)
                    ->setFirstName('delivery_firstName')
                    ->setLastName('delivery_lastName')
                    ->setLineOne('delivery_lineOne')
                    ->setLineTwo('delivery_lineTwo')
                    ->setPostcode('75000')
                    ->setCity('delivery_city')
                    ->setCountry('delivery_country')
                    ->setCreatedAt($this->frDateTimeGenerator->generateImmutable())
                )
                ->setInvoiceDetail(
                    (new PostalDetail)
                    ->setCivility(TextConfig::CIVILITY_M)
                    ->setFirstName('civility_firstName')
                    ->setLastName('civility_lastName')
                    ->setLineOne('invoice_lineOne')
                    ->setLineTwo('invoice_lineTwo')
                    ->setPostcode('75000')
                    ->setCity('invoice_city')
                    ->setCountry('invoice_country')
                    ->setCreatedAt($this->frDateTimeGenerator->generateImmutable())
                )
                ->setTotalPrice(100)
                ->setCreatedAt($this->frDateTimeGenerator->generateImmutable())
                ;
    }
   
    private function createValidCheckoutData(): stdClass
    {
        return (object)[
            'civilState' => (object)[
                'civility' => TextConfig::CIVILITY_M,
                'firstName' => 'civility_firstName',
                'lastName' => 'civility_lastName',
            ],
            'deliveryAddress' => (object)[
                'civility' => TextConfig::CIVILITY_M,
                'firstName' => 'delivery_firstName',
                'lastName' => 'delivery_lastName',
                'lineOne' => 'delivery_lineOne',
                'lineTwo' => 'delivery_lineTwo',
                'postcode' => '75000',
                'city' => 'delivery_city',
                'country' => 'delivery_country',
            ],
            'invoiceAddress' => (object)[
                'lineOne' => 'invoice_lineOne',
                'lineTwo' => 'invoice_lineTwo',
                'postcode' => '75000',
                'city' => 'invoice_city',
                'country' => 'invoice_country',
            ],
        ];
    }

}