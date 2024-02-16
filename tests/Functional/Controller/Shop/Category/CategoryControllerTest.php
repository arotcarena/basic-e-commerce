<?php
namespace App\Tests\Functional\Controller\Shop\Category;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use App\Tests\Functional\FunctionalTest;
use Symfony\Component\HttpFoundation\Response;
use App\DataFixtures\Tests\CategoryTestFixtures;



/**
 * @group FunctionalShop
 */
class CategoryControllerTest extends FunctionalTest
{
    private Category $category;

    public function setUp(): void 
    {
        parent::setUp();

        $this->loadFixtures([CategoryTestFixtures::class]);
        $this->category = $this->findEntity(CategoryRepository::class);
    }

    public function testShowPageRender()
    {
        $this->client->request('GET', $this->urlGenerator->generate('category_show', [
            'slug' => $this->category->getSlug()
        ]));
        $this->assertResponseIsSuccessful();
    }
    public function testShowWithInexistantCategorySlugParam()
    {
        $this->client->request('GET', $this->urlGenerator->generate('category_show', [
            'slug' => 'slug-inexistant'
        ]));
        $this->assertResponseStatusCodeSame(Response::HTTP_INTERNAL_SERVER_ERROR);
    }
    public function testShowContainsCorrectBreadCrumb()
    {
        $this->client->request('GET', $this->urlGenerator->generate('category_show', [
            'slug' => $this->category->getSlug()
        ]));
        $this->assertSelectorTextContains('.breadcrumb-item', $this->category->getName());
        $this->assertSelectorTextContains('.breadcrumb-home-link', 'Accueil');
    }
    
}