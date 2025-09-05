<?php

namespace backend\models\business;

use backend\models\nomenclators\Canton;
use backend\models\nomenclators\Collector;
use backend\models\nomenclators\ConditionSale;
use backend\models\nomenclators\CreditDays;
use backend\models\nomenclators\CustomerClassification;
use backend\models\nomenclators\CustomerType;
use backend\models\nomenclators\Disctrict;
use backend\models\nomenclators\ExonerationDocumentType;
use backend\models\nomenclators\IdentificationType;
use backend\models\nomenclators\Province;
use backend\models\nomenclators\RouteTransport;
use backend\models\nomenclators\UtilsConstants;
use common\models\User;
use Yii;
use backend\models\BaseModel;
use yii\helpers\ArrayHelper;
use yii\helpers\StringHelper;
use common\models\GlobalFunctions;
use yii\helpers\Html;

/**
 * This is the model class for table "customer".
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $commercial_name
 * @property string|null $code
 * @property string|null $description
 * @property bool|null $status
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property int|null $identification_type_id
 * @property string|null $identification
 * @property string|null $foreign_identification
 * @property int|null $customer_type_id
 * @property int|null $customer_classification_id
 * @property string|null $country_code_phone
 * @property string|null $phone
 * @property string|null $country_code_fax
 * @property string|null $fax
 * @property string|null $email
 * @property int|null $province_id
 * @property int|null $canton_id
 * @property int|null $disctrict_id
 * @property string|null $address
 * @property string|null $other_signs
 * @property int|null $condition_sale_id
 * @property float|null $credit_amount_colon
 * @property float|null $credit_amount_usd
 * @property int|null $credit_days_id
 * @property bool|null $enable_credit_max
 * @property int|null $price_assigned
 * @property bool|null $is_exonerate
 * @property int|null $exoneration_document_type_id
 * @property string|null $number_exoneration_doc
 * @property string|null $name_institution_exoneration
 * @property string|null $exoneration_date
 * @property float|null $exoneration_purchase_percent
 * @property int|null $route_transport_id
 * @property int|null $pre_invoice_type
 *
 * @property Canton $canton
 * @property User $collector
 * @property User $seller
 * @property ConditionSale $conditionSale
 * @property CreditDays $creditDays
 * @property CustomerClassification $customerClassification
 * @property CustomerType $customerType
 * @property Disctrict $disctrict
 * @property ExonerationDocumentType $exonerationDocumentType
 * @property IdentificationType $identificationType
 * @property Province $province
 * @property RouteTransport $routeTransport
 * @property CustomerContact[] $customerContacts
 * @property Proforma[] $proformas

 */
