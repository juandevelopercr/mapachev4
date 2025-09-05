<?php

namespace backend\models\business;

use Yii;
use yii\base\Model;

/**
 * AssignLocation form
 */
class AssignLocation extends Model
{
    public $item_imported_ids = [];
    public $sector_location_id;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            // username and password are both required
            [['item_imported_ids','sector_location_id'], 'required'],
            [['item_imported_ids','sector_location_id'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'item_imported_ids' => Yii::t('backend','Items pendientes'),
            'sector_location_id' => Yii::t('backend','Ubicación destino'),
        ];
    }


}
