<?php
/**
 * HubSpot Toolbox plugin for Craft CMS 3.x
 *
 * Turnkey HubSpot integration for CraftCMS
 *
 * @link      https://venveo.com
 * @copyright Copyright (c) 2018 Venveo
 */

namespace venveo\hubspottoolbox\records;

use craft\db\ActiveRecord;

/**
 * HubSpotFormRecord Record
 *
 * @property int id
 * @property string dateExpires
 * @property string accessToken
 * @property int appId
 * @property int hubId
 * @property null|string refreshToken
 */
class HubSpotToken extends ActiveRecord
{

    /**
     * @inheritdoc
     * @return string
     */
    public static function tableName(): string
    {
        return '{{%hubspot_tokens}}';
    }
}
