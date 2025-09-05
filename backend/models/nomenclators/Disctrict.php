<?php

namespace backend\models\nomenclators;

use backend\models\business\Customer;
use Yii;
use backend\models\BaseModel;
use yii\helpers\StringHelper;
use common\models\GlobalFunctions;
use yii\helpers\Html;

/**
 * This is the model class for table "disctrict".
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $code
 * @property string|null $description
 * @property int $status
 * @property string $created_at
 * @property string $updated_at
 * @property int|null $canton_id
 *
 * @property Customer[] $customers
 * @property Canton $canton

 */
class Disctrict extends BaseModel
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'disctrict';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['description'], 'string'],
            [['status', 'canton_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['name', 'code'], 'string', 'max' => 255],
            [['canton_id'], 'exist', 'skipOnError' => true, 'targetClass' => Canton::className(), 'targetAttribute' => ['canton_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('backend', 'ID'),
            'name' => Yii::t('backend', 'Name'),
            'code' => Yii::t('backend', 'Code'),
            'description' => Yii::t('backend', 'Description'),
            'status' => Yii::t('backend', 'Status'),
            'created_at' => Yii::t('backend', 'Created At'),
            'updated_at' => Yii::t('backend', 'Updated At'),
            'canton_id' => Yii::t('backend', 'Canton ID'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCustomers()
    {
        return $this->hasMany(Customer::className(), ['disctrict_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCanton()
    {
        return $this->hasOne(Canton::className(), ['id' => 'canton_id']);
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
        return "/disctrict";
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
     * @param $canton_id
     * @return array
     */
    public static function getSelectMapSpecific($canton_id)
    {
        $models = self::find()
            ->where(['status' => self::STATUS_ACTIVE,'canton_id'=> $canton_id])
            ->asArray()
            ->all();

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
}
