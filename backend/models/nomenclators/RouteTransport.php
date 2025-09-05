<?php

namespace backend\models\nomenclators;

use backend\models\business\Customer;
use Yii;
use backend\models\BaseModel;
use yii\helpers\StringHelper;
use common\models\GlobalFunctions;
use yii\helpers\Html;

/**
 * This is the model class for table "route_transport".
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $code
 * @property string|null $description
 * @property bool|null $status
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property Customer[] $customers
 * @property RouteTransportHasCollector[] $routeTransportHasCollectors
 * @property Collector[] $collectors
 *
 */
class RouteTransport extends BaseModel
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'route_transport';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name','code'],'required'],
            ['code','unique'],
            [['description'], 'string'],
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
            'description' => Yii::t('backend', 'Descripción'),
            'status' => Yii::t('backend', 'Estado'),
            'created_at' => Yii::t('backend', 'Fecha de creación'),
            'updated_at' => Yii::t('backend', 'Fecha de actualización'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCustomers()
    {
        return $this->hasMany(Customer::className(), ['route_transport_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRouteTransportHasCollectors()
    {
        return $this->hasMany(RouteTransportHasCollector::className(), ['route_transport_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCollectors()
    {
        return $this->hasMany(Collector::className(), ['id' => 'collector_id'])->viaTable('route_transport_has_collector', ['route_transport_id' => 'id']);
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
    * @return string base route_transport to model links, default to '/'
    */
    public function getBaseLink()
    {
        return "/route-transport";
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
                $array_map[$model['id']] = $model['code'].' - '.$model['name'];
            }
        }

        return $array_map;
    }

    /**
     * @return string
     */
    public function generateCode()
    {
        $max_code = self::find()->max('code');
        $code = is_null($max_code) ? 1: ($max_code + 1);
        return GlobalFunctions::zeroFill($code,2);
    }
}
