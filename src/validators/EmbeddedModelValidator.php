<?php
/**
 * HEY - I STOLE THIS FROM ANDREW WELCH. GO BUY HIS THINGS.
 * @source https://github.com/nystudio107/craft-seomatic/blob/v3/src/validators/EmbeddedModelValidator.php
 */
namespace venveo\hubspottoolbox\validators;

use Craft;
use craft\base\Model;
use yii\validators\Validator;

class EmbeddedModelValidator extends Validator {
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function validateAttribute($model, $attribute)
    {
        /** @var Model $model */
        $value = $model->$attribute;

        if (!empty($value) && \is_object($value) && $value instanceof Model) {
            /** @var Model $value */
            if (!$value->validate()) {
                $errors = $value->getErrors();
                foreach ($errors as $attributeError => $valueErrors) {
                    /** @var array $valueErrors */
                    foreach ($valueErrors as $valueError) {
                        $model->addError(
                            $attribute,
                            Craft::t('hubspot-toolbox', 'Object failed to validate')
                            .'-'.$attributeError.' - '.$valueError
                        );
                    }
                }
            }
        } else {
            $model->addError($attribute, Craft::t('hubspot-toolbox', 'Is not a Model object.'));
        }
    }
}