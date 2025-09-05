<?php

namespace backend\models\business;

use common\models\User;
use Yii;
use backend\models\BaseModel;
use yii\helpers\StringHelper;
use common\models\GlobalFunctions;
use yii\helpers\Html;

/**
 * This is the model class for table "reception_item_po".
 *
 * @property int $id
 * @property int|null $item_payment_order_id
 * @property float|null $received
 * @property int|null $user_id
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property ItemPaymentOrder $itemPaymentOrder
 * @property User $user

 */
class ReceptionItemPo extends BaseModel
{
    public $payment_order_id;
    public $bar_code;
    public $supplier_code;
    public $description;
    public $quantity;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'reception_item_po';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['item_payment_order_id', 'user_id'], 'integer'],
            [['received'], 'number'],
            [['created_at', 'updated_at'], 'safe'],
            [['item_payment_order_id'], 'exist', 'skipOnError' => true, 'targetClass' => ItemPaymentOrder::className(), 'targetAttribute' => ['item_payment_order_id' => 'id']],
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
            'item_payment_order_id' => Yii::t('backend', 'Item'),
            'received' => Yii::t('backend', 'Recibido'),
            'user_id' => Yii::t('backend', 'Usuario'),
            'created_at' => Yii::t('backend', 'Fecha de creación'),
            'updated_at' => Yii::t('backend', 'Fecha de actualización'),
            'bar_code' => Yii::t('backend', 'Código de barras'),
            'supplier_code' => Yii::t('backend', 'Código de proveedor'),
            'description' => Yii::t('backend', 'Descripción'),
            'quantity' => Yii::t('backend', 'Solicitado'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItemPaymentOrder()
    {
        return $this->hasOne(ItemPaymentOrder::className(), ['id' => 'item_payment_order_id']);
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
        return "/reception-item-po";
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
