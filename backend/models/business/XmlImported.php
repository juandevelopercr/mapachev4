<?php

namespace backend\models\business;

use backend\models\nomenclators\BranchOffice;
use common\models\User;
use Yii;
use backend\models\BaseModel;
use yii\helpers\FileHelper;
use yii\helpers\StringHelper;
use common\models\GlobalFunctions;
use yii\helpers\Html;
use yii\web\UploadedFile;

/**
 * This is the model class for table "xml_imported".
 *
 * @property int $id
 * @property string|null $currency_code
 * @property float|null $currency_change_value
 * @property string|null $invoice_key
 * @property string|null $invoice_activity_code
 * @property string|null $invoice_consecutive_number
 * @property string|null $invoice_date
 * @property int|null $user_id
 * @property int|null $entry_id
 * @property string|null $xml_file
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property string|null $supplier_identification
 * @property string|null $supplier_identification_type
 * @property string|null $supplier_name
 * @property string|null $supplier_province_code
 * @property string|null $supplier_canton_code
 * @property string|null $supplier_district_code
 * @property string|null $supplier_barrio_code
 * @property string|null $supplier_other_signals
 * @property string|null $supplier_phone_country_code
 * @property string|null $supplier_phone
 * @property string|null $supplier_email
 * @property string|null $invoice_condition_sale_code
 * @property string|null $invoice_credit_time_code
 * @property string|null $invoice_payment_method_code
 * @property int|null $supplier_id
 * @property int|null $branch_office_id
 *
 * @property ItemImported[] $itemImporteds
 * @property BranchOffice $branchOffice
 * @property Entry $entry
 * @property Supplier $supplier
 * @property User $user

 */
class XmlImported extends BaseModel
{

