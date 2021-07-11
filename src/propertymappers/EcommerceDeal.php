<?php

namespace venveo\hubspottoolbox\propertymappers;

use craft\commerce\elements\Order;
use craft\commerce\Plugin as Commerce;
use venveo\hubspottoolbox\enums\HubSpotObjectType;
use venveo\hubspottoolbox\models\HubSpotObjectMapping;
use venveo\hubspottoolbox\traits\PreviewableMapperTrait;

class EcommerceDeal extends MultiTypePropertyMapper implements PreviewablePropertyMapperInterface
{
    use PreviewableMapperTrait;

    public static function getHubSpotObjectName(): string
    {
        return 'deals';
    }

    public static function getHubSpotObjectType(): string
    {
        return HubSpotObjectType::Deal;
    }

    public static function getObjectContext(): string
    {
        return 'ecomm.order';
    }


    public function getTemplateParams($source): array
    {
       $order = static::normalizeSource($source);
        return [
            'order' => $order
        ];
    }

    public function producePreviewObjectId()
    {
        return Order::find()->orderBy('RAND()')->one()->id;
    }

    public static function defineSourceTypes(): array
    {
        $statuses = Commerce::getInstance()->orderStatuses->allOrderStatuses;
        $sourceTypes = array_map(function ($status) {
            return new MapperSourceType([
                'displayName' => $status->name,
                'id' => 'status:' . $status->id
            ]);
        }, $statuses);

        $sourceTypes[] = new MapperSourceType([
            'displayName' => 'Checkout Pending',
            'id' => 'checkout-pending'
        ]);

        $sourceTypes[] = new MapperSourceType([
            'displayName' => 'Checkout Abandoned',
            'id' => 'checkout-abandoned'
        ]);

        return $sourceTypes;
    }

    public static function getSourceTypeName(): string
    {
        return 'Order Status';
    }

    public function getRecommendedMappings(): array
    {
        $mappings = [];
        $mappings[] = new HubSpotObjectMapping(['property' => 'dealname', 'template' => '{order.reference ?? order.number}']);
        $mappings[] = new HubSpotObjectMapping(['property' => 'amount', 'template' => '{order.total}']);
        return $mappings;
    }

    public function canBeAppliedToSource($source): bool
    {
        if ($this->sourceTypeId === null) {
            return true;
        }

        $order = static::normalizeSource($source);

        $edge = Commerce::getInstance()->getCarts()->getActiveCartEdgeDuration();
        $inactiveCutoff = new \DateTime($edge);
        $cartIsActive = $order->dateUpdated >= $inactiveCutoff;


        if ($this->sourceTypeId === 'checkout-pending' && !$order->isCompleted && $cartIsActive) {
            return true;
        }
        if ($this->sourceTypeId === 'checkout-abandoned' && !$order->isCompleted && !$cartIsActive) {
            return true;
        }

        if (strpos($this->sourceTypeId, 'status:') === 0) {
            $status = (int)substr($this->sourceTypeId, 7);
            if ($status === (int)$order->orderStatusId) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param int|Order $source
     * @return string
     */
    public static function getExternalObjectId($source)
    {
        $source = static::normalizeSource($source);
        return $source->uid;
    }

    /**
     * @param $source
     * @return Order|null
     */
    public static function normalizeSource($source)
    {
        if ($source instanceof Order) {
            return $source;
        }
        if (is_numeric($source)) {
            return Commerce::getInstance()->orders->getOrderById($source);
        }
        return null;
    }
}