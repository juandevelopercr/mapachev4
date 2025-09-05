<?php

namespace backend\models\business;

use common\models\User;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\StringHelper;
use common\models\GlobalFunctions;
use yii\helpers\Html;

/**
 * This is the model class for table "collector_has_customer".
 *
 * @property int $user_id
 * @property int $seller_id
 *
 */
class UserHasSeller extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_has_seller';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'seller_id'], 'required'],
            [['user_id', 'seller_id'], 'default', 'value' => null],
            [['user_id', 'seller_id'], 'integer'],
            [['user_id', 'seller_id'], 'unique', 'targetAttribute' => ['user_id', 'seller_id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
            [['seller_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['seller_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'user_id' => Yii::t('backend', 'Usuario'),
            'seller_id' => Yii::t('backend', 'Vendedor'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVendedor()
    {
        return $this->hasOne(Customer::className(), ['id' => 'seller_id']);
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
        return "/user-has-seller";
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
     * @param $seller_id
     * @param $user_id
     * @return bool
     */
    public static function addRelation($seller_id, $user_id)
    {
        $model= new UserHasSeller();
        $model->user_id = $user_id;
        $model->seller_id = $seller_id;
        $model->save();
    }

    /**
     * @param $seller_id
     * @param $user_id
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public static function deleteRelation($seller_id, $user_id)
    {
        $model= self::find()->where(['user_id' => $user_id, 'seller_id' => $seller_id])->one();

        $model->delete();
    }

    /**
     * @param $user_id
     * @param bool $as_array
     * @return array|UserHasSeller[]|\yii\db\ActiveRecord[]
     */
    public static function getVendedorByUserId($user_id,$as_array = true)
    {
        $query= self::find()
            ->where(['user_id' => $user_id]);

        if($as_array)
        {
            $query->asArray();
        }

        $model = $query->all();

        return $model;
    }

    /**
     * $old_items_assigned elementos asociados antes de actualizar
     * $field es el campo que almacena la relacion
     * $param_to_check es el nombre del atributo a utilizar en el arrayMap
     *
     * @param $model
     * @param $old_items_assigned
     * @param $field
     * @param $param_to_check
     */
    public static function updateRelation($model, $old_items_assigned, $field, $param_to_check)
    {
        if (!empty($model->$field))
            $new_item_assigned = $model->$field;
        else
            $new_item_assigned = [];

        $toRemove = array_diff(ArrayHelper::map($old_items_assigned, $param_to_check, $param_to_check), $new_item_assigned);
        $toAdd = array_diff($new_item_assigned, ArrayHelper::map($old_items_assigned, $param_to_check, $param_to_check));
    
        if(isset($toAdd) && !empty($toAdd))
        {
            foreach ($toAdd as $item)
            {
                $result = self::addRelation($item,$model->id);
            }
        }

        if(isset($toRemove) && !empty($toRemove))
        {
            foreach ($toRemove as $item)
            {
                $result = self::deleteRelation($item,$model->id);
            }
        }
    }

    /**
     * Función que retorna un string separando por comas
     *
     * @param $id
     * @return string
     */
    public static function getVendedoresStringByUser($id)
    {
        $datos = self::find()->where(['user_id'=>$id])->one();
        $result = '';

        if($datos !== null)
        {
            $relations = self::getVendedorByUserId($id,false);
            $array = [];
            foreach ($relations AS $key => $value)
            {
                $array[] = $value->vendedor->name. ' '. $value->vendedor->last_name;
            }

            $result = implode(', ',$array);
        }

        return $result;
    }

    /**
     * @param $id
     * @return array
     */
    public static function getItemsAsignedByUserId($id)
    {
        $items_assigned = self::getVendedorByUserId($id);

        $items_ids= [];
        foreach ($items_assigned as $value)
        {
            $items_ids[]= $value['user_id'];
        }

        return $items_ids;
    }
}
