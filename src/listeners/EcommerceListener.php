<?php
/*
 *  @link      https://www.venveo.com
 *  @copyright Copyright (c) 2020 Venveo
 */

namespace venveo\hubspottoolbox\listeners;

use craft\commerce\base\Purchasable;
use craft\commerce\elements\Order;
use craft\events\ModelEvent;
use venveo\hubspottoolbox\entities\ecommerce\SyncMessagesWithMetaData;
use venveo\hubspottoolbox\enums\HubSpotObjectType;
use venveo\hubspottoolbox\features\EcommerceFeature;
use venveo\hubspottoolbox\HubSpotToolbox;
use venveo\hubspottoolbox\propertymappers\EcommerceProduct;

class EcommerceListener
{
    public static function handlePurchasableSaved(ModelEvent $e)
    {
        $pipeline = HubSpotToolbox::$plugin->propertyMappings->createPropertyMapperPipeline(EcommerceProduct::class);
        /** @var EcommerceFeature $feature */
        $settings = HubSpotToolbox::$plugin->features->getFeatureByHandle('ecommerce');

        /** @var Purchasable $purchasable */
        $purchasable = $e->sender;

        $syncMessageWrapper = new SyncMessagesWithMetaData([
            'storeId' => $settings->storeId,
            'objectType' => HubSpotObjectType::Product
        ]);

        $message = $pipeline->produceExternalSyncMessage($purchasable->id);
        $syncMessageWrapper->addMessage($message);
        HubSpotToolbox::$plugin->ecomm->sendSyncMessages($syncMessageWrapper);
    }

    public static function handlePurchasableDeleted(ModelEvent $e)
    {
        // TODO:
    }

    public static function handleOrderSaved(ModelEvent $e)
    {
        if ($e->isNew) {
            return null;
        }
        /** @var Order $order */
        $order = $e->sender;
        $orderQuery = (Order::find()->id($order->id));
        HubSpotToolbox::$plugin->ecommSync->syncOrders($orderQuery);
    }
}