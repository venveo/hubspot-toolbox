<?php

namespace venveo\hubspottoolbox\controllers;

use craft\helpers\UrlHelper;
use venveo\hubspottoolbox\entities\ecommerce\Store;
use venveo\hubspottoolbox\HubSpotToolbox;

class EcommerceController extends BaseHubSpotController
{
    public function actionIndex()
    {
        $this->requireValidHubSpotToken();
        return $this->redirect(UrlHelper::cpUrl('hubspot-toolbox/ecommerce/settings'));
    }

    public function actionSettings()
    {
        $this->requireValidHubSpotToken();
        $feature = HubSpotToolbox::$plugin->features->getFeatureByHandle('ecommerce');
        return $this->renderTemplate('hubspot-toolbox/_ecommerce/settings', [
            'feature' => $feature,
            'stores' => HubSpotToolbox::$plugin->ecomm->getStores()
        ]);
    }

    public function actionContactProperties()
    {
        $feature = HubSpotToolbox::$plugin->features->getFeatureByHandle('ecommerce');
        $this->requireValidSettingsForFeature($feature);
        return $this->renderTemplate('hubspot-toolbox/_ecommerce/contact-properties', [
            'feature' => $feature
        ]);
    }

    public function actionDealProperties()
    {
        $feature = HubSpotToolbox::$plugin->features->getFeatureByHandle('ecommerce');
        $this->requireValidSettingsForFeature($feature);
        return $this->renderTemplate('hubspot-toolbox/_ecommerce/deal-properties', [
            'feature' => $feature
        ]);
    }

    public function actionLineitemProperties()
    {
        $feature = HubSpotToolbox::$plugin->features->getFeatureByHandle('ecommerce');
        $this->requireValidSettingsForFeature($feature);
        return $this->renderTemplate('hubspot-toolbox/_ecommerce/lineitem-properties', [
            'feature' => $feature
        ]);
    }

    public function actionProductProperties()
    {
        $feature = HubSpotToolbox::$plugin->features->getFeatureByHandle('ecommerce');
        $this->requireValidSettingsForFeature($feature);
        return $this->renderTemplate('hubspot-toolbox/_ecommerce/product-properties', [
            'feature' => $feature
        ]);
    }

    public function actionSaveStore()
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();
        $storeId = \Craft::$app->request->getRequiredBodyParam('id');
        $storeLabel = \Craft::$app->request->getRequiredBodyParam('label');
        $storeUri = \Craft::$app->request->getBodyParam('adminUri');
        $store = new Store([
            'id' => $storeId,
            'label' => $storeLabel,
            'adminUri' => $storeUri
        ]);
        if (HubSpotToolbox::$plugin->ecomm->saveStore($store)) {
            return $this->asJson($store);
        }

        \Craft::$app->response->setStatusCode(400);
        return $this->asErrorJson($store->getFirstError('storeId'));
    }
}