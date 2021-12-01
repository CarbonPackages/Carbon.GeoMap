<?php

namespace Carbon\GeoMap\Service;

use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Http\Client\Browser;
use Neos\Flow\Http\Client\CurlEngine;
use Neos\Flow\Http\Client\InfiniteRedirectionException;
use Neos\Flow\I18n\EelHelper\TranslationHelper;
use function strtolower;
use function in_array;

/**
 * @Flow\Scope("singleton")
 */
class GeocodingService
{
    /**
     * @Flow\InjectConfiguration("defaultCountry")
     * @var string
     */
    protected $defaultCountry;

    /**
     * @Flow\Inject
     * @var TranslationHelper
     */
    protected $translationHelper;


    /**
     * @param NodeInterface $node
     * @param string $propertyName
     * @param mixed $oldValue
     * @param mixed $value
     * @throws InfiniteRedirectionException
     */
    public function nodePropertyChanged(
        NodeInterface $node,
        string $propertyName,
        $oldValue = null,
        $value = null
    ): void {
        if (
            !$node->getNodeType()->isOfType('Carbon.GeoMap:Mixin.Address') ||
            !in_array($propertyName, ['street', 'city', 'country'])
        ) {
            return;
        }

        $node->setProperty('lat', '');
        $node->setProperty('lng', '');

        if (empty($value) && $propertyName != 'country') {
            return;
        }

        $street = $node->getProperty('street');
        $city = $node->getProperty('city');
        $country = $node->getProperty('country');

        if (empty($country)) {
            $country = strtolower($this->defaultCountry);
        }
        $englishCountryName = $this->translationHelper->translate(
            $country,
            $country,
            [],
            'Countries',
            'Carbon.GeoMap',
            null,
            'en'
        );
        $address = sprintf(
            '%s, %s, %s',
            $street,
            $city,
            $englishCountryName
        );

        $latLng = $this->geocodeLatLngFromAddress($address);

        if ($latLng) {
            $node->setProperty('lat', $latLng['lat']);
            $node->setProperty('lng', $latLng['lng']);
        }
    }

    /**
     * @param string $address
     * @return array|null
     * @throws InfiniteRedirectionException
     */
    public function geocodeLatLngFromAddress(string $address): ?array
    {
        $url = 'https://nominatim.openstreetmap.org/search.php?q=' . urlencode($address) . '&limit=1&format=jsonv2';
        $browser = new Browser();
        $browser->setRequestEngine(new CurlEngine());
        $response = $browser->request($url);
        $jsonContent = $response->getBody();

        if ($jsonContent) {
            $json = json_decode($jsonContent, true);
            if (isset($json[0]) && isset($json[0]['lat']) && isset($json[0]['lon'])) {
                return [
                    'lat' => $json[0]['lat'],
                    'lng' => $json[0]['lon'],
                ];
            }
        }

        return null;
    }
}
