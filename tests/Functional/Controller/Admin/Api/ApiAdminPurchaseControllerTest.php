<?php
namespace App\Tests\Functional\Controller\Admin\Api;

use App\Config\SiteConfig;
use App\Repository\PurchaseRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Email\Customer\PurchaseStatusEmail;
use App\DataFixtures\Tests\UserTestFixtures;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\DataFixtures\Tests\PurchaseTestFixtures;
use App\Controller\Admin\Api\ApiAdminPurchaseController;
use App\Tests\Functional\Controller\Admin\AdminFunctionalTest;
use Exception;

/**
 * @group FunctionalAdmin
 * @group FunctionalAdminApi
 */
class ApiAdminPurchaseControllerTest extends AdminFunctionalTest
{
    public function setUp(): void 
    {
        parent::setUp();

        $this->loadFixtures([PurchaseTestFixtures::class, UserTestFixtures::class]);  //userFixtures obligatoire pour auth
    }

    //auth
    public function testRedirectToLoginWhenUserNotLogged()
    {
        $purchase = $this->findEntity(PurchaseRepository::class);
        $this->client->request('POST', $this->urlGenerator->generate('admin_api_purchase_updateStatus', [
            'id' => $purchase->getId()
        ]), [], [], ['CONTENT_TYPE' => 'application/json'], json_encode(SiteConfig::STATUS_DELIVERED));
        $this->assertResponseRedirects($this->urlGenerator->generate('security_login'));
    }
    public function testUserCannotAccess()
    {
        $this->loginUser();
        $purchase = $this->findEntity(PurchaseRepository::class);
        $this->client->request('POST', $this->urlGenerator->generate('admin_api_purchase_updateStatus', [
            'id' => $purchase->getId()
        ]), [], [], ['CONTENT_TYPE' => 'application/json'], json_encode(SiteConfig::STATUS_DELIVERED));
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }
    public function testAdminCanAccess()
    {
        $this->loginAdmin();
        $purchase = $this->findEntity(PurchaseRepository::class);
        $this->client->request('POST', $this->urlGenerator->generate('admin_api_purchase_updateStatus', [
            'id' => $purchase->getId()
        ]), [], [], ['CONTENT_TYPE' => 'application/json'], json_encode(SiteConfig::STATUS_DELIVERED));
        $this->assertResponseIsSuccessful();
    }

    //updateStatus
    public function testUpdateStatusWithMethodGETFail()
    {
        $this->loginAdmin();
        $purchase = $this->findEntity(PurchaseRepository::class);
        $this->client->request('GET', $this->urlGenerator->generate('admin_api_purchase_updateStatus', [
            'id' => $purchase->getId()
        ]));
        $this->assertResponseStatusCodeSame(Response::HTTP_METHOD_NOT_ALLOWED);
    }
    public function testUpdateStatusWithInexistantPurchaseId()
    {
        $this->loginAdmin();
        $this->client->request('POST', $this->urlGenerator->generate('admin_api_purchase_updateStatus', [
            'id' => 123456789
        ]));
        $this->assertResponseStatusCodeSame(Response::HTTP_INTERNAL_SERVER_ERROR);
        $responseContent = json_decode($this->client->getResponse()->getContent());
        $this->assertNotNull($responseContent->errors);
    }
    public function testUpdateStatusWithNoDataSent()
    {
        $this->loginAdmin();
        $purchase = $this->findEntity(PurchaseRepository::class);
        $this->client->request('POST', $this->urlGenerator->generate('admin_api_purchase_updateStatus', [
            'id' => $purchase->getId()
        ]));
        $this->assertResponseStatusCodeSame(Response::HTTP_INTERNAL_SERVER_ERROR);
        $responseContent = json_decode($this->client->getResponse()->getContent());
        $this->assertNotNull($responseContent->errors);
    }
    public function testUpdateStatusWithIncorrectDataSent()
    {
        $this->loginAdmin();
        $purchase = $this->findEntity(PurchaseRepository::class);
        $this->client->request('POST', $this->urlGenerator->generate('admin_api_purchase_updateStatus', [
            'id' => $purchase->getId()
        ]), [], [], ['CONTENT_TYPE' => 'application/json'], json_encode('incorrect_data'));
        $this->assertResponseStatusCodeSame(Response::HTTP_INTERNAL_SERVER_ERROR);
        $responseContent = json_decode($this->client->getResponse()->getContent());
        $this->assertNotNull($responseContent->errors);
    }
    public function testUpdateStatusRestrictUpdatingToPending()
    {
        $this->loginAdmin();
        $purchase = $this->findEntity(PurchaseRepository::class, ['status' => SiteConfig::STATUS_PAID]);
        $this->client->request('POST', $this->urlGenerator->generate('admin_api_purchase_updateStatus', [
            'id' => $purchase->getId()
        ]), [], [], ['CONTENT_TYPE' => 'application/json'], json_encode(SiteConfig::STATUS_PENDING));

        $this->assertResponseStatusCodeSame(Response::HTTP_INTERNAL_SERVER_ERROR);
        $responseContent = json_decode($this->client->getResponse()->getContent());
        $this->assertNotNull($responseContent->errors);
    }
    public function testUpdateStatusCorrectWorks()
    {
        $this->loginAdmin();
        $purchase = $this->findEntity(PurchaseRepository::class, ['status' => SiteConfig::STATUS_PENDING]);
        $id = $purchase->getId();
        $this->client->request('POST', $this->urlGenerator->generate('admin_api_purchase_updateStatus', [
            'id' => $id
        ]), [], [], ['CONTENT_TYPE' => 'application/json'], json_encode(SiteConfig::STATUS_SENT));
        $this->assertResponseIsSuccessful();
        $updatedPurchase = $this->findEntity(PurchaseRepository::class, ['id' => $id]);
        $this->assertEquals(SiteConfig::STATUS_SENT, $updatedPurchase->getStatus());
    }

