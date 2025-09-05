<?php

namespace backend\models\business;

use backend\models\nomenclators\BranchOffice;
use backend\models\nomenclators\ConditionSale;
use backend\models\nomenclators\CreditDays;
use backend\models\nomenclators\Currency;
use backend\models\nomenclators\Seller;
use backend\models\nomenclators\UtilsConstants;
use backend\models\nomenclators\Boxes;
use backend\models\settings\Setting;
use common\models\User;
use Yii;
use backend\models\BaseModel;
use yii\helpers\StringHelper;
use common\models\GlobalFunctions;
use yii\helpers\Html;

/**
 * This is the model class for table "proforma".
 *
 * @property int $id
 * @property string|null $consecutive
 * @property int|null $branch_office_id
 * @property int|null $customer_id
 * @property int|null $credit_days_id
 * @property int|null $condition_sale_id
 * @property string|null $request_date
 * @property float|null $change_type
 * @property int|null $currency_id
 * @property int|null $status
 * @property string|null $delivery_time
 * @property int|null $delivery_time_type
 * @property float|null $discount_percent
 * @property int|null $seller_id
 * @property string|null $observations
 * @property bool|null $is_editable
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property ItemProforma[] $itemProformas
 * @property BranchOffice $branchOffice
 * @property ConditionSale $conditionSale
 * @property CreditDays $creditDays
 * @property Currency $currency
 * @property Customer $customer
 * @property User $seller

 */
