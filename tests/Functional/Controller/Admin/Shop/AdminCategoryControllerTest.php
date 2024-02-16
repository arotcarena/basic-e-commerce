<?php
namespace App\Tests\Functional\Controller\Admin\Shop;

use App\Entity\Category;
use App\Entity\SubCategory;
use App\Service\PicturePathResolver;
use App\Repository\PictureRepository;
use App\Repository\CategoryRepository;
use App\Repository\SubCategoryRepository;
use Symfony\Component\DomCrawler\Crawler;
use App\DataFixtures\Tests\UserTestFixtures;
use Symfony\Component\HttpFoundation\Response;
use App\DataFixtures\Tests\CategoryTestFixtures;
use App\Tests\Functional\Controller\Admin\AdminFunctionalTest;



/**
 * @group FunctionalAdmin
 * @group FunctionalAdminShop
 * @group FunctionalAdminShopCategory
 */
class AdminCategoryControllerTest extends AdminFunctionalTest
{
    private PicturePathResolver $picturePathResolver;

    private CategoryRepository $categoryRepository;

    public function setUp(): void 
    {
        parent::setUp();

        $this->loadFixtures([UserTestFixtures::class, CategoryTestFixtures::class]);  // userTestFixtures pour loginUser et loginAdmin

        $this->picturePathResolver = $this->client->getContainer()->get(PicturePathResolver::class);

        $this->categoryRepository = $this->client->getContainer()->get(CategoryRepository::class);
    }

    // auth
    public function testRedirectToLoginWhenUserNotLogged()
    {
        $id = $this->findEntity(CategoryRepository::class)->getId();

        $this->client->request('GET', $this->urlGenerator->generate('admin_category_index'));
        $this->assertResponseRedirects($this->urlGenerator->generate('security_login'));

        $this->client->request('GET', $this->urlGenerator->generate('admin_category_create'));
        $this->assertResponseRedirects($this->urlGenerator->generate('security_login'));

        $this->client->request('GET', $this->urlGenerator->generate('admin_category_update', ['id' => $id]));
        $this->assertResponseRedirects($this->urlGenerator->generate('security_login'));
        
        $this->client->request('GET', $this->urlGenerator->generate('admin_category_show', ['id' => $id]));
        $this->assertResponseRedirects($this->urlGenerator->generate('security_login'));

        $this->client->request('POST', $this->urlGenerator->generate('admin_category_delete', ['id' => $id]));
        $this->assertResponseRedirects($this->urlGenerator->generate('security_login'));
    }
    public function testUserCannotAccess()
    {
        $this->loginUser();
        $id = $this->findEntity(CategoryRepository::class)->getId();

        $this->client->request('GET', $this->urlGenerator->generate('admin_category_index'));
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        $this->client->request('GET', $this->urlGenerator->generate('admin_category_create'));
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        $this->client->request('GET', $this->urlGenerator->generate('admin_category_update', ['id' => $id]));
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
        
        $this->client->request('GET', $this->urlGenerator->generate('admin_category_show', ['id' => $id]));
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        $this->client->request('POST', $this->urlGenerator->generate('admin_category_delete', ['id' => $id]));
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }
    public function testAdminCanAccess()
    {
        $this->loginAdmin();
        $id = $this->findEntity(CategoryRepository::class)->getId();

        $this->client->request('GET', $this->urlGenerator->generate('admin_category_index'));
        $this->assertResponseIsSuccessful();

        $this->client->request('GET', $this->urlGenerator->generate('admin_category_create'));
        $this->assertResponseIsSuccessful();

        $this->client->request('GET', $this->urlGenerator->generate('admin_category_update', ['id' => $id]));
        $this->assertResponseIsSuccessful();
        
        $this->client->request('GET', $this->urlGenerator->generate('admin_category_show', ['id' => $id]));
        $this->assertResponseIsSuccessful();

        $this->client->request('POST', $this->urlGenerator->generate('admin_category_delete'), ['id' => $id]);
        $this->assertResponseRedirects($this->urlGenerator->generate('admin_category_index'));
    }

