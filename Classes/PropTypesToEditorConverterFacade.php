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
     * @return null|EditorContainer
     */
    public function string(): ?EditorContainer
    {
        return new EditorContainer(
            $this->editorFactory->text()
        );
    }

    /**
     * @param array $allowedValues
     * @return null|EditorContainer
     */
    public function oneOf(array $allowedValues): ?EditorContainer
    {
        $options = [];

        foreach ($allowedValues as $allowedValue) {
            if (is_string($allowedValue) || is_int($allowedValue) || is_float($allowedValue)) {
                $options[(string) $allowedValue] = $allowedValue;
            }
        }

        if (count($options)) {
            return new EditorContainer(
                $this->editorFactory->selectBox([
                    'options' => $options
                ])
            );
        } else {
            return null;
        }
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