<?php

namespace backend\models\nomenclators;

use Yii;
use yii\helpers\Html;
use common\models\GlobalFunctions;
use backend\models\settings\Issuer;
use backend\models\business\Invoice;
use backend\models\business\Product;
use backend\models\business\Service;
use backend\models\settings\Setting;
use common\components\ApiV43\ApiXML;
use backend\models\business\Documents;
use backend\models\business\Adjustment;
use backend\models\business\CreditNote;
use common\components\ApiV43\ApiAccess;
use backend\models\business\ItemInvoice;
use backend\models\business\ItemCreditNote;
use common\components\ApiV43\ApiEnvioHacienda;
use common\components\ApiV43\ApiFirmadoHacienda;
use common\components\ApiV43\ApiConsultaHacienda;

class UtilsConstants
{
    const CUSTOMER_ASSIGN_PRICE_DETAIL = 1;
    const CUSTOMER_ASSIGN_PRICE_CUSTOM = 2;
    const CUSTOMER_ASSIGN_PRICE_1 = 3;
    const CUSTOMER_ASSIGN_PRICE_2 = 4;
    const CUSTOMER_ASSIGN_PRICE_3 = 5;
    const CUSTOMER_ASSIGN_PRICE_4 = 6;

    const INVOICE_TYPE_CREDIT = 1;
    const INVOICE_TYPE_COUNTED = 2;

    const ADJUSTMENT_TYPE_ADJUSTMENT = 1;
    const ADJUSTMENT_TYPE_TRANFER = 2;
    const ADJUSTMENT_TYPE_DECREASE = 3;
    const ADJUSTMENT_TYPE_ENTRY = 4;
    const ADJUSTMENT_TYPE_INVOICE_SALES = 5;
    const ADJUSTMENT_TYPE_CREDIT_NOTE = 6;
    const ADJUSTMENT_TYPE_DEBIT_NOTE = 7;

    const PAYMENT_ORDER_STATUS_TO_APPROVAL = 1;
    const PAYMENT_ORDER_STATUS_APPROVED = 2;
    const PAYMENT_ORDER_STATUS_PARTIAL = 3;
    const PAYMENT_ORDER_STATUS_TOTAL = 4;
    const PAYMENT_ORDER_STATUS_ANULATE = 5;

    const PAYOUT_STATUS_PENDING = 1;
    const PAYOUT_STATUS_BLOCKED = 2;
    const PAYOUT_STATUS_CANCEL = 3;

    const PROFORMA_STATUS_STARTED = 1;
    const PROFORMA_STATUS_SENT = 2;
    const PROFORMA_STATUS_APPROVED = 3;
    const PROFORMA_STATUS_ANULATE = 4;

    const DIGITAL_INVOICE_STATUS_STARTED = 1;
    const DIGITAL_INVOICE_STATUS_SENT = 2;
    const DIGITAL_INVOICE_STATUS_APPROVED = 3;
    const DIGITAL_INVOICE_STATUS_ANULATE = 4;

    const DELIVERY_TIMES_DAYS = 1;
    const DELIVERY_TIMES_WEEK = 2;
    const DELIVERY_TIMES_MONTH = 3;

    const PDF_ORIGINAL_COLON_TYPE = 1;
    const PDF_COPY_COLON_TYPE = 2;
    const PDF_ORIGINAL_DOLLAR_TYPE = 3;
    const PDF_COPY_DOLLAR_TYPE = 4;

    const SEND_MAIL_RESPONSE_TYPE_SUCCESS = 1;
    const SEND_MAIL_RESPONSE_TYPE_ERROR = 2;
    const SEND_MAIL_RESPONSE_TYPE_EXCEPTION = 3;
    const SEND_MAIL_RESPONSE_TYPE_CUSTOM = 4;
    const SEND_MAIL_RESPONSE_TYPE_EMPTY_EMAIL = 5;

    const PURCHASE_ORDER_STATUS_STARTED = 1;
    //const PURCHASE_ORDER_STATUS_PROCESS = 2;
    const PURCHASE_ORDER_STATUS_FINISHED = 3;
    //const PURCHASE_ORDER_STATUS_ANULATE = 4;

    const PRE_INVOICE_TYPE_INVOICE = 1;
    const PRE_INVOICE_TYPE_TICKET  = 2;
    const PRE_INVOICE_TYPE_DIGITAL = 3;

    const HACIENDA_STATUS_NOT_SENT = 1;
    const HACIENDA_STATUS_RECEIVED = 2;
    const HACIENDA_STATUS_ACCEPTED = 3;
    const HACIENDA_STATUS_REJECTED = 4;
    const HACIENDA_STATUS_CREDIT_NOTE = 5;
    const HACIENDA_STATUS_DEBIT_NOTE = 6;
    const HACIENDA_STATUS_CANCELLED = 7;
    const HACIENDA_STATUS_PENDING = 8;
    const HACIENDA_STATUS_ANULATE = 9;
    const HACIENDA_STATUS_CREDIT_NOTE_PARTIAL = 10;
    const HACIENDA_STATUS_DEBIT_NOTE_PARTIAL = 11;

    const HACIENDA_STATUS_ACEPTADO_RECEPTOR = 2;
    const HACIENDA_STATUS_ACEPTADO_PARCIAL_RECEPTOR = 3;
    const HACIENDA_STATUS_RECHAZADO_RECEPTOR = 4;
    const HACIENDA_STATUS_ACEPTADO_HACIENDA = 5;
    const HACIENDA_STATUS_ACEPTADO_PARCIAL_HACIENDA = 6;
    const HACIENDA_STATUS_RECHAZADO_HACIENDA = 7;
    const HACIENDA_STATUS_RECIBIDO_HACIENDA = 8;
    const HACIENDA_STATUS_RECIBIDO_PARCIAL_HACIENDA = 9;
    const HACIENDA_STATUS_RECIBIDO_RECHAZADO_HACIENDA = 10;


    const INVOICE_STATUS_PENDING = 1;
    const INVOICE_STATUS_CANCELLED = 2;

    const CREDIT_NOTE_TYPE_TOTAL = 1;
    const CREDIT_NOTE_TYPE_PARTIAL = 2;

    const DEBIT_NOTE_TYPE_TOTAL = 1;
    const DEBIT_NOTE_TYPE_PARTIAL = 2;

    const ACCOUNT_RECEIVABLE_PENDING = 1;
    const ACCOUNT_RECEIVABLE_CANCELLED = 2;

    const DEVOLUCION_TOTAL = 1;
    const DEVOLUCION_PARCIAL = 2;

    const TYPE_DOCUMENT_GASTO = 1;
    const TYPE_DOCUMENT_COMPRA = 2;

    const ACCOUNT_PAYABLE_PENDING = 1;
    const ACCOUNT_PAYABLE_CANCELLED = 2;

    /**
     * @param $array
     * @param $value
     * @param $optional_value
     * @return null|string
     */
    public static function getValueOfArray($array, $value, $optional_value)
    {
        if ($value !== null) {
            return (isset($array[$value]) && !empty($array[$value])) ? $array[$value] : null;
        } else {
            if ($optional_value)
                return null;
            else
                return $array;
        }
    }

    /**
     * Tipo de precio a aplicar a los clientes
     *
     * @param null|integer $value
     * @param boolean $optional_value Poner este valor en true cuando se quiere mostrar en los index el valor específico pero este es opcional, evita dar error y devuelve null
     * @return array|mixed
     */
    public static function getCustomerAsssignPriceSelectType($value = null, $optional_value = false)
    {
        $array = [];

        $array[self::CUSTOMER_ASSIGN_PRICE_DETAIL] = Yii::t('backend', 'Precio detalle');
        $array[self::CUSTOMER_ASSIGN_PRICE_CUSTOM] = Yii::t('backend', 'Precio personalizado');
        $array[self::CUSTOMER_ASSIGN_PRICE_1] = Yii::t('backend', 'Precio 1');
        $array[self::CUSTOMER_ASSIGN_PRICE_2] = Yii::t('backend', 'Precio 2');
        $array[self::CUSTOMER_ASSIGN_PRICE_3] = Yii::t('backend', 'Precio 3');
        $array[self::CUSTOMER_ASSIGN_PRICE_4] = Yii::t('backend', 'Precio 4');

        return self::getValueOfArray($array, $value, $optional_value);
    }

