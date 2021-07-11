<?php

namespace venveo\hubspottoolbox\controllers;

use Craft;
use craft\helpers\UrlHelper;
use craft\web\Controller;
use venveo\hubspottoolbox\HubSpotToolbox;
use venveo\hubspottoolbox\oauth\providers\HubSpot;
use venveo\hubspottoolbox\oauth\providers\HubSpotResourceOwner;
use venveo\hubspottoolbox\services\OauthService;
use yii\web\Response;

/**
 */
class ConnectionController extends Controller
{
    /**
     * @var HubSpot $provider
     */
    protected $provider = null;

    public function init()
    {
        parent::init();
        $this->provider = HubSpotToolbox::$plugin->oauth->getProvider();
    }

    public function actionAuthorize()
    {
        return $this->provider->authorize(['scope' => OauthService::$scopes], function ($url, $provider) {
            Craft::$app->session->set('oauth2state', $provider->getState());
            return Craft::$app->response->redirect($url);
        });
    }

    public function actionDeauthorize()
    {
        HubSpotToolbox::$plugin->oauth->deleteToken();
        return $this->redirect(UrlHelper::cpUrl('hubspot-toolbox/connection'));
    }


    /**
     * OAuth callback.
     *
     * @return Response
     * @throws \yii\base\InvalidConfigException
     */
    public function actionCallback()
    {
        $code = Craft::$app->request->getRequiredQueryParam('code');
        $state = Craft::$app->request->getRequiredQueryParam('state');
        $sessionState = Craft::$app->session->get('oauth2state');
        Craft::$app->session->remove('oauth2state');
        if ($state !== $sessionState) {
            Craft::$app->session->setError('Invalid oAuth2 State');
            return $this->redirect(UrlHelper::cpUrl('hubspot-toolbox/connection'));
        }

        $accessToken = $this->provider->getAccessToken('authorization_code', [
            'code' => $code
        ]);
        /** @var HubSpotResourceOwner $resourceOwner */
        $resourceOwner = $this->provider->getResourceOwner($accessToken);
        $tokenModel = HubSpotToolbox::$plugin->oauth->convertLeagueTokenToModel($accessToken,
            $resourceOwner->getHubId(), $resourceOwner->getAppId());
        HubSpotToolbox::$plugin->oauth->saveToken($tokenModel);

        Craft::$app->session->setNotice('Connected to HubSpot Account');
        return Craft::$app->response->redirect(UrlHelper::cpUrl('hubspot-toolbox/connection'));
    }

    public function actionIndex()
    {
        $settings = HubSpotToolbox::$plugin->getSettings();
        $token = HubSpotToolbox::$plugin->oauth->getToken(Craft::parseEnv($settings->appId));
        $owner = null;

        if ($token) {
            /** @var HubSpotResourceOwner $owner */
            $owner = $this->provider->getResourceOwner(HubSpotToolbox::$plugin->oauth->convertTokenToLeagueToken($token));
        }

        return $this->renderTemplate('hubspot-toolbox/_connection/index', [
            'token' => $token,
            'resourceOwner' => $owner
        ]);
    }
}
