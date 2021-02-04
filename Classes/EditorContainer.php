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
 * @Flow\Proxy(false)
 */
final class EditorContainer implements ProtectedContextAwareInterface
{
    /**
     * @var Props\EditorInterface
     */
    private $editor;

    /**
     * @var Props\PropValue
     */
    private $defaultValue;

    /**
     * @param Props\EditorInterface $editor
     */
    public function __construct(
        Props\EditorInterface $editor,
        Props\PropValue $defaultValue
    ) {
        $this->editor = $editor;
        $this->defaultValue = $defaultValue;
    }

    /**
     * @return Props\EditorInterface
     */
    public function getEditor(): Props\EditorInterface
    {
        return $this->editor;
    }

    /**
     * @return Props\PropValue
     */
    public function getDefaultValue(): Props\PropValue
    {
        return $this->defaultValue;
    }

    /**
     * Catch all calls to PropTypes.[propTypeName](...).isRequired and forward
     * this editor container instead
     *
     * @return self
     */
    public function getIsRequired(): self
    {
        return $this;
    }

    /**
     * Catch all other subsequent chain-calls and forward this editor container
     * instead
     *
     * @param string $methodName
     * @param array<mixed> $arguments
     * @return self
     */
    public function __call($methodName, $arguments)
    {
        return $this;
    }

    /**
     * @param string $methodName
     * @return bool
     */
    public function allowsCallOfMethod($methodName)
    {
        return $methodName !== 'getEditor';
    }
}