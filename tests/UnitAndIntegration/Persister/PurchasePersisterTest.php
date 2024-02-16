<?php
namespace App\Tests\UnitAndIntegration\Persister;

use App\Config\TextConfig;
use App\Convertor\PurchaseLineProductConvertor;
use App\Entity\Cart;
use App\Entity\CartLine;
use App\Entity\PostalDetail;
use App\Entity\Product;
use App\Entity\Purchase;
use App\Entity\PurchaseLine;
use App\Entity\User;
use App\Helper\FrDateTimeGenerator;
use App\Helper\PurchaseRefGenerator;
use App\Persister\PurchasePersister;
use App\Repository\AddressRepository;
use App\Repository\PurchaseRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use TypeError;


/**
 * @group Persister
 */
class PurchasePersisterTest extends TestCase
{
    private PurchasePersister $purchasePersister;

    private MockObject|PurchaseRepository $purchaseRepository;

    private MockObject|ValidatorInterface $validator;

    private MockObject|EntityManagerInterface $em;

    private MockObject|FrDateTimeGenerator $frDateTimeGenerator;

    private MockObject|PurchaseRefGenerator $purchaseRefGenerator;

    public function setUp(): void 
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $addressRepository = $this->createMock(AddressRepository::class);
        $this->purchaseRefGenerator = $this->createMock(PurchaseRefGenerator::class);
        $this->frDateTimeGenerator = $this->createMock(FrDateTimeGenerator::class);
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->purchaseRepository = $this->createMock(PurchaseRepository::class);