    //index
    public function testIndexRender()
    {
        $this->loginAdmin();
        $this->client->request('GET', $this->urlGenerator->generate('admin_category_index'));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Catégories');
    }
    public function testIndexAddNewCategoryButton()
    {
        $this->loginAdmin();
        $crawler = $this->client->request('GET', $this->urlGenerator->generate('admin_category_index'));
        $addNewCategoryButton = $crawler->filter('.admin-buttons-fixed-wrapper .admin-button:first-child');
        $this->assertEquals(
            $this->urlGenerator->generate('admin_category_create'),
            $addNewCategoryButton->attr('href')
        );
    }
    public function testIndexAddNewSubCategoryButton()
    {
        $this->loginAdmin();
        $crawler = $this->client->request('GET', $this->urlGenerator->generate('admin_category_index'));
        $addNewSubCategoryButton = $crawler->filter('.admin-buttons-fixed-wrapper .admin-button:nth-child(2)');
        $this->assertEquals(
            $this->urlGenerator->generate('admin_subCategory_create'),
            $addNewSubCategoryButton->attr('href')
        );
    }
    public function testIndexContainsCorrectCategoryShowUpdateDeleteButtons()
    {
        $this->loginAdmin();
        $crawler = $this->client->request('GET', $this->urlGenerator->generate('admin_category_index'));
        //on récupère l'id de la category de la 1ere ligne
        $categoryName = $crawler->filter('tbody tr:first-child td:first-child .admin-table-main-img-text')->text();
        $category = $this->findEntity(CategoryRepository::class, ['name' => $categoryName]);
        $id = $category->getId();
        //show
        $showButton = $crawler->filter('tbody tr:first-child td:last-child .admin-table-controls .admin-table-button:first-child');
        $this->assertEquals(
            $this->urlGenerator->generate('admin_category_show', ['id' => $id]),
            $showButton->attr('href')
        );
        //update
        $updateButton = $crawler->filter('tbody tr:first-child td:last-child .admin-table-controls .admin-table-button:nth-child(2)');
        $this->assertEquals(
            $this->urlGenerator->generate('admin_category_update', ['id' => $id]),
            $updateButton->attr('href')
        );
        //delete
        $deleteForm = $crawler->filter('tbody tr:first-child td:last-child .admin-table-controls form');
        $deleteButton = $crawler->filter('tbody tr:first-child td:last-child .admin-table-controls form .admin-table-button');
        $this->assertEquals(
            $this->urlGenerator->generate('admin_category_delete'),
            $deleteForm->attr('action')
        );
        $this->assertEquals('id', $deleteButton->attr('name'));
        $this->assertEquals($id, $deleteButton->attr('value'));
    }
    public function testIndexContainsCorrectSubCategoryShowUpdateDeleteButtons()
    {
        $this->loginAdmin();
        $crawler = $this->client->request('GET', $this->urlGenerator->generate('admin_category_index'));
        //on récupère l'id de la subCategory de la 1ere ligne dans la category de la 1ere ligne
        $subCategoryName = $crawler->filter('tbody tr:first-child .admin-table-expand-cell-line:first-child > span:first-of-type')->text();
        //on récupère le nom de la category (afin de ne pas se confondre car les subCategory ont parfois le même nom)
        $categoryName = $crawler->filter('tbody tr:first-child td:first-child .admin-table-main-img-text')->text();
        $category = $this->findEntity(CategoryRepository::class, ['name' => $categoryName]);
        //on peut maintenant récupérer la bonne subCategory
        $subCategory = $this->findEntity(SubCategoryRepository::class, ['name' => $subCategoryName, 'parentCategory' => $category]);
        $id = $subCategory->getId();
        //show
        $showButton = $crawler->filter('tbody tr:first-child .admin-table-expand-cell-line:first-child .admin-table-controls .admin-table-button:first-child');
        $this->assertEquals(
            $this->urlGenerator->generate('admin_subCategory_show', ['id' => $id]),
            $showButton->attr('href')
        );
        //update
        $updateButton = $crawler->filter('tbody tr:first-child .admin-table-expand-cell-line:first-child .admin-table-controls .admin-table-button:nth-child(2)');
        $this->assertEquals(
            $this->urlGenerator->generate('admin_subCategory_update', ['id' => $id]),
            $updateButton->attr('href')
        );
        //delete
        $deleteForm = $crawler->filter('tbody tr:first-child .admin-table-expand-cell-line:first-child .admin-table-controls form');
        $deleteButton = $crawler->filter('tbody tr:first-child .admin-table-expand-cell-line:first-child .admin-table-controls form .admin-table-button');
        $this->assertEquals(
            $this->urlGenerator->generate('admin_subCategory_delete'),
            $deleteForm->attr('action')
        );
        $this->assertEquals('id', $deleteButton->attr('name'));
        $this->assertEquals($id, $deleteButton->attr('value'));
    }

