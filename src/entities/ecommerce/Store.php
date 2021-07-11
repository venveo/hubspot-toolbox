<?php
/*
 *  @link      https://www.venveo.com
 *  @copyright Copyright (c) 2020 Venveo
 */

namespace venveo\hubspottoolbox\entities\ecommerce;


use craft\validators\HandleValidator;
use craft\validators\UriValidator;
use venveo\hubspottoolbox\entities\HubSpotEntity;

class Store extends HubSpotEntity
{
    public string $id;

    public string $label;

    public string $adminUri;

    protected function defineRules(): array
    {
        $rules = parent::defineRules();
        $rules[] = [['id', 'label'], 'required'];
        $rules[] = [['id'], HandleValidator::class];
        $rules[] = [['adminUri'], UriValidator::class];
        return $rules;
    }
}