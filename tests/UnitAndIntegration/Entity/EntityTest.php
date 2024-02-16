<?php
namespace App\Tests\UnitAndIntegration\Entity;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator\ValidatorInterface;


abstract class EntityTest extends KernelTestCase
{
    private ValidatorInterface $validator;

    protected string $moreThan50Caracters;

    protected string $moreThan200Caracters;

    protected string $moreThan2000Caracters;


    public function setUp(): void 
    {
        parent::setUp();

        self::bootKernel();

        $this->validator = static::getContainer()->get(ValidatorInterface::class);

        $this->moreThan50Caracters = str_repeat('tencars...', 5) . 'and_more';
        $this->moreThan200Caracters = str_repeat('tencars...', 20) . 'and_more';
        $this->moreThan2000Caracters = str_repeat('tencars...', 200) . 'and_more';
    }

    protected function assertHasErrors(int $expectedErrors, Object $entity)
    {
        $violations = $this->validator->validate($entity);
        $message = [];
        /** @var ConstraintViolation $violation */
        foreach($violations as $violation)
        {
            $message[] = $violation->getPropertyPath() . ' => ' . $violation->getMessage();
        }
        $this->assertCount($expectedErrors, $violations, implode(', ', $message));
    }
}