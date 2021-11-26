<?php

namespace Carbon\GeoMap\DataSource;

use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\I18n\EelHelper\TranslationHelper;
use Neos\Flow\ResourceManagement\ResourceManager;
use Neos\Neos\Service\DataSource\AbstractDataSource;
use Exception;
use function ucfirst;
use function sprintf;

/**
 * Returns a list of styles with preview images
 */
class MapStyleDataSource extends AbstractDataSource
{
    /**
     * @var string
     */
    static protected $identifier = 'carbon-geomap-mapstyle';

    /**
     * @Flow\Inject
     * @var TranslationHelper
     */
    protected $translationHelper;

    /**
     * @Flow\Inject
     * @var ResourceManager
     */
    protected $resourceManager;


    /**
     * @param NodeInterface $node The node that is currently edited (optional)
     * @param array $arguments Additional arguments (key / value)
     * @return array
     * @throws Exception
     */
    public function getData(NodeInterface $node = null, array $arguments = [])
    {
        $packageKey = $arguments['packageKey'] ?? null;
        $styles = $arguments['styles'] ?? null;
        $previewPattern = $arguments['previewPattern'] ?? null;

        if (empty($styles) || empty($packageKey)) {
            return [];
        }

        $values = [];
        foreach ($styles as $style) {
            $preview = null;
            if (!empty($previewPattern)) {
                $preview = $this->resourceManager->getPublicPackageResourceUri(
                    $packageKey,
                    sprintf($previewPattern, $style)
                );
            }

            $values[] = [
                'label' => $this->translationHelper->translate(
                    $style,
                    ucfirst($style),
                    [],
                    'MapStyle',
                    $packageKey
                ),
                'value' => $style,
                'preview' => $preview,
            ];
        }

        return $values;
    }
}
