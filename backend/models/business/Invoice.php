<?php

namespace backend\models\business;

use backend\models\nomenclators\BranchOffice;
use backend\models\nomenclators\ConditionSale;
use backend\models\nomenclators\CreditDays;
use backend\models\nomenclators\Currency;
use backend\models\nomenclators\PaymentMethod;
use backend\models\nomenclators\RouteTransport;
use backend\models\nomenclators\UtilsConstants;
use backend\models\nomenclators\Boxes;
use backend\models\settings\Issuer;
use backend\models\settings\Setting;
use common\models\User;
use Yii;
use backend\models\BaseModel;
use yii\helpers\FileHelper;
use yii\helpers\StringHelper;
use common\models\GlobalFunctions;
use yii\helpers\Html;
use Da\QrCode\QrCode;
use yii\helpers\Url;
use kartik\mpdf\Pdf;
use Mpdf\QrCode\Output;
use common\components\ApiV43\ApiXML;
use common\components\ApiV43\ApiFirmadoHacienda;

/**
 * This is the model class for table "invoice".
 *
 * @property int $id
 * @property int|null $branch_office_id
 * @property int|null $customer_id
 * @property int|null $condition_sale_id
 * @property int|null $credit_days_id
 * @property int|null $currency_id
 * @property int|null $invoice_type
 * @property string|null $key
 * @property string|null $consecutive
 * @property string|null $emission_date
 * @property float|null $change_type
 * @property string|null $pay_date fecha en la que se cancela la factura por un abono
 * @property string|null $observations
 * @property int|null $status_account_receivable_id
 * @property string|null $response_xml
 * @property int|null $contingency
 * @property int|null $correct_invoice 1 Si corrige una factura
 * @property int|null $correct_invoice_id id de la factura que corrige
 * @property string|null $reference_number
 * @property string|null $reference_emission_date
 * @property string|null $reference_code 05 Sustituye comprobante provisional por contingencia
 * @property string|null $reference_reason
 * @property string|null $access_token
 * @property int|null $erased_by_note
 * @property int|null $num_request_hacienda_set
 * @property int|null $num_request_hacienda_get
 * @property float|null $total_tax
 * @property float|null $total_discount
 * @property float|null $total_exonerado
 * @property float|null $total_comprobante
 * @property int|null $status
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property int|null $route_transport_id
 * @property int|null $status_hacienda
 * @property int|null $ready_to_send_email
 * @property int|null $email_sent
 *
 * @property BranchOffice $branchOffice
 * @property ConditionSale $conditionSale
 * @property CreditDays $creditDays
 * @property Currency $currency
 * @property Customer $customer
 * @property Invoice $correctInvoice
 * @property Invoice[] $invoices
 * @property User $seller
 * @property User $collector
 * @property ItemInvoice[] $itemInvoices
 * @property PaymentMethodHasInvoice[] $paymentMethodHasInvoices
 * @property PaymentMethod[] $paymentMethods
 * @property RouteTransport $routeTransport

 */
class Invoice extends BaseModel
{
    public $payment_methods = [];
    public $sellers = [];
    public $collectors = [];

    // Resumen de la factura
    public $totalServGravados = 0;
    public $totalServExentos = 0;
    public $totalMercanciasGravadas = 0;
    public $totalMercanciasExentas = 0;

    public $totalImpuestoServGravados = 0;
    public $totalImpuestoMercanciasGravadas = 0;
    public $totalImpuestoServExonerados = 0;
    public $totalImpuestoMercanciasExoneradas = 0;
    public $totalMontoServExonerado = 0;
    public $totalMontoMercExonerado = 0;
    public $totalImpuestoNeto = 0;

    public $totalGravado = 0;
    public $totalExento = 0;
    public $totalExonerado = 0;
    public $totalVenta = 0;
    public $totalDescuentos = 0;
    public $totalVentaNeta = 0;
    public $totalImpuesto = 0;
    public $montoTotal = 0;

    public $dias_trascurridos;
    public $dias_vencidos;
    public $color;	

    public $cliente;
    public $vendedor;   
    
    public $commercial_name;


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'invoice';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            //[['emission_date', 'consecutive', 'branch_office_id', 'box_id', 'customer_id', 'condition_sale_id', 'currency_id', 'status', 'change_type', 'payment_methods', 'invoice_type'], 'required'],
            [['emission_date', 'branch_office_id', 'box_id', 'customer_id', 'condition_sale_id', 'currency_id', 'status', 'change_type', 'payment_methods', 'invoice_type'], 'required'],
            [[
                'branch_office_id', 'customer_id', 'condition_sale_id', 'credit_days_id', 'currency_id', 'status_account_receivable_id', 'contingency', 'correct_invoice', 'correct_invoice_id', 'printed',
                'erased_by_note', 'num_request_hacienda_set', 'user_id', 'num_request_hacienda_get', 'status', 'invoice_type', 'status_hacienda', 'route_transport_id', 'ready_to_send_email', 'email_sent', 'box_id'
            ], 'integer'],
            [['emission_date', 'pay_date', 'reference_emission_date', 'created_at', 'updated_at', 'payment_methods', 'sellers', 'collectors', 'commercial_name'], 'safe'],
            [['change_type', 'total_tax', 'total_discount', 'total_exonerado', 'total_comprobante'], 'number'],
            [['observations'], 'string'],

