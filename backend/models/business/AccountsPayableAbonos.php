<?php

namespace backend\models\business;

use Yii;
use backend\models\nomenclators\Banks;
use backend\models\nomenclators\PaymentMethod;
use common\models\User;
/**
 * This is the model class for table "accounts_payable_abonos".
 *
 * @property int $id
 * @property int $accounts_payable_abonos_id
 * @property string|null $emission_date
 * @property int|null $payment_method_id
 * @property string|null $reference
 * @property int $bank_id
 * @property float|null $amount
 * @property string|null $comment
 *
 * @property Banks $bank
 * @property AccountsPayable $AccountsPayable
 * @property PaymentMethod $paymentMethod
 */
class AccountsPayableAbonos extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'accounts_payable_abonos';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['accounts_payable_abonos_id', 'payment_method_id', 'amount', 'reference'], 'required'],
            [['accounts_payable_abonos_id', 'payment_method_id', 'bank_id'], 'default', 'value' => null],
            [['accounts_payable_abonos_id', 'payment_method_id', 'bank_id'], 'integer'],
            [['emission_date'], 'safe'],
            [['amount'], 'number'],
            [['reference'], 'string', 'max' => 200],
            [['comment'], 'string', 'max' => 255],
            [['bank_id'], 'exist', 'skipOnError' => true, 'targetClass' => Banks::className(), 'targetAttribute' => ['bank_id' => 'id']],
            [['accounts_payable_abonos_id'], 'exist', 'skipOnError' => true, 'targetClass' => AccountsPayable::className(), 'targetAttribute' => ['accounts_payable_abonos_id' => 'id']],
            [['payment_method_id'], 'exist', 'skipOnError' => true, 'targetClass' => PaymentMethod::className(), 'targetAttribute' => ['payment_method_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'accounts_payable_abonos_id' => 'Factura',
            'emission_date' => 'Fecha',
            'payment_method_id' => 'Método de Pago',
            'reference' => 'Referencia',
            'bank_id' => 'Banco',
            'amount' => 'Monto',
            'comment' => 'Comentario',
        ];
    }

    /**
     * Gets query for [[Bank]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBank()
    {
        return $this->hasOne(Banks::className(), ['id' => 'bank_id']);
    }

    /**
     * Gets query for [[AccountsPayable]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAccountsPayable()
    {
        return $this->hasOne(AccountsPayable::className(), ['id' => 'accounts_payable_abonos_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCollector()
    {
        return $this->hasOne(User::className(), ['id' => 'collector_id']);
    }    

    /**
     * Gets query for [[PaymentMethod]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPaymentMethod()
    {
        return $this->hasOne(PaymentMethod::className(), ['id' => 'payment_method_id']);
    }

    public static function getAbonosByInvoiceID($accounts_payable_abonos_id)
    {
        $data = AccountsPayableAbonos::find()->where(['accounts_payable_abonos_id'=>$accounts_payable_abonos_id])->sum('amount');
        if (is_null($data))
            $data = 0;
        return $data;    
    }
}
