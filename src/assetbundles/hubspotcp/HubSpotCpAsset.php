<?php

namespace venveo\hubspottoolbox\assetbundles\hubspotcp;

use Craft;
use craft\helpers\App;
use craft\helpers\Json;
use craft\helpers\UrlHelper;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;
use craft\web\assets\vue\VueAsset;
use venveo\hubspottoolbox\HubSpotToolbox;
use yii\caching\TagDependency;
use yii\web\NotFoundHttpException;

class HubSpotCpAsset extends AssetBundle
{
    /**
     * @var string
     */
    protected $appJs = 'main.js';

    /**
     * @var string
     */
    protected $appCss = 'app.css';

    /**
     * @inheritdoc
     */
    public function init()
    {
//        $this->sourcePath = __DIR__ . '/';

        $this->depends = [
            CpAsset::class,
        ];

        if ($this->getDevServer()) {
            // Development
            $devServer = static::getDevServer();
            $this->js[] = $devServer . '/' . $this->appJs;
        } else {
            // Production
            $this->js[] = 'js/chunk-vendors.js';
            $this->js[] = 'js/' . $this->appJs;
            $this->css[] = 'css/chunk-vendors.css';
            $this->css[] = 'css/' . $this->appCss;
        }

        parent::init();
    }


    /**
     * @return string
     */
    private static function getDevServer(): string
    {
        return 'https://craft3-plugindev.test:8080/dist';
    }
}
