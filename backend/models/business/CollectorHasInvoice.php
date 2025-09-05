<?php

namespace backend\models\business;

use common\models\User;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\StringHelper;
use common\models\GlobalFunctions;
use yii\helpers\Html;

/**
 * This is the model class for table "collector_has_invoice".
 *
 * @property int $invoice_id
 * @property int $collector_id
 *
 * @property PaymentMethod $user
 * @property Invoice $invoice

 */
class CollectorHasInvoice extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'collector_has_invoice';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['invoice_id', 'collector_id'], 'required'],
            [['invoice_id', 'collector_id'], 'default', 'value' => null],
            [['invoice_id', 'collector_id'], 'integer'],
            [['invoice_id', 'collector_id'], 'unique', 'targetAttribute' => ['invoice_id', 'collector_id']],
            [['collector_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['collector_id' => 'id']],
            [['invoice_id'], 'exist', 'skipOnError' => true, 'targetClass' => Invoice::className(), 'targetAttribute' => ['invoice_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'invoice_id' => Yii::t('backend', 'Factura'),
            'collector_id' => Yii::t('backend', 'Agente Cobrador'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCollector()
    {
        return $this->hasOne(User::className(), ['id' => 'collector_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getInvoice()
    {
        return $this->hasOne(Invoice::className(), ['id' => 'invoice_id']);
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
        return "/collector-has-invoice";
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
     * @param $collector_id
     * @param $invoice_id
     * @return bool
     */
    public static function addRelation($collector_id, $invoice_id)
    {
        $model= new CollectorHasInvoice();
        $model->invoice_id = $invoice_id;
        $model->collector_id = $collector_id;
        $model->save();
    }

    /**
     * @param $collector_id
     * @param $invoice_id
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public static function deleteRelation($collector_id, $invoice_id)
    {
        $model= self::find()->where(['invoice_id' => $invoice_id, 'collector_id' => $collector_id])->one();

        $model->delete();
    }

    /**
     * @param $invoice_id
     * @param bool $as_array
     * @return array|CollectorHasInvoice[]|\yii\db\ActiveRecord[]
     */
    public static function getCollectorByInvoiceId($invoice_id,$as_array = true)
    {
        $query= self::find()
            ->where(['invoice_id' => $invoice_id]);

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
    public static function getCollectorStringByInvoice($id)
    {
        //$payment_method = self::find()->where(['invoice_id'=>$id])->one();
        $result = '';

        //if($payment_method !== null)
        //{
        $relations = self::getCollectorByInvoiceId($id,false);
        $array = [];
        foreach ($relations AS $key => $value)
        {
            $array[] = $value->collector->name. ' '. $value->collector->last_name;
        }

        $result = implode(', ',$array);
        //}

        return $result;
    }

    /**
     * @param $id
     * @return array
     */
    public static function getItemsAsignedByInvoiceId($id)
    {
        $items_assigned = self::getCollectorByInvoiceId($id);

        $items_ids= [];
        foreach ($items_assigned as $value)
        {
            $items_ids[]= $value['invoice_id'];
        }

        return $items_ids;
    }
}
