<?php
namespace App\Tests\Functional\Controller\Security;

use App\Config\SiteConfig;
use App\Entity\User;
use App\Config\TextConfig;
use App\Tests\Utils\UserFixturesTrait;
use App\Tests\Functional\FunctionalTest;
use App\DataFixtures\Tests\UserTestFixtures;
use App\Tests\Functional\Controller\Security\LoginTrait;



/**
 * @group FunctionalSecurity
 */
class PasswordControllerTest extends FunctionalTest
{
    use UserFixturesTrait;

    use LoginTrait;

    /**function askResetPassword() */
    public function testAskResetPasswordPageRender()
    {
        $this->client->request('GET', $this->urlGenerator->generate('security_askResetPassword'));
        $this->assertResponseIsSuccessful('Le statut de la réponse est !== de 200');
        $this->assertSelectorNotExists('.form-error');
    }
    public function testBlankEmailResultsInError()
    {
        $this->submitEmail('');
        $this->assertSelectorExists('.form-error');
    }   
    public function testInexistantEmailResultsInError()
    {
        $this->submitEmail('emailinexistant@vraimentinexistant.us');
        $this->assertSelectorExists('.form-error');
    }
    public function testCorrectEmailRedirectToLogin()
    {
        $this->loadFixtures([UserTestFixtures::class], $this->client);
        
        $this->submitEmail('user@gmail.com');

        $this->assertResponseRedirects($this->urlGenerator->generate('security_login'));
        $this->client->followRedirect();
        $this->assertSelectorTextContains('.alert.alert-success', TextConfig::ALERT_RESET_PASSWORD, 'le flash de succès n\'est pas présent');
    }
    public function testResetPasswordTokenAndExpirationDateAreCorrectlySet()
    {
        $this->loadFixtures([UserTestFixtures::class], $this->client);
        
        $this->submitEmail('user@gmail.com');

        $user = $this->findUserByEmail('user@gmail.com');
        $this->assertNotNull($user->getResetPasswordToken());
        $this->assertEqualsWithDelta((time() + SiteConfig::TOKEN_TIME_VALIDITY), $user->getResetPasswordTokenExpireAt(), 60);
    }

