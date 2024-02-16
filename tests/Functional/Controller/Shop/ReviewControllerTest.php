<?php
namespace App\Tests\Functional\Shop;

use App\Convertor\PurchaseLineProductConvertor;
use App\DataFixtures\Tests\ProductTestFixtures;
use App\Entity\Purchase;
use InvalidArgumentException;
use App\Repository\UserRepository;
use App\Repository\ProductRepository;
use App\Repository\PurchaseRepository;
use App\Tests\Functional\FunctionalTest;
use App\Tests\Functional\LoginUserTrait;
use Symfony\Component\HttpFoundation\Response;
use App\DataFixtures\Tests\PurchaseTestFixtures;
use App\DataFixtures\Tests\UserWithNoPurchaseTestFixtures;
use App\Entity\Product;
use App\Service\UserBoughtProductVerificator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Exception\MissingMandatoryParametersException;

/**
 * @group FunctionalShop
 */
class ReviewControllerTest extends FunctionalTest
{
    use LoginUserTrait;

    public function setUp(): void
    {
        parent::setUp();

        $this->loadFixtures([PurchaseTestFixtures::class]);  // depends on UserTestFixtures & ProductTestFixtures
    }

    //auth
    public function testNotLoggedUserCannotAccess()
    {
        $product = $this->findEntity(ProductRepository::class);
        $this->client->request('GET', $this->urlGenerator->generate('review_create', ['productSlug' => $product->getSlug(), 'publicRef' => $product->getPublicRef()]));
        $this->assertResponseRedirects($this->urlGenerator->generate('security_login'));
    }
    public function testUserCanAccess()
    {
        /** @var Purchase */
        $purchase = $this->findEntity(PurchaseRepository::class);
        $user = $purchase->getUser();
        $productArray = $purchase->getPurchaseLines()->get(0)->getProduct();
        $product = $this->findEntity(ProductRepository::class, ['id' => $productArray['id']]);
        
        $this->loginUser($user);

        $this->client->request('GET', $this->urlGenerator->generate('review_create', ['productSlug' => $product->getSlug(), 'publicRef' => $product->getPublicRef()]));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Laisser un avis');
    }

