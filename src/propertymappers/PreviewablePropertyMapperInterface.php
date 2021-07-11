<?php

namespace venveo\hubspottoolbox\propertymappers;

/**
 * Interface PreviewablePropertyMapperInterface
 * @package venveo\hubspottoolbox\propertymappers
 * @property-read null $initialPreviewObjectId
 */
interface PreviewablePropertyMapperInterface
{
    /**
     * Returns a random ID that can be previewed on this mapper
     *
     * @return mixed
     */
    public function getInitialPreviewObjectId();

    public function setInitialPreviewObjectId($id);

    public function producePreviewObjectId();
}