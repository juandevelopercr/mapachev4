<?php

namespace backend\models\business;

use backend\models\BaseModel;
use backend\models\nomenclators\Boxes;
use backend\models\nomenclators\BranchOffice;
use backend\models\nomenclators\ConditionSale;
use backend\models\nomenclators\CreditDays;
use backend\models\nomenclators\Currency;
use backend\models\nomenclators\PaymentMethod;
use backend\models\nomenclators\RouteTransport;
use backend\models\nomenclators\UtilsConstants;
use backend\models\settings\Issuer;
use backend\models\settings\Setting;
use common\components\ApiV43\ApiFirmadoHacienda;
use common\components\ApiV43\ApiXML;
use common\models\GlobalFunctions;
use common\models\User;
use Yii;
use Da\QrCode\QrCode;
use kartik\mpdf\Pdf;
use yii\helpers\FileHelper;
use yii\helpers\Html;
use yii\helpers\StringHelper;
use yii\helpers\Url;

/**
 * This is the model class for table "credit_note".
 *
 * @property int $id
 * @property int|null $branch_office_id
 * @property int|null $customer_id
 * @property int|null $condition_sale_id
 * @property int|null $credit_days_id
 * @property int|null $currency_id
 * @property int|null $credit_note_type
 * @property string|null $key
 * @property string|null $consecutive
 * @property string|null $emission_date
 * @property float|null $change_type
 * @property string|null $pay_date fecha en la que se cancela la factura por un abono
 * @property string|null $observations
 * @property int|null $status_account_receivable_id
 * @property string|null $response_xml
 * @property int|null $contingency
 * @property int|null $correct_credit_note 1 Si corrige una factura
 * @property int|null $correct_credit_note_id id de la factura que corrige
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
 * @property CreditNote $correctCreditNote
 * @property CreditNote[] $credit_notes
 * @property User $seller
 * @property User $collector
 * @property ItemCreditNote[] $itemCreditNotes
 * @property PaymentMethodHasCreditNote[] $paymentMethodHasCreditNotes
 * @property PaymentMethod[] $paymentMethods
 * @property RouteTransport $routeTransport

 */
