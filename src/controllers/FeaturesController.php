<?php

namespace venveo\hubspottoolbox\controllers;

use craft\helpers\UrlHelper;
use craft\web\Controller;
use venveo\hubspottoolbox\features\HubSpotFeature;
use venveo\hubspottoolbox\HubSpotToolbox;

/**
 */
class FeaturesController extends Controller
{
    public function actionSave()
    {
        $this->requirePostRequest();
        $feature = HubSpotToolbox::$plugin->features->getFeatureByHandle(\Craft::$app->request->getRequiredBodyParam('feature'));
        $settings = \Craft::$app->request->getRequiredBodyParam('settings');
        \Craft::configure($feature, $settings);
        $feature->enabled = true;

        if (HubSpotToolbox::$plugin->features->saveFeature($feature)) {
            \Craft::$app->session->setNotice('Saved settings');
            return null;
        }
        \Craft::$app->session->setError('Oops! Something went wrong.');
        return null;
    }
}
