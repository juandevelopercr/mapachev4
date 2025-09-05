<?php

namespace backend\models\business;

use Yii;
use backend\models\nomenclators\Boxes;
use backend\models\business\MovementCashRegister;
use backend\models\nomenclators\MovementTypes;
use common\models\User;
use yii\db\Exception;
use yii\web\Response;
/**
 * This is the model class for table "cash_register".
 *
 * @property int $id
 * @property int $box_id
 * @property int $seller_id
 * @property string $opening_date
 * @property string $opening_time
 * @property string|null $closing_date
 * @property string|null $closing_time
 * @property float $initial_amount
 * @property float|null $end_amount
 * @property float|null $total_sales
 * @property bool|null $status
 *
 * @property Boxes $box
 * @property User $seller
 * @property MovementCashRegister[] $movementCashRegisters
 */
class CashRegister extends \yii\db\ActiveRecord
{
    public $branch_office_id;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'cash_register';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['box_id', 'seller_id', 'opening_date', 'opening_time', 'initial_amount'], 'required'],
            [['box_id', 'seller_id'], 'default', 'value' => null],
            [['box_id', 'seller_id'], 'integer'],
            [['opening_date', 'opening_time', 'closing_date', 'closing_time'], 'safe'],
            [['initial_amount', 'end_amount', 'total_sales'], 'number'],
            [['status'], 'boolean'],
            [['box_id'], 'exist', 'skipOnError' => true, 'targetClass' => Boxes::className(), 'targetAttribute' => ['box_id' => 'id']],
            [['seller_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['seller_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'box_id' => 'Caja',
            'seller_id' => 'Vendedor',
            'opening_date' => 'Fecha Apertura',
            'opening_time' => 'Hora de apertura',
            'closing_date' => 'Fecha de cierre',
            'closing_time' => 'Hora de cierre',
            'initial_amount' => 'Monto inicial',
            'end_amount' => 'Monto final',
            'total_sales' => 'Total de ventas',
            'status' => 'Estado',
            'branch_office_id'=> 'Sucursal',
        ];
    }

    /**
     * Gets query for [[Box]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBox()
    {
        return $this->hasOne(Boxes::className(), ['id' => 'box_id']);
    }

    /**
     * Gets query for [[Seller]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSeller()
    {
        return $this->hasOne(User::className(), ['id' => 'seller_id']);
    }

    /**
     * Gets query for [[MovementCashRegisters]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMovementCashRegisters()
    {
        return $this->hasMany(MovementCashRegister::className(), ['cash_register_id' => 'id']);
    }

    public static function getDatos($box_id){
        return CashRegister::find()->select('cash_register.*, boxes.numero, boxes.name')
                                    ->join("INNER JOIN", "boxes", "cash_register.box_id = boxes.id")
                                    ->where(["box_id"=>$box_id])
                                    ->all();
    }

    public static function cajaAbierta($box_id)
    {
        $data = CashRegister::find()->where(['box_id'=>$box_id, 'status'=>1])->one();
        if (is_null($data))
            return false;
        else
            return true;  
    }

    public static function AbrirCaja($box_id, $efectivo)
    {
        $cashRegister = CashRegister::find()->where(['box_id'=>$box_id, 'status'=>1])->one();  
        $result = true;
        $transaction = \Yii::$app->db->beginTransaction();
        try
        {        
            if (is_null($cashRegister))      
            {
                $cashRegister = new CashRegister();
                //$coins = CoinDenominations::find()->asArray()->all();
                $box = Boxes::find()->where(['id' => $box_id])->one();
                $cashRegister->opening_date = date('Y-m-d');
                $cashRegister->opening_time = date('H:i:s');
                $cashRegister->initial_amount = 0;
                $cashRegister->seller_id = Yii::$app->user->id;
                $cashRegister->branch_office_id = $box->branch_office_id;
                $cashRegister->box_id = $box_id;
                $cashRegister->status = 1;

                if ($cashRegister->save())
                {
                    $movementCashRegister = new MovementCashRegister;
                    $movementCashRegister->cash_register_id = $cashRegister->id;
                    $movementCashRegister->movement_type_id = MovementTypes::APERTURA_CAJA;
                    $movementCashRegister->movement_date = date('Y-m-d');
                    $movementCashRegister->movement_time = date('H:i:s');
                    $initial_amount = 0;
                    if ($movementCashRegister->save()) {
                        
                        foreach ($efectivo as $row) {
                            if (isset($row['count']) && $row['count'] > 0) {
                                $movementCashRegisterDetail = new MovementCashRegisterDetail;
                                $movementCashRegisterDetail->movement_cash_register_id = $movementCashRegister->id;
                                $movementCashRegisterDetail->value = $row['value'];
                                $movementCashRegisterDetail->count = $row['count'];
                                $movementCashRegisterDetail->comment = $row['description'];
                                $movementCashRegisterDetail->coin_denomination_id = $row['denominations_id'];
                                if (!$movementCashRegisterDetail->save())
                                //die(var_dump($movementCashRegisterDetail->getErrors()));
                                    $result = false;   
                                $initial_amount += $row['value'] * $row['count'];
                            }
                        }
                        $cashRegister->initial_amount = $initial_amount;
                        $cashRegister->save();                                
                    }
                    else
                        $result = false;
                }
                else
                    $result = false;
            }
        }
        catch (Exception $e)
        {
            $result = false;
            $transaction->rollBack();
        }
        if ($result == true){
            $transaction->commit(); 
        }
        else{
            $cashRegister = NULL;
            $transaction->rollBack();
        }

        return $cashRegister;    
    }

    public static function RegisterMovimiento($box_id, $tipo, $invoice_id, $cantidad, $valor, $coment)
    { 
        $result = false;
        $transaction = \Yii::$app->db->beginTransaction();
        try
        {
            $cashRegister = CashRegister::find()->where(['box_id'=>$box_id, 'status'=>1])->one();        
            if (is_null($cashRegister)){ // La caja está cerrada, entonces abrirla

                $efectivo = [
                    'value'=> 0,
                    'count'=> 1,
                    'description'=> 'Apertura automática del sistema',
                    'denominations_id'=> NULL,
                ];
                $cashRegister = CashRegister::AbrirCaja($box_id, $efectivo);
            }

            if (!is_null($cashRegister))
            {
                $movimiento = new MovementCashRegister;
                $movimiento->cash_register_id = $cashRegister->id;
                $movimiento->movement_type_id = $tipo;
                $movimiento->movement_date = date('Y-m-d');
                $movimiento->movement_time = date('H:i:s');
                if ($movimiento->save())
                {
                    $movementCashRegisterDetail = new MovementCashRegisterDetail;
                    $movementCashRegisterDetail->movement_cash_register_id = $movimiento->id;
                    $movementCashRegisterDetail->value = $valor;
                    $movementCashRegisterDetail->invoice_id = $invoice_id;
                    $movementCashRegisterDetail->count = $cantidad;
                    $movementCashRegisterDetail->comment = $coment;
                    if ($movementCashRegisterDetail->save())
                    {
                        $result = true;
                        $transaction->commit();
                    }
                }
            }                
        }
        catch (Exception $e)
        {
            $result = false;
            $transaction->rollBack();
        }
        return $result;
    }

    public static function ArqueoCaja($box_id)
    {
        $cashRegister = CashRegister::find()->where(['box_id'=>$box_id, 'status'=>1])->one();  
        
    }
}
