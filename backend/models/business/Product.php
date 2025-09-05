<?php

namespace backend\models\business;

use backend\models\nomenclators\BranchOffice;
use backend\models\nomenclators\Cabys;
use backend\models\nomenclators\Category;
use backend\models\nomenclators\ExonerationDocumentType;
use backend\models\nomenclators\Family;
use backend\models\nomenclators\InventoryType;
use backend\models\nomenclators\TaxRateType;
use backend\models\nomenclators\TaxType;
use backend\models\nomenclators\UnitType;
use backend\models\nomenclators\UtilsConstants;
use Yii;
use backend\models\BaseModel;
use yii\helpers\FileHelper;
use yii\helpers\StringHelper;
use common\models\GlobalFunctions;
use yii\helpers\Html;
use yii\web\UploadedFile;

/**
 * This is the model class for table "product".
 *
 * @property int $id
 * @property string|null $code
 * @property string|null $image
 * @property string|null $description
 * @property string|null $entry_date
 * @property string|null $bar_code
 * @property int|null $cabys_id
 * @property int|null $family_id
 * @property int|null $category_id
 * @property int|null $unit_type_id
 * @property int|null $inventory_type_id
 * @property string|null $location
 * @property string|null $branch
 * @property float|null $initial_existence
 * @property int|null $min_quantity
 * @property int|null $max_quantity
 * @property int|null $package_quantity
 * @property float|null $price
 * @property float|null $percent1
 * @property float|null $price1
 * @property float|null $percent2
 * @property float|null $price2
 * @property float|null $percent3
 * @property float|null $price3
 * @property float|null $percent4
 * @property float|null $price4
 * @property float|null $percent_detail
 * @property float|null $price_detail
 * @property float|null $price_custom
 * @property float|null $discount_amount
 * @property string|null $nature_discount
 * @property int|null $tax_type_id
 * @property int|null $tax_rate_type_id
 * @property float|null $tax_rate_percent
 * @property int|null $exoneration_document_type_id
 * @property string|null $number_exoneration_doc
 * @property string|null $name_institution_exoneration
 * @property string|null $exoneration_date
 * @property float|null $exoneration_purchase_percent
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property string|null $supplier_code
 * @property int|null $quantity_by_box
 *
 * @property Adjustment[] $adjustments
 * @property ItemEntry[] $itemEntries
 * @property ItemPaymentOrder[] $itemPaymentOrders
 * @property ItemProforma[] $itemProformas
 * @property PhysicalLocation[] $physicalLocations
 * @property Cabys $cabys
 * @property Category $category
 * @property ExonerationDocumentType $exonerationDocumentType
 * @property Family $family
 * @property InventoryType $inventoryType
 * @property TaxRateType $taxRateType
 * @property TaxType $taxType
 * @property UnitType $unitType
 * @property ProductHasBranchOffice[] $productHasBranchOffices
 * @property BranchOffice[] $branchOffices
 * @property ProductHasSupplier[] $productHasSuppliers
 * @property Supplier[] $suppliers

 */
class Product extends BaseModel
{
    const CHANGE_QUANTITY_PLUS = 1;
    const CHANGE_QUANTITY_MINUS = 2;
    const CHANGE_QUANTITY_SET = 3;

    public $suppliers = [];
    public $total_quantity;

    //virual fields calculator
    public $calc_percent1;
    public $calc_price1;
    public $calc_utility1;
    public $calc_percent2;
    public $calc_price2;
    public $calc_utility2;
    public $utility1;
    public $utility2;
    public $utility3;
    public $utility4;
    public $utility5;
    public $price_bulto;
    public $price_bulto_with_iva;
    public $pricebulto1;
    public $price_bulto1_with_iva;
    public $pricebulto2;
    public $price_bulto2_with_iva;
    public $pricebulto3;
    public $price_bulto3_with_iva;
    public $pricebulto4;
    public $price_bulto4_with_iva;
    public $pricebulto5;
    public $price_bulto5_with_iva;

    public $price1_with_iva;
    public $price2_with_iva;
    public $price3_with_iva;
    public $price4_with_iva;
    public $price5_with_iva;

