<?php

namespace backend\models\business;

use Yii;
use yii\helpers\Html;
use common\models\User;
use backend\models\BaseModel;
use yii\helpers\StringHelper;
use common\models\GlobalFunctions;
use backend\models\nomenclators\TaxType;
use backend\models\business\SectorLocation;
use backend\models\nomenclators\TaxRateType;
use backend\models\nomenclators\UtilsConstants;

/**
 * This is the model class for table "item_entry".
 *
 * @property int $id
 * @property int|null $entry_id
 * @property string|null $product_code
 * @property string|null $product_description
 * @property int|null $product_id
 * @property float|null $past_price
 * @property float|null $price
 * @property float|null $past_quantity
 * @property float|null $entry_quantity
 * @property float|null $new_quantity
 * @property string|null $observations
 * @property int|null $user_id
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property int|null $sector_location_id
 *
 * @property Entry $entry
 * @property SectorLocation $sectorLocation
 * @property Product $product
 * @property User $user

 */
class ItemEntry extends BaseModel
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'item_entry';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['entry_id', 'product_id', 'user_id', 'entry_quantity','price', 'sector_location_id'], 'required'],
            [['entry_id', 'product_id', 'user_id', 'sector_location_id', 'tax_type_id', 'tax_rate_type_id',], 'integer'],
            [['past_price', 'price', 'past_quantity', 'entry_quantity', 'new_quantity', 'tax_amount', 'tax_rate_percent', 'subtotal'], 'number'],
            [['observations'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['product_code', 'product_description'], 'string', 'max' => 255],
            [['entry_id'], 'exist', 'skipOnError' => true, 'targetClass' => Entry::className(), 'targetAttribute' => ['entry_id' => 'id']],
            [['sector_location_id'], 'exist', 'skipOnError' => true, 'targetClass' => SectorLocation::className(), 'targetAttribute' => ['sector_location_id' => 'id']],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::className(), 'targetAttribute' => ['product_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
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
            'entry_id' => Yii::t('backend', 'Entrada'),
            'product_code' => Yii::t('backend', 'Código'),
            'product_description' => Yii::t('backend', 'Descripción'),
            'product_id' => Yii::t('backend', 'Producto'),
            'past_price' => Yii::t('backend', 'Precio anterior'),
            'price' => Yii::t('backend', 'Precio'),
            'tax_amount' => Yii::t('backend', 'IVA'),
            'tax_type_id'=> Yii::t('backend', 'Código del impuesto'),
            'tax_rate_type_id'=> Yii::t('backend', 'Código tarifa de impuesto'),
            'tax_rate_percent' => Yii::t('backend', 'Tarifa Impuesto %'),
            'subtotal' => Yii::t('backend', 'Subtotal'),
            'past_quantity' => Yii::t('backend', 'Cantidad anterior'),
            'entry_quantity' => Yii::t('backend', 'Cantidad ingresada'),
            'new_quantity' => Yii::t('backend', 'Nueva cantidad'),
            'observations' => Yii::t('backend', 'Observaciones'),
            'user_id' => Yii::t('backend', 'Usuario'),
            'created_at' => Yii::t('backend', 'Fecha de creación'),
            'updated_at' => Yii::t('backend', 'Fecha de actualización'),
            'sector_location_id' => Yii::t('backend', 'Ubicación'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEntry()
    {
        return $this->hasOne(Entry::className(), ['id' => 'entry_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPhysicalLocation()
    {
        return $this->hasOne(SectorLocation::className(), ['id' => 'sector_location_id']);
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
        return "/item-entry";
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

    public function extractInfoProduct()
    {
        $product = Product::findOne($this->product_id);
        $past_quantity = ProductHasBranchOffice::getQuantity($this->product_id,$this->entry->branch_office_id);

        $this->product_code = $product->bar_code;
        $this->product_description = $product->description;
        $this->past_price = $product->price;
        $this->past_quantity = ($past_quantity !== false)? $past_quantity : 0;
        $this->new_quantity = $this->past_quantity + $this->entry_quantity;
    }

    /**
     * @param ItemImported $model_item_imported
     * @return bool
     */
    public static function convertXmlItemToEntryItem($model_item_imported)
    {
        $model = new ItemEntry();
        $model->entry_id = $model_item_imported->xmlImported->entry_id;
        $model->user_id = Yii::$app->user->id;
        $product_model = Product::find()->where(['supplier_code' => $model_item_imported->code])->one();
        $model->product_id = $product_model->id;
        $model->product_code = $product_model->supplier_code;
        $model->product_description = $product_model->description;
        $model->past_price = $product_model->price;
        $model->entry_quantity = $model_item_imported->quantity;
        $model->price = $model_item_imported->price_by_unit;
        $model->past_quantity = ProductHasBranchOffice::getQuantity($model->product_id,$model->entry->branch_office_id);
        $model->new_quantity = $model->past_quantity + $model->entry_quantity;
        $model->sector_location_id = $model_item_imported->sector_location_id;

        if ($model->save())
        {
            //Actualizar cantidad en la ubicacion especifica del sector de una sucursal
            PhysicalLocation::updateQuantity($model->product_id, $model->sector_location_id, $model->entry_quantity, PhysicalLocation::CHANGE_QUANTITY_PLUS);

            //Actualizar cantidad general de una sucursal
            ProductHasBranchOffice::updateQuantity($model->product_id, $model->entry->branch_office_id, $model->entry_quantity,ProductHasBranchOffice::CHANGE_QUANTITY_PLUS);

            //Actualizar cantidad total del producto
            Product::updateQuantity($model->product_id, $model->entry_quantity,Product::CHANGE_QUANTITY_PLUS);

            //Registrar el tipo de ajuste realizado
            Adjustment::add(
                $model->product_id,
                UtilsConstants::ADJUSTMENT_TYPE_ENTRY,
                $model->entry_quantity,
                $model->new_quantity,
                $model->past_quantity,
                $model->entry->branch_office_id,
                $model->sector_location_id,
                $model->entry->invoice_number
            );

            return true;
        }
        else
        {
            return false;
        }
    }
}
