<?php
namespace App\Tests\Functional\Controller\Admin\Customer;

use App\Entity\Purchase;
use App\Config\SiteConfig;
use App\Repository\PurchaseRepository;
use Symfony\Component\DomCrawler\Crawler;
use App\DataFixtures\Tests\UserTestFixtures;
use Symfony\Component\HttpFoundation\Response;
use App\DataFixtures\Tests\PurchaseTestFixtures;
use App\Tests\Functional\Controller\Admin\AdminFunctionalTest;
use App\Twig\Runtime\PriceFormaterExtensionRuntime;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @group FunctionalAdmin
 * @group FunctionalAdminCustomer
 */
class AdminPurchaseControllerTest extends AdminFunctionalTest
{
    private PurchaseRepository $purchaseRepository;

    private PriceFormaterExtensionRuntime $priceFormater;

    private EntityManagerInterface $em;

    public function setUp(): void 
    {
        parent::setUp();

        $this->loadFixtures([PurchaseTestFixtures::class, UserTestFixtures::class]);  // userTestFixtures pour loginUser et loginAdmin

        $this->purchaseRepository = $this->client->getContainer()->get(PurchaseRepository::class);

        $this->priceFormater = $this->client->getContainer()->get(PriceFormaterExtensionRuntime::class);

        $this->em = $this->client->getContainer()->get(EntityManagerInterface::class);
    }


     // auth
     public function testRedirectToLoginWhenUserNotLogged()
     {
         $id = $this->findEntity(PurchaseRepository::class)->getId();
 
         $this->client->request('GET', $this->urlGenerator->generate('admin_purchase_index'));
         $this->assertResponseRedirects($this->urlGenerator->generate('security_login'));
         
         $this->client->request('GET', $this->urlGenerator->generate('admin_purchase_show', ['id' => $id]));
         $this->assertResponseRedirects($this->urlGenerator->generate('security_login'));
     }
     public function testUserCannotAccess()
     {
         $this->loginUser();
         $id = $this->findEntity(PurchaseRepository::class)->getId();
 
         $this->client->request('GET', $this->urlGenerator->generate('admin_purchase_index'));
         $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
         
         $this->client->request('GET', $this->urlGenerator->generate('admin_purchase_show', ['id' => $id]));
         $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
     }
     public function testAdminCanAccess()
     {
         $this->loginAdmin();
         $id = $this->findEntity(PurchaseRepository::class)->getId();
 
         $this->client->request('GET', $this->urlGenerator->generate('admin_purchase_index'));
         $this->assertResponseIsSuccessful();
 
         $this->client->request('GET', $this->urlGenerator->generate('admin_purchase_show', ['id' => $id]));
         $this->assertResponseIsSuccessful();
     }


