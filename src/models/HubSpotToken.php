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
use DateTime;

class HubSpotToken extends Model
{

    /**
     * @var int|null
     */
    public $id;

    /**
     * @var int
     */
    public $hubId;

    /**
     * @var int
     */
    public $appId;

    /**
     * @var string|null
     */
    public $accessToken;

    /**
     * @var string|null
     */
    public $refreshToken;

    /**
     * @var DateTime|null
     */
    public $dateExpires;

    /**
     * @var DateTime|null
     */
    public $dateCreated;

    /**
     * @var DateTime|null
     */
    public $dateUpdated;

    /**
     * @var string
     */
    public $uid;

    /**
     * Has token expired.
     *
     * @return bool
     */
    public function hasExpired()
    {
        $now = new DateTime();
        $dateExpires = $this->dateExpires;

        return $now->getTimestamp() > $dateExpires->getTimestamp();
    }

    /**
     * Remaining seconds before token expiry.
     *
     * @return int
     */
    public function getRemainingSeconds()
    {
        $now = new DateTime();
        $dateExpires = $this->dateExpires;

        return $dateExpires->getTimestamp() - $now->getTimestamp();
    }

    /**
     * @inheritdoc
     */
    public function datetimeAttributes(): array
    {
        $attributes = parent::datetimeAttributes();

        $attributes[] = 'dateExpires';

        return $attributes;
    }
}
