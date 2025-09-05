<?php

namespace backend\models\nomenclators;

use Yii;
use backend\models\BaseModel;
use yii\helpers\StringHelper;
use common\models\GlobalFunctions;
use yii\helpers\Html;

/**
 * This is the model class for table "reference_code".
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $code
 * @property bool|null $status
 * @property string|null $created_at
 * @property string|null $updated_at

 */
class ReferenceCode extends BaseModel
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'reference_code';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['status'], 'boolean'],
            [['created_at', 'updated_at'], 'safe'],
            [['name', 'code'], 'string', 'max' => 255],
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
            'status' => Yii::t('backend', 'Estado'),
            'created_at' => Yii::t('backend', 'Fecha de creación'),
            'updated_at' => Yii::t('backend', 'Fecha de actualización'),
        ];
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
        return "/reference-code";
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
     * Returns a mapped array for using on Select widget
     *
     * @param boolean $check_status
     * @return array
     */
    public static function getSelectMap($check_status = false)
    {
        $query = self::find();
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
                $array_map[$model['code']] = $model['code'].' - '.$model['name'];
            }
        }

        return $array_map;
    }

    public static function getIdReferenceCodeByCode($code)
    {
        $model = self::find()->select(['id'])->where(['code' => $code])->one();
        return $model->id;
    }


    public static function getSelectMapStatic()
    {
        return ['01'=>'01- Anula factura', '02'=>'02- Devolución de Mercancia'];
    }   

    public static function getSelectMapStaticDevolucion()
    {
        return ['02'=>'02- Devolución de Mercancia'];
    }   
    
}