    public function testIndexBreadcrumb()
    {
        $this->loginAdmin();
        $crawler = $this->client->request('GET', $this->urlGenerator->generate('admin_category_index'));
        $this->assertBreadcrumbHomeLink($crawler);
        $this->assertSelectorTextContains('.breadcrumb-item', 'Catégories');
        $count = $this->categoryRepository->count([]);
        $this->assertSelectorTextContains('.breadcrumb-item', $count);
    }
    public function testIndexContainsCorrectCategoryCount()
    {
        $this->loginAdmin();
        $count = $this->categoryRepository->count([]);
        $this->client->request('GET', $this->urlGenerator->generate('admin_category_index'));
        $this->assertSelectorTextContains('.breadcrumb-item', $count);
        $this->assertSelectorExists('.admin-table-line:nth-child('.$count.')');
    }
    public function testIndexContainsCorrectOrderedCategories()
    {
        $this->loginAdmin();
        $orderedCategories = $this->categoryRepository->findAllOrderedForMenuList();
        $this->client->request('GET', $this->urlGenerator->generate('admin_category_index'));
        foreach($orderedCategories as $category)
        {
            $this->assertSelectorExists('.admin-table-line:nth-child('.$category->getListPosition().') .admin-table-main-img-text', $category->getName());
        }
    }
   

