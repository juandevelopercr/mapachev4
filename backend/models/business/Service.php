<?php

namespace backend\models\business;

use backend\models\nomenclators\Cabys;
use backend\models\nomenclators\ExonerationDocumentType;
use backend\models\nomenclators\TaxRateType;
use backend\models\nomenclators\TaxType;
use backend\models\nomenclators\UnitType;
use Yii;
use backend\models\BaseModel;
use yii\helpers\StringHelper;
use common\models\GlobalFunctions;
use yii\helpers\Html;

/**
 * This is the model class for table "service".
 *
 * @property int $id
 * @property string|null $code
 * @property int|null $cabys_id
 * @property string|null $name
 * @property int|null $unit_type_id
 * @property float|null $price
 * @property float|null $discount_amount
 * @property string|null $nature_discount
 * @property int|null $tax_type_id
 * @property int|null $tax_rate_type_id
 * @property float|null $tax_rate_percent
 * @property float|null $tax_amount
 * @property int|null $exoneration_document_type_id
 * @property string|null $number_exoneration_doc
 * @property string|null $name_institution_exoneration
 * @property string|null $exoneration_date
 * @property float|null $exoneration_purchase_percent
 * @property float|null $exonerated_tax_amount
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property Cabys $cabys
 * @property ExonerationDocumentType $exonerationDocumentType
 * @property TaxRateType $taxRateType
 * @property TaxType $taxType
 * @property UnitType $unitType

 */
class Service extends BaseModel
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'service';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name','code','unit_type_id','price'],'required'],
            [['code'],'unique'],
            [['cabys_id', 'unit_type_id', 'tax_type_id', 'tax_rate_type_id', 'exoneration_document_type_id'], 'integer'],
            [['price', 'discount_amount', 'tax_rate_percent', 'tax_amount', 'exoneration_purchase_percent', 'exonerated_tax_amount','exoneration_date', 'created_at', 'updated_at'], 'safe'],
            [['code', 'name', 'number_exoneration_doc', 'name_institution_exoneration'], 'string', 'max' => 255],
            [['nature_discount'], 'string', 'max' => 80],        
            [['cabys_id'], 'exist', 'skipOnError' => true, 'targetClass' => Cabys::className(), 'targetAttribute' => ['cabys_id' => 'id']],
            [['exoneration_document_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => ExonerationDocumentType::className(), 'targetAttribute' => ['exoneration_document_type_id' => 'id']],
            [['tax_rate_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => TaxRateType::className(), 'targetAttribute' => ['tax_rate_type_id' => 'id']],
            [['tax_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => TaxType::className(), 'targetAttribute' => ['tax_type_id' => 'id']],
            [['unit_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => UnitType::className(), 'targetAttribute' => ['unit_type_id' => 'id']],
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
            'code' => Yii::t('backend', 'Código'),
            'cabys_id' => Yii::t('backend', 'Cabys'),
            'name' => Yii::t('backend', 'Descripción'),
            'unit_type_id' => Yii::t('backend', 'Unidad de medida'),
            'price' => Yii::t('backend', 'Precio'),
            'discount_amount' => Yii::t('backend', 'Monto Descuento'),
            'nature_discount' => Yii::t('backend', 'Naturaleza Descuento'),
            'tax_type_id' => Yii::t('backend', 'Código del impuesto'),
            'tax_rate_type_id' => Yii::t('backend', 'Código de la tarifa del impuesto'),
            'tax_rate_percent' => Yii::t('backend', 'Tarifa Impuesto %'),
            'tax_amount' => Yii::t('backend', 'Monto Impuesto'),
            'exoneration_document_type_id' => Yii::t('backend', 'Tipo documento'),
            'number_exoneration_doc' => Yii::t('backend', 'No. documento'),
            'name_institution_exoneration' => Yii::t('backend', 'Instituto emite'),
            'exoneration_date' => Yii::t('backend', 'Fecha de emisión'),
            'exoneration_purchase_percent' => Yii::t('backend', 'Porcentaje compra'),
            'exonerated_tax_amount' => Yii::t('backend', 'Monto Impuesto'),
            'created_at' => Yii::t('backend', 'Fecha de creación'),
            'updated_at' => Yii::t('backend', 'Fecha de actualización'),
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
    public function getCabys()
    {
        return $this->hasOne(Cabys::className(), ['id' => 'cabys_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getExonerationDocumentType()
    {
        return $this->hasOne(ExonerationDocumentType::className(), ['id' => 'exoneration_document_type_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTaxRateType()
    {
        return $this->hasOne(TaxRateType::className(), ['id' => 'tax_rate_type_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTaxType()
    {
        return $this->hasOne(TaxType::className(), ['id' => 'tax_type_id']);
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
        return "/service";
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
     * @return string
     */
    public function generateCode()
    {
        $max_code = self::find()->max('code');
        $code = is_null($max_code) ? 1: ($max_code + 1);
        return GlobalFunctions::zeroFill($code,6);
    }

    public function getPercentIvaToApply()
    {
        $percent = 0;
        if (!is_null($this->tax_type_id) && !is_null($this->tax_rate_type_id) && !is_null($this->tax_rate_percent)){
            $percent = $this->tax_rate_percent;
        }
        return $percent;
    }    

    public function getDiscount()
    {
        return (!is_null($this->discount_amount) && !empty($this->discount_amount)) ? $this->discount_amount : 0;
    }    
}
