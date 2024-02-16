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
 * @group FunctionalAdminShopSubCategory
 */
class AdminSubCategoryControllerTest extends AdminFunctionalTest
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
        $id = $this->findEntity(SubCategoryRepository::class)->getId();

        $this->client->request('GET', $this->urlGenerator->generate('admin_subCategory_create'));
        $this->assertResponseRedirects($this->urlGenerator->generate('security_login'));

        $this->client->request('GET', $this->urlGenerator->generate('admin_subCategory_update', ['id' => $id]));
        $this->assertResponseRedirects($this->urlGenerator->generate('security_login'));
        
        $this->client->request('GET', $this->urlGenerator->generate('admin_subCategory_show', ['id' => $id]));
        $this->assertResponseRedirects($this->urlGenerator->generate('security_login'));

        $this->client->request('POST', $this->urlGenerator->generate('admin_subCategory_delete', ['id' => $id]));
        $this->assertResponseRedirects($this->urlGenerator->generate('security_login'));
    }
    public function testUserCannotAccess()
    {
        $this->loginUser();
        $id = $this->findEntity(SubCategoryRepository::class)->getId();

        $this->client->request('GET', $this->urlGenerator->generate('admin_subCategory_create'));
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        $this->client->request('GET', $this->urlGenerator->generate('admin_subCategory_update', ['id' => $id]));
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
        
        $this->client->request('GET', $this->urlGenerator->generate('admin_subCategory_show', ['id' => $id]));
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        $this->client->request('POST', $this->urlGenerator->generate('admin_subCategory_delete', ['id' => $id]));
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }
    public function testAdminCanAccess()
    {
        $this->loginAdmin();
        $id = $this->findEntity(SubCategoryRepository::class)->getId();

        $this->client->request('GET', $this->urlGenerator->generate('admin_subCategory_create'));
        $this->assertResponseIsSuccessful();

        $this->client->request('GET', $this->urlGenerator->generate('admin_subCategory_update', ['id' => $id]));
        $this->assertResponseIsSuccessful();
        
        $this->client->request('GET', $this->urlGenerator->generate('admin_subCategory_show', ['id' => $id]));
        $this->assertResponseIsSuccessful();

        $this->client->request('POST', $this->urlGenerator->generate('admin_subCategory_delete'), ['id' => $id]);
        $this->assertResponseRedirects($this->urlGenerator->generate('admin_category_index'));
    }

    // create
    //on vérifie les constraints qui ne sont pas sur Category mais sur le form CategoryType (pictureOne NotNull) ou même dans le controller (uniqueSlug)
    public function testCreateRender()
    {
        $this->loginAdmin();
        $this->client->request('GET', $this->urlGenerator->generate('admin_subCategory_create'));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Ajouter une sous-catégorie');
    }
    public function testCreateBreadcrumb()
    {
        $this->loginAdmin();
        $crawler = $this->client->request('GET', $this->urlGenerator->generate('admin_subCategory_create'));
        $this->assertBreadcrumbHomeLink($crawler);
        $this->assertBreadcrumbCategoryIndex($crawler);
        $this->assertBreadcrumbSubCategoryIndex($crawler);
        $this->assertSelectorTextContains('.breadcrumb-item', 'Ajouter');
    }
    public function testCreateValidSubCategoryWorks()
    {
        $this->loginAdmin();
        $this->submitForm(
            'admin_subCategory_create', 
            'Ajouter', 
            $this->createValidData([
                'slug' => 'test-create-valid-subcategory'
            ])
        );
        $persistedsubCategory = $this->findEntity(SubCategoryRepository::class, ['slug' => 'test-create-valid-subcategory']);
        $this->assertNotNull($persistedsubCategory);
    }
    public function testCreateInvalidSubCategoryWithNoPicture()
    {
        $this->loginAdmin();
        $this->submitForm(
            'admin_subCategory_create', 
            'Ajouter', 
            $this->createValidData([
                'picture' => null
            ])
        );
        $this->assertSelectorTextContains('.picture-group .admin-form-error', 'obligatoire');
    }
    public function testCreateInvalidSubCategoryWithTooBigPictureSize()
    {
        $this->loginAdmin();
        $this->submitForm(
            'admin_subCategory_create', 
            'Ajouter', 
            $this->createValidData([
                'picture' => $this->createUploadedFile('morethan2500k.jpg')
            ])
        );
        $this->assertSelectorTextContains('.picture-group .admin-form-error', 'Image trop lourde');
    }
    public function testCreateInvalidSubCategoryWithBadFormatPicture()
    {
        $this->loginAdmin();
        $this->submitForm(
            'admin_subCategory_create', 
            'Ajouter', 
            $this->createValidData([
                'picture' => $this->createUploadedFile('badformat.txt')
            ])
        );
        $this->assertSelectorTextContains('.picture-group .admin-form-error', 'Format requis');
    }
    public function testCreateInvalidSubCategoryWithExistantSlugAndSameParentCategory()
    {
        $this->loginAdmin();
        $category = $this->findEntity(CategoryRepository::class, ['slug' => 'categorie-1']); // category avec 3 subCategory
        $existantSubCategory = $category->getSubCategories()->get(0);
        $this->submitForm(
            'admin_subCategory_create', 
            'Ajouter', 
            $this->createValidData([
                'slug' => $existantSubCategory->getSlug(),
                'parentCategory' => (string)($category->getId())
            ])
        );
        $this->assertSelectorTextContains('.slug-group .admin-form-error', 'déjà utilisé');
    }
    public function testCreateInvalidSubCategoryWithExistantListPositionAndSameParentCategory()
    {
        $this->loginAdmin();
        $category = $this->findEntity(CategoryRepository::class, ['slug' => 'categorie-1']); // category avec 3 subCategory
        $existantSubCategory = $category->getSubCategories()->get(0);
        $this->submitForm(
            'admin_subCategory_create', 
            'Ajouter', 
            $this->createValidData([
                'listPosition' => $existantSubCategory->getListPosition(),
                'parentCategory' => $category->getId()
            ])
        );
        $this->assertSelectorTextContains('.listPosition-group .admin-form-error', 'déjà utilisée');
    }
    public function testCreateValidCategoryCorrectPersist()
    {
        $this->loginAdmin();
        $category = $this->findEntity(CategoryRepository::class, ['slug' => 'categorie-1']); // category avec 3 subCategory (listPosition: 1, 2, 3)
        $this->submitForm(
            'admin_subCategory_create', 
            'Ajouter', 
            $this->createValidData([
                'picture' => $this->createUploadedFile('lessthan200k.jpg'),
                'name' => 'sous-catégorie de test',
                'slug' => 'une-sous-categorie-de-test',
                'listPosition' => '25',
                'parentCategory' => $category->getId()
            ])
        );
        /** @var SubCategory */
        $persistedSubCategory = $this->findEntity(SubCategoryRepository::class, ['slug' => 'une-sous-categorie-de-test']);
        $this->assertTrue(str_contains($persistedSubCategory->getPicture()->getFileName(), 'lessthan200k'));
        $this->assertEquals('sous-catégorie de test', $persistedSubCategory->getName());
        $this->assertEquals(25, $persistedSubCategory->getListPosition());
        $this->assertEquals($category->getId(), $persistedSubCategory->getParentCategory()->getId());
    }
    public function testCreateValidSubCategoryRedirectToAdminCategoryIndex()
    {
        $this->loginAdmin();
        $this->submitForm(
            'admin_subCategory_create', 
            'Ajouter', 
            $this->createValidData()
        );
        $this->assertResponseRedirects($this->urlGenerator->generate('admin_category_index'));
    }
    public function testCreateValidCategoryPictureCanBeResolved()
    {
        $this->loginAdmin();
        $this->submitForm(
            'admin_subCategory_create', 
            'Ajouter', 
            $this->createValidData([
                'slug' => 'retrouve-moi',
                'picture' => $this->createUploadedFile('lessthan10k.jpg')
            ])
        );
        /** @var SubCategory */
        $persistedSubCategory = $this->findEntity(SubCategoryRepository::class, ['slug' => 'retrouve-moi']);
        $this->assertNotSame(
            '/img/default.jpg',
            $this->picturePathResolver->getPath($persistedSubCategory->getPicture())
        );
        $this->assertStringContainsString(
            '/img/pictures/lessthan10k',
            $this->picturePathResolver->getPath($persistedSubCategory->getPicture())
        );
    }
    public function testCreateValidSubCategoryAltPersisted()
    {
        $this->loginAdmin();
        $this->submitForm(
            'admin_subCategory_create', 
            'Ajouter', 
            $this->createValidData([
                'slug' => 'sous-categorie-avec-alt',
                'picture' => $this->createUploadedFile('lessthan200k.jpg'),
                'alt' => 'sub-category-alt'
            ])
        );
        /** @var SubCategory */
        $persistedSubCategory = $this->findEntity(SubCategoryRepository::class, ['slug' => 'sous-categorie-avec-alt']);
        $this->assertEquals('sub-category-alt', $persistedSubCategory->getPicture()->getAlt());
    }
    
    //update
    public function testUpdateWithInexistantIdParam()
    {
        $this->loginAdmin();
        $this->client->request('GET', $this->urlGenerator->generate('admin_subCategory_update', [
            'id' => '12345678944561256'
        ]));
        $this->assertResponseStatusCodeSame(Response::HTTP_INTERNAL_SERVER_ERROR);
    }
    public function testUpdateRender()
    {
        $this->loginAdmin();
        $subCategory = $this->findEntity(SubCategoryRepository::class);
        $this->client->request('GET', $this->urlGenerator->generate('admin_subCategory_update', [
            'id' => $subCategory->getId()
        ]));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Modifier la sous-catégorie "'.$subCategory->getName().'"');
    }
    public function testUpdateBreadcrumb()
    {
        $this->loginAdmin();
        $subCategory = $this->findEntity(SubCategoryRepository::class);
        $crawler = $this->client->request('GET', $this->urlGenerator->generate('admin_subCategory_update', [
            'id' => $subCategory->getId()
        ]));
        $this->assertBreadcrumbHomeLink($crawler);
        $this->assertBreadcrumbCategoryIndex($crawler);
        $this->assertSelectorTextContains('.breadcrumb-link:nth-child(3)', $subCategory->getParentCategory()->getName());
        $this->assertEquals(
            $this->urlGenerator->generate('admin_category_show', ['id' => $subCategory->getParentCategory()->getId()]),
            $crawler->filter('.breadcrumb-link:nth-child(3)')->attr('href')
        );
        $this->assertSelectorTextContains('.breadcrumb-link:nth-child(4)', $subCategory->getName());
        $this->assertEquals(
            $this->urlGenerator->generate('admin_subCategory_show', ['id' => $subCategory->getId()]),
            $crawler->filter('.breadcrumb-link:nth-child(4)')->attr('href')
        );
        $this->assertSelectorTextContains('.breadcrumb-item', 'Modifier');
    }
    public function testUpdateWithValidDataRedirectToIndex()
    {
        $this->loginAdmin();
        $subCategory = $this->findEntity(SubCategoryRepository::class);
        $this->submitForm(
            'admin_subCategory_update', 
            'Modifier', 
            $this->createValidData(),
            ['id' => $subCategory->getId()]
        );
        $this->assertResponseRedirects($this->urlGenerator->generate('admin_category_index'));
    }
    /**
     * Cette fonction teste à la fois le fait qu'on peut modifier une category sans changer son slug (constraint UniqueSlug), et aussi sans soumettre une picture
     */
    public function testUpdateWithoutChangeNothingWorks()
    {
        $this->loginAdmin();
        $subCategory = $this->findEntity(SubCategoryRepository::class);
        $this->submitForm(
            'admin_subCategory_update', 
            'Modifier', 
            [],
            ['id' => $subCategory->getId()]
        );
        $this->assertResponseRedirects($this->urlGenerator->generate('admin_category_index'));
    }
    public function testUpdateCorrectChanges()
    {
        $this->loginAdmin();
        $category = $this->findEntity(CategoryRepository::class, ['slug' => 'categorie-3']);
        $oldSubCategory = $this->findEntity(SubCategoryRepository::class);
        //on vérifie que la subCategory n'a pas cette category comme parente
        $this->assertNotSame($category->getId(), $oldSubCategory->getParentCategory()->getId(), 'il faut utiliser une autre parentCategory pour le test, car actuellement il ne prouve pas que la parentCategory est bien persistée');
        $this->submitForm(
            'admin_subCategory_update', 
            'Modifier', 
            [
                'sub_category[picture]' => $this->createUploadedFile('lessthan10k.jpg'),
                'sub_category[alt]' => 'updated-sub-category-alt',
                'sub_category[name]' => 'updated subcategory',
                'sub_category[slug]' => 'updated-subcategory',
                'sub_category[listPosition]' => '150',
                'sub_category[parentCategory]' => (string)($category->getId())
            ],
            ['id' => $oldSubCategory->getId()]
        );
        /** @var SubCategory */
        $updatedSubCategory = $this->findEntity(SubCategoryRepository::class, ['id' => $oldSubCategory->getId()]);
        $this->assertNotNull($updatedSubCategory);
        $this->assertStringContainsString(
            'lessthan10k', 
            $this->picturePathResolver->getPath($updatedSubCategory->getPicture())
        );
        $this->assertEquals('updated-sub-category-alt', $updatedSubCategory->getPicture()->getAlt());
        $this->assertEquals('updated subcategory', $updatedSubCategory->getName());
        $this->assertEquals('updated-subcategory', $updatedSubCategory->getSlug());
        $this->assertEquals(150, $updatedSubCategory->getListPosition());
        $this->assertEquals($category->getId(), $updatedSubCategory->getParentCategory()->getId());
    }
    public function testUpdateNotChangedValuesRemainUnchanged()
    {
        $this->loginAdmin();
        $oldSubCategory = $this->findEntity(SubCategoryRepository::class);
        $this->submitForm(
            'admin_subCategory_update', 
            'Modifier', 
            [
                'sub_category[slug]' => 'updated-sub-category-new',
            ],
            ['id' => $oldSubCategory->getId()]
        );
        /** @var SubCategory */
        $updatedSubCategory = $this->findEntity(SubCategoryRepository::class, ['id' => $oldSubCategory->getId()]);
        $this->assertEquals($oldSubCategory->getName(), $updatedSubCategory->getName());
        $this->assertEquals($oldSubCategory->getListPosition(), $updatedSubCategory->getListPosition());
        $this->assertEquals($oldSubCategory->getPicture(), $updatedSubCategory->getPicture());  // on ne compare surtout pas l'id car les pictures sont certainement null
        $this->assertEquals($oldSubCategory->getParentCategory()->getId(), $updatedSubCategory->getParentCategory()->getId());
    }
    public function testUpdateWithInvalidTooBigPicture()
    {
        $this->loginAdmin();
        $oldSubCategory = $this->findEntity(SubCategoryRepository::class); 
        $this->submitForm(
            'admin_subCategory_update', 
            'Modifier', 
            [
                'sub_category[picture]' => $this->createUploadedFile('morethan2500k.jpg'),
            ],
            ['id' => $oldSubCategory->getId()]
        );
        $this->assertSelectorTextContains('.picture-group .admin-form-error', 'Image trop lourde');
    }
    public function testUpdateWithInvalidBadFormatPicture()
    {
        $this->loginAdmin();
        $oldSubCategory = $this->findEntity(SubCategoryRepository::class); 
        $this->submitForm(
            'admin_subCategory_update', 
            'Modifier', 
            [
                'sub_category[picture]' => $this->createUploadedFile('badformat.txt'),
            ],
            ['id' => $oldSubCategory->getId()]
        );
        $this->assertSelectorTextContains('.picture-group .admin-form-error', 'Format requis');
    }
    public function testUpdateInvalidSubCategoryWithExistantSlugAndSameParentCategory()
    {
        $this->loginAdmin();
        //les parentCategory des subCategory ont toujours au moins 2 subCategories
        $oldSubCategory = $this->findEntity(SubCategoryRepository::class); 
        $parentCategory = $oldSubCategory->getParentCategory();
        foreach($parentCategory->getSubCategories() as $subCategory)
        {
            if($subCategory->getId() !== $oldSubCategory->getId())
            {
                $existantSlug = $subCategory->getSlug();
            }
        }
        $this->submitForm(
            'admin_subCategory_update', 
            'Modifier', 
            [
                'sub_category[slug]' => $existantSlug
            ],
            ['id' => $oldSubCategory->getId()]
        );
        $this->assertSelectorTextContains('.slug-group .admin-form-error', 'déjà utilisé');
    }
    public function testUpdateInvalidSubCategoryWithExistantListPositionAndSameParentCategory()
    {
        $this->loginAdmin();
        //les parentCategory des subCategory ont toujours au moins 2 subCategories
        $oldSubCategory = $this->findEntity(SubCategoryRepository::class); 
        $parentCategory = $oldSubCategory->getParentCategory();
        foreach($parentCategory->getSubCategories() as $subCategory)
        {
            if($subCategory->getId() !== $oldSubCategory->getId())
            {
                $existantListPosition = $subCategory->getListPosition();
            }
        }
        $this->submitForm(
            'admin_subCategory_update', 
            'Modifier', 
            [
                'sub_category[listPosition]' => $existantListPosition
            ],
            ['id' => $oldSubCategory->getId()]
        );
        $this->assertSelectorTextContains('.listPosition-group .admin-form-error', 'déjà utilisée');
    }


    //delete
    public function testDeleteWithInexistantIdParam()
    {
        $this->loginAdmin();
        $this->client->request('POST', $this->urlGenerator->generate('admin_subCategory_delete'), [
            'id' => 123456789456123456
        ]);
        $this->assertResponseStatusCodeSame(Response::HTTP_INTERNAL_SERVER_ERROR);
    }
    public function testDeleteWithValidParamsRedirectToIndex()
    {
        $this->loginAdmin();
        $subCategory = $this->findEntity(SubCategoryRepository::class);
        $id = $subCategory->getId();
        $this->client->request('POST', $this->urlGenerator->generate('admin_subCategory_delete'), [
            'id' => $id
        ]);
        $this->assertResponseRedirects($this->urlGenerator->generate('admin_category_index'));
    }
    public function testDeleteWillDeleteCorrectSubCategory()
    {
        $this->loginAdmin();
        $subCategory = $this->findEntity(SubCategoryRepository::class);
        $id = $subCategory->getId();
        $this->client->request('POST', $this->urlGenerator->generate('admin_subCategory_delete'), [
            'id' => $id
        ]);
        $this->assertNull(
            $this->findEntity(SubCategoryRepository::class, ['id' => $id])
        );
    }
    public function testDeleteWillDeletePictureToo()
    {
        $this->loginAdmin();
        //on crée une subCategory avec une picture
        $this->submitForm(
            'admin_subCategory_create', 
            'Ajouter', 
            $this->createValidData([
                'slug' => 'test-create-valid-subcategory-with-picture',
                'picture' => $this->createUploadedFile('lessthan10k.jpg'),
            ])
        );
        //on récupère la subCategory persistée et la picture persistée également
        $persistedSubCategory = $this->findEntity(SubCategoryRepository::class, ['slug' => 'test-create-valid-subcategory-with-picture']);
        $pictureId = $persistedSubCategory->getPicture()->getId();
        //on vérifie qu'on peut bien retrouver la picture *
        $this->assertNotNull(
            $this->findEntity(PictureRepository::class, ['id' => $pictureId])
        );
        //on supprime la subCategory
        $this->client->request('POST', $this->urlGenerator->generate('admin_subCategory_delete'), [
            'id' => $persistedSubCategory->getId()
        ]);
        //* on vérifie qu'on ne peut plus retrouver la picture
        $this->assertNull(
            $this->findEntity(PictureRepository::class, ['id' => $pictureId])
        );
    }
   
    //show
    public function testShowWithInexistantIdParam()
    {
        $this->loginAdmin();
        $this->client->request('GET', $this->urlGenerator->generate('admin_subCategory_show', [
            'id' => '12345678944561256'
        ]));
        $this->assertResponseStatusCodeSame(Response::HTTP_INTERNAL_SERVER_ERROR);
    }
    public function testShowRender()
    {
        $this->loginAdmin();
        $subCategory = $this->findEntity(SubCategoryRepository::class);
        $this->client->request('GET', $this->urlGenerator->generate('admin_subCategory_show', [
            'id' => $subCategory->getId()
        ]));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', $subCategory->getName());
    }
    public function testShowContainsCorrectUpdateButton()
    {
        $this->loginAdmin();
        $subCategory = $this->findEntity(SubCategoryRepository::class);
        $crawler = $this->client->request('GET', $this->urlGenerator->generate('admin_subCategory_show', [
            'id' => $subCategory->getId()
        ]));
        $this->assertEquals(
            $this->urlGenerator->generate('admin_subCategory_update', ['id' => $subCategory->getId()]),
            $crawler->filter('.admin-buttons-wrapper .admin-button:first-child')->attr('href')
        );
    }
    public function testShowContainsCorrectDeleteButton()
    {
        $this->loginAdmin();
        $subCategory = $this->findEntity(SubCategoryRepository::class);
        $crawler = $this->client->request('GET', $this->urlGenerator->generate('admin_subCategory_show', [
            'id' => $subCategory->getId()
        ]));
        $deleteForm = $crawler->filter('.admin-buttons-wrapper form');
        $deleteButton = $crawler->filter('.admin-buttons-wrapper form .admin-button');
        $this->assertEquals(
            $this->urlGenerator->generate('admin_subCategory_delete'),
            $deleteForm->attr('action')
        );
        $this->assertEquals('id', $deleteButton->attr('name'));
        $this->assertEquals($subCategory->getId(), $deleteButton->attr('value'));
    }
    public function testShowBreadcrumb()
    {
        $this->loginAdmin();
        $subCategory = $this->findEntity(SubCategoryRepository::class);
        $crawler = $this->client->request('GET', $this->urlGenerator->generate('admin_subCategory_show', [
            'id' => $subCategory->getId()
        ]));
        $this->assertBreadcrumbHomeLink($crawler);
        $this->assertBreadcrumbCategoryIndex($crawler);
        $this->assertSelectorTextContains('.breadcrumb-link:nth-child(3)', $subCategory->getParentCategory()->getName());
        $this->assertEquals(
            $this->urlGenerator->generate('admin_category_show', ['id' => $subCategory->getParentCategory()->getId()]),
            $crawler->filter('.breadcrumb-link:nth-child(3)')->attr('href')
        );
        $this->assertSelectorTextContains('.breadcrumb-item', $subCategory->getName());
    }
    public function testShowContainsCorrectSections()
    {
        $this->loginAdmin();
        $subCategory = $this->findEntity(SubCategoryRepository::class, ['slug' => 'sous-categorie-3']); // subcategory avec une picture

        $this->client->request('GET', $this->urlGenerator->generate('admin_subCategory_show', [
            'id' => $subCategory->getId()
        ]));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.picture');
        $this->assertSelectorTextContains('.pictureAlt', $subCategory->getPicture()->getAlt());
        $this->assertSelectorTextContains('.name', $subCategory->getName());
        $this->assertSelectorTextContains('.slug', $subCategory->getSlug());
        $this->assertSelectorTextContains('.parentCategory', $subCategory->getParentCategory()->getName());
    }
    


    private function createValidData(array $options = []):array
    {
        $category = $this->findEntity(CategoryRepository::class);
        $subCategoryData = [
            'sub_category[picture]' => $this->createUploadedFile('lessthan200k.jpg'),
            'sub_category[alt]' => 'texte alternatif de sous catégorie',
            'sub_category[name]' => 'nouvelle sous-categorie',
            'sub_category[slug]' => 'nouvelle-sous-categorie',
            'sub_category[parentCategory]' => $category->getId(),
            'sub_category[listPosition]' => '100'
        ];
        foreach($options as $key => $value)
        {
            $subCategoryData['sub_category['.$key.']'] = $value;
        }
        return $subCategoryData;
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
    private function assertBreadcrumbSubCategoryIndex(Crawler $crawler): void
    {
        $this->assertSelectorTextContains('.breadcrumb-link:nth-child(3)', 'Sous-catégories');
        $count = $this->client->getContainer()->get(SubCategoryRepository::class)->count([]);
        $this->assertSelectorTextContains('.breadcrumb-link:nth-child(3)', $count);
        $this->assertEquals(
            $this->urlGenerator->generate('admin_category_index'),
            $crawler->filter('.breadcrumb-link:nth-child(3)')->attr('href')
        );
    }
    
}