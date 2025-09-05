<?php

namespace backend\models\business;

use Yii;
use backend\models\nomenclators\MovementTypes;
use backend\models\business\CashRegister;
use backend\models\business\MovementCashRegisterDetail;

/**
 * This is the model class for table "movement_cash_register".
 *
 * @property int $id
 * @property int $cash_register_id
 * @property int $movement_type_id
 * @property string $movement_date
 * @property string $movement_time
 *
 * @property CashRegister $cashRegister
 * @property MovementTypes $movementType
 * @property MovementCashRegisterDetail[] $movementCashRegisterDetails
 */
class MovementCashRegister extends \yii\db\ActiveRecord
{
    
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'movement_cash_register';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['cash_register_id', 'movement_type_id', 'movement_date', 'movement_time'], 'required'],
            [['cash_register_id', 'movement_type_id'], 'default', 'value' => null],
            [['cash_register_id', 'movement_type_id'], 'integer'],
            [['movement_date', 'movement_time'], 'safe'],
            [['cash_register_id'], 'exist', 'skipOnError' => true, 'targetClass' => CashRegister::className(), 'targetAttribute' => ['cash_register_id' => 'id']],
            [['movement_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => MovementTypes::className(), 'targetAttribute' => ['movement_type_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'cash_register_id' => 'Cash Register ID',
            'movement_type_id' => 'Movement Type ID',
            'movement_date' => 'Movement Date',
            'movement_time' => 'Movement Time',
        ];
    }

    /**
     * Gets query for [[CashRegister]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCashRegister()
    {
        return $this->hasOne(CashRegister::className(), ['id' => 'cash_register_id']);
    }

    /**
     * Gets query for [[MovementType]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMovementType()
    {
        return $this->hasOne(MovementTypes::className(), ['id' => 'movement_type_id']);
    }

    /**
     * Gets query for [[MovementCashRegisterDetails]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMovementCashRegisterDetails()
    {
        return $this->hasMany(MovementCashRegisterDetail::className(), ['movement_cash_register_id' => 'id']);
    }
}
