<?php
namespace App\Tests\Functional\Controller\Admin\Shop;

use App\Entity\Product;
use App\Entity\Category;
use App\Entity\SubCategory;
use App\Repository\UserRepository;
use App\Service\PicturePathResolver;
use App\Repository\PictureRepository;
use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DomCrawler\Crawler;
use App\DataFixtures\Tests\UserTestFixtures;
use Symfony\Component\HttpFoundation\Response;
use App\DataFixtures\Tests\ProductTestFixtures;
use App\Twig\Runtime\PriceFormaterExtensionRuntime;
use App\Tests\Functional\Controller\Admin\AdminFunctionalTest;
use App\DataFixtures\Tests\ProductWithOrWithoutCategoryTestFixtures;
use App\Repository\SubCategoryRepository;
use App\Twig\Runtime\ProductPicturePositionResolverExtensionRuntime;



/**
 * @group FunctionalAdmin
 * @group FunctionalAdminShop
 * @group FunctionalAdminShopProduct
 */
class AdminProductControllerTest extends AdminFunctionalTest
{
    public const POSLABEL = [
        1 => 'pictureOne',
        2 => 'pictureTwo',
        3 => 'pictureThree'
    ];

    private ProductPicturePositionResolverExtensionRuntime $picturePositionResolver;

    private PicturePathResolver $picturePathResolver;


    public function setUp(): void 
    {
        parent::setUp();

        $this->loadFixtures([UserTestFixtures::class, ProductTestFixtures::class]);  // userTestFixtures pour loginUser et loginAdmin

        $this->picturePositionResolver = $this->client->getContainer()->get(ProductPicturePositionResolverExtensionRuntime::class);
        $this->picturePathResolver = $this->client->getContainer()->get(PicturePathResolver::class);
    }

    // auth
    public function testRedirectToLoginWhenUserNotLogged()
    {
        $id = $this->findEntity(ProductRepository::class)->getId();

        $this->client->request('GET', $this->urlGenerator->generate('admin_product_index'));
        $this->assertResponseRedirects($this->urlGenerator->generate('security_login'));

        $this->client->request('GET', $this->urlGenerator->generate('admin_product_create'));
        $this->assertResponseRedirects($this->urlGenerator->generate('security_login'));

        $this->client->request('GET', $this->urlGenerator->generate('admin_product_update', ['id' => $id]));
        $this->assertResponseRedirects($this->urlGenerator->generate('security_login'));
        
        $this->client->request('GET', $this->urlGenerator->generate('admin_product_show', ['id' => $id]));
        $this->assertResponseRedirects($this->urlGenerator->generate('security_login'));

        $this->client->request('POST', $this->urlGenerator->generate('admin_product_delete', ['id' => $id]));
        $this->assertResponseRedirects($this->urlGenerator->generate('security_login'));
    }
    public function testUserCannotAccess()
    {
        $this->loginUser();
        $id = $this->findEntity(ProductRepository::class)->getId();

        $this->client->request('GET', $this->urlGenerator->generate('admin_product_index'));
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        $this->client->request('GET', $this->urlGenerator->generate('admin_product_create'));
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        $this->client->request('GET', $this->urlGenerator->generate('admin_product_update', ['id' => $id]));
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
        
        $this->client->request('GET', $this->urlGenerator->generate('admin_product_show', ['id' => $id]));
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        $this->client->request('POST', $this->urlGenerator->generate('admin_product_delete', ['id' => $id]));
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }
    public function testAdminCanAccess()
    {
        $this->loginAdmin();
        $product = $this->findEntity(ProductRepository::class);
        $id = $product->getId();

        $this->client->request('GET', $this->urlGenerator->generate('admin_product_index'));
        $this->assertResponseIsSuccessful();

        $this->client->request('GET', $this->urlGenerator->generate('admin_product_create'));
        $this->assertResponseIsSuccessful();

        $this->client->request('GET', $this->urlGenerator->generate('admin_product_update', ['id' => $id]));
        $this->assertResponseIsSuccessful();
        
        $this->client->request('GET', $this->urlGenerator->generate('admin_product_show', ['id' => $id]));
        $this->assertResponseIsSuccessful();

        $this->client->request('POST', $this->urlGenerator->generate('admin_product_delete'), ['id' => $id]);
        $this->assertResponseRedirects($this->urlGenerator->generate('admin_product_index'));
    }

