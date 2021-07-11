<?php
/*
 *  @link      https://www.venveo.com
 *  @copyright Copyright (c) 2020 Venveo
 */

namespace venveo\hubspottoolbox\entities\ecommerce;

use venveo\hubspottoolbox\entities\HubSpotEntity;
use venveo\hubspottoolbox\enums\HubSpotObjectType;
use venveo\hubspottoolbox\validators\EmbeddedModelValidator;

/**
 * Class HubSpotSyncMessage
 * @package venveo\hubspottoolbox\entities
 */
class SyncMessagesWithMetaData extends HubSpotEntity
{
    public string $storeId;
    public string $objectType;
    /** @var <ExternalSyncMessage>[] $messages */
    public array $messages = [];


    /**
     * Adds a message to the sync messages request
     * @param ExternalSyncMessage $message
     * @param false $validate
     * @return bool
     */
    public function addMessage(ExternalSyncMessage $message, $validate = false): bool
    {
        if ($validate && !$message->validate()) {
            return false;
        }
        $this->messages[] = $message;
        return true;
    }

    public function getMessagesPayload() {
        return array_map(function(ExternalSyncMessage $m) {
            return array_filter($m->toArray());
        }, $this->messages);
    }

    protected function defineRules(): array
    {
        $rules = parent::defineRules();
        $rules[] = [['storeId', 'objectType'], 'required'];
        $rules[] = ['messages', 'each', 'rule' => [EmbeddedModelValidator::class]];
        $rules[] = ['objectType', 'in', 'range' => [
            HubSpotObjectType::Contact,
            HubSpotObjectType::Deal,
            HubSpotObjectType::LineItem,
            HubSpotObjectType::Product
        ]];
        return $rules;
    }
}