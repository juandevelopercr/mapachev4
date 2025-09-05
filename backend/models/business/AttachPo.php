<?php

namespace backend\models\business;

use common\models\User;
use Yii;
use backend\models\BaseModel;
use yii\helpers\FileHelper;
use yii\helpers\StringHelper;
use common\models\GlobalFunctions;
use yii\helpers\Html;
use yii\web\UploadedFile;

/**
 * This is the model class for table "attach_po".
 *
 * @property int $id
 * @property int|null $payment_order_id
 * @property int|null $user_id
 * @property string|null $document_file
 * @property string|null $observations
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property PaymentOrder $paymentOrder
 * @property User $user

 */
class AttachPo extends BaseModel
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'attach_po';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['payment_order_id', 'user_id'], 'default', 'value' => null],
            ['document_file','required','on' => 'create'],
            ['observations','required'],
            [['payment_order_id', 'user_id'], 'integer'],
            [['observations'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['document_file'], 'string', 'max' => 255],
            [['document_file'], 'file', 'extensions'=> 'jpg, gif, png, svg, jpeg'],
            [['payment_order_id'], 'exist', 'skipOnError' => true, 'targetClass' => PaymentOrder::className(), 'targetAttribute' => ['payment_order_id' => 'id']],
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
            'user_id' => Yii::t('backend', 'Usuario'),
            'document_file' => Yii::t('backend', 'Documento'),
            'observations' => Yii::t('backend', 'Observaciones'),
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
        return "/attach-po";
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
     * @return boolean true if exists stored image
     */
    public function hasImage()
    {
        return (isset($this->document_file) && !empty($this->document_file) && $this->document_file !== '');
    }

    /**
     * fetch stored image file name with complete path
     * @return string
     */
    public function getImageFile()
    {
        if(!file_exists("uploads/attach-po/") || !is_dir("uploads/attach-po/")){
            try{
                FileHelper::createDirectory("uploads/attach-po/", 0777);
            }catch (\Exception $exception){
                Yii::info("Error handling AttachPo folder resources");
            }

        }
        if(isset($this->document_file) && !empty($this->document_file) && $this->document_file !== '')
            return "uploads/attach-po/{$this->document_file}";
        else
            return null;

    }

    /**
     * fetch stored image url
     * @return string
     */
    public function getImageUrl()
    {
        if($this->hasImage()){
            return "uploads/attach-po/{$this->document_file}";
        }else{
            return GlobalFunctions::getNoImageDefaultUrl();
        }

    }

    /**
     * Process upload of image
     * @return mixed the uploaded image instance
     */
    public function uploadImage() {
        // get the uploaded file instance. for multiple file uploads
        // the following data will return an array (you may need to use
        // getInstances method)
        $image = UploadedFile::getInstance($this, 'document_file');

        // if no logo was uploaded abort the upload
        if (empty($image)) {
            return false;
        }

        // store the source file name
        // $this->filename = $image->name;
        $explode= explode('.',$image->name);
        $ext = end($explode);
        $hash_name = GlobalFunctions::generateRandomString(10);
        $this->document_file = "{$hash_name}.{$ext}";

        // the uploaded logo instance
        return $image;
    }

    /**
     * Process deletion of logo
     * @return boolean the status of deletion
     */
    public function deleteImage() {
        $file = $this->getImageFile();

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
            Yii::info("Error deleting image on product: " . $file);
            Yii::info($exception->getMessage());
            return false;
        }

        // if deletion successful, reset your file attributes
        $this->document_file = null;

        return true;
    }

    /**
     * @return string
     */
    public function getPreview()
    {
        if(isset($this->document_file) && !empty($this->document_file))
        {
            $path_url = GlobalFunctions::getFileUrlByNamePath('attach-po',$this->document_file);
        }
        else
        {
            $path_url = '/'.GlobalFunctions::getNoImageDefaultUrl();
        }

        return $path_url;
    }
}