            // Regla de validación condicional
            ['credit_days_id', 'required', 'when' => function ($model) {
                // Esta función anónima se ejecutará para determinar si la regla de validación debe aplicarse
                // Retorna true si condition_sale_id es igual a uno, lo que indica que credit_day es obligatorio
                return $model->condition_sale_id == 9; // Credito
            }],

            ['contract', 'validateContractNumber'],

            [['key', 'consecutive', 'response_xml', 'reference_number', 'reference_code', 'reference_reason', 'access_token', 'printed_user'], 'string', 'max' => 255],
            [['contract', 'confirmation_number'], 'string', 'max' => 10],
            [['branch_office_id'], 'exist', 'skipOnError' => true, 'targetClass' => BranchOffice::className(), 'targetAttribute' => ['branch_office_id' => 'id']],
            [['condition_sale_id'], 'exist', 'skipOnError' => true, 'targetClass' => ConditionSale::className(), 'targetAttribute' => ['condition_sale_id' => 'id']],
            [['credit_days_id'], 'exist', 'skipOnError' => true, 'targetClass' => CreditDays::className(), 'targetAttribute' => ['credit_days_id' => 'id']],
            [['currency_id'], 'exist', 'skipOnError' => true, 'targetClass' => Currency::className(), 'targetAttribute' => ['currency_id' => 'id']],
            [['customer_id'], 'exist', 'skipOnError' => true, 'targetClass' => Customer::className(), 'targetAttribute' => ['customer_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
            [['correct_invoice_id'], 'exist', 'skipOnError' => true, 'targetClass' => Invoice::className(), 'targetAttribute' => ['correct_invoice_id' => 'id']],
            [['route_transport_id'], 'exist', 'skipOnError' => true, 'targetClass' => RouteTransport::className(), 'targetAttribute' => ['route_transport_id' => 'id']],
            [['box_id'], 'exist', 'skipOnError' => true, 'targetClass' => Boxes::className(), 'targetAttribute' => ['box_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('backend', 'ID'),
            'branch_office_id' => Yii::t('backend', 'Sucursal'),
            'customer_id' => Yii::t('backend', 'Cliente'),
            'condition_sale_id' => Yii::t('backend', 'Condición de venta'),
            'credit_days_id' => Yii::t('backend', 'Días de crédito'),
            'currency_id' => Yii::t('backend', 'Moneda'),
            'invoice_type' => Yii::t('backend', 'Tipo de documento'),
            'key' => Yii::t('backend', 'Clave'),
            'consecutive' => Yii::t('backend', 'Consecutivo'),
            'emission_date' => Yii::t('backend', 'Fecha de emisión'),
            'change_type' => Yii::t('backend', 'Tipo de cambio'),
            'pay_date' => Yii::t('backend', 'Fecha de pago'),
            'observations' => Yii::t('backend', 'Observaciones'),
            'status_account_receivable_id' => Yii::t('backend', 'Estado de cuenta por cobrar'),
            'response_xml' => Yii::t('backend', 'Respuesta XML'),
            'contingency' => Yii::t('backend', 'Contingencia'),
            'correct_invoice' => Yii::t('backend', 'Corrige factura'),
            'correct_invoice_id' => Yii::t('backend', 'Factura que corrige'),
            'reference_number' => Yii::t('backend', 'Número de referencia'),
            'reference_emission_date' => Yii::t('backend', 'Fecha de emisión de referencia'),
            'reference_code' => Yii::t('backend', 'Código de referencia'),
            'reference_reason' => Yii::t('backend', 'Razón de referencia'),
            'access_token' => Yii::t('backend', 'Access Token'),
            'erased_by_note' => Yii::t('backend', 'Borrado por nota'),
            'num_request_hacienda_set' => Yii::t('backend', 'Número de peticiones SET a Hacienda'),
            'num_request_hacienda_get' => Yii::t('backend', 'Número de peticiones GET a Hacienda'),
            'total_tax' => Yii::t('backend', 'Impuesto total'),            
            'total_discount' => Yii::t('backend', 'Descuento total'),
            'total_exonerado' => Yii::t('backend', 'Exonerado total'),
            'total_comprobante' => Yii::t('backend', 'Comprobante total'),            
            'status' => Yii::t('backend', 'Estado factura'),
            'status_hacienda' => Yii::t('backend', 'Estado de hacienda'),
            'created_at' => Yii::t('backend', 'Fecha de creación'),
            'updated_at' => Yii::t('backend', 'Fecha de actualización'),
            'payment_methods' => Yii::t('backend', 'Medios de pagos (hasta 4)'),
            'sellers' => Yii::t('backend', 'Agente Vendedor'),
            'collectors' => Yii::t('backend', 'Agente Cobrador'),
            'route_transport_id' => Yii::t('backend', 'Ruta de transporte'),
            'ready_to_send_email' => Yii::t('backend', 'Lista para enviar'),
            'email_sent' => Yii::t('backend', 'Email enviado'),
            'box_id' => Yii::t('backend', 'Caja'),
            'commercial_name' => Yii::t('backend', 'Nombre Comercial'),
            'printed'=> Yii::t('backend', 'Impreso'),
            'printed_user'=> Yii::t('backend', 'Usuaro que Imprimió'),
            'user_id' => Yii::t('backend', 'Usuario'),
            'contract'=> Yii::t('backend', 'Contrato'),
            'confirmation_number'=> Yii::t('backend', 'No. Confirmación'),
        ];
    }

    public function validateContractNumber($attribute, $params)
    {        
        if (!$this->hasErrors()) {
            $query = self::find()->where(['contract' => $this->contract])
                                 ->andWhere(['status_hacienda'=>[UtilsConstants::HACIENDA_STATUS_NOT_SENT, UtilsConstants::HACIENDA_STATUS_RECEIVED, 
                                                                 UtilsConstants::HACIENDA_STATUS_ACCEPTED]]);
            
            // Si el modelo tiene un ID, significa que se está actualizando
            if (!$this->isNewRecord) {
                $query->andWhere(['!=', 'id', $this->id]);
            }
    
            $count = $query->count();
    
            if ($count >= 2) {
                $this->addError($attribute, 'No puede haber más de dos registros con el mismo número de contrato.');
                Yii::info('Invoice: ' . $this->contract . ' Duplicada ');
            }
        }            
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
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
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
    public function getCorrectInvoice()
    {
        return $this->hasOne(Invoice::className(), ['id' => 'correct_invoice_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getInvoices()
    {
        return $this->hasMany(Invoice::className(), ['correct_invoice_id' => 'id']);
    }     

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRouteTransport()
    {
        return $this->hasOne(RouteTransport::className(), ['id' => 'route_transport_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItemInvoices()
    {
        return $this->hasMany(ItemInvoice::className(), ['invoice_id' => 'id']);
    }

    public function getItemCount(){
        return ItemInvoice::find()->where(['invoice_id'=>$this->id])->count();
    }       

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAbonosInvoices()
    {
        return $this->hasMany(InvoiceAbonos::className(), ['invoice_id' => 'id']);
    }    

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPaymentMethodHasInvoices()
    {
        return $this->hasMany(PaymentMethodHasInvoice::className(), ['invoice_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPaymentMethods()
    {
        return $this->hasMany(PaymentMethod::className(), ['id' => 'payment_method_id'])->viaTable('payment_method_has_invoice', ['invoice_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBox()
    {
        return $this->hasOne(Boxes::className(), ['id' => 'box_id']);
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
        return "/invoice";
    }

    /*
    public function afterSave($insert, $changedAttributes)
    {
        if ($this->isNewRecord) {
        }
        if ($this->status_hacienda == UtilsConstants::HACIENDA_STATUS_NOT_SENT) 
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

    /** :::::::::::: END > Abstract Methods and Overrides ::::::::::::*/

    public function generateConsecutive()
    {
        $issuer = Issuer::find()->one();

        //Sucursal
        $a_number = str_pad($this->branchOffice->code, 3, '0', STR_PAD_LEFT);

        // Caja
        $b_number =  str_pad($this->box->numero, 5, '0', STR_PAD_LEFT);

        $c_number = '01'; //Por defecto Factura

        // Tipo de comprobante
        $initconsecutive = $issuer->init_consecutive_invoice;
        $invoice_type = (int) $this->invoice_type;
        if ($invoice_type === UtilsConstants::PRE_INVOICE_TYPE_TICKET) {
            $c_number = '04';
            $initconsecutive = $issuer->init_consecutive_tiquete;
        }
        
        $identificacion = $a_number . $b_number . $c_number;

        $connection = \Yii::$app->db;
        $sql = "SELECT MAX(SUBSTRING(consecutive, 11, 10)) AS consecutive FROM invoice where invoice_type = $invoice_type AND SUBSTRING(consecutive, 1, 10) = '" . $identificacion . "'";
        $data = $connection->createCommand($sql);
        $consecutive = $data->queryOne();
        if ((is_null($consecutive) || empty($consecutive) || $consecutive == 0) && $initconsecutive > 0)
            $code = $initconsecutive;
        else
            $code = (isset($consecutive)) ? (int) $consecutive['consecutive'] + 1 : 1;
        //if ($code == 1)
          //  $code = 6997;

        /*
        Esta numeración se crea de la siguiente manera:
        A: Los 3 primeros dígitos identifican el local o establecimiento donde se emitió el comprobante electrónico o documento asociado.
           El número 001 corresponde a la oficina central, casa matriz o establecimiento principal y los número 002 y subsiguientes identifican cada una de las sucursales.
        B: Del cuarto al octavo dígito identificará la terminal o punto de venta de emisión del comprobante electrónico o documento asociado.
           En los casos que solo se cuente con una terminal o se posea un servidor centralizado deberá visualizarse de la siguiente manera “00001”.
        C: Del noveno al décimo espacio corresponderá al tipo de comprobante electrónico o documento asociado. Se deben utilizar los siguientes códigos:
            Factura electrónica	01
            Nota de débito electrónica	02
            Nota de crédito electrónica	03
            Tiquete Electrónico	04
            Confirmación de aceptación del comprobante electrónico	05
            Confirmación de aceptación parcial del comprobante electrónico	06
            Confirmación de rechazo del comprobante electrónico	07
        D: Del undécimo al vigésimo dígito le corresponderá al consecutivo de los comprobantes electrónicos o documento asociado.
           Inicia con el número 1, para cada sucursal o terminal según sea el caso
        */
        // Consecutivo del comprobante
        $d_number = $code;

        $result = $a_number . '' . $b_number . '' . $c_number . '' . str_pad($d_number, 10, '0', STR_PAD_LEFT);
        return $result;
    }

    public function generateKey()
    {
        $issuer = Issuer::find()->one();

        $this->emission_date = (isset($this->emission_date) && !empty($this->emission_date)) ? date('Y-m-d H:i:s', strtotime($this->emission_date)) : date('Y-m-d H:i:s');

        $this->key = '506' . date('d') . date('m') . date('y');
        // La identificación debe tener una longitud de 12 digitos, completar con 0
        $this->key .= str_pad($issuer->identification, 12, '0', STR_PAD_LEFT);

        $this->key .= $this->consecutive;
        // Un digito para situación del comprobante electrónico
        // 1  Normal: Comprobantes electrónicos que son generados y transmitidos en el mismo acto de compra-venta y prestación del servicio al sistema de validación de comprobantes electrónicos de la Dirección General de Tributación de Costa Rica.
        // 2  Contingencia:	Comprobantes electrónicos que sustituyen al comprobante físico emitido por contingencia.
        // 3  Sin internet:	Comprobantes que han sido generados y expresados en formato electrónico, pero no se cuenta con el respectivo acceso a internet para el envío inmediato de los mismos a la Dirección General de Tributación de Costa Rica.
        $this->key .= '1';
        // Los restantes dígitos son un código de seguridad generados por el sistema nuestro
        $this->key .= date('Y') . date('m') . date('d');
    }

    function afterFind()
    {
        $this->emission_date = date('Y-m-d H:i:s', strtotime($this->emission_date));

        //BEGIN payment method has invoice
        $payment_methods_assigned = PaymentMethodHasInvoice::getPaymentMethodByInvoiceId($this->id);

        $payment_methods_assigned_ids = [];
        foreach ($payment_methods_assigned as $value) {
            $payment_methods_assigned_ids[] = $value['payment_method_id'];
        }

        $this->payment_methods = $payment_methods_assigned_ids;
        //END payment method has invoice     
        
        

        //BEGIN seller method has invoice
        $sellers_assigned = SellerHasInvoice::getSellerByInvoiceId($this->id);

        $sellers_assigned_ids = [];
        foreach ($sellers_assigned as $value) {
            $sellers_assigned_ids[] = $value['seller_id'];
        }

        $this->sellers = $sellers_assigned_ids;
        //END seller method has invoice 


        //BEGIN collector method has invoice
        $collectors_assigned = CollectorHasInvoice::getCollectorByInvoiceId($this->id);

        $collectors_assigned_ids = [];
        foreach ($collectors_assigned as $value) {
            $collectors_assigned_ids[] = $value['collector_id'];
        }

        $this->collectors = $collectors_assigned_ids;
        //END collector method has invoice         


        $this->setResumenFactura();

        if (!is_null($this->reference_emission_date) && !empty($this->reference_emission_date)) {
            $this->reference_emission_date = date("Y-m-d", strtotime($this->reference_emission_date));
        }
    }

    public function beforeSave($insert)
    {
        //$resume = self::getResumeInvoice($this->id);
        //$total_price = $resume->subtotal + $resume->tax_amount - $resume->discount_amount - $resume->exonerate_amount;

        $this->total_tax = $this->totalImpuesto;
        $this->total_discount = $this->totalDescuentos;
        $this->total_exonerado = $this->totalExonerado;

        if (is_null($this->access_token) || empty($this->access_token))
            $this->access_token = Yii::$app->security->generateRandomString() . '_' . time();

        if (parent::beforeSave($insert)) {
            $this->emission_date = date('Y-m-d H:i:s');
            /*
            if (is_null($this->id) || empty($this->id)) {
                $issuer = Issuer::find()->one();

                $this->emission_date = (isset($this->emission_date) && !empty($this->emission_date)) ? date('Y-m-d H:i:s', strtotime($this->emission_date)) : date('Y-m-d H:i:s');

                $this->key = '506' . date('d') . date('m') . date('y');
                // La identificación debe tener una longitud de 12 digitos, completar con 0
                $this->key .= str_pad($issuer->identification, 12, '0', STR_PAD_LEFT);

                $this->key .= $this->consecutive;
                // Un digito para situación del comprobante electrónico
                // 1  Normal: Comprobantes electrónicos que son generados y transmitidos en el mismo acto de compra-venta y prestación del servicio al sistema de validación de comprobantes electrónicos de la Dirección General de Tributación de Costa Rica.
                // 2  Contingencia:	Comprobantes electrónicos que sustituyen al comprobante físico emitido por contingencia.
                // 3  Sin internet:	Comprobantes que han sido generados y expresados en formato electrónico, pero no se cuenta con el respectivo acceso a internet para el envío inmediato de los mismos a la Dirección General de Tributación de Costa Rica.
                $this->key .= '1';
                // Los restantes dígitos son un código de seguridad generados por el sistema nuestro
                $this->key .= date('Y') . date('m') . date('d');
            }
            */
            return true;
        } else {
            return false;
        }
    }

    function zerofill($valor, $longitud)
    {
        $res = str_pad($valor, $longitud, '0', STR_PAD_LEFT);
        return $res;
    }

    public function setResumenFactura()
    {
        $moneda = 'COLONES';
        $factura_detalles = ItemInvoice::findAll(['invoice_id' => $this->id]);

        foreach ($factura_detalles as $key => $detalle) {
            $this->montoTotal += $detalle->getMonto($moneda);

            $this->totalServGravados += $detalle->getServGravado($moneda);
            $this->totalMercanciasGravadas += $detalle->getMercanciaGravada($moneda);

            $this->totalImpuestoServGravados += $detalle->getImpuestoServGravado($moneda);
            $this->totalImpuestoMercanciasGravadas += $detalle->getImpuestoMercanciaGravada($moneda);

            $this->totalImpuestoServExonerados += $detalle->getImpuestoServExonerado($moneda);
            $this->totalImpuestoMercanciasExoneradas += $detalle->getImpuestoMercanciaExonerada($moneda);

            $this->totalMontoServExonerado += $detalle->getMontoServExonerado($moneda);
            $this->totalMontoMercExonerado += $detalle->getMontoMercExonerado($moneda);

            $this->totalImpuestoNeto += $detalle->getMontoImpuestoNeto($moneda);

            $this->totalServExentos += $detalle->getServExento($moneda);
            $this->totalMercanciasExentas += $detalle->getMercanciaExenta($moneda);
            $this->totalDescuentos += $detalle->getDescuento($moneda);
        }
        $this->totalGravado = $this->totalServGravados + $this->totalMercanciasGravadas;
        $this->totalExento = $this->totalServExentos + $this->totalMercanciasExentas;
        $this->totalExonerado = $this->totalMontoServExonerado + $this->totalMontoMercExonerado;

        $this->totalVenta = $this->totalGravado + $this->totalExento + $this->totalExonerado;
        $this->totalVentaNeta = $this->totalVenta - $this->totalDescuentos;

        $this->totalImpuesto = $this->totalImpuestoServGravados + $this->totalImpuestoMercanciasGravadas - $this->totalImpuestoServExonerados - $this->totalImpuestoMercanciasExoneradas;
        $this->total_comprobante = $this->totalVentaNeta + $this->totalImpuesto;

        $this->total_tax = $this->totalImpuesto;
        $this->total_discount = $this->totalDescuentos;
        $this->total_exonerado = $this->totalExonerado;
    }

    public static function getTotalPrices($id)
    {
        return ItemInvoice::find()->where(['invoice_id' => $id])->sum('price_total');
    }

    public static function getResumeInvoice($invoice_id)
    {
        $resume = ItemInvoice::find()
            ->select([
                'SUM(subtotal) AS subtotal',
                'SUM(tax_amount) AS tax_amount',
                'SUM(discount_amount) AS discount_amount',
                'SUM(exonerate_amount) AS exonerate_amount',
                'SUM(price_total) + SUM(tax_amount) AS price_total',
            ])
            ->where(['invoice_id' => $invoice_id])
            ->one();

        return $resume;
    }

    /**
     * @param $email
     */
    public function sendEmail($subject, $email, $email_cc, $body)
    {
        //$subject = Yii::t('backend', 'Factura electrónica #' . $this->consecutive);
        //$email_cc = UtilsConstants::getListaEmailsByEmailString($this->customer->email_cc);
        $emisor = Issuer::find()->one();
        $logo = "<img src=\"" . GlobalFunctions::BASE_URL. Setting::getUrlLogoBySettingAndType(2, Setting::SETTING_ID) . "\" width=\"165\"/>";        

        $mailer = Yii::$app->mail->compose(['html' => 'notification-invoice-html'], [
                'logo'=>$logo,
                'key'=>$this->key,
                'emisor'=>$emisor->name,
                'cliente'=>$this->customer->name,
                'consecutive'=>$this->consecutive,
                'emission_date'=>date('d-m-Y', strtotime($this->emission_date)),
                'symbol'=>$this->currency->symbol,
                'total'=>$this->total_comprobante,
                'body'=>$body,
            ])
            ->setTo($email)
            ->setCc($email_cc)
            ->setFrom([Setting::getEmail() => Setting::getName()])
            ->setSubject($subject);
            //->attach($file_pdf, ['fileName' => 'Factura_' . $this->consecutive]);

        // Adjuntar PDF
        $nombrearchivo = $this->key . '.pdf';
        $archivo = $this->getInvoicePdf($this->id, true, 'COLONES', $destino = 'file', $nombrearchivo);        
        if (!empty($archivo)) {
            $mailer->attach($archivo, ['fileName' => $nombrearchivo]);
        }

        // Adjuntar XML del SISTEMA
        $invoice = Invoice::find()->where(['id' => $this->id])->one();
        $items_invoice = ItemInvoice::find()->where(['invoice_id' => $this->id])->all();

        $apiXML = new ApiXML();
        $issuer = Issuer::find()->one();
        $xml = $apiXML->genXMLFe($issuer, $invoice, $items_invoice);

        $p12Url = $issuer->getFilePath();
        $pinP12 = $issuer->certificate_pin;

        $doc_type = '01'; // Factura
        $apiFirma = new ApiFirmadoHacienda();
        $xmlFirmado = $apiFirma->firmar($p12Url, $pinP12, $xml, $doc_type);

        $xml = base64_decode($xmlFirmado);

        $nombre_archivo = $invoice->key . '.xml';
        // create attachment on-the-fly
        $mailer->attachContent($xml, ['fileName' => $nombre_archivo, 'contentType' => 'text/plain']);


        // Adjuntar XML de respuesta de Hacienda si existe
        $url_xml_hacienda_verificar = Yii::getAlias('@backend/web/uploads/xmlh/FE-MH-' . $invoice->key . '.xml');
        $nombre_archivo = $invoice->key . '_respuesta.xml';
        if (file_exists($url_xml_hacienda_verificar))
            $mailer->attach($url_xml_hacienda_verificar, ['fileName' => $nombre_archivo]);

        // Adjuntar documentos asociados a la factura
        $documents = InvoiceDocuments::find()->where(['invoice_id'=>$invoice->id])->all();
        foreach ($documents as $document)
        {
            $doc_verificar = Yii::getAlias('@backend/web/uploads/documents/' . $document->documento);
            if (file_exists($doc_verificar) && !empty($document->documento) )
                $mailer->attach($doc_verificar, ['fileName' => $document->documento]);            
        }

        try {
            if ($mailer->send()) {
                return UtilsConstants::SEND_MAIL_RESPONSE_TYPE_SUCCESS;
            } else {
                return UtilsConstants::SEND_MAIL_RESPONSE_TYPE_ERROR;
            }
        } catch (\Swift_TransportException $e) {
            return UtilsConstants::SEND_MAIL_RESPONSE_TYPE_EXCEPTION;
        }
    } 

    /**
     * @param $invoice_id
     */
    public function verifyStock()
    {
        $unit_type = '';
        $items = ItemInvoice::find()->where(['invoice_id' => $this->id])->all();
        foreach ($items as $key => $item) {
            if (isset($item->product_id)) {
                $product = Product::findOne($item->product_id);
                //$current_stock = ProductHasBranchOffice::getQuantity($item->product_id, $this->id);
                $current_stock = ProductHasBranchOffice::getQuantity($item->product_id);
                $request_quantity = $item->quantity;
                if (isset($item->unit_type_id)) {
                    $unit_type = $item->unitType->code;

                    if ($unit_type == 'CAJ' || $unit_type == 'CJ') {
                        if (isset($product->quantity_by_box)) {
                            $request_quantity *= $product->quantity_by_box;
                            $unit_type .= ' [1x' . $product->quantity_by_box . ']';
                        }
                    } elseif ($unit_type == 'BULT' || $unit_type == 'PAQ') {
                        if (isset($product->package_quantity)) {
                            $request_quantity *= $product->package_quantity;
                            $unit_type .= ' [1x' . $product->package_quantity . ']';
                        }
                    }
                }

                $request_quantity_total = $request_quantity + $product->min_quantity;

                if ($request_quantity > $current_stock) {
                    Invoice::sendAlertStock($current_stock, $item->quantity, $request_quantity_total, $product, $this, $unit_type);                    
                }
            }
        }
    }

    /**
     * @param $email
     */
    public static function sendAlertStock($current_stock, $request_quantity, $request_quantity_total, $product, $invoice, $unit_type = '')
    {
        $cc_mails = Setting::getValueByField('proforma_stock_alert_mails');

        if ($cc_mails !== '' && GlobalFunctions::validateCCMails($cc_mails)) {
            $cc_mails_explode = explode(';', $cc_mails);

            if (count($cc_mails_explode) > 0) {
                $mails_to_send = [];

                foreach ($cc_mails_explode as $email) {
                    $mails_to_send[] = trim($email);
                }
            } else {
                $mails_to_send = $cc_mails;
            }


            $subject = Yii::t('backend', 'Alerta sobre facturación de productos');

            $mailer = Yii::$app->mail->compose(['html' => 'alert_invoice_stock-html'], ['current_stock' => $current_stock, 'request_quantity' => $request_quantity, 'request_quantity_total' => $request_quantity_total, 'invoice' => $invoice, 'product' => $product, 'unit_type' => $unit_type])
                ->setTo($mails_to_send)
                ->setFrom([Setting::getEmail() => Setting::getName()])
                ->setSubject($subject);

            try {
                if ($mailer->send()) {
                    return UtilsConstants::SEND_MAIL_RESPONSE_TYPE_SUCCESS;
                } else {
                    return UtilsConstants::SEND_MAIL_RESPONSE_TYPE_ERROR;
                }
            } catch (\Swift_TransportException $e) {
                return UtilsConstants::SEND_MAIL_RESPONSE_TYPE_EXCEPTION;
            }
        }
    }

    public function getTotalAmount()
    {
        $resume = self::getResumeInvoice($this->id);
        $change_type = (isset($this->change_type) && $this->change_type > 0) ? $this->change_type : 1;

        $total = ($this->currency->symbol == 'CRC') ? $resume->price_total : ($resume->price_total * $change_type);

        return $total ?? 0;
    }

    /**
     * @param $invoice_id
     * @param $new_status
     */
    public static function setStatusHacienda($invoice_id, $new_status)
    {
        $invoice = self::findOne($invoice_id);
        $invoice->status_hacienda = $new_status;
        $invoice->save(false);
    }

    /**
     * Process to generate QR code
     * @param boolean $is_backend
     * @return string
     */
    public function generateQrCode()
    {
        if (!file_exists("uploads/invoice_qr/") || !is_dir("uploads/invoice_qr/")) {
            try {
                FileHelper::createDirectory("uploads/invoice_qr/", 0777);
            } catch (\Exception $exception) {
                Yii::info("Error handling invoice_qr folder resources");
            }
        }
        $name_qrcode = $this->key;
        $url = GlobalFunctions::BASE_URL.'/hacienda/show-comprobante?key='. $name_qrcode;

        $qrCode = (new QrCode($url))->setSize(250)->setMargin(20);

        $path = Yii::$app->basePath . '/web/uploads/invoice_qr/';

        $qrCode->writeFile($path . $name_qrcode . '.png');

        $file = GlobalFunctions::BASE_URL. Url::to('@web/uploads/invoice_qr/' . $name_qrcode . '.png');

        return $file;
    }

    /**
     * @param string $key
     * @param string $status_hacienda
     * @param string $xml_response_hacienda_decode
     */
    public static function verifyResponseStatusHacienda($key, $status_hacienda, $xml_response_hacienda_decode)
    {
        if ($key !== null) {
            $invoice = self::find()->where(['key' => $key])->one();

            if ($invoice !== null) {
                if (!file_exists("uploads/xmlh/") || !is_dir("uploads/xmlh/")) {
                    try {
                        FileHelper::createDirectory("uploads/xmlh/", 0777);
                    } catch (\Exception $exception) {
                        Yii::info("Error handling xmlh folder resources");
                    }
                }

                if ($status_hacienda == 'rechazado') {
                    self::setStatusHacienda($invoice->id, UtilsConstants::HACIENDA_STATUS_REJECTED); // Rechazada

                    $xml_filename = $invoice->key . '_respuesta.xml';
                    $path = Yii::getAlias('@backend/web/uploads/xmlh/' . $xml_filename);
                    file_put_contents($path, $xml_response_hacienda_decode);
                    $invoice->response_xml = $path;
                } elseif ($status_hacienda == 'aceptado') {
                    self::setStatusHacienda($invoice->id, UtilsConstants::HACIENDA_STATUS_ACCEPTED); // Aceptada
                    
                    // Verifico si el estado ya estaba en aceptada entonces no enviar email ni rebajar de inventario
                    //if ($invoice->status_hacienda != UtilsConstants::HACIENDA_STATUS_ACCEPTED)
                    if ($invoice->status_hacienda == UtilsConstants::HACIENDA_STATUS_RECEIVED)                    
                    {
                        $xml_filename = $invoice->key . '_respuesta.xml';
                        $path = Yii::getAlias('@backend/web/uploads/xmlh/' . $xml_filename);
                        file_put_contents($path, $xml_response_hacienda_decode);
                        $invoice->response_xml = $xml_filename;

                        //$invoice->verifyStock();

                        // Extraer del inventario 
                        $enviar = true;             
                        //$items_associates = ItemInvoice::findAll(['invoice_id' => $invoice->id]);                        
                        //if ($invoice->invoice_type == UtilsConstants::PRE_INVOICE_TYPE_INVOICE && $enviar == true)
                        //{
                        // Enviar documentos por emails
                        $subject = Yii::t('backend', 'Factura electrónica #' . $invoice->consecutive);
                        $email = $invoice->customer->email;
                        $email_cc = UtilsConstants::getListaEmailsByEmailString($invoice->customer->email_cc);
                        $issuer = Issuer::find()->one();
                        $email_cc[] = $issuer->email;
                        $body = '';
                        $invoice->sendEmail($subject, $email, $email_cc, $body);
                        //}
                    }

                } elseif ($status_hacienda == 'recibido') {
                    self::setStatusHacienda($invoice->id, UtilsConstants::HACIENDA_STATUS_RECEIVED); // Recibida
                }

                $invoice->save(false);
            }
        }
    }

    public function IsCanceled()
    {
        $cancelada = false;
        if ($this->status_hacienda == UtilsConstants::HACIENDA_STATUS_CANCELLED || $this->status_hacienda == UtilsConstants::HACIENDA_STATUS_ANULATE)
            $cancelada = true;

        if ($this->status_hacienda == UtilsConstants::HACIENDA_STATUS_CREDIT_NOTE) {
            $nota = CreditNote::find()->where(['reference_number' => trim($this->key)])->one();
            if (!is_null($nota) && $nota->reference_code == '01')
                $cancelada = true;
        }
        return $cancelada;
    }

    public function getTotalItem()
    {
        $data = ItemInvoice::find()->where(['invoice_id' => $this->id])->sum('quantity');
        if (is_null($data))
            $data = 0;
        return (int)$data;
    }

    public function getInvoicePdf($invoice_id, $original, $moneda = 'COLONES', $destino = 'browser', $filename = 'Factura')
    {
        $logo = "<img src=\"" . GlobalFunctions::BASE_URL. Setting::getUrlLogoBySettingAndType(2, Setting::SETTING_ID) . "\" width=\"165\"/>";        
        $configuracion = Setting::find()->where(['id' => 1])->one();
        $textCuentas = $configuracion->bank_information;
        $invoice = Invoice::find()->where(['id' => $invoice_id])->one();
        $data = '';

        $qr_code_invoice = $invoice->generateQrCode();
        $img_qr = '<img src="' . $qr_code_invoice . '" width="100"/>';

        $items_invoice = ItemInvoice::find()->where(['invoice_id' => $invoice->id])->all();

        $data = \Yii::$app->view->renderFile(Yii::getAlias('@common').'/mail/FE-html.php', [
            'invoice' => $invoice,
            'items_invoice' => $items_invoice,
            'logo' => $logo,
            'moneda' => $moneda,
            'original' => $original,
            'img_qr' => $img_qr,
            'textCuentas' => $textCuentas,
        ]);

        if ($destino == 'browser') {
            Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
            $pdf = new Pdf([
                'mode' => Pdf::MODE_CORE,
                'destination' => Pdf::DEST_BROWSER,
                'content' => $data,
                'filename' => $filename,
                'options' => [
                    // any mpdf options you wish to set
                    'title' => 'Factura',
                    'defaultheaderline' => 0,
                    //'default_font' => 'Calibri',
                    'setAutoTopMargin' => 'stretch',
                    'showWatermarkText' => $invoice->IsCanceled(),
                ],
                'methods' => [
                    'SetTitle' => Yii::t('backend', 'Facturas'),
                    'SetWatermarkText' => 'ANULADO',
                    'SetSubject' => 'Generating PDF files via yii2-mpdf extension has never been easy',
                    'SetFooter' => ['|' . Yii::t('backend', 'Página') . ' {PAGENO}|'],
                ],
            ]);

            return $pdf->render();
        } else {
            if (!file_exists("uploads/invoice/") || !is_dir("uploads/invoice/")) {
                try {
                    FileHelper::createDirectory("uploads/invoice/", 0777);
                } catch (\Exception $exception) {
                    Yii::info("Error handling Factura folder resources");
                }
            }

            $file_pdf_save = Yii::getAlias('@backend') . '/web/uploads/invoice/' . $filename;

            $pdf = new Pdf([
                'mode' => Pdf::MODE_CORE,
                'destination' => Pdf::DEST_FILE,
                'content' => $data,
                'filename' => $file_pdf_save,
                'options' => [
                    // any mpdf options you wish to set
                    'title' => 'Factura',
                    'defaultheaderline' => 0,
                    //'default_font' => 'Calibri',
                    'setAutoTopMargin' => 'stretch',
                    'showWatermarkText' => $invoice->IsCanceled(),
                ],
                'methods' => [
                    'SetTitle' => Yii::t('backend', 'Facturas'),
                    'SetWatermarkText' => 'ANULADO',
                    'SetSubject' => 'Generating PDF files via yii2-mpdf extension has never been easy',
                    'SetFooter' => ['|' . Yii::t('backend', 'Página') . ' {PAGENO}|'],
                ],
            ]);
            $pdf->render();

            return $file_pdf_save;
        }
    }    

    public static function getTotalVentas($box_id, $opening_date, $opening_time, $seller_id, $payment_methods = NULL)
    {
        $fecha_inicio = date('Y-m-d H:i:s', strtotime($opening_date . ' ' . $opening_time));
        $fecha_fin    = date('Y-m-d H:i:s');

        // Métodos de pago en efectivo 
        //$payment_methods = [1];
        $query = Invoice::find()->join('INNER JOIN', 'payment_method_has_invoice', "payment_method_has_invoice.invoice_id = invoice.id")
                                //->join('INNER JOIN', 'seller_has_invoice', "seller_has_invoice.invoice_id = invoice.id")
                                //->where(['box_id' => $box_id, 'seller_has_invoice.seller_id' => $seller_id])
                                ->where(['box_id' => $box_id])
                                ->andWhere(['>=', 'emission_date', $fecha_inicio])
                                ->andWhere(['<=', 'emission_date', $fecha_fin]);

        if (!is_null($payment_methods))    
            $query->andWhere(['payment_method_has_invoice.payment_method_id'=>$payment_methods]);

        $total_venta = $query->sum('invoice.total_comprobante');

        if (is_null($total_venta))
            $total_venta = 0;
        return $total_venta;
    }

    public static function getCountInvoiceByBox($branch_office, $box)
    {
        $data = Invoice::find()->where(['branch_office_id' => $branch_office, 'box_id' => $box])->count('*');
        if (is_null($data))
            $data = 0;
        return $data;
    }

    public static function getTotalVentasByMetodoPago($box_id, $opening_date, $opening_time, $seller_id)
    {
        $fecha_inicio = date('Y-m-d h:i:s', strtotime($opening_date . ' ' . $opening_time));
        $fecha_fin    = date('Y-m-d h:i:s');

        $ventas = Invoice::find()->select('sum(total_proof_crc + total_proof_usd) as total_venta, max(payment_method.name) as metodo_pago')
            ->join('INNER JOIN', 'payment_method_has_invoice', "payment_method_has_invoice.invoice_id = invoice.id")
            ->join('INNER JOIN', 'payment_method', "payment_method_has_invoice.payment_method_id = payment_method.id")
            //->join('INNER JOIN', 'seller_has_invoice', "seller_has_invoice.invoice_id = invoice.id")
            //->where(['box_id' => $box_id, 'seller_has_invoice.seller_id' => $seller_id])
            ->where(['box_id' => $box_id])
            ->andWhere(['>=', 'emission_date', $fecha_inicio])
            ->andWhere(['<=', 'emission_date', $fecha_fin])
            ->groupBy('payment_method.name')
            ->orderBy('metodo_pago ASC')
            ->asArray()
            ->all();
        return $ventas;
    }

  public function getDesgloseImpuesto()
  {
    $items = ItemInvoice::findAll(['invoice_id' => $this->id]);
    $nodoDesgloseImpuesto = [];

    if ($this->currency_id === Currency::getCurrencyIdByCode('USD'))    
        $strmoneda = 'DOLARES';    
    else    
        $strmoneda = 'COLONES';    

    // Agrupar impuestos por código y tarifa
    $impuestosAgrupados = [];
    foreach ($items as $desglose) {        
      $clave = $desglose->taxType->code . '|' . $desglose->taxRateType->code;

      if (!isset($impuestosAgrupados[$clave])) {
        $impuestosAgrupados[$clave] = [
          'codigo' => $desglose->taxType->code,
          'tarifa' => $desglose->taxRateType->code,
          'total' => 0
        ];
      }
      $montoImpuesto = $desglose->getMontoImpuesto($strmoneda);

      $impuestosAgrupados[$clave]['total'] += $montoImpuesto;
    }

    // Crear nodos agrupados
    foreach ($impuestosAgrupados as $impuesto) {      
      $nodoDesgloseImpuesto [] = [
        'Codigo' => $impuesto['codigo'],
        'CodigoTarifaIVA' => $impuesto['tarifa'],
        'TotalMontoImpuesto'=> number_format($impuesto['total'], 5, '.', '')
      ];      
    }
    return $nodoDesgloseImpuesto;
  }
}
