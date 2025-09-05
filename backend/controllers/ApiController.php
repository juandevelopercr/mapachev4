<?php

namespace backend\controllers;

use Yii;
use backend\models\support\ApiRequestLog;
use backend\models\support\ApiRequestLogSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\models\GlobalFunctions;
use yii\helpers\Url;
use yii\db\Exception;

use common\models\User;
use backend\models\business\Customer;
use backend\models\business\CustomerSearch;
use backend\models\nomenclators\PaymentMethod;
use backend\models\business\CollectorHasPurchaseOrder;
use backend\models\business\Product;
use backend\models\business\Service;
use backend\models\business\ItemPurchaseOrder;
use backend\models\business\ItemPurchaseOrderForm;
use backend\models\business\PurchaseOrder;
use backend\models\nomenclators\UnitType;
use backend\models\nomenclators\Currency;
use backend\models\business\PaymentMethodHasPurchaseOrder;
use backend\models\nomenclators\UtilsConstants;
use backend\components\ApiBCCR;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use yii\filters\auth\HttpBearerAuth;
use yii\helpers\ArrayHelper;
use yii\helpers\HtmlPurifier;
use yii\rest\ActiveController;
use yii\web\Response;

use backend\models\business\Invoice;
use backend\models\business\InvoiceAbonos;
use backend\models\nomenclators\Banks;
use backend\models\business\CollectorHasInvoice;
use backend\models\settings\Setting;
//PRUEBA


/**
 * ApiController implements the CRUD actions for ApiRequestLog model.
 */
class ApiController extends Controller
{

    public $enableCsrfValidation = false;

    /**
     * @var string Override in all child controller
     */
    public $modelClass = 'none';


