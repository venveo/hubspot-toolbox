<?php
/**
 * HubSpot Toolbox plugin for Craft CMS 3.x
 *
 * Turnkey HubSpot integration for CraftCMS
 *
 * @link      https://venveo.com
 * @copyright Copyright (c) 2018 Venveo
 */

namespace venveo\hubspottoolbox\variables;

use venveo\hubspottoolbox\HubSpotToolbox;

use Craft;
use venveo\hubspottoolbox\services\hubspot\ContactsService;

/**
 * HubSpot Toolbox Variable
 *
 * Craft allows plugins to provide their own template variables, accessible from
 * the {{ craft }} global variable (e.g. {{ craft.hubspotToolbox }}).
 *
 * https://craftcms.com/docs/plugins/variables
 *
 * @author    Venveo
 * @package   HubspotToolbox
 * @since     1.0.0
 */
class HubspotVariable
{
    // Public Methods
    // =========================================================================

    /**
     * Whatever you want to output to a Twig template can go into a Variable method.
     * You can have as many variable functions as you want.  From any Twig template,
     * call it like this:
     *
     *     {{ craft.hubspot.isContact }}
     *
     * @return array
     */
    public function isContact()
    {
        return HubSpotToolbox::$plugin->hubspot->isContact();
    }


    public function forms()
    {
        return HubSpotToolbox::$plugin->getHubSpotService()->getAllForms();
    }

    public function portalId() {
        $settings = HubSpotToolbox::$plugin->getSettings();
        return $settings['hubspotPortalId'];
    }
}
