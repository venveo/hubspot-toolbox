<?php
/*
 *  @link      https://www.venveo.com
 *  @copyright Copyright (c) 2020 Venveo
 */

namespace venveo\hubspottoolbox\controllers;

use craft\web\Controller;
use venveo\hubspottoolbox\HubSpotToolbox;
use venveo\hubspottoolbox\models\HubSpotObjectMapping;
use venveo\hubspottoolbox\propertymappers\PreviewablePropertyMapperInterface;
use venveo\hubspottoolbox\propertymappers\PropertyMapperInterface;
use yii\web\HttpException;

class PropertyMappersController extends Controller
{
    private static $requestMapper = null;

    public function actionGetMappings()
    {
        $mapper = $this->getMapperFromRequest();
        $attributes = ['propertiesFromApi', 'propertyMappings.property'];
        $data = $mapper->toArray([], $attributes);
        $data['previewData'] = [];
        if ($mapper instanceof PreviewablePropertyMapperInterface) {
            $previewId = $this->getPreviewSourceFromRequest();
            $preview = $mapper->renderTemplates($previewId);
            $data['previewData']['preview'] = $preview;
            $data['previewData']['previewObjectId'] = $previewId;
        }
        return $this->asJson($data);
    }

    public function actionSaveMapping()
    {
        $this->requireAcceptsJson();
        $this->requirePostRequest();
        $data = \Craft::$app->request->getRequiredBodyParam('mapping');
        $mapper = $this->getMapperFromRequest();

        $propertyData = \Craft::$app->request->getBodyParam('property');
        if ($propertyData) {
            $property = HubSpotToolbox::$plugin->properties->getOrCreateProperty($mapper::getHubSpotObjectType(),
                $propertyData['name'], $propertyData['dataType']);
        } else {
            $property = HubSpotToolbox::$plugin->properties->getPropertyById($mapper::getHubSpotObjectType(),
                $data['propertyId']);
        }
        $mappingId = null;
        if (isset($data['id'])) {
            $existingMapping = HubSpotToolbox::$plugin->propertyMappings->getMappingById($data['id']);
            if ($existingMapping->datePublished === null) {
                $mappingId = $existingMapping->id;
            }
        }

        $mapping = new HubSpotObjectMapping([
            'id' => $mappingId,
            'template' => $data['template'] ?? '',
            'propertyId' => $property->id,
            'mapperId' => $mapper->id,
            'datePublished' => null
        ]);
        HubSpotToolbox::$plugin->propertyMappings->saveMapping($mapping);
        $mappingData = $mapping->toArray();
        if ($mapper instanceof PreviewablePropertyMapperInterface) {
            $previewId = $this->getPreviewSourceFromRequest();
            $preview = $mapper->renderProperty($mapping, $previewId);
            $mappingData['previewData']['preview'] = $preview;
            $mappingData['previewData']['previewObjectId'] = $previewId;
        }
        return $this->asJson($mappingData);
    }

    public function actionPublish()
    {
        $this->requireAcceptsJson();
        $this->requirePostRequest();
        $mapper = $this->getMapperFromRequest();
        HubSpotToolbox::$plugin->propertyMappings->publishMappings($mapper);
        return $this->asJson(['success' => true]);
    }

    protected function getMapperFromRequest(): PropertyMapperInterface
    {
        if (self::$requestMapper) {
            return self::$requestMapper;
        }
        $mapperType = \Craft::$app->request->getRequiredParam('mapper');
        $sourceTypeId = \Craft::$app->request->getParam('sourceTypeId');

        if (class_exists($mapperType) && in_array(PropertyMapperInterface::class, class_implements($mapperType),
                true)) {
            return self::$requestMapper = HubSpotToolbox::$plugin->propertyMappings->getOrCreateObjectMapper($mapperType,
                $sourceTypeId);
        }

        throw new HttpException('Invalid mapper');
    }

    protected function getPreviewSourceFromRequest()
    {
        /** @var PreviewablePropertyMapperInterface $mapper */
        $mapper = $this->getMapperFromRequest();
        $previewId = \Craft::$app->request->getParam('previewObjectId') ?? $mapper->getInitialPreviewObjectId();
        return $previewId;
    }

    public function actionDeleteMapping()
    {
        $this->requirePostRequest();
        $mappingId = \Craft::$app->request->getRequiredBodyParam('id');
        $mapping = HubSpotToolbox::$plugin->properties->getMappingById($mappingId);
        HubSpotToolbox::$plugin->properties->deleteMapping($mapping);
        return $this->asJson(['success' => true]);
    }
}