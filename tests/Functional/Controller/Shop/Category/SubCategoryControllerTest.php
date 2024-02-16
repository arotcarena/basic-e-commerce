<?php
namespace App\Tests\Functional\Controller\Shop\Category;

use App\Entity\SubCategory;
use App\Tests\Functional\FunctionalTest;
use App\Repository\SubCategoryRepository;
use Symfony\Component\HttpFoundation\Response;
use App\DataFixtures\Tests\CategoryTestFixtures;


/**
 * @group FunctionalShop
 */
class SubCategoryControllerTest extends FunctionalTest
{
    private SubCategory $subCategory;

    public function setUp(): void 
    {
        parent::setUp();

        $this->loadFixtures([CategoryTestFixtures::class]);
        $this->subCategory = $this->findEntity(SubCategoryRepository::class);
    }

    public function testShowPageRender()
    {
        $this->client->request('GET', $this->urlGenerator->generate('subCategory_show', [
            'categorySlug' => $this->subCategory->getParentCategory()->getSlug(),
            'subCategorySlug' => $this->subCategory->getSlug()
        ]));
        $this->assertResponseIsSuccessful();
    }
    public function testShowWithInexistantCategorySlugParam()
    {
        $this->client->request('GET', $this->urlGenerator->generate('subCategory_show', [
            'categorySlug' => 'slug-inexistant',
            'subCategorySlug' => $this->subCategory->getSlug()
        ]));
        $this->assertResponseStatusCodeSame(Response::HTTP_INTERNAL_SERVER_ERROR);
    }
    public function testShowWithInexistantSubCategorySlugParam()
    {
        $this->client->request('GET', $this->urlGenerator->generate('subCategory_show', [
            'categorySlug' => $this->subCategory->getParentCategory()->getSlug(),
            'subCategorySlug' => 'slug-inexistant'
        ]));
        $this->assertResponseStatusCodeSame(Response::HTTP_INTERNAL_SERVER_ERROR);
    }
    public function testShowContainsCorrectBreadCrumb()
    {
        $this->client->request('GET', $this->urlGenerator->generate('subCategory_show', [
            'categorySlug' => $this->subCategory->getParentCategory()->getSlug(),
            'subCategorySlug' => $this->subCategory->getSlug()
        ]));
        $this->assertSelectorTextContains('.breadcrumb-home-link', 'Accueil');
        $this->assertSelectorTextContains('.breadcrumb-link', $this->subCategory->getParentCategory()->getName());
        $this->assertSelectorTextContains('.breadcrumb-item', $this->subCategory->getName());
    }
}