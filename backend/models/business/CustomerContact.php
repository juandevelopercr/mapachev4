<?php

namespace backend\models\business;

use backend\models\nomenclators\Department;
use Yii;
use backend\models\BaseModel;
use yii\helpers\StringHelper;
use common\models\GlobalFunctions;
use yii\helpers\Html;

/**
 * This is the model class for table "customer_contact".
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $last_name
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $fax
 * @property string|null $ext
 * @property string|null $cellphone
 * @property int|null $customer_id
 * @property int|null $department_id
 * @property int $status
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Department $department
 * @property Customer $customer

 */
class CustomerContact extends BaseModel
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'customer_contact';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name','email','customer_id', 'department_id','last_name', 'email', 'phone'],'required'],
            [['customer_id', 'department_id', 'status'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['name', 'last_name', 'email', 'phone', 'fax', 'ext', 'cellphone'], 'string', 'max' => 255],
            [['department_id'], 'exist', 'skipOnError' => true, 'targetClass' => Department::className(), 'targetAttribute' => ['department_id' => 'id']],
            [['customer_id'], 'exist', 'skipOnError' => true, 'targetClass' => Customer::className(), 'targetAttribute' => ['customer_id' => 'id']],
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
            'last_name' => Yii::t('backend', 'Apellidos'),
            'email' => Yii::t('backend', 'Correo electrónico'),
            'phone' => Yii::t('backend', 'Teléfono'),
            'fax' => Yii::t('backend', 'Fax'),
            'ext' => Yii::t('backend', 'Ext'),
            'cellphone' => Yii::t('backend', 'Celular'),
            'customer_id' => Yii::t('backend', 'Cliente'),
            'department_id' => Yii::t('backend', 'Departamento'),
            'status' => Yii::t('backend', 'Estado'),
            'created_at' => Yii::t('backend', 'Fecha de creación'),
            'updated_at' => Yii::t('backend', 'Fecha de actualización'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDepartment()
    {
        return $this->hasOne(Department::className(), ['id' => 'department_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCustomer()
    {
        return $this->hasOne(Customer::className(), ['id' => 'customer_id']);
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
        return "/customer-contact";
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
