<?php

namespace backend\models\business;

use Yii;
use common\models\User;
use backend\models\BaseModel;
use yii\helpers\StringHelper;
use common\models\GlobalFunctions;
use yii\helpers\Html;
/**
 * This is the model class for table "item_manual_invoice".
 *
 * @property int $id
 * @property int|null $invoice_id
 * @property string|null $description
 * @property int|null $service_id
 * @property float|null $quantity
 * @property float|null $price
 * @property int|null $user_id
 * @property int|null $unit_type_id
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property ManualInvoice $invoice
 * @property Service $service
 * @property User $user
 */
class ItemManualInvoice extends BaseModel
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'item_manual_invoice';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['invoice_id', 'service_id', 'user_id', 'unit_type_id'], 'default', 'value' => null],
            [['invoice_id', 'service_id', 'user_id', 'unit_type_id'], 'integer'],
            [['quantity', 'price'], 'number'],
            [['created_at', 'updated_at'], 'safe'],
            [['description'], 'string', 'max' => 255],
            [['invoice_id'], 'exist', 'skipOnError' => true, 'targetClass' => ManualInvoice::className(), 'targetAttribute' => ['invoice_id' => 'id']],
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
            'invoice_id' => 'Factura',
            'description' => 'Descripción',
            'service_id' => 'Servicio',
            'quantity' => 'Cantidad',
            'price' => 'Precio',
            'user_id' => 'Usuario',
            'unit_type_id' => 'Unidad medida',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[Invoice]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getInvoice()
    {
        return $this->hasOne(ManualInvoice::className(), ['id' => 'invoice_id']);
    }

    /**
     * Gets query for [[Service]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getService()
    {
        return $this->hasOne(Service::className(), ['id' => 'service_id']);
    }

    /**
     * Gets query for [[User]].
     *
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
        return "/item-manual-invoice";
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
}