class Customer extends BaseModel
{
    public $sellers = [];
    public $collectors = [];
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'customer';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'code' ,'identification_type_id', 'email','address','condition_sale_id','pre_invoice_type'],'required'],
            [['description'], 'string'],
            ['email','email'],
            ['email','trim'],
            [['email', 'email_cc'], 'string', 'max' => 255],
            ['email_cc','trim'],  
            
            // Validación de unicidad para el campo `identification`
            ['identification', 'unique', 'message' => 'La identificación ya ha sido registrada.'],
            
            // Validación de unicidad para el campo `foreign_identification`
            ['foreign_identification', 'unique', 'message' => 'La identificación extranjera ya ha sido registrada.'],

            [['status', 'identification_type_id', 'customer_type_id', 'customer_classification_id', 'province_id', 'canton_id', 'disctrict_id', 'condition_sale_id', 'credit_days_id', 'enable_credit_max', 'price_assigned', 'is_exonerate', 'exoneration_document_type_id','route_transport_id','pre_invoice_type'], 'integer'],
            [['created_at', 'updated_at', 'exoneration_date', 'sellers', 'collectors'], 'safe'],
            [['credit_amount_colon', 'credit_amount_usd', 'exoneration_purchase_percent'], 'number'],
            [['name', 'commercial_name', 'code', 'identification', 'foreign_identification', 'country_code_phone', 'phone', 'country_code_fax', 'fax', 'email', 'address', 'other_signs', 'number_exoneration_doc', 'name_institution_exoneration'], 'string', 'max' => 255],
            [['canton_id'], 'exist', 'skipOnError' => true, 'targetClass' => Canton::className(), 'targetAttribute' => ['canton_id' => 'id']],
            [['condition_sale_id'], 'exist', 'skipOnError' => true, 'targetClass' => ConditionSale::className(), 'targetAttribute' => ['condition_sale_id' => 'id']],
            [['credit_days_id'], 'exist', 'skipOnError' => true, 'targetClass' => CreditDays::className(), 'targetAttribute' => ['credit_days_id' => 'id']],
            [['customer_classification_id'], 'exist', 'skipOnError' => true, 'targetClass' => CustomerClassification::className(), 'targetAttribute' => ['customer_classification_id' => 'id']],
            [['customer_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => CustomerType::className(), 'targetAttribute' => ['customer_type_id' => 'id']],
            [['disctrict_id'], 'exist', 'skipOnError' => true, 'targetClass' => Disctrict::className(), 'targetAttribute' => ['disctrict_id' => 'id']],
            [['exoneration_document_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => ExonerationDocumentType::className(), 'targetAttribute' => ['exoneration_document_type_id' => 'id']],
            [['identification_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => IdentificationType::className(), 'targetAttribute' => ['identification_type_id' => 'id']],
            [['province_id'], 'exist', 'skipOnError' => true, 'targetClass' => Province::className(), 'targetAttribute' => ['province_id' => 'id']],
            [['route_transport_id'], 'exist', 'skipOnError' => true, 'targetClass' => RouteTransport::className(), 'targetAttribute' => ['route_transport_id' => 'id']],
            ['email_cc', 'checkEmail', 'skipOnEmpty' => false, 'skipOnError' => false],
            ['identification','checkIdentificacion', 'skipOnEmpty' => false, 'skipOnError' => false],	
            //['province_id','checkProvincia', 'skipOnEmpty' => false, 'skipOnError' => false],	
            //['canton_id','checkCanton', 'skipOnEmpty' => false, 'skipOnError' => false],	
            //['disctrict_id','checkDistrito', 'skipOnEmpty' => false, 'skipOnError' => false],	
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => Yii::t('backend', 'Nombre'),
            'commercial_name' => Yii::t('backend', 'Nombre comercial'),
            'code' => Yii::t('backend', 'Código'),
            'description' => Yii::t('backend', 'Descripción'),
            'status' => Yii::t('backend', 'Estado'),
            'created_at' => Yii::t('backend', 'Fecha de creación'),
            'updated_at' => Yii::t('backend', 'Fecha de actualización'),
            'identification_type_id' => Yii::t('backend', 'Tipo de identificación'),
            'identification' => Yii::t('backend', 'Identificación'),
            'foreign_identification' => Yii::t('backend', 'Identif. extranjera'),
            'customer_type_id' => Yii::t('backend', 'Tipo de cliente'),
            'customer_classification_id' => Yii::t('backend', 'Clasificación'),
            'country_code_phone' => Yii::t('backend', 'Cod. País'),
            'phone' => Yii::t('backend', 'Teléfono'),
            'country_code_fax' => Yii::t('backend', 'Cod. País'),
            'fax' => 'Fax',
            'email' => Yii::t('backend', 'Correo electrónico'),
            'email_cc'=> Yii::t('backend', 'Copia de Correo electrónico (puede serpararlo por ;)'),
            'province_id' => Yii::t('backend', 'Provincia'),
            'canton_id' => Yii::t('backend', 'Cantón'),
            'disctrict_id' => Yii::t('backend', 'Distrito'),
            'address' => Yii::t('backend', 'Dirección'),
            'other_signs' => Yii::t('backend', 'Otras señas'),
            'condition_sale_id' => Yii::t('backend', 'Condición de venta'),
            'credit_amount_colon' => Yii::t('backend', 'Monto de crédito ¢'),
            'credit_amount_usd' => Yii::t('backend', 'Monto de crédito $'),
            'credit_days_id' => Yii::t('backend', 'Días de crédito'),
            'enable_credit_max' => Yii::t('backend', 'Crédito máximo'),
            'price_assigned' => Yii::t('backend', 'Precio asignado'),
            'sellers' => Yii::t('backend', 'Agente Vendedor'),
            'collectors' => Yii::t('backend', 'Agente Cobrador'),            
            'is_exonerate' => Yii::t('backend', 'Exonerado'),
            'exoneration_document_type_id' => Yii::t('backend', 'Tipo documento'),
            'number_exoneration_doc' => Yii::t('backend', 'No. documento'),
            'name_institution_exoneration' => Yii::t('backend', 'Instituto emite'),
            'exoneration_date' => Yii::t('backend', 'Fecha de emisión'),
            'exoneration_purchase_percent' => Yii::t('backend', 'Porcentaje compra'),
            'route_transport_id' => Yii::t('backend', 'Ruta de transporte'),
            'pre_invoice_type' => Yii::t('backend', 'Pre-factura'),
            'user_id' => 'Usuario',
        ];
    }

     /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function beforeSave($insert)
    {
        if ($this->isNewRecord) {
            $this->user_id = Yii::$app->user->id;
        } 
        return parent::beforeSave($insert);
    }

    public function checkIdentificacion($attribute, $params) {
		if (($this->identification_type_id != 10) && (empty($this->identification) || is_null($this->identification))) {
			$this->addError($attribute, 'Debe definir la identificación del cliente');
		}
	}

    public function checkProvincia($attribute, $params) {
        if ($this->id != NULL){
            if (($this->identification_type_id != 10) && (empty($this->provincia_id) || is_null($this->provincia_id))) {
                $this->addError($attribute, 'Debe definir la provincia');
            }
        }
	}
    
    public function checkCanton($attribute, $params) {
        if ($this->id != NULL){
            if (($this->identification_type_id != 10) && (empty($this->canton_id) || is_null($this->canton_id))) {
                $this->addError($attribute, 'Debe definir el cantón');
            }
        }
	}

    public function checkDistrito($attribute, $params) {
        if ($this->id != NULL){
            if (($this->identification_type_id != 10) && (empty($this->disctrict_id) || is_null($this->disctrict_id))) {
                $this->addError($attribute, 'Debe definir el distrito');
            }
        }
	}

    public function checkEmail($attribute, $params)
    {
        $email_cc = $this->email_cc;

        if (strlen(trim($email_cc)) > 0) {
            $arr_cc = explode(';', $email_cc);
        } else {
            $arr_cc = array();
        }
        
        $direcciones_ok = true;
        foreach ($arr_cc as $ccs) {
            $ccs = trim($ccs);
            if (!filter_var($ccs, FILTER_VALIDATE_EMAIL)) {
                $direcciones_ok = false;
                break;
            }
        }

        if ($direcciones_ok == false) {
            $this->addError($attribute, 'Existe un error en la dirección de email de copia');
            return false;
        }
        else
            return true;
    }    

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCanton()
    {
        return $this->hasOne(Canton::className(), ['id' => 'canton_id']);
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
    public function getCustomerClassification()
    {
        return $this->hasOne(CustomerClassification::className(), ['id' => 'customer_classification_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCustomerType()
    {
        return $this->hasOne(CustomerType::className(), ['id' => 'customer_type_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDisctrict()
    {
        return $this->hasOne(Disctrict::className(), ['id' => 'disctrict_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getExonerationDocumentType()
    {
        return $this->hasOne(ExonerationDocumentType::className(), ['id' => 'exoneration_document_type_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getIdentificationType()
    {
        return $this->hasOne(IdentificationType::className(), ['id' => 'identification_type_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProvince()
    {
        return $this->hasOne(Province::className(), ['id' => 'province_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCustomerContacts()
    {
        return $this->hasMany(CustomerContact::className(), ['customer_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProformas()
    {
        return $this->hasMany(Proforma::className(), ['customer_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRouteTransport()
    {
        return $this->hasOne(RouteTransport::className(), ['id' => 'route_transport_id']);
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
        return "/customer";
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
    public function generateCode()
    {
        $max_code = self::find()->max('code');
        $code = is_null($max_code) ? 1: ($max_code + 1);
        return GlobalFunctions::zeroFill($code,6);
    }

    /**
     * @return array
     */
    public function fields()
    {
        $fields['id'] = 'id';
        $fields['name'] = 'name';
        $fields['commercial_name'] = 'commercial_name';
        $fields['code'] = 'code';
        $fields['email'] = 'email';
        $fields['description'] = 'description';

        $fields['identification_type_id'] = 'identification_type_id';
        $fields['identification_type_label'] = function(Customer $model){
            return isset($model->identification_type_id)? $model->identificationType->name : '';
        };

        $fields['identification'] = 'identification';
        $fields['foreign_identification'] = 'foreign_identification';

        $fields['customer_type_id'] = 'customer_type_id';
        $fields['customer_type_label'] = function(Customer $model){
            return isset($model->customer_type_id)? $model->customerType->name : '';
        };

        $fields['customer_classification_id'] = 'customer_classification_id';
        $fields['customer_classification_label'] = function(Customer $model){
            return isset($model->customer_classification_id)? $model->customerClassification->name: '';
        };

        $fields['country_code_phone'] = 'country_code_phone';
        $fields['phone'] = 'phone';
        $fields['country_code_fax'] = 'country_code_fax';
        $fields['fax'] = 'fax';

        $fields['province_id'] = 'province_id';
        $fields['province_label'] = function(Customer $model){
            return isset($model->province_id)? $model->province->name : '';
        };

        $fields['canton_id'] = 'canton_id';
        $fields['canton_label'] = function(Customer $model){
            return isset($model->canton_id)? $model->canton->name : '';
        };

        $fields['disctrict_id'] = 'disctrict_id';
        $fields['disctrict_label'] = function(Customer $model){
            return isset($model->disctrict_id)? $model->disctrict->name : '';
        };

        $fields['address'] = 'address';
        $fields['other_signs'] = 'other_signs';

        $fields['condition_sale_id'] = 'condition_sale_id';
        $fields['condition_sale_label'] = function(Customer $model){
            return isset($model->condition_sale_id)? $model->conditionSale->name : '';
        };

        $fields['credit_amount_colon'] = 'credit_amount_colon';
        $fields['credit_amount_usd'] = 'credit_amount_usd';

        $fields['credit_days_id'] = 'credit_days_id';
        $fields['credit_days_label'] = function(Customer $model){
            return isset($model->credit_days_id)? $model->creditDays->name : '';
        };

        $fields['enable_credit_max'] = 'enable_credit_max';

        $fields['price_assigned'] = 'price_assigned';
        $fields['price_assigned_label'] = function(Customer $model){
            return isset($model->price_assigned)? UtilsConstants::getCustomerAsssignPriceSelectType($model->price_assigned) : '';
        };

        $fields['is_exonerate'] = 'is_exonerate';

        $fields['exoneration_document_type_id'] = 'exoneration_document_type_id';
        $fields['exoneration_document_type_label'] = function(Customer $model){
            return isset($model->exoneration_document_type_id)? $model->exonerationDocumentType->name : '';
        };

        $fields['number_exoneration_doc'] = 'number_exoneration_doc';
        $fields['name_institution_exoneration'] = 'name_institution_exoneration';
        $fields['exoneration_date'] = 'exoneration_date';
        $fields['exoneration_purchase_percent'] = 'exoneration_purchase_percent';

        $fields['route_transport_id'] = 'route_transport_id';
        $fields['route_transport_label'] = function(Customer $model){
            return isset($model->route_transport_id)? $model->routeTransport->name : '';
        };

        $fields['pre_invoice_type'] = 'pre_invoice_type';
        $fields['pre_invoice_type_label'] = function(Customer $model){
            return isset($model->pre_invoice_type)? UtilsConstants::getPreInvoiceSelectType($model->pre_invoice_type) : '';
        };

        $fields['status'] = 'status';
        $fields['created_at'] = 'created_at';
        $fields['updated_at'] = 'updated_at';

        return $fields;
    }

    /**
     * Get list.
     * @return array
     */
    public static function getSelectMap($check_status=true)
    {
        $array_map = [];

        $query = self::find();

        if($check_status)
        {
            $query->where(['status' => self::STATUS_ACTIVE]);
        }

        $models = $query->all();

        if(count($models)>0)
        {
            foreach ($models AS $index => $model)
            {
                $temp_name = (isset($model->commercial_name) && !empty($model->commercial_name))? $model->name.' - '.$model->commercial_name : $model->name;
                $array_map[$model->id] = $temp_name;
            }
        }

        return $array_map;
    }

    public static function getSelectMapByVendedor($check_status=true)
    {
        $array_map = [];

        $query = self::find();

        if($check_status)
        {
            $query->where(['status' => self::STATUS_ACTIVE]);
        }
        
        //$sellers = UserHasSeller::find()->where(['user_id'=>Yii::$app->user->id])->all();
        $seller_ids = UserHasSeller::find()
                                        ->select('seller_id') // Selecciona solo el campo 'seller_id'
                                        ->where(['user_id' => Yii::$app->user->id])
                                        ->column(); // Obtiene un arreglo lineal con los valores de 'seller_id'

        $seller_has_customer = SellerHasCustomer::find()->select('customer_id') 
        ->where(['seller_id' => $seller_ids])
        ->column(); 

        if (empty($seller_has_customer))
            $seller_has_customer = 1575;

        $query->andWhere(['id'=>$seller_has_customer]);

        $models = $query->all();

        

        if(count($models)>0)
        {
            foreach ($models AS $index => $model)
            {
                $temp_name = (isset($model->commercial_name) && !empty($model->commercial_name))? $model->name.' - '.$model->commercial_name : $model->name;
                $array_map[$model->id] = $temp_name;
            }
        }
        
        return $array_map;
    }
}
