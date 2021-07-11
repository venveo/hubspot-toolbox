<?php
/*
 *  @link      https://www.venveo.com
 *  @copyright Copyright (c) 2020 Venveo
 */

namespace venveo\hubspottoolbox\controllers;

use craft\helpers\UrlHelper;
use craft\web\Controller;
use venveo\hubspottoolbox\features\HubSpotFeatureInterface;
use venveo\hubspottoolbox\HubSpotToolbox;
use venveo\hubspottoolbox\traits\HubSpotApiKeyAuthorization;
use venveo\hubspottoolbox\traits\HubSpotDevAuthorization;
use venveo\hubspottoolbox\traits\HubSpotTokenAuthorization;

abstract class BaseHubSpotController extends Controller
{
    use HubSpotTokenAuthorization;
    use HubSpotApiKeyAuthorization;
    use HubSpotDevAuthorization;

    /**
     * @throws \yii\base\ExitException
     */
    public function requireValidHubSpotToken()
    {
        try {
            $this->getHubSpot();
        } catch (\Exception $e) {
            if (HubSpotToolbox::$plugin->getSettings()->validate()) {
                \Craft::$app->session->setError('Please verify your connection');
                $this->redirect(UrlHelper::cpUrl('hubspot-toolbox/connection'));
            } else {
                \Craft::$app->session->setError('Please verify your connection settings');
                $this->redirect(UrlHelper::cpUrl('settings/plugins/hubspot-toolbox'));
            }
            \Craft::$app->end();
        }
    }

    /**
     * @param $featureHandle
     * @throws \yii\base\ExitException
     */
    public function requireValidSettingsForFeature(HubSpotFeatureInterface $feature)
    {
        if (!$feature->validate()) {
            \Craft::$app->session->setError('This feature requires additional settings');
            $this->redirect($feature->getCpEditUrl());
            \Craft::$app->end();
        }
    }
}