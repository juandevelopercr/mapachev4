<?php

namespace backend\models\business;

use Yii;
use yii\web\UploadedFile;
use backend\models\settings\Issuer;
use common\models\User;
use backend\models\BaseModel;
use yii\helpers\FileHelper;
use yii\helpers\StringHelper;
use common\models\GlobalFunctions;
use backend\models\nomenclators\UtilsConstants;
use backend\models\nomenclators\ConditionSale;
use yii\helpers\Html;
use Da\QrCode\QrCode;
use yii\helpers\Url;
/**
 * This is the model class for table "documents".
 *
 * @property int $id
 * @property int $receiver_id
 * @property string $key
 * @property string $consecutive
 * @property string $transmitter
 * @property string $transmitter_identification_type
 * @property string $transmitter_identification
 * @property string|null $document_type
 * @property string|null $emission_date
 * @property string|null $reception_date
 * @property string|null $url_xml
 * @property string|null $url_pdf
 * @property string|null $url_ahc
 * @property string|null $currency
 * @property float|null $change_type
 * @property float|null $total_tax
 * @property float|null $total_invoice
 * @property string|null $transmitter_email
 * @property string|null $message_detail
 * @property string|null $condition_sale
 * @property float|null $total_amount_tax_credit
 * @property float|null $total_amount_applicable_expense
 * @property int|null $attempts_making_set
 * @property int|null $attempts_making_get
 * @property int|null $status
 *
 * @property DocumentsState $state
 * @property Issuer $receiver
 */
