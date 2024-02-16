<?php
namespace App\Tests\EndToEnd\Admin\Customer;

use App\Config\SiteConfig;
use Facebook\WebDriver\WebDriverBy;
use App\DataFixtures\Tests\UserTestFixtures;
use App\Tests\EndToEnd\Admin\AdminEndToEndTest;
use App\DataFixtures\Tests\PurchaseTestFixtures;

class PurchaseStatusUpdateEndToEndTest extends AdminEndToEndTest
{
    public function setUp(): void 
    {
        parent::setUp();

        $this->loadFixtures([UserTestFixtures::class, PurchaseTestFixtures::class]);
        $this->loginAdmin();
    }

    public function testContainsUpdateButton()
    {
        $this->goToShowPage();
        $this->client->waitFor('#purchase-status-updater .admin-button', 5);
        $this->assertSelectorTextContains('#purchase-status-updater .admin-button', 'Modifier');
    }

    public function testOnUpdateButtonClickSelectAppears()
    {
        $this->goToShowPage();
        $this->client->waitFor('#purchase-status-updater .admin-button', 5);
        $this->client->findElement(WebDriverBy::cssSelector('#purchase-status-updater .admin-button'))->click();
        $this->assertSelectorExists('select[name=status]');
    }

    public function testSelectContainsCorrectChoices()
    {
        $this->goToShowPage();
        $this->client->waitFor('#purchase-status-updater .admin-button', 5);
        $this->client->findElement(WebDriverBy::cssSelector('#purchase-status-updater .admin-button'))->click();
        
        $i = 0;
        foreach ([SiteConfig::STATUS_PENDING, SiteConfig::STATUS_PAID, SiteConfig::STATUS_SENT, SiteConfig::STATUS_DELIVERED, SiteConfig::STATUS_CANCELED] as $choice)
        {
            $i++;
            $option = $this->client->findElement(WebDriverBy::cssSelector('select[name=status] option:nth-child('.$i.')'));
            $this->assertEquals($choice, $option->getAttribute('value'));
            $this->assertEquals(SiteConfig::STATUS_LABELS[$choice], $option->getText());
        }
    }

    public function testSelectSameAsCurrentHasNoEffect()
    {
        $this->goToShowPage();
        $this->client->waitFor('#purchase-status-updater .admin-button', 5);
        $currentStatusLabel = $this->client->findElement(WebDriverBy::cssSelector('.status'))->getText();
        // on clique sur modifier
        $this->client->findElement(WebDriverBy::cssSelector('#purchase-status-updater .admin-button'))->click();
        // on clique sur le select
        $this->client->findElement(WebDriverBy::cssSelector('select[name=status]'))->click();
        //puis sur l'option déjà sélectionnée
        for ($i=1; $i <= 5; $i++) { 
            $option = $this->client->findElement(WebDriverBy::cssSelector('select[name=status] option:nth-child('.$i.')'))->click();
            if($option->getText() === $currentStatusLabel)
            {
                $option->click();
                break;
            }
        }
        $this->assertSelectorTextNotContains('.admin-button:first-child', 'Valider');
    }

    public function testOnSelectValidateButtonAppears()
    {
        // le status actuel est paid
        $this->goToShowPage(['status' => SiteConfig::STATUS_PAID]);
        $this->client->waitFor('#purchase-status-updater .admin-button', 5);
        // on clique sur modifier
        $this->client->findElement(WebDriverBy::cssSelector('#purchase-status-updater .admin-button'))->click();
        // on clique sur le select
        $this->client->findElement(WebDriverBy::cssSelector('select[name=status]'))->click();
        //puis sur l'option sent
        $optionSent = $this->client->findElement(WebDriverBy::cssSelector('select[name=status] option:nth-child(3)'));
        $this->assertEquals(SiteConfig::STATUS_SENT, $optionSent->getAttribute('value'));
        $optionSent->click();
        $this->assertSelectorTextContains('.admin-button:first-child', 'Valider');
    }

    public function testSelectPendingIsNotPermitted()
    {
        $this->goToShowPage(['status' => SiteConfig::STATUS_SENT]);
        $this->client->waitFor('#purchase-status-updater .admin-button', 5);
        // on clique sur modifier
        $this->client->findElement(WebDriverBy::cssSelector('#purchase-status-updater .admin-button'))->click();
        // on clique sur le select
        $this->client->findElement(WebDriverBy::cssSelector('select[name=status]'))->click();
        //puis sur l'option pending
        $optionPending = $this->client->findElement(WebDriverBy::cssSelector('select[name=status] option:first-child'));
        $this->assertEquals(SiteConfig::STATUS_PENDING, $optionPending->getAttribute('value'));
        $optionPending->click();
        //on valide
        $this->client->executeScript('confirm = () => true');
        $this->client->findElement(WebDriverBy::cssSelector('.admin-button:first-child'))->click();
        //on attend l'affichage de l'erreur
        $this->client->waitFor('.admin-form-error');
        $this->assertSelectorExists('.admin-form-error');
        $this->assertSelectorNotExists('select[name=status]');
    }

    public function testUpdateStatusWork()
    {
        //le status actuel est sent
        $this->goToShowPage(['status' => SiteConfig::STATUS_SENT]);
        $this->client->waitFor('#purchase-status-updater .admin-button', 5);
        // on clique sur modifier
        $this->client->findElement(WebDriverBy::cssSelector('#purchase-status-updater .admin-button'))->click();
        // on clique sur le select
        $this->client->findElement(WebDriverBy::cssSelector('select[name=status]'))->click();
        //puis sur l'option delivered
        $optionDelivered = $this->client->findElement(WebDriverBy::cssSelector('select[name=status] option:nth-child(4)'));
        $this->assertEquals(SiteConfig::STATUS_DELIVERED, $optionDelivered->getAttribute('value'));
        $optionDelivered->click();
        //on valide
        $this->client->executeScript('confirm = () => true');
        $this->client->findElement(WebDriverBy::cssSelector('.admin-button:first-child'))->click();
        //on attend la fin du chargement et on vérifie que le changement est bien pris en compte
        $this->client->waitFor('.status', 5);
        $this->assertSelectorTextContains('.status', SiteConfig::STATUS_LABELS[SiteConfig::STATUS_DELIVERED]);
        //on actualise la page et on vérifie que c'est bien persisté
        $this->client->request('GET', $this->client->getCurrentURL());
        $this->assertSelectorTextContains('.status', SiteConfig::STATUS_LABELS[SiteConfig::STATUS_DELIVERED]);
    }


    private function goToShowPage($searchParams = []): void 
    {
        $this->client->request('GET', $this->urlGenerator->generate('admin_purchase_index', $searchParams));
        $this->client->findElement(WebDriverBy::cssSelector('tbody tr:first-child td:last-child a'))->click();
    }
}