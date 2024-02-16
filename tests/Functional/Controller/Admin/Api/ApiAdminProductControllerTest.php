<?php
namespace App\Tests\Functional\Controller\Admin\Api;

use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Response;
use App\DataFixtures\Tests\ProductTestFixtures;
use App\DataFixtures\Tests\UserTestFixtures;
use App\Tests\Functional\Controller\Admin\AdminFunctionalTest;
use stdClass;



/**
 * @group FunctionalAdmin
 * @group FunctionalAdminApi
 */
class ApiAdminProductControllerTest extends AdminFunctionalTest
{

    public function setUp(): void 
    {
        parent::setUp();

        $this->loadFixtures([ProductTestFixtures::class, UserTestFixtures::class]);
    }

    public function testRedirectToLoginWhenUserNotLogged()
    {
        $product = $this->findEntity(ProductRepository::class, ['slug' => 'product-with-suggested-products']);
        $this->client->request('GET', $this->urlGenerator->generate('admin_api_product_suggestedProducts', [
            'id' => $product->getId()
        ]));
        $this->assertResponseRedirects($this->urlGenerator->generate('security_login'));
    }
    public function testUserCannotAccess()
    {
        $this->loginUser();
        $product = $this->findEntity(ProductRepository::class, ['slug' => 'product-with-suggested-products']);
        $this->client->request('GET', $this->urlGenerator->generate('admin_api_product_suggestedProducts', [
            'id' => $product->getId()
        ]));
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }
    public function testAdminCanAccess()
    {
        $this->loginAdmin();
        $product = $this->findEntity(ProductRepository::class, ['slug' => 'product-with-suggested-products']);
        $this->client->request('GET', $this->urlGenerator->generate('admin_api_product_suggestedProducts', [
            'id' => $product->getId()
        ]));
        $this->assertResponseIsSuccessful();
    }
    public function testSuggestedProductsWithInexistantProductId()
    {
        $this->loginAdmin();
        $this->client->request('GET', $this->urlGenerator->generate('admin_api_product_suggestedProducts', [
            'id' => 123456789
        ]));
        $this->assertResponseStatusCodeSame(Response::HTTP_INTERNAL_SERVER_ERROR);
        $responseContent = json_decode($this->client->getResponse()->getContent());
        $this->assertNotNull($responseContent->errors);
    }
    public function testSuggestedProductsReturnArrayOfStdClass()
    {
        $this->loginAdmin();
        $product = $this->findEntity(ProductRepository::class, ['slug' => 'product-with-suggested-products']);
        $this->client->request('GET', $this->urlGenerator->generate('admin_api_product_suggestedProducts', [
            'id' => $product->getId()
        ]));
        $suggestedProducts = json_decode($this->client->getResponse()->getContent());
        foreach($suggestedProducts as $suggestedProduct)
        {
            $this->assertInstanceOf(stdClass::class, $suggestedProduct);
        }
    }
    public function testSuggestedProductsReturnCorrectCount()
    {
        $this->loginAdmin();
        $product = $this->findEntity(ProductRepository::class, ['slug' => 'product-with-suggested-products']);
        $this->client->request('GET', $this->urlGenerator->generate('admin_api_product_suggestedProducts', [
            'id' => $product->getId()
        ]));
        $suggestedProducts = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals(
            count($product->getSuggestedProducts()), 
            count($suggestedProducts)
        );
    }
    public function testSuggestedProductsReturnCorrectProducts()
    {
        $this->loginAdmin();
        $product = $this->findEntity(ProductRepository::class, ['slug' => 'product-with-suggested-products']);
        $this->client->request('GET', $this->urlGenerator->generate('admin_api_product_suggestedProducts', [
            'id' => $product->getId()
        ]));
        $suggestedProducts = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals(
            $product->getSuggestedProducts()->get(0)->getId(), $suggestedProducts[0]->id
        );
        $this->assertEquals(
            $product->getSuggestedProducts()->get(1)->getId(), $suggestedProducts[1]->id
        );
    }
}