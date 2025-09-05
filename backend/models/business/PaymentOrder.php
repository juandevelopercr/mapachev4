<?php

namespace backend\models\business;

use backend\models\nomenclators\ConditionSale;
use backend\models\nomenclators\CreditDays;
use backend\models\nomenclators\Currency;
use backend\models\nomenclators\Project;
use backend\models\nomenclators\UtilsConstants;
use backend\models\settings\Setting;
use Yii;
use backend\models\BaseModel;
use yii\helpers\StringHelper;
use common\models\GlobalFunctions;
use yii\helpers\Html;

/**
 * This is the model class for table "payment_order".
 *
 * @property int $id
 * @property string|null $number
 * @property string|null $request_date
 * @property string|null $require_date
 * @property float|null $change_type
 * @property string|null $observations
 * @property int|null $project_id
 * @property int|null $supplier_id
 * @property int|null $status_payment_order_id
 * @property int|null $condition_sale_id
 * @property int|null $credit_days_id
 * @property int|null $currency_id
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property int|null $payout_status
 * @property bool|null $is_editable
 *
 * @property ItemPaymentOrder[] $itemPaymentOrders
 * @property ConditionSale $conditionSale
 * @property CreditDays $creditDays
 * @property Currency $currency
 * @property Project $project
 * @property Supplier $supplier

 */
class PaymentOrder extends BaseModel
{

