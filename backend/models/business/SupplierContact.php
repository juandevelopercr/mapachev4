<?php

namespace backend\models\business;

use backend\models\nomenclators\Department;
use backend\models\nomenclators\JobPosition;
use Yii;
use backend\models\BaseModel;
use yii\helpers\StringHelper;
use common\models\GlobalFunctions;
use yii\helpers\Html;

/**
 * This is the model class for table "supplier_contact".
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $ext
 * @property string|null $cellphone
 * @property int|null $supplier_id
 * @property int|null $department_id
 * @property int|null $job_position_id
 * @property int $status
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Supplier $supplier
 * @property Department $department
 * @property JobPosition $jobPosition

 */
class SupplierContact extends BaseModel
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'supplier_contact';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'email'],'required'],
            [['supplier_id', 'department_id', 'job_position_id', 'status'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            ['email','email'],
            [['name', 'email', 'phone', 'ext', 'cellphone'], 'string', 'max' => 255],
            [['supplier_id'], 'exist', 'skipOnError' => true, 'targetClass' => Supplier::className(), 'targetAttribute' => ['supplier_id' => 'id']],
            [['department_id'], 'exist', 'skipOnError' => true, 'targetClass' => Department::className(), 'targetAttribute' => ['department_id' => 'id']],
            [['job_position_id'], 'exist', 'skipOnError' => true, 'targetClass' => JobPosition::className(), 'targetAttribute' => ['job_position_id' => 'id']],
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
            'email' => Yii::t('backend', 'Correo electrónico'),
            'phone' => Yii::t('backend', 'Teléfono'),
            'ext' => Yii::t('backend', 'Ext'),
            'cellphone' => Yii::t('backend', 'Celular'),
            'supplier_id' => Yii::t('backend', 'Proveedor'),
            'department_id' => Yii::t('backend', 'Departamento'),
            'job_position_id' => Yii::t('backend', 'Puesto'),
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
    public function getJobPosition()
    {
        return $this->hasOne(JobPosition::className(), ['id' => 'job_position_id']);
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
        return "/supplier-contact";
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