    /**
     * Tipo de precio en modo reducido
     *
     * @param null|integer $value
     * @param boolean $optional_value Poner este valor en true cuando se quiere mostrar en los index el valor específico pero este es opcional, evita dar error y devuelve null
     * @return array|mixed
     */
    public static function getPriceTypeMiniLabel($value = null, $optional_value = false)
    {
        $array = [];

        $array[self::CUSTOMER_ASSIGN_PRICE_DETAIL] = 'PD';
        $array[self::CUSTOMER_ASSIGN_PRICE_CUSTOM] = 'PP';
        $array[self::CUSTOMER_ASSIGN_PRICE_1] = 'P1';
        $array[self::CUSTOMER_ASSIGN_PRICE_2] = 'P2';
        $array[self::CUSTOMER_ASSIGN_PRICE_3] = 'P3';
        $array[self::CUSTOMER_ASSIGN_PRICE_4] = 'P4';

        return self::getValueOfArray($array, $value, $optional_value);
    }

    /**
     * Tipos de facturas
     *
     * @param null|integer $value
     * @param boolean $optional_value Poner este valor en true cuando se quiere mostrar en los index el valor específico pero este es opcional, evita dar error y devuelve null
     * @return array|mixed
     */
    public static function getInvoiceType($value = null, $optional_value = false)
    {
        $array = [];

        $array[self::INVOICE_TYPE_CREDIT] = Yii::t('backend', 'Crédito');
        $array[self::INVOICE_TYPE_COUNTED] = Yii::t('backend', 'Contado');

        return self::getValueOfArray($array, $value, $optional_value);
    }

    /**
     * Tipos de ajustes
     *
     * @param null|integer $value
     * @param boolean $optional_value Poner este valor en true cuando se quiere mostrar en los index el valor específico pero este es opcional, evita dar error y devuelve null
     * @return array|mixed
     */
    public static function getAdjustmentType($value = null, $optional_value = false, $to_index = false)
    {
        $array = [];

        if ($to_index) {
            $array[self::ADJUSTMENT_TYPE_ADJUSTMENT] = Yii::t('backend', 'Ajustes');
            $array[self::ADJUSTMENT_TYPE_TRANFER] = Yii::t('backend', 'Traslados');
            $array[self::ADJUSTMENT_TYPE_DECREASE] = Yii::t('backend', 'Mermas');
            $array[self::ADJUSTMENT_TYPE_ENTRY] = Yii::t('backend', 'Entradas');
            $array[self::ADJUSTMENT_TYPE_INVOICE_SALES] = Yii::t('backend', 'Salidas');
            $array[self::ADJUSTMENT_TYPE_CREDIT_NOTE] = Yii::t('backend', 'Nota de Crédito');            
        } else {
            $array[self::ADJUSTMENT_TYPE_ADJUSTMENT] = Yii::t('backend', 'Ajuste');
            $array[self::ADJUSTMENT_TYPE_TRANFER] = Yii::t('backend', 'Traslado');
            $array[self::ADJUSTMENT_TYPE_DECREASE] = Yii::t('backend', 'Merma / Destrucción');
            $array[self::ADJUSTMENT_TYPE_ENTRY] = Yii::t('backend', 'Entrada');
            $array[self::ADJUSTMENT_TYPE_INVOICE_SALES] = Yii::t('backend', 'Salida');
            $array[self::ADJUSTMENT_TYPE_CREDIT_NOTE] = Yii::t('backend', 'Nota de Crédito');            
        }

        return self::getValueOfArray($array, $value, $optional_value);
    }

    /**
     * Tipos de redirect desde ajustes
     *
     * @param null|integer $value
     * @param boolean $optional_value Poner este valor en true cuando se quiere mostrar en los index el valor específico pero este es opcional, evita dar error y devuelve null
     * @return array|mixed
     */
    public static function getRedirectAdjustmentType($type)
    {
        $value = (int) $type;
        if ($value === self::ADJUSTMENT_TYPE_ADJUSTMENT) {
            return 'index';
        } elseif ($value === self::ADJUSTMENT_TYPE_TRANFER) {
            return 'index_transfer';
        } elseif ($value === self::ADJUSTMENT_TYPE_DECREASE) {
            return 'index_decrease';
        } elseif ($value === self::ADJUSTMENT_TYPE_INVOICE_SALES) {
            return 'index_output_invoice';
        } else {
            return 'index';
        }
    }

    /**
     * Estados de órdenes de compra
     *
     * @param null|integer $value
     * @param boolean $optional_value Poner este valor en true cuando se quiere mostrar en los index el valor específico pero este es opcional, evita dar error y devuelve null
     * @return array|mixed
     */
    public static function getStatusPaymentOrderSelectMap($value = null, $optional_value = false, $show_colors = false)
    {
        $array = [];

        //$array[self::PAYMENT_ORDER_STATUS_APPROVED] = Yii::t('backend', 'Aprobada');

        $array[self::PAYMENT_ORDER_STATUS_TO_APPROVAL] = Yii::t('backend', 'Por aprobar');
        $array[self::PAYMENT_ORDER_STATUS_APPROVED] = Yii::t('backend', 'Aprobada');
        $array[self::PAYMENT_ORDER_STATUS_PARTIAL] = Yii::t('backend', 'Parcial');
        $array[self::PAYMENT_ORDER_STATUS_TOTAL] = Yii::t('backend', 'Total');
        $array[self::PAYMENT_ORDER_STATUS_ANULATE] = Yii::t('backend', 'Anulada');

        if (!$show_colors) {
            return self::getValueOfArray($array, $value, $optional_value);
        } else {
            $result = self::getValueOfArray($array, $value, $optional_value);

            if ($value === self::PAYMENT_ORDER_STATUS_TO_APPROVAL) {
                return '<span class="badge bg-red">' . $result . '</span>';
            } elseif ($value === self::PAYMENT_ORDER_STATUS_APPROVED) {
                return '<span class="badge bg-orange">' . $result . '</span>';
            } elseif ($value === self::PAYMENT_ORDER_STATUS_PARTIAL) {
                return '<span class="badge bg-custom-yellow">' . $result . '</span>';
            } elseif ($value === self::PAYMENT_ORDER_STATUS_TOTAL) {
                return '<span class="badge bg-green">' . $result . '</span>';
            } elseif ($value === self::PAYMENT_ORDER_STATUS_ANULATE) {
                return '<span class="badge bg-black">' . $result . '</span>';
            } else {
                return '<span class="badge bg-gray">' . $result . '</span>';
            }
        }
    }

    /**
     * Estados de pago
     *
     * @param null|integer $value
     * @param boolean $optional_value Poner este valor en true cuando se quiere mostrar en los index el valor específico pero este es opcional, evita dar error y devuelve null
     * @return array|mixed
     */
    public static function getPayoutStatusSelectType($value = null, $optional_value = false)
    {
        $array = [];

        $array[self::PAYOUT_STATUS_PENDING] = Yii::t('backend', 'Pendiente');
        $array[self::PAYOUT_STATUS_BLOCKED] = Yii::t('backend', 'Bloqueada');
        $array[self::PAYOUT_STATUS_CANCEL] = Yii::t('backend', 'Cancelada');

        return self::getValueOfArray($array, $value, $optional_value);
    }

    /**
     * Codigos alfabeticos para sectores
     *
     * @param null|integer $value
     * @param boolean $optional_value Poner este valor en true cuando se quiere mostrar en los index el valor específico pero este es opcional, evita dar error y devuelve null
     * @return array|mixed
     */
    public static function getAlphabetCodesSelectMap($value = null, $optional_value = false)
    {
        $array = [
            'A' => 'A',
            'B' => 'B',
            'C' => 'C',
            'D' => 'D',
            'E' => 'E',
            'F' => 'F',
            'G' => 'G',
            'H' => 'H',
            'I' => 'I',
            'J' => 'J',
            'K' => 'K',
            'L' => 'L',
            'M' => 'M',
            'N' => 'N',
            'O' => 'O',
            'P' => 'P',
            'Q' => 'Q',
            'R' => 'R',
            'S' => 'S',
            'T' => 'T',
            'U' => 'U',
            'V' => 'V',
            'W' => 'W',
            'X' => 'X',
            'Y' => 'Y',
            'Z' => 'Z',
            Product::CODE_DEVOLUTIONS => Product::CODE_DEVOLUTIONS,
        ];

        return self::getValueOfArray($array, $value, $optional_value);
    }

    /**
     * Estados de proformas
     *
     * @param null|integer $value
     * @param boolean $optional_value Poner este valor en true cuando se quiere mostrar en los index el valor específico pero este es opcional, evita dar error y devuelve null
     * @return array|mixed
     */
    public static function getProformaStatusSelectType($value = null, $optional_value = false)
    {
        $array = [];

        $array[self::PROFORMA_STATUS_STARTED] = Yii::t('backend', 'Iniciada');
        $array[self::PROFORMA_STATUS_SENT] = Yii::t('backend', 'Enviada');
        $array[self::PROFORMA_STATUS_APPROVED] = Yii::t('backend', 'Aprobada');
        $array[self::PROFORMA_STATUS_ANULATE] = Yii::t('backend', 'Anulada');

        return self::getValueOfArray($array, $value, $optional_value);
    }

