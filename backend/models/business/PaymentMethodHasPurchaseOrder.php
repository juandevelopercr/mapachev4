<?php

namespace backend\models\business;

use backend\models\nomenclators\PaymentMethod;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\StringHelper;
use common\models\GlobalFunctions;
use yii\helpers\Html;

/**
 * This is the model class for table "payment_method_has_purchase_order".
 *
 * @property int $purchase_order_id
 * @property int $payment_method_id
 *
 * @property PaymentMethod $paymentMethod
 * @property PurchaseOrder $purchase_order

 */
class PaymentMethodHasPurchaseOrder extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'payment_method_has_purchase_order';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['purchase_order_id', 'payment_method_id'], 'required'],
            [['purchase_order_id', 'payment_method_id'], 'default', 'value' => null],
            [['purchase_order_id', 'payment_method_id'], 'integer'],
            [['purchase_order_id', 'payment_method_id'], 'unique', 'targetAttribute' => ['purchase_order_id', 'payment_method_id']],
            [['payment_method_id'], 'exist', 'skipOnError' => true, 'targetClass' => PaymentMethod::className(), 'targetAttribute' => ['payment_method_id' => 'id']],
            [['purchase_order_id'], 'exist', 'skipOnError' => true, 'targetClass' => PurchaseOrder::className(), 'targetAttribute' => ['purchase_order_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'purchase_order_id' => Yii::t('backend', 'PurchaseOrder ID'),
            'payment_method_id' => Yii::t('backend', 'Payment Method ID'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPaymentMethod()
    {
        return $this->hasOne(PaymentMethod::className(), ['id' => 'payment_method_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPurchaseOrder()
    {
        return $this->hasOne(PurchaseOrder::className(), ['id' => 'purchase_order_id']);
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
        return "/payment-method-has-purchase-order";
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
     * @param $payment_method_id
     * @param $purchase_order_id
     * @return bool
     */
    public static function addRelation($payment_method_id, $purchase_order_id)
    {
        $model= new PaymentMethodHasPurchaseOrder();
        $model->purchase_order_id = $purchase_order_id;
        $model->payment_method_id = $payment_method_id;
        $model->save();
    }

    /**
     * @param $payment_method_id
     * @param $purchase_order_id
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public static function deleteRelation($payment_method_id, $purchase_order_id)
    {
        $model= self::find()->where(['purchase_order_id' => $purchase_order_id, 'payment_method_id' => $payment_method_id])->one();

        $model->delete();
    }

    /**
     * @param $purchase_order_id
     * @param bool $as_array
     * @return array|PaymentMethodHasPurchaseOrder[]|\yii\db\ActiveRecord[]
     */
    public static function getPaymentMethodByPurchaseOrderId($purchase_order_id,$as_array = true)
    {
        $query= self::find()
            ->where(['purchase_order_id' => $purchase_order_id]);

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
    public static function getPaymentMethodStringByPurchaseOrder($id)
    {
        $payment_method = self::find()->where(['purchase_order_id'=>$id])->one();
        $result = '';

        if($payment_method !== null)
        {
            $relations = self::getPaymentMethodByPurchaseOrderId($id,false);
            $array = [];
            foreach ($relations AS $key => $value)
            {
                $array[] = $value->paymentMethod->code.' - '.$value->paymentMethod->name;
            }

            $result = implode(', ',$array);
        }

        return $result;
    }

    /**
     * @param $id
     * @return array
     */
    public static function getItemsAsignedByPurchaseOrderId($id)
    {
        $items_assigned = self::getPaymentMethodByPurchaseOrderId($id);

        $items_ids= [];
        foreach ($items_assigned as $value)
        {
            $items_ids[]= $value['purchase_order_id'];
        }

        return $items_ids;
    }
}
