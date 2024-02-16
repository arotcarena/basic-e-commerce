<?php
namespace App\Tests\Functional\Controller\Admin;

use App\DataFixtures\Tests\ProductTestFixtures;
use App\DataFixtures\Tests\PurchaseTestFixtures;
use App\DataFixtures\Tests\UserTestFixtures;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use App\Repository\PurchaseRepository;
use App\Repository\ReviewRepository;
use App\Repository\SubCategoryRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;
use App\Tests\Functional\Controller\Admin\AdminFunctionalTest;




/**
 * @group FunctionalAdmin
 * @group FunctionalAdminHome
 */
class AdminHomeControllerTest extends AdminFunctionalTest
{
    public function setUp(): void 
    {
        parent::setUp();

        $this->loadFixtures([UserTestFixtures::class, ProductTestFixtures::class, PurchaseTestFixtures::class]);
    }


    public function testNotLoggedUserIsRedirectedToLogin()
    {
        $this->client->request('GET', $this->urlGenerator->generate('admin_home'));
        $this->assertResponseRedirects($this->urlGenerator->generate('security_login'));
    }
    public function testUserCannotAccess()
    {
        $this->loginUser();
        $this->client->request('GET', $this->urlGenerator->generate('admin_home'));
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }
    public function testAdminCanAccess()
    {
        $this->loginAdmin();
        $this->client->request('GET', $this->urlGenerator->generate('admin_home'));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Administration');
    }

    public function testContainsLinkToProductManagement()
    {
        $this->loginAdmin();
        $crawler = $this->client->request('GET', $this->urlGenerator->generate('admin_home'));
        $this->assertSelectorTextContains('.admin-block:first-child a:first-child', 'Gérer les produits');
        $this->assertEquals(
            $this->urlGenerator->generate('admin_product_index'),
            $crawler->filter('.admin-block:first-child a:first-child')->attr('href')
        );
    }
    public function testContainsLinkToCategoryManagement()
    {
        $this->loginAdmin();
        $crawler = $this->client->request('GET', $this->urlGenerator->generate('admin_home'));
        $this->assertSelectorTextContains('.admin-block:first-child a:nth-child(2)', 'Gérer les catégories');
        $this->assertEquals(
            $this->urlGenerator->generate('admin_category_index'),
            $crawler->filter('.admin-block:first-child a:nth-child(2)')->attr('href')
        );
    }
    public function testContainsLinkToReviewManagement()
    {
        $this->loginAdmin();
        $crawler = $this->client->request('GET', $this->urlGenerator->generate('admin_home'));
        $this->assertSelectorTextContains('.admin-block:first-child a:nth-child(3)', 'Modérer les avis');
        $this->assertEquals(
            $this->urlGenerator->generate('admin_review_index'),
            $crawler->filter('.admin-block:first-child a:nth-child(3)')->attr('href')
        );
    }
    public function testContainsProductCount()
    {
        $this->loginAdmin();
        $this->client->request('GET', $this->urlGenerator->generate('admin_home'));
        $this->assertSelectorTextContains('.admin-block-text:first-child', 'produits');
        $count = $this->client->getContainer()->get(ProductRepository::class)->count([]);
        $this->assertSelectorTextContains('.admin-block-text:first-child', $count);

    }
    public function testContainsProductWithNoStockCount()
    {
        $this->loginAdmin();
        $this->client->request('GET', $this->urlGenerator->generate('admin_home'));
        $this->assertSelectorTextContains('.admin-block-text.text-small', 'en rupture de stock');
        $count = $this->client->getContainer()->get(ProductRepository::class)->count(['stock' => 0]);
        $this->assertSelectorTextContains('.admin-block-text.text-small', $count);
    }
    public function testContainsCategoryCount()
    {
        $this->loginAdmin();
        $this->client->request('GET', $this->urlGenerator->generate('admin_home'));
        $this->assertSelectorTextContains('.admin-block-body .admin-block-text:nth-child(3)', 'catégories');
        $count = $this->client->getContainer()->get(CategoryRepository::class)->count([]);
        $this->assertSelectorTextContains('.admin-block-body .admin-block-text:nth-child(3)', $count);
    }
    public function testContainsSubCategoryCount()
    {
        $this->loginAdmin();
        $this->client->request('GET', $this->urlGenerator->generate('admin_home'));
        $this->assertSelectorTextContains('.admin-block-body .admin-block-text:nth-child(4)', 'sous-catégories');
        $count = $this->client->getContainer()->get(SubCategoryRepository::class)->count([]);
        $this->assertSelectorTextContains('.admin-block-body .admin-block-text:nth-child(4)', $count);
    }
    public function testContainsReviewsPendingCount()
    {
        $this->loginAdmin();
        $this->client->request('GET', $this->urlGenerator->generate('admin_home'));
        $this->assertSelectorTextContains('.admin-block-body .admin-block-text:nth-child(5)', 'avis à modérer');
        $count = $this->client->getContainer()->get(ReviewRepository::class)->count(['moderationStatus' => null]);
        $this->assertSelectorTextContains('.admin-block-body .admin-block-text:nth-child(5)', $count);
    }

    public function testContainsLinkToPurchaseManagement()
    {
        $this->loginAdmin();
        $crawler = $this->client->request('GET', $this->urlGenerator->generate('admin_home'));
        $this->assertSelectorTextContains('.admin-block:nth-child(2) a', 'Gérer les commandes');
        $this->assertEquals(
            $this->urlGenerator->generate('admin_purchase_index'),
            $crawler->filter('.admin-block:nth-child(2) a')->attr('href')
        );
    }

    public function testContainsPurchasesInProcessCount()
    {
        $this->loginAdmin();
        $this->client->request('GET', $this->urlGenerator->generate('admin_home'));
        $this->assertSelectorTextContains('.purchases-in-process', 'Commande');
        $count = $this->client->getContainer()->get(PurchaseRepository::class)->countPurchasesInProcess();
        $this->assertSelectorTextContains('.purchases-in-process', $count);
    }

    public function testContainsUserCount()
    {
        $this->loginAdmin();
        $this->client->request('GET', $this->urlGenerator->generate('admin_home'));
        $this->assertSelectorTextContains('.user-count', 'Utilisateur');
        $count = $this->client->getContainer()->get(UserRepository::class)->count([]);
        $this->assertSelectorTextContains('.user-count', $count - 1); // - 1 pour décompter l'admin
    }

}