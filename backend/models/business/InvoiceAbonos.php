<?php

namespace backend\models\business;

use Yii;
use backend\models\nomenclators\Banks;
use backend\models\nomenclators\PaymentMethod;
use common\models\User;
/**
 * This is the model class for table "invoice_abonos".
 *
 * @property int $id
 * @property int $invoice_id
 * @property string|null $emission_date
 * @property int|null $payment_method_id
 * @property string|null $reference
 * @property int $bank_id
 * @property float|null $amount
 * @property string|null $comment
 *
 * @property Banks $bank
 * @property Invoice $invoice
 * @property PaymentMethod $paymentMethod
 */
class InvoiceAbonos extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'invoice_abonos';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['invoice_id', 'payment_method_id', 'amount', 'reference', 'collector_id'], 'required'],
            [['invoice_id', 'payment_method_id', 'bank_id'], 'default', 'value' => null],
            [['invoice_id', 'payment_method_id', 'bank_id', 'collector_id'], 'integer'],
            [['emission_date'], 'safe'],
            [['amount'], 'number'],
            [['reference'], 'string', 'max' => 200],
            [['comment'], 'string', 'max' => 255],
            [['bank_id'], 'exist', 'skipOnError' => true, 'targetClass' => Banks::className(), 'targetAttribute' => ['bank_id' => 'id']],
            [['invoice_id'], 'exist', 'skipOnError' => true, 'targetClass' => Invoice::className(), 'targetAttribute' => ['invoice_id' => 'id']],
            [['collector_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['collector_id' => 'id']],
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
            'invoice_id' => 'Factura',
            'emission_date' => 'Fecha',
            'payment_method_id' => 'Método de Pago',
            'reference' => 'Referencia',
            'bank_id' => 'Banco',
            'amount' => 'Monto',
            'comment' => 'Comentario',
            'collector_id' => Yii::t('backend', 'Agente Cobrador'),
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
     * Gets query for [[Invoice]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getInvoice()
    {
        return $this->hasOne(Invoice::className(), ['id' => 'invoice_id']);
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

    public static function getAbonosByInvoiceID($invoice_id)
    {
        $data = InvoiceAbonos::find()->where(['invoice_id'=>$invoice_id])->sum('amount');
        if (is_null($data))
            $data = 0;
        return $data;    
    }

    public static function addAbono($invoice, $monto, $comentario){
        $collector = CollectorHasInvoice::find()->where(['invoice_id'=>$invoice->id])->one();

        $abono = new InvoiceAbonos;
        $abono->invoice_id = $invoice->id;
        $abono->emission_date = date('Y-m-d H:i:s');
        $abono->payment_method_id = 1;
        $abono->reference = $invoice->consecutive;
        $abono->amount = $monto;
        $abono->comment = $comentario;
        $abono->collector_id = $collector->collector_id;
        $abono->save();
    }
}
