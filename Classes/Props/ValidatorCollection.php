<?php

namespace Sitegeist\Monocle\PropTypes\Props;

use Neos\Flow\Validation\Validator\ValidatorInterface;

class ValidatorCollection
{
    /**
     * @var ValidatorInterface[]
     */
    protected $validators = [];

    public function __construct(ValidatorInterface ...$validators)
    {
        $this->validators = $validators;
    }

    public function getValidators(): array
    {
        return $this->validators;
    }

    public function hasValidatorOfType(string $className): bool
    {
        foreach ($this->validators as $validator) {
            if (is_a($validator, $className)) {
                return true;
            }
        }
        return false;
    }

    public function getValidatorOfType(string $className): ?ValidatorInterface
    {
        foreach ($this->validators as $validator) {
            if (is_a($validator, $className)) {
                return $validator;
            }
        }
        return null;
    }
}
