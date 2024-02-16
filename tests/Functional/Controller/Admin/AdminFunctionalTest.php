<?php
namespace App\Tests\Functional\Controller\Admin;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Tests\Functional\FunctionalTest;
use App\DataFixtures\Tests\UserTestFixtures;
use App\Tests\Utils\FormTrait;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\File\UploadedFile;


abstract class AdminFunctionalTest extends FunctionalTest
{
    use FormTrait;

    public function setUp(): void 
    {
        parent::setUp();

        $this->loadFixtures([UserTestFixtures::class]);
    }

    protected function loginAdmin()
    {
        /** @var User */
        $admin = $this->findEntity(UserRepository::class, ['email' => 'admin@gmail.com']);
        $this->client->loginUser($admin);
    }
    protected function loginUser()
    {
        /** @var User */
        $confirmedUser = $this->findEntity(UserRepository::class, ['email' => 'confirmed_user@gmail.com']);
        $this->client->loginUser($confirmedUser);
    }

    protected function submitForm(string $route, string $button, array $data, array $routeParams = null)
    {
        $url = $routeParams ? $this->urlGenerator->generate($route, $routeParams): $this->urlGenerator->generate($route);
        $crawler = $this->client->request('GET', $url);
        $form = $crawler->selectButton($button)->form($data);
        $this->client->submit($form);
    }
    protected function createUploadedFile(string $file): UploadedFile
    {
        return new UploadedFile($this->client->getKernel()->getProjectDir().'\public\img\test\\'.$file, $file);
    }

    protected function assertBreadcrumbHomeLink(Crawler $crawler): void
    {
        $this->assertSelectorTextContains('.breadcrumb-home-link', 'Administration');
        $this->assertEquals(
            $this->urlGenerator->generate('admin_home'),
            $crawler->filter('.breadcrumb-home-link')->attr('href')
        );
    }

}