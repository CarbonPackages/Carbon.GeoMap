<?php

namespace Carbon\GeoMap\DataSource;

use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\I18n\Service as LocalizationService;
use Neos\Flow\ResourceManagement\ResourceManager;
use Neos\Neos\Service\DataSource\AbstractDataSource;
use Neos\Neos\Service\XliffService;
use JsonException;
use function json_decode;

/**
 * Return a list of countries with preview images
 */
class CountryDataSource extends AbstractDataSource
{
    /**
     * @var string
     */
    protected static $identifier = 'carbon-geomap-country';

    /**
     * @Flow\Inject
     * @var XliffService
     */
    protected $xliffService;


    /**
     * @Flow\Inject
     * @var LocalizationService
     */
    protected $localizationService;


    /**
     * @Flow\Inject
     * @var ResourceManager
     */
    protected $resourceManager;


    /**
     * @param NodeInterface $node The node that is currently edited (optional)
     * @param array $arguments Additional arguments (key / value)
     * @return array
     * @throws JsonException
     */
    public function getData(NodeInterface $node = null, array $arguments = [])
    {
        $locale = $this->localizationService->getConfiguration()->getCurrentLocale();
        $json = json_decode(
            $this->xliffService->getCachedJson($locale),
            true,
            512,
            JSON_THROW_ON_ERROR
        );
        $entries = $json['Carbon_GeoMap']['Countries'] ?? [];

        $countries = [];
        foreach ($entries as $key => $value) {
            $countries[] = [
                'label' => $value,
                'value' => $key,
                'preview' => $this->resourceManager->getPublicPackageResourceUri(
                    'Carbon.GeoMap',
                    sprintf('Flags/%s.svg', $key)
                ),
            ];
        }

        return $countries;
    }
}
