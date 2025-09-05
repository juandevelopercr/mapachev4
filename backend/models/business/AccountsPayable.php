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

class AccountsPayable extends \yii\db\ActiveRecord
{
    public $dias_trascurridos;
    public $dias_vencidos;
    public $color;	
    public $transmitter;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'accounts_payable';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['key', 'emission_date', 'currency', 'status'], 'required'],
            [['key'], 'string','length'=>50],
            [['proveedor'], 'string','length'=>100],
            [['status'], 'integer'],
            [['emission_date', 'transmitter'], 'safe'],
            [['total_invoice'], 'number'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('backend', 'ID'),
            'key' => Yii::t('backend', 'Consecutivo'),
            'emission_date' => Yii::t('backend', 'Fecha de emisión'),
            'currency' => Yii::t('backend', 'Moneda'),
            'total_invoice' => Yii::t('backend', 'Total a Pagar'),
            'status' => Yii::t('backend', 'Estado'),
            'transmitter'=> Yii::t('backend', 'Proveedor'),
            'proveedor' => Yii::t('backend', 'Proveedor'),
        ];
    }
   
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAbonos()
    {
        return $this->hasMany(AccountsPayableAbonos::className(), ['accounts_payable_abonos_id' => 'id']);
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
        return "/accounts-payable";
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

    function afterFind()
    {
        $this->emission_date = date('Y-m-d H:i:s', strtotime($this->emission_date));     
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if (is_null($this->id) || empty($this->id)) {
                $this->emission_date = (isset($this->emission_date) && !empty($this->emission_date)) ? date('Y-m-d H:i:s', strtotime($this->emission_date)) : date('Y-m-d H:i:s');
            }
            return true;
        } else {
            return false;
        }
    }  
    
	public static function addCuentaPorPagar($documento)
	{
		// Hacer aqui el proceso de adicionar una cuenta por pagar
		// PEDIENTE CACERES 
		$cuentaPagar = new AccountsPayable;
		$cuentaPagar->key = $documento->key;
		$cuentaPagar->proveedor = $documento->proveedor;
		$cuentaPagar->emission_date = $documento->emission_date;
		$cuentaPagar->currency = $documento->currency;
		$cuentaPagar->total_invoice = $documento->total_invoice;
		$cuentaPagar->status = UtilsConstants::ACCOUNT_PAYABLE_PENDING;
        
		if ($cuentaPagar->save(false))
        {
			$result = true;
        }
		else{
			$result = false;
        }
          
		return $result;	
	}    

    function getProveedor($data)
    {
        $proveedor = '';
        if (strlen($data->key) < 50){
            $proveedor = $data->proveedor;
        }
        else
        {
            $documento = Documents::find()->where(['key' => $data->key])->one();
            if (!is_null($documento)) {
                $proveedor = $documento->transmitter;
            }
        }
        return $proveedor;
    }
    
}
