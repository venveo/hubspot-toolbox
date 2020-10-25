<?php
/**
 * HubSpot Toolbox plugin for Craft CMS 3.x
 *
 * Turnkey HubSpot integration for CraftCMS
 *
 * @link      https://venveo.com
 * @copyright Copyright (c) 2018 Venveo
 */

namespace venveo\hubspottoolbox;

use Craft;
use craft\base\Plugin;
use craft\commerce\elements\Order;
use craft\commerce\elements\Variant;
use craft\events\ModelEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\services\Plugins;
use craft\web\UrlManager;
use venveo\hubspottoolbox\listeners\EcommerceListener;
use venveo\hubspottoolbox\models\Settings;
use venveo\hubspottoolbox\services\FeaturesService;
use venveo\hubspottoolbox\services\hubspot\EcommDealsService;
use venveo\hubspottoolbox\services\hubspot\EcommService;
use venveo\hubspottoolbox\services\hubspot\EcommSettingsService;
use venveo\hubspottoolbox\services\hubspot\PropertiesService;
use venveo\hubspottoolbox\services\OauthService;
use yii\base\Event;

/**
 * @author    Venveo
 * @package   HubspotToolbox
 * @since     1.0.0
 *
 * @property  OauthService $oauth
 * @property  FeaturesService $features
 * @property  EcommService $ecomm
 * @property  EcommDealsService $ecommDeals
 * @property  EcommSettingsService $ecommSettings
 * @property  PropertiesService $properties
 * @property-read array $cpNavItem
 * @property  Settings $settings
 * @method    Settings getSettings()
 */
class HubSpotToolbox extends Plugin
{
    public $hasCpSection = true;
    public $hasCpSettings = true;

    /**
     * @var HubSpotToolbox
     */
    public static $plugin;

    public $schemaVersion = '1.0.1';

    public function init()
    {
        parent::init();
        self::$plugin = $this;

        $this->setComponents([
            'ecomm' => EcommService::class,
            'ecommSettings' => EcommSettingsService::class,
            'ecommDeals' => EcommDealsService::class,
            'oauth' => OauthService::class,
            'features' => FeaturesService::class,
            'properties' => PropertiesService::class
        ]);

        // Add in our Twig extensions
//        Craft::$app->view->registerTwigExtension(new HubspotToolboxTwigExtension());

        // Register our fields
//        Event::on(Fields::class, Fields::EVENT_REGISTER_FIELD_TYPES, function (RegisterComponentTypesEvent $event) {
//            $event->types[] = HubSpotFormField::class;
//        }
//        );

        Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_CP_URL_RULES, function (RegisterUrlRulesEvent $event) {
            $event->rules['hubspot-toolbox'] = 'hubspot-toolbox/index';

            $event->rules['hubspot-toolbox/ecommerce'] = 'hubspot-toolbox/ecommerce';
            $event->rules['hubspot-toolbox/ecommerce/settings'] = 'hubspot-toolbox/ecommerce/settings';
            $event->rules['hubspot-toolbox/ecommerce/contacts'] = 'hubspot-toolbox/ecommerce/contacts';

            $event->rules['hubspot-toolbox/connection'] = 'hubspot-toolbox/connection/index';
        });

//        Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_SITE_URL_RULES,
//            function (RegisterUrlRulesEvent $event) {
//                $event->rules['api/hub/submit/<formId:\d+>'] = ['route' => 'hubspot-toolbox/form/submit'];
//            });

        Event::on(Plugins::class, Plugins::EVENT_AFTER_LOAD_PLUGINS, function () {
            Event::on(Variant::class, Variant::EVENT_AFTER_SAVE, function (ModelEvent $e) {
                EcommerceListener::handlePurchasableSaved($e);
            });

            Event::on(Order::class, Order::EVENT_AFTER_SAVE, function (ModelEvent $e) {
                EcommerceListener::handleOrderSaved($e);
            });
        });

//        Event::on(CraftVariable::class, CraftVariable::EVENT_INIT, function (Event $event) {
//            $variable = $event->sender;
//            $variable->set('hubspot', HubspotVariable::class);
//        });
    }

    /**
     * Creates and returns the model used to store the plugin’s settings.
     *
     * @return \craft\base\Model|null
     */
    protected function createSettingsModel()
    {
        return new Settings();
    }


    public function getCpNavItem(): array
    {
        $ret = parent::getCpNavItem();
        $ret['label'] = Craft::t('hubspot-toolbox', 'HubSpot');

        $ret['subnav']['ecommerce'] = [
            'label' => 'E-Commerce',
            'url' => 'hubspot-toolbox/ecommerce'
        ];

        $ret['subnav']['connection'] = [
            'label' => Craft::t('hubspot-toolbox', 'Connection'),
            'url' => 'hubspot-toolbox/connection'
        ];


        return $ret;
    }

    public function settingsHtml()
    {
        return Craft::$app->view->renderTemplate(
            'hubspot-toolbox/_settings',
            [
                'settings' => $this->getSettings()
            ]
        );
    }
}
