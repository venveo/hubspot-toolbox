<?php
/*
 *  @link      https://www.venveo.com
 *  @copyright Copyright (c) 2020 Venveo
 */

namespace venveo\hubspottoolbox\propertymappers;

use craft\commerce\elements\Order;
use craft\commerce\models\LineItem;
use craft\commerce\Plugin;
use venveo\hubspottoolbox\enums\HubSpotObjectType;
use venveo\hubspottoolbox\models\HubSpotObjectMapping;
use venveo\hubspottoolbox\traits\PreviewableMapperTrait;

class EcommerceLineItem extends PropertyMapper implements PreviewablePropertyMapperInterface
{
    use PreviewableMapperTrait;

    public static function getHubSpotObjectName(): string
    {
        return 'line_items';
    }

    public static function getHubSpotObjectType(): string
    {
        return HubSpotObjectType::LineItem;
    }

    public static function getObjectContext(): string
    {
        return 'ecomm.order.lineitem';
    }

    public function getTemplateParams($source): array
    {
        $lineitem = static::normalizeSource($source);
        return [
            'lineitem' => $lineitem
        ];
    }

    public function producePreviewObjectId()
    {
        $order = Order::find()->orderBy('RAND()')->hasLineItems()->one();
        return $order->lineItems[0]->id;
    }

    public function getRecommendedMappings(): array
    {
        $mappings = [];
        $mappings[] = new HubSpotObjectMapping(['property' => 'price', 'template' => '{lineitem.salePrice}']);
        $mappings[] = new HubSpotObjectMapping(['property' => 'quantity', 'template' => '{lineitem.qty}']);
        $mappings[] = new HubSpotObjectMapping(['property' => 'name', 'template' => '{lineitem.purchasable.title}']);
        $mappings[] = new HubSpotObjectMapping(['property' => 'description', 'template' => '{lineitem.description}']);
        $mappings[] = new HubSpotObjectMapping(['property' => 'discount', 'template' => '{lineitem.discount * -1}']);
        $mappings[] = new HubSpotObjectMapping(['property' => 'tax', 'template' => '{lineitem.tax}']);
        return $mappings;
    }

    /**
     * @param int|LineItem $source
     * @return int|mixed|null
     */
    public static function getExternalObjectId($source)
    {
        $source = static::normalizeSource($source);
        return $source->id;
    }

    /**
     * @param $source
     * @return LineItem|null
     */
    public static function normalizeSource($source)
    {
        if ($source instanceof LineItem) {
            return $source;
        }
        if (is_numeric($source)) {
            return Plugin::getInstance()->getLineItems()->getLineItemById($source);
        }
        return null;
    }
}