class Documents extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'documents';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['status'], 'required'],
            [['receiver_id', 'attempts_making_set', 'attempts_making_get', 'status'], 'default', 'value' => null],
            [['receiver_id', 'attempts_making_set', 'attempts_making_get', 'status', 'type'], 'integer'],
            [['emission_date', 'reception_date'], 'safe'],
            [['change_type', 'total_tax', 'total_invoice', 'total_amount_tax_credit', 'total_amount_applicable_expense'], 'number'],
            [['key'], 'string', 'max' => 50],
            [['consecutive'], 'string', 'max' => 20],
            [['transmitter', 'url_xml', 'url_pdf', 'url_ahc'], 'string', 'max' => 255],
            [['transmitter_identification_type', 'document_type', 'condition_sale'], 'string', 'max' => 2],
            [['transmitter_identification'], 'string', 'max' => 12],
            [['currency'], 'string', 'max' => 4],
            [['transmitter_email', 'proveedor', 'xml_emission_date'], 'string', 'max' => 100],
            [['message_detail'], 'string', 'max' => 80],
            [['url_xml'], 'file', 'extensions' => 'xml'],
			[['url_pdf'], 'file', 'extensions' => 'pdf'],            
            [['receiver_id'], 'exist', 'skipOnError' => true, 'targetClass' => Issuer::className(), 'targetAttribute' => ['receiver_id' => 'id']],
            ['message_detail','checkDetalle', 'skipOnEmpty' => false, 'skipOnError' => false],							
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'receiver_id' => 'Receptor',
            'key' => 'No. Factura',
            'consecutive' => 'Consecutivo',
            'transmitter' => 'Emisor',
            'transmitter_identification_type' => 'Tipo de Identificación del Emisor',
            'transmitter_identification' => 'Identificación del Emisor',
            'transmitter_email' => 'E-mail Emisor',
            'document_type' => 'Tipo de documento',
            'emission_date' => 'Fecha de emisión',
            'reception_date' => 'Fecha de recepción',
            'url_xml' => 'Url Xml',
            'url_pdf' => 'Url Pdf',
            'url_ahc' => 'Url Ahc',
            'currency' => 'Moneda',
            'change_type' => 'Tipo de cambio',
            'total_tax' => 'Total de impuesto',
            'total_invoice' => 'Total factura',
            'message_detail' => 'Detalle del Mensaje',
            'condition_sale' => 'Condición de venta',
            'total_amount_tax_credit' => 'Monto Total Impuesto Acreditable',
            'total_amount_applicable_expense' => 'Monto Total De Gasto Aplicable',
            'attempts_making_set' => 'Números de Intento enviando mensaje del receptor a Hacienda',
            'attempts_making_get' => 'Números de Intento obteniendo estado de mensaje del receptor a Hacienda',
            'status' => 'Mensaje de aceptación',
            'type' => 'Tipo',
            'xml_emission_date' => 'Fecha de emisión', 
        ];
    }

	public function checkDetalle($attribute, $params) {
		if ($this->status == UtilsConstants::HACIENDA_STATUS_RECHAZADO_RECEPTOR) {
			$this->addError($attribute, 'El campo Detalle del Mensaje no puede estar vacio, cuando el estado del documento es Rechazado');
		}
	}    

    /**
     * Gets query for [[Receiver]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getReceiver()
    {
        return $this->hasOne(Issuer::className(), ['id' => 'receiver_id']);
    }

	public function getDocumentType()
	{
		$str = '';
		switch ($this->document_type)	
		{
			case '01':$str = 'FE';
					  break;	
			case '02':$str = 'ND';
					  break;	
			case '03':$str = 'NC';
					  break;	
			case '04':$str = 'TE';
					  break;	
			case '05':$str = 'MH';
					  break;	
			case '06':$str = 'MH';
					  break;	
			case '07':$str = 'MH';
					  break;	
		}
		return $str;
	}    

        /**
     * Upload file
     * @param $fileName
     * @param $fileNameType
     * @param $filePath
     * @param $fileField
     * @return mixed the uploaded image instance
     */
    public function uploadFile($fileName,$fileNameType,$filePath,$fileField)
	{
        // get the uploaded file instance. for multiple file uploads
        // the following data will return an array (you may need to use
        // getInstances method)
        $file = UploadedFile::getInstance($this, $fileField);

        // if no file was uploaded abort the upload
        if (empty($file)) {
            return false;
        } else {

			// set fileName by fileNameType
			switch($fileNameType)
			{
				case "original":
					// get original file name
					$name = $file->name;
					break;
				case "casual":
					// generate a unique file name
					$name = Yii::$app->security->generateRandomString();
					break;
				default:
					// get item title like filename
					$name = $fileName;
					break;
			}

			// file extension
			$fileExt  = $file->extension;
			// purge filename
			$fileName = $this->generateFileName($name);
			// set field to filename.extensions
			$this->$fileField = $fileName.".{$fileExt}";
			// update file->name
			$file->name = $fileName.".{$fileExt}";	    		
			
			// save images to imagePath
			$file->saveAs($filePath.$fileName.".{$fileExt}");

			// the uploaded file instance
			return $file;
		}
    }
	
    /**
     * Generate fileName
     * @param $name
     * @return string fileName
     */
	public function generateFileName($name)
    {
		// remove any duplicate whitespace, and ensure all characters are alphanumeric
		$str = preg_replace(array('/\s+/','/[^A-Za-z0-9\-]/'), array('-',''), $name);

        // lowercase and trim
        $str = trim(strtoupper($str));

        return $str;
    }

	/**
	 * Generate URL alias
     * @param $name
	 * @return string alias
	 */
	public function generateAlias($name)
    {
        // remove any '-' from the string they will be used as concatonater
		$str = str_replace('-', ' ', $name);
        $str = str_replace('_', ' ', $str);

		// remove any duplicate whitespace, and ensure all characters are alphanumeric
		$str = preg_replace(array('/\s+/','/[^A-Za-z0-9\-]/'), array('-',''), $str);

        // lowercase and trim
        $str = trim(strtolower($str));

        return $str;
    }

    /**
     * Function for creating directory to save file
     * @param string $path file to create
     */
    protected function createDirectory($path)
    {
        $sizes = array(
            'small',
            'medium',
            'large',
            'extra',
        );

        foreach($sizes as $size)
        {
            if(!file_exists($path.$size))
            {
                mkdir($path.$size, 0755, true);
            }
        }
    }	
	
	/**
     * fetch stored file url
     * @return string
     */
    public function getFileUrlXML()
    {
        // return a default image placeholder if your source avatar is not found
        $file = isset($this->url_xml) && !is_null($this->url_xml) && !empty($this->url_xml) ? $this->url_xml : '';
        return Yii::getAlias('/backend/web/uploads/documents/').$file;
    }	
	
	/**
     * fetch stored file name with complete path
     * @return string
     */
    public function getFilePathXML() {
        return isset($this->url_xml) ? Yii::getAlias("@backend/web/uploads/documents/").$this->url_xml : null;
    }	
	
	/**
     * fetch stored file url
     * @return string
     */
    public function getFileUrlPDF()
    {
        // return a default image placeholder if your source avatar is not found
        $file = isset($this->url_pdf) && !is_null($this->url_pdf) && !empty($this->url_pdf) ? $this->url_pdf : '';
		if (!empty($file))
        	return Yii::getAlias('/backend/web/uploads/documents/').$file;
		else
			return '';
    }	
	
	/**
     * fetch stored file name with complete path
     * @return string
     */
    public function getFilePathPDF() {
        return isset($this->url_pdf) ? Yii::getAlias("@backend/web/uploads/documents/").$this->url_pdf : null;
    }	
	
	
	public function getConsecutivo()
	{
        if (is_null(Yii::$app->user) || is_null(Yii::$app->user->id))
            $user = User::findOne(1);
        else
            $user = User::findOne(Yii::$app->user->id);
        //Sucursal
        $a_number = str_pad($user->branchOffice->code, 3, '0', STR_PAD_LEFT);

        // Caja
        $b_number =  str_pad($user->box->numero, 5, '0', STR_PAD_LEFT);

        $sucursalCaja = $a_number.$b_number;

		$receiver_id = $this->receiver_id;
		$connection = \Yii::$app->db;
        $sql = "SELECT MAX(SUBSTRING(consecutive, 11, 10)) AS consecutive FROM documents WHERE receiver_id = ".$receiver_id." AND SUBSTRING(consecutive, 1, 8) = '" . $sucursalCaja . "'";

		$data = $connection->createCommand($sql);
		$result = $data->queryOne();

        $consecutive = (isset($result['consecutive'])) ? (int) $result['consecutive'] + 1 : 1;

		
		/*
		if ($consecutive['consecutive'] == 1) // Si el consecutive es 1 entonces chequeo el inicio de consecutive del emisor para iniciar con ese
		{
			$emisor = Emisores::find()->where(['id'=>$receiver_id])->one();
			if (!is_null($emisor))
				if (!is_null($emisor->inicio_consecutive_documentos) && !empty($emisor->inicio_consecutive_documentos) && $emisor->inicio_consecutive_documentos > 0)
					$consecutive['consecutive'] = $emisor->inicio_consecutive_documentos;						
		}	
		*/	
		/*
		NumConsecutivoReceptor: Este atributo obligatorio de 20 posiciones que corresponde al número consecutive del receptor y cuya definición abarca de la página 5 a la 7 de la 
		Resolución de Comprobantes Electrónicos DGT-R-48-2016. Y que seguidamente resumimos:

	    De la posición 1 a 3, se identifica el local o establecimiento desde se emitió el mensaje de receptor. El número 001 corresponde a la oficina central y del 002 en adelante a 
		las sucursales.
	    
		De la posición 4 a la 8, identifica la terminal o punto de venta, inicia en 00001.
	    
		De la posición 9 al 10, corresponde al tipo de documento que estamos trabajando. En este caso, al ser un mensaje de receptor, 
		debemos elegir entre 05 (aceptación), 06 (aceptación parcial) o 07 (rechazo). Es importante que haya una consistencia entre esos valores y el dato que coloquemos 
		en el atributo Mensaje. Es decir, si en Mensaje colocamos el valor 1 de aceptación, entonces debemos colocar el valor 05 en este espacio. Caso contrario, se daría un rechazo por parte del Ministerio de Hacienda.
    	
		De la posición 11 a la 20, corresponde al consecutive del receptor iniciando en 1 para cada terminal o sucursal.
		*/
		$respuesta = '05';
		switch ($this->status)
		{
			case 2: $respuesta = '05';  // Aceptado
					break;	
			case 3: $respuesta = '06';  // Aceptado parcial
					break;	
			case 4: $respuesta = '07';  // Rechazo
					break;	
		}
		$consecutive = $sucursalCaja.str_pad($respuesta, 2, '0', STR_PAD_LEFT).str_pad($consecutive, 10, '0', STR_PAD_LEFT);
		return trim($consecutive);
	}	
	
	public function setRespuestaConsecutivo()
	{
		switch ($this->status)
		{
			case 2: $respuesta = '05';  // Aceptado
					$this->consecutive = substr_replace ($this->consecutive, $respuesta, 8, 2);
					break;	
			case 3: $respuesta = '06';  // Aceptado parcial
					$this->consecutive = substr_replace ($this->consecutive, $respuesta, 8, 2);
					break;	
			case 4: $respuesta = '07';  // Rechazo
					$this->consecutive = substr_replace ($this->consecutive, $respuesta, 8, 2);
					break;	
		}
		return $this->consecutive;
	}

    /**
     * @param string $key
     * @param string $status_hacienda
     * @param string $xml_response_hacienda_decode
     */
    public static function verifyResponseStatusHacienda($key, $status_hacienda, $xml_response_hacienda_decode)
    {
        /*
        $archivo = Yii::getAlias("@backend/web/uploads/hacienda.txt");
        $contenido = date('d-m-Y h:i:s').' - '.$key.' - '.$status_hacienda;
        $file = fopen($archivo, "a");
        fputs($file,$contenido);
        fclose($file);
        */
        if($key !== null)
        {
            $document = self::find()->where(['key' => $key])->one();

            if($document !== null)
            {
                if(!file_exists("uploads/xmlh/") || !is_dir("uploads/xmlh/"))
                {
                    try
                    {
                        FileHelper::createDirectory("uploads/xmlh/", 0777);
                    }
                    catch (\Exception $exception)
                    {
                        Yii::info("Error handling xmlh folder resources");
                    }
                }

                if ($status_hacienda == 'rechazado')
                {
                    self::setStatusHacienda($document->id, UtilsConstants::HACIENDA_STATUS_RECHAZADO_HACIENDA); // Rechazada

                    $xml_filename = $document->getTipoDocumento().'-MH-'.$document->key.'-'.$document->consecutive.'.xml';
                    $path = Yii::getAlias('@backend/web/uploads/xmlh/'.$xml_filename);
                    file_put_contents($path, $xml_response_hacienda_decode);
                    $document->url_ahc = $xml_filename;     
                }
                elseif ($status_hacienda == 'aceptado')
                {
                    switch ($document->status)
                    {
                        case 1: $document->status = UtilsConstants::HACIENDA_STATUS_ACEPTADO_HACIENDA; 
                                 break;
                        case 2: $document->status = UtilsConstants::HACIENDA_STATUS_ACEPTADO_HACIENDA; 
                                break;
                        case 3: $document->status = UtilsConstants::HACIENDA_STATUS_ACEPTADO_HACIENDA; 
                                break;							
                        case 7: $document->status = UtilsConstants::HACIENDA_STATUS_ACEPTADO_HACIENDA; 
                                break;
                        case 8: $document->status = UtilsConstants::HACIENDA_STATUS_ACEPTADO_PARCIAL_HACIENDA; 
                                break;
                        case 9: $document->status = UtilsConstants::HACIENDA_STATUS_ACEPTADO_HACIENDA; 
                                break;													
                    }

                    self::setStatusHacienda($document->id, $document->status); // Aceptada

                    $xml_filename = $document->getTipoDocumento().'-MH-'.$document->key.'-'.$document->consecutive.'.xml';
                    $path = Yii::getAlias('@backend/web/uploads/xmlh/'.$xml_filename);
                    file_put_contents($path, $xml_response_hacienda_decode);
                    $document->url_ahc = $xml_filename;                    
                }
                elseif ($status_hacienda == 'recibido')
                {
                    switch ($document->status)
                    {
                        case 7: $document->status = UtilsConstants::HACIENDA_STATUS_RECIBIDO_HACIENDA; 
                                break;
                        case 8: $document->status = UtilsConstants::HACIENDA_STATUS_RECIBIDO_PARCIAL_HACIENDA; 
                                break;
                        case 9: $document->status = UtilsConstants::HACIENDA_STATUS_RECIBIDO_RECHAZADO_HACIENDA; 
                                break;							
                    }
                    self::setStatusHacienda($document->id, $document->status); // Recibida
                }

                $document->save(false);
            }
        }
    }    

    /**
     * @param $invoice_id
     * @param $new_status
     */
    public static function setStatusHacienda($document_id, $new_status)
    {
        $document = self::findOne($document_id);
        $document->status = $new_status;
        $document->save(false);
    }    

    public function getTipoDocumento()
	{
		$str = '';
		switch ($this->document_type)	
		{
			case 'FE':$str = 'FE';
					  break;	 
			case 'ND':$str = 'ND';
					  break;	 
			case 'NC':$str = 'NC';
					  break;	 
			case 'TE':$str = 'TE';
					  break;	
			case 'MR':$str = 'MR';
					  break;
			case 'MR':$str = 'MR';
					  break;
			case 'MR':$str = 'MR';
					  break;
			case 'FEC':$str = 'FEC';
					  break;
			case 'FEE':$str = 'FEE';							  								   
					  break;					  
		}
		return $str;
	}

    public static function getSelectMap()
    {
        $array_map = [];
    
        // Use 'distinct' correctly and select the desired fields
        $query = self::find()->select(['id', 'transmitter'])->distinct('transmitter');
    
        $models = $query->all();
    
        if (count($models) > 0) {
            foreach ($models as $model) {
                $temp_name = $model->transmitter;
                $array_map[$temp_name] = $temp_name;
            }
        }
        return $array_map;
    }

}