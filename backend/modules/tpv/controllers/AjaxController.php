<?php

namespace backend\modules\tpv\controllers;

use backend\components\ApiBCCR;
use backend\models\business\Customer;
use backend\models\business\Invoice;
use backend\models\business\InvoiceSearch;
use backend\models\business\ItemInvoice;
use backend\models\business\ItemInvoiceSearch;
use backend\models\business\PaymentMethodHasInvoice;
use backend\models\business\PaymentMethodHasCreditNote;
use backend\models\business\SellerHasCreditNote;
use backend\models\business\CollectorHasCreditNote;
use backend\models\business\SellerHasInvoice;
use backend\models\business\CollectorHasInvoice;
use backend\models\business\Product;
use backend\models\business\ProductHasBranchOffice;
use backend\models\business\CashRegister;
use backend\models\business\CashRegisteSearch;
use backend\models\business\CreditNote;
use backend\models\business\ItemCreditNote;
use backend\models\business\MovementCashRegister;
use backend\models\business\MovementCashRegisterDetail;
use backend\models\business\MovementCashRegisterDetailSearch;
use backend\models\nomenclators\Boxes;
use backend\models\nomenclators\CoinDenominations;
use backend\models\nomenclators\MovementTypes;
use backend\models\nomenclators\Category;
use backend\models\nomenclators\BranchOffice;
use backend\models\nomenclators\ConditionSale;
use backend\models\nomenclators\Currency;
use backend\models\nomenclators\UtilsConstants;
use backend\models\nomenclators\PaymentMethod;
use backend\models\settings\Issuer;
use backend\models\settings\Setting;
use common\components\ApiV43\ApiAccess;
use common\components\ApiV43\ApiConsultaHacienda;
use common\components\ApiV43\ApiEnvioHacienda;
use common\components\ApiV43\ApiFirmadoHacienda;
use common\components\ApiV43\ApiXML;
use common\models\EnviarEmailForm;
use backend\modules\tpv\models\BuscadorForm;
use common\models\User;
use kartik\mpdf\Pdf;
use Mpdf\QrCode\QrCode;
use Mpdf\QrCode\Output;
use Yii;
use yii\helpers\FileHelper;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\models\GlobalFunctions;
use Serializable;
use yii\db\Exception;
use yii\web\Response;

/**
 * AjaxController implements the CRUD actions for Invoice model.
 */
class AjaxController extends Controller
{

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                    'multiple_delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Invoice models.
     * @return mixed
     */
    public function actionSearchProducts($value)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $product = Product::find()->where(['bar_code' => trim($value)])->one();

        if ($product) {
            $price_unit = $product->price_detail;
            $percent_iva = $product->getPercentIvaToApply();
            $discount_amount = is_null($product->getDiscount()) ? 0 : $product->getDiscount();
            $exoneration = is_null($product->exoneration_purchase_percent) ? 0 : $product->exoneration_purchase_percent;
            $nature_discount = $product->nature_discount;
            if (is_null($nature_discount) || empty($nature_discount))
                $nature_discount = '-';

            $data = [
                'id' => $product->id,
                'name' => $product->description,
                'price' => $price_unit,
                'percent_iva' => $percent_iva,
                'discount_amount' => $discount_amount,
                'exoneration' => $exoneration,
                'nature_discount' => $nature_discount,
            ];
        } else
            $data = [];

