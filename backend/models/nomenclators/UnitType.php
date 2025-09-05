<?php

namespace backend\models\nomenclators;

use backend\models\business\ItemProforma;
use backend\models\business\Product;
use backend\models\business\Service;
use phpDocumentor\Reflection\Types\Self_;
use Yii;
use backend\models\BaseModel;
use yii\helpers\StringHelper;
use common\models\GlobalFunctions;
use yii\helpers\Html;

/**
 * This is the model class for table "unit_type".
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $code
 * @property string|null $description
 * @property bool|null $status
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property ItemProforma[] $itemProformas
 * @property Product[] $products
 * @property Service[] $services

 */
class UnitType extends BaseModel
{
    const CODE_OTROS = 'Otros';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'unit_type';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name','code'],'required'],
            [['description'], 'string'],
            [['status'], 'integer'],
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
            'description' => Yii::t('backend', 'Descripción'),
            'status' => Yii::t('backend', 'Estado'),
            'created_at' => Yii::t('backend', 'Fecha de creación'),
            'updated_at' => Yii::t('backend', 'Fecha de actualización'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItemProformas()
    {
        return $this->hasMany(ItemProforma::className(), ['unit_type_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProducts()
    {
        return $this->hasMany(Product::className(), ['unit_type_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getServices()
    {
        return $this->hasMany(Service::className(), ['unit_type_id' => 'id']);
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
        return "/unit-type";
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
    public static function getSelectMap($check_status = false,$only_code = false, $simplify = true)
    {
        $query = self::find();
        if($check_status)
        {
            $query->where(['status' => self::STATUS_ACTIVE]);
        }

        if($simplify) {
            $query->andWhere(['IN','code',['PAQ', 'Unid', 'BULT', 'CAJ']]);
        }

        $models = $query->asArray()->all();

        $array_map = [];

        if(count($models)>0)
        {
            foreach ($models AS $index => $model)
            {
                if($only_code)
                {
                    $array_map[$model['id']] = $model['code'];
                }
                else
                {
                    $array_map[$model['id']] = $model['code'].' - '.$model['name'];

                }
            }
        }
        return $array_map;
    }

    public static function getSelectMapProfessionalService($check_status = false,$only_code = false, $simplify = true)
    {
        $query = self::find();
        if($check_status)
        {
            $query->where(['status' => self::STATUS_ACTIVE]);
        }

        if($simplify) {
            $query->andWhere(['IN','code',['Sp']]);
        }

        $models = $query->asArray()->all();

        $array_map = [];

        if(count($models)>0)
        {
            foreach ($models AS $index => $model)
            {
                if($only_code)
                {
                    $array_map[$model['id']] = $model['code'];
                }
                else
                {
                    $array_map[$model['id']] = $model['code'].' - '.$model['name'];

                }
            }
        }

        return $array_map;
    }    

    /**
     * Returns a mapped array for using on Select widget
     *
     * @param boolean $check_status
     * @return array
     */
    public static function getSelectMapByCode($code)
    {
        $query = self::find();
        $query->where(['code' => $code]);

        $models = $query->asArray()->all();

        $array_map = [];

        if(count($models)>0)
        {
            foreach ($models AS $index => $model)
            {
                $array_map[$model['id']] = $model['code'].' - '.$model['name'];
            }
        }

        return $array_map;
    }    

    /**
     * @param $code
     * @return int|mixed
     */
    public static function getUnitTypeIdByCode($code)
    {
        $model = self::find()->where(['code' => $code])->one();
        return $model->id;
    }
}