<?php

namespace wooppay\YiiToSymfonyValidatorsBundle\Validator;

use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class InConstraintValidator extends ConstraintValidator
{
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof InConstraint) {
            throw new UnexpectedTypeException($constraint, InConstraint::class);
        }

        $params = $constraint->getParams();

        if ($params['allowEmpty'] == false && empty($value)) {
            $this->addViolation($constraint, 'emptyValue');
        } else {
            if ($params['strict'] == false) {
                if ($params['not'] == false) {
                    if (!in_array($value, $params['range'])) {
                        $this->addViolation($constraint, 'valueShouldBeInList', implode(', ', $params['range']));
                    }
                } else {
                    if (in_array($value, $params['range'])) {
                        $this->addViolation($constraint, 'valueShouldNotBeInList', implode(', ', $params['range']));
                    }
                }
            } else {
                if ($params['not'] == false) {
                    if (!in_array($value, $params['range'], true)) {
                        $this->addViolation($constraint, 'valueShouldBeInList', implode(', ', $params['range']));
                    }
                } else {
                    if (in_array($value, $params['range'], true)) {
                        $this->addViolation($constraint, 'valueShouldNotBeInList', implode(', ', $params['range']));
                    }
                }
            }
        }
    }

    private function addViolation(Constraint $constraint, string $text, string $value = null)
    {
        $this->context->buildViolation($constraint->message)
            ->setParameter('{{ string }}', $this->translator->trans($text, [
                (!$value) ? null : '%value%' => $value
            ], 'validation'))
            ->addViolation()
        ;
    }
}