    //index
    public function testIndexRender()
    {
        $this->loginAdmin();
        $this->client->request('GET', $this->urlGenerator->generate('admin_purchase_index'));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Commandes');
    }
    public function testIndexBreadcrumb()
    {
        $this->loginAdmin();
        $crawler = $this->client->request('GET', $this->urlGenerator->generate('admin_purchase_index'));
        $this->assertBreadcrumbHomeLink($crawler);
        $this->assertBreadcrumbIndexLink($crawler);
    }
    public function testIndexContainsCorrectShowButton()
    {
        $this->loginAdmin();
        $crawler = $this->client->request('GET', $this->urlGenerator->generate('admin_purchase_index'));
        //on récupère l'id de la purchase de la 1ere ligne
        $purchaseRef = $crawler->filter('tbody tr:first-child td.ref')->text();
        $id = $this->findEntity(PurchaseRepository::class, ['ref' => $purchaseRef])->getId();
        //on vérifie la présence du bouton show et son href
        $showButton = $crawler->filter('tbody tr:first-child td.controls .admin-table-button');
        $this->assertStringContainsString('Voir la commande', $showButton->attr('title'));
        $this->assertEquals(
            $this->urlGenerator->generate('admin_purchase_show', ['id' => $id]),
            $showButton->attr('href')
        );
    }
    public function testIndexCorrectCountWithoutFilters()
    {
        $this->loginAdmin();
        $this->client->request('GET', $this->urlGenerator->generate('admin_purchase_index'));
        $inprocessCount = $this->purchaseRepository->countPurchasesInProcess();
        $totalCount = $this->purchaseRepository->count([]);
        $this->assertSelectorTextContains('.breadcrumb-link', $inprocessCount);
        $this->assertSelectorTextContains('.admin-count', $totalCount);
    }
    public function testIndexCorrectCountWithFilters()
    {
        $this->loginAdmin();
        $this->client->request('GET', $this->urlGenerator->generate('admin_purchase_index'), [
            'status' => SiteConfig::STATUS_PAID
        ]);
        $inprocessCount = $this->purchaseRepository->countPurchasesInProcess();
        $filteredCount = $this->purchaseRepository->count(['status' => SiteConfig::STATUS_PAID]);
        $this->assertSelectorTextContains('.breadcrumb-link', $inprocessCount);
        $this->assertSelectorTextContains('.admin-count', $filteredCount);
    }
    /**
     * Il y a 20 purchases dans les fixtures, donc pour contrôler le total affiché on est pas géné par la pagination qui est de 20 également
     */
    public function testIndexCorrectFilters()
    {
        $this->loginAdmin();
        $crawler = $this->client->request('GET', $this->urlGenerator->generate('admin_purchase_index'), [
            'status' => SiteConfig::STATUS_SENT
        ]);
        $count1 = $crawler->filter('tbody tr')->count();
        $this->assertEquals(
            $this->purchaseRepository->count(['status' => SiteConfig::STATUS_SENT]),
            $count1
        );
        if($count1 > 5) 
        {
            $count1 = 5;  // pour éviter que le test soit trop long (on se contente de vérifier un maximum de 5 lignes)
        }
        for ($i=1; $i <= $count1; $i++) 
        { 
            //on vérifie le label du status
            $this->assertSelectorTextContains('tbody tr:nth-child('.$i.') .status', SiteConfig::STATUS_LABELS[SiteConfig::STATUS_SENT]);
            //et le status lui-même
            $this->assertEquals(
                $crawler->filter('tbody tr:nth-child('.$i.') .status')->attr('value'),
                SiteConfig::STATUS_SENT
            );
        }

        //on recommence avec un autre status
        $crawler = $this->client->request('GET', $this->urlGenerator->generate('admin_purchase_index'), [
            'status' => SiteConfig::STATUS_DELIVERED
        ]);
        $count2 = $crawler->filter('tbody tr')->count();
        $this->assertEquals(
            $this->purchaseRepository->count(['status' => SiteConfig::STATUS_DELIVERED]),
            $count2
        );
        if($count2 > 5) 
        {
            $count2 = 5;  // pour éviter que le test soit trop long (on se contente de vérifier un maximum de 5 lignes)
        }
        for ($i=1; $i <= $count2; $i++) 
        { 
            //on vérifie le label du status
            $this->assertSelectorTextContains('tbody tr:nth-child('.$i.') .status', SiteConfig::STATUS_LABELS[SiteConfig::STATUS_DELIVERED]);
            //et le status lui-même
            $this->assertEquals(
                $crawler->filter('tbody tr:nth-child('.$i.') .status')->attr('value'),
                SiteConfig::STATUS_DELIVERED
            );
        }
        //on vérifie qu'au moins un élément a pu être testé (sinon le test n\'est pas probant)
        if(($count1 + $count2) === 0)
        {
            $this->fail('Aucun élément testé, il faudrait créer plus de Purchases pour que le test soit probant');
        }
    }
    public function testIndexIsSortByCreatedAtDescByDefault()
    {
        $this->loginAdmin();
        $crawler = $this->client->request('GET', $this->urlGenerator->generate('admin_purchase_index'));
        
        $previousTimestamp = time();
        for ($i=1; $i <= 7; $i++) { 
            $lineTimestamp = $crawler->filter('tbody tr:nth-child('.$i.') .createdAt')->attr('value');  // on a placé dans value createdAt.timestamp
            $this->assertTrue($lineTimestamp <= $previousTimestamp);
            $previousTimestamp = $lineTimestamp;    
        }
    }
    public function testIndexCorrectSort()
    {
        $this->loginAdmin();
        $crawler = $this->client->request('GET', $this->urlGenerator->generate('admin_purchase_index'), [
            'sortBy' => 'createdAt_ASC'
        ]);
        $previousTimestamp = 0;
        for ($i=1; $i <= 7; $i++) { 
            $lineTimestamp = $crawler->filter('tbody tr:nth-child('.$i.') .createdAt')->attr('value');
            $this->assertTrue($lineTimestamp > $previousTimestamp);
            $previousTimestamp = $lineTimestamp;    
        }
    }
    public function testIndexTableLineClassDependsOnStatus()
    {
        $this->loginAdmin();
        $crawler = $this->client->request('GET', $this->urlGenerator->generate('admin_purchase_index'));
        for ($i=10; $i <= 20; $i++) { 
            $lineStatus = $crawler->filter('tbody tr:nth-child('.$i.') .status')->attr('value');
            $lineClass = $crawler->filter('tbody tr:nth-child('.$i.')')->attr('class');
            if($lineStatus === SiteConfig::STATUS_DELIVERED || $lineStatus === SiteConfig::STATUS_CANCELED)
            {
                $this->assertTrue($lineClass === null || !str_contains($lineClass, 'strong'));
                $verified1 = true;
            }
            else
            {
                $this->assertStringContainsString('strong', $lineClass);
                $verified2 = true;
            }
        }
        if(!isset($verified1) || !isset($verified2))
        {
            $message = !isset($verified1) ? 'Il n\'y a aucune purchase avec un status terminé (delivered ou canceled)': 'Il n\'y a aucune purchase avec un status en cours (autre que delivered ou canceled)';
            $this->fail('Le test n\'est pas probant. '.$message);
        }
    }
    public function testIndexSearchFiltersArePresent()
    {
        $this->loginAdmin();
        $this->client->request('GET', $this->urlGenerator->generate('admin_purchase_index'));
        $this->assertSelectorExists('[name=status]');
        $this->assertSelectorExists('[name=sortBy]');
    }
    public function testIndexSearchFiltersContainsCorrectStatusChoices()
    {
        $this->loginAdmin();
        $crawler = $this->client->request('GET', $this->urlGenerator->generate('admin_purchase_index'));

        $this->assertSelectContainsChoices(
            [
                '' => '',
                SiteConfig::STATUS_LABELS[SiteConfig::STATUS_PENDING] => SiteConfig::STATUS_PENDING,
                SiteConfig::STATUS_LABELS[SiteConfig::STATUS_PAID] => SiteConfig::STATUS_PAID,
                SiteConfig::STATUS_LABELS[SiteConfig::STATUS_SENT] => SiteConfig::STATUS_SENT,
                SiteConfig::STATUS_LABELS[SiteConfig::STATUS_DELIVERED] => SiteConfig::STATUS_DELIVERED,
                SiteConfig::STATUS_LABELS[SiteConfig::STATUS_CANCELED] => SiteConfig::STATUS_CANCELED
            ],
            'status',
            $crawler
        );
    }
    public function testIndexSearchFiltersContainsCorrectSortChoices()
    {
        $this->loginAdmin();
        $crawler = $this->client->request('GET', $this->urlGenerator->generate('admin_purchase_index'));

        $this->assertSelectContainsChoices(
            [
                '' => '',
                'Plus récentes d\'abord' => 'createdAt_DESC',
                'Plus anciennes d\'abord' => 'createdAt_ASC'
            ],
            'sortBy',
            $crawler
        );
    }
    public function testIndexWithPurchaseHavingNoUser()
    {
        $this->loginAdmin();
        $crawler = $this->client->request('GET', $this->urlGenerator->generate('admin_purchase_index'));
        //on récupère la 1ère purchase et on supprime le User associé, sauf s'il s'agit de l'admin, auquel cas il faut prendre la purchase suivante
        $line = 1;
        $purchaseRef = $crawler->filter('tbody tr:nth-child('.$line.') td.ref')->text();
        $purchase = $this->findEntity(PurchaseRepository::class, ['ref' => $purchaseRef]);
        if(in_array('ROLE_ADMIN', $purchase->getUser()->getRoles()))
        {
            $line = 2;
            $purchaseRef = $crawler->filter('tbody tr:nth-child('.$line.') td.ref')->text();
            $purchase = $this->findEntity(PurchaseRepository::class, ['ref' => $purchaseRef]);
        }
        $this->assertNotNull($purchase->getUser(), 'le test est faussé, cette purchase n\'a pas de User associé');
        $this->em->remove($purchase->getUser());
        $this->em->flush();

        $crawler = $this->client->request('GET', $this->urlGenerator->generate('admin_purchase_index'));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('tbody tr:nth-child('.$line.') td.user', 'Anonyme');
    }

