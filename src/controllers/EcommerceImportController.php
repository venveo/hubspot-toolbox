<?php
namespace venveo\hubspottoolbox\controllers;

use craft\helpers\Json;
use craft\web\Controller;

class EcommerceImportController extends Controller {

    public function actionWebhookImport() {
        $this->requirePostRequest();
        $data = \Craft::$app->request->getRawBody();
        $requestObject = Json::decodeIfJson($data);
        $portalId = $requestObject['portalId'];
        $storeId = $requestObject['storeId'];
        $importStartedAt = $requestObject['importStartedAt'];
        $settingsToImport = $requestObject['settingsToImport'];
        /*
        {
  "portalId": 1234567,
  "storeId": "store-to-import-id",
  "importStartedAt": 1552678940201,
  "settingsToImport": [
    {
      "settingsId": 1,
      "objectType": "PRODUCT"
    },
    {
      "settingsId": 2,
      "objectType": "CONTACT"
    },
    {
      "settingsId": 3,
      "objectType": "LINE_ITEM"
    },
    {
      "settingsId": 4,
      "objectType": "DEAL"
    }
  ]
}
         */

        // RESPONSE FORMAT
        /*
         {
  "importCounts": [
    {
      "settingsId": 1,
      "count": 24
    },
    {
      "settingsId": 2,
      "count": 792
    },
    {
      "settingsId": 3,
      "count": 0
    },
    {
      "settingsId": 4,
      "count": 901
    }
  ]
}
         */
    }
}