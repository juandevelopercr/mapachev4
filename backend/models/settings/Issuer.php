<?php

namespace backend\models\settings;

use backend\models\nomenclators\Canton;
use backend\models\nomenclators\Disctrict;
use backend\models\nomenclators\IdentificationType;
use backend\models\nomenclators\Province;
use yii\helpers\FileHelper;
use yii\helpers\Url;
use Yii;
use backend\models\BaseModel;
use yii\helpers\StringHelper;
use common\models\GlobalFunctions;
use yii\helpers\Html;
use yii\web\UploadedFile;

/**
 * This is the model class for table "issuer".
 *
 * @property int $id
 * @property string|null $code
 * @property int|null $identification_type_id
 * @property string|null $identification
 * @property string|null $code_economic_activity
 * @property string|null $name
 * @property string|null $address
 * @property string|null $country_code_phone
 * @property string|null $phone
 * @property string|null $country_code_fax
 * @property string|null $fax
 * @property string|null $name_brach_office
 * @property string|null $number_brach_office
 * @property string|null $number_box
 * @property int|null $province_id
 * @property int|null $canton_id
 * @property int|null $disctrict_id
 * @property string|null $other_signs
 * @property string|null $email
 * @property float|null $change_type_dollar
 * @property string|null $certificate_pin
 * @property string|null $api_user_hacienda
 * @property string|null $api_password
 * @property bool|null $enable_prod_enviroment
 * @property string|null $logo_file
 * @property string|null $certificate_digital_file
 * @property string|null $signature_digital_file
 * @property string|null $footer_one_receipt
 * @property string|null $footer_two_receipt
 * @property string|null $digital_proforma_footer
 * @property string|null $digital_invoice_footer
 * @property string|null $electronic_proforma_footer
 * @property string|null $electronic_invoice_footer
 * @property string|null $account_status_footer
 * @property string|null $invoice_header
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property Canton $canton
 * @property Disctrict $disctrict
 * @property IdentificationType $identificationType
 * @property Province $province

 */