    //index
    public function testIndexRender()
    {
        $this->loginAdmin();
        $this->client->request('GET', $this->urlGenerator->generate('admin_product_index'));
        $this->assertSelectorTextContains('h1', 'Produits');
    }
    public function testIndexAddNewProductButton()
    {
        $this->loginAdmin();
        $crawler = $this->client->request('GET', $this->urlGenerator->generate('admin_product_index'));
        $this->assertEquals(
            $this->urlGenerator->generate('admin_product_create'),
            $crawler->filter('.admin-buttons-fixed-wrapper .admin-button')->attr('href')
        );
    }
    public function testIndexContainsCorrectProductShowUpdateAndDeleteButtons()
    {
        $this->loginAdmin();
        $crawler = $this->client->request('GET', $this->urlGenerator->generate('admin_product_index'));
        //on récupère l'id du product de la 1ere ligne
        $productDesignation = $crawler->filter('tbody tr:first-child td:nth-child(2)')->text();
        $product = $this->findEntity(ProductRepository::class, ['designation' => $productDesignation]); 
        $id = $product->getId();       
        //show
        $showButton = $crawler->filter('tbody tr:first-child .admin-table-controls .admin-table-button:first-child');
        $this->assertEquals(
            $this->urlGenerator->generate('admin_product_show', ['id' => $id]),
            $showButton->attr('href')
        );
        //update
        $updateButton = $crawler->filter('tbody tr:first-child .admin-table-controls .admin-table-button:nth-child(2)');
        $this->assertEquals(
            $this->urlGenerator->generate('admin_product_update', ['id' => $id]),
            $updateButton->attr('href')
        );
        $this->assertEquals('Modifier', $updateButton->attr('title'));
        //delete
        $deleteForm = $crawler->filter('tbody tr:first-child .admin-table-controls form');
        $deleteButton = $crawler->filter('tbody tr:first-child .admin-table-controls form .admin-table-button');
        $this->assertEquals(
            $this->urlGenerator->generate('admin_product_delete'),
            $deleteForm->attr('action')
        );
        $this->assertEquals('id', $deleteButton->attr('name'));
        $this->assertEquals($id, $deleteButton->attr('value'));
        $this->assertEquals('Supprimer', $deleteButton->attr('title'));
    }
    public function testIndexBreadcrumb()
    {
        $this->loginAdmin();
        $crawler = $this->client->request('GET', $this->urlGenerator->generate('admin_product_index'));
        $this->assertBreadcrumbHomeLink($crawler);
        $this->assertBreadcrumbProductIndexLink($crawler);
    }
    public function testIndexContainsCorrectProductCount()
    {
        $this->loginAdmin();
        $count = $this->client->getContainer()->get(ProductRepository::class)->count([]);
        $this->client->request('GET', $this->urlGenerator->generate('admin_product_index'));
        $this->assertSelectorTextContains('.admin-count', $count);
    }
    public function testIndexContainsFilters()
    {
        $this->loginAdmin();
        $this->client->request('GET', $this->urlGenerator->generate('admin_product_index'));
        $this->assertSelectorExists('.admin-filters');
    }
    public function testIndexContainsCorrectFilters()
    {
        $this->loginAdmin();
        $this->client->request('GET', $this->urlGenerator->generate('admin_product_index'));
        $this->assertSelectorExists('.admin-filters input[name=q]');
        $this->assertSelectorExists('.admin-filters select[name=category]');
        $this->assertSelectorExists('.admin-filters select[name=subCategory]');
        $this->assertSelectorExists('.admin-filters input[name=minPrice]');
        $this->assertSelectorExists('.admin-filters input[name=maxPrice]');
        $this->assertSelectorExists('.admin-filters input[name=minStock]');
        $this->assertSelectorExists('.admin-filters input[name=maxStock]');
        $this->assertSelectorExists('.admin-filters select[name=sortBy]');
    }
    public function testIndexFiltersSelectContainsCorrectChoices()
    {
        $this->loginAdmin();
        $crawler = $this->client->request('GET', $this->urlGenerator->generate('admin_product_index'));
        //category
        /** @var CategoryRepository */
        $categoryRepository = $this->client->getContainer()->get(CategoryRepository::class);
        $categories = $categoryRepository->findAll();
        $choices = ['' => ''];
        foreach($categories as $category)
        {
            $choices[$category->getName()] = (string)($category->getId());
        }
        $this->assertSelectContainsChoices($choices, 'category', $crawler);
        // sortBy
        $this->assertSelectContainsChoices(
            [
                '' => '',
                'plus récents d\'abord' => 'createdAt_DESC',
                 'plus anciens d\'abord' => 'createdAt_ASC',
                'du moins cher au plus cher' => 'price_ASC',
                'du plus cher au moins cher' => 'price_DESC'
            ],
            'sortBy',
            $crawler
        );
    }
    public function testIndexFiltersCount()
    {
        $this->loginAdmin();
        /** @var EntityManagerInterface */
        $em = $this->client->getContainer()->get(EntityManagerInterface::class);
        $count = $em->createQueryBuilder()
                        ->select('COUNT(p.id) as count')
                        ->from('App\Entity\Product', 'p')
                        ->where('p.price <= :price')
                        ->setParameter('price', 5000)
                        ->getQuery()
                        ->getOneOrNullResult()['count'];
        $totalCount = $this->client->getContainer()->get(ProductRepository::class)->count([]);

        $this->submitForm('admin_product_index', 'Recherche', [
            'maxPrice' => '50'
        ]);
        $this->assertSelectorTextContains('.breadcrumb-link', $totalCount);
        $this->assertSelectorTextContains('.admin-count', $count);
    }
    public function testIndexFilterStock()
    {
        $this->loginAdmin();
        $this->submitForm('admin_product_index', 'Recherche', [
            'maxStock' => 0
        ]);
        $this->assertSelectorTextContains('tbody > tr:first-child > td:nth-child(5)', '0');
        $this->assertSelectorTextContains('tbody > tr:last-child > td:nth-child(5)', '0');
    }
    public function testIndexFilterCategory()
    {
        $category = $this->findEntity(CategoryRepository::class, ['slug' => 'categorie-1']);
        $this->loginAdmin();
        $this->submitForm('admin_product_index', 'Recherche', [
            'category' => $category->getId()
        ]);
        $this->assertSelectorTextContains('tbody > tr:first-child > td:nth-child(3)', 'Catégorie 1');
        $this->assertSelectorTextContains('tbody > tr:last-child > td:nth-child(3)', 'Catégorie 1');
    }
    public function testIndexFilterQ()
    {
        $this->loginAdmin();
        $this->submitForm('admin_product_index', 'Recherche', [
            'q' => 'objet'
        ]);
        $this->assertSelectorTextContains('tbody > tr:first-child > td:nth-child(2)', 'mon objet');
        $this->assertSelectorTextContains('tbody > tr:last-child > td:nth-child(2)', 'objet');
    }
    public function testIndexFiltersUsesPriceTransformer()
    {
        $this->loginAdmin();
        $this->submitForm('admin_product_index', 'Recherche', [
            'maxPrice' => '15'
        ]);
        $this->assertSelectorTextContains('tbody > tr:first-child > td:nth-child(2)', 'objet');
        $this->assertSelectorTextContains('tbody > tr:last-child > td:nth-child(2)', 'objet');
    }
    public function testIndexFilterSort()
    {
        $this->loginAdmin();
        $this->submitForm('admin_product_index', 'Recherche', [
            'sortBy' => 'price_ASC'
        ]);
        $this->assertSelectorTextContains('tbody > tr:first-child > td:nth-child(4)', '10,00 €');
    }

