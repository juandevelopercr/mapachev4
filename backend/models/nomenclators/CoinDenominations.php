<?php

namespace backend\models\nomenclators;

use Yii;

/**
 * This is the model class for table "coin_denominations".
 *
 * @property int $id
 * @property string $description
 * @property float $value
 */
class CoinDenominations extends \yii\db\ActiveRecord
{
    const REGISTRO_ENTRADA_SALIDA = 17;  // Este es el registro de la tabla que no se debe borrar y es para las entradas y salidas de efectivo
    public $movement_cash_register_id;
    public $count; 
    public $comment;
    public $detail_value;
    public $coin_denomination_id;
    public $movement_cash_register_detail_id;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'coin_denominations';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['description', 'value'], 'required'],
            [['value'], 'number'],
            [['description'], 'string', 'max' => 200],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'description' => 'Denominación de moneda',
            'value' => 'Valor',
        ];
    }
}