    /**
     * Estados de facturas digitales
     *
     * @param null|integer $value
     * @param boolean $optional_value Poner este valor en true cuando se quiere mostrar en los index el valor específico pero este es opcional, evita dar error y devuelve null
     * @return array|mixed
     */
    public static function getDigitalInvoiceStatusSelectType($value = null, $optional_value = false)
    {
        $array = [];

        $array[self::DIGITAL_INVOICE_STATUS_STARTED] = Yii::t('backend', 'Iniciada');
        $array[self::DIGITAL_INVOICE_STATUS_SENT] = Yii::t('backend', 'Enviada');
        $array[self::DIGITAL_INVOICE_STATUS_APPROVED] = Yii::t('backend', 'Aprobada');
        $array[self::DIGITAL_INVOICE_STATUS_ANULATE] = Yii::t('backend', 'Anulada');

        return self::getValueOfArray($array, $value, $optional_value);
    }

    /**
     * Tipos de tiempos de entrega
     *
     * @param null|integer $value
     * @param boolean $optional_value Poner este valor en true cuando se quiere mostrar en los index el valor específico pero este es opcional, evita dar error y devuelve null
     * @return array|mixed
     */
    public static function getDeliveryTimesSelectType($value = null, $optional_value = false)
    {
        $array = [];

        $array[self::DELIVERY_TIMES_DAYS] = Yii::t('backend', 'Días');
        $array[self::DELIVERY_TIMES_WEEK] = Yii::t('backend', 'Semanas');
        $array[self::DELIVERY_TIMES_MONTH] = Yii::t('backend', 'Meses');

        return self::getValueOfArray($array, $value, $optional_value);
    }

    /**
     * Estados de órdenes de pedido
     *
     * @param null|integer $value
     * @param boolean $optional_value Poner este valor en true cuando se quiere mostrar en los index el valor específico pero este es opcional, evita dar error y devuelve null
     * @return array|mixed
     */
    public static function getPurchaseOrderStatusSelectType($value = null, $optional_value = false)
    {
        $array = [];

        $array[self::PURCHASE_ORDER_STATUS_STARTED] = Yii::t('backend', 'Iniciada');
        //$array[self::PURCHASE_ORDER_STATUS_PROCESS] = Yii::t('backend', 'Proceso');
        $array[self::PURCHASE_ORDER_STATUS_FINISHED] = Yii::t('backend', 'Finalizada');
        //$array[self::PURCHASE_ORDER_STATUS_ANULATE] = Yii::t('backend', 'Anulada');

        return self::getValueOfArray($array, $value, $optional_value);
    }

    /**
     * Tipos de pre-factura
     *
     * @param null|integer $value
     * @param boolean $optional_value Poner este valor en true cuando se quiere mostrar en los index el valor específico pero este es opcional, evita dar error y devuelve null
     * @return array|mixed
     */
    public static function getPreInvoiceSelectType($value = null, $optional_value = false)
    {
        $array = [];
        $array[self::PRE_INVOICE_TYPE_INVOICE] = Yii::t('backend', 'FACTURA');
        $array[self::PRE_INVOICE_TYPE_TICKET] = Yii::t('backend', 'TIQUETE');


        return self::getValueOfArray($array, $value, $optional_value);
    }

    /**
     * Tipo de precio a aplicar a los productos
     *
     * @param null|integer $value
     * @param boolean $optional_value Poner este valor en true cuando se quiere mostrar en los index el valor específico pero este es opcional, evita dar error y devuelve null
     * @return array|mixed
     */
    public static function getPriceTypeSelectByProduct($value = null, $optional_value = false, $product_service = null)
    {
        $array = [];

        if ($product_service !== null) {
            $explode = explode('-', $product_service);
            if ($explode[0] === 'P') {
                $product_model = Product::findOne($explode[1]);

                $array[self::CUSTOMER_ASSIGN_PRICE_1] = Yii::t('backend', 'Precio 1') . ' - ¢ ' . GlobalFunctions::formatNumber($product_model->getPriceByType(self::CUSTOMER_ASSIGN_PRICE_1), 2);
                $array[self::CUSTOMER_ASSIGN_PRICE_2] = Yii::t('backend', 'Precio 2') . ' - ¢ ' . GlobalFunctions::formatNumber($product_model->getPriceByType(self::CUSTOMER_ASSIGN_PRICE_2), 2);
                $array[self::CUSTOMER_ASSIGN_PRICE_3] = Yii::t('backend', 'Precio 3') . ' - ¢ ' . GlobalFunctions::formatNumber($product_model->getPriceByType(self::CUSTOMER_ASSIGN_PRICE_3), 2);
                $array[self::CUSTOMER_ASSIGN_PRICE_4] = Yii::t('backend', 'Precio 4') . ' - ¢ ' . GlobalFunctions::formatNumber($product_model->getPriceByType(self::CUSTOMER_ASSIGN_PRICE_4), 2);
                $array[self::CUSTOMER_ASSIGN_PRICE_DETAIL] = Yii::t('backend', 'Precio detalle') . ' - ¢ ' . GlobalFunctions::formatNumber($product_model->getPriceByType(self::CUSTOMER_ASSIGN_PRICE_DETAIL), 2);
                $array[self::CUSTOMER_ASSIGN_PRICE_CUSTOM] = Yii::t('backend', 'Precio personalizado') . ' - ¢ ' . GlobalFunctions::formatNumber($product_model->getPriceByType(self::CUSTOMER_ASSIGN_PRICE_CUSTOM), 2);
            } else if ($explode[0] === 'S') {
                $service_model = Service::findOne($explode[1]);
                $array[self::CUSTOMER_ASSIGN_PRICE_1] = Yii::t('backend', 'Precio') . ' - ¢ ' . GlobalFunctions::formatNumber($service_model->price, 2);
            }
        } else {
            $array[self::CUSTOMER_ASSIGN_PRICE_1] = Yii::t('backend', 'Precio 1');
            $array[self::CUSTOMER_ASSIGN_PRICE_2] = Yii::t('backend', 'Precio 2');
            $array[self::CUSTOMER_ASSIGN_PRICE_3] = Yii::t('backend', 'Precio 3');
            $array[self::CUSTOMER_ASSIGN_PRICE_4] = Yii::t('backend', 'Precio 4');
            $array[self::CUSTOMER_ASSIGN_PRICE_DETAIL] = Yii::t('backend', 'Precio detalle');
            $array[self::CUSTOMER_ASSIGN_PRICE_CUSTOM] = Yii::t('backend', 'Precio personalizado');
        }

        return self::getValueOfArray($array, $value, $optional_value);
    }

    /**
     * Estados de hacienda
     *
     * @param null|integer $value
     * @param boolean $optional_value Poner este valor en true cuando se quiere mostrar en los index el valor específico pero este es opcional, evita dar error y devuelve null
     * @return array|mixed
     */
    public static function getHaciendaStatusSelectType($value = null, $optional_value = false)
    {
        $array = [];

        $array[self::HACIENDA_STATUS_NOT_SENT] = Yii::t('backend', 'No enviada');
        $array[self::HACIENDA_STATUS_RECEIVED] = Yii::t('backend', 'Recibida');
        $array[self::HACIENDA_STATUS_ACCEPTED] = Yii::t('backend', 'Aceptada');
        $array[self::HACIENDA_STATUS_REJECTED] = Yii::t('backend', 'Rechazada');
        $array[self::HACIENDA_STATUS_CREDIT_NOTE] = Yii::t('backend', 'Nota de crédito');
        $array[self::HACIENDA_STATUS_CREDIT_NOTE_PARTIAL] = Yii::t('backend', 'Nota de Crédito Parcial');
        $array[self::HACIENDA_STATUS_DEBIT_NOTE] = Yii::t('backend', 'Nota de débito');
        $array[self::HACIENDA_STATUS_CANCELLED] = Yii::t('backend', 'Cancelada');
        $array[self::HACIENDA_STATUS_PENDING] = Yii::t('backend', 'Pendiente');
        $array[self::HACIENDA_STATUS_ANULATE] = Yii::t('backend', 'Anulada');

        return self::getValueOfArray($array, $value, $optional_value);
    }

