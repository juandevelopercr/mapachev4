<?php

namespace backend\models\business;

use backend\models\nomenclators\ConditionSale;
use backend\models\nomenclators\CreditDays;
use backend\models\business\SupplierBankInformation;
use Yii;
use backend\models\BaseModel;
use yii\helpers\StringHelper;
use common\models\GlobalFunctions;
use yii\helpers\Html;

/**
 * This is the model class for table "supplier".
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $code
 * @property string|null $identification
 * @property string|null $phone
 * @property string|null $address
 * @property string|null $web_site
 * @property string|null $entry_date
 * @property float|null $colon_credit
 * @property float|null $dollar_credit
 * @property int|null $max_credit
 * @property int|null $credit_days_id
 * @property int|null $condition_sale_id
 * @property int $status
 * @property string $updated_at
 * @property string $created_at
 *
 * @property Product[] $products
 * @property ConditionSale $conditionSale
 * @property CreditDays $creditDays
 * @property SupplierBankInformation[] $supplierBankInformations
 * @property SupplierContact[] $supplierContacts

 */
class Supplier extends BaseModel
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'supplier';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'identification','entry_date','condition_sale_id','code'],'required'],
            [['address'], 'string'],
            ['code','unique'],
            [['entry_date', 'updated_at', 'created_at'], 'safe'],
            [['colon_credit', 'dollar_credit'], 'number'],
            [['max_credit', 'credit_days_id', 'condition_sale_id', 'status'], 'integer'],
            [['name', 'code', 'identification', 'phone', 'web_site'], 'string', 'max' => 255],
            [['condition_sale_id'], 'exist', 'skipOnError' => true, 'targetClass' => ConditionSale::className(), 'targetAttribute' => ['condition_sale_id' => 'id']],
            [['credit_days_id'], 'exist', 'skipOnError' => true, 'targetClass' => CreditDays::className(), 'targetAttribute' => ['credit_days_id' => 'id']],
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
            'code' => Yii::t('backend', 'Código'),
            'identification' => Yii::t('backend', 'Identificación'),
            'phone' => Yii::t('backend', 'Teléfono'),
            'address' => Yii::t('backend', 'Dirección'),
            'web_site' => Yii::t('backend', 'Sitio web'),
            'entry_date' => Yii::t('backend', 'Fecha de ingreso'),
            'colon_credit' => Yii::t('backend', 'Monto de crédito ¢'),
            'dollar_credit' => Yii::t('backend', 'Monto de crédito $'),
            'max_credit' => Yii::t('backend', 'Crédito máximo'),
            'credit_days_id' => Yii::t('backend', 'Días de crédito'),
            'condition_sale_id' => Yii::t('backend', 'Condición de venta'),
            'status' => Yii::t('backend', 'Estado'),
            'created_at' => Yii::t('backend', 'Fecha de creación'),
            'updated_at' => Yii::t('backend', 'Fecha de actualización'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProducts()
    {
        return $this->hasMany(Product::className(), ['supplier_id' => 'id']);
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
    public function getSupplierBankInformations()
    {
        return $this->hasMany(SupplierBankInformation::className(), ['supplier_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSupplierContacts()
    {
        return $this->hasMany(SupplierContact::className(), ['supplier_id' => 'id']);
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
        return "/supplier";
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
     * Returns a mapped array for using on Select widget
     *
     * @param boolean $check_status
     * @return array
     */
    public static function getSelectMap($check_status = false)
    {
        $query = self::find()->select(['id','name','identification']);
        if($check_status)
        {
            $query->where(['status' => self::STATUS_ACTIVE]);
        }

        $models = $query->asArray()->all();

        $array_map = [];

        if(count($models)>0)
        {
            foreach ($models AS $index => $model)
            {
                $array_map[$model['id']] = $model['identification'].' - '.$model['name'];
            }
        }

        return $array_map;
    }

    public function getContactsToSendMail()
    {
        $models = SupplierContact::findAll(['supplier_id' => $this->id]);
        $array_mails = [];
        if($models !== null)
        {
           foreach ($models AS $key => $value)
           {
               $array_mails[] = $value->email;
           }
        }

        return $array_mails;
    }
}
