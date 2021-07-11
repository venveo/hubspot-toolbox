<?php
/**
 * HubSpot Toolbox plugin for Craft CMS 3.x
 *
 * Turnkey HubSpot integration for CraftCMS
 *
 * @link      https://venveo.com
 * @copyright Copyright (c) 2018 Venveo
 */

namespace venveo\hubspottoolbox\models;

use craft\base\Model;
use craft\behaviors\EnvAttributeParserBehavior;

/**
 * HubspotToolbox Settings Model
 *
 * This is a model used to define the plugin's settings.
 *
 * Models are containers for data. Just about every time information is passed
 * between services, controllers, and templates in Craft, it’s passed via a model.
 *
 * https://craftcms.com/docs/plugins/models
 *
 * @author    Venveo
 * @package   HubspotToolbox
 * @since     1.0.0
 */
class Settings extends Model
{

    public $appId = '';
    public $clientId = '';
    public $clientSecret = '';
    public $apiKey = '';
    public $devApiKey = '';


    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['parser'] = [
            'class' => EnvAttributeParserBehavior::class,
            'attributes' => [
                'appId',
                'clientId',
                'clientSecret',
                'apiKey',
                'devApiKey',
            ],
        ];
        return $behaviors;
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['appId', 'clientId', 'clientSecret', 'devApiKey', 'apiKey'], 'required']
        ];
    }
}
