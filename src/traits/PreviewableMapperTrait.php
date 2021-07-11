<?php
namespace venveo\hubspottoolbox\traits;

trait PreviewableMapperTrait {
    protected $initialPreviewObjectId = null;

    public function getInitialPreviewObjectId() {
        if (!$this->initialPreviewObjectId) {
            $this->setInitialPreviewObjectId($this->producePreviewObjectId());
        }
        return $this->initialPreviewObjectId;
    }

    public function setInitialPreviewObjectId($id) {
        $this->initialPreviewObjectId = $id;
    }

    public function producePreviewObjectId() {
        throw new \Exception('This method should be implemented on the mapper');
    }
}