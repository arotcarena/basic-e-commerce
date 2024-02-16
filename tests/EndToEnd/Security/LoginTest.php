<?php
namespace App\Tests\EndToEnd\Security;

use App\DataFixtures\Tests\UserTestFixtures;
use App\Tests\EndToEnd\EndToEndTest;
use App\Tests\Utils\UserFixturesTrait;
use Facebook\WebDriver\WebDriverBy;
use Symfony\Component\BrowserKit\Cookie;

class LoginTest extends EndToEndTest
{

    use UserFixturesTrait;

    public function testPasswordForgottenLink()
    {
        //on logout avant au cas ou car si on est loggé on ne peut pas accéder à cette page
        $this->client->request('GET', $this->urlGenerator->generate('security_logout'));
        
        $crawler = $this->client->request('GET', $this->urlGenerator->generate('security_login'));
        $crawler->findElement(WebDriverBy::linkText('Mot de passe oublié ?'))->click();
        $this->client->waitForElementToContain('label', 'Entrez votre adresse email', 3);
        $this->assertSelectorTextContains('label', 'Entrez votre adresse email');
    }

    public function testRegisterLink()
    {
        //on logout avant au cas ou car si on est loggé on ne peut pas accéder à cette page
        $this->client->request('GET', $this->urlGenerator->generate('security_logout'));

        $crawler = $this->client->request('GET', $this->urlGenerator->generate('security_login'));
        $crawler->findElement(WebDriverBy::linkText('Pas encore inscrit ? Cliquez ici pour créer un compte'))->click();
        $this->client->waitForElementToContain('.auth-form-button', 'Créer un compte', 3);
        $this->assertSelectorTextContains('.auth-form-button', 'Créer un compte');
    }

    public function testRememberMe()
    {
        $this->loginAndDestroySession(true);

        /** on essaie de se rendre sur une page qui nécessite d'être authentifié */
        $this->client->request('GET', $this->urlGenerator->generate('security_changePassword'));
        /** on doit pouvoir accéder */
        $this->client->waitForElementToContain('label', 'ncien mot de passe', 3);
        $this->assertSelectorTextContains('label', 'ncien mot de passe');
    }
    public function testDontRememberMe()
    {
        $this->loginAndDestroySession(false);

        /** on essaie de se rendre sur une page qui nécessite d'être authentifié */
        $this->client->request('GET', $this->urlGenerator->generate('security_changePassword'));
        /** on doit être redirigé vers login */
        $this->client->waitForElementToContain('.auth-form-button', 'Se connecter', 3);
        $this->assertSelectorTextContains('.auth-form-button', 'Se connecter');
    }

    private function loginAndDestroySession(bool $clickRememberMe)
    {
        $this->loadFixtures([UserTestFixtures::class]);
        $user = $this->findUserByEmail('confirmed_user@gmail.com');

        // on logout d'abord car si on est déjà loggé la page security_login n'est pas accessible
        $this->client->request('GET', $this->urlGenerator->generate('security_logout'));

        $crawler = $this->client->request('GET', $this->urlGenerator->generate('security_login'));
        $form = $crawler->selectButton('Se connecter')->form([
            'email' => $user->getEmail(),
            'password' => 'password'
        ]);
        if($clickRememberMe)
        {
            $crawler->findElement(WebDriverBy::name('_remember_me'))->click();
        }
        $this->client->submit($form);
        $this->client->waitFor('.alert.alert-success', 3);

        /**on supprime le cookie de la session */
        $this->client->getCookieJar()->set(new Cookie('PHPSESSID', '', time() - 1));
    }
}