<?php
namespace App\Tests\Functional\Controller\Admin\Api;

use stdClass;
use App\Repository\CategoryRepository;
use App\DataFixtures\Tests\UserTestFixtures;
use Symfony\Component\HttpFoundation\Response;
use App\DataFixtures\Tests\ProductTestFixtures;
use App\Tests\Functional\Controller\Admin\AdminFunctionalTest;



/**
 * @group FunctionalAdmin
 * @group FunctionalAdminApi
 */
class ApiAdminCategoryControllerTest extends AdminFunctionalTest
{

    public function setUp(): void 
    {
        parent::setUp();

        $this->loadFixtures([ProductTestFixtures::class, UserTestFixtures::class]);  // userTestFixtures pour le loginUser et loginAdmin
    }


    //auth
    public function testRedirectToLoginWhenUserNotLogged()
    {
        $category = $this->findEntity(CategoryRepository::class, ['slug' => 'categorie-1']);  // category avec subCategories
        $this->client->request('GET', $this->urlGenerator->generate('admin_api_category_findSubCategoryIdsByCategoryId', [
            'id' => $category->getId()
        ]));
        $this->assertResponseRedirects($this->urlGenerator->generate('security_login'));
    }
    public function testUserCannotAccess()
    {
        $this->loginUser();
        $category = $this->findEntity(CategoryRepository::class, ['slug' => 'categorie-1']);  // category avec subCategories
        $this->client->request('GET', $this->urlGenerator->generate('admin_api_category_findSubCategoryIdsByCategoryId', [
            'id' => $category->getId()
        ]));
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }
    public function testAdminCanAccess()
    {
        $this->loginAdmin();
        $category = $this->findEntity(CategoryRepository::class, ['slug' => 'categorie-1']);  // category avec subCategories
        $this->client->request('GET', $this->urlGenerator->generate('admin_api_category_findSubCategoryIdsByCategoryId', [
            'id' => $category->getId()
        ]));
        $this->assertResponseIsSuccessful();
    }

    //findSubCategoryIdsByCategoryId
    public function testFindSubCategoryIdsByCategoryIdWithInexistantCategoryId()
    {
        $this->loginAdmin();
        $this->client->request('GET', $this->urlGenerator->generate('admin_api_category_findSubCategoryIdsByCategoryId', [
            'id' => 123456789
        ]));
        $this->assertResponseStatusCodeSame(Response::HTTP_INTERNAL_SERVER_ERROR);
        $responseContent = json_decode($this->client->getResponse()->getContent());
        $this->assertNotNull($responseContent->errors);
    }
    public function testFindSubCategoryIdsByCategoryId()
    {
        $this->loginAdmin();
        $category = $this->findEntity(CategoryRepository::class);
        $this->client->request('GET', $this->urlGenerator->generate('admin_api_category_findSubCategoryIdsByCategoryId', [
            'id' => $category->getId()
        ]));
        $this->assertResponseIsSuccessful();
    }
    public function testFindSubCategoryIdsByCategoryIdReturnArrayOfInt()
    {
        $this->loginAdmin();
        $category = $this->findEntity(CategoryRepository::class, ['slug' => 'categorie-1']);  // category avec subCategories
        $this->client->request('GET', $this->urlGenerator->generate('admin_api_category_findSubCategoryIdsByCategoryId', [
            'id' => $category->getId()
        ]));
        $subCategoryIds = json_decode($this->client->getResponse()->getContent());
        $this->assertIsArray($subCategoryIds);
        foreach($subCategoryIds as $subCategoryId)
        {
            $this->assertIsInt($subCategoryId);
        }
    }
    public function testFindSubCategoryIdsByCategoryIdReturnCorrectCountSubCategories()
    {
        $this->loginAdmin();
        $category = $this->findEntity(CategoryRepository::class, ['slug' => 'categorie-1']);  // category avec subCategories
        $this->client->request('GET', $this->urlGenerator->generate('admin_api_category_findSubCategoryIdsByCategoryId', [
            'id' => $category->getId()
        ]));
        $subCategoryIds = json_decode($this->client->getResponse()->getContent());
        $this->assertCount(3, $subCategoryIds);
    }
    public function testFindSubCategoryIdsByCategoryIdReturnCorrectSubCategories()
    {
        $this->loginAdmin();
        $category = $this->findEntity(CategoryRepository::class, ['slug' => 'categorie-1']);  // category avec subCategories
        $this->client->request('GET', $this->urlGenerator->generate('admin_api_category_findSubCategoryIdsByCategoryId', [
            'id' => $category->getId()
        ]));
        $subCategoryIds = json_decode($this->client->getResponse()->getContent());
        foreach($category->getSubCategories() as $subCategory)
        {
            $this->assertContains($subCategory->getId(), $subCategoryIds);
        }
    }


    public function testFindCategoryWithInexistantId()
    {
        $this->loginAdmin();
        $this->client->request('GET', $this->urlGenerator->generate('admin_api_category_findCategory', [
            'id' => 123456789
        ]));
        $this->assertResponseStatusCodeSame(Response::HTTP_INTERNAL_SERVER_ERROR);
        $responseContent = json_decode($this->client->getResponse()->getContent());
        $this->assertNotNull($responseContent->errors);
    }
    public function testFindCategory()
    {
        $this->loginAdmin();
        $category = $this->findEntity(CategoryRepository::class);
        $this->client->request('GET', $this->urlGenerator->generate('admin_api_category_findCategory', [
            'id' => $category->getId()
        ]));
        $this->assertResponseIsSuccessful();
    }
    public function testFindCategoryReturnStdClass()
    {
        $this->loginAdmin();
        $category = $this->findEntity(CategoryRepository::class);
        $this->client->request('GET', $this->urlGenerator->generate('admin_api_category_findCategory', [
            'id' => $category->getId()
        ]));
        $data = json_decode($this->client->getResponse()->getContent());
        $this->assertInstanceOf(stdClass::class, $data);
    }
    public function testFindCategoryReturnCorrectCategoryValues()
    {
        $this->loginAdmin();
        $category = $this->findEntity(CategoryRepository::class);
        $this->client->request('GET', $this->urlGenerator->generate('admin_api_category_findCategory', [
            'id' => $category->getId()
        ]));
        $data = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals($category->getId(), $data->id);
        $this->assertEquals($category->getListPosition(), $data->listPosition);
        $this->assertNotNull($data->target);
        $this->assertNotNull($data->subCategories);
    }
    public function testFindCategoryReturnCorrectSubCategories()
    {
        $this->loginAdmin();
        $category = $this->findEntity(CategoryRepository::class, ['slug' => 'categorie-1']);  // category avec subCategories
        $this->client->request('GET', $this->urlGenerator->generate('admin_api_category_findCategory', [
            'id' => $category->getId()
        ]));
        $data = json_decode($this->client->getResponse()->getContent());
        $this->assertCount($category->getSubCategories()->count(), $data->subCategories);
        $this->assertEquals($category->getSubCategories()->get(0)->getId(), $data->subCategories[0]->id);
        $this->assertEquals($category->getSubCategories()->get(1)->getId(), $data->subCategories[1]->id);
    }
}