class Proforma extends BaseModel
{
    public $payment_methods = [];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'proforma';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['request_date', 'consecutive', 'branch_office_id', 'box_id', 'customer_id', 'condition_sale_id', 'currency_id', 'status', 'delivery_time_type', 'seller_id', 'change_type', 'delivery_time', 'invoice_type'], 'required'],
            ['payment_methods','required','on' => ['create','update']],
            [['branch_office_id', 'box_id', 'customer_id', 'credit_days_id', 'condition_sale_id', 'currency_id', 'status', 'delivery_time_type', 'seller_id', 'invoice_type', 'facturada'], 'integer'],
            [['request_date', 'created_at', 'updated_at','payment_methods'], 'safe'],
            [['change_type', 'discount_percent'], 'number'],
            [['observations'], 'string'],
            [['is_editable'], 'boolean'],
            [['consecutive', 'delivery_time'], 'string', 'max' => 255],
            [['branch_office_id'], 'exist', 'skipOnError' => true, 'targetClass' => BranchOffice::className(), 'targetAttribute' => ['branch_office_id' => 'id']],
            [['box_id'], 'exist', 'skipOnError' => true, 'targetClass' => Boxes::className(), 'targetAttribute' => ['box_id' => 'id']],
            [['condition_sale_id'], 'exist', 'skipOnError' => true, 'targetClass' => ConditionSale::className(), 'targetAttribute' => ['condition_sale_id' => 'id']],
            [['credit_days_id'], 'exist', 'skipOnError' => true, 'targetClass' => CreditDays::className(), 'targetAttribute' => ['credit_days_id' => 'id']],
            [['currency_id'], 'exist', 'skipOnError' => true, 'targetClass' => Currency::className(), 'targetAttribute' => ['currency_id' => 'id']],
            [['customer_id'], 'exist', 'skipOnError' => true, 'targetClass' => Customer::className(), 'targetAttribute' => ['customer_id' => 'id']],
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
            'consecutive' => Yii::t('backend', 'Consecutivo'),
            'branch_office_id' => Yii::t('backend', 'Sucursal'),
            'customer_id' => Yii::t('backend', 'Cliente'),
            'credit_days_id' => Yii::t('backend', 'Días de crédito'),
            'condition_sale_id' => Yii::t('backend', 'Condición de venta'),
            'request_date' => Yii::t('backend', 'Fecha de emisión'),
            'change_type' => Yii::t('backend', 'Tipo de cambio'),
            'currency_id' => Yii::t('backend', 'Moneda'),
            'status' => Yii::t('backend', 'Estado'),
            'delivery_time' => Yii::t('backend', 'Tiempo de entrega'),
            'delivery_time_type' => Yii::t('backend', 'Tipo de tiempo'),
            'discount_percent' => Yii::t('backend', 'Descuento %'),
            'seller_id' => Yii::t('backend', 'Vendedor'),
            'observations' => Yii::t('backend', 'Observaciones'),
            'is_editable' => Yii::t('backend', 'Editable'),
            'created_at' => Yii::t('backend', 'Fecha de creación'),
            'updated_at' => Yii::t('backend', 'Fecha de actualización'),
            'payment_methods' => Yii::t('backend', 'Medios de pagos (hasta 4)'),
            'box_id' => Yii::t('backend', 'Caja'),
            'invoice_type' => Yii::t('backend', 'Tipo de documento'),
            'facturada'=> Yii::t('backend', 'Facturada'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItemProformas()
    {
        return $this->hasMany(ItemProforma::className(), ['proforma_id' => 'id']);
    }

    public function getItemCount(){
        return ItemProforma::find()->where(['proforma_id'=>$this->id])->count();
    }        

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBranchOffice()
    {
        return $this->hasOne(BranchOffice::className(), ['id' => 'branch_office_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBox()
    {
        return $this->hasOne(Boxes::className(), ['id' => 'box_id']);
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
    public function getCustomer()
    {
        return $this->hasOne(Customer::className(), ['id' => 'customer_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSeller()
    {
        return $this->hasOne(User::className(), ['id' => 'seller_id']);
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
        return "/proforma";
    }

    /*
    public function afterSave($insert, $changedAttributes)
    {
        if ($this->isNewRecord) {
        }
        if ($this->status == UtilsConstants::PROFORMA_STATUS_STARTED)
            $this->verifyStock();

        return parent::afterSave($insert, $changedAttributes);
    }    
    */ 

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
    public function generateConsecutive()
    {
        $year = date('Y');
        $connection = \Yii::$app->db;
        $sql = "SELECT MAX(SUBSTRING(consecutive, 1, 6)) AS consecutive FROM proforma WHERE SUBSTRING(consecutive, 10, 13)='".$year."'";
        $data = $connection->createCommand($sql);
        $consecutive = $data->queryOne();
        $code = (isset($consecutive))? (int)$consecutive['consecutive'] + 1 : 1;

        return GlobalFunctions::zeroFill($code,6).'-'.date('mY');
    }

    public static function getTotalPrices($id)
    {
        return ItemProforma::find()->where(['proforma_id' => $id])->sum('price_total');
    }

    public static function getResumeProforma($proforma_id)
    {
        $resume = ItemProforma::find()
            ->select([
                'SUM(subtotal) AS subtotal',
                'SUM(tax_amount) AS tax_amount',
                'SUM(discount_amount) AS discount_amount',
                'SUM(exonerate_amount) AS exonerate_amount',
                'SUM(price_total) AS price_total',
            ])
            ->where(['proforma_id' => $proforma_id])
            ->one();

        return $resume;
    }

    /**
     * @param $email
     */
    public function sendEmail($file_pdf)
    {      
        if (is_null($this->customer->email) || empty($this->customer->email) || strlen($this->customer->email) < 5)  
            return UtilsConstants::SEND_MAIL_RESPONSE_TYPE_EMPTY_EMAIL;
        $subject = Yii::t('backend','Proforma #'.$this->consecutive);

        $mailer = Yii::$app->mail->compose(['html' => 'proforma-html'], ['proforma_number' => $this->consecutive])
            ->setTo($this->customer->email)
            ->setFrom([Setting::getEmail() => Setting::getName()])
            ->setSubject($subject)
            ->attach($file_pdf, ['fileName'=>'Proforma_'.$this->consecutive]);
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

    /**
     * @param $proforma_id
     */
    public function verifyStock()
    {        
        $unit_type = '';
        $items = ItemProforma::find()->where(['proforma_id' => $this->id])->all();
        
        foreach ($items AS $key => $item)
        {
            if(isset($item->product_id))
            {
                $product = Product::findOne($item->product_id);
                //$current_stock = ProductHasBranchOffice::getQuantity($item->product_id, $this->id);
                $current_stock = ProductHasBranchOffice::getQuantity($item->product_id);
                $request_quantity = $item->quantity;
                if(isset($item->unit_type_id))
                {
                    $unit_type = $item->unitType->code;

                    if($unit_type == 'CAJ' || $unit_type == 'CJ')
                    {
                        if(isset($product->quantity_by_box))
                        {
                            $request_quantity *= $product->quantity_by_box;
                            $unit_type .= ' [1x'.$product->quantity_by_box.']';
                        }
                    }
                    elseif($unit_type == 'BULT' || $unit_type == 'PAQ')
                    {
                        if(isset($product->package_quantity))
                        {
                            $request_quantity *= $product->package_quantity;
                            $unit_type .= ' [1x'.$product->package_quantity.']';
                        }
                    }
                }

                $request_quantity_total = $request_quantity + $product->min_quantity;

                if($request_quantity > $current_stock)
                {                    
                    Proforma::sendAlertStock($current_stock, $item->quantity, $request_quantity_total, $product, $this, $unit_type);
                }
            }
        }
    }

    /**
     * @param $email
     */
    public static function sendAlertStock($current_stock, $request_quantity, $request_quantity_total, $product, $proforma, $unit_type = '')
    {
        $cc_mails = Setting::getValueByField('proforma_stock_alert_mails');        
        if($cc_mails !== '' && GlobalFunctions::validateCCMails($cc_mails))
        {
            $cc_mails_explode = explode(';', $cc_mails);

            if (count($cc_mails_explode) > 0)
            {
                $mails_to_send = [];

                foreach ($cc_mails_explode AS $email) {
                    $mails_to_send[] = trim($email);
                }
            }
            else
            {
                $mails_to_send = $cc_mails;
            }

            $subject = Yii::t('backend','Alerta sobre cotización de productos');
            $mailer = Yii::$app->mail->compose(['html' => 'alert_proforma_stock-html'], ['current_stock' => $current_stock, 'request_quantity' => $request_quantity, 'request_quantity_total' => $request_quantity_total, 'proforma' => $proforma,'product' => $product, 'unit_type' => $unit_type])
                ->setTo($mails_to_send)
                ->setFrom([Setting::getEmail() => Setting::getName()])
                ->setSubject($subject);

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
    }

    public function getTotalAmount()
    {
        $resume = self::getResumeProforma($this->id);
        $change_type = (isset($this->change_type) && $this->change_type > 0)? $this->change_type : 1;

        $total = ($this->currency->symbol == 'CRC')? $resume->price_total : ($resume->price_total*$change_type);

        return $total;
    }
}