    public $payment_methods = [];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'payment_order';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['number','supplier_id','condition_sale_id','change_type','request_date','require_date','status_payment_order_id','currency_id','payout_status','payment_methods'],'required'],
            [['request_date', 'require_date', 'created_at', 'updated_at','payment_methods'], 'safe'],
            [['change_type'], 'number'],
            [['observations'], 'string'],
            [['project_id', 'supplier_id', 'status_payment_order_id', 'condition_sale_id', 'credit_days_id', 'currency_id', 'payout_status'], 'default', 'value' => null],
            [['project_id', 'supplier_id', 'status_payment_order_id', 'condition_sale_id', 'credit_days_id', 'currency_id', 'payout_status'], 'integer'],
            [['is_editable'], 'boolean'],
            [['number'], 'string', 'max' => 255],
            [['condition_sale_id'], 'exist', 'skipOnError' => true, 'targetClass' => ConditionSale::className(), 'targetAttribute' => ['condition_sale_id' => 'id']],
            [['credit_days_id'], 'exist', 'skipOnError' => true, 'targetClass' => CreditDays::className(), 'targetAttribute' => ['credit_days_id' => 'id']],
            [['currency_id'], 'exist', 'skipOnError' => true, 'targetClass' => Currency::className(), 'targetAttribute' => ['currency_id' => 'id']],
            [['project_id'], 'exist', 'skipOnError' => true, 'targetClass' => Project::className(), 'targetAttribute' => ['project_id' => 'id']],
            [['supplier_id'], 'exist', 'skipOnError' => true, 'targetClass' => Supplier::className(), 'targetAttribute' => ['supplier_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'number' => Yii::t('backend', 'Número'),
            'request_date' => Yii::t('backend', 'Fecha de solicitud'),
            'require_date' => Yii::t('backend', 'Fecha requerida'),
            'change_type' => Yii::t('backend', 'Tipo de cambio'),
            'observations' => Yii::t('backend', 'Observaciones'),
            'project_id' => Yii::t('backend', 'Proyecto'),
            'supplier_id' => Yii::t('backend', 'Proveedor'),
            'status_payment_order_id' => Yii::t('backend', 'Estado'),
            'condition_sale_id' => Yii::t('backend', 'Condición de venta'),
            'credit_days_id' => Yii::t('backend', 'Días de crédito'),
            'currency_id' => Yii::t('backend', 'Moneda'),
            'created_at' => Yii::t('backend', 'Fecha de creación'),
            'updated_at' => Yii::t('backend', 'Fecha de actualización'),
            'payout_status' => Yii::t('backend', 'Estado de pago'),
            'is_editable' => Yii::t('backend', '¿Editable?'),
            'payment_methods' => Yii::t('backend', 'Medios de pagos (hasta 4)'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItemPaymentOrders()
    {
        return $this->hasMany(ItemPaymentOrder::className(), ['payment_order_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getConditionSale()
    {
        return $this->hasOne(ConditionSale::className(), ['id' => 'condition_sale_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreditDays()
    {
        return $this->hasOne(CreditDays::className(), ['id' => 'credit_days_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCurrency()
    {
        return $this->hasOne(Currency::className(), ['id' => 'currency_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProject()
    {
        return $this->hasOne(Project::className(), ['id' => 'project_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSupplier()
    {
        return $this->hasOne(Supplier::className(), ['id' => 'supplier_id']);
    }

    /** :::::::::::: START > Abstract Methods and Overrides ::::::::::::*/

    /**
    * @return string The base name for current model, it must be implemented on each child
    */
    public function getBaseName()
    {
        return StringHelper::basename(get_class($this));
    }

    /**
    * @return string base route to model links, default to '/'
    */
    public function getBaseLink()
    {
        return "/payment-order";
    }

    /**
    * Returns a link that represents current object model
    * @return string
    *
    */
    public function getIDLinkForThisModel()
    {
        $id = $this->getRepresentativeAttrID();
        if (isset($this->$id)) {
            $name = $this->getRepresentativeAttrName();
            return Html::a($this->$name, [$this->getBaseLink() . "/view", 'id' => $this->getId()]);
        } else {
            return GlobalFunctions::getNoValueSpan();
        }
    }

    /** :::::::::::: END > Abstract Methods and Overrides ::::::::::::*/

    /**
     * @return string
     */
    public function generateOrderNumber()
    {
        $year = date('Y');
        $connection = \Yii::$app->db;
        $sql = "SELECT MAX(SUBSTRING(number, 1, 6)) AS consecutive FROM payment_order WHERE SUBSTRING(number, 10, 13)='".$year."'";
        $data = $connection->createCommand($sql);
        $consecutive = $data->queryOne();
        $code = (isset($consecutive))? (int)$consecutive['consecutive'] + 1 : 1;

        return GlobalFunctions::zeroFill($code,6).'-'.date('mY');
    }

    public static function getResumePaymentOrder($payment_order_id)
    {
        $resume = ItemPaymentOrder::find()
            ->select([
                'SUM(subtotal) AS subtotal',
                'SUM(tax_amount) AS tax_amount',
                'SUM(discount_amount) AS discount_amount',
                'SUM(exonerate_amount) AS exonerate_amount',
                'SUM(price_total) AS price_total',
                ])
            ->where(['payment_order_id' => $payment_order_id])
            ->one();

        return $resume;
    }

    /**
     * @param $email
     */
    public function sendEmail($file_pdf)
    {
        $mails_to_send = $this->supplier->getContactsToSendMail();
        if(count($mails_to_send) > 0)
        {
            $subject = Yii::t('backend','Orden de compra # '.$this->number);

            //Generar fichero de orden de compra para adjuntar al correo
            $mailer = Yii::$app->mail->compose(['html' => 'payment-order-html'], ['order_number' => $this->number])
                ->setTo($mails_to_send)
                ->setFrom([Setting::getEmail() => Setting::getName()])
                ->setSubject($subject)
                ->attach($file_pdf, ['fileName'=>'Orden_compra_'.$this->number]);

            try
            {
                if($mailer->send())
                {
                    return UtilsConstants::SEND_MAIL_RESPONSE_TYPE_SUCCESS;
                }
                else
                {
                    return UtilsConstants::SEND_MAIL_RESPONSE_TYPE_ERROR;
                }
            }
            catch (\Swift_TransportException $e)
            {
                return UtilsConstants::SEND_MAIL_RESPONSE_TYPE_EXCEPTION;
            }
        }
        else
        {
            return UtilsConstants::SEND_MAIL_RESPONSE_TYPE_CUSTOM;
        }
    }

    public function getTotalAmount()
    {
        $resume = self::getResumePaymentOrder($this->id);
        $change_type = (isset($this->change_type) && $this->change_type > 0)? $this->change_type : 1;

        $total = ($this->currency->symbol == 'CRC')? $resume->price_total : ($resume->price_total*$change_type);

        return $total?? 0;
    }
}