    const CODE_DEVOLUTIONS = 'DEV_PEND_000';
    const NAME_DEVOLUTIONS = 'DEVOLUCIONES_PENDIENTES';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'product';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['description', 'code', 'price', 'cabys_id', 'family_id', 'category_id', 'unit_type_id', 'inventory_type_id', 'min_quantity', 'entry_date', 'bar_code', 'supplier_code'], 'required'],
            [['entry_date', 'exoneration_date', 'created_at', 'updated_at', 'total_quantity'], 'safe'],
            [['cabys_id', 'family_id', 'category_id', 'unit_type_id', 'inventory_type_id', 'tax_type_id', 'tax_rate_type_id', 'exoneration_document_type_id', 'min_quantity', 'max_quantity', 'package_quantity', 'quantity_by_box'], 'integer'],
            [['initial_existence', 'min_quantity', 'max_quantity', 'package_quantity', 'price', 'percent1', 'price1', 'percent2', 'price2', 'percent3', 'price3', 'percent4', 'price4', 'percent_detail', 'price_detail', 'price_custom', 'discount_amount', 'tax_rate_percent', 'exoneration_purchase_percent', 'suppliers'], 'safe'],
            [['code', 'image', 'description', 'bar_code', 'location', 'branch', 'number_exoneration_doc', 'name_institution_exoneration', 'supplier_code'], 'string', 'max' => 255],
            [['nature_discount'], 'string', 'max' => 80],
            [['cabys_id'], 'exist', 'skipOnError' => true, 'targetClass' => Cabys::className(), 'targetAttribute' => ['cabys_id' => 'id']],
            [['category_id'], 'exist', 'skipOnError' => true, 'targetClass' => Category::className(), 'targetAttribute' => ['category_id' => 'id']],
            [['exoneration_document_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => ExonerationDocumentType::className(), 'targetAttribute' => ['exoneration_document_type_id' => 'id']],
            [['family_id'], 'exist', 'skipOnError' => true, 'targetClass' => Family::className(), 'targetAttribute' => ['family_id' => 'id']],
            [['inventory_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => InventoryType::className(), 'targetAttribute' => ['inventory_type_id' => 'id']],
            [['tax_rate_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => TaxRateType::className(), 'targetAttribute' => ['tax_rate_type_id' => 'id']],
            [['tax_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => TaxType::className(), 'targetAttribute' => ['tax_type_id' => 'id']],
            [['unit_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => UnitType::className(), 'targetAttribute' => ['unit_type_id' => 'id']],
            ['nature_discount', 'checkNatureDiscount', 'skipOnEmpty' => false, 'skipOnError' => false],
            ['tax_type_id', 'checkImpuesto', 'skipOnEmpty' => false, 'skipOnError' => false],
            ['tax_rate_type_id', 'checkImpuestoTarifaCodigo', 'skipOnEmpty' => false, 'skipOnError' => false],
            ['tax_rate_percent', 'checkImpuestoTarifa', 'skipOnEmpty' => false, 'skipOnError' => false],
            ['exoneration_document_type_id', 'checkDocumentoExoneracion', 'skipOnEmpty' => false, 'skipOnError' => false],
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
            'image' => Yii::t('backend', 'Imagen'),
            'description' => Yii::t('backend', 'Descripción'),
            'entry_date' => Yii::t('backend', 'Fecha de entrada'),
            'bar_code' => Yii::t('backend', 'Código de barras'),
            'cabys_id' => 'Cabys',
            'family_id' => Yii::t('backend', 'Familia'),
            'category_id' => Yii::t('backend', 'Categoría'),
            'unit_type_id' => Yii::t('backend', 'Unidad de medida'),
            'inventory_type_id' => Yii::t('backend', 'Tipo de inventario'),
            'location' => Yii::t('backend', 'Ubicación física'),
            'branch' => Yii::t('backend', 'Marca'),
            'initial_existence' => Yii::t('backend', 'Cantidad actual'),
            'min_quantity' => Yii::t('backend', 'Cantidad mínima'),
            'max_quantity' => Yii::t('backend', 'Cantidad máxima'),
            'package_quantity' => Yii::t('backend', 'Cantidad bulto/paquete'),
            'price' => Yii::t('backend', 'Precio - Costo'),
            'percent1' => Yii::t('backend', 'Porcentaje 1'),
            'price1' => Yii::t('backend', 'Precio 1'),
            'percent2' => Yii::t('backend', 'Porcentaje 2'),
            'price2' => Yii::t('backend', 'Precio 2'),
            'percent3' => Yii::t('backend', 'Porcentaje 3'),
            'price3' => Yii::t('backend', 'Precio 3'),
            'percent4' => Yii::t('backend', 'Porcentaje 4'),
            'price4' => Yii::t('backend', 'Precio 4'),
            'percent_detail' => Yii::t('backend', 'Porcentaje Detalle'),
            'price_detail' => Yii::t('backend', 'Precio Detalle'),
            'price_custom' => Yii::t('backend', 'Precio Personalizado'),
            'discount_amount' => Yii::t('backend', 'Monto Descuento'),
            'nature_discount' => Yii::t('backend', 'Naturaleza Descuento'),
            'tax_type_id' => Yii::t('backend', 'Código del impuesto'),
            'tax_rate_type_id' => Yii::t('backend', 'Código de la tarifa del impuesto'),
            'tax_rate_percent' => Yii::t('backend', 'Tarifa Impuesto %'),
            'exoneration_document_type_id' => Yii::t('backend', 'Tipo documento'),
            'number_exoneration_doc' => Yii::t('backend', 'No. documento'),
            'name_institution_exoneration' => Yii::t('backend', 'Instituto emite'),
            'exoneration_date' => Yii::t('backend', 'Fecha de emisión'),
            'exoneration_purchase_percent' => Yii::t('backend', 'Porcentaje compra'),
            'created_at' => Yii::t('backend', 'Fecha de creación'),
            'updated_at' => Yii::t('backend', 'Fecha de actualización'),
            'suppliers' => Yii::t('backend', 'Proveedores'),
            'total_quantity' => Yii::t('backend', 'Cantidad'),
            'supplier_code' => Yii::t('backend', 'Código de proveedor'),
            'calc_percent1' => Yii::t('backend', 'Porcentaje'),
            'calc_percent2' => Yii::t('backend', 'Porcentaje'),
            'calc_price1' => Yii::t('backend', 'Precio'),
            'calc_price2' => Yii::t('backend', 'Precio'),
            'calc_utility1' => Yii::t('backend', 'Utilidad'),
            'calc_utility2' => Yii::t('backend', 'Utilidad'),
            'utility1' => Yii::t('backend', 'Utilidad'),
            'utility2' => Yii::t('backend', 'Utilidad'),
            'utility3' => Yii::t('backend', 'Utilidad'),
            'utility4' => Yii::t('backend', 'Utilidad'),
            'utility5' => Yii::t('backend', 'Utilidad'),
            'price_bulto'=> Yii::t('backend', 'Precio bulto'),
            'pricebulto1' => Yii::t('backend', 'Precio bulto 1'),
            'pricebulto2' => Yii::t('backend', 'Precio bulto 2'),
            'pricebulto3' => Yii::t('backend', 'Precio bulto 3'),
            'pricebulto4' => Yii::t('backend', 'Precio bulto 4'),
            'pricebulto5' => Yii::t('backend', 'Precio detalle bulto'),
            'quantity_by_box' => Yii::t('backend', 'Cantidad por caja'),
        ];
    }

    public function checkNatureDiscount($attribute, $params)
    {
        $discount_apply = false;
        if (!empty($this->discount_amount))
            $discount_apply = true;

        if ($discount_apply == true && empty($this->nature_discount)) {
            $this->addError($attribute, 'Debe especificar la naturaleza del descuento');
        }
    }

    public function checkImpuesto($attribute, $params)
    {
        $aplicar_impuesto = false;
        if (!empty($this->tax_rate_type_id) || (!empty($this->tax_rate_percent) && $this->tax_rate_percent >= 0))
            $aplicar_impuesto = true;
        else
		if (!empty($this->exoneration_document_type_id))
            $aplicar_impuesto = true;
        if ($aplicar_impuesto == true && empty($this->tax_type_id)) {
            $this->addError($attribute, 'Debe especificar el código del impuesto');
        }
    }

    public function checkImpuestoTarifaCodigo($attribute, $params)
    {
        $aplicar_impuesto = false;
        if (!empty($this->tax_type_id) || (!empty($this->tax_rate_percent) && $this->tax_rate_percent >= 0))
            $aplicar_impuesto = true;
        else
		if (!empty($this->exoneration_document_type_id))
            $aplicar_impuesto = true;
        if ($aplicar_impuesto == true && empty($this->tax_rate_type_id)) {
            $this->addError($attribute, 'Debe especificar el código de la tarifa del impuesto');
        }
    }

    public function checkImpuestoTarifa($attribute, $params)
    {
        if (($this->tax_type_id > 0 || $this->tax_rate_type_id > 0) && (empty($this->tax_rate_percent) || strlen($this->tax_rate_percent) <= 0)) {
            $this->addError($attribute, 'Debe especificar la tarifa del impuesto');
        }
    }

    public function checkDocumentoExoneracion($attribute, $params)
    {
        if (empty($this->exoneration_document_type_id) && (!empty($this->number_exoneration_doc) || !empty($this->name_institution_exoneration))) {
            $this->addError($attribute, 'Debe especificar el tipo de documento de exoneración');
        }

        if (!empty($this->exoneration_document_type_id) && (empty(trim($this->number_exoneration_doc)))) {
            $this->addError('number_exoneration_doc', 'Debe especificar el número de documento de exoneración');
        }

        if (!empty($this->exoneration_document_type_id) && (empty($this->name_institution_exoneration) || empty(trim($this->name_institution_exoneration)))) {
            $this->addError('name_institution_exoneration', 'Debe especificar el nombre de la institución que emitió la exoneración');
        }

        if (!empty($this->exoneration_document_type_id) && ((empty($this->exoneration_date) || empty($this->exoneration_date)))) {
            $this->addError('exoneration_date', 'Debe especificar la fecha de exoneración');
        }

        if (!empty($this->exoneration_document_type_id) && ((empty($this->exoneration_purchase_percent) || empty($this->exoneration_purchase_percent) || $this->exoneration_purchase_percent <= 0))) {
            $this->addError('exoneration_purchase_percent', 'Debe especificar el porciento de compra de exoneración');
        }
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAdjustments()
    {
        return $this->hasMany(Adjustment::className(), ['product_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItemEntries()
    {
        return $this->hasMany(ItemEntry::className(), ['product_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItemPaymentOrders()
    {
        return $this->hasMany(ItemPaymentOrder::className(), ['product_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItemProformas()
    {
        return $this->hasMany(ItemProforma::className(), ['product_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPhysicalLocations()
    {
        return $this->hasMany(PhysicalLocation::className(), ['product_id' => 'id']);
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
    public function getCategory()
    {
        return $this->hasOne(Category::className(), ['id' => 'category_id']);
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
    public function getFamily()
    {
        return $this->hasOne(Family::className(), ['id' => 'family_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getInventoryType()
    {
        return $this->hasOne(InventoryType::className(), ['id' => 'inventory_type_id']);
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

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductHasBranchOffices()
    {
        return $this->hasMany(ProductHasBranchOffice::className(), ['product_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBranchOffices()
    {
        return $this->hasMany(BranchOffice::className(), ['id' => 'branch_office_id'])->viaTable('product_has_branch_office', ['product_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductHasSuppliers()
    {
        return $this->hasMany(ProductHasSupplier::className(), ['product_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSuppliers()
    {
        return $this->hasMany(Supplier::className(), ['id' => 'supplier_id'])->viaTable('product_has_supplier', ['product_id' => 'id']);
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
        return "/product";
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
        $code = is_null($max_code) ? 1 : ($max_code + 1);
        return GlobalFunctions::zeroFill($code, 6);
    }

    /**
     * @return boolean true if exists stored image
     */
    public function hasImage()
    {
        return (isset($this->image) && !empty($this->image) && $this->image !== '');
    }

    /**
     * fetch stored image file name with complete path
     * @return string
     */
    public function getImageFile()
    {
        if (!file_exists("uploads/products/") || !is_dir("uploads/products/")) {
            try {
                FileHelper::createDirectory("uploads/products/", 0777);
            } catch (\Exception $exception) {
                Yii::info("Error handling Faqs folder resources");
            }
        }
        if (isset($this->image) && !empty($this->image) && $this->image !== '')
            return "uploads/products/{$this->image}";
        else
            return null;
    }

    /**
     * fetch stored image url
     * @return string
     */
    public function getImageUrl()
    {
        if ($this->hasImage()) {
            return "uploads/products/{$this->image}";
        } else {
            return GlobalFunctions::getNoImageDefaultUrl();
        }
    }

    /**
     * Process upload of image
     * @return mixed the uploaded image instance
     */
    public function uploadImage()
    {
        // get the uploaded file instance. for multiple file uploads
        // the following data will return an array (you may need to use
        // getInstances method)
        $image = UploadedFile::getInstance($this, 'image');

        // if no logo was uploaded abort the upload
        if (empty($image)) {
            return false;
        }

        // store the source file name
        // $this->filename = $image->name;
        $explode = explode('.', $image->name);
        $ext = end($explode);
        $hash_name = GlobalFunctions::generateRandomString(10);
        $this->image = "{$hash_name}.{$ext}";

        // the uploaded logo instance
        return $image;
    }

    /**
     * Process deletion of logo
     * @return boolean the status of deletion
     */
    public function deleteImage()
    {
        $file = $this->getImageFile();

        // check if file exists on server
        if (empty($file) || !file_exists($file)) {
            return false;
        }

        // check if uploaded file can be deleted on server
        try {
            if (!unlink($file)) {
                return false;
            }
        } catch (\Exception $exception) {
            Yii::info("Error deleting image on product: " . $file);
            Yii::info($exception->getMessage());
            return false;
        }

        // if deletion successful, reset your file attributes
        $this->image = null;

        return true;
    }

    /**
     * @return string
     */
    public function getPreview()
    {
        if (isset($this->image) && !empty($this->image)) {
            $path_url = GlobalFunctions::getFileUrlByNamePath('products', $this->image);
        } else {
            $path_url = '/' . GlobalFunctions::getNoImageDefaultUrl();
        }

        return $path_url;
    }

    public function filterPrices()
    {
        if ($this->price1 == 'NaN') {
            $this->price1 = 0;
        }

        if ($this->percent1 == 'NaN') {
            $this->percent1 = 0;
        }

        if ($this->price2 == 'NaN') {
            $this->price2 = 0;
        }

        if ($this->percent2 == 'NaN') {
            $this->percent2 = 0;
        }

        if ($this->price3 == 'NaN') {
            $this->price3 = 0;
        }

        if ($this->percent3 == 'NaN') {
            $this->percent3 = 0;
        }

        if ($this->price4 == 'NaN') {
            $this->price4 = 0;
        }

        if ($this->percent4 == 'NaN') {
            $this->percent4 = 0;
        }

        if ($this->price_detail == 'NaN') {
            $this->price_detail = 0;
        }

        if ($this->percent_detail == 'NaN') {
            $this->percent_detail = 0;
        }
    }

    /**
     * Returns a mapped array for using on Select widget
     *
     * @param boolean $check_status
     * @return array
     */
    public static function getSelectMap($check_status = false)
    {
        $query = self::find();
        if ($check_status) {
            $query->where(['status' => self::STATUS_ACTIVE]);
        }

        $models = $query->asArray()->all();

        $array_map = [];

        if (count($models) > 0) {
            foreach ($models as $index => $model) {
                $array_map[$model['id']] = $model['bar_code'] . ' - ' . $model['description'];
            }
        }

        return $array_map;
    }

    /**
     * @param $product_id
     * @param $quantity
     * @param $type // 1: sum, 2: minus, 3:set
     */
    public static function updateQuantity($product_id, $quantity, $type)
    {
        $model = Product::findOne($product_id);
        $model->initial_existence = (isset($model->initial_existence)) ? $model->initial_existence : 0;

        if ($model !== null) {
            if ($type === self::CHANGE_QUANTITY_PLUS) {
                $model->initial_existence = $model->initial_existence + $quantity;
            } elseif ($type === self::CHANGE_QUANTITY_MINUS) {
                $model->initial_existence = $model->initial_existence - $quantity;
            } elseif ($type === self::CHANGE_QUANTITY_SET) {
                $model->initial_existence = $quantity;
            }

            $model->save();
        }
    }

    /**
     * @param $new_price
     * @return bool
     */
    public function updatePrices($new_price)
    {
        $this->price = $new_price;

        if (isset($this->percent1) && $this->percent1 !== 0) {
            $this->price1 = (($this->price * $this->percent1 / 100) + $this->price);
        }

        if (isset($this->percent2) && $this->percent2 !== 0) {
            $this->price2 = (($this->price * $this->percent2 / 100) + $this->price);
        }

        if (isset($this->percent3) && $this->percent3 !== 0) {
            $this->price3 = (($this->price * $this->percent3 / 100) + $this->price);
        }

        if (isset($this->percent4) && $this->percent4 !== 0) {
            $this->price4 = (($this->price * $this->percent4 / 100) + $this->price);
        }

        if (isset($this->percent_detail) && $this->percent_detail !== 0) {
            $this->price_detail = (($this->price * $this->percent_detail / 100) + $this->price);
        }

        return $this->save();
    }

    public function validatePrices()
    {
        $price = GlobalFunctions::formatNumber($this->price, 2);
        $price1 = GlobalFunctions::formatNumber($this->price1, 2);
        $price2 = GlobalFunctions::formatNumber($this->price2, 2);
        $price3 = GlobalFunctions::formatNumber($this->price3, 2);
        $price4 = GlobalFunctions::formatNumber($this->price4, 2);
        $price_detail = GlobalFunctions::formatNumber($this->price_detail, 2);
        $price_custom = GlobalFunctions::formatNumber($this->price_custom, 2);

        if ($price1 < $price) {
            $this->addError('price1', Yii::t('backend', 'Precio 1 debe ser mayor a Precio - Costo'));
        }

        if ($price2 < $price) {
            $this->addError('price2', Yii::t('backend', 'Precio 2 debe ser mayor a Precio - Costo'));
        }

        if ($price3 < $price) {
            $this->addError('price3', Yii::t('backend', 'Precio 3 debe ser mayor a Precio - Costo'));
        }

        if ($price4 < $price) {
            $this->addError('price4', Yii::t('backend', 'Precio 4 debe ser mayor a Precio - Costo'));
        }

        if ($price_detail < $price) {
            $this->addError('price_detail', Yii::t('backend', 'Precio Detalle debe ser mayor a Precio - Costo'));
        }

        if ($price_custom < $price) {
            $this->addError('price_custom', Yii::t('backend', 'Precio Personalizado debe ser mayor a Precio - Costo'));
        }
    }

    /**
     * @param $type
     * @return float|null
     */
    public function getPriceByType($type)
    {
        switch ($type) {
            case UtilsConstants::CUSTOMER_ASSIGN_PRICE_DETAIL: {
                    $result_price = (isset($this->price_detail) && !empty($this->price_detail)) ? $this->price_detail : $this->price;
                    break;
                }
            case UtilsConstants::CUSTOMER_ASSIGN_PRICE_CUSTOM: {
                    $result_price = (isset($this->price_custom) && !empty($this->price_custom)) ? $this->price_custom : $this->price;
                    break;
                }
            case UtilsConstants::CUSTOMER_ASSIGN_PRICE_1: {
                    $result_price = (isset($this->price1) && !empty($this->price1)) ? $this->price1 : $this->price;
                    break;
                }
            case UtilsConstants::CUSTOMER_ASSIGN_PRICE_2: {
                    $result_price = (isset($this->price2) && !empty($this->price2)) ? $this->price2 : $this->price;
                    break;
                }
            case UtilsConstants::CUSTOMER_ASSIGN_PRICE_3: {
                    $result_price = (isset($this->price3) && !empty($this->price3)) ? $this->price3 : $this->price;
                    break;
                }
            case UtilsConstants::CUSTOMER_ASSIGN_PRICE_4: {
                    $result_price = (isset($this->price4) && !empty($this->price4)) ? $this->price4 : $this->price;
                    break;
                }
            default: {
                    $result_price = $this->price;
                    break;
                }
        }

        return $result_price;
    }

    /**
     * @param $product_id
     * @param $origin_branch_office_id
     * @param $quantity_to_extract
     * @return bool
     */
    public static function checkStockToExtract($product_id, $origin_branch_office_id, $quantity_to_extract)
    {
        $current_stock = ProductHasBranchOffice::getQuantity($product_id, $origin_branch_office_id);

        return ($current_stock >= $quantity_to_extract) ? true : false;
    }

    /**
     * @param $product_id
     * @param $origin_branch_office_id
     * @param $quantity_to_extract
     * @return bool
     */
    // Obtiene la disponibilidad del producto en una unidad de medida
    public static function getStockByUnitType($product_id, $origin_branch_office_id, $unit_type)
    {
        $product = Product::findOne($product_id);
        $current_stock = ProductHasBranchOffice::getQuantity($product_id, $origin_branch_office_id);
        $unitType = UnitType::findOne($unit_type);

        $stock = $current_stock;

        if($unitType->code == 'CAJ' || $unitType->code == 'CJ')
        {
            if(isset($product->quantity_by_box))
            {
                $stock = $current_stock / $product->quantity_by_box;
            }
        }
        elseif($unitType->code == 'BULT' || $unitType->code == 'PAQ')
        {
            if(isset($product->package_quantity))
            {
                $stock = $current_stock / $product->package_quantity;
            }
        }
        return intval($stock);
    }    

    /**
     * @param $product_id
     * @param $origin_branch_office_id
     * @param $quantity_to_extract
     * @param $invoice_id
     * @param $past_quantity
     */
    public static function extractInventory($product_id, $adjustment_type, $origin_branch_office_id, $quantity_to_extract, $invoice_id, $observations = null, $key = null)
    {
        $total_extract = GlobalFunctions::formatNumber($quantity_to_extract, 2, true);

        $locations = PhysicalLocation::find()
            ->innerJoin('sector_location', 'physical_location.sector_location_id = sector_location.id')
            ->innerJoin('sector', 'sector_location.sector_id = sector.id')
            ->where(['physical_location.product_id' => $product_id])
            ->andWhere(['sector.branch_office_id' => $origin_branch_office_id])
            ->andWhere(['>', 'physical_location.quantity', 0])
            ->orderBy('physical_location.id DESC')
            ->all();

        $total_locations = count($locations);
        $i = 0;

        while ($total_extract > 0 && $i < $total_locations) {
            $past_quantity = ProductHasBranchOffice::getQuantity($product_id, $origin_branch_office_id);

            $temp_location = $locations[$i];
            $temp_quantity = GlobalFunctions::formatNumber($temp_location->quantity, 2, true);

            if ($total_extract <= $temp_quantity) {
                $disp = GlobalFunctions::formatNumber($total_extract, 2, true);
                $total_extract = GlobalFunctions::formatNumber(0, 2, true);
            } else {
                $extract = $total_extract - $temp_quantity;
                $total_extract = GlobalFunctions::formatNumber($extract, 2, true);
                $disp = $temp_quantity;
            }

            //Actualizar cantidad en la ubicacion especifica del sector de una sucursal
            PhysicalLocation::updateQuantity($product_id, $temp_location->sector_location_id, $disp, PhysicalLocation::CHANGE_QUANTITY_MINUS);

            //Actualizar cantidad general de una sucursal
            ProductHasBranchOffice::updateQuantity($product_id, $temp_location->sectorLocation->sector->branch_office_id, $disp, ProductHasBranchOffice::CHANGE_QUANTITY_MINUS);

            //Actualizar cantidad total del producto
            Product::updateQuantity($product_id, $disp, Product::CHANGE_QUANTITY_MINUS);

            Adjustment::extract($product_id, $adjustment_type, $disp, $origin_branch_office_id, $temp_location->sector_location_id, $invoice_id, $past_quantity, $observations, $key);            

            $i++;
        }
    }

    public static function returnToInventory($product_id, $adjustment_type, $origin_branch_office_id, $quantity_to_return, $invoice_id, $hacienda_rejected = false, $observats = null, $key = null)
    {
        $total_return = GlobalFunctions::formatNumber($quantity_to_return, 2, true);

        $locations = PhysicalLocation::find()
            ->innerJoin('sector_location', 'physical_location.sector_location_id = sector_location.id')
            ->innerJoin('sector', 'sector_location.sector_id = sector.id')
            ->where(['physical_location.product_id' => $product_id])
            ->andWhere(['sector.branch_office_id' => $origin_branch_office_id])
            ->orderBy('physical_location.quantity ASC')
            ->all();

        $total_locations = count($locations);
        $i = 0;

        while ($total_return > 0 && $i < $total_locations) {
            $temp_location = $locations[$i];
            //$current_quantity = GlobalFunctions::formatNumber($temp_location->quantity, 2, true);
            //$max_capacity = GlobalFunctions::formatNumber($temp_location->max_capacity, 2, true);
            //$temp_quantity = $max_capacity - $current_quantity;

            //if ($temp_quantity > 0) {
                $past_quantity = ProductHasBranchOffice::getQuantity($product_id, $origin_branch_office_id);

                //if ($total_return <= $temp_quantity) {
                    $disp = GlobalFunctions::formatNumber($total_return, 2, true);
                    $total_return = GlobalFunctions::formatNumber(0, 2, true);
                    /*
                } else {
                    $return_quantity = $total_return - $temp_quantity;
                    $total_return = GlobalFunctions::formatNumber($return_quantity, 2, true);
                    $disp = $temp_quantity;
                }
                */

                //Actualizar cantidad en la ubicacion especifica del sector de una sucursal
                PhysicalLocation::updateQuantity($product_id, $temp_location->sector_location_id, $disp, PhysicalLocation::CHANGE_QUANTITY_PLUS);

                //Actualizar cantidad general de una sucursal
                ProductHasBranchOffice::updateQuantity($product_id, $temp_location->sectorLocation->sector->branch_office_id, $disp, ProductHasBranchOffice::CHANGE_QUANTITY_PLUS);

                //Actualizar cantidad total del producto
                Product::updateQuantity($product_id, $disp, Product::CHANGE_QUANTITY_PLUS);

                $new_quantity = $past_quantity + $disp;

                Adjustment::add(
                    $product_id,
                    $adjustment_type,
                    $disp,
                    $new_quantity,
                    $past_quantity,
                    $origin_branch_office_id,
                    $temp_location->sector_location_id,
                    $invoice_id,
                    null,
                    null,
                    $observats, 
                    $key,
                );
            //}
            $i++;
        }

        // Esta parate era para llevar un contro de la capacidad del almacen pero Henry me dijo que lo quitara
        /*
        //Si quedaron elementos pendientes por capacidades llenas
        if ($total_return > 0) {
            $past_quantity = ProductHasBranchOffice::getQuantity($product_id, $origin_branch_office_id);

            //VERIFICAR SI EXISTE EL SECTOR PARA DEVOLUCIONES
            $sector_exist = Sector::find()->where(['branch_office_id' => $origin_branch_office_id, 'code' => Product::CODE_DEVOLUTIONS])->one();
            if ($sector_exist !== null) {
                $sector = $sector_exist;
            } else {
                $sector = new Sector(['branch_office_id' => $origin_branch_office_id, 'code' => Product::CODE_DEVOLUTIONS, 'name' => Product::NAME_DEVOLUTIONS, 'status' => true]);
                $sector->save();
            }

            //VERIFICAR SI EXISTE EL SECTOR_LOCATION PARA DEVOLUCIONES
            $sector_location_exist = SectorLocation::find()->where(['sector_id' => $sector->id, 'code' => Product::CODE_DEVOLUTIONS])->one();
            if ($sector_location_exist !== null) {
                $sector_location = $sector_location_exist;
            } else {
                $sector_location = new SectorLocation(['sector_id' => $sector->id, 'code' => Product::CODE_DEVOLUTIONS, 'name' => Product::NAME_DEVOLUTIONS]);
                $sector_location->save();
            }

            //VERIFICAR SI EXISTE EL PHYSICAL_LOCATION PARA DEVOLUCIONES
            $physical_location_exist = PhysicalLocation::find()->where(['sector_location_id' => $sector_location->id, 'product_id' => $product_id])->one();
            if ($physical_location_exist !== null) {
                $physical_location = $physical_location_exist;
            } else {
                $physical_location = new PhysicalLocation(['sector_location_id' => $sector_location->id, 'product_id' => $product_id, 'quantity' => 0, 'max_capacity' => 0]);
                $physical_location->save();
            }

            //Actualizar cantidad en la ubicacion especifica del sector de una sucursal
            PhysicalLocation::updateQuantity($product_id, $sector_location->id, $total_return, PhysicalLocation::CHANGE_QUANTITY_PLUS);

            //Actualizar cantidad general de una sucursal
            ProductHasBranchOffice::updateQuantity($product_id, $sector->branch_office_id, $total_return, ProductHasBranchOffice::CHANGE_QUANTITY_PLUS);

            //Actualizar cantidad total del producto
            Product::updateQuantity($product_id, $total_return, Product::CHANGE_QUANTITY_PLUS);

            $new_quantity = $past_quantity + $total_return;

            Adjustment::add(
                $product_id,
                UtilsConstants::ADJUSTMENT_TYPE_ADJUSTMENT,
                $total_return,
                $new_quantity,
                $past_quantity,
                $origin_branch_office_id,
                $sector_location->id,
                $invoice_id,
                null,
                null,
                $observats
            );
        }
        */
    }

    public function getPercentIvaToApply()
    {
        $percent = 0;
        if (!is_null($this->tax_type_id) && !is_null($this->tax_rate_type_id) && !is_null($this->tax_rate_percent)) {
            $percent = $this->tax_rate_percent;
        }
        return $percent;
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

    public function getDiscount()
    {
        return (!is_null($this->discount_amount) && !empty($this->discount_amount)) ? $this->discount_amount : 0;
    }

    public function getExistence()
    {
        $data = PhysicalLocation::find()->where(['product_id' => $this->id])->sum('quantity');
        return $data;
    }

    public function getPriceByTypeAndUnitType($type_price = 0, $unit_type = NULL)
    {
        $value_return = $this->price;
        $type = (int) $type_price;

        if ($type !== 0) {
            if ($type === UtilsConstants::CUSTOMER_ASSIGN_PRICE_1) {
                $value_return = (isset($this->price1) && !empty($this->price1)) ? $this->price1 : $this->price;
            } elseif ($type === UtilsConstants::CUSTOMER_ASSIGN_PRICE_2) {
                $value_return = (isset($this->price2) && !empty($this->price2)) ? $this->price2 : $this->price;
            } elseif ($type === UtilsConstants::CUSTOMER_ASSIGN_PRICE_3) {
                $value_return = (isset($this->price3) && !empty($this->price3)) ? $this->price3 : $this->price;
            } elseif ($type === UtilsConstants::CUSTOMER_ASSIGN_PRICE_4) {
                $value_return = (isset($this->price4) && !empty($this->price4)) ? $this->price4 : $this->price;
            } elseif ($type === UtilsConstants::CUSTOMER_ASSIGN_PRICE_CUSTOM) {
                $value_return = (isset($this->price_custom) && !empty($this->price_custom)) ? $this->price_custom : $this->price;
            } elseif ($type === UtilsConstants::CUSTOMER_ASSIGN_PRICE_DETAIL) {
                $value_return = (isset($this->price_detail) && !empty($this->price_detail)) ? $this->price_detail : $this->price;
            }
        }

        if (!is_null($unit_type) && !empty($unit_type)) {
            $unit_type = (int)$unit_type;
            if ($this->unit_type_id != $unit_type) {
                // Si la unidad de medida del producto es diferente a la unidad de medida seleccionado hay que hacer conversión
                $itemUnitType = UnitType::find()->where(['id' => $unit_type])->one();
                if ($this->unitType->code == 'UN' || $this->unitType->code == 'UND' || $this->unitType->code == 'Unid') {
                    $unit_type_code = $itemUnitType->code;

                    if ($unit_type_code == 'CAJ' || $unit_type_code == 'CJ') {
                        if (isset($this->quantity_by_box)) {
                            $value_return *= $this->quantity_by_box;
                        }
                    } elseif ($unit_type_code == 'BULT' || $unit_type_code == 'PAQ') {
                        if (isset($this->package_quantity)) {
                            $value_return *= $this->package_quantity;
                        }
                    }
                } else
                if ($this->unitType->code == 'PAQ' || $this->unitType->code == 'BULT' || $this->unitType->code == 'CJ' || $this->unitType->code == 'CAJ') {
                    $unit_type_code = $this->unitType->code;

                    if ($unit_type_code == 'CAJ' || $unit_type_code == 'CJ') {
                        if (isset($this->quantity_by_box) && $this->quantity_by_box > 0) {
                            $value_return = $value_return / $this->quantity_by_box;
                        }
                    } elseif ($unit_type_code == 'BULT' || $unit_type_code == 'PAQ') {
                        if (isset($this->package_quantity) && $this->package_quantity > 0) {
                            $value_return = $value_return / $this->package_quantity;
                        }
                    }
                }
            }
        }

        return $value_return;
    }

    public static function getUnitQuantityByItem($product_id, $quantity, $unit_type)
    {
        // Henry me dijo que siempre la unidad de medida del producto es en unidad
        $product = Product::find()->where(['id' => $product_id])->one();
        $unit_type = (int)$unit_type;
        $itemUnitType = UnitType::find()->where(['id' => $unit_type])->one();
        $unit_type_code = $itemUnitType->code;

        if ($unit_type_code == 'CAJ' || $unit_type_code == 'CJ') {
            if (isset($product->quantity_by_box)) {
                $quantity *= $product->quantity_by_box;
            }
        } elseif ($unit_type_code == 'BULT' || $unit_type_code == 'PAQ') {
            if (isset($product->package_quantity)) {
                $quantity *= $product->package_quantity;
            }
        }
        return $quantity;              
    }
}