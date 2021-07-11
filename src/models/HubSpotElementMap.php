<?php

namespace venveo\hubspottoolbox\models;

use craft\base\Model;

class HubSpotElementMap extends Model
{
    public $id;
    public $elementId;
    public $elementSiteId;
    public $remoteObjectId;
    public $dateLastSynced;

    public $dateCreated;
    public $dateUpdated;
    public $uid;
}
