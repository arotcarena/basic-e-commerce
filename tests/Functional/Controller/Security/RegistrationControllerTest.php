<?php
namespace App\Tests\Functional\Controller\Security;

use App\Config\TextConfig;
use App\Tests\Utils\UserFixturesTrait;
use App\Tests\Functional\Controller\Security\LoginTrait;
use App\Tests\Functional\FunctionalTest;



/**
 * @group FunctionalSecurity
 */
class RegistrationControllerTest extends FunctionalTest
{
    use LoginTrait;
    
    use UserFixturesTrait;
    
    /** register */
    public function testRegisterPageRender()
    {
        $this->client->request('GET', $this->urlGenerator->generate('security_register'));
        $this->assertResponseIsSuccessful('response status de la page security_register != 200');
        $this->assertSelectorTextContains('button', 'Créer un compte', 'la route security_register ne fonctionne pas correctement');
    }

    /** emailConfirmation */
    public function testIncorrectConfirmationTokenRedirectToHome()
    {
        $this->client->request('GET', $this->urlGenerator->generate('security_emailConfirmation'), [
            'token' => $this->getUserWithValidToken($this->client)->getId() . '==' . 'incorrectToken'
        ]);
        $this->assertResponseRedirects($this->urlGenerator->generate('home'));
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert.alert-danger', 'le flash d\'erreur n\'est pas présent');
    }
    public function testIncorrectConfirmationTokenUserIsNotVerified()
    {
        $user = $this->getUserWithValidToken($this->client);

        $this->client->request('GET', $this->urlGenerator->generate('security_emailConfirmation'), [
            'token' => $user->getId() . '==' . 'incorrectToken'
        ]);
        $this->tryLogin($user->getEmail(), 'password', false);
        $this->assertLoginFail();
        $this->assertSelectorTextContains('.alert.alert-danger', TextConfig::ERROR_NOT_CONFIRMED_USER);
    }
    public function testExpiredConfirmationTokenRedirectToHomeAndUserIsNotVerified()
    {
        $userWithExpiredToken = $this->getUserWithExpiredToken($this->client);

        $this->client->request('GET', $this->urlGenerator->generate('security_emailConfirmation'), [
            'token' => $userWithExpiredToken->getId() . '==' . $userWithExpiredToken->getConfirmationToken()
        ]);
        $this->assertResponseRedirects($this->urlGenerator->generate('home'));
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert.alert-danger');

        $this->tryLogin($userWithExpiredToken->getEmail(), 'password', false);
        $this->assertLoginFail();
        $this->assertSelectorTextContains('.alert.alert-danger', TextConfig::ERROR_NOT_CONFIRMED_USER);
    }
    public function testCorrectConfirmationTokenRedirectToLogin()
    {
        $user = $this->getUserWithValidToken($this->client);

        $this->client->request('GET', $this->urlGenerator->generate('security_emailConfirmation'), [
            'token' => $user->getId() . '==' . $user->getConfirmationToken()
        ]);
        $this->assertResponseRedirects($this->urlGenerator->generate('security_login'));
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert.alert-success', 'le flash de succès n\'est pas présent');
    }
    public function testCorrectConfirmationTokenUserIsVerifiedAndCanLogin()
    {
        $user = $this->getUserWithValidToken($this->client);

        $this->client->request('GET', $this->urlGenerator->generate('security_emailConfirmation'), [
            'token' => $user->getId() . '==' . $user->getConfirmationToken()
        ]);
        $this->tryLogin($user->getEmail(), 'password', false);
        $this->assertResponseRedirects();
        $this->client->followRedirect();
        $this->assertSelectorTextContains('.alert.alert-success', TextConfig::ALERT_LOGIN_SUCCESS);
    }
}