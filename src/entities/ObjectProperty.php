<?php
/*
 *  @link      https://www.venveo.com
 *  @copyright Copyright (c) 2020 Venveo
 */

namespace venveo\hubspottoolbox\entities;

/**
 * @package venveo\hubspottoolbox\entities
 */
class ObjectProperty extends HubSpotEntity
{
    // HubSpot attributes
    public $name;
    public $label;
    public $description;
    public $groupName;
    public $type;
    public $fieldType;
    public $hidden;
    public $deleted;
    public $currencyPropertyName;
    public $displayOrder;
    public $textDisplayHint;
    public $numberDisplayHint;
    public $optionsAreMutable;
    public $isCustomizedDefault;
    public $createdAt;
    public $updatedAt;
    public $options;
    public $formField;
    public $readOnlyValue;
    public $readOnlyDefinition;
    public $mutableDefinitionNotDeletable;
    public $favorited;
    public $favoritedOrder;
    public $calculated;
    public $externalOptions;
    public $displayMode;
    public $showCurrencySymbol;
    public $hubspotDefined;
    public $searchTextAnalysisMode;
    public $updatedUserId;
    public $hasUniqueValue;
    public $referencedObjectType;
    public $createdUserId;
    public $searchableInGlobalSearch;
    public $optionSortStrategy;

    protected function defineRules(): array
    {
        $rules = parent::defineRules();
        return $rules;
    }
}