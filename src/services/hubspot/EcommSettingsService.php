<?php
/**
 * HubSpot Toolbox plugin for Craft CMS 3.x
 *
 * Turnkey HubSpot integration for CraftCMS
 *
 * @link      https://venveo.com
 * @copyright Copyright (c) 2018 Venveo
 */

namespace venveo\hubspottoolbox\services\hubspot;

use craft\base\Component;
use SevenShores\Hubspot\Exceptions\HubspotException;
use venveo\hubspottoolbox\entities\ecommerce\ExternalPropertyMapping;
use venveo\hubspottoolbox\entities\ecommerce\ExternalSyncSettings;
use venveo\hubspottoolbox\entities\ecommerce\Settings as EcommerceBridgeSettings;
use venveo\hubspottoolbox\enums\HubSpotObjectType;
use venveo\hubspottoolbox\HubSpotToolbox;
use venveo\hubspottoolbox\models\HubSpotObjectProperty;
use venveo\hubspottoolbox\propertymappers\EcommerceContact;
use venveo\hubspottoolbox\propertymappers\EcommerceDeal;
use venveo\hubspottoolbox\propertymappers\EcommerceLineItem;
use venveo\hubspottoolbox\propertymappers\EcommerceProduct;
use venveo\hubspottoolbox\traits\HubSpotApiKeyAuthorization;

class EcommSettingsService extends Component
{
    use HubSpotApiKeyAuthorization;

    public function getMappingTypeFromProperty(HubSpotObjectProperty $property): string
    {
        $type = $property->dataType;
        switch($type) {
            case 'number':
                return ExternalPropertyMapping::DATA_TYPE_NUMBER;
            case 'datetime':
                return ExternalPropertyMapping::DATA_TYPE_DATETIME;
            default:
                return ExternalPropertyMapping::DATA_TYPE_STRING;
        }
    }

    /**
     * @param HubSpotObjectProperty $property
     * @return ExternalPropertyMapping
     */
    protected function constructExternalPropertyMapping(HubSpotObjectProperty $property): ExternalPropertyMapping
    {
        return new ExternalPropertyMapping($property->name, $property->name,
            $this->getMappingTypeFromProperty($property));
    }

    public function getMappingSettings()
    {
        try {
            $results = $this->getHubSpotFromKey()->ecommerceBridge()->getSettings();
        } catch (HubspotException $e) {
            \Craft::dd($e->getResponse()->getBody()->getContents());
        }
        return $results->getData();
    }

    protected function getContactProperties(): ExternalSyncSettings
    {
        $properties = HubSpotToolbox::$plugin->properties->getAllPropertiesForMapperType(EcommerceContact::class);
        $props = [];
        foreach ($properties as $property) {
            $props[] = $this->constructExternalPropertyMapping($property);
        }
        return new ExternalSyncSettings([
            'properties' => $props
        ]);

        /*
                $props[] = new ExternalPropertyMapping('email', 'email',
                    ExternalPropertyMapping::DATA_TYPE_STRING);
                $props[] = new ExternalPropertyMapping('firstname', 'firstname',
                    ExternalPropertyMapping::DATA_TYPE_STRING);
                $props[] = new ExternalPropertyMapping('lastname', 'lastname',
                    ExternalPropertyMapping::DATA_TYPE_STRING);
                $props[] = new ExternalPropertyMapping('billing_company', 'billing_company',
                    ExternalPropertyMapping::DATA_TYPE_STRING);
                $props[] = new ExternalPropertyMapping('billing_phone', 'billing_phone',
                    ExternalPropertyMapping::DATA_TYPE_STRING);
                $props[] = new ExternalPropertyMapping('billing_mobile', 'billing_mobile',
                    ExternalPropertyMapping::DATA_TYPE_STRING);
                $props[] = new ExternalPropertyMapping('billing_address_1', 'billing_address_1',
                    ExternalPropertyMapping::DATA_TYPE_STRING);
                $props[] = new ExternalPropertyMapping('billing_city', 'billing_city',
                    ExternalPropertyMapping::DATA_TYPE_STRING);
                $props[] = new ExternalPropertyMapping('billing_state', 'billing_state',
                    ExternalPropertyMapping::DATA_TYPE_STRING);
                $props[] = new ExternalPropertyMapping('billing_country', 'billing_country',
                    ExternalPropertyMapping::DATA_TYPE_STRING);
                $props[] = new ExternalPropertyMapping('billing_postcode', 'billing_postcode',
                    ExternalPropertyMapping::DATA_TYPE_STRING);
        */

    }

    protected function getProductProperties(): ExternalSyncSettings
    {
        $properties = HubSpotToolbox::$plugin->properties->getAllPropertiesForMapperType(EcommerceProduct::class);
        $props = [];
        foreach ($properties as $property) {
            $props[] = $this->constructExternalPropertyMapping($property);
        }
        return new ExternalSyncSettings([
            'properties' => $props
        ]);
//        $props = [];
//        $props[] = new ExternalPropertyMapping('name', 'name',
//            ExternalPropertyMapping::DATA_TYPE_STRING);
//        $props[] = new ExternalPropertyMapping('hs_sku', 'hs_sku',
//            ExternalPropertyMapping::DATA_TYPE_STRING);
//        $props[] = new ExternalPropertyMapping('ip__ecomm_bridge__image_url', 'ip__ecomm_bridge__image_url',
//            ExternalPropertyMapping::DATA_TYPE_STRING);
//        $props[] = new ExternalPropertyMapping('price', 'price',
//            ExternalPropertyMapping::DATA_TYPE_NUMBER);
//        $props[] = new ExternalPropertyMapping('description', 'description',
//            ExternalPropertyMapping::DATA_TYPE_STRING);
    }