    public function testUpdateStatusDontUpdateWhenStatusEmailError()
    {
        $purchaseStatusEmail = $this->createMock(PurchaseStatusEmail::class);
        $em = $this->createMock(EntityManagerInterface::class);
        $purchaseRepository = $this->createMock(PurchaseRepository::class);

        $newStatus = SiteConfig::STATUS_DELIVERED;
        $purchase = $this->findEntity(PurchaseRepository::class, ['status' => SiteConfig::STATUS_SENT]);

        $apiAdminPurchaseController = new ApiAdminPurchaseController($em, $purchaseRepository, $purchaseStatusEmail);
        
        /** @var MockObject $purchaseRepository */
        $purchaseRepository->expects($this->once())
                            ->method('find')
                            ->with($purchase->getId())
                            ->willReturn($purchase);
        /** @var MockObject $purchaseStatusEmail */
        $purchaseStatusEmail->expects($this->once())
                            ->method('send')
                            ->with($purchase, $newStatus)
                            ->willThrowException(new Exception());
        /** @var MockObject $em */
        $em->expects($this->never())
            ->method('flush')
            ;
        $apiAdminPurchaseController->updateStatus($purchase->getId(), new Request([], [], [], [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($newStatus)));
    }
    public function testUpdateStatusDontUpdateAndDontSendEmailWhenNewStatusIsSameAsOldStatus()
    {
        $purchaseStatusEmail = $this->createMock(PurchaseStatusEmail::class);
        $em = $this->createMock(EntityManagerInterface::class);
        $purchaseRepository = $this->createMock(PurchaseRepository::class);

        $newStatus = SiteConfig::STATUS_DELIVERED;
        $purchase = $this->findEntity(PurchaseRepository::class, ['status' => SiteConfig::STATUS_DELIVERED]);

        $apiAdminPurchaseController = new ApiAdminPurchaseController($em, $purchaseRepository, $purchaseStatusEmail);
        
        /** @var MockObject $purchaseRepository */
        $purchaseRepository->expects($this->once())
                            ->method('find')
                            ->with($purchase->getId())
                            ->willReturn($purchase);
        /** @var MockObject $purchaseStatusEmail */
        $purchaseStatusEmail->expects($this->never())
                            ->method('send');
        /** @var MockObject $em */
        $em->expects($this->never())
            ->method('flush');
        $apiAdminPurchaseController->updateStatus($purchase->getId(), new Request([], [], [], [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($newStatus)));
    }
    public function testUpdateStatusSendStatusEmail()
    {
        $purchaseStatusEmail = $this->createMock(PurchaseStatusEmail::class);
        $em = $this->createMock(EntityManagerInterface::class);
        $purchaseRepository = $this->createMock(PurchaseRepository::class);

        $newStatus = SiteConfig::STATUS_DELIVERED;
        $purchase = $this->findEntity(PurchaseRepository::class, ['status' => SiteConfig::STATUS_SENT]);

        $apiAdminPurchaseController = new ApiAdminPurchaseController($em, $purchaseRepository, $purchaseStatusEmail);
        
        /** @var MockObject $purchaseRepository */
        $purchaseRepository->expects($this->once())
                            ->method('find')
                            ->with($purchase->getId())
                            ->willReturn($purchase);
        /** @var MockObject $purchaseStatusEmail */
        $purchaseStatusEmail->expects($this->once())
                            ->method('send')
                            ->with($purchase, $newStatus);
        $apiAdminPurchaseController->updateStatus($purchase->getId(), new Request([], [], [], [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($newStatus)));
    }
    
}