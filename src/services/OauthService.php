<?php

namespace venveo\hubspottoolbox\services;

use Craft;
use craft\base\Component;
use craft\helpers\UrlHelper;
use DateTime;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use venveo\hubspottoolbox\HubSpotToolbox;
use venveo\hubspottoolbox\models\HubSpotToken;
use venveo\hubspottoolbox\models\HubSpotToken as HubSpotTokenModel;
use venveo\hubspottoolbox\models\Settings;
use venveo\hubspottoolbox\oauth\providers\HubSpot as HubSpotProvider;
use venveo\hubspottoolbox\records\HubSpotToken as HubSpotTokenRecord;

class OauthService extends Component
{
    protected Settings $settings;

    static $scopes = [
        'actions',
        'automation',
        'timeline',
        'oauth',
        'files',
        'e-commerce',
        'hubdb',
        'contacts',
        'forms',
        'content',
        'reports',
        'automation',
        'integration-sync',
    ];

    public function init()
    {
        parent::init();

        /** @var Settings settings */
        $this->settings = HubSpotToolbox::$plugin->getSettings();
    }

    /**
     * @return HubSpotProvider
     */
    public function getProvider()
    {
        return new HubSpotProvider([
            'clientId' => Craft::parseEnv($this->settings->clientId),
            'clientSecret' => Craft::parseEnv($this->settings->clientSecret),
            'redirectUri' => UrlHelper::actionUrl('hubspot-toolbox/connection/callback')
        ]);
    }


    /**
     * Saves the OAuth token.
     *
     * @param HubSpotTokenModel $token
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function saveToken(HubSpotTokenModel $token)
    {
        $oauthTokenRecord = HubSpotTokenRecord::find()->where([
            'appId' => $token->appId,
            'hubId' => $token->hubId
        ])->one();

        if (!$oauthTokenRecord) {
            $oauthTokenRecord = new HubSpotTokenRecord();
        }

        $oauthTokenRecord->accessToken = $token->accessToken;
        $oauthTokenRecord->refreshToken = $token->refreshToken;
        $oauthTokenRecord->dateExpires = $token->dateExpires;
        $oauthTokenRecord->appId = $token->appId;
        $oauthTokenRecord->hubId = $token->hubId;
        $oauthTokenRecord->save();
    }

    /**
     * @param AccessToken $token
     * @param $hubId
     * @param $appId
     * @return HubSpotTokenModel
     */
    public function convertLeagueTokenToModel(AccessToken $token, $hubId, $appId): HubSpotToken
    {
        $ts = $token->getExpires();
        $tokenModel = new HubSpotTokenModel();
        $tokenModel->refreshToken = $token->getRefreshToken();
        $tokenModel->accessToken = $token->getToken();
        $tokenModel->dateExpires = new DateTime("@$ts");
        $tokenModel->hubId = $hubId;
        $tokenModel->appId = $appId;
        return $tokenModel;
    }

    public function convertTokenToLeagueToken(HubSpotTokenModel $token): AccessToken {
        $dataObject = [
            'access_token' => $token->accessToken,
            'refresh_token' => $token->refreshToken,
            'expires' => $token->dateExpires->getTimestamp(),
        ];
        return new AccessToken($dataObject);
    }

    public function refreshToken(HubSpotToken $oldToken)
    {
        try {
            $accessToken = $this->provider->getAccessToken('refresh_token', [
                'refresh_token' => $oldToken->refreshToken
            ]);

            $newToken = $this->convertLeagueTokenToModel($accessToken, $oldToken->hubId, $oldToken->appId);
            $this->saveToken($newToken);
        } catch (IdentityProviderException $exception) {
            $this->deleteToken();
            return null;
        }
        return $newToken;
    }

    /**
     * Returns the OAuth token.
     *
     * @return HubSpotToken|null
     */
    public function getToken($appId, $attemptRefresh = true)
    {
        // Or use the token from the database otherwise
        $oauthTokenRecord = HubSpotTokenRecord::find()
            ->where(['appId' => $appId])
            ->one();

        if (!$oauthTokenRecord) {
            return null;
        }
        $token = new HubSpotTokenModel($oauthTokenRecord->getAttributes());

        if (!$token) {
            return null;
        }
        if ($token->hasExpired() && $attemptRefresh) {
            $token = $this->refreshToken($token);
        }

        return $token;
    }

    /**
     * Deletes an OAuth token.
     */
    public function deleteToken()
    {
        $oauthToken = HubSpotTokenRecord::find()
            ->one();

        if ($oauthToken) {
            $oauthToken->delete();
        }
    }
}