    //show
    public function testShowWithInexistantIdParam()
    {
        $this->loginAdmin();
        $this->client->request('GET', $this->urlGenerator->generate('admin_purchase_show', [
            'id' => '12345678944561256'
        ]));
        $this->assertResponseStatusCodeSame(Response::HTTP_INTERNAL_SERVER_ERROR);
    }
    public function testShowRender()
    {
        $this->loginAdmin();
        $purchase = $this->findEntity(PurchaseRepository::class);
        $this->client->request('GET', $this->urlGenerator->generate('admin_purchase_show', ['id' => $purchase->getId()]));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Commande n°'.$purchase->getRef());
    }
    public function testShowBreadcrumb()
    {
        $this->loginAdmin();
        $purchase = $this->findEntity(PurchaseRepository::class);
        $crawler = $this->client->request('GET', $this->urlGenerator->generate('admin_purchase_show', ['id' => $purchase->getId()]));
        $this->assertBreadcrumbHomeLink($crawler);
        $this->assertBreadcrumbIndexLink($crawler);
        $this->assertSelectorTextContains('.breadcrumb-item', 'Commande n°'.$purchase->getRef());
    }
    public function testShowContainsCorrectSections()
    {
        $this->loginAdmin();
        /** @var Purchase */
        $purchase = $this->findEntity(PurchaseRepository::class);
        
        $this->client->request('GET', $this->urlGenerator->generate('admin_purchase_show', ['id' => $purchase->getId()]));

        $this->assertSelectorTextContains('.ref', $purchase->getRef());

        $user = $purchase->getUser();
        $this->assertSelectorTextContains('.userDetails', $user->getCivility().' '.$user->getFirstName().' '.$user->getLastName());
        $this->assertSelectorTextContains('.email', $user->getEmail());

        $deliveryDetail = $purchase->getDeliveryDetail();
        $this->assertSelectorTextContains('.deliveryDetail p:first-child', $deliveryDetail->getCivility().' '.$deliveryDetail->getFirstName().' '.$deliveryDetail->getLastName());
        $this->assertSelectorTextContains('.deliveryDetail p:nth-child(2)', $deliveryDetail->getLineOne());
        if($deliveryDetail->getLineTwo())
        {
            $this->assertSelectorTextContains('.deliveryDetail p:nth-child(3)', $deliveryDetail->getLineTwo());
        }
        $this->assertSelectorTextContains('.deliveryDetail p:nth-child(4)', $deliveryDetail->getPostcode().' '.$deliveryDetail->getCity());
        $this->assertSelectorTextContains('.deliveryDetail p:nth-child(5)', $deliveryDetail->getCountry());
        
        $invoiceDetail = $purchase->getInvoiceDetail();
        $this->assertSelectorTextContains('.invoiceDetail p:first-child', $invoiceDetail->getCivility().' '.$invoiceDetail->getFirstName().' '.$invoiceDetail->getLastName());
        $this->assertSelectorTextContains('.invoiceDetail p:nth-child(2)', $invoiceDetail->getLineOne());
        if($invoiceDetail->getLineTwo())
        {
            $this->assertSelectorTextContains('.invoiceDetail p:nth-child(3)', $invoiceDetail->getLineTwo());
        }
        $this->assertSelectorTextContains('.invoiceDetail p:nth-child(4)', $invoiceDetail->getPostcode().' '.$invoiceDetail->getCity());
        $this->assertSelectorTextContains('.invoiceDetail p:nth-child(5)', $invoiceDetail->getCountry());

        $i = 0;
        foreach($purchase->getPurchaseLines() as $purchaseLine)
        {   
            $i++;
            $product = $purchaseLine->getProduct();
            $this->assertSelectorTextContains('.purchaseLine:nth-child('.$i.') .productPublicRef', $product['publicRef']);
            $this->assertSelectorTextContains('.purchaseLine:nth-child('.$i.') .productDesignation', $product['designation']);
            $this->assertSelectorTextContains('.purchaseLine:nth-child('.$i.') .quantity', $purchaseLine->getQuantity());
            $this->assertSelectorTextContains('.purchaseLine:nth-child('.$i.') .productPrice', $this->priceFormater->format($product['price']));
            $this->assertSelectorTextContains('.purchaseLine:nth-child('.$i.') .lineTotalPrice', $this->priceFormater->format($purchaseLine->getTotalPrice()));
        }

        $this->assertSelectorTextContains('.totalPrice', $this->priceFormater->format($purchase->getTotalPrice()));

        $this->assertSelectorTextContains('.status', SiteConfig::STATUS_LABELS[$purchase->getStatus()]);
        $this->assertSelectorTextContains('.createdAt', $purchase->getCreatedAt()->format('d/m/Y'));
        if($purchase->getPaidAt())
        {
            $this->assertSelectorTextContains('.paidAt', $purchase->getPaidAt()->format('d/m/Y'));
        }
    }
    public function testShowWithPurchaseHavingNoUser()
    {
        $this->loginAdmin();
        $purchase = $this->findEntity(PurchaseRepository::class, ['ref' => 'purchase_for_cart_update_test']); // on choisir une purchase avec un user particulier pour éviter de tomber sur admin et de le supprimer
        //on supprime le User associé
        $this->em->remove($purchase->getUser());
        $this->em->flush();

        $crawler = $this->client->request('GET', $this->urlGenerator->generate('admin_purchase_show', ['id' => $purchase->getId()]));
        $this->assertResponseIsSuccessful();
    }



    private function assertBreadcrumbIndexLink(Crawler $crawler): void
    {
    $this->assertSelectorTextContains('.breadcrumb-link', 'Commandes');
        $count = $this->purchaseRepository->countPurchasesInProcess();
        $this->assertSelectorTextContains('.breadcrumb-link', $count);
        $this->assertEquals(
            $this->urlGenerator->generate('admin_purchase_index'),
            $crawler->filter('.breadcrumb-link')->attr('href')
        );
    }
}