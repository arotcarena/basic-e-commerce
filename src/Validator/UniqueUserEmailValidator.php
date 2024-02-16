<?php

namespace App\Validator;

use App\Repository\UserRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class UniqueUserEmailValidator extends ConstraintValidator
{
    public function __construct(
        private UserRepository $userRepository
    )
    {

    }

    public function validate($value, Constraint $constraint)
    {
        /* @var App\Validator\UniqueEmail $constraint */

        if (null === $value || '' === $value) {
            return;
        }

        $user = $this->userRepository->findOneByEmail($value);
        if($user !== null)
        {
            $this->context->buildViolation($constraint->message)
                            ->setParameter('{{ value }}', $value)
                            ->addViolation();
        }
    }
}