        $this->purchasePersister = new PurchasePersister(
            $this->em, $addressRepository, 
            $this->purchaseRefGenerator, 
            $this->frDateTimeGenerator, 
            $this->validator, 
            $this->purchaseRepository,
            new PurchaseLineProductConvertor
        );
    }


    public function testPersistWithInvalidCartThrowException()
    {
        $this->expectException(TypeError::class);
        $this->purchasePersister->persist(new Purchase, new Cart, new stdClass);
    }

    public function testPersistWithInvalidCartDontPersist()
    {
        $this->em->expects($this->never())
                ->method('flush')
        ;
        $this->expectException(TypeError::class);
        $this->purchasePersister->persist(new Purchase, new Cart, new stdClass);
    }

    public function testPersistInvalidPurchaseReturnFalse()
    {
        $this->validator->expects($this->exactly(3))
                        ->method('validate')
                        ->willReturn(new ConstraintViolationList([new ConstraintViolation('error', null, [], '', null, '')]))
                        ;
        $this->assertFalse(
            $this->purchasePersister->persist(new Purchase, $this->createValidCart(), $this->createValidCheckoutData())
        );
    }

    public function testPersistInvalidPurchaseDontPersist()
    {
        $this->validator->expects($this->exactly(3))
                        ->method('validate')
                        ->willReturn(new ConstraintViolationList([new ConstraintViolation('error', null, [], '', null, '')]))
                        ;
        $this->em->expects($this->never())
                    ->method('flush')
                    ;
        $this->purchasePersister->persist(new Purchase, $this->createValidCart(), $this->createValidCheckoutData());
    }

    public function testPersistValidPurchasePersistCorrectData()
    {
        $this->validator->expects($this->exactly(3))
                        ->method('validate')
                        ->willReturn(new ConstraintViolationList([]))
                        ;
        $this->em->expects($this->once())
                    ->method('flush')
                    ;
        $this->purchasePersister->persist(new Purchase, $this->createValidCart(), $this->createValidCheckoutData());
    }
    public function testPersistValidPurchaseReturnTrue()
    {
        $this->validator->expects($this->exactly(3))
                        ->method('validate')
                        ->willReturn(new ConstraintViolationList([]))
                        ;
        $this->assertTrue(
            $this->purchasePersister->persist(new Purchase, $this->createValidCart(), $this->createValidCheckoutData())
        );
    }


    public function testPersistSetCorrectDeliveryDetailValuesIntoPurchase()
    {
        $purchase = new Purchase;
        $this->validator->expects($this->exactly(3))
                        ->method('validate')
                        ->willReturn(new ConstraintViolationList([]))
                        ;
        $this->purchasePersister->persist($purchase, $this->createValidCart(), $this->createValidCheckoutData());

        $this->assertEquals(TextConfig::CIVILITY_M, $purchase->getDeliveryDetail()->getCivility());
        $this->assertEquals('delivery_firstName', $purchase->getDeliveryDetail()->getFirstName());
        $this->assertEquals('delivery_lastName', $purchase->getDeliveryDetail()->getLastName());
        $this->assertEquals('delivery_lineOne', $purchase->getDeliveryDetail()->getLineOne());
        $this->assertEquals('delivery_lineTwo', $purchase->getDeliveryDetail()->getLineTwo());
        $this->assertEquals('75000', $purchase->getDeliveryDetail()->getPostcode());
        $this->assertEquals('delivery_city', $purchase->getDeliveryDetail()->getCity());
        $this->assertEquals('delivery_country', $purchase->getDeliveryDetail()->getCountry());
    }
    public function testPersistSetCorrecInvoiceDetailValuesIntoPurchase()
    {
        $purchase = new Purchase;
        $this->validator->expects($this->exactly(3))
                        ->method('validate')
                        ->willReturn(new ConstraintViolationList([]))
                        ;
        $this->purchasePersister->persist($purchase, $this->createValidCart(), $this->createValidCheckoutData());
        
        $this->assertEquals(TextConfig::CIVILITY_M, $purchase->getInvoiceDetail()->getCivility());
        $this->assertEquals('civility_firstName', $purchase->getInvoiceDetail()->getFirstName());
        $this->assertEquals('civility_lastName', $purchase->getInvoiceDetail()->getLastName());
        $this->assertEquals('invoice_lineOne', $purchase->getInvoiceDetail()->getLineOne());
        $this->assertEquals('invoice_lineTwo', $purchase->getInvoiceDetail()->getLineTwo());
        $this->assertEquals('75000', $purchase->getInvoiceDetail()->getPostcode());
        $this->assertEquals('invoice_city', $purchase->getInvoiceDetail()->getCity());
        $this->assertEquals('invoice_country', $purchase->getInvoiceDetail()->getCountry());
    }
    public function testPersistSetCorrectPurchaseLinesValuesIntoPurchase()
    {
        $purchase = new Purchase;
        $this->validator->expects($this->exactly(3))
                        ->method('validate')
                        ->willReturn(new ConstraintViolationList([]))
                        ;
        $this->purchasePersister->persist($purchase, $this->createValidCart(), $this->createValidCheckoutData());

        $this->assertEquals(500, $purchase->getTotalPrice());

        $purchaseLine = $purchase->getPurchaseLines()->get(0);
        $this->assertEquals('publicRef', $purchaseLine->getProduct()['publicRef']);
        $this->assertEquals('privateRef', $purchaseLine->getProduct()['privateRef']);
        $this->assertEquals('produit', $purchaseLine->getProduct()['designation']);
        $this->assertEquals(100, $purchaseLine->getProduct()['price']);
        $this->assertEquals(1, $purchaseLine->getQuantity());
        $this->assertEquals(100, $purchaseLine->getTotalPrice());

        $purchaseLine = $purchase->getPurchaseLines()->get(1);
        $this->assertEquals('publicRef2', $purchaseLine->getProduct()['publicRef']);
        $this->assertEquals('privateRef2', $purchaseLine->getProduct()['privateRef']);
        $this->assertEquals('produit 2', $purchaseLine->getProduct()['designation']);
        $this->assertEquals(200, $purchaseLine->getProduct()['price']);
        $this->assertEquals(2, $purchaseLine->getQuantity());
        $this->assertEquals(400, $purchaseLine->getTotalPrice());
    }
    public function testPersistSetCorrectValuesIntoPurchase()
    {
        $purchase = new Purchase;
        $this->validator->expects($this->exactly(3))
                        ->method('validate')
                        ->willReturn(new ConstraintViolationList([]))
                        ;
        $this->purchasePersister->persist($purchase, $this->createValidCart(), $this->createValidCheckoutData());

        $this->assertNotNull($purchase->getRef());
        $this->assertInstanceOf(
            DateTimeImmutable::class,
            $purchase->getCreatedAt()
        );
        $this->assertNull($purchase->getUser());
    }
    public function testPersistSetCorrectUserIntoPurchaseIfUserIsPassed()
    {
        $user = new User;
        $purchase = new Purchase;
        $this->validator->expects($this->exactly(3))
                        ->method('validate')
                        ->willReturn(new ConstraintViolationList([]))
                        ;
        $this->purchasePersister->persist($purchase, $this->createValidCart(), $this->createValidCheckoutData(), $user);

        $this->assertEquals($user, $purchase->getUser());
    }

  

    private function createValidCart(): Cart
    {
        return (new Cart)
                ->setUser(new User)
                ->addCartLine(
                    (new CartLine)
                    ->setProduct(
                        (new Product)
                        ->setPublicRef('publicRef')
                        ->setPrivateRef('privateRef')
                        ->setDesignation('produit')
                        ->setPrice(100)
                    )
                    ->setTotalPrice(100)
                    ->setQuantity(1)
                )
                ->addCartLine(
                    (new CartLine)
                    ->setProduct(
                        (new Product)
                        ->setPublicRef('publicRef2')
                        ->setPrivateRef('privateRef2')
                        ->setDesignation('produit 2')
                        ->setPrice(200)
                    )
                    ->setTotalPrice(400)
                    ->setQuantity(2)
                )
                ->setTotalPrice(500)
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