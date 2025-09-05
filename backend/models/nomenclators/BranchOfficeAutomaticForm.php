<?php

namespace backend\models\nomenclators;

use Yii;
use yii\base\Model;

/**
 * BranchOfficeAutomaticForm
 */
class BranchOfficeAutomaticForm extends Model
{
    public $sector_name;
    public $sector_code_start;
    public $sector_code_end;

    public $location_name;
    public $location_code_start;
    public $location_code_end;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['sector_name', 'sector_code_start', 'sector_code_end', 'sector_name', 'location_name', 'location_code_start', 'location_code_end'], 'required'],
            [['sector_code_start', 'sector_code_end'], 'string'],
            [['location_code_start', 'location_code_end'], 'integer'],
            ['location_code_end', function ($attribute, $params) {
                if ($this->$attribute <= $this->location_code_start) {
                    $this->addError($attribute, Yii::t('backend', 'Código Final debe ser mayor que Código Inicial'));
                }
            }],
            ['sector_code_end', function ($attribute, $params) {
                if ($this->$attribute < $this->sector_code_start) {
                    $this->addError($attribute, Yii::t('backend', 'Código Final debe ser mayor o igual que Código Inicial'));
                }
            }]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'sector_name' => Yii::t('backend', 'Nombre genérico'),
            'sector_code_start' => Yii::t('backend', 'Cod. Inicial'),
            'sector_code_end' => Yii::t('backend', 'Cod. Final'),
            'location_name' => Yii::t('backend', 'Nombre genérico'),
            'location_code_start' => Yii::t('backend', 'Cod. Inicial'),
            'location_code_end' => Yii::t('backend', 'Cod. Final'),
        ];
    }

}