    // create
    //on vérifie les constraints qui ne sont pas sur Product mais sur le form ProductType (pictureOne NotNull) ou même dans le controller (uniqueSlug)
    public function testCreateRender()
    {
        $this->loginAdmin();
        $this->client->request('GET', $this->urlGenerator->generate('admin_product_create'));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Ajouter un produit');
    }
    public function testCreateBreadcrumb()
    {
        $this->loginAdmin();
        $crawler = $this->client->request('GET', $this->urlGenerator->generate('admin_product_create'));
        $this->assertBreadcrumbHomeLink($crawler);
        $this->assertBreadcrumbProductIndexLink($crawler);
        $this->assertSelectorTextContains('.breadcrumb-item', 'Ajouter');
    }
    public function testCreateValidProductWorks()
    {
        $this->loginAdmin();
        $this->submitForm(
            'admin_product_create', 
            'Ajouter', 
            $this->createValidData([
                'slug' => 'test-create-valid-product'
            ])
        );
        $persistedProduct = $this->findEntity(ProductRepository::class, ['slug' => 'test-create-valid-product']);
        $this->assertNotNull($persistedProduct);
    }
    public function testCreateInvalidProductWithNoFirstPicture()
    {
        $this->loginAdmin();
        $this->submitForm(
            'admin_product_create', 
            'Ajouter', 
            $this->createValidData([
                'pictureOne' => null
            ])
        );
        $this->assertSelectorTextContains('.pictureOne-group .admin-form-error', 'obligatoire');
    }
    public function testCreateInvalidProductWithTooBigPictureSize()
    {
        $this->loginAdmin();
        $this->submitForm(
            'admin_product_create', 
            'Ajouter', 
            $this->createValidData([
                'pictureOne' => $this->createUploadedFile('morethan2500k.jpg')
            ])
        );
        $this->assertSelectorTextContains('.pictureOne-group .admin-form-error', 'Image trop lourde');
    }
    public function testCreateInvalidProductWithBadFormatPicture()
    {
        $this->loginAdmin();
        $this->submitForm(
            'admin_product_create', 
            'Ajouter', 
            $this->createValidData([
                'pictureOne' => $this->createUploadedFile('badformat.txt')
            ])
        );
        $this->assertSelectorTextContains('.pictureOne-group .admin-form-error', 'Format requis');
    }
    public function testCreateInvalidProductWithExistingSlugAndSameCategoryAndSubCategory()
    {
        $this->loadFixtures([ProductWithOrWithoutCategoryTestFixtures::class, UserTestFixtures::class]);
        $product = $this->findEntity(ProductRepository::class, ['slug' => 'product-with-category-and-subcategory']);
        $this->loginAdmin();
        $this->submitForm(
            'admin_product_create', 
            'Ajouter', 
            $this->createValidData([
                'slug' => $product->getSlug(),
                'category' => $product->getCategory()->getId(),
                'subCategory' => $product->getSubCategory()->getId(),
            ])
        );
        $this->assertSelectorExists('.slug-group .admin-form-error');
    }
    public function testCreateValidProductCorrectPersist()
    {
        $products = $this->client->getContainer()->get(UserRepository::class)->findAll();
        $this->loginAdmin();
        $this->submitForm(
            'admin_product_create', 
            'Ajouter', 
            $this->createValidData([
                'designation' => 'le produit de test',
                'slug' => 'un-produit-de-test',
                'price' => '55,55',
                'stock' => '123',
                'publicRef' => 'refDuProduit',
                'pictureOne' => $this->createUploadedFile('lessthan200k.jpg'),
                'suggestedProducts' => [$products[0]->getId(), $products[1]->getId()]
            ])
        );
        /** @var Product */
        $persistedProduct = $this->findEntity(ProductRepository::class, ['designation' => 'le produit de test']);
        $this->assertEquals('un-produit-de-test', $persistedProduct->getSlug());
        $this->assertEquals(5555, $persistedProduct->getPrice());
        $this->assertEquals(123, $persistedProduct->getStock());
        $this->assertEquals('refDuProduit', $persistedProduct->getPublicRef());
        $this->assertTrue(str_contains($persistedProduct->getPictures()->get(0)->getFileName(), 'lessthan200k'));
        $this->assertEquals(
            $products[0]->getId(),
            $persistedProduct->getSuggestedProducts()->get(0)->getId(),
            'peut-être que le pb vient simplement de l\'ordre d\'ajout des suggestedProducts'
        );
        $this->assertEquals(
            $products[1]->getId(),
            $persistedProduct->getSuggestedProducts()->get(1)->getId()
        );
    }
    public function testCreateValidProductRedirectToAdminProductShow()
    {
        $this->loginAdmin();
        $this->submitForm(
            'admin_product_create', 
            'Ajouter', 
            $this->createValidData([
                'slug' => 'product-to-follow'
            ])
        );
        $persistedProduct = $this->findEntity(ProductRepository::class, ['slug' => 'product-to-follow']);
        $this->assertResponseRedirects($this->urlGenerator->generate('admin_product_show', [
            'id' => $persistedProduct->getId()
        ]));
    }
    public function testCreateValidProductFirstPictureCanBeResolve()
    {
        $this->loginAdmin();
        $this->submitForm(
            'admin_product_create', 
            'Ajouter', 
            $this->createValidData([
                'slug' => 'retrouve-moi',
                'pictureOne' => $this->createUploadedFile('lessthan10k.jpg')
            ])
        );
        /** @var Product */
        $persistedProduct = $this->findEntity(ProductRepository::class, ['slug' => 'retrouve-moi']);
        $this->assertNotSame(
            '/img/default.jpg',
            $this->picturePathResolver->getPath($persistedProduct->getPictures()->get(0))
        );
        $this->assertStringContainsString(
            '/img/pictures/lessthan10k',
            $this->picturePathResolver->getPath($persistedProduct->getPictures()->get(0))
        );
    }
    public function testCreateValidProductCorrectOrderedPictures()
    {
        $this->loginAdmin();
        $this->submitForm(
            'admin_product_create', 
            'Ajouter', 
            $this->createValidData([
                'slug' => 'produit-avec-trois-images',
                'pictureOne' => $this->createUploadedFile('lessthan200k.jpg'),
                'pictureTwo' => $this->createUploadedFile('lessthan10k.jpg'),
                'pictureThree' => $this->createUploadedFile('lessthan200k.jpg')
            ])
        );
        /** @var Product */
        $persistedProduct = $this->findEntity(ProductRepository::class, ['slug' => 'produit-avec-trois-images']);
        $this->assertStringContainsString(
            'lessthan200k',
            $this->picturePositionResolver->getPictureAtPosition($persistedProduct, 1)->getFileName()
        );
        $this->assertStringContainsString(
            'lessthan10k',
            $this->picturePositionResolver->getPictureAtPosition($persistedProduct, 2)->getFileName()
        );
        $this->assertStringContainsString(
            'lessthan200k',
            $this->picturePositionResolver->getPictureAtPosition($persistedProduct, 3)->getFileName()
        );
    }
    public function testCreateValidProductAltPersisted()
    {
        $this->loginAdmin();
        $this->submitForm(
            'admin_product_create', 
            'Ajouter', 
            $this->createValidData([
                'slug' => 'produit-avec-deux-alt',
                'pictureOne' => $this->createUploadedFile('lessthan200k.jpg'),
                'pictureTwo' => $this->createUploadedFile('lessthan10k.jpg'),
                'altOne' => 'alt-1',
                'altTwo' => 'alt-2'
            ])
        );
        /** @var Product */
        $persistedProduct = $this->findEntity(ProductRepository::class, ['slug' => 'produit-avec-deux-alt']);
        $pictureOne = $this->picturePositionResolver->getPictureAtPosition($persistedProduct, 1);
        $pictureTwo = $this->picturePositionResolver->getPictureAtPosition($persistedProduct, 2);
        $this->assertEquals('alt-1', $pictureOne->getAlt());
        $this->assertEquals('alt-2', $pictureTwo->getAlt());
    }
    