    //create
    public function testCreateWithNoProductParam()
    {
        $this->loginUser();
        $this->expectException(MissingMandatoryParametersException::class);
        $this->client->request('GET', $this->urlGenerator->generate('review_create'));
    }
    public function testCreateWithInexistantProductParam()
    {
        $this->loginUser();
        $this->client->request('GET', $this->urlGenerator->generate('review_create', ['productSlug' => 'unslugquinexistepas', 'publicRef' => 'unerefquinexistepas']));
        $this->assertResponseStatusCodeSame(Response::HTTP_INTERNAL_SERVER_ERROR);
    }
    public function testCreateWithUserThatDidntBuyAnyProduct()
    {
        $this->loadFixtures([UserWithNoPurchaseTestFixtures::class, ProductTestFixtures::class]);
        $user = $this->findEntity(UserRepository::class, ['email' => 'user_with_no_purchase@gmail.com']);

        $this->loginUser($user);
        $product = $this->findEntity(ProductRepository::class);

        $this->client->request('GET', $this->urlGenerator->generate('review_create', ['productSlug' => $product->getSlug(), 'publicRef' => $product->getPublicRef()]));
        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }
    public function testCreateWithUserThatDidntBuyThisProduct()
    {
        $this->loadFixtures([UserWithNoPurchaseTestFixtures::class, ProductTestFixtures::class]);
        $user = $this->findEntity(UserRepository::class, ['email' => 'user_with_specific_purchase@gmail.com']);
        $this->loginUser($user);
        $product = $this->getProductWithDifferentSlug('product-for-specific-purchase-user');

        $this->assertFalse((new UserBoughtProductVerificator(new PurchaseLineProductConvertor))->verify($user, $product, 'le test ne sera pas concluant car le user a acheté le product. Il faut changer de user ou product'));

        $this->client->request('GET', $this->urlGenerator->generate('review_create', ['productSlug' => $product->getSlug(), 'publicRef' => $product->getPublicRef()]));
        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }
    public function testCreateContainsCorrectProductInfos()
    {
        /** @var Purchase */
        $purchase = $this->findEntity(PurchaseRepository::class);
        $user = $purchase->getUser();
        $productArray = $purchase->getPurchaseLines()->get(0)->getProduct();
        $product = $this->findEntity(ProductRepository::class, ['id' => $productArray['id']]);
        
        $this->loginUser($user);

        $this->client->request('GET', $this->urlGenerator->generate('review_create', ['productSlug' => $product->getSlug(), 'publicRef' => $product->getPublicRef()]));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Laisser un avis', 'Le titre ne contient pas "Laisser un avis"');
        $this->assertSelectorTextContains('h1', $product->getDesignation(), 'Le titre ne contient pas ou pas le bon nom de produit');
    }
    public function testCreateSubmitInvalidData()
    {
        /** @var Purchase */
        $purchase = $this->findEntity(PurchaseRepository::class);
        $user = $purchase->getUser();
        $productArray = $purchase->getPurchaseLines()->get(0)->getProduct();
        $product = $this->findEntity(ProductRepository::class, ['id' => $productArray['id']]);
        
        $this->loginUser($user);

        $crawler = $this->client->request('GET', $this->urlGenerator->generate('review_create', ['productSlug' => $product->getSlug(), 'publicRef' => $product->getPublicRef()]));
        $this->expectException(InvalidArgumentException::class);
        $form = $crawler->selectButton('Valider')->form([
            'review[fullName]' => 'Jean Paul',
            'review[rate]' => '7',
            'review[comment]' => 'Très bien ce produit'
        ]);
    }
    public function testCreateSubmitValidDataRedirects()
    {
        /** @var Purchase */
        $purchase = $this->findEntity(PurchaseRepository::class);
        $user = $purchase->getUser();
        $productArray = $purchase->getPurchaseLines()->get(0)->getProduct();
        $product = $this->findEntity(ProductRepository::class, ['id' => $productArray['id']]);
        
        $this->loginUser($user);

        $crawler = $this->client->request('GET', $this->urlGenerator->generate('review_create', ['productSlug' => $product->getSlug(), 'publicRef' => $product->getPublicRef()]));
        $form = $crawler->selectButton('Valider')->form($this->createValidReviewData());
        $this->client->submit($form);
        $this->assertResponseRedirects($this->urlGenerator->generate('home'));
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert.alert-success');
    }
    public function testCreateSubmitValidDataCorrectPersist()
    {
        /** @var Purchase */
        $purchase = $this->findEntity(PurchaseRepository::class);
        $user = $purchase->getUser();
        $productArray = $purchase->getPurchaseLines()->get(0)->getProduct();
        $product = $this->findEntity(ProductRepository::class, ['id' => $productArray['id']]);
        
        $this->loginUser($user);

        $crawler = $this->client->request('GET', $this->urlGenerator->generate('review_create', ['productSlug' => $product->getSlug(), 'publicRef' => $product->getPublicRef()]));
        $form = $crawler->selectButton('Valider')->form($this->createValidReviewData());
        $this->client->submit($form);
        $product = $this->findEntity(ProductRepository::class, ['id' => $product->getId()]);
        $this->assertEquals(
            'Jean Paul',
            $product->getReviews()->get(0)->getFullName()
        );
        $this->assertEquals(
            '3',
            $product->getReviews()->get(0)->getRate()
        );
        $this->assertEquals(
            'Très bien ce produit',
            $product->getReviews()->get(0)->getComment()
        );
    }

    private function createValidReviewData()
    {
        return [
            'review[fullName]' => 'Jean Paul',
            'review[rate]' => '3',
            'review[comment]' => 'Très bien ce produit'
        ];
    }

    private function getProductWithDifferentSlug(string $slug):Product
    {
        /** @var EntityManagerInterface */
        $em = $this->client->getContainer()->get(EntityManagerInterface::class);
        return $em->createQueryBuilder('p')
                    ->select('p')
                    ->from('App\Entity\Product', 'p')
                    ->where('p.slug != :slug')
                    ->setParameter('slug', $slug)
                    ->getQuery()
                    ->getResult()[0]
                    ;
    }
}