    public static function getStatusName($estado_id)
    {
        $result = '';
        switch ($estado_id) {
            case self::HACIENDA_STATUS_NOT_SENT:
                $result = Yii::t('backend', 'No enviada');
                break;
            case self::HACIENDA_STATUS_RECEIVED:
                $result = Yii::t('backend', 'Recibida');
                break;
            case self::HACIENDA_STATUS_ACCEPTED:
                $result = Yii::t('backend', 'Aceptada');
                break;
            case self::HACIENDA_STATUS_REJECTED:
                $result = Yii::t('backend', 'Rechazada');
                break;
            case self::HACIENDA_STATUS_CREDIT_NOTE:
                $result = Yii::t('backend', 'Nota de crédito');
                break;
            case self::HACIENDA_STATUS_CREDIT_NOTE_PARTIAL:
                $result = Yii::t('backend', 'Nota de Crédito Parcial');
                break;
            case self::HACIENDA_STATUS_DEBIT_NOTE:
                $result = Yii::t('backend', 'Nota de débito');
                break;
            case self::HACIENDA_STATUS_CANCELLED:
                $result = Yii::t('backend', 'Cancelada');
                break;
            case self::HACIENDA_STATUS_PENDING:
                $result = Yii::t('backend', 'Pendiente');
                break;
            case self::HACIENDA_STATUS_ANULATE:
                $result = Yii::t('backend', 'Anulada');
                break;
        }
        return $result;
    }

    /**
     * Estados de facturas
     *
     * @param null|integer $value
     * @param boolean $optional_value Poner este valor en true cuando se quiere mostrar en los index el valor específico pero este es opcional, evita dar error y devuelve null
     * @return array|mixed
     */
    public static function getInvoiceStatusSelectType($value = null, $optional_value = false)
    {
        $array = [];

        $array[self::INVOICE_STATUS_PENDING] = Yii::t('backend', 'Pendiente');
        $array[self::INVOICE_STATUS_CANCELLED] = Yii::t('backend', 'Cancelada');

        return self::getValueOfArray($array, $value, $optional_value);
    }

    /**
     * Estados de cuenta por cobrar
     *
     * @param null|integer $value
     * @param boolean $optional_value Poner este valor en true cuando se quiere mostrar en los index el valor específico pero este es opcional, evita dar error y devuelve null
     * @return array|mixed
     */
    public static function getCuentaCobrarStatus($value = null, $optional_value = false)
    {
        $array = [];
        $array[self::ACCOUNT_RECEIVABLE_PENDING] = Yii::t('backend', 'PENDIENTE');
        $array[self::ACCOUNT_RECEIVABLE_CANCELLED] = Yii::t('backend', 'CANCELADA');

        return self::getValueOfArray($array, $value, $optional_value);
    }

    /**
     * Tipos de notas de crédito
     *
     * @param null|integer $value
     * @param boolean $optional_value Poner este valor en true cuando se quiere mostrar en los index el valor específico pero este es opcional, evita dar error y devuelve null
     * @return array|mixed
     */
    public static function getCreditNoteType($value = null, $optional_value = false)
    {
        $array = [];

        $array[self::CREDIT_NOTE_TYPE_TOTAL] = Yii::t('backend', 'Total');
        $array[self::CREDIT_NOTE_TYPE_PARTIAL] = Yii::t('backend', 'Parcial');

        return self::getValueOfArray($array, $value, $optional_value);
    }

    /**
     * Estados de notas de credito
     *
     * @param null|integer $value
     * @param boolean $optional_value Poner este valor en true cuando se quiere mostrar en los index el valor específico pero este es opcional, evita dar error y devuelve null
     * @return array|mixed
     */
    public static function getCreditNoteStatusSelectType($value = null, $optional_value = false)
    {
        $array = [];

        $array[self::INVOICE_STATUS_PENDING] = Yii::t('backend', 'Pendiente');
        $array[self::INVOICE_STATUS_CANCELLED] = Yii::t('backend', 'Cancelada');

        return self::getValueOfArray($array, $value, $optional_value);
    }

    /**
     * Tipos de notas de debito
     *
     * @param null|integer $value
     * @param boolean $optional_value Poner este valor en true cuando se quiere mostrar en los index el valor específico pero este es opcional, evita dar error y devuelve null
     * @return array|mixed
     */
    public static function getDebitNoteType($value = null, $optional_value = false)
    {
        $array = [];

        $array[self::DEBIT_NOTE_TYPE_TOTAL] = Yii::t('backend', 'Total');
        $array[self::DEBIT_NOTE_TYPE_PARTIAL] = Yii::t('backend', 'Parcial');

        return self::getValueOfArray($array, $value, $optional_value);
    }

    /**
     * Estados de notas de credito
     *
     * @param null|integer $value
     * @param boolean $optional_value Poner este valor en true cuando se quiere mostrar en los index el valor específico pero este es opcional, evita dar error y devuelve null
     * @return array|mixed
     */
    public static function getDebitNoteStatusSelectType($value = null, $optional_value = false)
    {
        $array = [];

        $array[self::INVOICE_STATUS_PENDING] = Yii::t('backend', 'Pendiente');
        $array[self::INVOICE_STATUS_CANCELLED] = Yii::t('backend', 'Cancelada');

        return self::getValueOfArray($array, $value, $optional_value);
    }

    /**
     * Estados de facturas
     *
     * @param null|integer $value
     * @param boolean $optional_value Poner este valor en true cuando se quiere mostrar en los index el valor específico pero este es opcional, evita dar error y devuelve null
     * @return array|mixed
     */
    public static function getDocumentStatusSelectType($value = null, $optional_value = false)
    {
        $array = [];
        $array[self::HACIENDA_STATUS_ACEPTADO_RECEPTOR] = Yii::t('backend', 'Aceptado Receptor');
        $array[self::HACIENDA_STATUS_ACEPTADO_PARCIAL_RECEPTOR] = Yii::t('backend', 'Aceptado Parcial Receptor');
        $array[self::HACIENDA_STATUS_RECHAZADO_RECEPTOR] = Yii::t('backend', 'Rechazado Receptor');
        $array[self::HACIENDA_STATUS_ACEPTADO_HACIENDA] = Yii::t('backend', 'Aceptado Hacienda');
        $array[self::HACIENDA_STATUS_ACEPTADO_PARCIAL_HACIENDA] = Yii::t('backend', 'Haceptado Parcial Hacienda');
        $array[self::HACIENDA_STATUS_RECHAZADO_HACIENDA] = Yii::t('backend', 'Rechazado Hacienda');
        $array[self::HACIENDA_STATUS_RECIBIDO_HACIENDA] = Yii::t('backend', 'Recibido Hacienda');
        $array[self::HACIENDA_STATUS_RECIBIDO_PARCIAL_HACIENDA] = Yii::t('backend', 'Recibido Parcial Hacienda');
        $array[self::HACIENDA_STATUS_RECIBIDO_RECHAZADO_HACIENDA] = Yii::t('backend', 'Rechazado Hacienda');
        return self::getValueOfArray($array, $value, $optional_value);
    }

    public static function getDocumentStatusOnlyAceptadoSelectType($value = null, $optional_value = false)
    {
        $array = [];
        $array[self::HACIENDA_STATUS_ACEPTADO_RECEPTOR] = Yii::t('backend', 'Aceptado Receptor');
        //$array[self::HACIENDA_STATUS_ACEPTADO_PARCIAL_RECEPTOR] = Yii::t('backend', 'Aceptado Parcial Receptor');
        return self::getValueOfArray($array, $value, $optional_value);
    }

    public static function getListaEmailsByEmailString($emails)
    {
        $lista = [];
        if (strlen(trim($emails)) > 0) {
            $arr_cc = explode(';', $emails);
        } else {
            $arr_cc = array();
        }

        foreach ($arr_cc as $ccs) {
            $ccs = trim($ccs);
            if (filter_var($ccs, FILTER_VALIDATE_EMAIL)) {
                $lista[] = $ccs;
            }
        }
        return $lista;
    }

    /**
     * Tipos de documentos de gastos
     *
     * @param null|integer $value
     * @param boolean $optional_value Poner este valor en true cuando se quiere mostrar en los index el valor específico pero este es opcional, evita dar error y devuelve null
     * @return array|mixed
     */
    public static function getDocumentType($value = null, $optional_value = false)
    {
        $array = [];

        $array['FE'] = Yii::t('backend', 'Factura Electrónica');
        $array['ND'] = Yii::t('backend', 'Nota de Débito');
        $array['NC'] = Yii::t('backend', 'Nota de Crédito');
        $array['TE'] = Yii::t('backend', 'Tiquete electrónico');
        $array['MR'] = Yii::t('backend', 'Mensaje de receptor');
        $array['FEC'] = Yii::t('backend', 'Factura electrónica de compra');
        $array['FEE'] = Yii::t('backend', 'Factura electrónica de exportación');

        return self::getValueOfArray($array, $value, $optional_value);
    }

    /**
     * Estados de cuentas por pagar
     *
     * @param null|integer $value
     * @param boolean $optional_value Poner este valor en true cuando se quiere mostrar en los index el valor específico pero este es opcional, evita dar error y devuelve null
     * @return array|mixed
     */
    public static function getStatusAccountsPayable($value = null, $optional_value = false)
    {
        $array = [];

        $array[self::ACCOUNT_PAYABLE_PENDING] = Yii::t('backend', 'Pendiente');
        $array[self::ACCOUNT_PAYABLE_CANCELLED] = Yii::t('backend', 'Cancelada');

        return self::getValueOfArray($array, $value, $optional_value);
    }

