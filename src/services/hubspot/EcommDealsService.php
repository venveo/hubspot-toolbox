<?php
/*
 *  @link      https://www.venveo.com
 *  @copyright Copyright (c) 2020 Venveo
 */

namespace venveo\hubspottoolbox\services\hubspot;

use craft\base\Component;
use venveo\hubspottoolbox\traits\HubSpotTokenAuthorization;

/**
 */
class EcommDealsService extends Component
{
    use HubSpotTokenAuthorization;

    public function getDealStages()
    {
        /**
         Default deal stages:
         - processed
         - checkout_completed
         - cancelled
         - shipped
         - checkout_abandoned
         - checkout_pending
         */
        return $this->getHubSpot()->crmPipelines()->all('deal');
    }
}