    // create
    //on vérifie les constraints qui ne sont pas sur Category mais sur le form CategoryType (pictureOne NotNull) ou même dans le controller (uniqueSlug)
    public function testCreateRender()
    {
        $this->loginAdmin();
        $this->client->request('GET', $this->urlGenerator->generate('admin_category_create'));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Ajouter une catégorie');
    }
    public function testCreateBreadcrumb()
    {
        $this->loginAdmin();
        $crawler = $this->client->request('GET', $this->urlGenerator->generate('admin_category_create'));
        $this->assertBreadcrumbHomeLink($crawler);
        $this->assertBreadcrumbCategoryIndex($crawler);
        $this->assertSelectorTextContains('.breadcrumb-item', 'Ajouter');
    }
    public function testCreateValidCategoryWorks()
    {
        $this->loginAdmin();
        $this->submitForm(
            'admin_category_create', 
            'Ajouter', 
            $this->createValidData([
                'slug' => 'test-create-valid-category'
            ])
        );
        $persistedCategory = $this->findEntity(CategoryRepository::class, ['slug' => 'test-create-valid-category']);
        $this->assertNotNull($persistedCategory);
    }
    public function testCreateInvalidCategoryWithNoPicture()
    {
        $this->loginAdmin();
        $this->submitForm(
            'admin_category_create', 
            'Ajouter', 
            $this->createValidData([
                'picture' => null
            ])
        );
        $this->assertSelectorTextContains('.picture-group .admin-form-error', 'obligatoire');
    }
    public function testCreateInvalidCategoryWithTooBigPictureSize()
    {
        $this->loginAdmin();
        $this->submitForm(
            'admin_category_create', 
            'Ajouter', 
            $this->createValidData([
                'picture' => $this->createUploadedFile('morethan2500k.jpg')
            ])
        );
        $this->assertSelectorTextContains('.picture-group .admin-form-error', 'Image trop lourde');
    }
    public function testCreateInvalidCategoryWithBadFormatPicture()
    {
        $this->loginAdmin();
        $this->submitForm(
            'admin_category_create', 
            'Ajouter', 
            $this->createValidData([
                'picture' => $this->createUploadedFile('badformat.txt')
            ])
        );
        $this->assertSelectorTextContains('.picture-group .admin-form-error', 'Format requis');
    }
    public function testCreateValidCategoryCorrectPersist()
    {
        $this->loginAdmin();
        $this->submitForm(
            'admin_category_create', 
            'Ajouter', 
            $this->createValidData([
                'picture' => $this->createUploadedFile('lessthan200k.jpg'),
                'name' => 'catégorie de test',
                'slug' => 'une-categorie-de-test',
                'listPosition' => '25'
            ])
        );
        /** @var Category */
        $persistedCategory = $this->findEntity(CategoryRepository::class, ['slug' => 'une-categorie-de-test']);
        $this->assertTrue(str_contains($persistedCategory->getPicture()->getFileName(), 'lessthan200k'));
        $this->assertEquals('catégorie de test', $persistedCategory->getName());
        $this->assertEquals(25, $persistedCategory->getListPosition());
    }
    public function testCreateValidCategoryRedirectToAdminCategoryIndex()
    {
        $this->loginAdmin();
        $this->submitForm(
            'admin_category_create', 
            'Ajouter', 
            $this->createValidData()
        );
        $this->assertResponseRedirects($this->urlGenerator->generate('admin_category_index'));
    }
    public function testCreateValidCategoryPictureCanBeResolved()
    {
        $this->loginAdmin();
        $this->submitForm(
            'admin_category_create', 
            'Ajouter', 
            $this->createValidData([
                'slug' => 'retrouve-moi',
                'picture' => $this->createUploadedFile('lessthan10k.jpg')
            ])
        );
        /** @var Category */
        $persistedCategory = $this->findEntity(CategoryRepository::class, ['slug' => 'retrouve-moi']);
        $this->assertNotSame(
            '/img/default.jpg',
            $this->picturePathResolver->getPath($persistedCategory->getPicture())
        );
        $this->assertStringContainsString(
            '/img/pictures/lessthan10k',
            $this->picturePathResolver->getPath($persistedCategory->getPicture())
        );
    }
    public function testCreateValidCategoryAltPersisted()
    {
        $this->loginAdmin();
        $this->submitForm(
            'admin_category_create', 
            'Ajouter', 
            $this->createValidData([
                'slug' => 'categorie-avec-alt',
                'picture' => $this->createUploadedFile('lessthan200k.jpg'),
                'alt' => 'category-alt'
            ])
        );
        /** @var Category */
        $persistedCategory = $this->findEntity(CategoryRepository::class, ['slug' => 'categorie-avec-alt']);
        $this->assertEquals('category-alt', $persistedCategory->getPicture()->getAlt());
    }
    