    public static function getAnnosWithData()
    {
        $connection = \Yii::$app->db;
        $data = Invoice::find()->select('DISTINCT EXTRACT(YEAR FROM emission_date) AS anno')->asArray()->orderBy('anno ASC')->all();

        $listaannos = [];
        foreach ($data as $d)
            $listaannos[$d['anno']] = $d['anno'];
        return $listaannos;
    }

    public static function getDatosFacturacion($anno, $mes, $moneda_id)
    {
        $data = Invoice::find()->select('SUM(total_comprobante) AS "total_with_iva", SUM(total_tax) AS "total_iva", SUM(total_discount) AS "total_discount"')
            ->where(['EXTRACT(YEAR FROM emission_date)' => $anno, 'EXTRACT(MONTH FROM emission_date)' => $mes])
            ->andWhere(['currency_id' => $moneda_id, 'status_hacienda' => UtilsConstants::HACIENDA_STATUS_ACCEPTED])
            ->asArray()
            ->one();

        return $data;
    }

    public static function getMes($nummes)
    {
        $mes = '';
        switch ($nummes) {
            case 1:
                $mes = 'Enero';
                break;
            case 2:
                $mes = 'Febrero';
                break;
            case 3:
                $mes = 'Marzo';
                break;
            case 4:
                $mes = 'Abril';
                break;
            case 5:
                $mes = 'Mayo';
                break;
            case 6:
                $mes = 'Junio';
                break;
            case 7:
                $mes = 'Julio';
                break;
            case 8:
                $mes = 'Agosto';
                break;
            case 9:
                $mes = 'Septiembre';
                break;
            case 10:
                $mes = 'Octubre';
                break;
            case 11:
                $mes = 'Noviembre';
                break;
            case 12:
                $mes = 'Diciembre';
                break;
        }
        return $mes;
    }

    public static function getDatosFacturasMeses($anno, $currency_id)
    {
        $estado = self::HACIENDA_STATUS_ACCEPTED;
        $sql = "SELECT
                    (SELECT SUM(total_comprobante) FROM invoice  
                    WHERE status_hacienda = " . $estado . " AND EXTRACT(YEAR FROM emission_date) = " . $anno . " AND EXTRACT(MONTH FROM emission_date) = '01' AND 
                        currency_id  = " . $currency_id . ") AS total_ene,

                    (SELECT SUM(total_comprobante) FROM invoice  
                    WHERE status_hacienda = " . $estado . " AND EXTRACT(YEAR FROM emission_date) = " . $anno . " AND EXTRACT(MONTH FROM emission_date) = '02' AND 
                        currency_id  = " . $currency_id . ") AS total_feb,        
                        
                    (SELECT SUM(total_comprobante) FROM invoice  
                    WHERE status_hacienda = " . $estado . " AND EXTRACT(YEAR FROM emission_date) = " . $anno . " AND EXTRACT(MONTH FROM emission_date) = '03' AND 
                        currency_id  = " . $currency_id . ") AS total_mar,                         
        
                    (SELECT SUM(total_comprobante) FROM invoice  
                    WHERE status_hacienda = " . $estado . " AND EXTRACT(YEAR FROM emission_date) = " . $anno . " AND EXTRACT(MONTH FROM emission_date) = '04' AND 
                        currency_id  = " . $currency_id . ") AS total_abr,  

                    (SELECT SUM(total_comprobante) FROM invoice  
                    WHERE status_hacienda = " . $estado . " AND EXTRACT(YEAR FROM emission_date) = " . $anno . " AND EXTRACT(MONTH FROM emission_date) = '05' AND 
                        currency_id  = " . $currency_id . ") AS total_may, 

                    (SELECT SUM(total_comprobante) FROM invoice  
                    WHERE status_hacienda = " . $estado . " AND EXTRACT(YEAR FROM emission_date) = " . $anno . " AND EXTRACT(MONTH FROM emission_date) = '06' AND 
                        currency_id  = " . $currency_id . ") AS total_jun, 

                    (SELECT SUM(total_comprobante) FROM invoice  
                    WHERE status_hacienda = " . $estado . " AND EXTRACT(YEAR FROM emission_date) = " . $anno . " AND EXTRACT(MONTH FROM emission_date) = '07' AND 
                        currency_id  = " . $currency_id . ") AS total_jul, 

                    (SELECT SUM(total_comprobante) FROM invoice  
                    WHERE status_hacienda = " . $estado . " AND EXTRACT(YEAR FROM emission_date) = " . $anno . " AND EXTRACT(MONTH FROM emission_date) = '08' AND 
                        currency_id  = " . $currency_id . ") AS total_ago, 

                    (SELECT SUM(total_comprobante) FROM invoice  
                    WHERE status_hacienda = " . $estado . " AND EXTRACT(YEAR FROM emission_date) = " . $anno . " AND EXTRACT(MONTH FROM emission_date) = '09' AND 
                        currency_id  = " . $currency_id . ") AS total_sep,

                    (SELECT SUM(total_comprobante) FROM invoice  
                    WHERE status_hacienda = " . $estado . " AND EXTRACT(YEAR FROM emission_date) = " . $anno . " AND EXTRACT(MONTH FROM emission_date) = '10' AND 
                        currency_id  = " . $currency_id . ") AS total_oct,

                    (SELECT SUM(total_comprobante) FROM invoice  
                    WHERE status_hacienda = " . $estado . " AND EXTRACT(YEAR FROM emission_date) = " . $anno . " AND EXTRACT(MONTH FROM emission_date) = '11' AND 
                        currency_id  = " . $currency_id . ") AS total_nov,

                    (SELECT SUM(total_comprobante) FROM invoice  
                    WHERE status_hacienda = " . $estado . " AND EXTRACT(YEAR FROM emission_date) = " . $anno . " AND EXTRACT(MONTH FROM emission_date) = '12' AND 
                        currency_id  = " . $currency_id . ") AS total_dic";


        $connection = \Yii::$app->db;
        $query = $connection->createCommand($sql);
        $datos = $query->queryAll();
        $data = $datos[0];

        return [
            'data' => $data
        ];
    }

    static function getVentasRutasAndMes($anno, $mes, $moneda_id)
    {
        $sql = "SELECT sum(coalesce(invoice.total_comprobante, 0)) as total_comprobante, MAX(route_transport.name) as ruta
                FROM route_transport
                LEFT JOIN invoice ON route_transport.id = invoice.route_transport_id AND 
                    invoice.status_hacienda = " . UtilsConstants::HACIENDA_STATUS_ACCEPTED . " AND 
                    EXTRACT(YEAR FROM emission_date) = '" . $anno . "' AND EXTRACT(MONTH FROM emission_date) = '" . $mes . "'
                WHERE invoice.total_comprobante > 0	AND currency_id = $moneda_id
                GROUP BY route_transport.id
                ORDER BY ruta ASC";

        $connection = \Yii::$app->db;
        $query = $connection->createCommand($sql);
        $datos = $query->queryAll();

        $ventas = [];

        foreach ($datos as $d) {
            $ventas[] = ['label' => $d['ruta'], 'value' => $d['total_comprobante']];
        }
        return ['ventas' => $ventas];
    }

    static function getVentasByVendedor($anno, $mes, $moneda_id)
    {
        $sql = "SELECT sum(coalesce(invoice.total_comprobante, 0)) as total_comprobante, CONCAT_WS (' ' , u.name , u.last_name) as vendedor
        FROM user as u
        INNER JOIN seller_has_invoice ON seller_has_invoice.seller_id = u.id
        INNER JOIN invoice ON seller_has_invoice.invoice_id = invoice.id AND
             EXTRACT(YEAR FROM emission_date) = '" . $anno . "' AND EXTRACT(MONTH FROM emission_date) = '" . $mes . "'
        WHERE invoice.total_comprobante > 0	 AND invoice.status_hacienda = " . UtilsConstants::HACIENDA_STATUS_ACCEPTED . " AND currency_id = $moneda_id
        GROUP BY u.id
        ORDER BY vendedor ASC";

        $connection = \Yii::$app->db;
        $query = $connection->createCommand($sql);
        $datos = $query->queryAll();

        // inicializar
        $ventas = [];

        foreach ($datos as $d) {
            $ventas[] = ['label' => $d['vendedor'], 'value' => $d['total_comprobante']];
        }
        usort($ventas, 'self::comparar');

        return [
            'ventas' => $ventas,
        ];
    }

    // Para la llamada al usor y ordenar
    static function comparar($x, $y)
    {
        if ($x['value'] == $y['value'])
            return 0;
        else if ($x['value'] < $y['value'])
            return -1;
        else
            return 1;
    }

