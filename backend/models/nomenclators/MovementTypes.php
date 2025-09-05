<?php

namespace backend\models\nomenclators;

use Yii;
use  backend\models\business\PointsSaleMovements;

/**
 * This is the model class for table "movement_types".
 *
 * @property int $id
 * @property string $nombre
 *
 */
class MovementTypes extends \yii\db\ActiveRecord
{
    const APERTURA_CAJA = 1;    
    const ENTRADA_EFECTIVO = 2;
    const SALIDA_EFECTIVO = 3;
    const CIERRE_CAJA = 4;
    const VENTA = 5;
    const DEVOLUCION = 6;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'movement_types';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['nombre'], 'required'],
            [['nombre'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'nombre' => 'Nombre',
        ];
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
                $array_map[$model['id']] = $model['nombre'];
            }
        }

        return $array_map;
    }    
}
