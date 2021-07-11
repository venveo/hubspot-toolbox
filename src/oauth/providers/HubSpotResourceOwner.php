<?php
/**
 * Originally by: https://github.com/helpscout/oauth2-hubspot
 */

namespace venveo\hubspottoolbox\oauth\providers;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class HubSpotResourceOwner implements ResourceOwnerInterface
{
    /**
     * Raw response
     *
     * @var array
     */
    protected $response;

    /**
     * @param array $response
     */
    public function __construct(array $response)
    {
        $this->response = $response;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->getResponseData('user_id');
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->getResponseData('user');
    }

    /**
     * @return integer
     */
    public function getHubId()
    {
        return $this->getResponseData('hub_id');
    }

    /**
     * @return integer
     */
    public function getAppId()
    {
        return $this->getResponseData('app_id');
    }

    /**
     * @return string
     */
    public function getHubSpotDomain()
    {
        return $this->getResponseData('hub_domain');
    }

    /**
     * @return string
     */
    public function getScopes()
    {
        return $this->getResponseData('scopes');
    }


    /**
     * Attempts to pull value from array using dot notation.
     *
     * @param string $path
     * @param string $default
     *
     * @return mixed
     */
    protected function getResponseData($path, $default = null)
    {
        $array = $this->response;

        if (!empty($path)) {
            $keys = explode('.', $path);

            foreach ($keys as $key) {
                if (isset($array[$key])) {
                    $array = $array[$key];
                } else {
                    return $default;
                }
            }
        }

        return $array;
    }

    public function toArray()
    {
        return $this->response;
    }
}