    //update
    public function testUpdateWithInexistantIdParam()
    {
        $this->loginAdmin();
        $this->client->request('GET', $this->urlGenerator->generate('admin_product_update', [
            'id' => '12345678944561256'
        ]));
        $this->assertResponseStatusCodeSame(Response::HTTP_INTERNAL_SERVER_ERROR);
    }
    public function testUpdateRender()
    {
        $this->loginAdmin();
        $product = $this->findEntity(ProductRepository::class);
        $this->client->request('GET', $this->urlGenerator->generate('admin_product_update', [
            'id' => $product->getId()
        ]));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Modifier "'.$product->getDesignation().'"');
    }
    public function testUpdateBreadcrumb()
    {
        $this->loginAdmin();
        $product = $this->findEntity(ProductRepository::class);
        $crawler = $this->client->request('GET', $this->urlGenerator->generate('admin_product_update', ['id' => $product->getId()]));
        $this->assertBreadcrumbHomeLink($crawler);
        $this->assertBreadcrumbProductIndexLink($crawler);
        $this->assertSelectorTextContains('.breadcrumb-link:nth-child(3)', $product->getDesignation());
        $this->assertEquals(
            $this->urlGenerator->generate('admin_product_show', ['id' => $product->getId()]),
            $crawler->filter('.breadcrumb-link:nth-child(3)')->attr('href')
        );
        $this->assertSelectorTextContains('.breadcrumb-item', 'Modifier');
    }
    public function testUpdateWithValidDataRedirectToProductShow()
    {
        $this->loginAdmin();
        $product = $this->findEntity(ProductRepository::class);
        $this->submitForm(
            'admin_product_update', 
            'Modifier', 
            $this->createValidData(),
            ['id' => $product->getId()]
        );
        $this->assertResponseRedirects($this->urlGenerator->generate('admin_product_show', [
            'id' => $product->getId()
        ]));
    }
    /**
     * Cette fonction teste à la fois le fait qu'on peut modifier un product sans changer son slug (constraint UniqueSlug), et aussi sans soumettre une pictureOne
     */
    public function testUpdateWithoutChangeNothingWorks()
    {
        $this->loginAdmin();
        $product = $this->findEntity(ProductRepository::class);
        $this->submitForm(
            'admin_product_update', 
            'Modifier', 
            [],
            ['id' => $product->getId()]
        );
        $this->assertResponseRedirects($this->urlGenerator->generate('admin_product_show', [
            'id' => $product->getId()
        ]));
    }
    public function testUpdateCorrectChanges()
    {
        $suggestedProduct = $this->findEntity(ProductRepository::class);
        $this->loginAdmin();
        $oldProduct = $this->findEntity(ProductRepository::class, ['slug' => 'product-with-suggested-products']); // product with 2 suggestedProducts
        $this->submitForm(
            'admin_product_update', 
            'Modifier', 
            [
                'product[designation]' => 'updated product',
                'product[slug]' => 'updated-product',
                'product[pictureOne]' => $this->createUploadedFile('lessthan10k.jpg'),
                'product[altOne]' => 'updated-alt-1',
                'product[pictureThree]' => $this->createUploadedFile('lessthan200k.jpg'),
                'product[altThree]' => 'updated-alt-3',
                'product[stock]' => '789',
                'product[price]' => '22,45',
                'product[suggestedProducts]' => [$suggestedProduct->getId()]
            ],
            ['id' => $oldProduct->getId()]
        );
        /** @var Product */
        $updatedProduct = $this->findEntity(ProductRepository::class, ['id' => $oldProduct->getId()]);
        $this->assertNotNull($updatedProduct);
        $this->assertEquals('updated product', $updatedProduct->getDesignation());
        $this->assertEquals('updated-product', $updatedProduct->getSlug());
        $this->assertStringContainsString(
            'lessthan10k', 
            $this->picturePathResolver->getPath(
                $this->picturePositionResolver->getPictureAtPosition($updatedProduct, 1)
            )
        );
        $this->assertEquals(
            'updated-alt-1', 
            $this->picturePositionResolver->getPictureAtPosition($updatedProduct, 1)->getAlt()
        );
        $this->assertStringContainsString(
            'lessthan200k', 
            $this->picturePathResolver->getPath(
                $this->picturePositionResolver->getPictureAtPosition($updatedProduct, 3)
            )
        );
        $this->assertEquals(
            'updated-alt-3', 
            $this->picturePositionResolver->getPictureAtPosition($updatedProduct, 3)->getAlt()
        );
        $this->assertEquals(789, $updatedProduct->getStock());
        $this->assertEquals(2245, $updatedProduct->getPrice());
        $this->assertCount(1, $updatedProduct->getSuggestedProducts());
        $this->assertEquals(
            $suggestedProduct->getId(),
            $updatedProduct->getSuggestedProducts()->get(0)->getId()
        );
    }
    public function testUpdateNotChangedValuesRemainUnchanged()
    {
        $this->loginAdmin();
        $oldProduct = $this->findEntity(ProductRepository::class, ['slug' => 'product-with-suggested-products']); // product with 2 suggestedProducts
        $this->submitForm(
            'admin_product_update', 
            'Modifier', 
            [
                'product[slug]' => 'updated-product',
            ],
            ['id' => $oldProduct->getId()]
        );
        /** @var Product */
        $updatedProduct = $this->findEntity(ProductRepository::class, ['id' => $oldProduct->getId()]);
        $this->assertEquals($oldProduct->getDesignation(), $updatedProduct->getDesignation());
        $this->assertEquals($oldProduct->getStock(), $updatedProduct->getStock());
        $this->assertEquals($oldProduct->getPrice(), $updatedProduct->getPrice());
        $this->assertCount(2, $updatedProduct->getSuggestedProducts());
    }
    public function testUpdateWithInvalidTooBigPicture()
    {
        $this->loginAdmin();
        $oldProduct = $this->findEntity(ProductRepository::class);
        $this->submitForm(
            'admin_product_update', 
            'Modifier', 
            [
                'product[pictureThree]' => $this->createUploadedFile('morethan2500k.jpg'),
            ],
            ['id' => $oldProduct->getId()]
        );
        $this->assertSelectorTextContains('.pictureThree-group .admin-form-error', 'Image trop lourde');
    }
    public function testUpdateWithInvalidBadFormatPicture()
    {
        $this->loginAdmin();
        $oldProduct = $this->findEntity(ProductRepository::class);
        $this->submitForm(
            'admin_product_update', 
            'Modifier', 
            [
                'product[pictureTwo]' => $this->createUploadedFile('badformat.txt'),
            ],
            ['id' => $oldProduct->getId()]
        );
        $this->assertSelectorTextContains('.pictureTwo-group .admin-form-error', 'Format requis');
    }
    public function testUpdateInvalidExistingSlugWithSameCategoryAndSubCategory()
    {
        $this->loadFixtures([ProductWithOrWithoutCategoryTestFixtures::class, UserTestFixtures::class]);
        $productToCopySlug = $this->findEntity(ProductRepository::class, ['slug' => 'product-with-category-and-subcategory']);
        $oldProduct = $this->findEntity(ProductRepository::class, ['slug' => 'product-with-category']); // on spécifie le product qu'on veut pour être sur de pas tomber sur le même

        $this->loginAdmin();
        $this->submitForm(
            'admin_product_update', 
            'Modifier', 
            [
                'product[slug]' => $productToCopySlug->getSlug(),
                'product[category]' => $productToCopySlug->getCategory()->getId(),
                'product[subCategory]' => $productToCopySlug->getSubCategory()->getId(),
            ],
            ['id' => $oldProduct->getId()]
        );
        $this->assertSelectorExists('.slug-group .admin-form-error');
    }


