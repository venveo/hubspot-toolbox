<?php
/*
 *  @link      https://www.venveo.com
 *  @copyright Copyright (c) 2020 Venveo
 */

namespace venveo\hubspottoolbox\propertymappers;


use craft\helpers\ArrayHelper;

abstract class MultiTypePropertyMapper extends PropertyMapper implements MultiTypeMapperInterface
{
    /**
     * Gets all sources types indexed by ID. These should be defined via  defineSourceTypes()
     *
     * @return MapperSourceType[] indexed by id
     */
    final public static function getSourceTypes(): array
    {
        return ArrayHelper::index(static::defineSourceTypes(), 'id');
    }

    /**
     * @inheritdoc
     */
    public function fields()
    {
        $fields = parent::fields();
        $fields[] = 'sourceTypes';
        $fields[] = 'sourceTypeName';
        return $fields;
    }
}