<?php

namespace venveo\hubspottoolbox\models;

use Carbon\Carbon;
use SevenShores;
use venveo\hubspottoolbox\records\TokenRecord;
use yii\base\Model;

/**
 * Class Link
 *
 *
 * @property mixed $connectURL
 * @property string $cacheKey
 * @property string $loginURL
 * @property null|\venveo\hubspottoolbox\records\TokenRecord $token
 */
class HubSpotApp extends Model
{
    public $clientId;
    public $clientSecret;
    public $appId;
    public $appName;
    public $handle = null;
    public $scopes;

    private $provider;
    private $hubspotService;

    /**
     * Link constructor.
     *
     * @param array $config
     */
    public function __construct($config = [])
    {
        parent::__construct($config);
    }

    public function getCacheKey()
    {
        return 'hbspt.app.'.$this->handle.'.token';
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return '';
    }

    public function getProvider()
    {
        $callback = \Craft::$app->getConfig()->getGeneral()->siteUrl.'/'.\Craft::$app->getConfig()->getGeneral()->cpTrigger.'/hubspot-toolbox/oauth/'.$this->handle.'/callback';

        if (!$this->provider) {
            $this->provider = new \Flipbox\OAuth2\Client\Provider\HubSpot([
                'clientId' => $this->clientId,
                'clientSecret' => $this->clientSecret,
                'redirectUri' => $callback
            ]);
        }
        return $this->provider;
    }

    public function getConnectURL()
    {
        return $this->getProvider()->getAuthorizationUrl([
            'scope' => $this->scopes
        ]);
    }

    /**
     * Gets the URL for the login
     *
     * @return string
     */
    public function getLoginURL()
    {
        $url = \Craft::$app->getConfig()->getGeneral()->siteUrl.'/'.\Craft::$app->getConfig()->getGeneral()->cpTrigger.'/hubspot-toolbox/oauth/'.$this->handle.'/login';
        return $url;
    }

    /**
     * Finds a token for this app
     *
     * @return null|TokenRecord
     */
    public function getToken()
    {
        // First check the cache
        if ($token = \Craft::$app->cache->get($this->getCacheKey())) {
            $token = unserialize($token, [TokenRecord::class]);
            if ($token instanceof TokenRecord) {
                return $token;
            }
        }

        // Check to see if we even have one in the db
        $token = TokenRecord::findOne(['appHandle' => $this->handle]);
        if (!$token) {
            return null;
        }

        // Make sure it's not expired (with 1 minute buffer)
        if (time() < $token->expires - 60) {
            return $token;
        }

        // It's expired, so let's create a new one
        $newToken = $this->getProvider()->getAccessToken('refresh_token',
            [
                'refresh_token' => $token->refreshToken
            ]);
        $token->refreshToken = $newToken->getRefreshToken();
        $token->accessToken = $newToken->getToken();
        $token->expires = $newToken->getExpires();
        $token->save();
        \Craft::$app->cache->set($this->getCacheKey(), serialize($token), $newToken->getExpires() - time() - 60);

        return $token;
    }

    /**
     * Gets the hubspot service helper
     *
     * @return SevenShores\Hubspot\Factory
     */
    public function getHubSpotService()
    {
        if (!$this->hubspotService instanceof SevenShores\Hubspot\Factory) {
            $this->hubspotService = new SevenShores\Hubspot\Factory(
                [
                    'oauth2' => true,
                    'key' => $this->getToken()->accessToken
                ]
            );
        }
        return $this->hubspotService;
    }
}
