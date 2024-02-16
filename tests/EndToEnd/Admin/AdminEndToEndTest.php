<?php
namespace App\Tests\EndToEnd\Admin;

use App\Tests\EndToEnd\EndToEndTest;
use App\DataFixtures\Tests\UserTestFixtures;

abstract class AdminEndToEndTest extends EndToEndTest
{
    public function setUp(): void 
    {
        parent::setUp();

        $this->loadFixtures([UserTestFixtures::class]);
    }

    protected function loginAdmin()
    {
        // on logout d'abord car si on est déjà loggé la page security_login n'est pas accessible
        $this->client->request('GET', $this->urlGenerator->generate('security_logout'));

        $crawler = $this->client->request('GET', $this->urlGenerator->generate('security_login'));
        $form = $crawler->selectButton('Se connecter')->form([
            'email' => 'admin@gmail.com',
            'password' => 'password'
        ]);
        $this->client->submit($form);
        $this->client->waitFor('.alert.alert-success', 3);
    }
}