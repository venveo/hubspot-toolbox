<?php

namespace venveo\hubspottoolbox\services;

use Craft;
use craft\base\Component;
use craft\base\MemoizableArray;
use craft\db\Query;
use craft\errors\MissingComponentException;
use craft\helpers\Component as ComponentHelper;
use craft\helpers\StringHelper;
use venveo\hubspottoolbox\features\EcommerceFeature;
use venveo\hubspottoolbox\features\FormsFeature;
use venveo\hubspottoolbox\features\HubSpotFeature;
use venveo\hubspottoolbox\features\HubSpotFeatureInterface;
use venveo\hubspottoolbox\records\HubSpotFeature as HubSpotFeatureRecord;
use yii\base\Exception;

/**
 *
 * @property-read \venveo\hubspottoolbox\features\HubSpotFeatureInterface[] $features
 */
class FeaturesService extends Component
{

    /**
     * @var MemoizableArray|null all features
     */
    private $_features;

    public function getFeatureByType($type)
    {
        return $this->_features()->firstWhere('type', $type);
    }

    /**
     * @return <HubSpotFeature>[]
     */
    public function getEnabledFeatures()
    {
        return $this->_features()->where('enabled', true);
    }

    /**
     * @param $handle
     * @return HubSpotFeature
     */
    public function getFeatureByHandle($handle)
    {
        return $this->_features()->firstWhere('handle', $handle);
    }

    private function _features(): MemoizableArray
    {
        $availableFeatures = [
            EcommerceFeature::class,
            FormsFeature::class
        ];
        if ($this->_features === null) {
            $features = [];
            $featureConfigs = $this->_createFeatureQuery()->indexBy('type')->all();
            foreach ($availableFeatures as $availableFeature) {
                if (array_key_exists($availableFeature, $featureConfigs)) {
                    $features[] = $this->createFeature($featureConfigs[$availableFeature]);
                } else {
                    $features[] = $this->createFeature([
                        'type' => $availableFeature,
                    ]);
                }
            }
            $this->_features = new MemoizableArray($features);
        }

        return $this->_features;
    }

    /**
     * Returns all features
     *
     * @return HubSpotFeatureInterface[] All features
     */
    public function getFeatures(): array
    {
        return $this->_features()->all();
    }

    /**
     * Saves a feature.
     *
     * @param HubSpotFeature $feature The gateway to be saved.
     * @param bool $runValidation Whether the feature should be validated
     * @return bool Whether the feature was saved successfully or not.
     * @throws Exception
     */
    public function saveFeature(HubSpotFeature $feature, bool $runValidation = true): bool
    {
        $feature->beforeSave($feature->getIsNew());
        if ($runValidation && !$feature->validate()) {
            Craft::info('Feature not saved due to validation error.', __METHOD__);
            return false;
        }

        $existingFeature = $this->getFeatureByType(get_class($feature));

        if ($existingFeature && ($feature->id || $feature->id != $existingFeature->id)) {
            $feature->addError('type', Craft::t('hubspot-toolbox', 'That type is already in use.'));
            return false;
        }

        $configData = [
            'type' => get_class($feature),
            'enabled' => $feature->enabled,
            'settings' => $feature->getSettings()
        ];

        $this->_saveFeatureRecord($configData);
        $feature->afterSave($feature->getIsNew());
        $this->_features = null; // reset cache

        return true;
    }

    public function _saveFeatureRecord($data)
    {
        $transaction = Craft::$app->getDb()->beginTransaction();
        try {
            $featureRecord = $this->_getFeatureRecord($data['type']);
            $featureRecord->settings = $data['settings'] ?? null;
            $featureRecord->enabled = $data['enabled'] ?? false;

            // Save the volume
            $featureRecord->save(false);

            $transaction->commit();
        } catch (Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }


    /**
     * Creates a feature with a given config
     *
     * @param mixed $config The features’s class name, or its config, with a `type` value and optionally a `settings` value
     * @return HubSpotFeatureInterface The feature
     * @throws \yii\base\InvalidConfigException
     */
    public function createFeature($config): HubSpotFeatureInterface
    {
        if (is_string($config)) {
            $config = ['type' => $config];
        }

        try {
            /** @var HubSpotFeature $feature */
            $feature = ComponentHelper::createComponent($config, HubSpotFeatureInterface::class);
        } catch (MissingComponentException $e) {
            $config['errorMessage'] = $e->getMessage();
            $config['expectedType'] = $config['type'];
            unset($config['type']);
            $feature = null;
        }

        return $feature;
    }

    /**
     * Returns a Query object prepped for retrieving features.
     *
     * @return Query The query object.
     */
    private function _createFeatureQuery(): Query
    {
        return (new Query())
            ->select([
                'id',
                'type',
                'enabled',
                'settings',
            ])
            ->from([HubSpotFeatureRecord::tableName()]);
    }

    /**
     * Gets a feature's record by type.
     *
     * @param string $type
     * @return HubSpotFeatureRecord
     */
    private function _getFeatureRecord(string $type): HubSpotFeatureRecord
    {
        if ($feature = HubSpotFeatureRecord::findOne(['type' => $type])) {
            return $feature;
        }
        return new HubSpotFeatureRecord([
            'type' => $type,
            'enabled' => false,
            'uid' => StringHelper::UUID()
        ]);
    }

}