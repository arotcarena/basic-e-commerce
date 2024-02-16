<?php
namespace App\Tests\UnitAndIntegration\Validator;

use App\DataFixtures\Tests\UserTestFixtures;
use App\Repository\UserRepository;
use App\Tests\Utils\FixturesTrait;
use App\Tests\Utils\UserFixturesTrait;
use App\Validator\UniqueUserEmail;
use App\Validator\UniqueUserEmailValidator;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

/**
 * @group Validator
 */
class UniqueUserEmailValidatorTest extends KernelTestCase
{
    use FixturesTrait;

    use UserFixturesTrait;


    public function testInvalidExistingEmail()
    {
        $this->loadFixtures([UserTestFixtures::class]);
        $existingEmail = $this->findUser([])->getEmail();

        $context = $this->createMock(ExecutionContextInterface::class);
        $constraintViolationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);
        /** @var MockObject $context */
        $context->expects($this->once())
                ->method('buildViolation')
                ->willReturn($constraintViolationBuilder)
                ;

        /** @var ExecutionContextInterface $context */
        $this->validate($context, $existingEmail);
    }
    public function testValidUniqueEmail()
    {
        $context = $this->createMock(ExecutionContextInterface::class);
        /** @var MockObject $context */
        $context->expects($this->never())
                ->method('buildViolation')
                ;

        /** @var ExecutionContextInterface $context */
        $this->validate($context, 'validuniquemail@uniquedomain.de');
    }

    
    private function validate(ExecutionContextInterface $context, string $email)
    {
        /** @var UserRepository */
        $userRepository = static::getContainer()->get(UserRepository::class);

        $validator = new UniqueUserEmailValidator($userRepository);
        $constraint = new UniqueUserEmail();      
        
        $validator->initialize($context);
        $validator->validate($email, $constraint);
    }
}