    //update
    public function testUpdateWithInexistantIdParam()
    {
        $this->loginAdmin();
        $this->client->request('GET', $this->urlGenerator->generate('admin_category_update', [
            'id' => '12345678944561256'
        ]));
        $this->assertResponseStatusCodeSame(Response::HTTP_INTERNAL_SERVER_ERROR);
    }
    public function testUpdateRender()
    {
        $this->loginAdmin();
        $category = $this->findEntity(CategoryRepository::class);
        $this->client->request('GET', $this->urlGenerator->generate('admin_category_update', [
            'id' => $category->getId()
        ]));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Modifier la catégorie "'.$category->getName().'"');
    }
    public function testUpdateBreadcrumb()
    {
        $this->loginAdmin();
        $category = $this->findEntity(CategoryRepository::class);
        $crawler = $this->client->request('GET', $this->urlGenerator->generate('admin_category_update', [
            'id' => $category->getId()
        ]));
        $this->assertBreadcrumbHomeLink($crawler);
        $this->assertBreadcrumbCategoryIndex($crawler);
        $this->assertSelectorTextContains('.breadcrumb-link:nth-child(3)', $category->getName());
        $this->assertEquals(
            $this->urlGenerator->generate('admin_category_show', ['id' => $category->getId()]),
            $crawler->filter('.breadcrumb-link:nth-child(3)')->attr('href')
        );
        $this->assertSelectorTextContains('.breadcrumb-item', 'Modifier');
    }
    public function testUpdateWithValidDataRedirectToIndex()
    {
        $this->loginAdmin();
        $category = $this->findEntity(CategoryRepository::class);
        $this->submitForm(
            'admin_category_update', 
            'Modifier', 
            $this->createValidData(),
            ['id' => $category->getId()]
        );
        $this->assertResponseRedirects($this->urlGenerator->generate('admin_category_index'));
    }
    /**
     * Cette fonction teste à la fois le fait qu'on peut modifier une category sans changer son slug (constraint UniqueSlug), et aussi sans soumettre une picture
     */
    public function testUpdateWithoutChangeNothingWorks()
    {
        $this->loginAdmin();
        $category = $this->findEntity(CategoryRepository::class);
        $this->submitForm(
            'admin_category_update', 
            'Modifier', 
            [],
            ['id' => $category->getId()]
        );
        $this->assertResponseRedirects($this->urlGenerator->generate('admin_category_index'));
    }
    public function testUpdateCorrectChanges()
    {
        $this->loginAdmin();
        $oldCategory = $this->findEntity(CategoryRepository::class); 
        $this->submitForm(
            'admin_category_update', 
            'Modifier', 
            [
                'category[picture]' => $this->createUploadedFile('lessthan10k.jpg'),
                'category[alt]' => 'updated-category-alt',
                'category[name]' => 'updated category',
                'category[slug]' => 'updated-category',
                'category[listPosition]' => '150'
            ],
            ['id' => $oldCategory->getId()]
        );
        /** @var Category */
        $updatedCategory = $this->findEntity(CategoryRepository::class, ['id' => $oldCategory->getId()]);
        $this->assertNotNull($updatedCategory);
        $this->assertStringContainsString(
            'lessthan10k', 
            $this->picturePathResolver->getPath($updatedCategory->getPicture())
        );
        $this->assertEquals('updated-category-alt', $updatedCategory->getPicture()->getAlt());
        $this->assertEquals('updated category', $updatedCategory->getName());
        $this->assertEquals('updated-category', $updatedCategory->getSlug());
        $this->assertEquals(150, $updatedCategory->getListPosition());
    }
    public function testUpdateNotChangedValuesRemainUnchanged()
    {
        $this->loginAdmin();
        $oldCategory = $this->findEntity(CategoryRepository::class); 
        $this->submitForm(
            'admin_category_update', 
            'Modifier', 
            [
                'category[slug]' => 'updated-category-new',
            ],
            ['id' => $oldCategory->getId()]
        );
        /** @var Category */
        $updatedCategory = $this->findEntity(CategoryRepository::class, ['id' => $oldCategory->getId()]);
        $this->assertEquals($oldCategory->getName(), $updatedCategory->getName());
        $this->assertEquals($oldCategory->getListPosition(), $updatedCategory->getListPosition());
        $this->assertEquals($oldCategory->getPicture(), $updatedCategory->getPicture());  // on ne compare surtout pas l'id car les pictures sont certainement null
    }
    public function testUpdateWithInvalidTooBigPicture()
    {
        $this->loginAdmin();
        $oldCategory = $this->findEntity(CategoryRepository::class); 
        $this->submitForm(
            'admin_category_update', 
            'Modifier', 
            [
                'category[picture]' => $this->createUploadedFile('morethan2500k.jpg'),
            ],
            ['id' => $oldCategory->getId()]
        );
        $this->assertSelectorTextContains('.picture-group .admin-form-error', 'Image trop lourde');
    }
    public function testUpdateWithInvalidBadFormatPicture()
    {
        $this->loginAdmin();
        $oldCategory = $this->findEntity(CategoryRepository::class); 
        $this->submitForm(
            'admin_category_update', 
            'Modifier', 
            [
                'category[picture]' => $this->createUploadedFile('badformat.txt'),
            ],
            ['id' => $oldCategory->getId()]
        );
        $this->assertSelectorTextContains('.picture-group .admin-form-error', 'Format requis');
    }


