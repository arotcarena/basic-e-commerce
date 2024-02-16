<?php
namespace App\Tests\Functional\Admin\Shop;

use App\Entity\Review;
use App\Config\SiteConfig;
use App\Repository\ReviewRepository;
use App\Service\ProductShowUrlResolver;
use Symfony\Component\DomCrawler\Crawler;
use App\DataFixtures\Tests\ReviewTestFixtures;
use Symfony\Component\HttpFoundation\Response;
use App\Tests\Functional\Controller\Admin\AdminFunctionalTest;

class AdminReviewControllerTest extends AdminFunctionalTest
{
    private ReviewRepository $reviewRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->loadFixtures([ReviewTestFixtures::class]);  // depends on UserTestFixtures & ProductTestFixtures

        $this->reviewRepository = $this->client->getContainer()->get(ReviewRepository::class);
    }

    // auth
    public function testRedirectToLoginWhenUserNotLogged()
    {
        $id = $this->findEntity(ReviewRepository::class)->getId();

        $this->client->request('GET', $this->urlGenerator->generate('admin_review_index'));
        $this->assertResponseRedirects($this->urlGenerator->generate('security_login'));
        
        $this->client->request('GET', $this->urlGenerator->generate('admin_review_show', ['id' => $id]));
        $this->assertResponseRedirects($this->urlGenerator->generate('security_login'));
    }
    public function testUserCannotAccess()
    {
        $this->loginUser();
        $id = $this->findEntity(ReviewRepository::class)->getId();

        $this->client->request('GET', $this->urlGenerator->generate('admin_review_index'));
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
        
        $this->client->request('GET', $this->urlGenerator->generate('admin_review_show', ['id' => $id]));
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }
    public function testAdminCanAccess()
    {
        $this->loginAdmin();
        $id = $this->findEntity(ReviewRepository::class)->getId();

        $this->client->request('GET', $this->urlGenerator->generate('admin_review_index'));
        $this->assertResponseIsSuccessful();

        $this->client->request('GET', $this->urlGenerator->generate('admin_review_show', ['id' => $id]));
        $this->assertResponseIsSuccessful();
    }

    //index
    public function testIndexRender()
    {
        $this->loginAdmin();
        $this->client->request('GET', $this->urlGenerator->generate('admin_review_index'));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Avis');
    }
    public function testIndexBreadcrumb()
    {
        $this->loginAdmin();
        $crawler = $this->client->request('GET', $this->urlGenerator->generate('admin_review_index'));
        $this->assertBreadcrumbHomeLink($crawler);
        $this->assertBreadcrumbIndexLink($crawler);
    }
    
    public function testIndexContainsCorrectShowButton()
    {
        $this->loginAdmin();
        $crawler = $this->client->request('GET', $this->urlGenerator->generate('admin_review_index'));
        //on récupère l'id de la review de la 1ere ligne
        $id = $crawler->filter('tbody tr:first-child')->attr('data-id');
        //on vérifie la présence du bouton show et son href
        $showButton = $crawler->filter('tbody tr:first-child td.controls .admin-table-button');
        $this->assertStringContainsString('Voir l\'avis', $showButton->attr('title'));
        $this->assertEquals(
            $this->urlGenerator->generate('admin_review_show', ['id' => $id]),
            $showButton->attr('href')
        );
    }
    public function testIndexCorrectCountWithoutFilters()
    {
        $this->loginAdmin();
        $this->client->request('GET', $this->urlGenerator->generate('admin_review_index'));
        $pendingCount = $this->reviewRepository->count(['moderationStatus' => null]);
        $totalCount = $this->reviewRepository->count([]);
        $this->assertSelectorTextContains('.breadcrumb-link', $pendingCount);
        $this->assertSelectorTextContains('.admin-count', $totalCount);
    }
    public function testIndexCorrectCountWithFilters()
    {
        $this->loginAdmin();
        $this->client->request('GET', $this->urlGenerator->generate('admin_review_index'), [
            'moderationStatus' => SiteConfig::MODERATION_STATUS_ACCEPTED
        ]);
        $pendingCount = $this->reviewRepository->count(['moderationStatus' => null]);
        $filteredCount = $this->reviewRepository->count(['moderationStatus' => SiteConfig::MODERATION_STATUS_ACCEPTED]);
        $this->assertSelectorTextContains('.breadcrumb-link', $pendingCount);
        $this->assertSelectorTextContains('.admin-count', $filteredCount);
    }
    /**
     * Il y a 20 reviews dans les fixtures, donc pour contrôler le total affiché on est pas géné par la pagination qui est de 20 également
     */
    public function testIndexCorrectFilters()
    {
        $this->loginAdmin();
        $crawler = $this->client->request('GET', $this->urlGenerator->generate('admin_review_index'), [
            'moderationStatus' => SiteConfig::MODERATION_STATUS_REFUSED
        ]);
        $count1 = $crawler->filter('tbody tr')->count();
        $this->assertEquals(
            $this->reviewRepository->count(['moderationStatus' => SiteConfig::MODERATION_STATUS_REFUSED]),
            $count1
        );
        if($count1 > 5) 
        {
            $count1 = 5;  // pour éviter que le test soit trop long (on se contente de vérifier un maximum de 5 lignes)
        }
        for ($i=1; $i <= $count1; $i++) 
        { 
            //on vérifie le label du status
            $this->assertSelectorTextContains('tbody tr:nth-child('.$i.') .moderationStatus', Siteconfig::MODERATION_STATUS_REFUSED_LABEL);
            //et le status lui-même
            $this->assertEquals(
                $crawler->filter('tbody tr:nth-child('.$i.') .moderationStatus')->attr('value'),
                SiteConfig::MODERATION_STATUS_REFUSED
            );
        }

        //on recommence avec un autre status
        $crawler = $this->client->request('GET', $this->urlGenerator->generate('admin_review_index'), [
            'moderationStatus' => SiteConfig::MODERATION_STATUS_PENDING
        ]);
        $count2 = $crawler->filter('tbody tr')->count();
        $this->assertEquals(
            $this->reviewRepository->count(['moderationStatus' => null]),
            $count2
        );
        if($count2 > 5) 
        {
            $count2 = 5;  // pour éviter que le test soit trop long (on se contente de vérifier un maximum de 5 lignes)
        }
        for ($i=1; $i <= $count2; $i++) 
        { 
            //on vérifie le label du status
            $this->assertSelectorTextContains('tbody tr:nth-child('.$i.') .moderationStatus', SiteConfig::MODERATION_STATUS_PENDING_LABEL);
            //et le status lui-même
            $this->assertEquals(
                $crawler->filter('tbody tr:nth-child('.$i.') .moderationStatus')->attr('value'),
                ''  // car la valeur est null
            );
        }
        //on vérifie qu'au moins un élément a pu être testé (sinon le test n\'est pas probant)
        if(($count1 + $count2) === 0)
        {
            $this->fail('Aucun élément testé, il faudrait créer plus de Reviews pour que le test soit probant');
        }
    }
    public function testIndexIsSortByCreatedAtDescByDefault()
    {
        $this->loginAdmin();
        $crawler = $this->client->request('GET', $this->urlGenerator->generate('admin_review_index'));
        
        $previousTimestamp = time();
        for ($i=1; $i <= 7; $i++) { 
            $lineTimestamp = $crawler->filter('tbody tr:nth-child('.$i.') .createdAt')->attr('value');  // on a placé dans value createdAt.timestamp
            $this->assertTrue($lineTimestamp < $previousTimestamp);
            $previousTimestamp = $lineTimestamp;    
        }
    }
    public function testIndexCorrectSort()
    {
        $this->loginAdmin();
        $crawler = $this->client->request('GET', $this->urlGenerator->generate('admin_review_index'), [
            'sortBy' => 'createdAt_ASC'
        ]);
        $previousTimestamp = 0;
        for ($i=1; $i <= 7; $i++) { 
            $lineTimestamp = $crawler->filter('tbody tr:nth-child('.$i.') .createdAt')->attr('value');
            $this->assertTrue($lineTimestamp > $previousTimestamp);
            $previousTimestamp = $lineTimestamp;    
        }
        //on filtre avec autre chose
        $crawler = $this->client->request('GET', $this->urlGenerator->generate('admin_review_index'), [
            'sortBy' => 'rate_DESC'
        ]);
        $previousRate = 5;
        for ($i=1; $i <= 7; $i++) { 
            $lineRate = $crawler->filter('tbody tr:nth-child('.$i.') .rate')->text();
            $this->assertTrue($lineRate <= $previousRate);
            $previousRate = $lineRate;    
        }
    }
    public function testIndexTableLineClassDependsOnModerationStatus()
    {
        $this->loginAdmin();
        $crawler = $this->client->request('GET', $this->urlGenerator->generate('admin_review_index'));
        for ($i=1; $i <= 10; $i++) { 
            $lineStatus = $crawler->filter('tbody tr:nth-child('.$i.') .moderationStatus')->attr('value');
            $lineClass = $crawler->filter('tbody tr:nth-child('.$i.')')->attr('class');
            if($lineStatus === SiteConfig::MODERATION_STATUS_ACCEPTED || $lineStatus === SiteConfig::MODERATION_STATUS_REFUSED)
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
            $message = !isset($verified1) ? 'Il n\'y a aucune review avec un moderation status refused ou accepted': 'Il n\'y a aucune review avec un moderationStatus pending';
            $this->fail('Le test n\'est pas probant. '.$message);
        }
    }
    public function testIndexSearchFiltersArePresent()
    {
        $this->loginAdmin();
        $this->client->request('GET', $this->urlGenerator->generate('admin_review_index'));
        $this->assertSelectorExists('[name=rate]');
        $this->assertSelectorExists('[name=moderationStatus]');
        $this->assertSelectorExists('[name=sortBy]');
    }
    public function testIndexSearchFiltersContainsCorrectModerationStatusChoices()
    {
        $this->loginAdmin();
        $crawler = $this->client->request('GET', $this->urlGenerator->generate('admin_review_index'));

        $this->assertSelectContainsChoices(
            [
                '' => '',
                SiteConfig::MODERATION_STATUS_PENDING_LABEL => SiteConfig::MODERATION_STATUS_PENDING,
                SiteConfig::MODERATION_STATUS_ACCEPTED_LABEL => SiteConfig::MODERATION_STATUS_ACCEPTED,
                SiteConfig::MODERATION_STATUS_REFUSED_LABEL => SiteConfig::MODERATION_STATUS_REFUSED
            ]
            , 
            'moderationStatus', 
            $crawler
        );
    }
    
    public function testIndexSearchFiltersContainsCorrectRatesChoices()
    {
        $this->loginAdmin();
        $crawler = $this->client->request('GET', $this->urlGenerator->generate('admin_review_index'));

        $this->assertSelectContainsChoices(
            [
                '' => '',
                '1' => 1,
                '2' => 2,
                '3' => 3,
                '4' => 4,
                '5' => 5
            ],
            'rate',
            $crawler
        );
    }
    public function testIndexSearchFiltersContainsCorrectSortChoices()
    {
        $this->loginAdmin();
        $crawler = $this->client->request('GET', $this->urlGenerator->generate('admin_review_index'));

        $this->assertSelectContainsChoices(
            [
                '' => '',
                'Meilleures notes d\'abord' => 'rate_DESC',
                'Plus mauvaises notes d\'abord' => 'rate_ASC',
                'Plus récents d\'abord' => 'createdAt_DESC',
                'Plus anciens d\'abord' => 'createdAt_ASC'
            ],
            'sortBy',
            $crawler
        );
    }

     //show
     public function testShowWithInexistantIdParam()
    {
        $this->loginAdmin();
        $this->client->request('GET', $this->urlGenerator->generate('admin_review_show', [
            'id' => '12345678944561256'
        ]));
        $this->assertResponseStatusCodeSame(Response::HTTP_INTERNAL_SERVER_ERROR);
    }
     public function testShowRender()
     {
         $this->loginAdmin();
         $review = $this->findEntity(ReviewRepository::class);
         $this->client->request('GET', $this->urlGenerator->generate('admin_review_show', ['id' => $review->getId()]));
         $this->assertResponseIsSuccessful();
         $this->assertSelectorTextContains('h1', 'Avis sur "'.$review->getProduct()->getDesignation().'"');
     }
     public function testShowBreadcrumb()
     {
         $this->loginAdmin();
         $review = $this->findEntity(ReviewRepository::class);
         $crawler = $this->client->request('GET', $this->urlGenerator->generate('admin_review_show', ['id' => $review->getId()]));
         $this->assertBreadcrumbHomeLink($crawler);
         $this->assertBreadcrumbIndexLink($crawler);
         $this->assertSelectorTextContains('.breadcrumb-item', $review->getProduct()->getDesignation());
     }
     public function testShowContainsCorrectSections()
     {
        $this->loginAdmin();
        /** @var Review */
        $review = $this->findEntity(ReviewRepository::class);
        
        $crawler = $this->client->request('GET', $this->urlGenerator->generate('admin_review_show', ['id' => $review->getId()]));

        $productDesignation = $crawler->filter('.productDesignation');
        $this->assertEquals($review->getProduct()->getDesignation(), $productDesignation->text());
        $this->assertEquals(
            $this->client->getContainer()->get(ProductShowUrlResolver::class)->getUrl($review->getProduct()),
            $productDesignation->attr('href')
        );

        $this->assertSelectorTextContains('.fullName', $review->getFullName());
        $this->assertSelectorTextContains('.email', $review->getUser()->getEmail());
        $this->assertSelectorTextContains('.rate', $review->getRate());
        $this->assertSelectorTextContains('.comment', $review->getComment());
        $this->assertSelectorTextContains('.createdAt', $review->getCreatedAt()->format('d/m/Y H:h'));
        $this->assertSelectorTextContains('.moderationStatus', $review->getModerationStatusLabel());
     }


    private function assertBreadcrumbIndexLink(Crawler $crawler): void 
    {
        $this->assertSelectorTextContains('.breadcrumb-link', 'Avis');
        $count = $this->reviewRepository->count(['moderationStatus' => null]);
        $this->assertSelectorTextContains('.breadcrumb-link', $count);
        $this->assertEquals(
            $this->urlGenerator->generate('admin_review_index'),
            $crawler->filter('.breadcrumb-link')->attr('href')
        );
    }

}