    /** function resetPassword() */
    /** verification du token */
    public function testIncorrectResetPasswordTokenRedirectToHome()
    {
        $this->client->request('GET', $this->urlGenerator->generate('security_resetPassword'), [
            'token' => $this->getUserWithValidToken()->getId() . '==' . 'incorrectToken'
        ]);
        $this->assertResponseRedirects($this->urlGenerator->generate('home'));
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert.alert-danger', 'le flash d\'erreur n\'est pas présent');
    }
    public function testExpiredResetPasswordTokenRedirectToHome()
    {
        $userWithExpiredToken = $this->getUserWithExpiredToken($this->client);

        $this->client->request('GET', $this->urlGenerator->generate('security_resetPassword'), [
            'token' => $userWithExpiredToken->getId() . '==' . $userWithExpiredToken->getResetPasswordToken()
        ]);
        $this->assertResponseRedirects($this->urlGenerator->generate('home'));
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert.alert-danger');
    }
    public function testCorrectResetPasswordTokenPageRender()
    {
        $user = $this->getUserWithValidToken();

        $this->client->request('GET', $this->urlGenerator->generate('security_resetPassword'), [
            'token' => $user->getId() . '==' . $user->getResetPasswordToken()
        ]);
        $this->assertResponseIsSuccessful('Le statut de la réponse est !== de 200');
        $this->assertSelectorTextContains('label', 'mot de passe');
    }
    /** POST nouveau mot de passe */
    public function testNewPasswordWithInvalidPasswords()
    {
        $this->submitPassword('password', 'otherPassword');
        $this->assertSelectorExists('.password-group .form-error');
    }
    public function testNewPasswordWithInvalidBlankPassword()
    {
        $this->submitPassword('', '');
        $this->assertSelectorExists('.password-group .form-error');
    }
    public function testNewPasswordWithValidPasswordRedirectToLogin()
    {
        $this->submitPassword('password', 'password');
        $this->assertResponseRedirects($this->urlGenerator->generate('security_login'));
        $this->client->followRedirect();
        $this->assertSelectorTextContains('.alert.alert-success', TextConfig::ALERT_RESET_PASSWORD_SUCCESS);
    }
    public function testNewPasswordIsCorrectlyPersisted()
    {
        $user = $this->getUserWithValidToken();
        $this->submitPassword('newpassword', 'newpassword', $user);
        
        $this->tryLogin($user->getEmail(), 'newpassword');
        $this->assertResponseRedirects();
        $this->client->followRedirect();
        $this->assertSelectorTextContains('.alert.alert-danger', TextConfig::ERROR_NOT_CONFIRMED_USER);
        $this->assertSelectorTextNotContains('.alert.alert-danger', TextConfig::ERROR_INVALID_CREDENTIALS);
    }

    /**function changePassword */
    public function  testChangePasswordRedirectsNonAuthenticatedUser()
    {
        $this->client->request('GET', $this->urlGenerator->generate('security_changePassword'));
        $this->assertResponseRedirects($this->urlGenerator->generate('security_login'));
    }
    public function testChangePasswordPageRenderWithAuthenticatedUser()
    {
        $this->loadFixtures([UserTestFixtures::class], $this->client);
        $user = $this->findUser([], $this->client);
        $this->client->loginUser($user);

        $this->client->request('GET', $this->urlGenerator->generate('security_changePassword'));
        $this->assertResponseIsSuccessful('Le statut de la réponse est !== de 200');
        $this->assertSelectorTextContains('label', 'ncien mot de passe');
    }
    public function testChangePasswordWithIncorrectOldPassword()
    {
        $this->loginUserAndTryChangePassword('incorrect_old_password', 'newpassword', 'newpassword');
        $this->assertSelectorExists('.oldPassword-group .form-error');
    }
    public function testChangePasswordWithInvalidNewPassword()
    {
        $this->loginUserAndTryChangePassword('password', 'pass', 'pass');
        $this->assertSelectorExists('.password-group .form-error');
    }
    public function testChangePasswordSuccess()
    {
        $this->loginUserAndTryChangePassword('password', 'newpassword', 'newpassword');
        $this->assertResponseRedirects();
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert.alert-success');
    }


    /**helpers */
    private function loginUserAndTryChangePassword(string $oldPassword, string $password, string $passwordConfirm)
    {
        $this->loadFixtures([UserTestFixtures::class], $this->client);
        $user = $this->findUserByEmail('user@gmail.com', $this->client);
        $this->client->loginUser($user);

        $crawler = $this->client->request('GET', $this->urlGenerator->generate('security_changePassword'));
        $form = $crawler->selectButton('Valider')->form([
            'change_password[oldPassword]' => $oldPassword,
            'change_password[password]' => $password,
            'change_password[passwordConfirm]' => $passwordConfirm
        ]);
        $this->client->submit($form);
    }

    private function submitEmail(string $email)
    {
        $crawler = $this->client->request('GET', $this->urlGenerator->generate('security_askResetPassword'));
        $form = $crawler->selectButton('Valider')->form([
            'email' => $email
        ]);
        $this->client->submit($form);
    }

    private function submitPassword(string $password, string $passwordConfirm, ?User $user = null)
    {
        $user = $user !== null ? $user: $this->getUserWithValidToken();
        $crawler = $this->client->request('GET', $this->urlGenerator->generate('security_resetPassword'), [
            'token' => $user->getId() . '==' . $user->getResetPasswordToken()
        ]);
        $form = $crawler->selectButton('Valider')->form([
            'new_password[password]' => $password,
            'new_password[passwordConfirm]' => $passwordConfirm
        ]);
        $this->client->submit($form);
    }
}