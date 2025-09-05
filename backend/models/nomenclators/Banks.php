<?php

namespace backend\models\nomenclators;

use Yii;
use backend\models\business\InvoiceAbonos;

/**
 * This is the model class for table "banks".
 *
 * @property int $id
 * @property string $name
 *
 * @property InvoiceAbonos[] $invoiceAbonos
 */
class Banks extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'banks';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
        ];
    }

    /**
     * Gets query for [[InvoiceAbonos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getInvoiceAbonos()
    {
        return $this->hasMany(InvoiceAbonos::className(), ['bank_id' => 'id']);
    }

   /**
     * Returns a mapped array for using on Select widget
     *
     * @param boolean $check_status
     * @return array
     */
    public static function getSelectMap()
    {
        $query = self::find();

        $models = $query->asArray()->all();
        $array_map = [];

        if(count($models)>0)
        {
            foreach ($models AS $index => $model)
            {
                $array_map[$model['id']] = $model['name'];
            }
        }

        return $array_map;
    }     
}
