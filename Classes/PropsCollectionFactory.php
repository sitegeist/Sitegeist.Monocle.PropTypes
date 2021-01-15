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
use Sitegeist\Monocle\Domain\Fusion;
use Sitegeist\Monocle\Domain\PrototypeDetails\Props;

/**
 * @Flow\Scope("singleton")
 */
final class PropsCollectionFactory implements Props\PropsCollectionFactoryInterface
{
    /**
     * @Flow\InjectConfiguration(path="fusionContextName")
     * @var string
     */
    protected $fusionContextName;

    /**
     * @Flow\Inject(lazy=false)
     * @var PropTypesToEditorConverterFacade
     */
    protected $propTypesToEditorConverterFacade;

    /**
     * @Flow\Inject
     * @var Props\EditorFactory
     */
    protected $editorFactory;

    /**
     * @param Fusion\Prototype $fusionPrototypeAst
     * @return Props\PropsCollectionInterface
     */
    public function fromPrototypeForPrototypeDetails(
        Fusion\Prototype $prototype
    ): Props\PropsCollectionInterface {
        $propsCollectionBuilder = new Props\PropsCollectionBuilder();
        $propTypesToEditorDictionary = $prototype
            ->evaluate(
                '/__meta/propTypes<Neos.Fusion:DataStructure>',
                [
                    $this->fusionContextName =>
                        $this->propTypesToEditorConverterFacade
                ]
            );

        foreach (Props\PropName::fromPrototype($prototype) as $propName) {
            if ($propValue = Props\PropValue::of($prototype, $propName)) {
                $editor = null;

                if (isset($propTypesToEditorDictionary[(string) $propName])) {
                    $editor = $propTypesToEditorDictionary[(string) $propName]
                        ->getEditor();
                } else {
                    $editor = $this->editorFactory->for($prototype, $propName);
                }

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