<?php

namespace venveo\hubspottoolbox\entities\ecommerce;

use venveo\hubspottoolbox\entities\HubSpotEntity;

/**
 * @package venveo\hubspottoolbox\entities
 */
class ExternalPropertyMapping extends HubSpotEntity
{
    public const DATA_TYPE_STRING = 'STRING';
    public const DATA_TYPE_NUMBER = 'NUMBER';
    public const DATA_TYPE_DATETIME = 'DATETIME';
    public const DATA_TYPE_AVATAR_IMAGE = 'AVATAR_IMAGE';

    public string $externalPropertyName = '';
    public string $hubspotPropertyName = '';
    public string $dataType = '';

    /**
     * ExternalPropertyMapping constructor.
     * @param $externalPropertyName
     * @param $hubspotPropertyName
     * @param $dataType
     */
    public function __construct($externalPropertyName, $hubspotPropertyName, $dataType)
    {
        parent::__construct([
            'externalPropertyName' => $externalPropertyName,
            'hubspotPropertyName' => $hubspotPropertyName,
            'dataType' => $dataType
        ]);
    }

    protected function defineRules(): array
    {
        $rules = parent::defineRules();
        $rules[] = [['externalPropertyName', 'hubspotPropertyName', 'dataType'], 'required'];
        $rules[] = [
            'dataType',
            'in',
            'range' => [
                self::DATA_TYPE_STRING,
                self::DATA_TYPE_NUMBER,
                self::DATA_TYPE_DATETIME,
                self::DATA_TYPE_AVATAR_IMAGE
            ]
        ];
        return $rules;
    }
}