    public static function sendInvoicesToHacienda()
    {
        $apiAccess = NULL;
        $facturas_no_enviadas = array();
        $logueado = false;
        $fecha_actual = date('Y-m-d H:i:s');

        /*
        $invoices = Invoice::find()->where(['status_hacienda' => UtilsConstants::HACIENDA_STATUS_NOT_SENT])
            ->andWhere(['<=', 'num_request_hacienda_set', 3])
            ->andwhere("emission_date + interval '5 minutes' < '" . $fecha_actual . "' ")
            ->orderBy('emission_date ASC')
            ->limit(100)->all();
            */

        $invoices = Invoice::find()
            ->where(['status_hacienda' => UtilsConstants::HACIENDA_STATUS_NOT_SENT])
            //->andWhere(['<=', 'num_request_hacienda_set', 3])
            ->andWhere(['<=', new \yii\db\Expression('COALESCE(num_request_hacienda_set, 0)'), 3])
            ->andWhere("DATE_ADD(emission_date, INTERVAL 5 MINUTE) < '" . $fecha_actual . "'")
            ->orderBy(['emission_date' => SORT_ASC])
            ->limit(100)
            ->all();            
            
        //https://herbavicr.com/v1/cron/send-invoice                    

        foreach ($invoices as $invoice) {
            $issuer = Issuer::find()->one();
            $datos = self::validaDatosFactura($invoice);


            $error = $datos['error'];
            $proceder = true;
            
            if ($error == 0 && $proceder == true) {               
                if (is_null($apiAccess)) {
                    // Si todas las validaciones son correctas, proceder al proceso
                    // Logearse en la api y obtener el token;
                    $apiAccess = new ApiAccess();
                    $datos = $apiAccess->loginHacienda($issuer);
                    $error = $datos['error'];
                    $tiempo_token = date('Y-m-d H:i:s');
                    $logueado = true;
                }
                $segundos_transcurridos = strtotime(date('Y-m-d H:i:s')) -  strtotime($tiempo_token);

                // Consultar el tiempo de expiración del token
                if ($segundos_transcurridos >= $apiAccess->expires_in) {
                    // Refresacar el token
                    $data = $apiAccess->refreshToken($issuer);
                    if ($data['error'] == 1) {
                        exit;
                    } else {
                        $tiempo_token = date('Y-m-d H:i:s');
                    }
                }

                $items_invoice = ItemInvoice::find()->where(['invoice_id' => $invoice->id])->all();

                // Obtener el xml firmado electrónicamente
                $apiXML = new ApiXML();
                $xml = $apiXML->genXMLFe($issuer, $invoice, $items_invoice);

                $p12Url = $issuer->getFilePath();
                $pinP12 = $issuer->certificate_pin;

                $doc_type = '01'; // Invoice
                $apiFirma = new ApiFirmadoHacienda();
                $xmlFirmado = $apiFirma->firmar($p12Url, $pinP12, $xml, $doc_type);

                // Enviar documento a hacienda
                $apiEnvioHacienda = new ApiEnvioHacienda();
                $datos = $apiEnvioHacienda->send($xmlFirmado, $apiAccess->token, $invoice, $issuer, $doc_type);
                // En $datos queda el mensaje de respuesta	

                $respuesta = $datos['response'];

                $code = $respuesta->getHeaders()->get('http-code');
                if ($code == '202' || $code == '201') {
                    $invoice->status_hacienda = UtilsConstants::HACIENDA_STATUS_RECEIVED; // Recibido
                    $invoice->save();
                } else
				if ($code == '400') {
                    $error = 1;
                    $mensaje = utf8_encode($respuesta->getHeaders()->get('X-Error-Cause'));

                    if (strpos($mensaje, "ya fue recibido anteriormente") == true)  // Si devuelve verdadero
                    {
                        $invoice->status_hacienda = UtilsConstants::HACIENDA_STATUS_RECEIVED; // Recibido
                        $invoice->save();
                    }
                } else {
                    $invoice->num_request_hacienda_set++;
                    $invoice->save();

                    if ($invoice->num_request_hacienda_set == 3) // 
                    {
                        // Enviar notificación	
                        $facturas_no_enviadas[] = $invoice;
                    }
                    $error = 1;
                }
            }
        }
        if ($logueado == true)
            $apiAccess->CloseSesion($apiAccess->token, $issuer);

        // Enviar notificación de facturas no enviadas
        if (!empty($facturas_no_enviadas)) {
            self::sendnotificacionesemail($facturas_no_enviadas);
        }
    }

    public static function getStatusInvoiceInHacienda()
    {
        $apiAccess = NULL;
        $logueado = false;
        $fecha_actual = date('Y-m-d H:i:s');

        $invoices = Invoice::find()->where(['status_hacienda' => UtilsConstants::HACIENDA_STATUS_RECEIVED])
            //->andWhere(['<=', 'num_request_hacienda_set', 3])
            ->andWhere(['<=', new \yii\db\Expression('COALESCE(num_request_hacienda_set, 0)'), 3])
            ->andWhere("DATE_ADD(emission_date, INTERVAL 5 MINUTE) < '" . $fecha_actual . "'")
            //->andwhere("(emission_date + INTERVAL '60 MINUTES') < '" . $fecha_actual . "' ")
            ->orderBy('emission_date ASC')
            ->limit(100)->all();                                     

        foreach ($invoices as $invoice) {
            $issuer = Issuer::find()->one();
            $datos = self::validaDatosFactura($invoice);

            $error = $datos['error'];
            $proceder = true;
            if ($error == 0 && $proceder == true) {
                if (is_null($apiAccess)) {
                    // Si todas las validaciones son correctas, proceder al proceso
                    // Logearse en la api y obtener el token;
                    $apiAccess = new ApiAccess();
                    $datos = $apiAccess->loginHacienda($issuer);
                    $error = $datos['error'];
                    $tiempo_token = date('Y-m-d H:i:s');
                    $logueado = true;
                }
                $segundos_transcurridos = strtotime(date('Y-m-d H:i:s')) -  strtotime($tiempo_token);

                // Consultar el tiempo de expiración del token
                if ($segundos_transcurridos >= $apiAccess->expires_in) {
                    // Refresacar el token
                    $data = $apiAccess->refreshToken($issuer);
                    if ($data['error'] == 1) {
                        exit;
                    } else {
                        $tiempo_token = date('Y-m-d H:i:s');
                    }
                }

                // consultar estado de documento en hacienda
                $apiConsultaHacienda = new ApiConsultaHacienda();
                $tipoDocumento = '01'; // Factura
                $datos = $apiConsultaHacienda->getEstado($invoice, $issuer, $apiAccess->token, $tipoDocumento);

                $estado = $datos['estado'];

                if ($estado == 'aceptado') {
                    // Extraer del inventario                     
                    Invoice::setStatusHacienda($invoice->id, UtilsConstants::HACIENDA_STATUS_ACCEPTED); // Aceptada
                    //if ($invoice->status_hacienda != UtilsConstants::HACIENDA_STATUS_ACCEPTED)
                    if ($invoice->status_hacienda == UtilsConstants::HACIENDA_STATUS_RECEIVED)                    
                    {
                        // Extraer del inventario     
                        $enviar = true;                                                
                        //if ($invoice->invoice_type == UtilsConstants::PRE_INVOICE_TYPE_INVOICE && $enviar == true)
                        //{
                        // Enviar documentos por emails
                        $subject = Yii::t('backend', 'Factura electrónica #' . $invoice->consecutive);
                        $email = $invoice->customer->email;
                        $email_cc = UtilsConstants::getListaEmailsByEmailString($invoice->customer->email_cc);                        
                        $email_cc[] = $issuer->email;
                        $body = '';
                        $invoice->sendEmail($subject, $email, $email_cc, $body);
                        //}
                    }                    
                } 
                else
                if ($estado == 'rechazado') {

                }
            }
        }
        if ($logueado == true)
            $apiAccess->CloseSesion($apiAccess->token, $issuer);
    }


