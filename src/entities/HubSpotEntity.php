<?php
namespace venveo\hubspottoolbox\entities;

use craft\base\Model;
use craft\helpers\DateTimeHelper;

abstract class HubSpotEntity extends Model {

    /**
     * @inheritdoc
     */
    public function init()
    {
        // Normalize the Microtime DateTime attributes
        foreach ($this->microtimeAttributes() as $attribute) {
            if ($this->$attribute !== null) {
                $this->$attribute = DateTimeHelper::toDateTime(round($this->$attribute / 1000));
            }
        }
        parent::init();
    }

    /**
     * @param bool $filterNull
     * @return array
     */
    public function prepareForApi($filterNull = true): array
    {
        $data = [];
        foreach($this->getAttributes() as $attribute => $value) {
            if ($value instanceof self) {
                $data[$attribute] = $this->$attribute->prepareForApi();
            } elseif (is_array($value)) {
                $data[$attribute] = array_map(function($val) {
                    if ($val instanceof self) {
                        return $val->prepareForApi();
                    }
                    return $val;
                }, $value);
            } else {
                $data[$attribute] = $value;
            }
        }
        if ($filterNull) {
            $data = array_filter($data);
        }
        return $data;
    }

    public function microtimeAttributes() {
        return [];
    }



    public function fields()
    {
        $fields = parent::fields();

        // Have all DateTime attributes converted to ISO-8601 strings
        foreach ($this->microtimeAttributes() as $attribute) {
            $fields[$attribute] = function($model, $attribute) {
                if (!empty($model->$attribute)) {
                    return DateTimeHelper::toIso8601($model->$attribute);
                }

                return $model->$attribute;
            };
        }
        return $fields;
    }
}