class CreditNote extends BaseModel
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

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'credit_note';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['emission_date', 'consecutive', 'branch_office_id', 'customer_id', 'condition_sale_id', 'currency_id', 'status', 'change_type', 'payment_methods', 'credit_note_type'], 'required'],
            [['branch_office_id', 'customer_id', 'condition_sale_id', 'credit_days_id', 'currency_id', 'status_account_receivable_id', 'contingency', 'correct_credit_note', 'correct_credit_note_id', 
              'erased_by_note', 'num_request_hacienda_set', 'num_request_hacienda_get', 'status', 'credit_note_type', 'status_hacienda',  
              'route_transport_id', 'ready_to_send_email', 'email_sent', 'box_id'], 'integer'],
            [['emission_date', 'pay_date', 'reference_emission_date', 'created_at', 'updated_at', 'payment_methods', 'sellers', 'collectors'], 'safe'],
            [['change_type', 'total_tax', 'total_discount', 'total_exonerado', 'total_comprobante'], 'number'],
            [['observations'], 'string'],
            [['contract', 'confirmation_number'], 'string', 'max' => 255],
            [['key', 'consecutive', 'response_xml', 'reference_number', 'reference_code', 'reference_reason', 'access_token'], 'string', 'max' => 255],
            [['branch_office_id'], 'exist', 'skipOnError' => true, 'targetClass' => BranchOffice::className(), 'targetAttribute' => ['branch_office_id' => 'id']],
            [['condition_sale_id'], 'exist', 'skipOnError' => true, 'targetClass' => ConditionSale::className(), 'targetAttribute' => ['condition_sale_id' => 'id']],
            [['credit_days_id'], 'exist', 'skipOnError' => true, 'targetClass' => CreditDays::className(), 'targetAttribute' => ['credit_days_id' => 'id']],
            [['currency_id'], 'exist', 'skipOnError' => true, 'targetClass' => Currency::className(), 'targetAttribute' => ['currency_id' => 'id']],
            [['customer_id'], 'exist', 'skipOnError' => true, 'targetClass' => Customer::className(), 'targetAttribute' => ['customer_id' => 'id']],
            [['correct_credit_note_id'], 'exist', 'skipOnError' => true, 'targetClass' => CreditNote::className(), 'targetAttribute' => ['correct_credit_note_id' => 'id']],
            [['route_transport_id'], 'exist', 'skipOnError' => true, 'targetClass' => RouteTransport::className(), 'targetAttribute' => ['route_transport_id' => 'id']],
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
            'credit_note_type' => Yii::t('backend', 'Tipo'),
            'key' => Yii::t('backend', 'Clave'),
            'consecutive' => Yii::t('backend', 'Consecutivo'),
            'emission_date' => Yii::t('backend', 'Fecha de emisión'),
            'change_type' => Yii::t('backend', 'Tipo de cambio'),
            'pay_date' => Yii::t('backend', 'Fecha de pago'),
            'observations' => Yii::t('backend', 'Observaciones'),
            'status_account_receivable_id' => Yii::t('backend', 'Estado de cuenta por cobrar'),
            'response_xml' => Yii::t('backend', 'Respuesta XML'),
            'contingency' => Yii::t('backend', 'Contingencia'),
            'correct_credit_note' => Yii::t('backend', 'Corrige factura'),
            'correct_credit_note_id' => Yii::t('backend', 'Factura que corrige'),
            'reference_number' => Yii::t('backend', 'Número de referencia'),
            'sellers' => Yii::t('backend', 'Agente Vendedor'),
            'collectors' => Yii::t('backend', 'Agente Cobrador'),
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
            'status' => Yii::t('backend', 'Estado'),
            'status_hacienda' => Yii::t('backend', 'Estado de hacienda'),
            'created_at' => Yii::t('backend', 'Fecha de creación'),
            'updated_at' => Yii::t('backend', 'Fecha de actualización'),
            'payment_methods' => Yii::t('backend', 'Medios de pagos (hasta 4)'),
            'route_transport_id' => Yii::t('backend', 'Ruta de transporte'),
            'ready_to_update_stock' => Yii::t('backend', 'Lista para aplicar'),
            'ready_to_send_email' => Yii::t('backend', 'Lista para enviar'),
            'email_sent' => Yii::t('backend', 'Email enviado'),
            'box_id'=> 'Caja',
            'contract'=> 'Contrato',
            'confirmation_number'=>'Número de confirmación',
        ];
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
    public function getBox()
    {
        return $this->hasOne(Boxes::className(), ['id' => 'box_id']);
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
    public function getCorrectCreditNote()
    {
        return $this->hasOne(CreditNote::className(), ['id' => 'correct_credit_note_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreditNotes()
    {
        return $this->hasMany(CreditNote::className(), ['correct_credit_note_id' => 'id']);
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
    public function getItemCreditNotes()
    {
        return $this->hasMany(ItemCreditNote::className(), ['credit_note_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPaymentMethodHasCreditNotes()
    {
        return $this->hasMany(PaymentMethodHasCreditNote::className(), ['credit_note_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPaymentMethods()
    {
        return $this->hasMany(PaymentMethod::className(), ['id' => 'payment_method_id'])->viaTable('payment_method_has_credit_note', ['credit_note_id' => 'id']);
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
        return "/credit_note";
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
    public function generateConsecutive()
    {
        $issuer = Issuer::find()->one();
        $initconsecutive = $issuer->init_consecutive_credit_note;

        //Sucursal
        $a_number = str_pad($this->branchOffice->code, 3, '0', STR_PAD_LEFT);

        // Caja
        $b_number =  str_pad($this->box->numero, 5, '0', STR_PAD_LEFT);

        $c_number = '03'; //Nota de credito

        $identificacion = $a_number . $b_number . $c_number;

        $connection = \Yii::$app->db;
        $sql = "SELECT MAX(SUBSTRING(consecutive, 11, 10)) AS consecutive FROM credit_note where SUBSTRING(consecutive, 1, 10) = '" . $identificacion . "'";
        $data = $connection->createCommand($sql);
        $consecutive = $data->queryOne();
        if ((is_null($consecutive) || empty($consecutive) || $consecutive == 0) && $initconsecutive > 0)
            $code = $initconsecutive;
        else
            $code = (isset($consecutive)) ? (int) $consecutive['consecutive'] + 1 : 1;

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

        $this->setResumenFactura();

        if (!is_null($this->reference_emission_date) && !empty($this->reference_emission_date)) {
            $this->reference_emission_date = date("Y-m-d", strtotime($this->reference_emission_date));
        }
    }

    public function beforeSave($insert)
    {
        //$resume = self::getResumeCreditNote($this->id);
        //$total_price = $resume->subtotal + $resume->tax_amount - $resume->discount_amount - $resume->exonerate_amount;
       
        $this->total_tax = $this->totalImpuesto;
        $this->total_discount = $this->totalDescuentos;
        $this->total_exonerado = $this->totalExonerado;

        if (is_null($this->access_token) || empty($this->access_token))
            $this->access_token = Yii::$app->security->generateRandomString() . '_' . time();

        if (parent::beforeSave($insert)) {
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
        $factura_detalles = ItemCreditNote::findAll(['credit_note_id' => $this->id]);

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
        return ItemCreditNote::find()->where(['credit_note_id' => $id])->sum('price_total');
    }

    public static function getResumeCreditNote($credit_note_id)
    {
        $resume = ItemCreditNote::find()
            ->select([
                'SUM(subtotal) AS subtotal',
                'SUM(tax_amount) AS tax_amount',
                'SUM(discount_amount) AS discount_amount',
                'SUM(exonerate_amount) AS exonerate_amount',
                'SUM(price_total) + SUM(tax_amount) AS price_total',
            ])
            ->where(['credit_note_id' => $credit_note_id])
            ->one();

        return $resume;
    }

    /**
     * @param $email
     */
/**
     * @param $email
     */
    public function sendEmail($subject, $email, $email_cc, $body)
    {
        //$subject = Yii::t('backend', 'Factura electrónica #' . $this->consecutive);
        //$email_cc = UtilsConstants::getListaEmailsByEmailString($this->customer->email_cc);
        $emisor = Issuer::find()->one();
        $logo = "<img src=\"" . GlobalFunctions::BASE_URL. Setting::getUrlLogoBySettingAndType(2, Setting::SETTING_ID) . "\" width=\"165\"/>";        

        $mailer = Yii::$app->mail->compose(['html' => 'notification-credit-note-html'], [
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
        $nombrearchivo = 'NC-' . $this->key . '.pdf';
        $archivo = $this->getInvoicePdf($this->id, true, 'COLONES', $destino = 'file', $nombrearchivo);        
        if (!empty($archivo)) {
            $mailer->attach($archivo, ['fileName' => $nombrearchivo]);
        }

        // Adjuntar XML del SISTEMA
        $invoice = CreditNote::find()->where(['id' => $this->id])->one();
        $items_invoice = ItemCreditNote::find()->where(['credit_note_id' => $this->id])->all();

        $apiXML = new ApiXML();
        $issuer = Issuer::find()->one();
        $xml = $apiXML->genXMLNC($issuer, $invoice, $items_invoice);

        $p12Url = $issuer->getFilePath();
        $pinP12 = $issuer->certificate_pin;

        $doc_type = '01'; // Factura
        $apiFirma = new ApiFirmadoHacienda();
        $xmlFirmado = $apiFirma->firmar($p12Url, $pinP12, $xml, $doc_type);

        $xml = base64_decode($xmlFirmado);

        $nombre_archivo = 'NC-' . $invoice->key . '.xml';
        // create attachment on-the-fly
        $mailer->attachContent($xml, ['fileName' => $nombre_archivo, 'contentType' => 'text/plain']);


        // Adjuntar XML de respuesta de Hacienda si existe
        $url_xml_hacienda_verificar = Yii::getAlias('@backend/web/uploads/xmlh/NC-MH-' . $invoice->key . '.xml');
        $nombre_archivo = 'NC-MH' . $invoice->key . '.xml';
        if (file_exists($url_xml_hacienda_verificar))
            $mailer->attach($url_xml_hacienda_verificar, ['fileName' => $nombre_archivo]);

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

    public function getInvoicePdf($invoice_id, $original, $moneda = 'COLONES', $destino = 'browser', $filename = 'Factura')
    {
        $logo = "<img src=\"" . GlobalFunctions::BASE_URL. Setting::getUrlLogoBySettingAndType(2, Setting::SETTING_ID) . "\" width=\"165\"/>";        
        $configuracion = Setting::find()->where(['id' => 1])->one();
        $textCuentas = $configuracion->bank_information;
        $invoice = CreditNote::find()->where(['id' => $invoice_id])->one();
        $data = '';

        $qr_code_invoice = $invoice->generateQrCode();
        $img_qr = '<img src="' . $qr_code_invoice . '" width="100"/>';

        $items_invoice = ItemCreditNote::find()->where(['credit_note_id' => $invoice->id])->all();

        $data = \Yii::$app->view->renderFile(Yii::getAlias('@common').'/mail/NC-html.php', [
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
                    'title' => 'Nota de Crédito',
                    'defaultheaderline' => 0,
                    //'default_font' => 'Calibri',
                    'setAutoTopMargin' => 'stretch',
                    //'showWatermarkText' => $invoice->IsCanceled(),
                ],
                'methods' => [
                    'SetTitle' => Yii::t('backend', 'Nota de Crédito'),
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
                    'title' => 'Nota de Crédito',
                    'defaultheaderline' => 0,
                    //'default_font' => 'Calibri',
                    'setAutoTopMargin' => 'stretch',
                    //'showWatermarkText' => $invoice->IsCanceled(),
                ],
                'methods' => [
                    'SetTitle' => Yii::t('backend', 'Nota de Crédito'),
                    'SetWatermarkText' => 'ANULADO',
                    'SetSubject' => 'Generating PDF files via yii2-mpdf extension has never been easy',
                    'SetFooter' => ['|' . Yii::t('backend', 'Página') . ' {PAGENO}|'],
                ],
            ]);
            $pdf->render();

            return $file_pdf_save;
        }
    }

    /**
     * @param $credit_note_id
     */
    public function verifyStock()
    {
        $unit_type = '';
        $items = ItemCreditNote::find()->where(['credit_note_id' => $this->id])->all();
        foreach ($items as $key => $item) {
            if (isset($item->product_id)) {
                $product = Product::findOne($item->product_id);
                $current_stock = ProductHasBranchOffice::getQuantity($item->product_id, $this->id);
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
                    CreditNote::sendAlertStock($current_stock, $item->quantity, $request_quantity_total, $product, $this, $unit_type);
                }
            }
        }
    }

    /**
     * @param $email
     */
    public static function sendAlertStock($current_stock, $request_quantity, $request_quantity_total, $product, $credit_note, $unit_type = '')
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

            $mailer = Yii::$app->mail->compose(['html' => 'alert_credit_note_stock-html'], ['current_stock' => $current_stock, 'request_quantity' => $request_quantity, 'request_quantity_total' => $request_quantity_total, 'credit_note' => $credit_note, 'product' => $product, 'unit_type' => $unit_type])
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
        $resume = self::getResumeCreditNote($this->id);
        $change_type = (isset($this->change_type) && $this->change_type > 0) ? $this->change_type : 1;

        $total = $resume->price_total;

        return $total ?? 0;
    }

    /**
     * @param $credit_note_id
     * @param $new_status
     */
    public static function setStatusHacienda($credit_note_id, $new_status)
    {
        $credit_note = self::findOne($credit_note_id);
        $credit_note->status_hacienda = $new_status;
        $credit_note->save(false);
    }

    /**
     * Process to generate QR code
     * @param boolean $is_backend
     * @return string
     */
    public function generateQrCode()
    {
        if (!file_exists("uploads/credit_note_qr/") || !is_dir("uploads/credit_note_qr/")) {
            try {
                FileHelper::createDirectory("uploads/credit_note_qr/", 0777);
            } catch (\Exception $exception) {
                Yii::info("Error handling credit_note_qr folder resources");
            }
        }
        $name_qrcode = $this->key;
        $url = GlobalFunctions::BASE_URL.'/hacienda/show-comprobante?key=' . $name_qrcode;

        $qrCode = (new QrCode($url))->setSize(250)->setMargin(20);

        $path = Yii::$app->basePath . '/web/uploads/credit_note_qr/';

        $qrCode->writeFile($path . $name_qrcode . '.png');

        $file = GlobalFunctions::BASE_URL. Url::to('@web/uploads/credit_note_qr/' . $name_qrcode . '.png');

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
            $credit_note = self::find()->where(['key' => $key])->one();

            if ($credit_note !== null) {
                if (!file_exists("uploads/xmlh/") || !is_dir("uploads/xmlh/")) {
                    try {
                        FileHelper::createDirectory("uploads/xmlh/", 0777);
                    } catch (\Exception $exception) {
                        Yii::info("Error handling xmlh folder resources");
                    }
                }

                if ($status_hacienda == 'rechazado') {
                    self::setStatusHacienda($credit_note->id, UtilsConstants::HACIENDA_STATUS_REJECTED); // Rechazada

                    $xml_filename = 'NC-MH-' . $credit_note->key . '.xml';
                    $path = Yii::getAlias('@backend/web/uploads/xmlh/' . $xml_filename);
                    file_put_contents($path, $xml_response_hacienda_decode);
                    $credit_note->response_xml = $path;
                } elseif ($status_hacienda == 'aceptado') {
                    self::setStatusHacienda($credit_note->id, UtilsConstants::HACIENDA_STATUS_ACCEPTED); // Aceptada

                    // Verifico si el estado ya estaba en aceptada entonces no enviar email ni rebajar de inventario
                    //if ($credit_note->status_hacienda != UtilsConstants::HACIENDA_STATUS_ACCEPTED)
                    if ($credit_note->status_hacienda == UtilsConstants::HACIENDA_STATUS_RECEIVED)  
                    {
                        $xml_filename = 'NC-MH-' . $credit_note->key . '.xml';
                        $path = Yii::getAlias('@backend/web/uploads/xmlh/' . $xml_filename);
                        file_put_contents($path, $xml_response_hacienda_decode);
                        $credit_note->response_xml = $xml_filename;

                        //devolver todos los items a inventario
                        $procesar = true;
                        $all_items = ItemCreditNote::find()->where(['credit_note_id' => $credit_note->id])->all();
                        foreach ($all_items as $idx => $model) {
                            if (isset($model->product_id) && !empty($model->product_id)) {
                                $observations = 'Devolución por aceptación en Hacienda de la Nota de crédito #' . $model->creditNote->key;
                                $adjustment_type = UtilsConstants::ADJUSTMENT_TYPE_CREDIT_NOTE;

                                // Chequear si ya se ha realizado ese ajuste
                                $adjustment = Adjustment::find()->where(['product_id'=>$model->product_id, 'key'=> $credit_note->key, 'type'=>$adjustment_type, 
                                                'origin_branch_office_id'=>$credit_note->branch_office_id, 'observations'=>$observations])->one();
                                if (is_null($adjustment)){
                                    // Se calcula la cantidad a extraer según la unidad de medidad del producto
                                    $quantity = Product::getUnitQuantityByItem($model->product_id, $model->quantity, $model->unit_type_id);
                                    
                                    Product::returnToInventory($model->product_id, $adjustment_type, $model->creditNote->branch_office_id, $quantity, $model->credit_note_id, false, $observations, $credit_note->key);                        
                                }
                                else
                                    $procesar = true;
                            }
                        }
                        $nota = CreditNote::find()->where(['id'=>$credit_note->id])->one();
                        // Adicionar un abono a la factura
                        $comentario = 'Abono por nota de crédito de devolución: '.$nota->consecutive;  
                        $invoice = Invoice::find()->where(['key'=>$nota->reference_number])->one();

                        if ($nota->reference_code == '01') {
                            $invoice->status_hacienda = UtilsConstants::HACIENDA_STATUS_CREDIT_NOTE;
                        } else {
                            $invoice->status_hacienda = UtilsConstants::HACIENDA_STATUS_CREDIT_NOTE_PARTIAL;
                        }
                        $invoice->save();
                        if ($procesar == true)
                            InvoiceAbonos::addAbono($invoice, $nota->total_comprobante, $comentario);
                    }

                } elseif ($status_hacienda == 'recibido') {
                    self::setStatusHacienda($credit_note->id, UtilsConstants::HACIENDA_STATUS_RECEIVED); // Recibida
                }

                $credit_note->save(false);
            }
        }
    }
}
