<?php declare(strict_types=1);
namespace Sitegeist\Monocle\PropTypes\Props;

/**
 * This file is part of the Sitegeist.Monocle.PropTypes package
 *
 * (c) 2020
 * Martin Ficzel <ficzel@sitegeist.de>
 * Wilhelm Behncke <behncke@sitegeist.de>
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Validation\Validator\ConjunctionValidator;
use Neos\Flow\Validation\Validator\StringValidator;
use Neos\Flow\Validation\Validator\ValidatorInterface;
use PackageFactory\AtomicFusion\PropTypes\Validators\BooleanValidator;
use PackageFactory\AtomicFusion\PropTypes\Validators\FloatValidator;
use PackageFactory\AtomicFusion\PropTypes\Validators\IntegerValidator;
use PackageFactory\AtomicFusion\PropTypes\Validators\OneOfValidator;
use Sitegeist\Monocle\Domain\PrototypeDetails\Props\Editor;
use Sitegeist\Monocle\Domain\PrototypeDetails\Props\EditorIdentifier;
use Sitegeist\Monocle\Domain\PrototypeDetails\Props\EditorInterface;
use Sitegeist\Monocle\Domain\PrototypeDetails\Props\EditorOptions;
use Sitegeist\Monocle\Domain\PrototypeDetails\Props\PropValue;

/**
 * @Flow\Proxy(false)
 */
final class ValidatorEditorFactory
{
    /**
     * Provides a fitting editor for any given prop value
     *
     * @param PropValue $propValue
     * @return null|EditorInterface
     */
    public function forValidator(ValidatorInterface $validator): ?EditorInterface
    {
        if ($validator instanceof ConjunctionValidator) {
            $validatorCollection = new ValidatorCollection(...$validator->getValidators());
        } else {
            $validatorCollection = new ValidatorCollection($validator);
        }

        if ($validatorCollection->hasValidatorOfType(BooleanValidator::class)) {
            return $this->checkBox();
        } elseif ($validatorCollection->hasValidatorOfType(StringValidator::class)) {
            return $this->text();
        } elseif ($validatorCollection->hasValidatorOfType(FloatValidator::class)) {
            return $this->number('float');
        } elseif ($validatorCollection->hasValidatorOfType(IntegerValidator::class)) {
            return $this->number('integer');
        } elseif ($oneOfValidator = $validatorCollection->getValidatorOfType(OneOfValidator::class)) {
            $values = $oneOfValidator->getOptions()['values'] ?: [];
            $options = array_map(
                function ($item) {
                    return ['label' => (string)$item, 'value' => $item];
                },
                $values
            );
            return $this->selectBox(['options' => $options]);
        }
        return null;
    }

    /**
     * Provides a CheckBox editor
     *
     * @return EditorInterface
     */
    public function checkbox(): EditorInterface
    {
        return new Editor(
            EditorIdentifier::fromString(
                'Sitegeist.Monocle/Props/Editors/Checkbox'
            ),
            EditorOptions::empty()
        );
    }

    /**
     * Provides a Text editor
     *
     * @return EditorInterface
     */
    public function text(): EditorInterface
    {
        return new Editor(
            EditorIdentifier::fromString(
                'Sitegeist.Monocle/Props/Editors/Text'
            ),
            EditorOptions::empty()
        );
    }

    /**
     * Provides a TextArea editor
     *
     * @return EditorInterface
     */
    public function textArea(): EditorInterface
    {
        return new Editor(
            EditorIdentifier::fromString(
                'Sitegeist.Monocle/Props/Editors/TextArea'
            ),
            EditorOptions::empty()
        );
    }

    /**
     * Provides a Number editor
     *
     * @return EditorInterface
     */
    public function number(string $numberType): EditorInterface
    {
        if (!in_array($numberType, ['integer', 'float'])) {
            throw new \UnexpectedValueException(
                '$numberType must be either "integer" or "float".'
            );
        }

        return new Editor(
            EditorIdentifier::fromString(
                'Sitegeist.Monocle/Props/Editors/Text'
            ),
            EditorOptions::fromArray([
                'castValueTo' => $numberType
            ])
        );
    }

    /**
     * Provides a SelectBox Editor
     *
     * @param array<string,mixed> $options
     * @return EditorInterface
     */
    public function selectBox(array $options): EditorInterface
    {
        if (!isset($options['options'])) {
            $options['options'] = [];
        }

        if (!is_array($options['options'])) {
            throw new \UnexpectedValueException(
                sprintf(
                    'SelectBox options must be an array. Got "%" instead.',
                    gettype($options['options'])
                )
            );
        } else {
            $options['options'] = array_values($options['options']);
        }

        foreach ($options['options'] as $option) {
            if (!isset($option['label'])) {
                throw new \UnexpectedValueException(
                    'All SelectBox options must have a label.'
                );
            }

            if (!is_string($option['label'])) {
                throw new \UnexpectedValueException(
                    sprintf(
                        'All SelectBox option labels must be of type string. Got "%s" instead.',
                        gettype($option['label'])
                    )
                );
            }

            if (!isset($option['value'])) {
                throw new \UnexpectedValueException(
                    sprintf(
                        'All SelectBox options must have a value. Found option "%s" without one.',
                        $option['label']
                    )
                );
            }

            if (!is_string($option['value']) && !is_int($option['value']) && !is_float($option['value'])) {
                throw new \UnexpectedValueException(
                    sprintf(
                        'All SelectBox option labels must be either of type string, integer or float. Got "%s" for option "%s" instead.',
                        gettype($option['value']),
                        $option['label']
                    )
                );
            }
        }

        return new Editor(
            EditorIdentifier::fromString(
                'Sitegeist.Monocle/Props/Editors/SelectBox'
            ),
            EditorOptions::fromArray($options)
        );
    }
}
