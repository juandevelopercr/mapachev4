<?php

namespace backend\models\business;

use Yii;
use backend\models\nomenclators\UnitType;
use backend\models\nomenclators\UtilsConstants;
use backend\models\nomenclators\TaxRateType;
use backend\models\nomenclators\TaxType;
use backend\models\nomenclators\ExonerationDocumentType;
use common\models\User;
use backend\models\BaseModel;
use yii\helpers\StringHelper;
use common\models\GlobalFunctions;
use yii\helpers\Html;

/**
 * This is the model class for table "item_purchase_order".
 *
 * @property int $id
 * @property int|null $purchase_order_id
 * @property string|null $code
 * @property string|null $description
 * @property int|null $product_id
 * @property int|null $service_id
 * @property float|null $quantity
 * @property float|null $price_unit
 * @property float|null $subtotal
 * @property float|null $tax_amount
 * @property float|null $discount_amount
 * @property float|null $exonerate_amount
 * @property float|null $price_total
 * @property int|null $user_id
 * @property int|null $price_type
 * @property int|null $unit_type_id
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property Product $product
 * @property PurchaseOrder $purchase_order
 * @property Service $service
 * @property User $user
 * @property UnitType $unitType

 */
class ItemPurchaseOrder extends BaseModel
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'item_purchase_order';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['code','description','quantity','subtotal','price_unit'],'required'],
            [['description','quantity','subtotal','price_unit'],'required'],
            [['purchase_order_id', 'product_id', 'service_id', 'user_id', 'price_type','unit_type_id'], 'default', 'value' => null],
            [['purchase_order_id', 'product_id', 'service_id', 'user_id', 'price_type','unit_type_id', 'tax_type_id', 'tax_rate_type_id', 'exoneration_document_type_id', 'exoneration_purchase_percent'], 'integer'],
            [['quantity', 'price_unit', 'subtotal', 'tax_amount', 'discount_amount', 'exonerate_amount', 'price_total', 'tax_rate_percent'], 'number'],
            [['created_at', 'updated_at', 'exoneration_date'], 'safe'],
            [['code', 'description'], 'string', 'max' => 255],
            [['number_exoneration_doc'], 'string', 'max' => 17],
            [['name_institution_exoneration'], 'string', 'max' => 100],
            [['nature_discount'], 'string', 'max' => 80],            
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::className(), 'targetAttribute' => ['product_id' => 'id']],
            [['purchase_order_id'], 'exist', 'skipOnError' => true, 'targetClass' => PurchaseOrder::className(), 'targetAttribute' => ['purchase_order_id' => 'id']],
            [['service_id'], 'exist', 'skipOnError' => true, 'targetClass' => Service::className(), 'targetAttribute' => ['service_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
            [['unit_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => UnitType::className(), 'targetAttribute' => ['unit_type_id' => 'id']],
            [['tax_rate_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => TaxRateType::className(), 'targetAttribute' => ['tax_rate_type_id' => 'id']],
            [['tax_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => TaxType::className(), 'targetAttribute' => ['tax_type_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'purchase_order_id' => Yii::t('backend', 'Orden de pedido'),
            'code' => Yii::t('backend', 'Código'),
            'description' => Yii::t('backend', 'Descripción'),
            'product_id' => Yii::t('backend', 'Producto'),
            'service_id' => Yii::t('backend', 'Servicio'),
            'quantity' => Yii::t('backend', 'Cantidad'),
            'price_unit' => Yii::t('backend', 'Precio unidad'),
            'subtotal' => Yii::t('backend', 'Subtotal'),
            'tax_amount' => Yii::t('backend', 'IVA'),
            'discount_amount' => Yii::t('backend', 'Descuento'),
            'exonerate_amount' => Yii::t('backend', 'Exoneración'),
            'price_total' => Yii::t('backend', 'Total'),
            'user_id' => Yii::t('backend', 'Usuario'),
            'price_type' => Yii::t('backend', 'Lista precio'),
            'unit_type_id' => Yii::t('backend', 'Tipo/Unidad'),
            'created_at' => Yii::t('backend', 'Fecha de creación'),
            'updated_at' => Yii::t('backend', 'Fecha de actualización'),
            'nature_discount' => Yii::t('backend', 'Naturaleza del decuento'),
            'tax_type_id'=> Yii::t('backend', 'Código del impuesto'),
            'tax_rate_type_id'=> Yii::t('backend', 'Código tarifa de impuesto'),
            'tax_rate_percent' => Yii::t('backend', 'Tarifa Impuesto %'),
            'exoneration_document_type_id' => Yii::t('backend', 'Tipo documento'),
            'number_exoneration_doc' => Yii::t('backend', 'No. documento'),
            'name_institution_exoneration' => Yii::t('backend', 'Instituto emite'),
            'exoneration_date' => Yii::t('backend', 'Fecha de emisión'),
            'exoneration_purchase_percent' => Yii::t('backend', 'Porcentaje compra'),
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
    public function getPurchaseOrder()
    {
        return $this->hasOne(PurchaseOrder::className(), ['id' => 'purchase_order_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getService()
    {
        return $this->hasOne(Service::className(), ['id' => 'service_id']);
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
    public function getUnitType()
    {
        return $this->hasOne(UnitType::className(), ['id' => 'unit_type_id']);
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
        return "/item-purchase-order";
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
     * @return array
     */
    public function fields()
    {

        $fields['id'] = 'id';
        $fields['purchase_order_id'] = 'purchase_order_id';
        $fields['code'] = 'code';
        $fields['description'] = 'description';

        $fields['product_id'] = 'product_id';
        $fields['product_label'] = function(ItemPurchaseOrder $model){
            return isset($model->product_id)? $model->product->description : '';
        };

        $fields['service_id'] = 'service_id';
        $fields['service_label'] = function(ItemPurchaseOrder $model){
            return isset($model->service_id)? $model->service->name : '';
        };

        $fields['quantity'] = 'quantity';
        $fields['price_unit'] = 'price_unit';
        $fields['subtotal'] = 'subtotal';
        $fields['tax_amount'] = 'tax_amount';
        $fields['discount_amount'] = 'discount_amount';
        $fields['exonerate_amount'] = 'exonerate_amount';
        $fields['price_total'] = 'price_total';

        $fields['price_type'] = 'price_type';
        $fields['price_type_label'] = function(ItemPurchaseOrder $model){
            return isset($model->price_type)? UtilsConstants::getCustomerAsssignPriceSelectType($model->price_type) : '';
        };

        $fields['unit_type_id'] = 'unit_type_id';
        $fields['unit_type_label'] = function(ItemPurchaseOrder $model){
            return isset($model->unit_type_id)? $model->unitType->code : '';
        };

        $fields['user_id'] = 'user_id';
        $fields['user_label'] = function(ItemPurchaseOrder $model){
            return isset($model->user_id)? User::getFullNameByUserId($model->user_id) : '';
        };

        $fields['created_at'] = 'created_at';
        $fields['updated_at'] = 'updated_at';

        return $fields;
    }

    public function getMontoTotalLinea()
    {
        return ($this->subtotal + $this->tax_amount - $this->exonerate_amount);
    }    
}
