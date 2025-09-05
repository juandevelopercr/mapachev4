<?php

namespace backend\models\business;

use Yii;
use backend\models\nomenclators\CoinDenominations;
/**
 * This is the model class for table "movement_cash_register_detail".
 *
 * @property int $id
 * @property int $movement_cash_register_id
 * @property float $value
 * @property int $count
 * @property string $comment
 * @property int|null $coin_denomination_id
 *
 * @property CoinDenominations $coinDenomination
 * @property MovementCashRegister $movementCashRegister
 */
class MovementCashRegisterDetail extends \yii\db\ActiveRecord
{
    public $movement_date;
    public $movement_time;
    public $monto_inicial;
    public $monto_adicionado;
    public $monto_retirado;
    public $monto_a_entregar;
    public $total_ventas;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'movement_cash_register_detail';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['movement_cash_register_id', 'value', 'count', 'comment'], 'required'],
            [['movement_cash_register_id', 'count', 'coin_denomination_id'], 'default', 'value' => null],
            [['movement_cash_register_id', 'count', 'coin_denomination_id', 'invoice_id'], 'integer'],
            [['value'], 'number'],
            [['comment'], 'string', 'max' => 255],
            [['coin_denomination_id'], 'exist', 'skipOnError' => true, 'targetClass' => CoinDenominations::className(), 'targetAttribute' => ['coin_denomination_id' => 'id']],
            [['movement_cash_register_id'], 'exist', 'skipOnError' => true, 'targetClass' => MovementCashRegister::className(), 'targetAttribute' => ['movement_cash_register_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'movement_cash_register_id' => 'Movement Cash Register',
            'value' => 'Cantidad',
            'count' => 'Count',
            'comment' => 'Comentario',
            'coin_denomination_id' => 'Coin Denomination',
            'movement_date' => 'Fecha',
            'movement_time' => 'Hora',
            'monto_inicial'=> 'Monto inicial',
            'monto_adicionado'=> 'Monto entrada de efectivo',
            'monto_retirado'=> 'Monto salida de efectivo',
            'monto_a_entregar'=> 'Monto que debe entregar',
            'total_ventas'=> 'Total ventas',
            'invoice_id'=> 'Invoice',          
        ];
    }

    /**
     * Gets query for [[CoinDenomination]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCoinDenomination()
    {
        return $this->hasOne(CoinDenominations::className(), ['id' => 'coin_denomination_id']);
    }

    /**
     * Gets query for [[MovementCashRegister]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMovementCashRegister()
    {
        return $this->hasOne(MovementCashRegister::className(), ['id' => 'movement_cash_register_id']);
    }

    public static function getMontoMovimiento($cash_register_id, $movement_type_id)
    {
        $data = MovementCashRegisterDetail::find()->join('INNER JOIN', 'movement_cash_register', 'movement_cash_register_detail.movement_cash_register_id = movement_cash_register.id')
                                                  ->join('INNER JOIN', 'cash_register', 'movement_cash_register.cash_register_id = cash_register.id')
                                                  ->where(['cash_register.id'=>$cash_register_id, 'movement_cash_register.movement_type_id'=>$movement_type_id])
                                                  ->sum('value * count'); 
        if (is_null($data))
            $data = 0;
        return  $data;                                                       
    }
}