class Issuer extends BaseModel
{
    public $file_main_logo;
    public $file_signature_digital;
	public $repass_smtp;    

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'issuer';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['host_smpt', 'user_smtp', 'pass_smtp', 'email_notificacion_smtp', 'ftp_host', 'ftp_user', 'ftp_password'], 'required'],
            [['identification_type_id', 'province_id', 'canton_id', 'disctrict_id', 'puerto_smpt', 
              'init_consecutive_invoice', 'init_consecutive_tiquete', 'init_consecutive_credit_note'], 'integer'],
            [['change_type_dollar'], 'number'],
            [['country_code_fax','country_code_phone'],'string', 'max' => 3,'tooLong' => '{attribute} debería contener como máximo 3 dígitos.'],
            [['fax','phone'],'string', 'max' => 20, 'tooLong' => '{attribute} debería contener como máximo 20 dígitos.'],
            [['enable_prod_enviroment'], 'boolean'],
            [['host_smpt', 'user_smtp', 'pass_smtp', 'email_notificacion_smtp', 'smtp_encryptation', 'ftp_host', 'ftp_user', 'ftp_password'], 'string', 'max' => 100],

            ['email_notificacion_smtp','email'],
            ['repass_smtp', 'compare', 'compareAttribute' => 'pass_smtp'],
            [['digital_proforma_footer', 'digital_invoice_footer', 'electronic_proforma_footer', 'electronic_invoice_footer', 'account_status_footer', 'invoice_header'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['code', 'identification', 'code_economic_activity', 'name', 'address', 'name_brach_office', 'number_brach_office', 'number_box', 'other_signs', 'email', 'certificate_pin', 'api_user_hacienda', 'api_password', 'footer_one_receipt', 'footer_two_receipt'], 'string', 'max' => 255],
            [['canton_id'], 'exist', 'skipOnError' => true, 'targetClass' => Canton::className(), 'targetAttribute' => ['canton_id' => 'id']],
            [['disctrict_id'], 'exist', 'skipOnError' => true, 'targetClass' => Disctrict::className(), 'targetAttribute' => ['disctrict_id' => 'id']],
            [['identification_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => IdentificationType::className(), 'targetAttribute' => ['identification_type_id' => 'id']],
            [['province_id'], 'exist', 'skipOnError' => true, 'targetClass' => Province::className(), 'targetAttribute' => ['province_id' => 'id']],
            [['logo_file'], 'file', 'extensions' => implode(',', GlobalFunctions::getImageFormats())],
            [['certificate_digital_file',], 'file', 'extensions' => 'p12'],
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
            'identification_type_id' => Yii::t('backend', 'Tipo de identificación'),
            'identification' => Yii::t('backend', 'Identificación'),
            'code_economic_activity' => Yii::t('backend', 'Código de actividad económica'),
            'name' => Yii::t('backend', 'Nombre'),
            'address' => Yii::t('backend', 'Dirección'),
            'country_code_phone' => Yii::t('backend', 'Cod. País'),
            'phone' => Yii::t('backend', 'Teléfono'),
            'country_code_fax' => Yii::t('backend', 'Cod. País'),
            'fax' => 'Fax',
            'name_brach_office' => Yii::t('backend', 'Sucursal'),
            'number_brach_office' => Yii::t('backend', 'No. sucursal'),
            'number_box' => Yii::t('backend', 'No. caja'),
            'province_id' => Yii::t('backend', 'Provincia'),
            'canton_id' => Yii::t('backend', 'Cantón'),
            'disctrict_id' => Yii::t('backend', 'Distrito'),
            'other_signs' => Yii::t('backend', 'Otras señas'),
            'email' => Yii::t('backend', 'Correo electrónico'),
            'change_type_dollar' => Yii::t('backend', 'Tipo de cambio $'),
            'certificate_pin' => Yii::t('backend', 'PIN del certificado'),
            'api_user_hacienda' => Yii::t('backend', 'Usuario API Hacienda'),
            'api_password' => Yii::t('backend', 'Contraseña API'),
            'enable_prod_enviroment' => Yii::t('backend', 'Ambiente de producción'),
            'logo_file' => Yii::t('backend', 'Logo'),
            'file_main_logo' => Yii::t('backend', 'Logo'),
            'certificate_digital_file' => Yii::t('backend', 'Certificado digital'),
            'file_certificate_digital' => Yii::t('backend', 'Certificado digital'),
            'signature_digital_file' => Yii::t('backend', 'Firma digital'),
            'file_signature_digital' => Yii::t('backend', 'Firma digital'),
            'footer_one_receipt' => Yii::t('backend', 'Pie de página 1 Recibo'),
            'footer_two_receipt' => Yii::t('backend', 'Pie de página 2 Recibo'),
            'digital_proforma_footer' => Yii::t('backend', 'Pie de página proforma'),
            'digital_invoice_footer' => Yii::t('backend', 'Pie de página QR facturas'),
            'electronic_proforma_footer' => Yii::t('backend', 'Pie de página proforma electrónica'),
            'electronic_invoice_footer' => Yii::t('backend', 'Pie de página factura electrónica'),
            'account_status_footer' => Yii::t('backend', 'Pie de página estado de cuenta'),
            'invoice_header' => Yii::t('backend', 'Encabezado de factura'),
            'created_at' => Yii::t('backend', 'Fecha de creación'),
            'updated_at' => Yii::t('backend', 'Fecha de actualización'),
            'host_smpt'=> Yii::t('backend', 'Host SMTP para recepción de documentos'),
            'user_smtp'=> Yii::t('backend', 'Usuario SMTP para recepción de documentos'),
            'pass_smtp'=> Yii::t('backend', 'Clave SMTP para recepción de documentos'),
            'repass_smtp'=> Yii::t('backend', 'Repetir Clave SMTP para recepción de documentos'),
            'email_notificacion_smtp'=> Yii::t('backend', 'Email para confirmación de recepción de documentos'),
            'smtp_encryptation'=> Yii::t('backend', 'SMTP Encriptación'),
            'ftp_host'=> Yii::t('backend', 'FTP Host'),
            'ftp_user'=> Yii::t('backend', 'FTP User'),
            'ftp_password'=> Yii::t('backend', 'FTP Clave'),
            'init_consecutive_invoice' => Yii::t('backend', 'Inicializar Consecutivo de Factura'),
            'init_consecutive_tiquete' => Yii::t('backend', 'Inicializar Consecutivo de Tiquete'),
            'init_consecutive_credit_note' => Yii::t('backend', 'Inicializar Consecutivo de Nota de crédito'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCanton()
    {
        return $this->hasOne(Canton::className(), ['id' => 'canton_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDisctrict()
    {
        return $this->hasOne(Disctrict::className(), ['id' => 'disctrict_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getIdentificationType()
    {
        return $this->hasOne(IdentificationType::className(), ['id' => 'identification_type_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProvince()
    {
        return $this->hasOne(Province::className(), ['id' => 'province_id']);
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
        return "/issuer";
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

    public static function getIdentificator()
    {
        $model = Issuer::find()->select(['id'])->one();
        return $model->id;
    }

    /** :::::::::::: BEGIN > Uploading Images Methods ::::::::::::*/
    /**
     * fetch stored logo file name with complete path
     * @return string
     */
    public function getImageFile($type)
    {
        switch ($type)
        {
            case 1:
                {
                    if(isset($this->logo_file) && !empty($this->logo_file) && $this->logo_file !== '')
                        return 'images/'.$this->logo_file;
                    else
                        return null;
                    break;
                }
            case 2:
                {
                    if(isset($this->signature_digital_file) && !empty($this->signature_digital_file) && $this->signature_digital_file !== '')
                        return 'images/'.$this->signature_digital_file;
                    else
                        return null;
                    break;
                }
        }

    }

    /**
     * fetch stored logo url
     * @param $type // [1 => logo_file, 2 => signature_digital_file, 3 => mini_logo_header, 4 => back_image_login]
     * @return string
     */
    public function getImageUrl($type)
    {
        // return a default logo placeholder if your source avatar is not found
        switch ($type)
        {
            case 1:
                {
                    $logo = isset($this->logo_file) ? $this->logo_file : 'noimage_default.jpg';
                    break;
                }
            case 2:
                {
                    $logo = isset($this->signature_digital_file) ? $this->signature_digital_file : 'noimage_default.jpg';
                    break;
                }
        }

        return 'images/'.$logo;
    }

    /**
     * Process upload of logo
     * @param $file_name_atrributte //name of field to upload [[file_logo_file, file_signature_digital_file, file_mini_signature_digital_file]]
     * @param $type // [1 => logo_file, 2 => signature_digital_file, 3 => mini_signature_digital_file, 4 => back_image_login]
     * @return mixed the uploaded logo instance
     */
    public function uploadImage($file_name_atrributte,$type) {
        // get the uploaded file instance. for multiple file uploads
        // the following data will return an array (you may need to use
        // getInstances method)
        $logo = UploadedFile::getInstance($this, $file_name_atrributte);

        // if no logo was uploaded abort the upload
        if (empty($logo)) {
            return false;
        }

        // store the source file name
        // $this->filename = $logo->name;
        $explode= explode('.',$logo->name);
        $ext = end($explode);
        $language_active = Yii::$app->language;

        // generate a unique file name
        switch ($type)
        {
            case 1:
                {
                    $this->logo_file = "logo_file_$language_active.{$ext}";
                    break;
                }
            case 2:
                {
                    $this->signature_digital_file = "signature_digital_file_$language_active.{$ext}";
                    break;
                }
        }

        // the uploaded logo instance
        return $logo;
    }

    /**
     * Process deletion of logo
     * @param $type // [1 => logo_file, 2 => signature_digital_file, 3 => mini_logo_header,4 => back_image_login]
     * @return boolean the status of deletion
     */
    public function deleteImage($type) {
        $file = $this->getImageFile($type);

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
            Yii::info("Error deleting image on issuer: " . $file);
            Yii::info($exception->getMessage());
            return false;
        }

        // if deletion successful, reset your file attributes
        switch ($type)
        {
            case 1:
                {
                    $this->logo_file = null;
                    break;
                }
            case 2:
                {
                    $this->signature_digital_file = null;
                    break;
                }
        }

        return true;
    }

    /**
     * get path logo of issuer
     * @param integer $issuer_id
     * @param integer $type // [1 => logo_file, 2 => signature_digital_file, 3 => mini_signature_digital_file,4 => back_image_login]
     * @return string $logo_path
     */
    public static function getUrlLogoByIssuerAndType($type,$issuer_id=null)
    {
        $path = Url::to('@web/images/');

        if($issuer_id !== null)
        {
            $model = self::findOne($issuer_id);

            if($model)
            {
                switch ($type)
                {
                    case 1:
                        {
                            if($model->logo_file === null || $model->logo_file === '')
                            {
                                $url = $path.'noimage_default.jpg';
                            }
                            else
                            {
                                $url = $path.''.$model->logo_file;
                            }
                            break;
                        }
                    case 2:
                        {
                            if($model->signature_digital_file === null || $model->signature_digital_file === '')
                            {
                                $url = $path.'noimage_default.jpg';
                            }
                            else
                            {
                                $url = $path.''.$model->signature_digital_file;
                            }
                            break;
                        }
                }

                return $url;
            }

        }

        return $path.'noimage_default.jpg';
    }
    /** :::::::::::: END > Uploading Images Methods ::::::::::::*/

    /**
     * @return boolean true if exists stored image
     */
    public function hasCertificate()
    {
        return (isset($this->certificate_digital_file) && !empty($this->certificate_digital_file) && $this->certificate_digital_file !== '');
    }

    /**
     * fetch stored image file name with complete path
     * @return string
     */
    public function getCertificateFile()
    {
        if(!file_exists("uploads/certificates/") || !is_dir("uploads/certificates/")){
            try{
                FileHelper::createDirectory("uploads/certificates/", 0777);
            }catch (\Exception $exception){
                Yii::info("Error handling Issuer folder resources");
            }

        }
        if(isset($this->certificate_digital_file) && !empty($this->certificate_digital_file) && $this->certificate_digital_file !== '')
            return "uploads/certificates/{$this->certificate_digital_file}";
        else
            return null;

    }

    /**
     * fetch stored certificates url
     * @return string
     */
    public function getCertificateUrl()
    {
        if($this->hasCertificate()){
            return "uploads/certificates/{$this->certificate_digital_file}";
        }else{
            return GlobalFunctions::getNoImageDefaultUrl();
        }

    }

    /**
     * Process upload of image
     * @return mixed the uploaded image instance
     */
    public function uploadCertificate() {
        // get the uploaded file instance. for multiple file uploads
        // the following data will return an array (you may need to use
        // getInstances method)
        $cert = UploadedFile::getInstance($this, 'certificate_digital_file');

        // if no logo was uploaded abort the upload
        if (empty($cert)) {
            return false;
        }

        // store the source file name
        // $this->filename = $cert->name;
        $explode= explode('.',$cert->name);
        $ext = end($explode);
        $hash_name = GlobalFunctions::generateRandomString(10);
        $this->certificate_digital_file = "{$hash_name}.{$ext}";

        // the uploaded logo instance
        return $cert;
    }

    /**
     * Process deletion of certificate
     * @return boolean the status of deletion
     */
    public function deleteCertificate() {
        $file = $this->getCertificateFile();

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
            Yii::info("Error deleting certificate on issuer: " . $file);
            Yii::info($exception->getMessage());
            return false;
        }

        // if deletion successful, reset your file attributes
        $this->certificate_digital_file = null;

        return true;
    }

    public static function getChange_type_dollar()
    {
        $model = self::find()->select(['change_type_dollar'])->one();
        return (isset($model->change_type_dollar) && !empty($model->change_type_dollar))? $model->change_type_dollar : 1;
    }

    public static function setChange_type_dollar($new_value)
    {
        $model = self::find()->select(['change_type_dollar'])->one();
        $model->change_type_dollar = $new_value;
        $model->save();
    }

    /**
     * @param $fieldname
     * @return mixed|string
     */
    public static function getValueByField($fieldname)
    {
        $model = self::find()->one();

        return (isset($model->$fieldname) && !empty($model->$fieldname))? $model->$fieldname : '';
    }

    /**
     * fetch stored file name with complete path
     * @return string
     */
    public function getFilePath() {
        return isset($this->certificate_digital_file) ? Yii::getAlias("@backend/web/uploads/certificates/").$this->certificate_digital_file : null;
    }
}
