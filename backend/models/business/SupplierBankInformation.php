<?php

namespace backend\models\business;

use Yii;
use backend\models\BaseModel;
use yii\helpers\StringHelper;
use common\models\GlobalFunctions;
use yii\helpers\Html;

/**
 * This is the model class for table "supplier_bank_information".
 *
 * @property int $id
 * @property string|null $banck_name
 * @property string|null $checking_account
 * @property string|null $customer_account
 * @property string|null $mobile_account
 * @property int|null $supplier_id
 * @property bool|null $status
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property Supplier $supplier

 */
class SupplierBankInformation extends BaseModel
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'supplier_bank_information';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['banck_name', 'checking_account', 'customer_account','supplier_id'],'required'],
            [['supplier_id'], 'integer'],
            [['status'], 'boolean'],
            [['created_at', 'updated_at'], 'safe'],
            [['banck_name', 'checking_account', 'customer_account', 'mobile_account'], 'string', 'max' => 255],
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
            'banck_name' => Yii::t('backend', 'Nombre del banco'),
            'checking_account' => Yii::t('backend', 'Cuenta corriente'),
            'customer_account' => Yii::t('backend', 'Cuenta cliente(SINPE)'),
            'mobile_account' => Yii::t('backend', 'SINPE Móvil'),
            'supplier_id' => Yii::t('backend', 'Proveedor'),
            'status' => Yii::t('backend', 'Estado'),
            'created_at' => Yii::t('backend', 'Fecha de creación'),
            'updated_at' => Yii::t('backend', 'Fecha de actualización'),
        ];
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
        return "/supplier-bank-information";
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

}