    //delete
    public function testDeleteWithInexistantIdParam()
    {
        $this->loginAdmin();
        $this->client->request('POST', $this->urlGenerator->generate('admin_category_delete'), [
            'id' => 123456789456123456
        ]);
        $this->assertResponseStatusCodeSame(Response::HTTP_INTERNAL_SERVER_ERROR);
    }
    public function testDeleteWithValidParamsRedirectToIndex()
    {
        $this->loginAdmin();
        $category = $this->findEntity(CategoryRepository::class);
        $id = $category->getId();
        $this->client->request('POST', $this->urlGenerator->generate('admin_category_delete'), [
            'id' => $id
        ]);
        $this->assertResponseRedirects($this->urlGenerator->generate('admin_category_index'));
    }
    public function testDeleteWillDeleteCorrectCategory()
    {
        $this->loginAdmin();
        $category = $this->findEntity(CategoryRepository::class);
        $id = $category->getId();
        $this->client->request('POST', $this->urlGenerator->generate('admin_category_delete'), [
            'id' => $id
        ]);
        $this->assertNull(
            $this->findEntity(CategoryRepository::class, ['id' => $id])
        );
    }
    public function testDeleteWillDeletePictureToo()
    {
        $this->loginAdmin();
        //on crée une catégorie avec une picture
        $this->submitForm(
            'admin_category_create', 
            'Ajouter', 
            $this->createValidData([
                'slug' => 'test-create-valid-category-with-picture',
                'picture' => $this->createUploadedFile('lessthan10k.jpg'),
            ])
        );
        //on récupère la category persistée et la picture persistée également
        $persistedCategory = $this->findEntity(CategoryRepository::class, ['slug' => 'test-create-valid-category-with-picture']);
        $pictureId = $persistedCategory->getPicture()->getId();
        //on vérifie qu'on peut bien retrouver la picture *
        $this->assertNotNull(
            $this->findEntity(PictureRepository::class, ['id' => $pictureId])
        );
        //on supprime la category
        $this->client->request('POST', $this->urlGenerator->generate('admin_category_delete'), [
            'id' => $persistedCategory->getId()
        ]);
        //* on vérifie qu'on ne peut plus retrouver la picture
        $this->assertNull(
            $this->findEntity(PictureRepository::class, ['id' => $pictureId])
        );
    }
    public function testDeleteWillDeleteSubCategoriesToo()
    {
        $this->loginAdmin();
        $category = $this->findEntity(CategoryRepository::class); // toutes les category ont minimum 2 subCategories
        $subCategoryIds = [];
        foreach($category->getSubCategories() as $subCategory)
        {
            $subCategoryIds[] = $subCategory->getId();
        }
        //on vérifie qu'on peut retrouver les subCategories
        $this->assertNotNull($this->findEntity(SubCategoryRepository::class, ['id' => $subCategoryIds[0]]));
        //on supprime la category
        $this->client->request('POST', $this->urlGenerator->generate('admin_category_delete'), [
            'id' => $category->getId()
        ]);
        //on vérifie qu'on ne peut plus retrouver les subCategories
        foreach($subCategoryIds as $id)
        {
            $this->assertNull(
                $this->findEntity(SubCategoryRepository::class, ['id' => $id])
            );
        }
    }
   
