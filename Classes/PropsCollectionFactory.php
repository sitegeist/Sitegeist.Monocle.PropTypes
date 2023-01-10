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
use Neos\Flow\Validation\Validator\ValidatorInterface;
use Sitegeist\Monocle\Domain\Fusion;
use Sitegeist\Monocle\Domain\PrototypeDetails\Props;
use Sitegeist\Monocle\Domain\PrototypeDetails\Props\EditorFactory as MonocleEditorFactory;
use Sitegeist\Monocle\PropTypes\Props\ValidatorEditorFactory;

/**
 * @Flow\Scope("singleton")
 */
final class PropsCollectionFactory implements Props\PropsCollectionFactoryInterface
{
    /**
     * @Flow\Inject
     * @var MonocleEditorFactory
     */
    protected $monocleEditorFactory;

    /**
     * @Flow\Inject
     * @var ValidatorEditorFactory
     */
    protected $validatorEditorFactory;

    /**
     * @param Fusion\Prototype $fusionPrototypeAst
     * @return Props\PropsCollectionInterface
     */
    public function fromPrototypeForPrototypeDetails(Fusion\Prototype $prototype): Props\PropsCollectionInterface
    {
        $propsCollectionBuilder = new Props\PropsCollectionBuilder();
        $propTypesDictionary = $prototype
            ->evaluate(
                '/__meta/propTypes<Neos.Fusion:DataStructure>'
            );

        $alreadyProcessedPropNames = [];

        // iterate over propTypes and create editors
        foreach ($propTypesDictionary as $key => $propTypeValidator) {
            $propName = Props\PropName::fromString($key);
            if ($propTypeValidator instanceof ValidatorInterface) {
                $editor = $this->validatorEditorFactory->forValidator($propTypeValidator);
                $propValue = Props\PropValue::of($prototype, $propName) ?: Props\PropValue::fromAny("");
                if ($editor !== null) {
                    $alreadyProcessedPropNames[] = (string) $propName;
                    $propsCollectionBuilder->addProp(
                        new Props\Prop($propName, $propValue, $editor)
                    );
                }
            }
        }

        // fallback to default editor factory for yet unhandled props
        foreach (Props\PropName::fromPrototype($prototype) as $propName) {
            if (in_array((string) $propName, $alreadyProcessedPropNames)) {
                continue;
            }
            if ($propValue = Props\PropValue::of($prototype, $propName)) {
                $editor = $this->monocleEditorFactory->for($prototype, $propName);
                if ($editor !== null) {
                    $propsCollectionBuilder->addProp(
                        new Props\Prop($propName, $propValue, $editor)
                    );
                }
            }
        }

        return $propsCollectionBuilder->build();
    }
}
