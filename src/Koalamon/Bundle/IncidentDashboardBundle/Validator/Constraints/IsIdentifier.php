<?php

namespace Koalamon\Bundle\IncidentDashboardBundle\Validator\Constraints;
use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class IsIdentifier extends Constraint
{
    public $message = 'The string "%string%" contains an illegal character: it can only contain letters, numbers and -  _ .';

    public function validate($value, Constraint $constraint)
    {
        if (!preg_match('/^[a-zA-Z0-9_.-]+$/', $value, $matches)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('%string%', $value)
                ->addViolation();
        }
    }
}