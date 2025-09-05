<?php

namespace backend\models\business;

use common\models\User;
use Yii;
use backend\models\BaseModel;
use yii\helpers\StringHelper;
use common\models\GlobalFunctions;
use yii\helpers\Html;

/**
 * This is the model class for table "item_payment_order".
 *
 * @property int $id
 * @property int|null $payment_order_id
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
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property PaymentOrder $paymentOrder
 * @property Product $product
 * @property Service $service
 * @property User $user

 */
class ItemPaymentOrder extends BaseModel
{
    public $supplier_code;
    public $unit_type_id;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'item_payment_order';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['payment_order_id', 'product_id', 'service_id', 'user_id'], 'default', 'value' => null],
            [['payment_order_id', 'product_id', 'service_id', 'user_id','unit_type_id'], 'integer'],
            [['quantity', 'price_unit', 'subtotal', 'tax_amount', 'discount_amount', 'exonerate_amount', 'price_total'], 'number'],
            [['created_at', 'updated_at', 'supplier_code','unit_type_id'], 'safe'],
            [['code', 'description', 'supplier_code'], 'string', 'max' => 255],
            [['payment_order_id'], 'exist', 'skipOnError' => true, 'targetClass' => PaymentOrder::className(), 'targetAttribute' => ['payment_order_id' => 'id']],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::className(), 'targetAttribute' => ['product_id' => 'id']],
            [['service_id'], 'exist', 'skipOnError' => true, 'targetClass' => Service::className(), 'targetAttribute' => ['service_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'payment_order_id' => Yii::t('backend', 'Orden de compra'),
            'code' => Yii::t('backend', 'Código de barras'),
            'supplier_code' => Yii::t('backend', 'Código de proveedor'),
            'description' => Yii::t('backend', 'Descripción'),
            'product_id' => Yii::t('backend', 'Producto'),
            'service_id' => Yii::t('backend', 'Servicio'),
            'unit_type_id' => Yii::t('backend', 'Tipo/Unidad'),
            'quantity' => Yii::t('backend', 'Cantidad'),
            'price_unit' => Yii::t('backend', 'Precio unitario'),
            'subtotal' => Yii::t('backend', 'Subtotal'),
            'tax_amount' => Yii::t('backend', 'IVA'),
            'discount_amount' => Yii::t('backend', 'Descuento'),
            'exonerate_amount' => Yii::t('backend', 'Exoneración'),
            'price_total' => Yii::t('backend', 'Total'),
            'user_id' => Yii::t('backend', 'Usuario'),
            'created_at' => Yii::t('backend', 'Fecha de creación'),
            'updated_at' => Yii::t('backend', 'Fecha de actualización'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPaymentOrder()
    {
        return $this->hasOne(PaymentOrder::className(), ['id' => 'payment_order_id']);
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
        return "/item-payment-order";
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
