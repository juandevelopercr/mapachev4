<?php

namespace backend\models\business;

use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\StringHelper;
use common\models\GlobalFunctions;
use yii\helpers\Html;

/**
 * This is the model class for table "product_has_supplier".
 *
 * @property int $product_id
 * @property int $supplier_id
 * @property string|null $physical_location
 *
 * @property Product $product
 * @property Supplier $supplier

 */
class ProductHasSupplier extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'product_has_supplier';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['product_id', 'supplier_id'], 'required'],
            [['product_id', 'supplier_id'], 'integer'],
            [['physical_location'], 'string'],
            [['product_id', 'supplier_id'], 'unique', 'targetAttribute' => ['product_id', 'supplier_id']],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::className(), 'targetAttribute' => ['product_id' => 'id']],
            [['supplier_id'], 'exist', 'skipOnError' => true, 'targetClass' => Supplier::className(), 'targetAttribute' => ['supplier_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'product_id' => Yii::t('backend', 'Producto'),
            'supplier_id' => Yii::t('backend', 'Proveedor'),
            'physical_location' => Yii::t('backend', 'Ubicación física'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(Product::className(), ['id' => 'product_id']);
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
        return "/product-has-supplier";
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
     * @param $supplier_id
     * @param $product_id
     * @return bool
     */
    public static function addRelation($supplier_id, $product_id)
    {
        $model= new ProductHasSupplier();
        $model->product_id = $product_id;
        $model->supplier_id = $supplier_id;
        $model->save();
    }

    /**
     * @param $supplier_id
     * @param $product_id
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public static function deleteRelation($supplier_id, $product_id)
    {
        $model= self::find()->where(['product_id' => $product_id, 'supplier_id' => $supplier_id])->one();

        $model->delete();
    }

    /**
     * @param $product_id
     * @param bool $as_array
     * @return array|ProductHasSupplier[]|\yii\db\ActiveRecord[]
     */
    public static function getSuppliersByProductId($product_id,$as_array = true)
    {
        $query= self::find()
            ->where(['product_id' => $product_id]);

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
    public static function getSuppliersStringByProduct($id)
    {
        $supplier = self::find()->where(['product_id'=>$id])->one();
        $result = '';

        if($supplier !== null)
        {
            $relations = self::getSuppliersByProductId($id,false);
            $array = [];
            foreach ($relations AS $key => $value)
            {
                $array[] = $value->supplier->code.' - '.$value->supplier->name;
            }

            $result = implode(', ',$array);
        }

        return $result;
    }

    /**
     * @param $id
     * @return array
     */
    public static function getItemsAsignedByProductId($id)
    {
        $items_assigned = self::getSuppliersByProductId($id);

        $items_ids= [];
        foreach ($items_assigned as $value)
        {
            $items_ids[]= $value['product_id'];
        }

        return $items_ids;
    }
}
