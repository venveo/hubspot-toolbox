<?php
/*
 *  @link      https://www.venveo.com
 *  @copyright Copyright (c) 2020 Venveo
 */

namespace venveo\hubspottoolbox\propertymappers;

use venveo\hubspottoolbox\entities\ObjectProperty;
use venveo\hubspottoolbox\models\HubSpotObjectMapping;
use yii\base\Arrayable;

/**
 * Interface PropertyMapperInterface
 * @package venveo\hubspottoolbox\propertymappers
 *
 * @property string $type
 * @property-read array $templateParams
 * @property-read HubSpotObjectMapping[] $recommendedMappings
 */
interface PropertyMapperInterface extends \ArrayAccess, Arrayable, \IteratorAggregate
{
    /**
     * Returns the class type of the property mapper. Usually just `static`
     *
     * @return string
     */
    public function getType(): string;

    /**
     * Sets the type property of the property mapper
     *
     * @param string $type
     */
    public function setType(string $type);

    /**
     * What is the object name we're going to query from HubSpot for this mapper
     *
     * @return string
     */
    public static function getHubSpotObjectName(): string;

    /**
     * What is the object type - this is usually the singular form of the object name
     *
     * @return string
     */
    public static function getHubSpotObjectType(): string;

    /**
     * What is the context for this mapper?
     *
     * @return string
     */
    public static function getObjectContext(): string;

    /**
     * Gets all of the configured mappings for this mapper
     *
     * @return HubSpotObjectMapping[]
     */
    public function getPropertyMappings(): array;

    /**
     * Get all applicable object properties on this mapper
     *
     * @return ObjectProperty[]
     */
    public function getPropertiesFromApi(): array;

    /**
     * Provides an array of params to be passed into object templates
     *
     * @param $source
     * @return array
     */
    public function getTemplateParams($source): array;

    /**
     * Sets the `renderedValue` on the supplied mapping
     *
     * @param HubSpotObjectMapping $mapping
     * @param $source
     * @return string
     */
    public function renderProperty(HubSpotObjectMapping $mapping, $source): string;

    /**
     * Renders all property mappings via the renderProperty() method
     * @param $source
     * @return array
     */
    public function renderTemplates($source): array;

    /**
     * Returns a list of recommended object mappings for this mapper
     *
     * @return HubSpotObjectMapping[]
     */
    public function getRecommendedMappings(): array;

    /**
     * Produces a unique identifier for a given source
     *
     * @param $source
     * @return mixed
     */
    public static function getExternalObjectId($source);

    /**
     * Input sources can be a variety of types. This function should handle those variations so source consumers don't
     * have to.
     *
     * @param $source
     * @return mixed
     */
    public static function normalizeSource($source);

    /**
     * Returns whether the mapper can be applied to a source input
     *
     * @param $source
     * @return bool
     */
    public function canBeAppliedToSource($source): bool;
}