    public $array_xml;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'xml_imported';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['xml_file'],'required','on' => 'create'],
            [['xml_file','branch_office_id'],'required','on' => 'import'],
            [['currency_change_value'], 'number'],
            [['user_id', 'entry_id', 'supplier_id', 'branch_office_id'], 'default', 'value' => null],
            [['user_id', 'entry_id', 'supplier_id', 'branch_office_id'], 'integer'],
            [['created_at', 'updated_at','array_xml'], 'safe'],
            [['xml_file'], 'file', 'extensions'=>'xml'],
            [['supplier_other_signals'], 'string'],
            [['currency_code', 'invoice_key', 'invoice_activity_code', 'invoice_consecutive_number', 'invoice_date', 'xml_file', 'supplier_identification', 'supplier_identification_type', 'supplier_name', 'supplier_province_code', 'supplier_canton_code', 'supplier_district_code', 'supplier_barrio_code', 'supplier_phone_country_code', 'supplier_phone', 'supplier_email', 'invoice_condition_sale_code', 'invoice_credit_time_code', 'invoice_payment_method_code'], 'string', 'max' => 255],
            [['branch_office_id'], 'exist', 'skipOnError' => true, 'targetClass' => BranchOffice::className(), 'targetAttribute' => ['branch_office_id' => 'id']],
            [['entry_id'], 'exist', 'skipOnError' => true, 'targetClass' => Entry::className(), 'targetAttribute' => ['entry_id' => 'id']],
            [['supplier_id'], 'exist', 'skipOnError' => true, 'targetClass' => Supplier::className(), 'targetAttribute' => ['supplier_id' => 'id']],
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
            'currency_code' => Yii::t('backend', 'Código de moneda'),
            'currency_change_value' => Yii::t('backend', 'Tipo de cambio'),
            'invoice_key' => Yii::t('backend', 'Clave'),
            'invoice_activity_code' => Yii::t('backend', 'Código de actividad'),
            'invoice_consecutive_number' => Yii::t('backend', 'No. consecutivo'),
            'invoice_date' => Yii::t('backend', 'Fecha de emisión'),
            'user_id' => Yii::t('backend', 'Usuario'),
            'entry_id' => Yii::t('backend', 'Entrada'),
            'xml_file' => Yii::t('backend', 'Fichero XML'),
            'created_at' => Yii::t('backend', 'Fecha de creación'),
            'updated_at' => Yii::t('backend', 'Fecha de actualización'),
            'supplier_identification' => Yii::t('backend', 'Identificación'),
            'supplier_identification_type' => Yii::t('backend', 'Tipo de identificación'),
            'supplier_name' => Yii::t('backend', 'Nombre del proveedor'),
            'supplier_province_code' => Yii::t('backend', 'Provincia'),
            'supplier_canton_code' => Yii::t('backend', 'Cantón'),
            'supplier_district_code' => Yii::t('backend', 'Distrito'),
            'supplier_barrio_code' => Yii::t('backend', 'Barrio'),
            'supplier_other_signals' => Yii::t('backend', 'Otras señas'),
            'supplier_phone_country_code' => Yii::t('backend', 'Cod. País'),
            'supplier_phone' => Yii::t('backend', 'Teléfono'),
            'supplier_email' => Yii::t('backend', 'Correo electrónico'),
            'invoice_condition_sale_code' => Yii::t('backend', 'Condición de venta'),
            'invoice_credit_time_code' => Yii::t('backend', 'Plazo de crédito'),
            'invoice_payment_method_code' => Yii::t('backend', 'Medio de pago'),
            'supplier_id' => Yii::t('backend', 'Proveedor'),
            'branch_office_id' => Yii::t('backend', 'Sucursal'),
            'array_xml' => Yii::t('backend', 'ArregloXML'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBranchOffice()
    {
        return $this->hasOne(BranchOffice::className(), ['id' => 'branch_office_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSupplier()
    {
        return $this->hasOne(Supplier::className(), ['id' => 'supplier_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItemImporteds()
    {
        return $this->hasMany(ItemImported::className(), ['xml_imported_id' => 'id']);
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
        return "/xml-imported";
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
     * @return boolean true if exists stored xml_file
     */
    public function hasXml()
    {
        return (isset($this->xml_file) && !empty($this->xml_file) && $this->xml_file !== '');
    }

    /**
     * fetch stored xml_file file name with complete path
     * @return string
     */
    public function getXmlFile()
    {
        if(!file_exists("uploads/xml_imported/") || !is_dir("uploads/xml_imported/")){
            try{
                FileHelper::createDirectory("uploads/xml_imported/", 0777);
            }catch (\Exception $exception){
                Yii::info("Error handling XmlImport folder resources");
            }

        }
        if(isset($this->xml_file) && !empty($this->xml_file) && $this->xml_file !== '')
            return "uploads/xml_imported/{$this->xml_file}";
        else
            return null;

    }

    /**
     * fetch stored xml_file url
     * @return string
     */
    public function getXmlUrl()
    {
        if($this->hasXml()){
            return "uploads/xml_imported/{$this->xml_file}";
        }
        else
        {
            return '';
        }
    }

    /**
     * Process upload of xml_file
     * @return mixed the uploaded xml_file instance
     */
    public function uploadXml() {
        // get the uploaded file instance. for multiple file uploads
        // the following data will return an array (you may need to use
        // getInstances method)
        $xml_file = UploadedFile::getInstance($this, 'xml_file');
        $array_xml = GlobalFunctions::convertXmlInArrayPhp($xml_file->tempName);

        if(!$this->validateStructureXML($array_xml))
        {
            return false;
        }

        // if no logo was uploaded abort the upload
        if (empty($xml_file)) {
            return false;
        }

        $this->array_xml = $array_xml;
        // store the source file name
         $this->xml_file = $xml_file->name;

        // the uploaded logo instance
        return $xml_file;
    }

    /**
     * Process deletion of xml
     * @return boolean the status of deletion
     */
    public function deleteXml() {
        $file = $this->getXmlFile();

        // check if file exists on server
        if (empty($file) || !file_exists($file)) {
            return false;
        }

        // check if uploaded file can be deleted on server
        try{
            if (!unlink($file)) {
                return false;
            }
        }catch (\Exception $exception){
            Yii::info("Error deleting xml_file on xml_imported: " . $file);
            Yii::info($exception->getMessage());
            return false;
        }

        // if deletion successful, reset your file attributes
        $this->xml_file = null;

        return true;
    }

    /**
     * @param $array_xml
     * @return bool
     */
    public function validateStructureXML($array_xml)
    {
        if (isset($array_xml['ResumenFactura']['CodigoTipoMoneda']['CodigoMoneda']) &&
            isset($array_xml['ResumenFactura']['CodigoTipoMoneda']['TipoCambio']) &&
            isset($array_xml['Clave']) &&
            isset($array_xml['CodigoActividad']) &&
            isset($array_xml['NumeroConsecutivo']) &&
            isset($array_xml['FechaEmision']) &&
            isset($array_xml['DetalleServicio']['LineaDetalle']) &&
            isset($array_xml['Emisor']) &&
            isset($array_xml['CondicionVenta']) &&
            isset($array_xml['ResumenFactura']['TotalGravado'])
        )
        {
            return true;
        }
        else
        {
            return false;
        }
    }
}