    public function beforeAction($action)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return parent::beforeAction($action);
    }

    /**
     * Format response to JSON
     * @return array
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['contentNegotiator'] = [
            'class' => 'yii\filters\ContentNegotiator',
            'formats' => [
                'application/json' => Response::FORMAT_JSON,
            ]
        ];

        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::className(),
            'optional' => ["*"]
        ];

        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method, Authorization");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
        header("Allow: GET, POST, OPTIONS, PUT, DELETE");
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method == "OPTIONS") {
            die();
        }

        return $behaviors;
    }

    /**
     * @param string $action
     * @param null $model
     * @param array $params
     * @return bool|User|null|\yii\web\IdentityInterface
     */
    public function checkAccess($action, $model = null, $params = [])
    {
        if ($action == 'login') {
            return true;
        } else {
            return $this->validateUser();
        }
    }

    /**
     * Returns an array with request params send via POST, GET or RawBody
     * @return array|mixed|string
     */
    public function getRequestParamsAsArray()
    {
        $params = Yii::$app->request->post(); // Production mode

        try {
            $params = array_merge($params, Yii::$app->request->getQueryParams()); //Postman test mode
        } catch (\Exception $exception) {
            // Query Params is not an array
        }


        $raw = Yii::$app->request->getRawBody(); //Postman test mode
        try {
            $raw = json_decode($raw, true);
        } catch (\Exception $e) {
            $raw = [];
        }
        try {
            $params = array_merge($params, $raw);
        } catch (\Exception $exception) {
            // Raw is not an array
        }


        // Filter XSS Attacks
        try {
            foreach ($params as $key => $value) {
                if (is_array($value)) { // Avoid exception for purifier array
                    foreach ($value as $key2 => $value2) {
                        if (!is_array($value)) {
                            $value[$key2] = HtmlPurifier::process($value2);
                        }
                    }
                    $params[$key] = $value;
                } else {
                    $params[$key] = HtmlPurifier::process($value);
                }
            }
        } catch (\Exception $exception) {
            // Yii::error($exception->getMessage(), "WebFactory");
        }

        return $params;
    }

    /**
     * Return the access token provided by request
     * @return array|mixed|string
     */
    private function getAccessToken()
    {
        $access_token = Yii::$app->request->headers->get('access_token', null);
        if (!isset($access_token) || empty($access_token)) {
            $access_token = Yii::$app->request->headers->get('auth_key', null);
        }

        if (!isset($access_token) || empty($access_token)) {
            $access_token = Yii::$app->request->post('access_token', null);
        }
        if (!isset($access_token) || empty($access_token)) {
            $access_token = Yii::$app->request->post('auth_key', null);
        }
        if (!isset($access_token) || empty($access_token)) {
            $access_token = Yii::$app->request->getQueryParam('access_token', null);
        }
        if (!isset($access_token) || empty($access_token)) {
            $access_token = Yii::$app->request->getQueryParam('auth_key', null);
        }
        if (!isset($access_token) || empty($access_token)) {
            $raw_body = Yii::$app->request->getRawBody();
            $raw_body_array = json_decode($raw_body, true);
            $access_token = ArrayHelper::getValue($raw_body_array, "access_token", null);
            if (!isset($access_token) || empty($access_token)) {
                $access_token = ArrayHelper::getValue($raw_body_array, "auth_key", null);
            }
        }

        return HtmlPurifier::process($access_token);
    }

    /**
     * Return user if access token is valid
     * @return bool|null|User|\yii\web\IdentityInterface
     */
    public function validateUser()
    {
        $access_token = $this->getAccessToken();
        if (isset($access_token) && !empty($access_token)) {
            return User::findIdentityByAccessToken($access_token);
        }

        return false;
    }
    /**
     * Lists all ApiRequestLog models.
     * @return mixed
     */
    public function actionIndex()
    {
        echo "Api Controller";
    }

    public function actionLogin()
    {

        $response = [
            "success" => false,
            "data" => (object) [],
            "msg" => ""
        ];

        try {
            $data = $this->getRequestParamsAsArray();
            //var_dump($data);

            $username = isset($data["username"]) ? $data["username"] : null;
            $password = isset($data["password"]) ? $data["password"] : null;
            if ($username == null || $username == "") {
                $response["msg"] = "El parámetro username es requerido";
                return $response;
            }
            if ($password == null || $password == "") {
                $response["msg"] = "El parámetro password es requerido";
                return $response;
            }

            $user = User::findByUsername($username);
            if ($user) {

                if ($user->status === GlobalFunctions::STATUS_INACTIVE) {

                    $response["msg"] = "Usuario inactivo";
                }

                if (!$user->validatePassword($password)) {
                    $response["msg"] = "Contraseña incorrecta";
                } else {
                    $response["success"] = true;
                    $response["data"] = $user;
                }
            } else {
                $response["msg"] = "Usuario no existente";
            }


            return $response;
        } catch (\Throwable $th) {
            $response["more_msg"] = $th->getMessage();
            $response["msg"] = "Ocurrió un error";
            return $response;
        }


        // $username =$data;
        // User::findByUsername($this->username);
    }


    public function actionNewOrder()
    {

        $params = Yii::$app->request->queryParams;
        $userAuth = $this->checkAccess($this->action->id, null, $params);

        $response = [
            "success" => false,
            "data" => (object) [],
            "msg" => "",
            "auth" => false
        ];

        if (!$userAuth) {
            $response["msg"] = "Acceso inválido";
            return $response;
        }

        $response["auth"] = true;
        $response["success"] = true;

        $query = Customer::find()
            ->select(['customer.id','customer.pre_invoice_type', 'customer.name', 'customer.commercial_name', 'seller_has_customer.seller_id', 
                      'customer.route_transport_id', 'route_transport.name as route'])
            ->join('INNER JOIN', 'seller_has_customer', 'seller_has_customer.customer_id = customer.id')                      
            ->leftJoin('route_transport', 'route_transport.id = customer.route_transport_id')
            ->where(["seller_has_customer.seller_id" => $userAuth->id]);
        $customers = $query->asArray()->all();    
        $customers = array_map(function ($el) {
            $el["max_items"] = ($el["pre_invoice_type"] == UtilsConstants::PRE_INVOICE_TYPE_INVOICE) ? Setting::getLineNumInvoice() : Setting::getLineNumTicket();

            $el["id"] = intval($el["id"]);
            $el["seller_id"] = intval(($el["seller_id"]));
            $el["route_transport_id"] = intval($el["route_transport_id"]);
            $el["pre_invoice_type"] = intval($el["pre_invoice_type"]);
            return $el;
        },$customers);    

        $products = $this->getProductsAndServices(false);
        $units = $this->getUnits(true, true);

        $model = new PurchaseOrder();
        $model->consecutive = $model->generateConsecutive();

        $response["data"]->consecutive = $model->consecutive;

        $response["data"]->customers = $customers;
        $response["data"]->products = $products;
        $response["data"]->units = $units;
        
        return $response;
    }

    public function getProductsAndServices($show_code)
    {
        $array_map = [];

        //Agregar todos los productos
        $products = Product::find()->select(['id', 'description', 'bar_code'])->asArray()->all();

        if (count($products) > 0) {
            foreach ($products as $index => $product) {
                if ($show_code) {
                    $array_map['P-' . $product['id']] = $product['bar_code'] . ' - ' . $product['description'];
                } else {
                    $array_map['P-' . $product['id']] = $product['description'];
                }
            }
        }

        //Agregar todos los servicios
        // $services = Service::find()->select(['service.id', 'service.name', 'service.code'])->asArray()->all();

        // if (count($services) > 0) {
        //     foreach ($services as $key => $service) {
        //         if ($show_code) {
        //             $array_map['S-' . $service['id']] = $service['code'] . ' - ' . $service['name'];
        //         } else {
        //             $array_map['S-' . $service['id']] = $service['name'];
        //         }
        //     }
        // }

        return $array_map;
    }

    public function getUnits($check_status = false, $only_code = false, $simplify = true)
    {
        $query = UnitType::find();
        if ($check_status) {
            $query->where(['status' => UnitType::STATUS_ACTIVE]);
        }

        if ($simplify) {
            $query->andWhere(['IN', 'code', ['PAQ', 'Unid', 'BULT', 'CAJ']]);
        }

        $models = $query->asArray()->all();

        $array_map = [];

        if (count($models) > 0) {
            foreach ($models as $index => $model) {
                if ($only_code) {
                    $array_map[$model['id']] = $model['code'];
                } else {
                    $array_map[$model['id']] = $model['code'] . ' - ' . $model['name'];
                }
            }
        }

        return $array_map;
    }

    public function actionGetPriceTypes()
    {

        $params = Yii::$app->request->queryParams;
        $userAuth = $this->checkAccess($this->action->id, null, $params);

        $body = Yii::$app->request->bodyParams;

        $response = [
            "success" => false,
            "data" => (object) [],
            "msg" => "",
            "auth" => false,
            "errors" => (object) []
        ];

        //Autenticación
        if (!$userAuth) {
            $response["msg"] = "Acceso inválido";
            return $response;
        }

        $response["auth"] = true;
        //Fin autenticación

        $code = isset($body["code"]) ? $body["code"] : null;

        if ($code == null) {
            $response["msg"] = "Parámetros faltantes";
            $response["errors"]->code = "El parámetro code es requerido";
            return $response;
        }

        $strings = explode("-", $code);
        if (!isset($strings[1])) {
            $response["msg"] = "Parámetros faltantes";
            $response["errors"]->code = "El parámetro code no tiene estructura correcta";
            return $response;
        }

        $id = $strings[1];
        $model_found = false;

        if ($strings[0] == 'P') {

            $model =  Product::findOne($id);

            if ($model != null) {
                $model_found = true;

                $default_unid = UnitType::findOne(['code' => 'Unid']);
                $default_unid_id = ($default_unid !== null) ? $default_unid->id : null;


                $response["data"] = (object) [
                    'bar_code' => $model->bar_code,
                    'unit_type_id' => (isset($model->unit_type_id) && !empty($model->unit_type_id)) ? $model->unit_type_id : $default_unid_id
                ];
                $response["success"] = true;
            } else {
                $response["msg"] = "No se encontró el producto";
            }
        } else if ($strings[0] == 'S') {
            //Servicio
            $model =  Service::findOne($id);

            if ($model != null) {
                $model_found = true;
                $default_unid = UnitType::findOne(['code' => 'Unid']);
                $default_unid_id = ($default_unid !== null) ? $default_unid->id : null;

                $response["data"] = (object) [
                    'bar_code' => $model->bar_code,
                    'unit_type_id' => $default_unid_id
                ];

                $response["success"] = true;
            } else {
                $response["msg"] = "No se encontró el servicio";
            }
        }

        if ($model_found) {

            $list = UtilsConstants::getPriceTypeSelectByProduct(null, false, $code);

            $response["data"]->price_types = [];
            $response["data"]->selected_price_type = null;

            if ($code != null && count($list) > 0) {

                foreach ($list as $key => $value) {
                    $out[] = ['id' => $key, 'name' => $value];
                }

                $response["data"]->price_types = $out;
                $response["data"]->selected_price_type = UtilsConstants::CUSTOMER_ASSIGN_PRICE_1;
            }
        }

        return $response;
    }

    public function actionGetItemAmount()
    {

        $params = Yii::$app->request->queryParams;
        $userAuth = $this->checkAccess($this->action->id, null, $params);

        $body = Yii::$app->request->bodyParams;
        $item_error = false;

        $response = [
            "success" => false,
            "data" => (object) [],
            "msg" => "",
            "auth" => false,
            "errors" => (object) []
        ];

        //Autenticación
        if (!$userAuth) {
            $response["msg"] = "Acceso inválido";
            return $response;
        }

        $response["auth"] = true;
        //Fin autenticación

        try {

            if (!isset($body["product_service"]) || $body["product_service"] == null) {
                $response["errors"]->product_service = "El parámetro product_service es requerido";
            }

            if (!isset($body["quantity"]) || $body["quantity"] == null) {
                $response["errors"]->quantity = "El parámetro quantity es requerido";
            } else {
                if (!is_numeric($body["quantity"])) {
                    $response["errors"]->quantity = "El parámetro quantity debe ser numérico";
                }
            }

            if (!isset($body["price_type"]) || $body["price_type"] == null) {
                $response["errors"]->price_type = "El parámetro price_type es requerido";
            } else {
                if (!is_numeric($body["price_type"])) {
                    $response["errors"]->price_type = "El parámetro price_type debe ser numérico";
                }
            }

            if (!isset($body["unit_type_id"]) || $body["unit_type_id"] == null) {
                $response["errors"]->unit_type_id = "El parámetro unit_type_id es requerido";
            } else {
                if (!is_numeric($body["unit_type_id"])) {
                    $response["errors"]->unit_type_id = "El parámetro unit_type_id debe ser numérico";
                }
            }

            if (is_object($response["errors"]) && count(get_object_vars($response["errors"])) > 0) {
                $response["msg"] = "Ocurrió un error con los parámetros enviados";
                return $response;
            }


            $product_service_id = $body['product_service'];
            $quantity_label = $price_type_label = '';
            $explode = explode('-', $product_service_id);
            $model_reference = null;
            $item_model = null;

            if (isset($product_service_id) && !empty($product_service_id)) {

                if ($explode[0] == 'P') {

                    if (is_numeric($explode[1])&& is_integer((int) $explode[1])) {
                        $model_reference = Product::findOne($explode[1]);

                        if ($model_reference != null) {
                            $item_model = new ItemPurchaseOrder;
                            $item_model->unit_type_id = (isset($body['unit_type_id']) && !empty($body['unit_type_id'])) ? $body['unit_type_id'] : $model_reference->unit_type_id;
                            $item_model->quantity = $body['quantity'];
                            $request_quantity = $item_model->quantity;
                            $item_model->price_type = (isset($body['price_type']) && !empty($body['price_type'])) ? $body['price_type'] : UtilsConstants::CUSTOMER_ASSIGN_PRICE_1;

                            if (isset($item_model->price_type)) {
                                $price_type_label = UtilsConstants::getPriceTypeMiniLabel($item_model->price_type);
                                $item_model->price_unit = $model_reference->getPriceByTypeAndUnitType($item_model->price_type, $item_model->unit_type_id);
                            }

                        } else {
                            $item_error = true;
                            $response["msg"] = "No se encontró un producto con ese identificador";
                        }
                    } else {
                        $item_error = true;
                        $response["msg"] = "El identificador del producto no es entero";
                    }
                } elseif ($explode[0] == 'S') {

                    if (is_numeric($explode[1])&&is_integer($explode[1])) {
                        $model_reference = Service::findOne($explode[1]);

                        if ($model_reference != null) {
                            $item_model = new ItemPurchaseOrder;
                            $item_model->service_id = $explode[1];
                            $item_model->quantity = $body['quantity'];
                            $request_quantity = $item_model->quantity;
                            $item_model->price_unit = $model_reference->price;
                        } else {
                            if (!$item_error) $response["msg"] = "No se encontró un servicio con ese identificador";
                        }
                    } else {
                        $response["msg"] = "El identificador del producto no es entero"; 
                    }
                } else {
                    $response["msg"] = "El item no es servicio ni producto"; 
                }
            }

            if ($model_reference !== null && $item_model != null) {
                
                //$model->quantity = $array_posted['quantity'];
                $item_model->unit_type_id = $body['unit_type_id'];

                $percent_iva = $model_reference->getPercentIvaToApply();


                if ($explode[0] == 'S') {
                    $item_model->discount_amount = $model_reference->getDiscount();
                } else {
                    $item_model->discount_amount = $model_reference->getDiscount();
                }

                // Se aplica el descuento a nivel de producto o servicio

                $subtotal = $item_model->price_unit * $request_quantity - $item_model->discount_amount;
                $item_model->subtotal = (isset($subtotal) && !empty($subtotal)) ? $subtotal : 0;

                $tax_calculate = $subtotal * ($percent_iva / 100);
                $tax = (isset($tax_calculate) && !empty($tax_calculate)) ? $tax_calculate : 0;

                $exonerated = $tax * ($model_reference->exoneration_purchase_percent / 100);
                $exonerated_tax_amount = (isset($exonerated) && !empty($exonerated)) ? $exonerated : 0;                

                $item_model->tax_amount = $tax;
                $item_model->tax_rate_percent = $model_reference->tax_rate_percent;                
                $item_model->price_total = $subtotal + $tax - $exonerated_tax_amount;

                $item = [
                    "price_unit" => $item_model->price_unit, //precio unidad (calculado)
                    "price_unit_label" => GlobalFunctions::formatNumber($item_model->price_unit,2), //precio unidad (calculado)
                    "discount_amount" => $item_model->discount_amount,
                    "discount_amount_label" => GlobalFunctions::formatNumber($item_model->discount_amount,2),                   
                    
                    "tax_amount" => $item_model->tax_amount,//IVA
                    "tax_amount_label" => GlobalFunctions::formatNumber($item_model->tax_amount,2),
                    "exonerated_tax_amount" => $exonerated_tax_amount,
                    "exonerated_tax_amount_label" =>  GlobalFunctions::formatNumber($exonerated_tax_amount,2),                    
                    "price_total" => $item_model->price_total,
                    "price_total_label" => GlobalFunctions::formatNumber($item_model->price_total,2),
                    
                    
                ];
                $response["success"] = true;
                $response["data"] = $item;

            }

            return $response;
        } catch (\Exception $e) {
            //throw $th;
            $response["msg"] = "Ocurrió un error";
            $response["msg_detail"] = $e->getMessage();
            return $response;
        }
    }

    public function actionSaveOrder()
    {

        $params = Yii::$app->request->queryParams;
        $userAuth = $this->checkAccess($this->action->id, null, $params);

        $body = Yii::$app->request->bodyParams;

        $response = [
            "success" => false,
            "data" => (object) [],
            "msg" => "",
            "auth" => false,
            "errors" => (object) []
        ];

        //Autenticación
        if (!$userAuth) {
            $response["msg"] = "Acceso inválido";
            return $response;
        }

        $response["auth"] = true;
        //Fin autenticación

        try {
            $order = isset($body["order"]) ? $body["order"] : null;

            if ($order == null) {
                $response["msg"] = "Parámetros faltantes";
                $response["errors"]->order = "El parámetro order es requerido";
                return $response;
            }


            if (!isset($order["customer_id"]) || $order["customer_id"] == null) {
                $response["errors"]->customer_id = "El parámetro customer_id es requerido";
            } else {
                if (!is_numeric($order["customer_id"])) {
                    $response["errors"]->customer_id = "El parámetro customer_id debe ser numérico";
                }
            }

            if(isset($response["errors"]->customer_id)){
                $response["msg"] = "Ocurrió un error con los parámetros enviados";
                return $response;
            }

            //Segun cliente
            $customer = Customer::findOne($order["customer_id"]);
            /* $response["custom"] = $customer["pre_invoice_type"];
            return $response; */
            if ($customer == null) {
                $response["msg"] = "No se encontró el cliente";
                return $response;
            }
            
            $invoice_type = $customer["pre_invoice_type"];
            $maxNumItems = ($invoice_type == UtilsConstants::PRE_INVOICE_TYPE_INVOICE) ? Setting::getLineNumInvoice() : Setting::getLineNumTicket();


            if (!isset($order["items"]) || $order["items"] == null || $order["items"] == []) {
                $response["errors"]->items = "El parámetro items es requerido";
            } else {

                //Validar los items
                $items = $order["items"];

                if(sizeof($items)>$maxNumItems){
                    $response["errors"]->items = "El parámetro items supera el máximo ".$maxNumItems;
                }else{

                    foreach ($items as $key => $item) {
    
                        if (!isset($item["product_service"]) || $item["product_service"] == null) {
                            $response["errors"]->items = "Item " . $key . " : El parámetro product_service es requerido";
                        }
    
                        if (!isset($item["quantity"]) || $item["quantity"] == null) {
                            $response["errors"]->items = "Item " . $key . " : El parámetro quantity es requerido";
                        } else {
                            if (!is_numeric($item["quantity"])) {
                                $response["errors"]->items = "Item " . $key . " : El parámetro quantity debe ser numérico";
                            }
                        }
    
                        if (!isset($item["price_type"]) || $item["price_type"] == null) {
                            $response["errors"]->items = "Item " . $key . " : El parámetro price_type es requerido";
                        } else {
                            if (!is_numeric($item["price_type"])) {
                                $response["errors"]->items = "Item " . $key . " : El parámetro price_type debe ser numérico";
                            }
                        }
    
                        if (!isset($item["unit_type_id"]) || $item["unit_type_id"] == null) {
                            $response["errors"]->items = "Item " . $key . " : El parámetro unit_type_id es requerido";
                        } else {
                            if (!is_numeric($item["unit_type_id"])) {
                                $response["errors"]->items = "Item " . $key . " : El parámetro unit_type_id debe ser numérico";
                            }
                        }
                    }

                }
            }

            if (is_object($response["errors"]) && count(get_object_vars($response["errors"])) > 0) {
                $response["msg"] = "Ocurrió un error con los parámetros enviados";
                return $response;
            }

            
            //Crear orden

            $model = new PurchaseOrder();
            $model->loadDefaultValues();
            $model->is_editable = 1;
            $model->consecutive = $model->generateConsecutive();
            $model->scenario = 'create';
            $model->status = UtilsConstants::PURCHASE_ORDER_STATUS_STARTED;
            $model->change_type = ApiBCCR::getChangeTypeOfIssuer();
            

            $data = PaymentMethod::getSelectMap(false, '01');
            $defaulPayment = [];
            foreach ($data as $key => $value) {
                $defaulPayment[] = $key;
            }
            $model->payment_methods = $defaulPayment;

            $currency = Currency::findOne(['symbol' => 'CRC']);
            if ($currency !== null) {
                $model->currency_id = $currency->id;
            }
            $model->request_date = date('Y-m-d');

            $model->customer_id = $order["customer_id"];
            $model->branch_office_id = $userAuth->branch_office_id;
            $model->box_id = 1;//$userAuth->box_id;
            $model->observations = @$order["observations"];


            //segun cliente:
            $model->invoice_type = $invoice_type;
            $model->route_transport_id = $customer->route_transport_id;
            //$model->collector_id = $userAuth->id;
            $model->collectors[] = $userAuth->id;
            $model->condition_sale_id = $customer->condition_sale_id;

            //Segun , fijos            
            $model->delivery_time = '8';
            $model->delivery_time_type = 1;
            $model->discount_percent = null;
            $model->credit_days_id = null;


            if ($model->save()) {
                //Asignar métodos de pago
                PaymentMethodHasPurchaseOrder::updateRelation($model, [], 'payment_methods', 'payment_method_id');

                CollectorHasPurchaseOrder::updateRelation($model, [], 'collectors', 'collector_id');                

                $items_success = 0;

                $errors_items = [];
                //Crear items 

                foreach ($order["items"] as $key => $item) {

                    $item_error = false;

                    $product_service_id = $item['product_service'];
                    $quantity_label = $price_type_label = '';
                    $explode = explode('-', $product_service_id);
                    $model_reference = null;
                    $item_model = null;

                    if (isset($product_service_id) && !empty($product_service_id)) {

                        if ($explode[0] == 'P') {

                            if (is_numeric($explode[1])&& is_integer((int) $explode[1])) {
                                $model_reference = Product::findOne($explode[1]);

                                if ($model_reference != null) {
                                    $item_model = new ItemPurchaseOrder;
                                    $item_model->product_id = $explode[1];
                                    $item_model->code = $model_reference->code;
                                    $item_model->unit_type_id = (isset($item['unit_type_id']) && !empty($item['unit_type_id'])) ? $item['unit_type_id'] : $model_reference->unit_type_id;
                                    $item_model->user_id = $userAuth->id;
                                    $item_model->purchase_order_id = $model->id;
                                    $item_model->quantity = $item['quantity'];
                                    $request_quantity = $item_model->quantity;
                                    $item_model->price_type = (isset($item['price_type']) && !empty($item['price_type'])) ? $item['price_type'] : UtilsConstants::CUSTOMER_ASSIGN_PRICE_1;

                                    if (isset($item_model->price_type)) {
                                        $price_type_label = UtilsConstants::getPriceTypeMiniLabel($item_model->price_type);
                                        $item_model->price_unit = $model_reference->getPriceByTypeAndUnitType($item_model->price_type, $item_model->unit_type_id);
                                    }

                                    $item_model->description = $model_reference->description . ' <b>' . $price_type_label . ' ' . $quantity_label . '</b>';
                                } else {
                                    $item_error = true;
                                    array_push($errors_items, "Item " . $key . ": No se encontró un producto con ese identificador");
                                }
                            } else {
                                $item_error = true;
                                array_push($errors_items, "Item " . $key . ": El identificador del producto no es entero");
                            }
                        } elseif ($explode[0] == 'S') {

                            if (is_numeric($explode[1])&& is_integer($explode[1])) {
                                $model_reference = Service::findOne($explode[1]);

                                if ($model_reference != null) {
                                    $item_model = new ItemPurchaseOrder;
                                    $item_model->service_id = $explode[1];
                                    $item_model->code = $model_reference->code;
                                    $item_model->description = $model_reference->name;
                                    $item_model->quantity = $item['quantity'];
                                    $request_quantity = $item_model->quantity;
                                    $item_model->price_unit = $model_reference->price;
                                } else {
                                    if (!$item_error) array_push($errors_items, "Item " . $key . ": No se encontró un servicio con ese identificador");
                                }
                            } else {
                                array_push($errors_items, "Item " . $key . ": El identificador del producto no es entero");
                            }
                        } else {
                            if (!$item_error) array_push($errors_items, "Item " . $key . ": El item no es servicio ni producto");
                        }
                    }

                    if ($model_reference !== null && $item_model != null) {
                        $item_model->purchase_order_id = $model->id;
                        //$model->quantity = $array_posted['quantity'];
                        $item_model->unit_type_id = (isset($item['unit_type_id']) && !empty($item['unit_type_id'])) ? $item['unit_type_id'] : $model_reference->unit_type_id;

                        $percent_iva = $model_reference->getPercentIvaToApply();


                        if ($explode[0] == 'S') {
                            $item_model->discount_amount = $model_reference->getDiscount();
                            $item_model->nature_discount = $model_reference->nature_discount;
                        } else {
                            $item_model->discount_amount = $model_reference->getDiscount();
                            $item_model->nature_discount = $model_reference->nature_discount;
                        }
                        if (is_null($item_model->nature_discount) || empty($item_model->nature_discount))
                            $item_model->nature_discount = '-';

                        // Se aplica el descuento a nivel de producto o servicio

                        $subtotal = $item_model->price_unit * $request_quantity - $item_model->discount_amount;
                        $item_model->subtotal = (isset($subtotal) && !empty($subtotal)) ? $subtotal : 0;

                        $tax_calculate = $subtotal * ($percent_iva / 100);
                        $tax = (isset($tax_calculate) && !empty($tax_calculate)) ? $tax_calculate : 0;

                        $exonerated = $tax * ($model_reference->exoneration_purchase_percent / 100);
                        $exonerated_tax_amount = (isset($exonerated) && !empty($exonerated)) ? $exonerated : 0;
                        $item_model->exonerate_amount = $exonerated_tax_amount;
                        $item_model->exoneration_purchase_percent = (int) $model_reference->exoneration_purchase_percent;
                        $item_model->exoneration_document_type_id = $model_reference->exoneration_document_type_id;
                        $item_model->number_exoneration_doc = $model_reference->number_exoneration_doc;
                        $item_model->name_institution_exoneration = $model_reference->name_institution_exoneration;
                        $item_model->exoneration_date = $model_reference->exoneration_date;

                        $item_model->tax_amount = $tax;
                        $item_model->tax_rate_percent = $model_reference->tax_rate_percent;
                        $item_model->tax_type_id = $model_reference->tax_type_id;
                        $item_model->tax_rate_type_id = $model_reference->tax_rate_type_id;
                        $item_model->price_total = $subtotal + $tax - $exonerated_tax_amount;
                    }

                    if ($item_model != null && $item_model->save()) {
                        //Actualizar los totales de la factura
                        $invoice = PurchaseOrder::find()->where(['id' => $item_model->purchase_order_id])->one();
                        $invoice->save(false);
                        $item_model->refresh();
                        $items_success++;
                    } else {
                        if (!$item_error) array_push($errors_items, "Item " . $key . ": Ha ocurrido un error al intentar crear el registro." . (string) $item_model->errors);
                    }
                }

                if ($items_success == count($order["items"])) {

                    $response["success"] = true;
                    $response["data"]->id = $model->id;
                    $response["msg"] = "Orden creada";
                } else {

                    $model->delete();
                    $response["errors"]->items = $errors_items;

                    $response["msg"] = "No se pudo crear la orden";
                }
            } else {
                $response["errors"]->orden = $model->errors;

                $response["msg"] = "No se pudo crear la orden";
            }

            return $response;
        } catch (\Exception $e) {
            //throw $th;
            $response["msg"] = "Ocurrió un error";
            $response["msg_detail"] = $e->getMessage();
            return $response;
        }
    }

    public function actionListOrders(){
        $params = Yii::$app->request->queryParams;
        $userAuth = $this->checkAccess($this->action->id, null, $params);

        $body = Yii::$app->request->bodyParams;

        $response = [
            "success" => false,
            "data" => [],
            "msg" => "",
            "auth" => false,
            "errors" => (object) []
        ];

        //Autenticación
        if (!$userAuth) {
            $response["msg"] = "Acceso inválido";
            return $response;
        }

        $response["auth"] = true;
        //Fin autenticación

        //Listar las de hoy
        $query = PurchaseOrder::find()
        ->select(['purchase_order.id','purchase_order.consecutive','purchase_order.request_date','customer.commercial_name as customer','purchase_order.status','purchase_order.customer_id'])
        ->leftJoin('customer', 'customer.id = purchase_order.customer_id')
        ->join('INNER JOIN', 'collector_has_purchase_order', 'collector_has_purchase_order.purchase_order_id = purchase_order.id')
        ->where(["collector_has_purchase_order.collector_id" => $userAuth->id,"purchase_order.request_date"=>date('Y-m-d')])
        ->orderBy('purchase_order.id DESC');   
        
        $query_orders = $query->asArray()->all();
        
        $count_today = count($query_orders);
        $max_default = 30;

        if($count_today<$max_default){
            //Listar ultimas 30
            $query = PurchaseOrder::find()
            ->select(['purchase_order.id','purchase_order.consecutive','purchase_order.request_date','customer.commercial_name as customer','purchase_order.status','purchase_order.customer_id'])
            ->leftJoin('customer', 'customer.id = purchase_order.customer_id')
            ->join('INNER JOIN', 'collector_has_purchase_order', 'collector_has_purchase_order.purchase_order_id = purchase_order.id')
            ->where(["collector_has_purchase_order.collector_id" => $userAuth->id])
            ->orderBy('purchase_order.id DESC')->limit($max_default);              
            
            $query_orders = $query->asArray()->all();
        }

        $count = count($query_orders);
        $orders = [];
        foreach ($query_orders as $key => $order) {

            $_order = $order;
            $_order = [
                "id" => intval($order["id"]),
                "consecutive" => $order["consecutive"],
                "request_date" => $order["request_date"],
                "status" => intval($order["status"]),
                "customer" => $order["customer"],
                "customer_id" => isset($order["customer_id"])?intval($order["customer_id"]):null
            ];
            
            
            $resume = PurchaseOrder::getResumePurchaseOrder($order["id"]);
            $_order["subtotal"] = GlobalFunctions::formatNumber($resume->subtotal,2);
            $_order["discount_amount"] = GlobalFunctions::formatNumber($resume->discount_amount,2);
            $_order["tax_amount"] = GlobalFunctions::formatNumber($resume->tax_amount,2);
            $_order["exonerate_amount"] = GlobalFunctions::formatNumber($resume->exonerate_amount,2);
            $_order["price_total"] = GlobalFunctions::formatNumber($resume->price_total,2);
            $_order["price_total"] = GlobalFunctions::formatNumber($resume->price_total,2);
            
            $items_query = ItemPurchaseOrder::find()
            ->where(["purchase_order_id"=>$order['id']])->all();

            $items = [];
            foreach ($items_query as $item) {
                $_item = [
                    "id" => $item->id,
                    "description" => str_replace('</b>','',str_replace('<b>',' - ',$item->description) ),
                    "product_id" => $item->product_id,
                    "product_service" => $item->product_id!=null?'P-'.$item->product_id:'S-'.$item->service_id,
                    "quantity" => $item->quantity,
                    "quantity_label" => GlobalFunctions::formatNumber($item->quantity,2) ,
                    "unit_type_id" => $item->unit_type_id,
                    "unit_type_label" => $item->unitType->code,
                    "price_type" => $item->price_type,
                    "price_type_label" => UtilsConstants::getCustomerAsssignPriceSelectType($item->price_type),
                    "price_unit" => $item->price_unit,
                    "subtotal" => $item->subtotal,
                    "tax_amount" => $item->tax_amount,
                    "discount_amount" => $item->discount_amount,
                    "exonerate_amount" => $item->exonerate_amount,
                    "price_total" => $item->price_total,
                    "subtotal_label" => GlobalFunctions::formatNumber($item->subtotal,2),
                    "tax_amount_label" => GlobalFunctions::formatNumber($item->tax_amount,2),
                    "discount_amount_label" => GlobalFunctions::formatNumber($item->discount_amount,2),
                    "exonerate_amount_label" => GlobalFunctions::formatNumber($item->exonerate_amount,2),
                    "price_total_label" => GlobalFunctions::formatNumber($item->price_total,2),                                        
                ];
                array_push($items,$_item);
            }
            $_order["items"] =$items;
            array_push($orders,$_order);
        }

        $response["success"] = true;
        $response["data"] = $orders;
        $response["count_today"] = $count_today;
        $response["count"] = $count;
        return $response;

    }

    public function actionUpdateOrder(){

        $params = Yii::$app->request->queryParams;
        $userAuth = $this->checkAccess($this->action->id, null, $params);

        $body = Yii::$app->request->bodyParams;

        $response = [
            "success" => false,
            "data" => (object) [],
            "msg" => "",
            "auth" => false,
            "errors" => (object) []
        ];

        //Autenticación
        if (!$userAuth) {
            $response["msg"] = "Acceso inválido";
            return $response;
        }

        $response["auth"] = true;
        //Fin autenticación

        try {
            $order_id = isset($body["order_id"]) ? $body["order_id"] : null;
            $items = isset($body["items"]) ? $body["items"] : null;

            if ($order_id == null) {
                $response["msg"] = "Parámetros faltantes";
                $response["errors"]->order = "El parámetro order es requerido";
                return $response;
            }


            if (!isset($order_id) || $order_id == null) {
                $response["errors"]->order_id = "El parámetro order_id es requerido";
            } else {
                if (!is_numeric($order_id)) {
                    $response["errors"]->order_id = "El parámetro order_id debe ser numérico";
                }
            }

            if (!isset($items) || $items == null || $items == []) {
                $response["errors"]->items = "El parámetro items es requerido";
            } else {

                //Validar los items
                

                foreach ($items as $key => $item) {

                    if (!isset($item["product_service"]) || $item["product_service"] == null) {
                        $response["errors"]->items = "Item " . $key . " : El parámetro product_service es requerido";
                    }

                    if (!isset($item["quantity"]) || $item["quantity"] == null) {
                        $response["errors"]->items = "Item " . $key . " : El parámetro quantity es requerido";
                    } else {
                        if (!is_numeric($item["quantity"])) {
                            $response["errors"]->items = "Item " . $key . " : El parámetro quantity debe ser numérico";
                        }
                    }

                    if (!isset($item["price_type"]) || $item["price_type"] == null) {
                        $response["errors"]->items = "Item " . $key . " : El parámetro price_type es requerido";
                    } else {
                        if (!is_numeric($item["price_type"])) {
                            $response["errors"]->items = "Item " . $key . " : El parámetro price_type debe ser numérico";
                        }
                    }

                    if (!isset($item["unit_type_id"]) || $item["unit_type_id"] == null) {
                        $response["errors"]->items = "Item " . $key . " : El parámetro unit_type_id es requerido";
                    } else {
                        if (!is_numeric($item["unit_type_id"])) {
                            $response["errors"]->items = "Item " . $key . " : El parámetro unit_type_id debe ser numérico";
                        }
                    }
                }
            }


            //Crear orden           
            if (is_object($response["errors"]) && count(get_object_vars($response["errors"])) > 0) {
                $response["msg"] = "Ocurrió un error con los parámetros enviados";
                return $response;
            }
           
            $model = PurchaseOrder::findOne($order_id);
            if(!$model|| $model==null){
                $response["errors"]->order = "No existe una orden con ese id";
                $response["msg"] = "No existe una orden con ese id";
                return $response;
            }

            //BEGIN collector has collector
            $collector_assigned = CollectorHasPurchaseOrder::getCollectorByPurchaseOrderId($model->id);
            if (empty($collector_assigned))
            {
                $collector_assigned_ids[] = $userAuth->id;
            }
            else{
                $collector_assigned_ids = [];
                foreach ($collector_assigned as $value) {
                    $collector_assigned_ids[] = $value['collector_id'];
                }
            }

            $model->collectors = $collector_assigned_ids;
            //END seller method has collector 



            if ($model) {
                
                CollectorHasPurchaseOrder::updateRelation($model, $collector_assigned, 'collectors', 'collector_id');

                //Borrar todos los items
                ItemPurchaseOrder::deleteAll('purchase_order_id = '.$model->id);

                $items_success = 0;

                $errors_items = [];
                //Crear items 

                foreach ($items as $key => $item) {

                    $item_error = false;

                    $product_service_id = $item['product_service'];
                    $quantity_label = $price_type_label = '';
                    $explode = explode('-', $product_service_id);
                    $model_reference = null;
                    $item_model = null;

                    if (isset($product_service_id) && !empty($product_service_id)) {

                        if ($explode[0] == 'P') {

                            if (is_numeric($explode[1])&& is_integer((int) $explode[1])) {
                                $model_reference = Product::findOne($explode[1]);

                                if ($model_reference != null) {
                                    $item_model = new ItemPurchaseOrder;
                                    $item_model->product_id = $explode[1];
                                    $item_model->code = $model_reference->code;
                                    $item_model->unit_type_id = (isset($item['unit_type_id']) && !empty($item['unit_type_id'])) ? $item['unit_type_id'] : $model_reference->unit_type_id;
                                    $item_model->user_id = $userAuth->id;
                                    $item_model->purchase_order_id = $model->id;
                                    $item_model->quantity = $item['quantity'];
                                    $request_quantity = $item_model->quantity;
                                    $item_model->price_type = (isset($item['price_type']) && !empty($item['price_type'])) ? $item['price_type'] : UtilsConstants::CUSTOMER_ASSIGN_PRICE_1;

                                    if (isset($item_model->price_type)) {
                                        $price_type_label = UtilsConstants::getPriceTypeMiniLabel($item_model->price_type);
                                        $item_model->price_unit = $model_reference->getPriceByTypeAndUnitType($item_model->price_type, $item_model->unit_type_id);
                                    }

                                    $item_model->description = $model_reference->description . ' <b>' . $price_type_label . ' ' . $quantity_label . '</b>';
                                } else {
                                    $item_error = true;
                                    array_push($errors_items, "Item " . $key . ": No se encontró un producto con ese identificador");
                                }
                            } else {
                                $item_error = true;
                                array_push($errors_items, "Item " . $key . ": El identificador del producto no es entero");
                            }
                        } elseif ($explode[0] == 'S') {

                            if (is_numeric($explode[1])&& is_integer($explode[1])) {
                                $model_reference = Service::findOne($explode[1]);

                                if ($model_reference != null) {
                                    $item_model = new ItemPurchaseOrder;
                                    $item_model->service_id = $explode[1];
                                    $item_model->code = $model_reference->code;
                                    $item_model->description = $model_reference->name;
                                    $item_model->quantity = $item['quantity'];
                                    $request_quantity = $item_model->quantity;
                                    $item_model->price_unit = $model_reference->price;
                                } else {
                                    if (!$item_error) array_push($errors_items, "Item " . $key . ": No se encontró un servicio con ese identificador");
                                }
                            } else {
                                array_push($errors_items, "Item " . $key . ": El identificador del producto no es entero");
                            }
                        } else {
                            if (!$item_error) array_push($errors_items, "Item " . $key . ": El item no es servicio ni producto");
                        }
                    }

                    if ($model_reference !== null && $item_model != null) {
                        $item_model->purchase_order_id = $model->id;
                        //$model->quantity = $array_posted['quantity'];
                        $item_model->unit_type_id = (isset($item['unit_type_id']) && !empty($item['unit_type_id'])) ? $item['unit_type_id'] : $model_reference->unit_type_id;

                        $percent_iva = $model_reference->getPercentIvaToApply();


                        if ($explode[0] == 'S') {
                            $item_model->discount_amount = $model_reference->getDiscount();
                            $item_model->nature_discount = $model_reference->nature_discount;
                        } else {
                            $item_model->discount_amount = $model_reference->getDiscount();
                            $item_model->nature_discount = $model_reference->nature_discount;
                        }
                        if (is_null($item_model->nature_discount) || empty($item_model->nature_discount))
                            $item_model->nature_discount = '-';

                        // Se aplica el descuento a nivel de producto o servicio

                        $subtotal = $item_model->price_unit * $request_quantity - $item_model->discount_amount;
                        $item_model->subtotal = (isset($subtotal) && !empty($subtotal)) ? $subtotal : 0;

                        $tax_calculate = $subtotal * ($percent_iva / 100);
                        $tax = (isset($tax_calculate) && !empty($tax_calculate)) ? $tax_calculate : 0;

                        $exonerated = $tax * ($model_reference->exoneration_purchase_percent / 100);
                        $exonerated_tax_amount = (isset($exonerated) && !empty($exonerated)) ? $exonerated : 0;
                        $item_model->exonerate_amount = $exonerated_tax_amount;
                        $item_model->exoneration_purchase_percent = (int) $model_reference->exoneration_purchase_percent;
                        $item_model->exoneration_document_type_id = $model_reference->exoneration_document_type_id;
                        $item_model->number_exoneration_doc = $model_reference->number_exoneration_doc;
                        $item_model->name_institution_exoneration = $model_reference->name_institution_exoneration;
                        $item_model->exoneration_date = $model_reference->exoneration_date;

                        $item_model->tax_amount = $tax;
                        $item_model->tax_rate_percent = $model_reference->tax_rate_percent;
                        $item_model->tax_type_id = $model_reference->tax_type_id;
                        $item_model->tax_rate_type_id = $model_reference->tax_rate_type_id;
                        $item_model->price_total = $subtotal + $tax - $exonerated_tax_amount;
                    }

                    if ($item_model != null && $item_model->save()) {
                        //Actualizar los totales de la factura
                        $invoice = PurchaseOrder::find()->where(['id' => $item_model->purchase_order_id])->one();
                        $invoice->save(false);
                        $item_model->refresh();
                        $items_success++;
                    } else {
                        if (!$item_error) array_push($errors_items, "Item " . $key . ": Ha ocurrido un error al intentar crear el registro." . (string) $item_model->errors);
                    }
                }

                if ($items_success == count($items)) {

                    $response["success"] = true;
                    $response["data"]->id = $model->id;
                    $response["msg"] = "Orden actualizada";
                } else {

                    $model->delete();
                    $response["errors"]->items = $errors_items;

                    $response["msg"] = "No se pudo actualizar la orden";
                }
            } else {
                $response["errors"]->orden = $model->errors;

                $response["msg"] = "No se pudo actualizar la orden";
            }

            return $response;
        } catch (\Exception $e) {
            //throw $th;
            $response["msg"] = "Ocurrió un error";
            $response["msg_detail"] = $e->getMessage();
            return $response;
        }
    }

    public function actionListPendingInvoices()
    {

        $params = Yii::$app->request->queryParams;
        $userAuth = $this->checkAccess($this->action->id, null, $params);

        $body = Yii::$app->request->bodyParams;

        $response = [
            "success" => false,
            "data" => (object) [],
            "msg" => "",
            "auth" => false,
            "errors" => (object) []
        ];

        //Autenticación
        if (!$userAuth) {
            $response["msg"] = "Acceso inválido";
            return $response;
        }

        $response["auth"] = true;
        //Fin autenticación

        try {

            $is_point_sale = 0; // facturas de almacen
            $dias_credito = 8;
            $query = Invoice::find()->select("invoice.id,invoice.consecutive,invoice.status, CAST(emission_date AS date) as date, invoice.total_comprobante,customer.commercial_name, customer.name, collector_has_invoice.collector_id,(CAST(CAST(NOW() AS date) - CAST(emission_date AS date) as SIGNED)) - ".$dias_credito." AS dias_vencidos")
                ->join('INNER JOIN', 'customer', 'invoice.customer_id = customer.id')
                ->join('INNER JOIN', 'boxes', "invoice.box_id = boxes.id AND boxes.is_point_sale = " . $is_point_sale . "")
                ->Where([
                    //'invoice.condition_sale_id' => ConditionSale::CREDITO,
                    'invoice.status' => UtilsConstants::INVOICE_STATUS_PENDING,
                    'status_hacienda' => UtilsConstants::HACIENDA_STATUS_ACCEPTED,
                    'collector_has_invoice.collector_id'=> $userAuth->id
                ])
                ->join('INNER JOIN', 'seller_has_invoice', "seller_has_invoice.invoice_id = invoice.id")
                ->join('LEFT JOIN', 'collector_has_invoice', "collector_has_invoice.invoice_id = invoice.id")
                ->orderBy(' dias_vencidos DESC');
                $query_invoices = $query->asArray()->all();

                $invoices = [];
                $total = 0;
                foreach ($query_invoices as $key => $value) {
                    $temp_name = (isset($value["commercial_name"]) && !empty($value["commercial_name"])) ? $value["name"] . ' - ' . $value["commercial_name"] : $value["name"] ;
                    
                    $abonado = InvoiceAbonos::getAbonosByInvoiceID( $value["id"]);
                    $total_invoice = $value["total_comprobante"];
                    $pendiente = $total_invoice - $abonado;

                    $_invoice = [
                        "id" => intval($value["id"]),
                        "consecutive" => $value["consecutive"],
                        "status" => intval($value["status"]),
                        "date" => $value["date"],
                        "total" => GlobalFunctions::formatNumber($total_invoice, 2),
                        "paid" => GlobalFunctions::formatNumber($abonado, 2),
                        "pending" => GlobalFunctions::formatNumber($pendiente, 2),
                        "customer_name" => $temp_name,
                        "collector_id" => intval($value["collector_id"]),
                        "dias_vencidos" => $value["dias_vencidos"]
                    ];
                    array_push($invoices,$_invoice);
                    $total += $value["total_comprobante"];
                }
                $response["data"]->invoices = $invoices;
                $response["data"]->total = GlobalFunctions::formatNumber($total, 2);
                $response["success"] = true;
                return $response;
        } catch (\Exception $e) {
            $response["msg"] = "Ocurrió un error";
            $response["msg_detail"] = $e->getMessage();
            return $response;
        }
    }

    public function actionGetInvoiceDetail(){
        
        $params = Yii::$app->request->queryParams;
        $userAuth = $this->checkAccess($this->action->id, null, $params);

        $body = Yii::$app->request->bodyParams;

        $response = [
            "success" => false,
            "data" => (object) [],
            "msg" => "",
            "auth" => false,
            "errors" => (object) []
        ];

        //Autenticación
        if (!$userAuth) {
            $response["msg"] = "Acceso inválido";
            return $response;
        }

        $response["auth"] = true;
        //Fin autenticación

        try {

            $invoice_id = isset($body["invoice_id"]) ? $body["invoice_id"] : null;

            if ($invoice_id == null) {
                $response["msg"] = "Parámetros faltantes";
                $response["errors"]->invoice_id = "El parámetro invoice_id es requerido";
                return $response;
            }

            $model = Invoice::findOne($invoice_id);
            //Revisar si pertenece
            $collectors_assigned = CollectorHasInvoice::getCollectorByInvoiceId($invoice_id);
            $belongs_to_user = false;
            foreach ($collectors_assigned as $value) {
                if($value["collector_id"]==$userAuth->id){
                    $belongs_to_user = true;
                }
            }

            if(!$belongs_to_user){
                $response["msg"] = "Esta factura no pertenece al usuario";
                return $response;
            }

            $abonos_query = InvoiceAbonos::find()                   
            ->where(['invoice_abonos.invoice_id' => $invoice_id])
            ->orderBy('id DESC')
            ->all();        

            $total = $model->total_comprobante;
            $abonado = InvoiceAbonos::getAbonosByInvoiceID($model->id);
            $pendiente = $total - $abonado;

            $abonos = [];
            //TODO
            foreach ($abonos_query as $key => $abono) {
                $_abono = [
                    "id" => $abono->id,
                    "emission_date" =>date('d-M-Y', strtotime($abono->emission_date)),
                    "payment_method" => $abono->paymentMethod->name,
                    "bank" => isset($abono->bank)?$abono->bank->name:"",
                    "reference" => $abono->reference,
                    "comment" => $abono->comment,
                    "amount" => GlobalFunctions::formatNumber($abono->amount,2),

                ];      
                array_push($abonos,$_abono);
            }

            $response["data"] = [
                "id" => $model["id"],
                "consecutive" => $model["consecutive"],
                "total" => GlobalFunctions::formatNumber($total,2),
                "paid" =>  GlobalFunctions::formatNumber($abonado,2),
                "pending" => GlobalFunctions::formatNumber($pendiente,2),
                "total_as_number" => round( $total, 2, PHP_ROUND_HALF_EVEN),
                "paid_as_number" => (double)number_format($abonado,2,".",''),
                "pending_as_number" => (double)number_format($pendiente,2,".",''),
                "payments" => $abonos
            ];

            $response["success"] = true;
            return $response;
            
        } catch (\Exception $e) {
            $response["msg"] = "Ocurrió un error";
            $response["msg_detail"] = $e->getMessage();
            return $response;
        }
    }

    public function actionNewInvoicePayment(){
     
        $params = Yii::$app->request->queryParams;
        $userAuth = $this->checkAccess($this->action->id, null, $params);

        $body = Yii::$app->request->bodyParams;

        $response = [
            "success" => false,
            "data" => (object) [],
            "msg" => "",
            "auth" => false,
            "errors" => (object) []
        ];

        //Autenticación
        if (!$userAuth) {
            $response["msg"] = "Acceso inválido";
            return $response;
        }

        $response["auth"] = true;
        //Fin autenticación

        try {
            
            $payment_methods = PaymentMethod::getSelectMap();
            $_payment_methods = [];
            foreach ($payment_methods as $key => $value) {
                array_push($_payment_methods,[
                    "id" => $key,
                    "title" => $value
                ]);
            }
            $banks = Banks::getSelectMap();
            $_banks = [];
            foreach ($banks as $key => $value) {
                array_push($_banks,[
                    "id" => $key,
                    "title" => $value
                ]);
            }

            $response["success"] = true;
            $response["data"] = [
                "payment_methods" => $_payment_methods,
                "banks" => $_banks
            ];

            return $response;

        } catch (\Exception $e) {
            $response["msg"] = "Ocurrió un error";
            $response["msg_detail"] = $e->getMessage();
            return $response;
        }
    }

    public function actionSaveInvoicePayment(){

        $params = Yii::$app->request->queryParams;
        $userAuth = $this->checkAccess($this->action->id, null, $params);

        $body = Yii::$app->request->bodyParams;

        $response = [
            "success" => false,
            "data" => (object) [],
            "msg" => "",
            "auth" => false,
            "errors" => (object) []
        ];

        //Autenticación
        if (!$userAuth) {
            $response["msg"] = "Acceso inválido";
            return $response;
        }

        $response["auth"] = true;
        //Fin autenticación
        try {
            
            $invoice_id = isset($body["invoice_id"]) ? $body["invoice_id"] : null;
            $amount = isset($body["amount"]) ? $body["amount"] : null;
            $payment_method_id = isset($body["payment_method_id"]) ? $body["payment_method_id"] : null;
            $bank_id = isset($body["bank_id"]) ? $body["bank_id"] : null;
            $reference = isset($body["reference"]) ? $body["reference"] : null;
            $comment = isset($body["comment"]) ? $body["comment"] : null;

            $msg_error = "";

            if ($invoice_id == null) {
                $response["errors"]->invoice_id = "El parámetro invoice_id es requerido.";
                $msg_error .= $response["errors"]->invoice_id;
            }
            if ($amount == null) {
                $response["errors"]->amount = "El parámetro amount es requerido.";
                $msg_error .= $response["errors"]->amount;
            }else if(!is_numeric($amount)){
                $response["errors"]->amount = "El monto debe ser numérico. (".$amount.")";
                $msg_error .= $response["errors"]->amount;
            }
            if ($payment_method_id == null) {
                $response["errors"]->payment_method_id = "El parámetro payment_method_id es requerido.";
                $msg_error .= $response["errors"]->payment_method_id;
            }
            if ($reference == null) {
                $response["errors"]->reference = "El parámetro reference es requerido.";
                $msg_error .= $response["errors"]->reference;
            }

            if (is_object($response["errors"]) && count(get_object_vars($response["errors"])) > 0) {
                $response["msg"] = "Ocurrió un error con los parámetros enviados : ".$msg_error;
                return $response;
            }
             //Revisar si pertenece
            $collectors_assigned = CollectorHasInvoice::getCollectorByInvoiceId($invoice_id);
            $belongs_to_user = false;
            foreach ($collectors_assigned as $value) {
                if($value["collector_id"]==$userAuth->id){
                    $belongs_to_user = true;
                }
            }

            if(!$belongs_to_user){
                $response["msg"] = "Esta factura no pertenece al usuario";
                return $response;
            }

            $invoice = Invoice::findOne($invoice_id);
            $total = sprintf('%0.2f', $invoice->total_comprobante);
            $abonado = sprintf('%0.2f', InvoiceAbonos::getAbonosByInvoiceID($invoice->id));
            $model = new InvoiceAbonos;
            $model->invoice_id = $invoice->id;
            $model->emission_date = date('Y-m-d');
            $model->amount = $amount;
            $model->payment_method_id = $payment_method_id;
            $model->bank_id = $bank_id;
            $model->reference = $reference;
            $model->comment = $comment;
            $model->collector_id = $userAuth->id;

            $totalAbonado = sprintf('%0.2f', $abonado + $model->amount);                    

            if ($total < $totalAbonado){
                $response["msg"] = "El monto abonado supera el total de la factura";
                return $response;
            }

            $transaction = \Yii::$app->db->beginTransaction();
            try {                
                if ($model->save()) {
                    $totalAbonado = sprintf('%0.2f', $abonado + $model->amount);                    
                    if ($total == $totalAbonado){
                        $invoice->status = UtilsConstants::INVOICE_STATUS_CANCELLED;
                        $invoice->save();
                    }
       
                    $transaction->commit();
                    $response["success"] = true;
                    $response["msg"] = "El Abono se ha realizado satisfactoriamente";
                } else {
                    $response["msg"] = "Ha ocurrido un error";
                    
                }
                return $response;
            } catch (Exception $e) {
                $transaction->rollBack();
                $response["msg"] = "Error, ha ocurrido una excepción actualizando el elemento";
                $response["detail_msg"] = $e->getMessage();
            }   

            
        } catch (\Exception $e) {
            $response["msg"] = "Ocurrió un error";
            $response["msg_detail"] = $e->getMessage();
            return $response;
        }
    }
}
