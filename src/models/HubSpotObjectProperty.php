<?php

namespace venveo\hubspottoolbox\models;

use craft\base\Model;

/**
 * Class HubSpotObjectProperty
 * @package venveo\hubspottoolbox\models
 */
class HubSpotObjectProperty extends Model
{
    public $id;
    public $objectType;
    public $name;
    public $dataType;

    /**
     * @return array
     */
    public function defineRules(): array
    {
        $rules = parent::defineRules();
        $rules[] = [['id'], 'number', 'integerOnly' => true];
        $rules[] = [['objectType', 'name', 'dataType'], 'required'];
        return $rules;
    }
}
