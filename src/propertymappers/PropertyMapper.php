<?php
/*
 *  @link      https://www.venveo.com
 *  @copyright Copyright (c) 2020 Venveo
 */

namespace venveo\hubspottoolbox\propertymappers;

use Craft;
use craft\base\Component;
use venveo\hubspottoolbox\HubSpotToolbox;
use venveo\hubspottoolbox\models\HubSpotObjectMapping;
use venveo\hubspottoolbox\models\HubSpotObjectProperty;


/**
 *
 * @property-read array $propertyMappings
 * @property-read array $propertiesFromApi
 * @property-read \venveo\hubspottoolbox\models\HubSpotObjectProperty[] $properties
 */
abstract class PropertyMapper extends Component implements PropertyMapperInterface
{
    /**
     * @var int
     */
    public $id;
    /**
     * @var mixed
     */
    public $sourceTypeId;
    /**
     * @var string
     */
    public $uid;
    /**
     * @var \DateTime
     */
    public $dateCreated;
    /**
     * @var \DateTime
     */
    public $dateUpdated;

    protected $_type = null;

    /**
     * @inheritdoc
     */
    public function getType(): string
    {
        return $this->_type ?? static::class;
    }

    /**
     * @inheritdoc
     */
    public function setType(string $type)
    {
        $this->_type = $type;
    }

    /**
     * @inheritdoc
     */
    public function getPropertyMappings(): array
    {
        return HubSpotToolbox::$plugin->propertyMappings->getMappingsForMapper($this->id);
    }

    /**
     * @return HubSpotObjectProperty[]
     */
    public function getProperties(): array
    {
        return HubSpotToolbox::$plugin->properties->getAllPropertiesForMapper($this);
    }

    /**
     * @inheritdoc
     */
    public function getPropertiesFromApi(): array
    {
        return HubSpotToolbox::$plugin->properties->getPropertiesFromApi(static::getHubSpotObjectType());
    }

    /**
     * @inheritdoc
     */
    public function renderTemplates($source): array
    {
        $results = [];
        foreach ($this->propertyMappings as $mapping) {
            $results[$mapping->getProperty()->name] = $this->renderProperty($mapping, $source);
        }
        return $results;
    }

    /**
     * @inheritdoc
     */
    public function renderProperty(HubSpotObjectMapping $mapping, $source): string
    {
        $renderedTemplate = Craft::$app->view->renderObjectTemplate($mapping->template, [],
            $this->getTemplateParams($source));
        return $renderedTemplate;
    }

    /**
     * @inheritdoc
     */
    public function getRecommendedMappings(): array
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function fields()
    {
        $fields = parent::fields();
        $fields[] = 'type';
        return $fields;
    }

    /**
     * @inheritdoc
     */
    public function extraFields()
    {
        $extra = parent::extraFields();
        $extra[] = 'type';
        $extra[] = 'properties';
        $extra[] = 'propertiesFromApi';
        $extra[] = 'propertyMappings';
        if ($this instanceof PreviewablePropertyMapperInterface) {
            $extra[] = 'initialPreviewObjectId';
        }
        return $extra;
    }

    /**
     * @inheritdoc
     */
    public static function normalizeSource($source)
    {
        return $source;
    }

    /**
     * @inheritdoc
     */
    public function canBeAppliedToSource($source): bool
    {
        return true;
    }
}