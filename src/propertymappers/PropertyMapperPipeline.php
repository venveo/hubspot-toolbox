<?php
namespace venveo\hubspottoolbox\propertymappers;

use craft\base\Component;
use craft\base\Element;
use craft\helpers\ArrayHelper;
use venveo\hubspottoolbox\entities\ecommerce\ExternalSyncMessage;

class PropertyMapperPipeline extends Component {
    /**
     * @var PropertyMapper[]
     */
    private $propertyMappers = [];

    public function produceExternalSyncMessage($input): ExternalSyncMessage {
        $properties = [];
        $externalIdentifier = null;
        foreach($this->propertyMappers as $propertyMapper) {
            if (!$propertyMapper->canBeAppliedToSource($input)) {
                continue;
            }

            if (!$externalIdentifier) {
                $externalIdentifier = $propertyMapper::getExternalObjectId($input);
            }

            foreach($propertyMapper->getPropertyMappings() as $mapping) {
                if (!isset($properties[$mapping->getProperty()->name])) {
                    $properties[$mapping->getProperty()->name] = $propertyMapper->renderProperty($mapping, $input);
                }
            }
        }
        return new ExternalSyncMessage(['properties' => $properties, 'externalObjectId' => $externalIdentifier]);
    }

    /**
     * @return array
     */
    public function getPropertyMappers(): array
    {
        return $this->propertyMappers;
    }

    /**
     * @param PropertyMapperInterface[] $propertyMappers
     */
    public function setPropertyMappers(array $propertyMappers): void
    {
        // Make sure we process more specific property mappers first
        $this->propertyMappers = $propertyMappers;
        ArrayHelper::multisort($this->propertyMappers, function(PropertyMapper $mapper) {
            return isset($mapper->sourceTypeId) ? 0 : 1;
        });
    }
}