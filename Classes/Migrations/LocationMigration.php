<?php

namespace Carbon\GeoMap\Migrations;

use Neos\ContentRepository\Domain\Model\NodeData;
use Neos\ContentRepository\Migration\Transformations\AbstractTransformation;
use Neos\Neos\Controller\CreateContentContextTrait;

class LocationMigration extends AbstractTransformation
{
    use CreateContentContextTrait;

    /**
     * @param NodeData $node
     * @return boolean
     */
    public function isTransformable(NodeData $node)
    {
        if (!$node->hasProperty('lat') && !$node->hasProperty('lng')) {
            return false;
        }
        return true;
    }

    /**
     * @param NodeData $node
     * @return void
     */
    public function execute(NodeData $node)
    {
        $lat = (float)$node->getProperty('lat');
        $lng = (float)$node->getProperty('lng');
        $node->setProperty('location', [
            'lat' => $lat,
            'lng' => $lng,
        ]);
        $node->removeProperty('lat');
        $node->removeProperty('lng');
    }
}
