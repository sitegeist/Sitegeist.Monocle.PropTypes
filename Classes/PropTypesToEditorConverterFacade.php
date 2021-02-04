<?php declare(strict_types=1);
namespace Sitegeist\Monocle\PropTypes;

/**
 * This file is part of the Sitegeist.Monocle.PropTypes package
 *
 * (c) 2021
 * Wilhelm Behncke <behncke@sitegeist.de>
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Eel\ProtectedContextAwareInterface;
use Sitegeist\Monocle\Domain\PrototypeDetails\Props;

/**
 * @Flow\Scope("singleton")
 */
final class PropTypesToEditorConverterFacade implements ProtectedContextAwareInterface
{
    /**
     * @Flow\Inject
     * @var Props\EditorFactory
     */
    protected $editorFactory;

    /**
     * @param array $allowedValues
     * @return null|EditorContainer
     */
    public function oneOf(array $allowedValues): ?EditorContainer
    {
        $options = [];
        $type = null;

        foreach ($allowedValues as $allowedValue) {
            if ($type === null && is_string($allowedValue)) {
                $type = 'string';
            }

            if ($type === null && is_int($allowedValue)) {
                $type = 'integer';
            }

            if (($type === null || $type === 'integer') && is_float($allowedValue)) {
                $type = 'float';
            }

            if (is_string($allowedValue) || is_int($allowedValue) || is_float($allowedValue)) {
                $options[(string) $allowedValue] = [
                    'label' => (string) $allowedValue,
                    'value' => $allowedValue
                ];
            }
        }

        if ($type !== null) {
            $options = array_values($options);

            return new EditorContainer(
                $this->editorFactory->selectBox([
                    'options' => $options,
                    'castValueTo' => $type
                ]),
                Props\PropValue::fromAny($options[0])
            );
        } else {
            return null;
        }
    }

    /**
     * @param EditorContainer ...$editorContainers
     * @return null|EditorContainer
     */
    public function anyOf(EditorContainer ...$editorContainers): ?EditorContainer
    {
        if (isset($editorContainers[0])) {
            return $editorContainers[0];
        }

        return null;
    }

    /**
     * @param array $allowedValues
     * @return EditorContainer
     */
    public function getBoolean(): EditorContainer
    {
        return new EditorContainer(
            $this->editorFactory->checkbox(),
            Props\PropValue::fromAny(false)
        );
    }

    /**
     * @return EditorContainer
     */
    public function getString(): EditorContainer
    {
        return new EditorContainer(
            $this->editorFactory->text(),
            Props\PropValue::fromAny("")
        );
    }

    /**
     * @return EditorContainer
     */
    public function getInteger(): EditorContainer
    {
        return new EditorContainer(
            $this->editorFactory->number('integer'),
            Props\PropValue::fromAny("")
        );
    }

    /**
     * @return EditorContainer
     */
    public function getFloat(): EditorContainer
    {
        return new EditorContainer(
            $this->editorFactory->number('float'),
            Props\PropValue::fromAny("")
        );
    }

    /**
     * @param string $methodName
     * @param array<mixed> $arguments
     * @return null
     */
    public function __call($methodName, $arguments)
    {
        return null;
    }

    /**
     * @param string $methodName
     * @return bool
     */
    public function allowsCallOfMethod($methodName)
    {
        return true;
    }
}
