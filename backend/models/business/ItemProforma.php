<?php

namespace backend\models\business;

use Yii;
use backend\models\nomenclators\UnitType;
use common\models\User;
use backend\models\nomenclators\TaxRateType;
use backend\models\nomenclators\TaxType;
use backend\models\nomenclators\ExonerationDocumentType;
use backend\models\BaseModel;
use yii\helpers\StringHelper;
use common\models\GlobalFunctions;
use yii\helpers\Html;

/**
 * This is the model class for table "item_proforma".
 *
 * @property int $id
 * @property int|null $proforma_id
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
 * @property Proforma $proforma
 * @property Service $service
 * @property User $user
 * @property UnitType $unitType

 */
class ItemProforma extends BaseModel
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'item_proforma';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['proforma_id', 'code','description','quantity','subtotal','price_unit', 'unit_type_id'],'required'],
            [['product_id', 'service_id', 'user_id', 'price_type','unit_type_id'], 'default', 'value' => null],
            [['proforma_id', 'product_id', 'service_id', 'user_id', 'price_type','unit_type_id', 'tax_type_id', 'tax_rate_type_id', 'exoneration_document_type_id', 'exoneration_purchase_percent'], 'integer'],
            [['quantity', 'price_unit', 'subtotal', 'tax_amount', 'discount_amount', 'exonerate_amount', 'price_total', 'tax_rate_percent'], 'number'],
            [['created_at', 'updated_at', 'exoneration_date'], 'safe'],
            [['code', 'description'], 'string', 'max' => 255],
            [['number_exoneration_doc'], 'string', 'max' => 17],
            [['name_institution_exoneration'], 'string', 'max' => 100],
            [['nature_discount'], 'string', 'max' => 80],        
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::className(), 'targetAttribute' => ['product_id' => 'id']],
            [['proforma_id'], 'exist', 'skipOnError' => true, 'targetClass' => Proforma::className(), 'targetAttribute' => ['proforma_id' => 'id']],
            [['service_id'], 'exist', 'skipOnError' => true, 'targetClass' => Service::className(), 'targetAttribute' => ['service_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
            [['unit_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => UnitType::className(), 'targetAttribute' => ['unit_type_id' => 'id']],
            [['tax_rate_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => TaxRateType::className(), 'targetAttribute' => ['tax_rate_type_id' => 'id']],
            [['tax_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => TaxType::className(), 'targetAttribute' => ['tax_type_id' => 'id']],
            ['nature_discount','checkNatureDiscount', 'skipOnEmpty' => false, 'skipOnError' => false],	            
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'proforma_id' => Yii::t('backend', 'Proforma'),
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
            'nature_discount' => Yii::t('backend', 'Naturaleza Descuento'),
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

    public function checkNatureDiscount($attribute, $params) {
		$discount_apply = false;
		if (!empty($this->discount_amount))
		    $discount_apply = true;

		if ($discount_apply == true && empty($this->nature_discount)) {
			$this->addError($attribute, 'Debe especificar la naturaleza del descuento');
		}
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
    public function getProforma()
    {
        return $this->hasOne(Proforma::className(), ['id' => 'proforma_id']);
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
        return "/item-proforma";
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

    public function getMontoTotalLinea()
    {
        return ($this->subtotal + $this->tax_amount - $this->exonerate_amount);
    }

    /** :::::::::::: END > Abstract Methods and Overrides ::::::::::::*/    
}
