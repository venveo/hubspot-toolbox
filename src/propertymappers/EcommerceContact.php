<?php

namespace venveo\hubspottoolbox\propertymappers;

use craft\commerce\elements\Order;
use craft\commerce\models\Customer;
use craft\commerce\Plugin;
use venveo\hubspottoolbox\enums\HubSpotObjectType;
use venveo\hubspottoolbox\traits\PreviewableMapperTrait;

class EcommerceContact extends PropertyMapper implements PreviewablePropertyMapperInterface
{
    use PreviewableMapperTrait;

    public static function getHubSpotObjectName(): string
    {
        return 'contacts';
    }

    public static function getHubSpotObjectType(): string
    {
        return HubSpotObjectType::Contact;
    }

    public static function getObjectContext(): string
    {
        return 'ecomm.customer';
    }

    public function getTemplateParams($source): array
    {
        $customer = static::normalizeSource($source);
        return [
            'customer' => $customer
        ];
    }

    public function producePreviewObjectId()
    {
        /** @var Order $order */
        $order = Order::find()->orderBy('RAND()')->isCompleted(true)->one();
        if (!$order) {
            return null;
        }
        return $order->customer->id;
    }

    public function getRecommendedMappings(): array
    {
        // TODO: Refactor
        $mappings = [];
        $mappings[] = new HubSpotObjectMapping([
            'property' => 'firstname',
            'template' => '{customer.primaryBillingAddress.firstName}'
        ]);
        $mappings[] = new HubSpotObjectMapping([
            'property' => 'lastname',
            'template' => '{customer.primaryBillingAddress.lastName}'
        ]);
        $mappings[] = new HubSpotObjectMapping(['property' => 'email', 'template' => '{customerOrder.email}']);
        $mappings[] = new HubSpotObjectMapping([
            'property' => 'phone',
            'template' => '{customer.primaryShippingAddress.phone|default(customer.primaryBillingAddress.phone)}'
        ]);
        $mappings[] = new HubSpotObjectMapping([
            'property' => 'city',
            'template' => '{customer.primaryShippingAddress.city}'
        ]);
        $mappings[] = new HubSpotObjectMapping([
            'property' => 'state',
            'template' => '{customer.primaryShippingAddress.state}'
        ]);
        $mappings[] = new HubSpotObjectMapping([
            'property' => 'zip',
            'template' => '{customer.primaryShippingAddress.zipCode}'
        ]);
        $mappings[] = new HubSpotObjectMapping([
            'property' => 'country',
            'template' => '{customer.primaryShippingAddress.country}'
        ]);
        return $mappings;
    }

    /**
     * @param Customer|Order|int $source
     * @return int|mixed|string|null
     */
    public static function getExternalObjectId($source)
    {
        $source = static::normalizeSource($source);
        return $source->email;
    }

    /**
     * @param $source
     * @return Customer|null
     */
    public static function normalizeSource($source)
    {
        if ($source instanceof Customer) {
            return $source;
        }
        if ($source instanceof Order) {
            return $source->customer;
        }
        if (is_numeric($source)) {
            return Plugin::getInstance()->customers->getCustomerById($source);
        }
    }
}