    //show
    public function testShowWithInexistantIdParam()
    {
        $this->loginAdmin();
        $this->client->request('GET', $this->urlGenerator->generate('admin_category_show', [
            'id' => '12345678944561256'
        ]));
        $this->assertResponseStatusCodeSame(Response::HTTP_INTERNAL_SERVER_ERROR);
    }
    public function testShowRender()
    {
        $this->loginAdmin();
        $category = $this->findEntity(CategoryRepository::class);
        $this->client->request('GET', $this->urlGenerator->generate('admin_category_show', [
            'id' => $category->getId()
        ]));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', $category->getName());
    }
    public function testShowContainsCorrectUpdateButton()
    {
        $this->loginAdmin();
        $category = $this->findEntity(CategoryRepository::class);
        $crawler = $this->client->request('GET', $this->urlGenerator->generate('admin_category_show', [
            'id' => $category->getId()
        ]));
        $this->assertEquals(
            $this->urlGenerator->generate('admin_category_update', ['id' => $category->getId()]),
            $crawler->filter('.admin-buttons-wrapper .admin-button:first-child')->attr('href')
        );
    }
    public function testShowContainsCorrectDeleteButton()
    {
        $this->loginAdmin();
        $category = $this->findEntity(CategoryRepository::class);
        $crawler = $this->client->request('GET', $this->urlGenerator->generate('admin_category_show', [
            'id' => $category->getId()
        ]));
        $deleteForm = $crawler->filter('.admin-buttons-wrapper form');
        $deleteButton = $crawler->filter('.admin-buttons-wrapper form .admin-button');
        $this->assertEquals(
            $this->urlGenerator->generate('admin_category_delete'),
            $deleteForm->attr('action')
        );
        $this->assertEquals('id', $deleteButton->attr('name'));
        $this->assertEquals($category->getId(), $deleteButton->attr('value'));
    }
    public function testShowBreadcrumb()
    {
        $this->loginAdmin();
        $category = $this->findEntity(CategoryRepository::class);
        $crawler = $this->client->request('GET', $this->urlGenerator->generate('admin_category_show', [
            'id' => $category->getId()
        ]));
        $this->assertBreadcrumbHomeLink($crawler);
        $this->assertBreadcrumbCategoryIndex($crawler);
        $this->assertSelectorTextContains('.breadcrumb-item', $category->getName());
    }
    public function testShowContainsCorrectSections()
    {
        $this->loginAdmin();
        $category = $this->findEntity(CategoryRepository::class, ['slug' => 'categorie-3']);  // category avec 2 subCategories (dont le name commence par Sous-catégorie)

        $this->client->request('GET', $this->urlGenerator->generate('admin_category_show', [
            'id' => $category->getId()
        ]));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.picture');
        $this->assertSelectorTextContains('.pictureAlt', $category->getPicture()->getAlt());
        $this->assertSelectorTextContains('.name', $category->getName());
        $this->assertSelectorTextContains('.slug', $category->getSlug());
        $this->assertSelectorTextContains('.subCategory', 'Sous-catégorie'); // les deux subCategories ont un name commençant par Sous-catégorie
    }
    public function testShowContainsCorrectOrderedSubCategories()
    {
        $this->loginAdmin();
        $category = $this->findEntity(CategoryRepository::class, ['slug' => 'categorie-1']);  // category avec 3 subCategories entrées dans le désordre par rapport à leur listPosition
        $category = $this->categoryRepository->findOneOrdered($category->getId()); // obligatoire pour que les subCategories s'affichent dans le bon ordre
        
        $this->client->request('GET', $this->urlGenerator->generate('admin_category_show', [
            'id' => $category->getId()
        ]));
        foreach($category->getSubCategories() as $subCategory)
        {
            $this->assertSelectorTextContains('.subCategories:nth-of-type('.$subCategory->getListPosition().') .subCategory', $subCategory->getListPosition().'. '. $subCategory->getName());
        }
    }


    private function createValidData(array $options = []):array
    {
        $categoryData = [
            'category[picture]' => $this->createUploadedFile('lessthan200k.jpg'),
            'category[name]' => 'nouvelle categorie',
            'category[slug]' => 'nouvelle-categorie',
            'category[listPosition]' => '100'
        ];
        foreach($options as $key => $value)
        {
            $categoryData['category['.$key.']'] = $value;
        }
        return $categoryData;
    }

    private function assertBreadcrumbCategoryIndex(Crawler $crawler): void 
    {
        $this->assertSelectorTextContains('.breadcrumb-link:nth-child(2)', 'Catégories');
        $count = $this->categoryRepository->count([]);
        $this->assertSelectorTextContains('.breadcrumb-link:nth-child(2)', $count);
        $this->assertEquals(
            $this->urlGenerator->generate('admin_category_index'),
            $crawler->filter('.breadcrumb-link:nth-child(2)')->attr('href')
        );
    }
    
}