        return \Yii::$app->response->data = [
            'data' => $data,
        ];
    }

    public function actionGetProducts($value, $barcode)
    {

        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $str = "<div id='prodContainer' style='margin: 0'>";

        if ($value == 0) {
            $subquery = Product::find()->select('category_id');
            $categorias = Category::find()->join('INNER JOIN', 'product', 'product.category_id = category.id')
                ->where(['status' => true, 'category_id' => $subquery])
                ->asArray()->all();
            foreach ($categorias as $c) {
                $str .= "<div class='prod' onclick='loadProducts( " . $c['id'] . "); return false;'>
                            <span><img src='/backend/web/images/folder.png' width='80px' height='80px'></span><br /> 
                            <a href='#' title=\"" . $c['name'] . "\">" . $c['name'] . "</a>
                        </div>";
            }
        } else {
            $categorias = Category::find()->where(['status' => true, 'id' => $value])->asArray()->all();
            foreach ($categorias as $c) {
                $str .= "<div class='prod' onclick='loadProducts( 0 ); return false;'>
                            <span><img src='/backend/web/images/folder_up.png' width='80px' height='80px'></span><br /> 
                            <a href='#'>Inicio</a>
                        </div>";
            }
            // Buscar los productos de esa categoria
            $productos = Product::find()->where(['category_id' => $value])->limit(30)->all();
            foreach ($productos as $p) {

                $producto = Product::find()->where(['id' => $p['id']])->one();
                $price_unit = $producto->price_detail;
                $percent_iva = $producto->getPercentIvaToApply();


                $discount_amount = is_null($producto->getDiscount()) ? 0 : $producto->getDiscount();
                $exoneration = is_null($producto->exoneration_purchase_percent) ? 0 : $producto->exoneration_purchase_percent;
                $nature_discount = $producto->nature_discount;
                if (is_null($nature_discount) || empty($nature_discount))
                    $nature_discount = '-';

                $quantity = ProductHasBranchOffice::getQuantity($p['id']);

                $descripcion = $p['description'] . $producto->getSimboloDescriptPercentIvaToApply();
                $str .= "
                        <div class='prod productos' data-id=\"'" . $descripcion . "'\" onclick=\"addProduct( " . $p['id'] . ", '" . $descripcion . "', " . $price_unit . ", " . $percent_iva . ", " . $discount_amount . ", " . $exoneration . ", '" . $nature_discount . "', '0'); return false;\">			    						
                            <span>
                                <img src='/images/noimage_default.jpg' class=\"img-responsive center-block\">
                            </span> 
                            <a href='#' title=\"" . $descripcion . "\" style=\"overflow: hidden; text-overflow: ellipsis; white-space: nowrap;\"><span>" . $descripcion . "</span></a>
                            <br />
                            <a href='#'>¢" . $price_unit . "</a>
                            <br />
                            <a href='#' style=\"color: #bbb;\">[stock: " . $quantity . "]</a>
                        </div>";
            }
        }

        $str .= "</div>";
        $data = ['content' => $str];
        return \Yii::$app->response->data = [
            'data' => $data,
        ];
    }

    public function actionSave_preorder()
    {
        $post = Yii::$app->request->post();
        $pro = $post['mprop'];
        $morder = $post['xmorder'];
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $result = 0;
        if (!empty($morder)) {
            $result = true;
            $session = Yii::$app->session;
            if ($session->has('consecutivo')) {
                // Existe la session
                $consecutivo = $session->get('consecutivo') + 1;
                $listordenes = $session->get('order_save');
                $session->remove('order_save');
            } else {
                $consecutivo = 1;
                $listordenes = [];
            }

            $newOrden = [
                'consecutivo' => $consecutivo,
                'customer' => $pro,
                'fecha' => date('d-m-Y h:i:s'),
                'orden' => trim($morder),
            ];

            $listordenes[] = $newOrden;
            $session->set('consecutivo', $consecutivo);
            $session->set('order_save', $listordenes);
        }
        return \Yii::$app->response->data = [
            'data' => $result
        ];
    }

    public function actionView_pre_orders()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $session = Yii::$app->session;
        $html = '';
        if ($session->has('consecutivo')) {
            // Existe la session  
            $html = "<div id='lst_pre_o'>";
            $listordenes = $session->get('order_save');
            foreach ($listordenes as $orden) {
                $customer = (!empty($orden['customer'])) ? $orden['customer'] : 'Anónimo';
                $html .= "<div class='row table' id=\"f-" . $orden['consecutivo'] . "\">
                            <div class='col-sm-5' onclick=\"see_pre_order(" . $orden['consecutivo'] . ")\">" . $customer . "</div>
                            <div class='col-sm-6' onclick='see_pre_order(" . $orden['consecutivo'] . ")'>" . $orden['fecha'] . "</div>
                            <div class='col-sm-1'><a href='#'  onClick=\"del_pre_lst(" . $orden['consecutivo'] . ");\">X</a></div>
                        </div>\n";
            }
            $html .= "</div>";
        }
        return \Yii::$app->response->data = [
            'data' => $html
        ];
    }

    public function actionView_pre_order($value)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $html = $this->getPreOrden($value);
        return \Yii::$app->response->data = [
            'data' => $html
        ];
    }

    public function getPreOrden($id)
    {
        $html = '';
        $session = Yii::$app->session;
        if ($session->has('order_save')) {
            $ordenes = $session->get('order_save');

            $founded = false;
            $i = 0;
            $cant = count($ordenes);
            while ($founded == false && $i < $cant) {
                if ($ordenes[$i]['consecutivo'] == $id) {
                    $founded = true;
                    $orden = $ordenes[$i];
                    $html = trim(json_decode($orden['orden']));
                    //die(var_dump($orden['orden']));
                }
                $i++;
            }
        }
        return $html;
    }

    public function actionDel_db_pre_order($value)
    {
        $session = Yii::$app->session;
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        if ($session->has('order_save')) {
            $ordenes = $session->get('order_save');

            $founded = false;
            $i = 0;
            $cant = count($ordenes);
            while ($founded == false && $i < $cant) {
                if ($ordenes[$i]['consecutivo'] == $value) {
                    $founded = true;
                    unset($ordenes[$i]);
                    $session->remove('order_save');
                }
                $i++;
            }
            $session->set('order_save', $ordenes);
        }
        return \Yii::$app->response->data = [
            'data' => $founded
        ];
    }

    public function actionCheckout()
    {
        $metodosPagos = PaymentMethod::getSelectMapForTpv();
        return $this->renderAjax('_form', [
            'metodosPagos' => $metodosPagos
        ]);
    }

    //$value, $lines, $customerId, $mpay, $yourpay
    public function actionCreate_order()
    {
        // Estos son los parámetros que se pasan
        //value=orderRow_6867_1|orderRow_5288_1|&lines=0:466.89&customerId=&mpay=0&yourpay=0:466.89 
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $post = Yii::$app->request->post();
        $value = $post['ordenes'];

        $temp = explode(':', $post['lines']);        
        $lines = $temp[1];

        $customerId = $post['customerId'];

        $mpay = $post['mpay'];

        $tipo_factura = $post['tipo'];

        $temp = explode(':', $post['yourpay']);
        $yourpay = $temp[1];

        //$tarjeta = $post['tarjeta'];
        $tarjeta = '-';
        $tarjeta_referencia = $post['tarjeta_referencia'];
        $banco = $post['banco'];
        $banco_cheque = $post['banco_cheque'];
        $banco_comprobante = $post['banco_comprobante'];

        $tempOrdenes = explode('|', $value);
        $ordenes = [];
        foreach ($tempOrdenes as $ord) {
            if (isset($ord[1]))
                $ordenes[] = $this->getInfoOrden($ord);
        }
        $data = [
            'result' => false,
            'errores' => ['No se ha podido crear la factura'],
            'invoice_id' => -1,
        ];

        if (count($ordenes) > 0) {
            $data = $this->CreateInvoice($ordenes, $tipo_factura, $customerId, $lines, $mpay, $yourpay, $tarjeta, $tarjeta_referencia, $banco, $banco_comprobante);
        } else
            GlobalFunctions::addFlashMessage('danger', 'No es posible crear la orden. No se han registrado items. Registre los items e inténtelo nuevamente');

        return \Yii::$app->response->data = $data;
    }

    function getInfoOrden($row)
    {
        $temp = explode('_', $row);
        $item = [];
        if (is_array($temp)) {
            $item =  ['product_id' => $temp[1], 'quantity' => $temp[2]];
        }
        return $item;
    }

    public function CreateInvoice($ordenes, $tipo_factura, $customerId, $lines, $mpay, $yourpay, $tarjeta, $tarjeta_referencia, $banco, $banco_comprobante)
    {
        $model = new Invoice();
        $errores = [];
        $model->loadDefaultValues();
        $model->status = UtilsConstants::INVOICE_STATUS_PENDING;
        $model->status_hacienda = UtilsConstants::HACIENDA_STATUS_NOT_SENT;
        $model->change_type = ApiBCCR::getChangeTypeOfIssuer();
        $model->ready_to_send_email = 0;
        $model->email_sent = 0;
        $result = true;

        $model->payment_methods[] = $mpay;

        $currency = Currency::findOne(['symbol' => 'CRC']);
        if ($currency !== null) {
            $model->currency_id = $currency->id;
        }

        $model->emission_date = date('Y-m-d H:i:s');
        $model->branch_office_id = User::getBranchOfficeIdOfActiveUser();
        $model->box_id = User::getBoxIdOfActiveUser();
        $model->sellers = [yii::$app->user->id];
        $model->collectors = [yii::$app->user->id];

        // Chequear que el usuario activo tenga rol agente y que la caja esté abierta
        if (GlobalFunctions::getRol() != User::ROLE_AGENT) {
            $errores[] = "Usted no tiene acceso a crear facturas del punto de venta, solo usuarios con rol agente pueden realizarlas";
        }
        if (!CashRegister::cajaAbierta($model->box_id)) {
            // Abrir Caja.
            /*
            $box_name = $model->box->numero.'-'.$model->box->name;
            $errores[] = "La caja ".$box_name.", no se ha abierto. Para poder crear facturas debe abrir antes la caja";          
            */
            $efectivo[] = [
                'value' => 0,
                'count' => 1,
                'description' => 'Apertura automática del sistema',
                'denominations_id' => NULL,
            ];
            $cashRegister = CashRegister::AbrirCaja($model->box_id, $efectivo);
        }
        if (is_null($model->box_id) || empty($model->box_id)) {
            $model->addError('box_id', Yii::t('backend', 'Debe seleccionar el punto de venta'));
            $errores[] = 'Debe seleccionar el punto de venta';
        }

        if (empty($errores)) {
            if ($tipo_factura == 'fac')
                $model->invoice_type = UtilsConstants::PRE_INVOICE_TYPE_INVOICE;
            else
                $model->invoice_type = UtilsConstants::PRE_INVOICE_TYPE_TICKET;
            if (!is_null($customerId) && !empty($customerId) && $customerId > 0)
                $customer = Customer::find()->where(['id' => $customerId])->one();
            else
                $customer = Customer::find()->where(['code' => '000001'])->one();

            if ($customer !== null) {
                $model->customer_id = $customer->id;
                $model->condition_sale_id = (isset($customer->condition_sale_id) && !empty($customer->condition_sale_id)) ? $customer->condition_sale_id : null;
                $model->credit_days_id = (isset($customer->credit_days_id) && !empty($customer->credit_days_id)) ? $customer->credit_days_id : null;
            }

            $model->consecutive = $model->generateConsecutive();
            $transaction = \Yii::$app->db->beginTransaction();
            try {
                if (Invoice::find()->select(['consecutive'])->where(['consecutive' => $model->consecutive])->exists()) {
                    $model->consecutive = $model->generateConsecutive();
                }

                $model->status_account_receivable_id = UtilsConstants::HACIENDA_STATUS_PENDING; // Para la gestión de cuentas por cobrar
                if ($model->condition_sale_id !== ConditionSale::getIdCreditConditionSale())
                    $model->pay_date = date('Y-m-d');

                if ($model->save()) {

                    PaymentMethodHasInvoice::updateRelation($model, [], 'payment_methods', 'payment_method_id');

                    SellerHasInvoice::updateRelation($model, [], 'sellers', 'seller_id');

                    CollectorHasInvoice::updateRelation($model, [], 'collectors', 'collector_id');

                    // Items                    
                    foreach ($ordenes as $ord) {
                        $item = new ItemInvoice;

                        $product_service_id = $ord['product_id'];
                        $quantity_label = $price_type_label = '';

                        $model_reference = Product::findOne($product_service_id);
                        $um = 77;

                        $item_exist = ItemInvoice::find()->where(['invoice_id' => $model->id, 'product_id' => $product_service_id, 'unit_type_id' => $um])->one();
                        if ($item_exist !== null) {
                            $item = $item_exist;
                            $item->product_id = $product_service_id;
                            $item->code = $model_reference->code;
                            $item->unit_type_id = (isset($um) && !empty($um)) ? $um : $model_reference->unit_type_id;
                            $item->user_id = Yii::$app->user->id;
                            $item->invoice_id = $model->id;
                            $item->quantity += $ord['quantity'];
                            $request_quantity = $item->quantity;
                            $item->price_type = UtilsConstants::CUSTOMER_ASSIGN_PRICE_DETAIL;
                        } else {
                            $item = new ItemInvoice;
                            $item->product_id = $product_service_id;
                            $item->code = $model_reference->code;
                            $item->unit_type_id = (isset($um) && !empty($um)) ? $um : $model_reference->unit_type_id;
                            $item->user_id = Yii::$app->user->id;
                            $item->invoice_id = $model->id;
                            $item->quantity = $ord['quantity'];
                            $request_quantity = $item->quantity;
                            $item->price_type = UtilsConstants::CUSTOMER_ASSIGN_PRICE_DETAIL;
                        }

                        if (isset($item->price_type)) {
                            $price_type_label = UtilsConstants::getPriceTypeMiniLabel($item->price_type);
                            $item->price_unit = $model_reference->getPriceByTypeAndUnitType($item->price_type, $item->unit_type_id);
                        }

                        $item->description = $model_reference->description . ' <b>' . $price_type_label . ' ' . $quantity_label . '</b>';

                        if ($model_reference !== null) {
                            $item->invoice_id = $model->id;

                            $percent_iva = $model_reference->getPercentIvaToApply();

                            $item->discount_amount = $model_reference->getDiscount();
                            $item->nature_discount = $model_reference->nature_discount;

                            if (is_null($item->nature_discount) || empty($item->nature_discount))
                                $item->nature_discount = '-';

                            // Se aplica el descuento a nivel de producto o servicio                            
                            $subtotal = $item->price_unit * $request_quantity - $item->discount_amount;
                            $item->subtotal = (isset($subtotal) && !empty($subtotal)) ? $subtotal : 0;

                            $tax_calculate = $subtotal * ($percent_iva / 100);
                            $tax = (isset($tax_calculate) && !empty($tax_calculate)) ? $tax_calculate : 0;

                            $exonerated = $tax * ($model_reference->exoneration_purchase_percent / 100);
                            $exonerated_tax_amount = (isset($exonerated) && !empty($exonerated)) ? $exonerated : 0;
                            $item->exonerate_amount = $exonerated_tax_amount;
                            $item->exoneration_purchase_percent = (int)$model_reference->exoneration_purchase_percent;
                            $item->exoneration_document_type_id = $model_reference->exoneration_document_type_id;
                            $item->number_exoneration_doc = $model_reference->number_exoneration_doc;
                            $item->name_institution_exoneration = $model_reference->name_institution_exoneration;
                            $item->exoneration_date = $model_reference->exoneration_date;

                            $item->tax_amount = $tax;
                            $item->tax_rate_percent = $model_reference->tax_rate_percent;
                            $item->tax_type_id = $model_reference->tax_type_id;
                            $item->tax_rate_type_id = $model_reference->tax_rate_type_id;
                            $item->price_total = $subtotal + $tax - $exonerated_tax_amount;
                        }

                        if ($item->save()) {
                            //Actualizar los totales de la factura
                            $invoice = Invoice::find()->where(['id' => $item->invoice_id])->one();
                            $invoice->save(false);

                            // Enviar la factura hacienda
                            //$resultado = $this->EnviarFacturaHacienda($invoice);
                        } else {
                            $result = false;
                            $errores[] = 'Error, ha ocurrido una excepción creando los items de la factura';
                            $transaction->rollBack();
                        }
                    }
                    $errores[] = 'Se ha creado la orden satisfactoriamente';
                } else {
                    $errores[] = 'Error creando la factura';
                    $result == false;
                }

                if ($result == true) {
                    // Crear el movimiento
                    $user = User::find()->where(['id' => Yii::$app->user->id])->one();
    
                    $tipo = MovementTypes::VENTA;
                    $invoice_id = $model->id;
                    $invoice = Invoice::find()->where(['id' => $model->id])->one();
                    $cantidad = 1;
    
                    $valor = $invoice->total_comprobante;
    
                    $coment = 'Venta';
                    $box_id = $user->box_id;
                    if (CashRegister::RegisterMovimiento($box_id, $tipo, $invoice_id, $cantidad, $valor, $coment)) {
                        //$result = true;
                        $result = UtilsConstants::sendInvoiceToHacienda($invoice_id);
                        $transaction->commit();
                    } else {
                        $errores[] = 'Error creando el movimiento';
                        $result = false;
                    }
                }

            } catch (Exception $e) {
                $errores[] = 'Error, ha ocurrido una excepción creando el elemento';
                $transaction->rollBack();
            }
            
        } 
        else
            $result = false;

        $customer = Customer::find()->where(['code' => '000001'])->one();

        return [
            'result' => $result,
            'errores' => $errores,
            'invoice_id' => $model->id,
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
        ];
    }

    public function actionMventas()
    {
        // 1- Obtener la fecha de apertura de la caja
        // 2- Buscar todos los cash_register con esa fecha (pueden ser varios si se cierra y abre la caja en el mismo dia)
        // 3- buscar en movement_cash_register_detail todas las facturas realizadas
        $user = User::find()->where(['id' => Yii::$app->user->id])->one();
        $box = Boxes::find()->where(['id' => $user->box_id])->one();
        $lastId = 0;

        $cashRegister = CashRegister::find()->where(['box_id' => $box->id, 'seller_id' => Yii::$app->user->id, 'status' => true])->one();
        if (!is_null($cashRegister)) {

            $fecha_apertura = $cashRegister->opening_date;

            $cashRegisters = CashRegister::find()->select('id')->where(['box_id' => $box->id, 'seller_id' => Yii::$app->user->id, 'opening_date' => $fecha_apertura])->asArray()->all();

            $listCash = [];
            foreach ($cashRegisters as $c)
                $listCash[] = $c['id'];

            //  die(var_dump($cashRegisters));

            $subquery = MovementCashRegisterDetail::find()->select("DISTINCT(movement_cash_register_detail.invoice_id)")
                ->join('INNER JOIN', 'movement_cash_register', "movement_cash_register_detail.movement_cash_register_id = movement_cash_register.id AND 
                                                                    movement_cash_register.movement_type_id = " . MovementTypes::VENTA . " ")
                ->where(['movement_cash_register.cash_register_id' => $listCash]);

            $is_point_sale = 1;
            $invoices = Invoice::find()->join('INNER JOIN', 'branch_office', 'invoice.branch_office_id = branch_office.id')
                ->join('INNER JOIN', 'boxes', "invoice.box_id = boxes.id AND boxes.is_point_sale = " . $is_point_sale . "")
                ->join('INNER JOIN', 'seller_has_invoice', "seller_has_invoice.invoice_id = invoice.id AND seller_has_invoice.seller_id = " . Yii::$app->user->id . "")
                ->where(['invoice.id' => $subquery, 'invoice.box_id' => $user->box_id])
                ->orderBy('id DESC')
                ->asArray()
                ->all();

            $str = "<table class=\"table table-responsive table-striped table-hover\">
                    <thead>
                    <tr>
                        <th style=\"color:#FFF\"><strong>Vendedor</strong></th>
                        <th style=\"color:#FFF\"><strong>Consecutivo</strong></th>
                        <th style=\"color:#FFF\"><strong>Fecha</strong></th>
                        <th style=\"color:#FFF\"><strong>Estado</strong></th>
                        <th style=\"color:#FFF\"><strong>Total</strong></th>                    
                    </tr></thead>";
            if (count($invoices) > 0) {
                $lastId = $invoices[0]['id'];
            }
            $total = 0;
            foreach ($invoices as $invoice) {
                $fecha = date('d-m-Y h:i:s', strtotime($invoice['emission_date']));
                $user = User::find()->where(['id' => Yii::$app->user->id])->one();
                $vendedor = $user->name;
                $estado = UtilsConstants::getStatusName($invoice['status_hacienda']);
                $total += $invoice['total_comprobante'];

                $str .= "<tr onClick=\"display_orde(" . $invoice['id'] . ")\">
                            <td>" . $vendedor . "</td>
                            <td>" . $invoice['consecutive'] . "</td>
                            <td>" . $fecha . "</td>
                            <td>" . $estado . "</td>
                            <td>¢ " . GlobalFunctions::formatNumber($invoice['total_comprobante'], 2) . "</td>                        
                        </tr>";
            }
            $str .= "<tr>
                        <td colspan=\"4\" align=\"right\"><strong>TOTAL</strong></td>
                        <td><strong>¢ " . GlobalFunctions::formatNumber($total, 2) . "</strong></td>                        
                    </tr>";
            return $lastId . '###' . $str;
        } else {
            $str = 'La caja está cerrada';
            return $lastId . '###' . $str;
        }
    }

    public function actionDisplay_order($order_id)
    {
        $is_point_sale = 1;
        $invoice = Invoice::find()->where(['id' => $order_id])
            ->one();
        $str = '';
        if (!is_null($invoice)) {
            $metodo_pago = '';
            $metodoPago = PaymentMethodHasInvoice::find()->where(['invoice_id' => $invoice->id])->one();
            if (!is_null($metodoPago) && !is_null($metodoPago->paymentMethod))
                $metodo_pago = $metodoPago->paymentMethod->name;

            $fecha = date('d-m-Y H:i:s', strtotime($invoice->emission_date));
            $str = "<div class=\"container-fluid\" id=\"cont\">
                    <div class=\"row mcab\">Cliente</div>
                    <div class=\"row\">
                        <div class=\"col-xs-2\">Nombre</div>
                        <div class=\"col-xs-10\">" . $invoice->customer->name . "</div>
                    </div>
                    <div class=\"row  mcab\"> Venta </div>
                    <div class=\"row\">
                        <div class=\"col-xs-2\">Factura</div>
                        <div class=\"col-xs-10\" id=\"id_fac\">" . $invoice->id . "</div>
                    </div>
                    <div class=\"row\">
                        <div class=\"col-xs-2\">Fecha</div>
                        <div class=\"col-xs-10\">" . $fecha . "</div>
                    </div>
                    <div class=\"row\">
                        <div class=\"col-xs-2\">Pago</div>
                        <div class=\"col-xs-10\">" . $metodo_pago . "</div>
                    </div>
                </div>
                <table class=\"table table-striped table-responsive\">
                    <thead>
                        <tr>
                            <tr>PRODUCTOS</tr>
                            <tr></tr>
                            <tr></tr>
                            <tr></tr>
                        </tr>
                        <tr>
                            <th style=\"color:#FFF\"><strong>Articulo</strong></th>
                            <th style=\"color:#FFF\"><strong>Precio</strong></th>
                            <th style=\"color:#FFF\"><strong>Unidades</strong></td>
                            <th style=\"color:#FFF\"><strong>Total</strong></th>
                        </tr>
                    </thead>
                    <tbody>";

            $items = ItemInvoice::find()->where(['invoice_id' => $invoice->id])->all();
            $subtotal = 0;
            foreach ($items as $item) {
                $producto = $item->product;
                $subtotal += $item->price_unit * $item->quantity;
                $str .= "<tr>
                            <td>" . $producto->description . "</td>
                            <td>¢" . GlobalFunctions::formatNumber($item->price_unit, 2) . "</td>
                            <td align=\"center\">" . (int)$item->quantity . "</td>
                            <td>¢" . GlobalFunctions::formatNumber($item->price_unit * $item->quantity, 2) . "</td>
                        </tr>";
            }

            // CACERES
            $str .= "<tr>
                            <td class=\"text-right\" colspan=\"3\" style=\"font-size:20px;\">
                                <strong>Subtotal</strong>
                            </td>
                            <td style=\"font-size:20px;\">
                                <span>¢" . GlobalFunctions::formatNumber($subtotal, 2) . "</span>
                            </td>
                        </tr>
                        <tr>
                            <td class=\"text-right\" colspan=\"3\" style=\"font-size:20px;\">
                                <strong>Descuento</strong>
                            </td>
                            <td style=\"font-size:20px;\">
                                <span>¢" . GlobalFunctions::formatNumber($invoice->total_discount, 2) . "</span>
                            </td>
                        </tr>
                        <tr>
                            <td class=\"text-right\" colspan=\"3\" style=\"font-size:20px;\">
                                <strong>IVA</strong>
                            </td>
                            <td style=\"font-size:20px;\">
                                <span>¢" . GlobalFunctions::formatNumber($invoice->total_tax, 2) . "</span>
                            </td>
                        </tr>
                        <tr>
                            <td class=\"text-right\" colspan=\"3\" style=\"font-size:20px;\">
                                <strong>Exonerado</strong>
                            </td>
                            <td style=\"font-size:20px;\">
                                <span>¢" . GlobalFunctions::formatNumber($invoice->total_exonerado, 2) . "</span>
                            </td>
                        </tr>
                        <tr>
                            <td class=\"text-right\" colspan=\"3\" style=\"font-size:20px;\">
                                <strong>Total</strong>
                            </td>
                            <td style=\"font-size:20px;\">
                                <strong><span>¢" . GlobalFunctions::formatNumber($invoice->total_comprobante, 2) . "</span></strong>
                            </td>
                        </tr>
                    </tbody>
                </table>";


            // Mostrar las devoluciones
            $str_dev = "<div class=\"container-fluid\" id=\"cont\">
                    <div class=\"row  mcab roj\" >Devoluciones</div>";

            $str_dev .= "<table class=\"table table-striped table-responsive\">
                <thead>
                    <tr>
                        <tr>PRODUCTOS</tr>
                        <tr></tr>
                        <tr></tr>
                        <tr></tr>
                    </tr>
                    <tr class=\"danger\">
                        <th><strong>Articulo</strong></th>
                        <th><strong>Precio</strong></th>
                        <th><strong>Unidades</strong></td>
                        <th><strong>Total</strong></th>
                    </tr>
                </thead>
                <tbody>";

            $dev = '';
            // Buscar todas las notas de crédito o devoluciones realizadas que tengan estado 
            $datacreditnote = CreditNote::find()->select('id')
                ->where(['<>', 'status_hacienda', UtilsConstants::HACIENDA_STATUS_REJECTED])
                ->andWhere(['<>', 'status_hacienda', UtilsConstants::HACIENDA_STATUS_CANCELLED])
                ->andWhere(['<>', 'status_hacienda', UtilsConstants::HACIENDA_STATUS_ANULATE])
                ->andWhere(['reference_number' => $invoice->key, 'reference_code' => '03'])
                ->orderBy('id DESC')
                ->all();
            $items = '';



            foreach ($datacreditnote as $note) {
                $quantity_devoluciones = 0;
                $dataitem = ItemCreditNote::find()->where(['credit_note_id' => $note->id])->all();

                foreach ($dataitem as $item) {
                    $producto = $item->product;
                    // $subtotal += $item->price_unit * $quantity_devoluciones;
                    $dev .= "<tr>
                                    <td>" . $producto->description . "</td>
                                    <td>¢" . GlobalFunctions::formatNumber($item->price_unit, 2) . "</td>
                                    <td align=\"center\">" . (int)$item->quantity . "</td>
                                    <td>¢" . GlobalFunctions::formatNumber($item->price_unit * (int)$item->quantity, 2) . "</td>
                                </tr>";
                }
            }

            if (!empty($dev))
                $str .=  $str_dev . $dev . "</tbody></table></div>";
        }
        return $str;
    }

    public function actionTip_factur($value)
    {
        $invoice = Invoice::find()->where(['id' => $value])->one();
        return '2|' . $invoice->consecutive;
    }

    public function actionInvoice($orderId, $committed, $mg)
    {
        $invoice = Invoice::find()->where(['id' => $orderId])->one();
        $items = ItemInvoice::find()->where(['invoice_id' => $orderId])->all();
        $issuer = Issuer::find()->one();

        $logo = "<img src=\"" . Setting::getUrlLogoBySettingAndType(1, Setting::SETTING_ID) . "\" width=\"165\"/>";

        $qr_code_invoice = $invoice->generateQrCode();
        $img_qr = '<img src="' . $qr_code_invoice . '" width="100"/>';
        $moneda = 'COLONES';
        $original = true;

        return $this->renderAjax('_FE', [
            'issuer' => $issuer,
            'invoice' => $invoice,
            'items' => $items,
            'items_invoice' => $items,
            'logo' => $logo,
            'moneda' => $moneda,
            'original' => $original,
            'img_qr' => $img_qr
        ]);
    }

    public function actionTiquete_html($orderId, $committed, $mg)
    {
        $invoice = Invoice::find()->where(['id' => $orderId])->one();
        $items = ItemInvoice::find()->where(['invoice_id' => $orderId])->all();
        $issuer = Issuer::find()->one();
        $setting = Setting::find()->where(['id' => 1])->one();

        $logo = "<img src=\"" . Setting::getUrlLogoBySettingAndType(1, Setting::SETTING_ID) . "\" width=\"100\"/>";

        $qr_code_invoice = $invoice->generateQrCode();
        $img_qr = '<img src="' . $qr_code_invoice . '" width="100"/>';
        $moneda = 'COLONES';
        $original = true;

        return $this->renderAjax('_TE', [
            'issuer' => $issuer,
            'invoice' => $invoice,
            'items' => $items,
            'items_invoice' => $items,
            'logo' => $logo,
            'moneda' => $moneda,
            'original' => $original,
            'img_qr' => $img_qr,
            'setting' => $setting,
        ]);
    }

    public function actionLst_box_operations()
    {
        //<table class="table"><thead><tr><th>Operacio&#769;n</th><th>Vendedor</th><th>Valor</th><th>Fecha</th></tr></thead><tr id="31"><td>Apertura de caja</td><td>Vendedor Anonimo</td>  <td>0.00€</td>  <td>12/01/2022 01:12:32</td> </tr>
        $user = User::find()->where(['id' => Yii::$app->user->id])->one();
        $box_id = $user->box_id;

        $str = "<table class=\"table\"><thead><tr><th>Operacio&#769;n</th><th>Vendedor</th><th>Valor</th><th>Fecha</th></tr></thead>";
        $cashRegister = CashRegister::find()->where(['box_id' => $box_id, 'status' => 1])->one();
        if (is_null($cashRegister)) {
            $str .= "<tr><td colspan=\"4\">La caja está cerrada.</td></tr>";
        } else {

            $movimientos = MovementCashRegister::find()->where(['cash_register_id' => $cashRegister->id])->orderBy('id DESC')->all();
            foreach ($movimientos as $mov) {
                if ($mov->movement_type_id == MovementTypes::APERTURA_CAJA) {
                    $valor = MovementCashRegisterDetail::find()->where(['movement_cash_register_id' => $mov->id])->sum('value * count');
                    $valor = GlobalFunctions::formatNumber($valor, 2);
                    $str .= "<tr id=\"" . $mov->id . "\"><td>" . $mov->movementType->nombre . "</td><td>" . $user->name . "&nbsp;" . $user->last_name . "</td><td>¢" . $valor . "</td><td>" . date('d-m-Y', strtotime($mov->movement_date)) . "&nbsp;" . $mov->movement_time . "</td> </tr>";
                } else
                if ($mov->movement_type_id == MovementTypes::ENTRADA_EFECTIVO || $mov->movement_type_id == MovementTypes::SALIDA_EFECTIVO) {
                    $movementCashRegisterDetail = MovementCashRegisterDetail::find()->where(['movement_cash_register_id' => $mov->id])->one();
                    $valor = 0;
                    if (!is_null($movementCashRegisterDetail))
                        $valor = $movementCashRegisterDetail->value * $movementCashRegisterDetail->count;
                    $valor = GlobalFunctions::formatNumber($valor, 2);
                    $str .= "<tr id=\"" . $mov->id . "\"><td>" . $mov->movementType->nombre . "</td><td>" . $user->name . "&nbsp;" . $user->last_name . "</td><td>¢" . $valor . "</td><td>" . date('d-m-Y', strtotime($mov->movement_date)) . "&nbsp;" . $mov->movement_time . "</td> </tr>";
                } else
                if ($mov->movement_type_id == MovementTypes::VENTA) {
                    $movementCashRegisterDetail = MovementCashRegisterDetail::find()->where(['movement_cash_register_id' => $mov->id])->all();
                    foreach ($movementCashRegisterDetail as $detail) {
                        $valor = 0;
                        $invoice = Invoice::find()->where(['id' => $detail->invoice_id])->one();
                        if (!is_null($invoice))
                            $valor = $invoice->total_comprobante;
                        $valor = GlobalFunctions::formatNumber($valor, 2);
                        $str .= "<tr id=\"" . $mov->id . "\"><td>" . $mov->movementType->nombre . "</td><td>" . $user->name . "&nbsp;" . $user->last_name . "</td><td>¢" . $valor . "</td><td>" . date('d-m-Y', strtotime($mov->movement_date)) . "&nbsp;" . $mov->movement_time . "</td> </tr>";
                    }
                }
            }
        }
        $str .= "</thead></table>";
        return $str;
    }

    public function actionBoxstatus()
    {
        // La suma de la apertura de caja + entradas - salidas
        $user = User::find()->where(['id' => Yii::$app->user->id])->one();
        $box = Boxes::find()->where(['id' => $user->box_id])->one();
        $vendedor = '-';
        if (!is_null($user))
            $vendedor = $user->name;

        $cashRegister = CashRegister::find()->where(['box_id' => $box->id, 'seller_id' => Yii::$app->user->id, 'status' => 1])->one();

        if (!is_null($cashRegister)) {
            $apertura_efectivo = MovementCashRegisterDetail::find()->join('INNER JOIN', 'movement_cash_register', "movement_cash_register_detail.movement_cash_register_id = movement_cash_register.id AND 
                                                                    movement_cash_register.movement_type_id = " . MovementTypes::APERTURA_CAJA . " ")
                ->where(['movement_cash_register.cash_register_id' => $cashRegister->id])
                ->sum('movement_cash_register_detail.value * movement_cash_register_detail.count');

            $entrada_efectivo = MovementCashRegisterDetail::find()->join('INNER JOIN', 'movement_cash_register', "movement_cash_register_detail.movement_cash_register_id = movement_cash_register.id AND 
                                                                    movement_cash_register.movement_type_id = " . MovementTypes::ENTRADA_EFECTIVO . " ")
                ->where(['movement_cash_register.cash_register_id' => $cashRegister->id])
                ->sum('movement_cash_register_detail.value * movement_cash_register_detail.count');

            $salida_efectivo = MovementCashRegisterDetail::find()->join('INNER JOIN', 'movement_cash_register', "movement_cash_register_detail.movement_cash_register_id = movement_cash_register.id AND 
                                                                        movement_cash_register.movement_type_id = " . MovementTypes::SALIDA_EFECTIVO . " ")
                ->where(['movement_cash_register.cash_register_id' => $cashRegister->id])
                ->sum('movement_cash_register_detail.value * movement_cash_register_detail.count');

            $apertura_efectivo = (is_null($apertura_efectivo) || empty($apertura_efectivo)) ? 0 : $apertura_efectivo;
            $entrada_efectivo = (is_null($entrada_efectivo) || empty($entrada_efectivo)) ? 0 : $entrada_efectivo;
            $salida_efectivo = (is_null($salida_efectivo) || empty($salida_efectivo)) ? 0 : $salida_efectivo;


            $opendate = date('d-m-Y', strtotime($cashRegister->opening_date)) . ' ' . $cashRegister->opening_time;
            //$actualdate = date('Y-m-d H:s:i');

            $subquery = MovementCashRegisterDetail::find()->select("DISTINCT(movement_cash_register_detail.invoice_id)")
                ->join('INNER JOIN', 'movement_cash_register', "movement_cash_register_detail.movement_cash_register_id = movement_cash_register.id AND 
                                                movement_cash_register.movement_type_id = " . MovementTypes::VENTA . " ")
                ->where(['movement_cash_register.cash_register_id' => $cashRegister->id]);

            $pago_efectivo = Invoice::find()->join('INNER JOIN', 'payment_method_has_invoice', 'payment_method_has_invoice.invoice_id = invoice.id AND payment_method_id = 1')
                ->where(['box_id' => $user->box_id, 'id' => $subquery])
                //->andWhere(['and', "emission_date >= '".$opendate."'",  "emission_date <= '".$actualdate."'"])
                ->sum('total_comprobante');

            $decuentos = Invoice::find()->where(['box_id' => $user->box_id, 'id' => $subquery])
                ->sum('total_discount');

            $impuestos = Invoice::find()->where(['box_id' => $user->box_id, 'id' => $subquery])
                ->sum('total_tax');


            $pago_tarjeta = Invoice::find()->join('INNER JOIN', 'payment_method_has_invoice', 'payment_method_has_invoice.invoice_id = invoice.id AND payment_method_id = 2')
                ->where(['box_id' => $user->box_id, 'id' => $subquery])
                //->andWhere(['and', "emission_date >= '".$opendate."'",  "emission_date <= '".$actualdate."'"])
                ->sum('total_comprobante');

            if (is_null($pago_efectivo) || empty($pago_efectivo))
                $pago_efectivo = 0;

            if (is_null($pago_tarjeta) || empty($pago_tarjeta))
                $pago_tarjeta = 0;

            if (is_null($decuentos) || empty($decuentos))
                $decuentos = 0;

            $monto_efectivo = $pago_efectivo + $apertura_efectivo + $entrada_efectivo - $salida_efectivo;

            $html = "<p style=\"padding-top: 25px\"><strong>Vendedor:</strong> " . $vendedor . "</p>
                <p><strong>Caja: </strong>" . $box->name . "</p>
                <p><strong>Apertura de Caja:</strong> " . $opendate . "</p>
                <p><strong>Cierra de Caja:</strong> " . date('d-m-Y h:i:s') . "</p>

                <div id=\"cntr1\">
                    <table cellspacing='0' cellpadding='0' id=\"drtf3\" class=\"table\">
                    <div id=\"cntr1\">
                        <table border='0' cellspacing='0' cellpadding='0' id='drtf3'>
                        <tr>
                            <td colspan='2' style='border-bottom: 1px solid #777;'>
                                <strong>Arqueo de caja:</strong>
                            </td>
                        </tr>
                        <tr>
                            <td colspan='2'><br/></td>
                        </tr>
                        <tr>
                            <td style='width: 40%'>Apertura de efectivo</td>
                            <td>¢" . GlobalFunctions::formatNumber($apertura_efectivo, 2) . "</td>
                        </tr>  
                  
                        <tr>
                            <td colspan='2' style='border-bottom: 1px solid #777;'>
                                <br/><strong>Detalle de efectivo:</strong>
                            </td>
                        </tr>
                        <tr>
                            <td colspan='2'><br/></td>
                        </tr>
                        <tr>
                            <td>Apertura de Caja</td>
                            <td>¢" . GlobalFunctions::formatNumber($apertura_efectivo, 2) . "</td>
                        </tr>                        
                        <tr>
                            <td>Entrada de efectivo</td>
                            <td>¢" . GlobalFunctions::formatNumber($entrada_efectivo, 2) . "</td>
                        </tr>
                        <tr>
                            <td>Salida de efectivo</td>
                            <td>¢" . GlobalFunctions::formatNumber($salida_efectivo, 2) . "</td>
                        </tr> 
                        <tr>
                            <td>Ventas en efectivo</td>
                            <td>¢" . GlobalFunctions::formatNumber($pago_efectivo, 2) . "</td>
                        </tr>   
                        <tr>
                            <td><strong>TOTAL EFECTIVO</strong></td>
                            <td><strong>¢" . GlobalFunctions::formatNumber($monto_efectivo, 2) . "</strong></td>
                        </tr>                         
                        
                        <tr>
                            <td colspan='2' style='border-bottom: 1px solid #777;'>
                                <br/>Detalle general de ventas:
                            </td>
                        </tr>
                        <tr>
                            <td colspan='2'><br/></td>
                        </tr>
                        <tr>
                            <td>En efectivo</td>
                            <td>¢" . GlobalFunctions::formatNumber($pago_efectivo, 2) . "</td>
                        </tr>   
                        <tr>
                            <td>En tarjeta</td>
                            <td>¢" . GlobalFunctions::formatNumber($pago_tarjeta, 2) . "</td>
                        </tr>
                        <tr>
                            <td>Descuentos</td>
                            <td>¢" . GlobalFunctions::formatNumber($decuentos, 2) . "</td>
                        </tr>     
                        <tr>
                            <td>Impuestos</td>
                            <td>¢" . GlobalFunctions::formatNumber($impuestos, 2) . "</td>
                        </tr>                                              
                        <tr class='cntr2'>
                            <td><strong>TOTAL DE VENTAS:</strong></td>
                            <td><strong>¢" . GlobalFunctions::formatNumber($pago_efectivo + $pago_tarjeta - $decuentos, 2) . "</strong></td>
                        </tr>
                    </table>
                </div>
                <div class=\"row\">
                    <div class=\"col-xs-12\">
                        <button type=\"button\"  class=\"btn btn-block btn-danger btn-lg btn-square btn-carry\" style=\"padding:15px 0;\" onclick=\"closebox(); return false;\"><span class=\"glyphicon glyphicon-remove\"></span>&nbsp;&nbsp;Cerrar la caja</button>
                    </div>
                </div>";
        } else {
            $html = "<p style=\"padding-top: 25px\">
            Fecha: <strong>" . date('d-m-Y h:s:i') . "</strong>
            </p>
            <p>Usuario: <strong>Vendedor: " . $vendedor . "</strong></p>
            Caja : <strong>" . $box->name . "</strong>
            </br>";
        }
        return $html;
    }

    public function actionEntryinbox($value, $coment)
    {
        $user = User::find()->where(['id' => Yii::$app->user->id])->one();
        $box_id = $user->box_id;

        $cashRegister = NULL;
        if (!CashRegister::cajaAbierta($box_id)) {
            // Abrir Caja.
            $efectivo[] = [
                'value' => $value,
                'count' => 1,
                'description' => 'Apertura automática del sistema',
                'denominations_id' => NULL,
            ];
            $cashRegister = CashRegister::AbrirCaja($box_id, $efectivo);
            if (!is_null($cashRegister))
                $resultado = 'Se ha realizado la apertura de caja con el monto solicitado';
        } else {
            if (is_null($cashRegister))
                $cashRegister = CashRegister::find()->where(['box_id' => $box_id, 'status' => 1])->one();

            $movement_type_id = MovementTypes::ENTRADA_EFECTIVO;
            $transaction = \Yii::$app->db->beginTransaction();
            try {
                $movementCashRegisterDetail = new MovementCashRegisterDetail;

                $movementCashRegister = new MovementCashRegister;
                $movementCashRegister->cash_register_id = $cashRegister->id;
                $movementCashRegister->movement_type_id = $movement_type_id;
                $movementCashRegister->movement_date = date('Y-m-d');
                $movementCashRegister->movement_time = date('h:i:s');
                $resultado = 'Ha ocurrido un error, no se se ha registrado el efectivo';
                if ($movementCashRegister->save(false)) {
                    $movementCashRegisterDetail->movement_cash_register_id = $movementCashRegister->id;
                    $movementCashRegisterDetail->value = $value;
                    $movementCashRegisterDetail->count = 1;
                    $movementCashRegisterDetail->comment = $coment;
                    if ($movementCashRegisterDetail->save()) {
                        $resultado = 'Se ha registrado el efectivo';
                        $transaction->commit();
                    } else
                        $transaction->rollBack();
                }
            } catch (Exception $e) {
                GlobalFunctions::addFlashMessage('danger', Yii::t('backend', 'Error, ha ocurrido una excepción creando el elemento'));
                $transaction->rollBack();
            }
        }
        return $resultado;
    }

    public function actionOutofbox($value, $coment)
    {
        $user = User::find()->where(['id' => Yii::$app->user->id])->one();
        $box_id = $user->box_id;

        if (!CashRegister::cajaAbierta($box_id)) {
            $resultado = 'La caja se encuentra cerrada. Debe abrirla antes de retirar efectivo';
        } else {
            $cashRegister = CashRegister::find()->where(['box_id' => $box_id, 'status' => 1])->one();

            $movement_type_id = MovementTypes::SALIDA_EFECTIVO;
            $transaction = \Yii::$app->db->beginTransaction();
            try {
                $movementCashRegisterDetail = new MovementCashRegisterDetail;

                $movementCashRegister = new MovementCashRegister;
                $movementCashRegister->cash_register_id = $cashRegister->id;
                $movementCashRegister->movement_type_id = $movement_type_id;
                $movementCashRegister->movement_date = date('Y-m-d');
                $movementCashRegister->movement_time = date('h:i:s');
                $resultado = 'Ha ocurrido un error, no se se ha registrado el efectivo';
                if ($movementCashRegister->save(false)) {
                    $movementCashRegisterDetail->movement_cash_register_id = $movementCashRegister->id;
                    $movementCashRegisterDetail->value = $value;
                    $movementCashRegisterDetail->count = 1;
                    $movementCashRegisterDetail->comment = $coment;
                    if ($movementCashRegisterDetail->save()) {
                        $resultado = 'Se ha registrado el efectivo';
                        $transaction->commit();
                    } else
                        $transaction->rollBack();
                }
            } catch (Exception $e) {
                GlobalFunctions::addFlashMessage('danger', Yii::t('backend', 'Error, ha ocurrido una excepción creando el elemento'));
                $transaction->rollBack();
            }
        }
        return $resultado;
    }

    public function actionLogout_app($value)
    {
        $html = $this->getHtmlBoxStatus();
        //$html = "<p>Vendedor: <strong>mosaicvega@gmail.com</strong><br/>Caja : <strong>3</strong></br>Apertura de caja: <strong>07-01-2022 15:45:34</strong></br>Cierre de caja: <strong>2022-01-12 18:41:31</strong></br><br/><p style='margin: 8px 0;border-bottom: 1px solid #999;'>Arqueo de caja:</p><div id=\"cntr1\"><table border='0' cellspacing='0' cellpadding='0' id='drtf3'><tr><td style='width: 40%'>Efectivo</td><td>510.00</td></tr></table></div><p style='margin: 8px 0;border-bottom: 1px solid #999;'>Ventas:</p><div id=\"cntr1\"><table border='0' cellspacing='0' cellpadding='0' id='drtf3'><tr id='mt4'><td>En efectivo</td><td>500.00</td><tr><tr><td style='width: 40%'>Total</td><td>500.00</td></tr><tr><td>Descuentos</td><td>0.00</td></tr><tr><td>Impuestos</td><td>32.71</td></tr></table></div><div id=\"cntr1\"><p style='margin: 8px 0;border-bottom: 1px solid #999;'>Devoluciones:</p><table border='0' cellspacing='0' cellpadding='0' id='drtf3'><tr><td style='width: 40%'>Total</td><td>0.00</td></tr><tr><td style='width: 40%'>Impuestos (sobre devoluciones)</td><td>0.00</td></tr></table></div>";
        return $html;
    }

    public function getHtmlBoxStatus()
    {
        // La suma de la apertura de caja + entradas - salidas
        $user = User::find()->where(['id' => Yii::$app->user->id])->one();
        $box = Boxes::find()->where(['id' => $user->box_id])->one();
        $vendedor = '-';
        if (!is_null($user))
            $vendedor = $user->name;

        $cashRegister = CashRegister::find()->where(['box_id' => $box->id, 'seller_id' => Yii::$app->user->id, 'status' => 1])->one();

        $html = '';

        if (!is_null($cashRegister)) {
            $apertura_efectivo = MovementCashRegisterDetail::find()->join('INNER JOIN', 'movement_cash_register', "movement_cash_register_detail.movement_cash_register_id = movement_cash_register.id AND 
                                                                    movement_cash_register.movement_type_id = " . MovementTypes::APERTURA_CAJA . " ")
                ->where(['movement_cash_register.cash_register_id' => $cashRegister->id])
                ->sum('movement_cash_register_detail.value * movement_cash_register_detail.count');

            $entrada_efectivo = MovementCashRegisterDetail::find()->join('INNER JOIN', 'movement_cash_register', "movement_cash_register_detail.movement_cash_register_id = movement_cash_register.id AND 
                                                                    movement_cash_register.movement_type_id = " . MovementTypes::ENTRADA_EFECTIVO . " ")
                ->where(['movement_cash_register.cash_register_id' => $cashRegister->id])
                ->sum('movement_cash_register_detail.value * movement_cash_register_detail.count');

            $salida_efectivo = MovementCashRegisterDetail::find()->join('INNER JOIN', 'movement_cash_register', "movement_cash_register_detail.movement_cash_register_id = movement_cash_register.id AND 
                                                                        movement_cash_register.movement_type_id = " . MovementTypes::SALIDA_EFECTIVO . " ")
                ->where(['movement_cash_register.cash_register_id' => $cashRegister->id])
                ->sum('movement_cash_register_detail.value * movement_cash_register_detail.count');

            $apertura_efectivo = (is_null($apertura_efectivo) || empty($apertura_efectivo)) ? 0 : $apertura_efectivo;
            $entrada_efectivo = (is_null($entrada_efectivo) || empty($entrada_efectivo)) ? 0 : $entrada_efectivo;
            $salida_efectivo = (is_null($salida_efectivo) || empty($salida_efectivo)) ? 0 : $salida_efectivo;


            $opendate = date('d-m-Y', strtotime($cashRegister->opening_date)) . ' ' . $cashRegister->opening_time;
            //$actualdate = date('Y-m-d H:s:i');

            $subquery = MovementCashRegisterDetail::find()->select("DISTINCT(movement_cash_register_detail.invoice_id)")
                ->join('INNER JOIN', 'movement_cash_register', "movement_cash_register_detail.movement_cash_register_id = movement_cash_register.id AND 
                                                movement_cash_register.movement_type_id = " . MovementTypes::VENTA . " ")
                ->where(['movement_cash_register.cash_register_id' => $cashRegister->id]);

            $pago_efectivo = Invoice::find()->join('INNER JOIN', 'payment_method_has_invoice', 'payment_method_has_invoice.invoice_id = invoice.id AND payment_method_id = 1')
                ->where(['box_id' => $user->box_id, 'id' => $subquery])
                //->andWhere(['and', "emission_date >= '".$opendate."'",  "emission_date <= '".$actualdate."'"])
                ->sum('total_comprobante');

            $decuentos = Invoice::find()->where(['box_id' => $user->box_id, 'id' => $subquery])
                ->sum('total_discount');

            $impuestos = Invoice::find()->where(['box_id' => $user->box_id, 'id' => $subquery])
                ->sum('total_tax');


            $pago_tarjeta = Invoice::find()->join('INNER JOIN', 'payment_method_has_invoice', 'payment_method_has_invoice.invoice_id = invoice.id AND payment_method_id = 2')
                ->where(['box_id' => $user->box_id, 'id' => $subquery])
                //->andWhere(['and', "emission_date >= '".$opendate."'",  "emission_date <= '".$actualdate."'"])
                ->sum('total_comprobante');

            if (is_null($pago_efectivo) || empty($pago_efectivo))
                $pago_efectivo = 0;

            if (is_null($pago_tarjeta) || empty($pago_tarjeta))
                $pago_tarjeta = 0;

            if (is_null($decuentos) || empty($decuentos))
                $decuentos = 0;

            $monto_efectivo = $pago_efectivo + $apertura_efectivo + $entrada_efectivo - $salida_efectivo;

            $html = "<p style=\"padding-top: 25px\"><strong>Vendedor:</strong> " . $vendedor . "</p>
                <p><strong>Caja: </strong>" . $box->name . "</p>
                <p><strong>Apertura de Caja:</strong> " . $opendate . "</p>
                <p><strong>Cierra de Caja:</strong> " . date('d-m-Y h:i:s') . "</p>

                <div id=\"cntr1\">
                    <table cellspacing='0' cellpadding='0' id=\"drtf3\" class=\"table\">
                    <div id=\"cntr1\">
                        <table border='0' cellspacing='0' cellpadding='0' id='drtf3'>
                        <tr>
                            <td colspan='2' style='border-bottom: 1px solid #777;'>
                                Arqueo de caja:
                            </td>
                        </tr>
                        <tr>
                            <td colspan='2'><br/></td>
                        </tr>
                        <tr>
                            <td style='width: 40%'>Apertura de efectivo</td>
                            <td>¢" . GlobalFunctions::formatNumber($apertura_efectivo, 2) . "</td>
                        </tr>  
                  
                        <tr>
                            <td colspan='2' style='border-bottom: 1px solid #777;'>
                                <br/>Detalle de efectivo:
                            </td>
                        </tr>
                        <tr>
                            <td colspan='2'><br/></td>
                        </tr>
                        <tr>
                            <td>Apertura de Caja</td>
                            <td>¢" . GlobalFunctions::formatNumber($apertura_efectivo, 2) . "</td>
                        </tr>                        
                        <tr>
                            <td>Entrada de efectivo</td>
                            <td>¢" . GlobalFunctions::formatNumber($entrada_efectivo, 2) . "</td>
                        </tr>
                        <tr>
                            <td>Salida de efectivo</td>
                            <td>¢" . GlobalFunctions::formatNumber($salida_efectivo, 2) . "</td>
                        </tr> 
                        <tr>
                            <td>Ventas en efectivo</td>
                            <td>¢" . GlobalFunctions::formatNumber($pago_efectivo, 2) . "</td>
                        </tr>   
                        <tr>
                            <td>TOTAL EFECTIVO</td>
                            <td>¢" . GlobalFunctions::formatNumber($monto_efectivo, 2) . "</td>
                        </tr>                         
                        
                        <tr>
                            <td colspan='2' style='border-bottom: 1px solid #777;'>
                                <br/>Detalle general de ventas:
                            </td>
                        </tr>
                        <tr>
                            <td colspan='2'><br/></td>
                        </tr>
                        <tr>
                            <td>En efectivo</td>
                            <td>¢" . GlobalFunctions::formatNumber($pago_efectivo, 2) . "</td>
                        </tr>   
                        <tr>
                            <td>En tarjeta</td>
                            <td>¢" . GlobalFunctions::formatNumber($pago_tarjeta, 2) . "</td>
                        </tr>
                        <tr>
                            <td>Descuentos</td>
                            <td>¢" . GlobalFunctions::formatNumber($decuentos, 2) . "</td>
                        </tr>     
                        <tr>
                            <td>Impuestos</td>
                            <td>¢" . GlobalFunctions::formatNumber($impuestos, 2) . "</td>
                        </tr>                                              
                        <tr class='cntr2'>
                            <td><strong>Total de ventas:</strong></td>
                            <td><strong>¢" . GlobalFunctions::formatNumber($pago_efectivo + $pago_tarjeta - $decuentos, 2) . "</strong></td>
                        </tr>
                    </table>
                </div>";
        }
        return $html;
    }

    /*
    public function getHtmlBoxStatus()
    {
        // La suma de la apertura de caja + entradas - salidas
        $user = User::find()->where(['id' => Yii::$app->user->id])->one();
        $box = Boxes::find()->where(['id' => $user->box_id])->one();
        $html = '';
        $vendedor = '-';
        if (!is_null($user))
            $vendedor = $user->name;

        $cashRegister = CashRegister::find()->where(['box_id' => $box->id, 'seller_id' => Yii::$app->user->id, 'status' => 1])->one();
        if (!is_null($cashRegister))
        {
            $apertura_efectivo = MovementCashRegisterDetail::find()->join('INNER JOIN', 'movement_cash_register', "movement_cash_register_detail.movement_cash_register_id = movement_cash_register.id AND 
                                                                            movement_cash_register.movement_type_id = " . MovementTypes::APERTURA_CAJA . " ")
                ->where(['movement_cash_register.cash_register_id' => $cashRegister->id])
                ->sum('movement_cash_register_detail.value * movement_cash_register_detail.count');

            $entrada_efectivo = MovementCashRegisterDetail::find()->join('INNER JOIN', 'movement_cash_register', "movement_cash_register_detail.movement_cash_register_id = movement_cash_register.id AND 
                                                                            movement_cash_register.movement_type_id = " . MovementTypes::ENTRADA_EFECTIVO . " ")
                ->where(['movement_cash_register.cash_register_id' => $cashRegister->id])
                ->sum('movement_cash_register_detail.value * movement_cash_register_detail.count');

            $salida_efectivo = MovementCashRegisterDetail::find()->join('INNER JOIN', 'movement_cash_register', "movement_cash_register_detail.movement_cash_register_id = movement_cash_register.id AND 
                                                                                movement_cash_register.movement_type_id = " . MovementTypes::SALIDA_EFECTIVO . " ")
                ->where(['movement_cash_register.cash_register_id' => $cashRegister->id])
                ->sum('movement_cash_register_detail.value * movement_cash_register_detail.count');

            $apertura_efectivo = (is_null($apertura_efectivo) || empty($apertura_efectivo)) ? 0 : $apertura_efectivo;
            $entrada_efectivo = (is_null($entrada_efectivo) || empty($entrada_efectivo)) ? 0 : $entrada_efectivo;
            $salida_efectivo = (is_null($salida_efectivo) || empty($salida_efectivo)) ? 0 : $salida_efectivo;

            $monto_efectivo = $apertura_efectivo + $entrada_efectivo - $salida_efectivo;



            //$opendate = $cashRegister->opening_date.' '.$cashRegister->opening_time;
            //$actualdate = date('Y-m-d H:s:i');

            $subquery = MovementCashRegisterDetail::find()->select("DISTINCT(movement_cash_register_detail.invoice_id)")
                ->join('INNER JOIN', 'movement_cash_register', "movement_cash_register_detail.movement_cash_register_id = movement_cash_register.id AND 
                                                        movement_cash_register.movement_type_id = " . MovementTypes::VENTA . " ")
                ->where(['movement_cash_register.cash_register_id' => $cashRegister->id]);

            $pago_efectivo = Invoice::find()->join('INNER JOIN', 'payment_method_has_invoice', 'payment_method_has_invoice.invoice_id = invoice.id AND payment_method_id = 1')
                ->where(['box_id' => $user->box_id, 'id' => $subquery])
                //->andWhere(['and', "emission_date >= '".$opendate."'",  "emission_date <= '".$actualdate."'"])
                ->sum('total_comprobante');

            $pago_tarjeta = Invoice::find()->join('INNER JOIN', 'payment_method_has_invoice', 'payment_method_has_invoice.invoice_id = invoice.id AND payment_method_id = 2')
                ->where(['box_id' => $user->box_id, 'id' => $subquery])
                //->andWhere(['and', "emission_date >= '".$opendate."'",  "emission_date <= '".$actualdate."'"])
                ->sum('total_comprobante');

            $total_iva = Invoice::find()->join('INNER JOIN', 'payment_method_has_invoice', 'payment_method_has_invoice.invoice_id = invoice.id')
                ->where(['box_id' => $user->box_id, 'id' => $subquery])
                //->andWhere(['and', "emission_date >= '".$opendate."'",  "emission_date <= '".$actualdate."'"])
                ->sum('total_tax');

            $total_descuentos = Invoice::find()->join('INNER JOIN', 'payment_method_has_invoice', 'payment_method_has_invoice.invoice_id = invoice.id')
                ->where(['box_id' => $user->box_id, 'id' => $subquery])
                //->andWhere(['and', "emission_date >= '".$opendate."'",  "emission_date <= '".$actualdate."'"])
                ->sum('total_discount');            
                            

            if (is_null($pago_efectivo) || empty($pago_efectivo))
                $pago_efectivo = 0;

            if (is_null($pago_tarjeta) || empty($pago_tarjeta))
                $pago_tarjeta = 0;


            $html = "<p>Vendedor: <strong>" . $vendedor . "</strong>
                    <br/>Caja : <strong>" . $box->name . "</strong></br>
                    Apertura de caja: <strong>" . date('d-m-Y H:i:s', strtotime($cashRegister->opening_date)) . "</strong>
                    </br>
                    Cierre de caja: <strong>" . date('d-m-Y H:i:s') . "</strong>
                    </br><br/>
                    <p style='margin: 8px 0;border-bottom: 1px solid #999;'>Arqueo de caja:</p>
                    <div id=\"cntr1\">
                        <table border='0' cellspacing='0' cellpadding='0' id='drtf3'>
                            <tr>
                                <td style='width: 40%'>Efectivo</td>
                                <td>¢" . GlobalFunctions::formatNumber($monto_efectivo, 2) . "</td>
                            </tr>
                        </table>
                    </div>
                    <p style='margin: 8px 0;border-bottom: 1px solid #999;'>Ventas:</p>
                    <div id=\"cntr1\">
                        <table border='0' cellspacing='0' cellpadding='0' id='drtf3'>

                            <tr id='mt4'>
                                <td><strong>En efectivo</strong></td>
                                <td>¢" . GlobalFunctions::formatNumber($pago_efectivo, 2) . "</td>
                            </tr>
                            <tr>
                                <td><strong>En tarjeta</strong></td>
                                <td><strong>¢" . GlobalFunctions::formatNumber($pago_tarjeta, 2) . "</strong></td>
                            </tr>
                            <tr>
                                <td><strong>Descuentos</strong></td>
                                <td><strong>¢" . GlobalFunctions::formatNumber($total_descuentos, 2) . "</strong></td>
                            </tr>
                            <tr>
                                <td><strong>Impuestos</strong></td>
                                <td><strong>¢" . GlobalFunctions::formatNumber($total_iva, 2) . "</strong></td>                            
                            </tr>                           
                            <tr>
                                <td><strong>Total:</strong></td>
                                <td><strong>¢" . GlobalFunctions::formatNumber($pago_efectivo + $pago_tarjeta, 2) . "</strong></td>
                            </tr>
                        </table>
                    </div>
                    <div id=\"cntr1\">
                        <p style='margin: 8px 0;border-bottom: 1px solid #999;'>Devoluciones:</p>
                        <table border='0' cellspacing='0' cellpadding='0' id='drtf3'>
                            <tr>
                                <td style='width: 40%'>Total</td>
                                <td>0.00</td>
                            </tr>
                        </table>
                    </div>";
        }
        return $html;
    }
    */

    public function actionCloseboxfinish($value)
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        // Hay que cerrar la caja
        // La suma de la apertura de caja + entradas - salidas
        $user = User::find()->where(['id' => Yii::$app->user->id])->one();
        $box = Boxes::find()->where(['id' => $user->box_id])->one();

        $result = false;
        $cashRegister = CashRegister::find()->where(['box_id' => $box->id, 'seller_id' => Yii::$app->user->id, 'status' => 1])->one();
        if (!is_null($cashRegister)) {
            $apertura_efectivo = MovementCashRegisterDetail::find()->join('INNER JOIN', 'movement_cash_register', "movement_cash_register_detail.movement_cash_register_id = movement_cash_register.id AND 
                                                                            movement_cash_register.movement_type_id = " . MovementTypes::APERTURA_CAJA . " ")
                ->where(['movement_cash_register.cash_register_id' => $cashRegister->id])
                ->sum('movement_cash_register_detail.value * movement_cash_register_detail.count');

            $entrada_efectivo = MovementCashRegisterDetail::find()->join('INNER JOIN', 'movement_cash_register', "movement_cash_register_detail.movement_cash_register_id = movement_cash_register.id AND 
                                                                            movement_cash_register.movement_type_id = " . MovementTypes::ENTRADA_EFECTIVO . " ")
                ->where(['movement_cash_register.cash_register_id' => $cashRegister->id])
                ->sum('movement_cash_register_detail.value * movement_cash_register_detail.count');

            $salida_efectivo = MovementCashRegisterDetail::find()->join('INNER JOIN', 'movement_cash_register', "movement_cash_register_detail.movement_cash_register_id = movement_cash_register.id AND 
                                                                                movement_cash_register.movement_type_id = " . MovementTypes::SALIDA_EFECTIVO . " ")
                ->where(['movement_cash_register.cash_register_id' => $cashRegister->id])
                ->sum('movement_cash_register_detail.value * movement_cash_register_detail.count');

            $apertura_efectivo = (is_null($apertura_efectivo) || empty($apertura_efectivo)) ? 0 : $apertura_efectivo;
            $entrada_efectivo = (is_null($entrada_efectivo) || empty($entrada_efectivo)) ? 0 : $entrada_efectivo;
            $salida_efectivo = (is_null($salida_efectivo) || empty($salida_efectivo)) ? 0 : $salida_efectivo;
            $monto_efectivo = $apertura_efectivo + $entrada_efectivo - $salida_efectivo;

            $cashRegister->initial_amount = $apertura_efectivo;
            $cashRegister->end_amount = $monto_efectivo;
            $cashRegister->closing_date = date('Y-m-d');
            $cashRegister->closing_time = date('H:i:s');
            $cashRegister->status = false;

            $subquery = MovementCashRegisterDetail::find()->select("DISTINCT(movement_cash_register_detail.invoice_id)")
                ->join('INNER JOIN', 'movement_cash_register', "movement_cash_register_detail.movement_cash_register_id = movement_cash_register.id AND 
                                                                movement_cash_register.movement_type_id = " . MovementTypes::VENTA . " ")
                ->where(['movement_cash_register.cash_register_id' => $cashRegister->id]);

            $total_sales = Invoice::find()->join('INNER JOIN', 'payment_method_has_invoice', 'payment_method_has_invoice.invoice_id = invoice.id')
                ->where(['box_id' => $user->box_id, 'id' => $subquery])
                //->andWhere(['and', "emission_date >= '".$opendate."'",  "emission_date <= '".$actualdate."'"])
                ->sum('total_comprobante');
            if (is_null($total_sales))
                $total_sales = 0;

            $cashRegister->total_sales = $total_sales;
            if ($cashRegister->save())
                $result = true;
        }
        return \Yii::$app->response->data = $result;
    }

    public function actionCustomerform()
    {
        $model = new Customer();
        $model->loadDefaultValues();
        $model->status = 1;
        $model->code = $model->generateCode();

        $model->customer_type_id = 4;
        $model->customer_classification_id = 4;
        $model->price_assigned = UtilsConstants::CUSTOMER_ASSIGN_PRICE_DETAIL;
        $model->route_transport_id = 13; // Herbavic
        //$model->collector_id = yii::$app->user->id;
        //$model->collector_id = yii::$app->user->id;
        $model->condition_sale_id = 8; // Contado
        $model->pre_invoice_type = UtilsConstants::PRE_INVOICE_TYPE_TICKET;
        $model->identification_type_id = 6; // Cedula física       

        $modelBuscador = new BuscadorForm;

        return $this->renderAjax('_cutomerform', [
            'modelBuscador' => $modelBuscador,
            'model' => $model,
        ]);
    }

    public function actionOpenbox()
    {
        $user = User::find()->where(['id' => Yii::$app->user->id])->one();
        $box = Boxes::find()->where(['id' => $user->box_id])->one();
        $box_id = $box->id;
        $msg = '';

        $model = new CashRegister();
        $coins = CoinDenominations::find()->asArray()->orderBy('value DESC')->all();
        $box = Boxes::find()->where(['id' => $box_id])->one();
        $model->opening_date = date('Y-m-d');
        $model->opening_time = date('H:i:s');
        $model->initial_amount = 0;
        $model->seller_id = Yii::$app->user->id;
        if (!is_null($box))
            $model->branch_office_id = $box->branch_office_id;
        $model->box_id = $box_id;

        if ($this->cajaAbierta($box_id)) {
            GlobalFunctions::addFlashMessage('danger', Yii::t('backend', 'Error, La Caja seleccionada se encuentra abierta, por favor cierrela e inténtelo de nuevo'));
            $msg = 'La caja está abierta';
            return $msg;
        }

        if ($model->load(Yii::$app->request->post())) {
            $transaction = \Yii::$app->db->beginTransaction();
            $model->opening_date = date('Y-m-d');
            $model->opening_time = date('H:i:s');
            try {
                if ($model->save()) {
                    $movimiento = new MovementCashRegister;
                    $movimiento->cash_register_id = $model->id;
                    $movimiento->movement_type_id = MovementTypes::APERTURA_CAJA;
                    $movimiento->movement_date = date('Y-m-d');
                    $movimiento->movement_time = date('H:i:s');
                    $initial_amount = 0;
                    if ($movimiento->save()) {
                        $efectivo = Yii::$app->request->post()['efectivo'];
                        foreach ($efectivo as $e) {
                            if ($e['count'] > 0) {
                                $detail = new MovementCashRegisterDetail;
                                $detail->movement_cash_register_id = $movimiento->id;
                                $detail->value = $e['value'];
                                $detail->count = $e['count'];
                                $detail->comment = $e['description'];
                                $detail->coin_denomination_id = $e['denominations_id'];
                                $detail->save();
                                $initial_amount += $e['value'] * $e['count'];
                            }
                        }
                    }
                    $model->initial_amount = $initial_amount;
                    $model->save();
                    $transaction->commit();
                }
            } catch (Exception $e) {
                die(var_dump($e));
                $msg = Yii::t('backend', 'Error, ha ocurrido una excepción abriendo la caja');
                $transaction->rollBack();
                \Yii::$app->response->format = Response::FORMAT_JSON;
                return \Yii::$app->response->data = $msg;
            }

            //GlobalFunctions::addFlashMessage('success', Yii::t('backend', 'Información. Se ha abierto la caja satisfactoriamente'));            
            $msg = Yii::t('backend', 'Se ha abierto la caja satisfactoriamente');
            \Yii::$app->response->format = Response::FORMAT_JSON;
            return \Yii::$app->response->data = $msg;
        }

        return $this->renderAjax('_formOpenBox', [
            'msg' => $msg,
            'model' => $model,
            'box_id' => $box_id,
            'coins' => $coins,
        ]);
    }

    public function actionCheckstatusbox()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $user = User::find()->where(['id' => Yii::$app->user->id])->one();
        $box = Boxes::find()->where(['id' => $user->box_id])->one();
        $box_id = $box->id;
        $status = $this->cajaAbierta($box_id);
        if ($status == false)
            $status = 'CERRADA';
        else
            $status = 'ABIERTA';

        return [
            'status' => $status
        ];
    }

    public function cajaAbierta($box_id)
    {
        return CashRegister::cajaAbierta($box_id);
    }

    public function actionSearchcustomer($q = null, $id = null)
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $out = ['results' => ['id' => '', 'text' => '']];
        if (!is_null($q)) {
            $models = Customer::find()
                ->select(['id', 'name', 'identification'])
                ->where(['status' => 1])
                ->andWhere("name LIKE '%$q%' OR identification LIKE '%$q%'")
                ->limit(200)
                ->asArray()
                ->all();

            $array_map = [];

            if (count($models) > 0) {
                foreach ($models as $index => $model) {
                    $temp_name = $model['name'];
                    if (isset($model['identification']) && !empty($model['identification'])) {
                        $temp_name = "{$temp_name} - {$model['identification']}";
                    }

                    $temp_name = mb_strtoupper($temp_name);

                    $array_map[] = ['id' => $model['id'], 'text' => "{$temp_name}"];
                }

                $out['results'] = $array_map;
            }
        }
        return $out;
    }

    public function actionSeleccionarcustomer()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $model = new BuscadorForm;
        $result = ['id' => 0, 'nombre' => ''];
        if ($model->load(Yii::$app->request->post())) {
            $customer = Customer::find()->where(['id' => $model->customer_id])->one();
            if (!is_null($customer)) {
                $result = ['customer_id' => $customer->id, 'customer_name' => $customer->name];
            }
        }
        return \Yii::$app->response->data = $result;
    }



    public function actionAddnewcustomer()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $msg = '';
        $customer_id = -1;
        $customer_name = '';
        $result = false;

        $model = new Customer();
        if ($model->load(Yii::$app->request->post())) {
            $customer = Customer::find()->where(['name' => trim($model->name), 'identification' => $model->identification])->one();
            if (is_null($customer)) {
                $model->code = $model->generateCode();
                $model->customer_type_id = 4;
                $model->customer_classification_id = 4;
                $model->price_assigned = UtilsConstants::CUSTOMER_ASSIGN_PRICE_DETAIL;
                $model->route_transport_id = 13; // Herbavic
                $model->collector_id = yii::$app->user->id;
                $model->collector_id = yii::$app->user->id;
                $model->condition_sale_id = 8; // Contado
                $model->pre_invoice_type = UtilsConstants::PRE_INVOICE_TYPE_TICKET;
                $model->identification_type_id = 6; // Cedula física           
                //$model->name = $name;
                //$model->identification_type_id = $tipo_identificacion;
                //$model->identification = $identificacion;
                if ($model->save()) {
                    $result = true;
                    $msg = 'Se ha registrado el cliente';
                    $customer_id = $model->id;
                    $customer_name = $model->name;
                } else {
                    $msg = 'Ha ocurrido un error no se ha podido registrar el cliente.';
                }
            } else {
                $result = false;
                $msg = 'Ya existe un cliente con ese nombre e identificación';
            }
        }

        $data = [
            'result' => $result,
            'msg' => $msg,
            'customer_id' => $customer_id,
            'customer_name' => $customer_name,
        ];

        return \Yii::$app->response->data = $data;
    }

    public function actionPurchasereturn($value)
    {
        $invoice = Invoice::find()->where(['id' => $value])->one();
        $devuelta = false;
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $str = "<div class=\"container-fluid small\">
                   <form><input name='m_id' type='hidden' id='m_id' value='" . $value . "'/>
                        <div>
                            <div style='margin:20px 5px 8px 5px;text-decoration:underline;'><h5><strong>Seleccione los Productos y cantidades a devolver</strong></h5></div>";

        if ($invoice->status_hacienda == UtilsConstants::HACIENDA_STATUS_CREDIT_NOTE) {
            $str .= "<p>Esta orden ya fue devuelta</p>";
            $str .= "</div></form></div>";
            $devuelta = true;
        } else {
            $itemsInvoices = ItemInvoice::find()->where(['invoice_id' => $value])->all();
            $items = '';
            foreach ($itemsInvoices as $item) {
                $datacreditnote = CreditNote::find()->select('id')
                    ->where(['<>', 'status_hacienda', UtilsConstants::HACIENDA_STATUS_REJECTED])
                    ->andWhere(['<>', 'status_hacienda', UtilsConstants::HACIENDA_STATUS_CANCELLED])
                    ->andWhere(['<>', 'status_hacienda', UtilsConstants::HACIENDA_STATUS_ANULATE])
                    ->andWhere(['reference_number' => $invoice->key])
                    ->orderBy('id DESC')
                    ->all();
                if (!empty($datacreditnote)) {
                    foreach ($datacreditnote as $credit) {
                        $ids[] = $credit->id;
                    }
                    $dataitem = ItemCreditNote::find()->where(['credit_note_id' => $ids, 'product_id' => $item->product_id])->sum('quantity');

                    if (is_null($dataitem))
                        $quantity_devuelto = 0;
                    else
                        $quantity_devuelto = $dataitem;

                    $resto = $item->quantity - $quantity_devuelto;

                    if ($resto > 0)
                        $items .= "<label>
                                        <input name='product_x' type='checkbox' id='" . $item->product_id . "' value='" . $item->id . "'  />
                                        <input name='quantity_x' type=\"number\" min=\"1\" max=\"" . (int)$resto . "\" value='" . (int)$resto . "' id='x_" . $item->product_id . "' autocomplete='off' size='6' maxlength='2' style='font-size:11px;width:55px; max-height:10x; margin-right:5px;margin-right:5px;' />
                                        " . $item->description . "
                                    </label>
                                    <br />";
                } else {
                    //<input name='product_x' type='checkbox' id='" . $item->product_id . "' value='" . $item->id . "' checked='checked' />
                    $items .= "<label>
                    <input name='product_x' type='checkbox' id='" . $item->product_id . "' value='" . $item->id . "' />
                    <input name='quantity_x' type=\"number\" min=\"1\" max=\"" . (int)$item->quantity . "\" value='" . (int)$item->quantity . "' id='x_" . $item->product_id . "' autocomplete='off' size='6' maxlength='2' style='font-size:11px;width:55px; max-height:10x; margin-right:5px;margin-right:5px;' />
                        " . $item->description . "
                    </label>
                    <br />";
                }
            }
            if (empty($items))
                $devuelta = true;

            $str .= $items . "</div></form></div>";
        }
        $body = $str;
        return \Yii::$app->response->data = ['body' => $body, 'devuelta' => $devuelta];
    }

    public function actionPurchasereturn2($value, $mproduct, $met_print)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $invoice_id = $value;
        $result = false;
        $msg = '';
        $invoice = Invoice::find()->where(['id' => $invoice_id])->one();
        //232-1-6814 | 233-1-6815  primero es el id de item_invoice, el segundo es la cantidad devuelta y el tercero es el id del producto
        $listItems = explode('|', $mproduct);

        $listItemsDevueltos = [];
        $cant_item_invoice = ItemInvoice::find()->where(['invoice_id' => $invoice_id])->sum('quantity');
        if (is_null($cant_item_invoice))
            $cant_item_invoice = 0;
        else
            $cant_item_invoice = (int)$cant_item_invoice;

        $cant_item_devueltos = 0;
        if (count($listItems) > 0) {
            foreach ($listItems as $items) {
                $line = explode('-', $items);
                $item_invoice_id = $line[0];
                $item_count_devuelto = $line[1];
                $item_product_id = $line[2];
                $itemInvoice = ItemInvoice::find()->where(['id' => $item_invoice_id, 'invoice_id' => $invoice_id, 'product_id' => $item_product_id])->one();
                if ($itemInvoice->quantity < $item_count_devuelto) {
                    $msg = "Está intentando devolver: " . $item_count_devuelto . ' de ' . $itemInvoice->description . '. La cantidad a devolver es mayor que la cantidad que se facturó :' . $itemInvoice->quantity . 'Corrija la información e inténtelo nuevamente';
                    break;
                } else {
                    $cant_item_devueltos = $cant_item_devueltos + $item_count_devuelto;
                    $listItemsDevueltos[] = [
                        'item_invoice_id' => $item_invoice_id,
                        'item_count_invoice' => $itemInvoice->quantity,
                        'item_count_devuelto' => $item_count_devuelto,  // Aqui ya debo calcular la cantidad que debo poner en la nota de crédito
                        'item_product_id' => $item_product_id,
                        'item_name' => $itemInvoice->description,
                        'item_total' => $itemInvoice->getMontoTotalLinea(),
                    ];
                }
            }
        }

        if (!empty($listItemsDevueltos)) {
            //die(var_dump('item facturas = ').$cant_item_invoice.'  item devueltos = '.$cant_item_devueltos);
            $tipo_devolucion = UtilsConstants::DEVOLUCION_PARCIAL;

            //Si la factura no se ha enviado hacienda solo se ajusta la factura
            $data = $this->CrearDevolucionConNota($invoice, $listItemsDevueltos, $tipo_devolucion);

            $result = $data['result'];
            if ($result == true) {
                $creditNote = $data['creditNote'];

                $issuer = Issuer::find()->one();
                $str = "<table cellspacing='0' cellpadding='0' style='width: 58mm; max-width: 58mm !important; border: 1px solid #cccccc;font-family:Gotham, Helvetica, Arial, sans-serif; font-size:10px;margin: 0; text-align: center;' id='tg'>
                <tbody>
                  <tr>
                    <td style='font-size:30px;padding-top:10px;' colspan='3'>" . !empty($issuer->name) ? $issuer->name : 'Herbavi' . "</td>
                  </tr>
                  <tr>
                    <td colspan='3'>NIF: " . $issuer->identification . "</td>
                  </tr>
                  <tr>
                    <td colspan='3'>Ref. Fact. " . $invoice->consecutive . "</td>
                  </tr>
                  <tr>
                    <td colspan='3'>" . date('d-m-Y H:i:s', strtotime($invoice->emission_date)) . "</td>
                  </tr>
                  <tr>
                    <td style='padding-top:17px;font-weight:bold;' colspan='3'>DEVOLUCIO&#769;N DE COMPRA</td>
                  </tr>";
                $total_devuelto = 0;

                foreach ($listItemsDevueltos as $list) {
                    $monto_total_linea = 0;
                    if (!is_null($creditNote)) {
                        $itemCreditNote = ItemCreditNote::find()->where(['credit_note_id' => $creditNote->id, 'product_id' => $list['item_product_id']])->one();
                        if (!is_null($itemCreditNote)) {
                            $total_devuelto += $itemCreditNote->getMontoTotalLinea();
                            $monto_total_linea = $itemCreditNote->getMontoTotalLinea();
                            $fecha =  date('d-m-Y H:i:s', strtotime($creditNote->emission_date));
                        }
                    }
                    $str .= "<tr>
                        <td style='font-size: 16px;font-weight:bold;'>" . $list['item_name'] . "</td>
                        <td style='font-size: 16px;font-weight:bold;'>" . $list['item_count_devuelto'] . "</td>
                        <td style='font-size: 16px;font-weight:bold;'>" . $monto_total_linea . "</td>
                    </tr>";
                }
                $str .= "<tr>
                        <td style='font-size: 16px;font-weight:bold;' colspan='2'>Total</td>                        
                        <td style='font-size: 16px;font-weight:bold;'>¢" . GlobalFunctions::formatNumber($total_devuelto, 2) . "</td>
                    </tr>";
                $str .= "<tr>
                            <td style='padding-top:17px;padding-bottom:5px;' colspan='3'>Caja: " . $invoice->box->name . "</td>
                        </tr>
                        <tr>
                            <td colspan='3'>Fecha de la devolucio&#769;n: </td>
                        </tr>
                        <tr>
                            <td style='padding-bottom:5px;' colspan='3'> " . $fecha . " </td>
                        </tr>
                        <tr>
                            <td colspan='3'>Le atendio&#769;: </td>
                        </tr>
                        <tr>
                            <td style='padding-bottom:20px;' colspan='3'> " . SellerHasInvoice::getSellerStringByInvoice($invoice->id) . "</td>
                        </tr>
                        </tbody>
                    </table></div>";
            } else {
                $msg = 'No se pudo crear la devolución';
                $result = false;
            }
        }
        return \Yii::$app->response->data = ['msg' => $msg, 'result' => $result];
    }

    public function CrearDevolucionConAjuste($invoice, $itemsdevueltos, $tipo_devolucion)
    {
        $result = false;
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            if ($tipo_devolucion == UtilsConstants::DEVOLUCION_TOTAL) {
                MovementCashRegisterDetail::deleteAll(['invoice_id' => $invoice->id]);
                if ($invoice->delete()) {
                    $transaction->commit();
                    $result = true;
                }
            } else {
                foreach ($itemsdevueltos as $itemdevuelto) {
                    $item = ItemInvoice::find()->where(['invoice_id' => $invoice->id, 'product_id' => $itemdevuelto['item_product_id']])->one();
                    if (!is_null($item)) {
                        if ($item->quantity == $itemdevuelto['item_count_devuelto']) {
                            if ($item->delete()); {
                                $result = true;
                                $transaction->commit();
                            }
                        } else {
                            $item->quantity = $item->quantity - $itemdevuelto['item_count_devuelto'];

                            if ($item->save()) {
                                $result = true;
                                $transaction->commit();
                            }
                        }
                        // Actualizar los calculos de la factura
                        $datainvoice = Invoice::find()->where(['id' => $invoice->id])->one();
                        $datainvoice->save();
                    }
                }
            }
        } catch (Exception $e) {
            $result = false;
            $transaction->rollBack();
        }

        return [
            'result' => $result,
            'creditNote' => NULL,
        ];
    }

    public function CrearDevolucionConNota($invoice, $itemsdevueltos, $tipo_devolucion)
    {
        $result = true;
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $creditNote = new CreditNote;
            $creditNote->attributes = $invoice->attributes;
            $creditNote->status = UtilsConstants::INVOICE_STATUS_PENDING;
            $creditNote->status_hacienda = UtilsConstants::HACIENDA_STATUS_NOT_SENT;
            $creditNote->ready_to_send_email = 0;
            $creditNote->email_sent = 0;
            $creditNote->emission_date = date('Y-m-d H:i:s');
            $creditNote->response_xml = '';
            $creditNote->reference_number = $invoice->key;
            $creditNote->reference_emission_date = date('Y-m-d H:i:s');
            $creditNote->consecutive = $creditNote->generateConsecutive();

            $creditNote->credit_note_type = UtilsConstants::CREDIT_NOTE_TYPE_PARTIAL;
            $creditNote->reference_code = '03';
            $creditNote->reference_reason = 'Corrije documento de referencia';

            // Actualizar el estado de la factura
            //$invoice->status_hacienda = UtilsConstants::HACIENDA_STATUS_CREDIT_NOTE_PARTIAL;

            //BEGIN payment method has invoice
            $payment_methods_assigned = PaymentMethodHasInvoice::getPaymentMethodByInvoiceId($invoice->id);

            $payment_methods_assigned_ids = [];
            foreach ($payment_methods_assigned as $value) {
                $payment_methods_assigned_ids[] = $value['payment_method_id'];
            }

            $creditNote->payment_methods = $payment_methods_assigned_ids;
            //END payment method has invoice


            //BEGIN seller has seller
            $seller_assigned = SellerHasInvoice::getSellerByInvoiceId($invoice->id);

            $seller_assigned_ids = [];
            foreach ($seller_assigned as $value) {
                $seller_assigned_ids[] = $value['seller_id'];
            }

            $creditNote->sellers = $seller_assigned_ids;
            //END seller method has seller


            //BEGIN collector has collector
            $collector_assigned = CollectorHasInvoice::getCollectorByInvoiceId($invoice->id);

            $collector_assigned_ids = [];
            foreach ($collector_assigned as $value) {
                $collector_assigned_ids[] = $value['collector_id'];
            }

            $creditNote->collectors = $collector_assigned_ids;
            //END seller method has collector

            if ($creditNote->save()) {
                PaymentMethodHasCreditNote::updateRelation($creditNote, [], 'payment_methods', 'payment_method_id');

                SellerHasCreditNote::updateRelation($creditNote, [], 'sellers', 'seller_id');

                CollectorHasCreditNote::updateRelation($creditNote, [], 'collectors', 'collector_id');

                $result = true;
                $encontrado = false;
                $index = 0;
                foreach ($itemsdevueltos as $item) {

                    $items_invoice = ItemInvoice::find()->where(['invoice_id' => $invoice->id, 'product_id' => $item['item_product_id']])->one();

                    if (!is_null($items_invoice)) {
                        $new_item = new ItemCreditNote();
                        $new_item->attributes = $items_invoice->attributes;
                        $new_item->quantity = $item['item_count_devuelto'];
                        $new_item->credit_note_id = $creditNote->id;
                        $new_item->tax_rate_percent = $items_invoice->tax_rate_percent;
                        if (!$new_item->save()) {
                            $result = false;
                            $transaction->rollBack();
                            break;
                        }
                    }
                    $index++;
                }

                // Para que actualice los datos Calculados de la nota
                $datacreditNote = CreditNote::find()->where(['id' => $creditNote->id])->one();
                $datacreditNote->save();

                // Obtener los items de la nota de credito
                $cantItemNote = ItemCreditNote::find()->where(['credit_note_id' => $creditNote->id])->count('*');

                if ($cantItemNote > 0 && $creditNote->save()) {
                    $invoice->save();
                } else {
                    $result = false;
                }

                if ($result == true)
                    $transaction->commit();
            } //creditNote
        } catch (Exception $e) {
            $result = false;
            $transaction->rollBack();
        }

        return [
            'result' => $result,
            'creditNote' => $creditNote,
        ];
    }

    /*
    public function CrearDevolucion($invoice, $itemsdevueltos, $tipo_devolucion)
    {
        $result = true;
        $transaction = \Yii::$app->db->beginTransaction();
        try {        
                $creditNote = new CreditNote;
                $creditNote->attributes = $invoice->attributes;
                                                    
                $creditNote->credit_note_type = UtilsConstants::CREDIT_NOTE_TYPE_TOTAL;
                $creditNote->status = UtilsConstants::INVOICE_STATUS_PENDING;
                $creditNote->status_hacienda = UtilsConstants::HACIENDA_STATUS_NOT_SENT;
                $creditNote->ready_to_send_email = 0;
                $creditNote->email_sent = 0;
                $creditNote->emission_date = date('Y-m-d H:i:s');
                $creditNote->response_xml = '';
                $creditNote->reference_number = $invoice->key;
                $creditNote->reference_emission_date = date('Y-m-d H:i:s');
                $creditNote->consecutive = $creditNote->generateConsecutive();
                $creditNote->reference_code = '03';
                $creditNote->reference_reason = 'Corrije monto documento de referencia';

                //BEGIN payment method has invoice
                $payment_methods_assigned = PaymentMethodHasInvoice::getPaymentMethodByInvoiceId($invoice->id);

                $payment_methods_assigned_ids= [];
                foreach ($payment_methods_assigned as $value)
                {
                    $payment_methods_assigned_ids[]= $value['payment_method_id'];
                }

                $creditNote->payment_methods = $payment_methods_assigned_ids;
                if ($creditNote->save())
                {
                    PaymentMethodHasCreditNote::updateRelation($creditNote,[],'payment_methods','payment_method_id');

                    $items_associates = ItemInvoice::findAll(['invoice_id' => $invoice->id]);

                    foreach ($items_associates AS $index => $item)
                    {
                        // Buscar si la cantidad del item - las devoluciones > 0 entonces lo inserto
                        // Buscar todas las notas de crédito o devoluciones realizadas que tengan estado distinto HACIENDA_STATUS_REJECTED
                        $creditNotes = CreditNote::find()->select('id')
                                                        ->where(['<>', 'status_hacienda', UtilsConstants::HACIENDA_STATUS_REJECTED])
                                                        ->andWhere(['reference_number'=>$invoice->key])
                                                        ->asArray()
                                                        ->all(); 
                        $listIds = [];
                        foreach ($creditNotes as $n)                                
                            $listIds[] = $n['id'];
                        
                        $quantity_devoluciones = 0;                        
                        if (!empty($listIds))
                        {
                            // Si la suma de las cantidades de los items de la factura es igual a la suma de las cantidades de las notas de credito entonces devolución total
                            $quantity_devoluciones = ItemCreditNote::find()->where(['credit_note_id'=>$listIds, 'product_id'=>$item->product_id])->sum('quantity'); 
                            if (is_null($quantity_devoluciones))
                                $quantity_devoluciones = 0;
                        }
                        
                        $quantity_restante = $item->quantity - $quantity_devoluciones;
                        
                        // Ahora restarle la devolución
                        $encontrado = false;
                        $index = 0;
                        $canDevuelto = 0;

                        while (!$encontrado && $index < count($itemsdevueltos)){
                            if ($itemsdevueltos[$index]['item_product_id'] == $item->product_id){
                                $encontrado = true;
                                $canDevuelto = $itemsdevueltos[$index]['item_count_devuelto'];
                            }
                            $index++;                        
                        }

                        $quantity_restante = $quantity_restante - $canDevuelto;
                        if ($quantity_restante > 0)
                        {
                            $new_item = new ItemCreditNote();
                            $new_item->attributes = $item->attributes;    
                            $new_item->quantity = $quantity_restante;                    
                            $new_item->credit_note_id = $creditNote->id;
                            $new_item->tax_rate_percent = $item->tax_rate_percent;
                            if (!$new_item->save()){
                                $result = false;
                                $transaction->rollBack();
                                break;
                            }
                        }
                    } //foreach

                    $itemNote = ItemCreditNote::find()->where(['credit_note_id'=>$creditNote->id])->count('*');    
                    if (is_null($itemNote))
                        $itemNote = 0;

                    // Después de creada la nota entonces busque el tipo de devolución si es total o parcial y actualizar el estado en la nota            
                    if ($itemNote == 0) // Devolución total
                    {                        
                        $creditNote->credit_note_type = UtilsConstants::CREDIT_NOTE_TYPE_TOTAL;
                        $creditNote->reference_code = '01';
                        $creditNote->reference_reason = 'Anula documento de referencia';

                        $invoice->status_hacienda = UtilsConstants::HACIENDA_STATUS_CREDIT_NOTE;

                        // Si la devolución es total hay que anular la factura completa
                        foreach ($items_associates AS $index => $item){
                            $new_item = new ItemCreditNote();
                            $new_item->attributes = $item->attributes;    
                            $new_item->quantity = $item->quantity;                    
                            $new_item->credit_note_id = $creditNote->id;
                            $new_item->tax_rate_percent = $item->tax_rate_percent;
                            if (!$new_item->save()){
                                $result = false;
                                $transaction->rollBack();
                                break;
                            }
                        }
                    }
                    else
                    {
                        $creditNote->credit_note_type = UtilsConstants::CREDIT_NOTE_TYPE_PARTIAL;
                        $creditNote->reference_code = '03';
                        $creditNote->reference_reason = 'Corrije monto documento de referencia';

                        $invoice->status_hacienda = UtilsConstants::HACIENDA_STATUS_CREDIT_NOTE_PARTIAL;
                    }

                    // Obtener los items de la nota de credito
                    $cantItemNote = ItemCreditNote::find()->where(['credit_note_id'=>$creditNote->id])->count('*'); 

                    if ($cantItemNote > 0 && $creditNote->save())
                    {
                        $invoice->save();
                    }
                    else
                        $result = false;

                    if ($result == true)
                        $transaction->commit();   
                } //creditNote
            } catch (Exception $e) {
                $result = false;
                $transaction->rollBack();
            }     

        return [
            'result'=>$result,
            'creditNote'=>$creditNote,
        ];
    }
    */

    public function getTipoDevolucion($invoice)
    {
        $tipo = '';
        // Buscar todas las notas de crédito o devoluciones realizadas que tengan estado distinto HACIENDA_STATUS_REJECTED
        $creditNotes = CreditNote::find()->select('id')
            ->where(['<>', 'status_hacienda', UtilsConstants::HACIENDA_STATUS_REJECTED])
            ->andWhere(['reference_number' => $invoice->key])
            ->asArray()
            ->all();

        $listIds = [];
        foreach ($creditNotes as $n)
            $listIds[] = $n['id'];


        // Si la suma de las cantidades de los items de la factura es igual a la suma de las cantidades de las notas de credito entonces devolución total
        $quantity_devoluciones_invoice = ItemInvoice::find()->where(['invoice_id' => $invoice->id])->sum('quantity');
        if (is_null($quantity_devoluciones_invoice))
            $quantity_devoluciones_invoice = 0;
        $quantity_devoluciones = ItemCreditNote::find()->where(['credit_note_id' => $listIds])->sum('quantity');
        if (is_null($quantity_devoluciones))
            $quantity_devoluciones = 0;

        if ($quantity_devoluciones_invoice - $quantity_devoluciones == 0)
            $tipo = 'TOTAL';
        else
            $tipo = 'PARCIAL';
    }

    public function EnviarFacturaHacienda($invoice)
    {
        $emisor = Issuer::find()->one();

        // Si todas las validaciones son correctas, proceder al proceso
        // Logearse en la api y obtener el token;
        $apiAccess = new ApiAccess();
        $datos = $apiAccess->loginHacienda($emisor);

        $error = $datos['error'];
        if ($error == 0) {
            // Obtener el xml firmado electrónicamente
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

            // Enviar documento a hacienda
            $apiEnvioHacienda = new ApiEnvioHacienda();
            $datos = $apiEnvioHacienda->send($xmlFirmado, $apiAccess->token, $invoice, $emisor, $doc_type);
            // En $datos queda el mensaje de respuesta

            $respuesta = $datos['response'];
            $code = $respuesta->getHeaders()->get('http-code');
            $error = 0;
            if ($code == '202' || $code == '201' || $code == '200') {
                $mensaje = "La factura electrónica con clave: [" . $invoice->key . "] se recibió correctamente, queda pendiente la validación de esta y el envío de la respuesta de parte de Hacienda.";
                $invoice->status_hacienda = UtilsConstants::HACIENDA_STATUS_RECEIVED; // Recibido
                $invoice->save(false);
            } elseif ($code == '400') {
                $error = 1;
                $mensaje = utf8_encode($respuesta->getHeaders()->get('X-Error-Cause'));
            } else {
                $error = 1;
                $mensaje = "Ha ocurrido un error desconocido al enviar la factura electrónica con clave: [" . $invoice->key . "]. Póngase en contacto con el administrador del sistema";
            }
        } else {
            $error = 1;
            $mensaje = $datos['mensaje'];
        }
        $apiAccess->CloseSesion($apiAccess->token, $emisor);

        return ['error' => $error, 'mensaje' => $mensaje];
    }
}
