<?php
namespace App\Validator;

use App\Validator\GreaterThanHghstOffer;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class GreaterThanHghstOfferValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof GreaterThanHghstOffer) {
            throw new UnexpectedTypeException($constraint, GreaterThanHghstOffer::class);
        }

        // custom constraints should ignore null and empty values to allow
        // other constraints (NotBlank, NotNull, etc.) to take care of that
        if (null === $value || '' === $value) {
            return;
        }

        if (!is_int($value)) {
            // throw this exception if your validator cannot handle the passed type so that it can be marked as invalid
            throw new UnexpectedValueException($value, 'int');

            // separate multiple types using pipes
            // throw new UnexpectedValueException($value, 'string|int');
        }

        // access your configuration options like this:
        if ('strict' === $constraint->mode) {
            // ...
        }

        if (!preg_match('/^[a-zA-Z0-9]+$/', $value, $matches)) {
            // the argument must be a string or an object implementing __toString()
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ string }}', $value)
                ->addViolation();
        }
    }
}