    //delete
    public function testDeleteWithInexistantIdParam()
    {
        $this->loginAdmin();
        $this->client->request('POST', $this->urlGenerator->generate('admin_product_delete'), [
            'id' => 123456789456123456
        ]);
        $this->assertResponseStatusCodeSame(Response::HTTP_INTERNAL_SERVER_ERROR);
    }
    public function testDeleteWithValidParamsRedirectToIndex()
    {
        $this->loginAdmin();
        $product = $this->findEntity(ProductRepository::class);
        $id = $product->getId();
        $this->client->request('POST', $this->urlGenerator->generate('admin_product_delete'), [
            'id' => $id
        ]);
        $this->assertResponseRedirects($this->urlGenerator->generate('admin_product_index'));
    }
    public function testDeleteWillDeleteCorrectProduct()
    {
        $this->loginAdmin();
        $product = $this->findEntity(ProductRepository::class);
        $id = $product->getId();
        $this->client->request('POST', $this->urlGenerator->generate('admin_product_delete'), [
            'id' => $id
        ]);
        $this->assertNull(
            $this->findEntity(ProductRepository::class, ['id' => $id])
        );
    }
    public function testDeleteWillDeletePicturesToo()
    {
        $this->loginAdmin();
        //on crée un product avec 2 pictures
        $this->submitForm(
            'admin_product_create', 
            'Ajouter', 
            $this->createValidData([
                'slug' => 'test-create-valid-product-with-pictures',
                'pictureOne' => $this->createUploadedFile('lessthan10k.jpg'),
                'pictureThree' => $this->createUploadedFile('lessthan200k.jpg')
            ])
        );
        //on récupère le product persisté et ses deux pictures persistées également
        $persistedProduct = $this->findEntity(ProductRepository::class, ['slug' => 'test-create-valid-product-with-pictures']);
        $pictureOneId = $this->picturePositionResolver->getPictureAtPosition($persistedProduct, 1);
        $pictureThreeId = $this->picturePositionResolver->getPictureAtPosition($persistedProduct, 3);
        //on vérifie qu'on peut bien retrouver les picture *
        $this->assertNotNull(
            $this->findEntity(PictureRepository::class, ['id' => $pictureOneId])
        );
        //on supprime le product
        $this->client->request('POST', $this->urlGenerator->generate('admin_product_delete'), [
            'id' => $persistedProduct->getId()
        ]);
        //* on vérifie qu'on ne peut plus retrouver les picture
        $this->assertNull(
            $this->findEntity(PictureRepository::class, ['id' => $pictureOneId])
        );
        $this->assertNull(
            $this->findEntity(PictureRepository::class, ['id' => $pictureThreeId])
        );
    }
    public function testDeleteWillNotDeleteSuggestedProducts()
    {
        $this->loginAdmin();

        $product = $this->findEntity(ProductRepository::class, ['slug' => 'product-with-suggested-products']); // product with 2 suggestedProducts
        $suggestedProducts = $product->getSuggestedProducts();
        $id1 = $suggestedProducts->get(0)->getId();
        $id2 = $suggestedProducts->get(1)->getId();


        $this->client->request('POST', $this->urlGenerator->generate('admin_product_delete'), [
            'id' => $product->getId()
        ]);
        
        $this->assertNotNull($this->findEntity(ProductRepository::class, ['id' => $id1]));
        $this->assertNotNull($this->findEntity(ProductRepository::class, ['id' => $id2]));
    }
    //show
    public function testShowWithInexistantIdParam()
    {
        $this->loginAdmin();
        $this->client->request('GET', $this->urlGenerator->generate('admin_product_show', [
            'id' => '12345678944561256'
        ]));
        $this->assertResponseStatusCodeSame(Response::HTTP_INTERNAL_SERVER_ERROR);
    }
    public function testShowRender()
    {
        $this->loginAdmin();
        $product = $this->findEntity(ProductRepository::class);
        $this->client->request('GET', $this->urlGenerator->generate('admin_product_show', [
            'id' => $product->getId()
        ]));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', $product->getDesignation());
    }
    public function testShowContainsCorrectUpdateButton()
    {
        $this->loginAdmin();
        $product = $this->findEntity(ProductRepository::class);
        $crawler = $this->client->request('GET', $this->urlGenerator->generate('admin_product_show', [
            'id' => $product->getId()
        ]));
        $updateButton = $crawler->filter('.admin-buttons-wrapper a.admin-button');
        $this->assertEquals(
            $this->urlGenerator->generate('admin_product_update', ['id' => $product->getId()]),
            $updateButton->attr('href')
        );
    }
    public function testShowContainsCorrectDeleteButton()
    {
        $this->loginAdmin();
        $product = $this->findEntity(ProductRepository::class);
        $crawler = $this->client->request('GET', $this->urlGenerator->generate('admin_product_show', [
            'id' => $product->getId()
        ]));
        $deleteForm = $crawler->filter('.admin-buttons-wrapper form');
        $deleteButton = $crawler->filter('.admin-buttons-wrapper button.admin-button');
        $this->assertEquals(
            $this->urlGenerator->generate('admin_product_delete'),
            $deleteForm->attr('action')
        );
        $this->assertEquals('id', $deleteButton->attr('name'));
        $this->assertEquals($product->getId(), $deleteButton->attr('value'));
    }
    public function testShowBreadcrumb()
    {
        $this->loginAdmin();
        $product = $this->findEntity(ProductRepository::class);
        $crawler = $this->client->request('GET', $this->urlGenerator->generate('admin_product_show', [
            'id' => $product->getId()
        ]));
        $this->assertBreadcrumbHomeLink($crawler);
        $this->assertBreadcrumbProductIndexLink($crawler);
        $this->assertSelectorTextContains('.breadcrumb-item', $product->getDesignation());
    }
    public function testShowContainsCorrectSections()
    {
        /** @var PriceFormaterExtensionRuntime */
        $priceFormater = $this->client->getContainer()->get(PriceFormaterExtensionRuntime::class);
        $this->loginAdmin();
        // produit avec pictureOne et pictureThree (avec alt pour chaque picture), 2 suggestedProducts, category, subCategory
        $product = $this->findEntity(ProductRepository::class, ['slug' => 'product-with-all-fields']); 
        $this->client->request('GET', $this->urlGenerator->generate('admin_product_show', [
            'id' => $product->getId()
        ]));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.pictureOne');
        $this->assertSelectorTextContains('.pictureOneAlt', 'texte alternatif 1');
        $this->assertSelectorNotExists('.pictureTwoAlt');
        $this->assertSelectorExists('.pictureThree');
        $this->assertSelectorTextContains('.pictureThreeAlt', 'texte alternatif 3');
        $this->assertSelectorTextContains('.designation', $product->getDesignation());
        $this->assertSelectorTextContains('.slug', $product->getSlug());
        $this->assertSelectorTextContains('.category', $product->getCategory()->getName());
        $this->assertSelectorTextContains('.subCategory', $product->getSubCategory()->getName());
        $this->assertSelectorTextContains('.price', $priceFormater->format($product->getPrice()));
        $this->assertSelectorTextContains('.stock', $product->getStock());
        $this->assertSelectorTextContains('.publicRef', $product->getPublicRef());
        $this->assertSelectorTextContains('.privateRef', $product->getPrivateRef());
        $this->assertSelectorTextContains('.suggestedProducts:first-child .suggestedProduct', $product->getSuggestedProducts()->get(0)->getDesignation());
        $this->assertSelectorTextContains('.suggestedProducts:last-child .suggestedProduct', $product->getSuggestedProducts()->get(1)->getDesignation());
    }

    private function createValidData(array $options = []):array
    {
        $productData = [
            'product[pictureOne]' => $this->createUploadedFile('lessthan200k.jpg'),
            'product[designation]' => 'nouveau produit',
            'product[slug]' => 'nouveau-produit',
            'product[price]' => '10',
            'product[stock]' => '1',
            'product[publicRef]' => 'newRef',
        ];
        foreach($options as $key => $value)
        {
            $productData['product['.$key.']'] = $value;
        }
        return $productData;
    }

    private function assertBreadcrumbProductIndexLink(Crawler $crawler): void
    {
        $this->assertSelectorTextContains('.breadcrumb-link', 'Produits');
        $this->assertEquals(
            $this->urlGenerator->generate('admin_product_index'),
            $crawler->filter('.breadcrumb-link:nth-child(2)')->attr('href')
        );
        $count = $this->client->getContainer()->get(ProductRepository::class)->count([]);
        $this->assertSelectorTextContains('.breadcrumb-link', $count);
    }
   
}