    public function sendnotificacionesemail($facturas_no_enviadas)
    {
        if (!empty($facturas_no_enviadas)) {
            foreach ($facturas_no_enviadas as $invoice) {
                $subject = Yii::t('backend', 'Factura electrónica no enviada a hacienda');
                $email = $invoice->customer->email;
                $email_cc = UtilsConstants::getListaEmailsByEmailString($invoice->customer->email_cc);
                $body = '';
                $emisor = Issuer::find()->one();
                $logo = "<img src=\"" . Setting::getUrlLogoBySettingAndType(2, Setting::SETTING_ID) . "\" width=\"165\"/>";

                $mailer = Yii::$app->mail->compose(['html' => 'notification-invoice-not-send-html'], [
                    'logo' => $logo,
                    'key' => $invoice->key,
                    'emisor' => $emisor->name,
                    'cliente' => $invoice->customer->name,
                    'consecutive' => $invoice->consecutive,
                    'emission_date' => date('d-m-Y', strtotime($invoice->emission_date)),
                    'symbol' => $invoice->currency->symbol,
                    'total' => $invoice->total_comprobante,
                    'body' => $body,
                ])
                    ->setTo($email)
                    ->setCc($email_cc)
                    ->setFrom([Setting::getEmail() => Setting::getName()])
                    ->setSubject($subject);
                //->attach($file_pdf, ['fileName' => 'Factura_' . $this->consecutive]);

                // Adjuntar PDF
                $nombrearchivo = 'FE-' . $invoice->key . '.pdf';
                $archivo = $invoice->getInvoicePdf($invoice->id, true, 'COLONES', $destino = 'file', $nombrearchivo);
                if (!empty($archivo)) {
                    $mailer->attach($archivo, ['fileName' => $nombrearchivo]);
                }

                // Adjuntar XML del SISTEMA
                $invoice = Invoice::find()->where(['id' => $invoice->id])->one();
                $items_invoice = ItemInvoice::find()->where(['invoice_id' => $invoice->id])->all();

                $apiXML = new ApiXML();
                $issuer = Issuer::find()->one();
                $xml = $apiXML->genXMLFe($issuer, $invoice, $items_invoice);

                $p12Url = $issuer->getFilePath();
                $pinP12 = $issuer->certificate_pin;

                $doc_type = '01'; // Factura
                $apiFirma = new ApiFirmadoHacienda();
                $xmlFirmado = $apiFirma->firmar($p12Url, $pinP12, $xml, $doc_type);

                $xml = base64_decode($xmlFirmado);

                $nombre_archivo = 'FE-' . $invoice->key . '.xml';
                // create attachment on-the-fly
                $mailer->attachContent($xml, ['fileName' => $nombre_archivo, 'contentType' => 'text/plain']);

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
    }

    public static function validaDatosFactura($invoice)
    {
        // Valida que los datos de la factura, que tenga detalle y emisor definido
        $error = 0;
        $mensaje = '';
        $type = '';
        $titulo    = '';
        if (is_null($invoice)) {
            $error = 1;
            $mensaje = 'La factura seleccionada no se encuentra en la base de datos';
            $type = 'danger';
            $titulo = "Error <hr class=\"kv-alert-separator\">";
        }

        $items_exists = ItemInvoice::find()->where(['invoice_id' => $invoice->id])->exists();
        if (!$items_exists) {
            $error = 1;
            $mensaje = 'La factura seleccionada no contiene ninguna línea de producto / servicio. Por favor revise la información e inténtelo nuevamente';
            $type = 'warning';
            $titulo = "Advertencia <hr class=\"kv-alert-separator\">";
        }


        $configuracion = Issuer::find()->one();
        if (is_null($configuracion)) {
            $error = 1;
            $mensaje = 'No se ha podido obtener la información del emisor de la factura. Por favor revise los datos e inténtelo nuevamente, si el error persiste, póngase en contacto con el administrador del sistema';
            $type = 'danger';
            $titulo = "Error <hr class=\"kv-alert-separator\">";
        }
        return ['error' => $error, 'mensaje' => $mensaje, 'type' => $type, 'titulo' => $titulo];
    }

    public static function sendDocumentToHacienda()
    {
        $apiAccess = NULL;
        $logueado = false;
        $fecha_actual = date('Y-m-d H:i:s');
        $issuer = Issuer::find()->one();
        $documentos = Documents::find()->where(['receiver_id' => $issuer->id, 'status' => [
            UtilsConstants::HACIENDA_STATUS_ACEPTADO_RECEPTOR,
            UtilsConstants::HACIENDA_STATUS_RECHAZADO_RECEPTOR
        ]])
            ->andWhere(['<=', 'attempts_making_set', 3])
            //->andwhere("reception_date + interval '5 minutes' < '" . $fecha_actual . "' ")
            ->andWhere("DATE_ADD(reception_date, INTERVAL 5 MINUTE) < '" . $fecha_actual . "'")
            ->orderBy('reception_date ASC')
            ->limit(100)->all();

        foreach ($documentos as $documento) {
            $error = 0;
            if (is_null($apiAccess)) {
                // Si todas las validaciones son correctas, proceder al proceso
                // Logearse en la api y obtener el token;
                $apiAccess = new ApiAccess();
                $datos = $apiAccess->loginHacienda($issuer);
                $error = $datos['error'];
                if ($datos['error'] == 1) {
                    $documento->attempts_making_set++;
                    $documento->save();
                    break;
                } else {
                    $tiempo_token = date('Y-m-d H:i:s');
                    $logueado = true;
                }
            }
            if ($error == 0) {
                $segundos_transcurridos = strtotime(date('Y-m-d H:i:s')) -  strtotime($tiempo_token);

                // Consultar el tiempo de expiración del token
                if ($segundos_transcurridos >= $apiAccess->expires_in) {
                    // Refresacar el token
                    $data = $apiAccess->refreshToken($issuer);
                    if ($data['error'] == 1) {
                        exit;
                    } else {
                        $tiempo_token = date('Y-m-d H:i:s');
                    }
                }

                // Obtener el xml firmado electrónicamente
                $apiXML = new ApiXML();
                $xml = $apiXML->genXMLMr($documento, $issuer);

                $p12Url = $issuer->getFilePath();
                $pinP12 = $issuer->certificate_pin;

                $tipoDocumento = '05'; // Mensaje de Receptor
                $apiFirma = new ApiFirmadoHacienda();
                $xmlFirmado = $apiFirma->firmar($p12Url, $pinP12, $xml, $tipoDocumento);

                // Enviar documento a hacienda
                $apiEnvioHacienda = new ApiEnvioHacienda();
                $datos = $apiEnvioHacienda->sendMensaje($xmlFirmado, $apiAccess->token, $documento, $issuer);

                // En $datos queda el mensaje de respuesta	
                $respuesta = $datos['response'];

                $code = $respuesta->getHeaders()->get('http-code');
                if ($code == '202' || $code == '201') {
                    $documento->estado_id = UtilsConstants::HACIENDA_STATUS_RECIBIDO_HACIENDA; // Recibido
                    $documento->save();
                } else
				if ($code == '400') {
                    $error = 1;
                    $mensaje = utf8_encode($respuesta->getHeaders()->get('X-Error-Cause'));

                    if (strpos($mensaje, "ya fue recibido anteriormente") == true)  // Si devuelve verdadero
                    {
                        $documento->estado_id = UtilsConstants::HACIENDA_STATUS_RECIBIDO_HACIENDA; // Recibido
                        $documento->save();
                    }
                } else {
                    if ($documento->attempts_making_set <= 3) {
                        $documento->attempts_making_set++;
                        $documento->save();
                    }
                    if ($documento->attempts_making_set == 3) // 
                    {
                        // Enviar notificación	
                        self::sendnotificacionesDocumentosemail($documento);
                    }
                    $error = 1;
                }
            }
        }
        if ($logueado == true)
            $apiAccess->CloseSesion($apiAccess->token, $issuer);
    }

    public static function getStatusDocumentInHacienda()
    {
        $apiAccess = NULL;
        $logueado = false;
        $fecha_actual = date('Y-m-d H:i:s');
        $issuer = Issuer::find()->one();
        $documentos = Documents::find()->where(['receiver_id' => $issuer->id, 'status' => [
            UtilsConstants::HACIENDA_STATUS_RECIBIDO_HACIENDA,
            UtilsConstants::HACIENDA_STATUS_RECIBIDO_PARCIAL_HACIENDA
        ]])
            ->andWhere(['<=', 'attempts_making_set', 3])
            //->andwhere("(reception_date + INTERVAL '20 MINUTES') < '" . $fecha_actual . "' ")
            ->andWhere("DATE_ADD(reception_date, INTERVAL 5 MINUTE) < '" . $fecha_actual . "'")
            ->orderBy('reception_date ASC')
            ->limit(10)->all();

        foreach ($documentos as $documento) {
            $error = 0;
            if (is_null($apiAccess)) {
                // Si todas las validaciones son correctas, proceder al proceso
                // Logearse en la api y obtener el token;
                $apiAccess = new ApiAccess();
                $datos = $apiAccess->loginHacienda($issuer);
                $error = $datos['error'];
                if ($datos['error'] == 1) {
                    $documento->attempts_making_set++;
                    $documento->save();
                    break;
                } else {
                    $tiempo_token = date('Y-m-d H:i:s');
                    $logueado = true;
                }
            }
            if ($error == 0) {
                $segundos_transcurridos = strtotime(date('Y-m-d H:i:s')) -  strtotime($tiempo_token);

                // Consultar el tiempo de expiración del token
                if ($segundos_transcurridos >= $apiAccess->expires_in) {
                    // Refresacar el token
                    $data = $apiAccess->refreshToken($issuer);
                    if ($data['error'] == 1) {
                        exit;
                    } else {
                        $tiempo_token = date('Y-m-d H:i:s');
                    }
                }

                // consultar estado de documento en hacienda
                $apiConsultaHacienda = new ApiConsultaHacienda();
                $tipoDocumento = '05'; // Mensaje de Receptor
                $datos = $apiConsultaHacienda->getEstado($documento, $issuer, $apiAccess->token, $tipoDocumento);
                // En $datos queda el mensaje de respuesta
            }
        }
        if ($logueado == true)
            $apiAccess->CloseSesion($apiAccess->token, $issuer);
    }



    public function sendnotificacionesDocumentosemail($documento)
    {
        if (!is_null($documento)) {

            $subject = Yii::t('backend', 'Documento mensaje de receptor no enviado a hacienda');
            $issuer = Issuer::find()->one();
            $email = $issuer->email;
            //$email_cc = UtilsConstants::getListaEmailsByEmailString($invoice->customer->email_cc);
            $body = 'Notificación de documento mensaje de receptor no enviado hacienda';
            $emisor = Issuer::find()->one();
            $logo = "<img src=\"" . Setting::getUrlLogoBySettingAndType(2, Setting::SETTING_ID) . "\" width=\"165\"/>";

            $mailer = Yii::$app->mail->compose(['html' => 'notification-document-not-send-html'], [
                'logo' => $logo,
                'key' => $documento->key,
                'emisor' => $emisor->name,
                'cliente' => $documento->transmitter,
                'consecutive' => $documento->consecutive,
                'emission_date' => date('d-m-Y', strtotime($documento->emission_date)),
                'symbol' => $documento->currency,
                'total' => $documento->total_invoice,
                'body' => $body,
            ])
                ->setTo($email)
                //->setCc($email_cc)
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

    /**
     * Updates an existing ItemCreditNote() model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public static function devolucionItemCreditNote($itemCreditNote, $quantity_dev)
    {
        $result = false;
        $quantity_label = $price_type_label = '';

        if ((int)$quantity_dev > 0)
        {
            $request_quantity = $quantity_dev;  

            $itemCreditNote->quantity = $request_quantity;        
            $quantity_label = $price_type_label = '';       

            if (isset($itemCreditNote->product_id) && !empty($itemCreditNote->product_id)) {
                $model_reference = Product::findOne($itemCreditNote->product_id);
                
                if (isset($itemCreditNote->price_type)) {
                    $price_type_label = UtilsConstants::getPriceTypeMiniLabel($itemCreditNote->price_type);
                }

                $itemCreditNote->description = $model_reference->description . ' <b>' . $price_type_label . ' ' . $quantity_label . '</b>';
            }
            if (isset($itemCreditNote->service_id) && !empty($itemCreditNote->service_id)) {
                $model_reference = Service::findOne($itemCreditNote->service_id);
            }

            if ($model_reference !== null) {
                if (is_null($itemCreditNote->discount_amount) || empty($itemCreditNote->discount_amount))
                    $itemCreditNote->discount_amount = 0;
                if (is_null($itemCreditNote->nature_discount) || empty($itemCreditNote->nature_discount))
                    $itemCreditNote->nature_discount = '-';

                $percent_iva = $model_reference->getPercentIvaToApply();

                //$default_price = (isset($model_reference->price1) && !empty($model_reference->price1)) ? $model_reference->price1 : $model_reference->price;
                //$model->price_unit = (isset($model->price_unit) && !empty($model->price_unit)) ? $model->price_unit : $default_price;
                $itemCreditNote->discount_amount = $itemCreditNote->discount_amount * $request_quantity;

                $subtotal = $itemCreditNote->price_unit * $request_quantity - $itemCreditNote->discount_amount;
                $itemCreditNote->subtotal = (isset($subtotal) && !empty($subtotal)) ? $subtotal : 0;                

                $tax_calculate = $subtotal * ($percent_iva / 100);
                $tax = (isset($tax_calculate) && !empty($tax_calculate)) ? $tax_calculate : 0;

                $exonerated = $tax * ($model_reference->exoneration_purchase_percent / 100);
                $exonerated_tax_amount = (isset($exonerated) && !empty($exonerated)) ? $exonerated : 0;
                $itemCreditNote->exonerate_amount = $exonerated_tax_amount;
                $itemCreditNote->exoneration_purchase_percent = (int)$model_reference->exoneration_purchase_percent;
                $itemCreditNote->exoneration_document_type_id = $model_reference->exoneration_document_type_id;
                $itemCreditNote->number_exoneration_doc = $model_reference->number_exoneration_doc;
                $itemCreditNote->name_institution_exoneration = $model_reference->name_institution_exoneration;
                $itemCreditNote->exoneration_date = $model_reference->exoneration_date;

                $itemCreditNote->tax_amount = $tax;
                $itemCreditNote->tax_rate_percent = $model_reference->tax_rate_percent;
                $itemCreditNote->tax_type_id = $model_reference->tax_type_id;
                $itemCreditNote->tax_rate_type_id = $model_reference->tax_rate_type_id;
                $itemCreditNote->price_total = $subtotal + $tax - $exonerated_tax_amount;
            }

            if ($itemCreditNote->save()) {
                //Actualizar los totales de la factura
                $invoice = CreditNote::find()->where(['id'=>$itemCreditNote->credit_note_id])->one();                
                $invoice->save(false);                    
                $result = true;                
            } else {
                $result = false;
            }
        }
        return $result;
    }  

    public static function sendInvoiceToHacienda($id)
    {
        $apiAccess = NULL;
        $facturas_no_enviadas = array();
        $logueado = false;
        $fecha_actual = date('Y-m-d H:i:s');

        $invoice = Invoice::findOne($id);
        $issuer = Issuer::find()->one();
        $datos = self::validaDatosFactura($invoice);

        $error = $datos['error'];
        $proceder = true;
        $result = false;
        
        if ($error == 0 && $proceder == true) {               
            if (is_null($apiAccess)) {
                // Si todas las validaciones son correctas, proceder al proceso
                // Logearse en la api y obtener el token;
                $apiAccess = new ApiAccess();
                $datos = $apiAccess->loginHacienda($issuer);
                $error = $datos['error'];
                $tiempo_token = date('Y-m-d H:i:s');
                $logueado = true;
            }
            $segundos_transcurridos = strtotime(date('Y-m-d H:i:s')) -  strtotime($tiempo_token);

            // Consultar el tiempo de expiración del token
            if ($segundos_transcurridos >= $apiAccess->expires_in) {
                // Refresacar el token
                $data = $apiAccess->refreshToken($issuer);
                if ($data['error'] == 1) {
                    exit;
                } else {
                    $tiempo_token = date('Y-m-d H:i:s');
                }
            }

            $items_invoice = ItemInvoice::find()->where(['invoice_id' => $invoice->id])->all();

            // Obtener el xml firmado electrónicamente
            $apiXML = new ApiXML();
            $xml = $apiXML->genXMLFe($issuer, $invoice, $items_invoice);

            $p12Url = $issuer->getFilePath();
            $pinP12 = $issuer->certificate_pin;

            $doc_type = '01'; // Invoice
            $apiFirma = new ApiFirmadoHacienda();
            $xmlFirmado = $apiFirma->firmar($p12Url, $pinP12, $xml, $doc_type);

            // Enviar documento a hacienda
            $apiEnvioHacienda = new ApiEnvioHacienda();
            $datos = $apiEnvioHacienda->send($xmlFirmado, $apiAccess->token, $invoice, $issuer, $doc_type);
            // En $datos queda el mensaje de respuesta	

            $respuesta = $datos['response'];

            $code = $respuesta->getHeaders()->get('http-code');
            if ($code == '202' || $code == '201') {
                $invoice->status_hacienda = UtilsConstants::HACIENDA_STATUS_RECEIVED; // Recibido
                $result = true;
                $invoice->save();
            } else
            if ($code == '400') {
                $error = 1;
                $mensaje = utf8_encode($respuesta->getHeaders()->get('X-Error-Cause'));

                if (strpos($mensaje, "ya fue recibido anteriormente") == true)  // Si devuelve verdadero
                {
                    $invoice->status_hacienda = UtilsConstants::HACIENDA_STATUS_RECEIVED; // Recibido
                    $invoice->save();
                }
            } else {
                $invoice->num_request_hacienda_set++;
                $invoice->save();                
                $error = 1;
            }
        }
        
        if ($logueado == true)
            $apiAccess->CloseSesion($apiAccess->token, $issuer);

        return $result;
    }
    
    public static function sendCreditNoteToHacienda()
    {
    }

    public static function sendDebitNoteToHacienda()
    {
    }    
}
