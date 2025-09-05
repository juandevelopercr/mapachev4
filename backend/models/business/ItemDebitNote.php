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
 * This is the model class for table "item_debit_note".
 *
 * @property int $id
 * @property int|null $debit_note_id
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
 * @property DebitNote $debitNote
 * @property Service $service
 * @property User $user
 * @property UnitType $unitType

 */
class ItemDebitNote extends BaseModel
{
    const UPDATE_TYPE_ADD = 1;
    const UPDATE_TYPE_MINUS = 2;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'item_debit_note';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['code','description','quantity','subtotal','price_unit'],'required'],
            [['description','quantity','subtotal','price_unit'],'required'],
            [['debit_note_id', 'product_id', 'service_id', 'user_id', 'price_type','unit_type_id'], 'default', 'value' => null],
            [['debit_note_id', 'product_id', 'service_id', 'user_id', 'price_type','unit_type_id', 'tax_type_id', 'tax_rate_type_id', 'exoneration_document_type_id'], 'integer'],
            [['quantity', 'price_unit', 'subtotal', 'tax_amount', 'discount_amount', 'exonerate_amount', 'price_total', 'exoneration_purchase_percent', 'tax_rate_percent'], 'number'],
            [['created_at', 'updated_at', 'exoneration_date'], 'safe'],
            [['code', 'description'], 'string', 'max' => 255],
            [['number_exoneration_doc'], 'string', 'max' => 17],
            [['name_institution_exoneration'], 'string', 'max' => 100],            
            [['nature_discount'], 'string', 'max' => 80],      
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::className(), 'targetAttribute' => ['product_id' => 'id']],
            [['debit_note_id'], 'exist', 'skipOnError' => true, 'targetClass' => DebitNote::className(), 'targetAttribute' => ['debit_note_id' => 'id']],
            [['service_id'], 'exist', 'skipOnError' => true, 'targetClass' => Service::className(), 'targetAttribute' => ['service_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
            [['unit_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => UnitType::className(), 'targetAttribute' => ['unit_type_id' => 'id']],

        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'debit_note_id' => Yii::t('backend', 'Nota de débito'),
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
    public function getDebitNote()
    {
        return $this->hasOne(DebitNote::className(), ['id' => 'debit_note_id']);
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
        return "/item-debit_note";
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
        $fields['debit_note_id'] = 'debit_note_id';
        $fields['code'] = 'code';
        $fields['description'] = 'description';

        $fields['product_id'] = 'product_id';
        $fields['product_label'] = function(ItemDebitNote $model){
            return isset($model->product_id)? $model->product->description : '';
        };

        $fields['service_id'] = 'service_id';
        $fields['service_label'] = function(ItemDebitNote $model){
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
        $fields['price_type_label'] = function(ItemDebitNote $model){
            return isset($model->price_type)? UtilsConstants::getCustomerAsssignPriceSelectType($model->price_type) : '';
        };

        $fields['unit_type_id'] = 'unit_type_id';
        $fields['unit_type_label'] = function(ItemDebitNote $model){
            return isset($model->unit_type_id)? $model->unitType->code : '';
        };

        $fields['user_id'] = 'user_id';
        $fields['user_label'] = function(ItemDebitNote $model){
            return isset($model->user_id)? User::getFullNameByUserId($model->user_id) : '';
        };

        $fields['created_at'] = 'created_at';
        $fields['updated_at'] = 'updated_at';

        return $fields;
    }


    /****  FUNCIONES DEL ERP VIEJO PARA UTILIZAR EN EL APIXAML ANTIGUO ****/

    public function getPrecio($moneda = 'COLONES')
    {
        return $this->price_unit;
    }

    public function getMonto($moneda = 'COLONES')
    {
        //return $this->subtotal;
        return $this->price_unit * $this->quantity;
    }

    public function getDescuento($moneda = 'COLONES')
    {
        return $this->discount_amount;;
    }

    public function getSubTotal($moneda = 'COLONES')
    {
        $total = $this->getMonto($moneda) - $this->getDescuento($moneda);
        return $total;
    }

    public function getMontoImpuesto($moneda = 'COLONES')
    {
        $tax = 0;
        if (!is_null($this->tax_type_id) && $this->tax_type_id > 0 && !is_null($this->tax_rate_type_id) && $this->tax_rate_type_id > 0 && $this->tax_rate_percent >= 0)         
            $tax =$this->getSubTotal($moneda) * $this->tax_rate_percent / 100;
        return $tax;
    }

    public function getMontoImpuestoNeto($moneda = 'COLONES')
    {
        return $this->getMontoImpuesto($moneda) - $this->getMontoImpuestoExonerado($moneda);
    }

    // Devuelve el monto de precio * cantidad si el servicio está gravado
    public function getServGravado($moneda = 'COLONES')
    {
        // Obtiene el impuesto si es un servicio
        $gravado = 0;
        if (!is_null($this->service_id))
        {
            if (!is_null($this->exoneration_document_type_id) && $this->exoneration_purchase_percent >= 0 && !is_null($this->tax_rate_percent) && $this->tax_rate_percent > 0)
            {
                //$gravado = (1 - $this->exoneration_purchase_percent / 100) * $this->getMontoImpuesto($moneda);;	
                $gravado = $this->getMonto($moneda) /  $this->tax_rate_percent;
            }
            else
            if (!is_null($this->tax_type_id) && $this->tax_type_id > 0 && !is_null($this->tax_rate_type_id) && $this->tax_rate_type_id > 0 && $this->tax_rate_percent >= 0)         
                $gravado = $this->getMonto($moneda);
        }
        return $gravado;
    }

    // Devuelve el monto de precio * cantidad si la mercancia está gravado
    public function getMercanciaGravada($moneda = 'COLONES')
    {
        // Obtiene el impuesto si es una mercancia
        $gravado = 0;
        if (!is_null($this->product_id))
        {
            if (!is_null($this->exoneration_document_type_id) && $this->exoneration_purchase_percent >= 0 && !is_null($this->tax_rate_percent) && $this->tax_rate_percent > 0)
            {
                //$gravado = (1 - $this->exoneration_purchase_percent / 100) * $this->getMontoImpuesto($moneda);;	
                $gravado = $this->getMonto($moneda) /  $this->tax_rate_percent;
            }
            else
            if (!is_null($this->tax_type_id) && $this->tax_type_id > 0 && !is_null($this->tax_rate_type_id) && $this->tax_rate_type_id > 0 && $this->tax_rate_percent >= 0)         
                $gravado = $this->getMonto($moneda);
        }
        return $gravado;
    }

    public function getImpuestoServGravado($moneda = 'COLONES')
    {
        // Obtiene el impuesto si es un servicio
        if (!is_null($this->service_id))
            return $this->getMontoImpuesto($moneda);
        else
            return 0;
    }

    public function getImpuestoMercanciaGravada($moneda = 'COLONES')
    {
        // Obtiene el impuesto si es una mercancia
        if (!is_null($this->product_id))
            return $this->getMontoImpuesto($moneda);
        else
            return 0;
    }

    public function getServExento($moneda = 'COLONES')
    {
		$exento = 0;
		// Obtiene el monto exento si es un servicio
		if (!is_null($this->service_id))
		{
			if (!is_null($this->tax_type_id) && $this->tax_type_id > 0 && !is_null($this->tax_rate_type_id) && $this->tax_rate_type_id > 0 && $this->tax_rate_percent >= 0)         
				$exento = 0;
			else
				$exento = $this->getMonto($moneda);	
		}
		return $exento;
    }

    public function getMercanciaExenta($moneda = 'COLONES')
    {
        $exento = 0;
        if (!is_null($this->product_id))
        {
			if (!is_null($this->tax_type_id) && $this->tax_type_id > 0 && !is_null($this->tax_rate_type_id) && $this->tax_rate_type_id > 0 && $this->tax_rate_percent >= 0)         
				$exento = 0;
			else
				$exento = $this->getSubTotal($moneda);	
		}
		return $exento;
    }

    public function getMontoServExonerado($moneda = 'COLONES')
    {
        $montoExonerado = 0;

        if (!is_null($this->service_id))
        {
            if (!is_null($this->exoneration_document_type_id) && $this->exoneration_purchase_percent >= 0)	
			{
				$montoExonerado = $this->getSubTotal($moneda) * $this->exoneration_purchase_percent / 100;		
			}
        }
        return $montoExonerado;
    }

    public function getMontoMercExonerado($moneda = 'COLONES')
    {
        $montoExonerado = 0;
        if (!is_null($this->product_id))
        {
            if (!is_null($this->exoneration_document_type_id) && $this->exoneration_purchase_percent >= 0)	
			{
				$montoExonerado = $this->getSubTotal($moneda) * $this->exoneration_purchase_percent / 100;		
			}
        }
        return $montoExonerado;
    }

    public function getMontoImpuestoExonerado($moneda = 'COLONES')
    {
        $montoExonerado = 0;
        if(!is_null($this->product_id))
        {            
                $impuesto = $this->getMontoImpuesto($moneda);
                if (!is_null($this->exoneration_document_type_id) && $this->exoneration_purchase_percent >= 0 && !is_null($this->tax_rate_percent) && $this->tax_rate_percent > 0)
                {
                    $subtotal = $this->getSubTotal($moneda);
                    $grabado = $subtotal / $this->tax_rate_percent;
                    $montoExonerado = $subtotal - $grabado;
                }
        }
        elseif(!is_null($this->service_id))
        {
            $impuesto = $this->getMontoImpuesto($moneda);
            if (!is_null($this->exoneration_document_type_id) && $this->exoneration_purchase_percent >= 0 && !is_null($this->tax_rate_percent) && $this->tax_rate_percent > 0)
            {
                $subtotal = $this->getSubTotal($moneda);
                $grabado = $subtotal / $this->tax_rate_percent;
                $montoExonerado = $subtotal - $grabado;
            }
        }

        return $montoExonerado;
    }

    public function getImpuestoServExonerado($moneda = 'COLONES')
    {
        // Obtiene el impuesto si es un servicio
        if(!is_null($this->service_id))
            return $this->getMontoImpuestoExonerado($moneda);
        else
            return 0;
    }

    public function getImpuestoMercanciaExonerada($moneda = 'COLONES')
    {
        // Obtiene el impuesto si es una mercancia
        if(!is_null($this->product_id))
            return $this->getMontoImpuestoExonerado($moneda);
        else
            return 0;
    }

    public function getMontoTotalLinea($moneda = 'COLONES')
    {
        // Existe dos maneras de obtener el resultado del campo Monto Total Linea:
        // - Cuando no existe exoneración, se obtiene de la sumatoria de los campos “subtotal”, “monto del impuesto
        // - Cuando posee una exoneración, se obtiene de la sumatoria de los campos “Subtotal”, “Impuesto Neto”.
        //$impuesto_exonerado = $this->getMontoImpuestoExonerado($moneda);
        if(!is_null($this->product_id))
        {
            if (!is_null($this->exoneration_document_type_id) && $this->exoneration_purchase_percent >= 0)
                $MontoTotalLinea = $this->getSubTotal($moneda) + $this->getMontoImpuestoNeto($moneda);
            else
                $MontoTotalLinea = $this->getSubTotal($moneda) + $this->getMontoImpuesto($moneda);
        }
        elseif(!is_null($this->service_id))
        {
            if (!is_null($this->exoneration_document_type_id) && $this->exoneration_purchase_percent >= 0)
                $MontoTotalLinea = $this->getSubTotal($moneda) + $this->getMontoImpuestoNeto($moneda);
            else
                $MontoTotalLinea = $this->getSubTotal($moneda) + $this->getMontoImpuesto($moneda);
        }

        return $MontoTotalLinea;
    }

    /**
     * Funcion para comprobar que las unidades de medida de un item y un producto coincidan si son diferentes de Unidad.
     *
     * @param $product_unit_type_id
     * @param $item_unit_type_id
     * @return bool
     */
    public static function errorCheckUnitTypeItem($product_unit_type_id, $item_unit_type_id)
    {
        $product_unit = (int) $product_unit_type_id;
        $item_unit = (int) $item_unit_type_id;
        $id1 = (int) UnitType::getUnitTypeIdByCode('Unid');
        $id2 = (int) UnitType::getUnitTypeIdByCode('UND');

        if($product_unit !== $item_unit)
        {
            //Item es diferente de Unidad y producto es diferente de Unidad
            if($item_unit !== $id1 && $item_unit !== $id2 && $product_unit !== $id1 && $product_unit !== $id2)
            {
                return true;
            }

            //Item es Unidad pero producto es diferente de Unidad
            if($item_unit === $id1 || $item_unit === $id2)
            {
                if($product_unit !== $id1 && $product_unit !== $id2)
                {
                    return true;
                }
            }
        }

        //No hay error si la Unidad del item es igual al producto o Si cuando el producto es Unidad y el Item es diferente de Unidad
        return false;
    }

    public function getSimboloDescriptPercentIvaToApply()
    {
        $simbolo = '';       
        if (!is_null($this->tax_type_id) && !is_null($this->tax_rate_type_id) && !is_null($this->tax_rate_percent)) {
            $iva = (int)$this->tax_rate_percent;
            switch ($iva) {
                case 0:
                    $simbolo = ' - E';
                    break;                
                case 1:
                    $simbolo = ' - *';
                    break;
                case 2:
                    $simbolo = ' - #';
                    break;
                case 13:
                    $simbolo = ' - G';
                    break;
                        
                default:
                    # code...
                    break;
            }
        }        
        return $simbolo;
    }      
}