    protected function getDealProperties(): ExternalSyncSettings
    {
        $properties = HubSpotToolbox::$plugin->properties->getAllPropertiesForMapperType(EcommerceDeal::class);
        $props = [];
        foreach ($properties as $property) {
            $props[] = $this->constructExternalPropertyMapping($property);
        }
        return new ExternalSyncSettings([
            'properties' => $props
        ]);
//        $props[] = new ExternalPropertyMapping('dealstage', 'dealstage',
//            ExternalPropertyMapping::DATA_TYPE_STRING);
//        $props[] = new ExternalPropertyMapping('dealname', 'dealname',
//            ExternalPropertyMapping::DATA_TYPE_STRING);
//        $props[] = new ExternalPropertyMapping('closedate', 'closedate',
//            ExternalPropertyMapping::DATA_TYPE_STRING);
//        $props[] = new ExternalPropertyMapping('createdate', 'createdate',
//            ExternalPropertyMapping::DATA_TYPE_STRING);
//        $props[] = new ExternalPropertyMapping('amount', 'amount',
//            ExternalPropertyMapping::DATA_TYPE_NUMBER);
//        $props[] = new ExternalPropertyMapping('ip__ecomm_bridge__abandoned_cart_url', 'ip__ecomm_bridge__abandoned_cart_url',
//            ExternalPropertyMapping::DATA_TYPE_STRING);
//        $props[] = new ExternalPropertyMapping('ip__ecomm_bridge__discount_amount', 'ip__ecomm_bridge__discount_amount',
//            ExternalPropertyMapping::DATA_TYPE_NUMBER);
//        $props[] = new ExternalPropertyMapping('ip__ecomm_bridge__order_number', 'ip__ecomm_bridge__order_number',
//            ExternalPropertyMapping::DATA_TYPE_STRING);
//        $props[] = new ExternalPropertyMapping('ip__ecomm_bridge__shipment_ids', 'ip__ecomm_bridge__shipment_ids',
//            ExternalPropertyMapping::DATA_TYPE_STRING);
//        $props[] = new ExternalPropertyMapping('ip__ecomm_bridge__tax_amount', 'ip__ecomm_bridge__tax_amount',
//            ExternalPropertyMapping::DATA_TYPE_NUMBER);
//        $props[] = new ExternalPropertyMapping('description', 'description',
//            ExternalPropertyMapping::DATA_TYPE_STRING);
    }

    protected function getLineItemProperties(): ExternalSyncSettings
    {
        $properties = HubSpotToolbox::$plugin->properties->getAllPropertiesForMapperType(EcommerceLineItem::class);
        $props = [];
        foreach ($properties as $property) {
            $props[] = $this->constructExternalPropertyMapping($property);
        }
        return new ExternalSyncSettings([
            'properties' => $props
        ]);

//        $props[] = new ExternalPropertyMapping('discount_amount', 'discount',
//            ExternalPropertyMapping::DATA_TYPE_NUMBER);
//        $props[] = new ExternalPropertyMapping('quantity', 'quantity',
//            ExternalPropertyMapping::DATA_TYPE_NUMBER);
//        $props[] = new ExternalPropertyMapping('name', 'name',
//            ExternalPropertyMapping::DATA_TYPE_STRING);
//        $props[] = new ExternalPropertyMapping('price', 'price',
//            ExternalPropertyMapping::DATA_TYPE_NUMBER);
//        $props[] = new ExternalPropertyMapping('description', 'description',
//            ExternalPropertyMapping::DATA_TYPE_STRING);
//        $props[] = new ExternalPropertyMapping('amount', 'amount',
//            ExternalPropertyMapping::DATA_TYPE_NUMBER);
//        $props[] = new ExternalPropertyMapping('tax_amount', 'tax_amount',
//            ExternalPropertyMapping::DATA_TYPE_NUMBER);
    }


    public function saveMappingSettings()
    {
//        UrlHelper::actionUrl('hubspot-toolbox/ecommerce-import/webhook-import')
        $settings = new EcommerceBridgeSettings();
        $settings->webhookUri = null;
        $settings->enabled = true;
        $settings->setObjectSettings(HubSpotObjectType::Contact, $this->getContactProperties());
        $settings->setObjectSettings(HubSpotObjectType::Product, $this->getProductProperties());
        $settings->setObjectSettings(HubSpotObjectType::Deal, $this->getDealProperties());
        $settings->setObjectSettings(HubSpotObjectType::LineItem, $this->getLineItemProperties());
        try {
            $response = $this->getHubSpotFromKey()->ecommerceBridge()->upsertSettings($settings->prepareForApi());
        } catch (HubspotException $e) {
            \Craft::dd($e->getResponse()->getBody()->getContents());
        }
    }

    public function deleteMappingSettings()
    {
        return $this->getHubSpotFromKey()->ecommerceBridge()->deleteSettings()->getData();
    }
}
