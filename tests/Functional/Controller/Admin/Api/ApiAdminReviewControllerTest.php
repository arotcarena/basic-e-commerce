<?php
namespace App\Tests\Functional\Controller\Admin\Api;

use App\Config\SiteConfig;
use App\Repository\ReviewRepository;
use App\DataFixtures\Tests\ReviewTestFixtures;
use Symfony\Component\HttpFoundation\Response;
use App\Tests\Functional\Controller\Admin\AdminFunctionalTest;

class ApiAdminReviewControllerTest extends AdminFunctionalTest
{
    public function setUp(): void
    {
        parent::setUp();

        $this->loadFixtures([ReviewTestFixtures::class]);  //depends on UserTestFixtures, ProductTestFixtures
    }

    //auth
    public function testRedirectToLoginWhenUserNotLogged()
    {
        $review = $this->findEntity(ReviewRepository::class);
        $this->client->request('GET', $this->urlGenerator->generate('admin_api_review_updateModerationStatus', [
            'id' => $review->getId()
        ]), ['status' => SiteConfig::MODERATION_STATUS_ACCEPTED]);
        $this->assertResponseRedirects($this->urlGenerator->generate('security_login'));
    }
    public function testUserCannotAccess()
    {
        $this->loginUser();
        $review = $this->findEntity(ReviewRepository::class);
        $this->client->request('GET', $this->urlGenerator->generate('admin_api_review_updateModerationStatus', [
            'id' => $review->getId()
        ]), ['status' => SiteConfig::MODERATION_STATUS_ACCEPTED]);
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }
    public function testAdminCanAccess()
    {
        $this->loginAdmin();
        $review = $this->findEntity(ReviewRepository::class);
        $this->client->request('GET', $this->urlGenerator->generate('admin_api_review_updateModerationStatus', [
            'id' => $review->getId()
        ]), ['status' => SiteConfig::MODERATION_STATUS_ACCEPTED]);
        $this->assertResponseIsSuccessful();
    }

    //updateModerationStatus
    public function testUpdateModerationStatusWithInexistantReviewId()
    {
        $this->loginAdmin();
        $this->client->request('GET', $this->urlGenerator->generate('admin_api_review_updateModerationStatus', [
            'id' => 123456789
        ]), ['status' => SiteConfig::MODERATION_STATUS_ACCEPTED]);
        $this->assertResponseStatusCodeSame(Response::HTTP_INTERNAL_SERVER_ERROR);
        $responseContent = json_decode($this->client->getResponse()->getContent());
        $this->assertNotNull($responseContent->errors);
    }
    public function testUpdateStatusCorrectWorks()
    {
        $this->loginAdmin();
        $review = $this->findEntity(ReviewRepository::class, ['moderationStatus' => null]);
        $id = $review->getId();
        $this->client->request('GET', $this->urlGenerator->generate('admin_api_review_updateModerationStatus', [
            'id' => $id
    ]), ['status' => SiteConfig::MODERATION_STATUS_ACCEPTED]);
        $this->assertResponseIsSuccessful();
        $updatedReview = $this->findEntity(ReviewRepository::class, ['id' => $id]);
        $this->assertEquals(SiteConfig::MODERATION_STATUS_ACCEPTED, $updatedReview->getModerationStatus());
    }
    public function testUpdateModerationStatusToPendingStatus()
    {
        $this->loginAdmin();
        $review = $this->findEntity(ReviewRepository::class, ['moderationStatus' => SiteConfig::MODERATION_STATUS_ACCEPTED]);
        $id = $review->getId();
        $this->client->request('GET', $this->urlGenerator->generate('admin_api_review_updateModerationStatus', [
                'id' => $id
        ]));
        $this->assertResponseIsSuccessful();
        $updatedReview = $this->findEntity(ReviewRepository::class, ['id' => $id]);
        $this->assertEquals(null, $updatedReview->getModerationStatus());
    }
}