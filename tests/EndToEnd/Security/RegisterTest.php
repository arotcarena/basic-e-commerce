<?php
namespace App\Tests\EndToEnd\Security;


use App\Config\TextConfig;
use Facebook\WebDriver\WebDriverBy;
use App\DataFixtures\Tests\UserTestFixtures;
use App\Tests\EndToEnd\EndToEndTest;
use App\Tests\Utils\UserFixturesTrait;

class RegisterTest extends EndToEndTest
{

    use UserFixturesTrait;


    public function testInvalidDifferentsPasswords()
    {
        $this->tryRegister($this->getFaker()->email(), 'password', 'otherpassword', true);
        $this->assertSelectorNotExists('.alert.alert-success');
        $this->assertSelectorExists('.plainPassword-group .form-error');
    }
    public function testInvalidBlankPassword()
    {
        $this->tryRegister($this->getFaker()->email(), '', '', true);
        $this->assertSelectorNotExists('.alert.alert-success');
        $this->assertSelectorExists('.plainPassword-group .form-error');
    }
    public function testInvalidBlankEmail()
    {
        $this->tryRegister('', 'password', 'password', true);
        $this->assertSelectorNotExists('.alert.alert-success');
        $this->assertSelectorExists('.email-group .form-error');
    }
    public function testInvalidNotAgreeTerms()
    {
        $this->tryRegister($this->getFaker()->email(), 'password', 'password', false);
        $this->assertSelectorNotExists('.alert.alert-success');
        $this->assertSelectorExists('.agreeTerms-group .form-error');
    }
    public function testInvalidExistingEmail()
    {
        $existingEmail = $this->findUser([])->getEmail();
        
        $this->tryRegister($existingEmail, 'motdepasse', 'motdepasse', true);
        $this->assertSelectorNotExists('.alert.alert-success');
        $this->assertSelectorTextContains('.email-group .form-error > ul > li', 'compte déjà existant');
    }
    public function testValidRegistration()
    {
        $this->tryRegister($this->getFaker()->email(), 'motdepasse', 'motdepasse', true);
        $this->client->waitFor('.alert.alert-success', 5);
        $this->assertSelectorTextContains('.alert.alert-success', TextConfig::ALERT_REGISTER_SUCCESS);
        $this->assertSelectorNotExists('.form-error');
    }
    public function testOnValidRegistrationUserPersisted()
    {
        $email = $this->getFaker()->email();
        $password = 'motdepassedetest';

        $this->tryRegister($email, $password, $password, true);
        $this->assertSelectorTextContains('.alert.alert-success', TextConfig::ALERT_REGISTER_SUCCESS);

        $this->tryLogin($email, $password);
        $this->assertSelectorTextContains('.alert.alert-danger', TextConfig::ERROR_NOT_CONFIRMED_USER);
        $this->assertSelectorTextNotContains('.alert.alert-danger', TextConfig::ERROR_INVALID_CREDENTIALS);
    }
    
    private function tryRegister(string $email, string $plainPassword, string $passwordConfirm, bool $agreeTerms = false)
    {
        // on logout d'abord car si on est déjà loggé la page security_register n'est pas accessible
        $this->client->request('GET', $this->urlGenerator->generate('security_logout'));

        $this->loadFixtures([UserTestFixtures::class]);
        $crawler = $this->client->request('GET', $this->urlGenerator->generate('security_register'));
        $form = $crawler->selectButton('Créer un compte')->form([
            'user_registration[email]' => $email,
            'user_registration[plainPassword]' => $plainPassword,
            'user_registration[passwordConfirm]' => $passwordConfirm
        ]);
        if($agreeTerms)
        {
            $this->client->getWebDriver()->findElement(WebDriverBy::name('user_registration[agreeTerms]'))->click();
        }
        $this->client->submit($form);
    }

    private function tryLogin(string $email, string $password)
    {
        // on logout d'abord car si on est déjà loggé la page security_login n'est pas accessible
        $this->client->request('GET', $this->urlGenerator->generate('security_logout'));

        $this->loadFixtures([UserTestFixtures::class]);
        $crawler = $this->client->request('GET', $this->urlGenerator->generate('security_login'));
        $form = $crawler->selectButton('Se connecter')->form([
            'email' => $email,
            'password' => $password,
        ]);
        $this->client->submit($form);
    }
} 
