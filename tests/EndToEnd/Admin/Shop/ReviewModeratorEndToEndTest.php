<?php
namespace App\Tests\EndToEnd\Admin\Shop;

use App\Config\SiteConfig;
use Facebook\WebDriver\WebDriverBy;
use App\Tests\EndToEnd\Admin\AdminEndToEndTest;
use App\DataFixtures\Tests\ReviewTestFixtures;
use App\Repository\ReviewRepository;
use Facebook\WebDriver\WebDriver;

class ReviewModeratorEndToEndTest extends AdminEndToEndTest
{
    public function setUp(): void 
    {
        parent::setUp();

        $this->loadFixtures([ReviewTestFixtures::class]);  // depends on UserTestFixtures, ProductTestFixtures
        $this->loginAdmin();
    }

    public function testReviewShowContainsCorrectStatus()
    {
        $this->goToReviewShowPage(null);
        $this->client->waitFor('.admin-button', 5);
        $this->assertSelectorTextContains('.moderationStatus', SiteConfig::MODERATION_STATUS_PENDING_LABEL);

        $this->goToReviewShowPage(SiteConfig::MODERATION_STATUS_ACCEPTED);
        $this->client->waitFor('.admin-button', 5);
        $this->assertSelectorTextContains('.moderationStatus', SiteConfig::MODERATION_STATUS_ACCEPTED_LABEL);

        $this->goToReviewShowPage(SiteConfig::MODERATION_STATUS_REFUSED);
        $this->client->waitFor('.admin-button', 5);
        $this->assertSelectorTextContains('.moderationStatus', SiteConfig::MODERATION_STATUS_REFUSED_LABEL);
    }

    public function testPendingReviewShowContainsButtonsAcceptAndRefuse()
    {
        $this->goToReviewShowPage(null);
        $this->client->waitFor('.admin-button', 5);
        $this->assertSelectorTextContains('.admin-button:first-child', 'Accepter');
        $this->assertSelectorTextContains('.admin-button:last-child', 'Refuser');
    }

    public function testButtonAcceptCorrectUpdateStatus()
    {
        $id = $this->goToReviewShowPage(null);

        $this->client->waitFor('.admin-button', 5);
        $this->client->executeScript('confirm = () => true');  // il y a une demande de confirmation uniquement sur button accept
        $this->client->findElement(WebDriverBy::cssSelector('.admin-button:first-child'))->click();
        
        $this->client->waitForElementToContain('.moderationStatus', SiteConfig::MODERATION_STATUS_ACCEPTED_LABEL, 5);
        $this->assertSelectorTextContains('.moderationStatus', SiteConfig::MODERATION_STATUS_ACCEPTED_LABEL);
        
        //on rafrachit la page pour vérifier que c'est bien persisté
        $this->client->request('GET', $this->urlGenerator->generate('admin_review_show', ['id' => $id]));
        $this->assertSelectorTextContains('.moderationStatus', SiteConfig::MODERATION_STATUS_ACCEPTED_LABEL);
    }

    public function testButtonRefuseCorrectUpdateStatus()
    {
        $id = $this->goToReviewShowPage(null);

        $this->client->waitFor('.admin-button', 5);
        $this->client->findElement(WebDriverBy::cssSelector('.admin-button:last-child'))->click();
        
        $this->client->waitForElementToContain('.moderationStatus', SiteConfig::MODERATION_STATUS_REFUSED_LABEL, 5);
        $this->assertSelectorTextContains('.moderationStatus', SiteConfig::MODERATION_STATUS_REFUSED_LABEL);

        //on rafrachit la page pour vérifier que c'est bien persisté
        $this->client->request('GET', $this->urlGenerator->generate('admin_review_show', ['id' => $id]));
        $this->assertSelectorTextContains('.moderationStatus', SiteConfig::MODERATION_STATUS_REFUSED_LABEL);
    }

    public function testModeratedReviewShowContainsButtonUpdate()
    {
        $this->goToReviewShowPage(SiteConfig::MODERATION_STATUS_REFUSED);

        $this->client->waitFor('.admin-button', 5);
        $this->assertSelectorTextContains('.admin-button', 'Modifier');
    }

    public function testButtonUpdateCorrectSetStatusToPending()
    {
        $id = $this->goToReviewShowPage(SiteConfig::MODERATION_STATUS_REFUSED);

        $this->client->waitFor('.admin-button', 5);
        $this->client->findElement(WebDriverBy::cssSelector('.admin-button'))->click();

        $this->client->waitForElementToContain('.moderationStatus', SiteConfig::MODERATION_STATUS_PENDING_LABEL, 5);
        $this->assertSelectorTextContains('.moderationStatus', SiteConfig::MODERATION_STATUS_PENDING_LABEL);

        //on rafrachit la page pour vérifier que c'est bien persisté
        $this->client->request('GET', $this->urlGenerator->generate('admin_review_show', ['id' => $id]));
        $this->assertSelectorTextContains('.moderationStatus', SiteConfig::MODERATION_STATUS_PENDING_LABEL);
    }
    
    public function testClickOnUpdateButtonAcceptAndRefuseButtonsAppears()
    {
        $this->goToReviewShowPage(SiteConfig::MODERATION_STATUS_ACCEPTED);

        $this->client->waitFor('.admin-button', 5);
        $this->client->findElement(WebDriverBy::cssSelector('.admin-button'))->click();

        $this->client->waitForElementToContain('.moderationStatus', SiteConfig::MODERATION_STATUS_PENDING_LABEL, 5);
        
        $this->assertSelectorTextContains('.admin-button:first-child', 'Accepter');
        $this->assertSelectorTextContains('.admin-button:last-child', 'Refuser');
    }


    /**
     *
     * @param string|null $status
     * @return integer $id de la review
     */
    private function goToReviewShowPage(?string $status): int
    {
        if($status === null)
        {
            $status = SiteConfig::MODERATION_STATUS_PENDING; // valeur utilisée par ReviewFilter a la place de null
        }
        $this->client->request('GET', $this->urlGenerator->generate('admin_review_index', ['moderationStatus' => $status]));
        $id = $this->client->findElement(WebDriverBy::cssSelector('tbody tr:first-child'))->getAttribute('data-id');
        $this->client->request('GET', $this->urlGenerator->generate('admin_review_show', ['id' => $id]));
        return $id;
    }
    
}