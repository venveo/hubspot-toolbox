<?php
/*
 *  @link      https://www.venveo.com
 *  @copyright Copyright (c) 2020 Venveo
 */

namespace venveo\hubspottoolbox\propertymappers;

interface MultiTypeMapperInterface
{
    /**
     * Gets all source types indexed by their id
     *
     * @return MapperSourceType[]
     */
    public static function getSourceTypes(): array;

    /**
     * Gets a human readable name for the subtype
     *
     * @return string
     */
    public static function getSourceTypeName(): string;

    /**
     * Return any source types for this mapper
     *
     * @return MapperSourceType[]
     */
    public static function defineSourceTypes(): array;
}