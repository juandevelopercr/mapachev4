<?php

namespace backend\controllers;

use Yii;
use backend\models\business\Documents;
use backend\models\business\DocumentsSearch;
use backend\models\business\DocumentsState;
use backend\models\business\AccountsPayable;
use backend\modules\reportes\models\DocumentReportForm;
use backend\models\nomenclators\UtilsConstants;
use backend\models\settings\Issuer;
use kartik\form\ActiveForm;
use yii\helpers\Url;
use yii\web\UploadedFile;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\FileHelper;
use common\components\ApiV43\ApiAccess;
use common\components\ApiV43\ApiConsultaHacienda;
use common\components\ApiV43\ApiEnvioHacienda;
use common\components\ApiV43\ApiFirmadoHacienda;
use common\components\ApiV43\ApiXML;
use Smalot\PdfParser\Document;

/**
 * DocumentsController implements the CRUD actions for Documents model.
 */
class DocumentsController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Documents models.
     * @return mixed
     */
    public function actionIndex()
    {
		//$xml = file_get_contents($file->tempName);             		
		/*
		$documents = Documents::find()->all();
		foreach ($documents as $doc){
 	 		$xmlPath = Yii::getAlias('@backend/web/uploads/documents/' . $doc->url_xml);					
			$DOM = new \DomDocument('1.0', 'UTF-8');
			libxml_use_internal_errors(true);

			if ($DOM->load($xmlPath)) {
				// El XML se cargó correctamente
				// Aquí puedes trabajar con el DOM
				$data = $DOM->getElementsByTagName("FechaEmision");		
				$emission_date = trim($data->item(0)->nodeValue);						
				$doc->emission_date = date('Y-m-d H:i:s', strtotime($emission_date)); ;
				$doc->xml_emission_date = (string)$emission_date; 
				if (!$doc->save())
					die(var_dump("NO guardado"));
			} else {
				// Hubo un error al cargar el XML
				$errors = libxml_get_errors();
				foreach ($errors as $error) {
					// Manejar errores
					echo "Error: " . $error->message;
				}
				libxml_clear_errors();
			}		
		}		
		*/
		//die(var_dump("OSOSO"));

        $searchModel = new DocumentsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Documents model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Documents model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {		
        $model = new Documents();
		$model->LoadDefaultValues();
		$model->status = UtilsConstants::HACIENDA_STATUS_ACEPTADO_RECEPTOR;
        if ($model->load(Yii::$app->request->post()))
		{
			if (!$model->validate())
			{			
				$listErrors = ActiveForm::validate($model);
				$msg = '';
				$type = 'danger';
				$attributos = [];
				foreach ($listErrors as $key => $listmesg)
				{
					$attributos[] = $key;
					foreach ($listmesg as $ms){
						if (!empty($msg))
							$msg.= '<br />';	
						$msg.= '- '.$ms;
					}
				}
				$mensaje = $msg;
				$type = 'danger';
				$titulo = "Error <hr class=\"kv-alert-separator\">";	
				\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON; 
				return \Yii::$app->response->data  = ['mensaje' => $mensaje, 'type'=>$type, 'titulo'=>$titulo, 'regresar'=>0];
			}			
			
			$file = UploadedFile::getInstance($model, 'url_xml');
			// if no file was uploaded abort the upload
			if (empty($file))
			{
				$mensaje = 'No se ha cargado el archivo';
				$type = 'danger';
				$titulo = "Error <hr class=\"kv-alert-separator\">";	
				\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON; 
				return \Yii::$app->response->data  = ['mensaje' => $mensaje, 'type'=>$type, 'titulo'=>$titulo, 'regresar'=>0];
				/*
				Yii::$app->session->setFlash('danger', 'No se ha cargado el archivo');
				return $this->render('create', [
					'model' => $model,
				]);
				*/
			}
			$xml = file_get_contents($file->tempName); 
            
			$DOM = new \DomDocument('1.0','UTF-8');
			libxml_use_internal_errors(true);
			if ( !$DOM->loadXML($xml) ) {
				$errors = libxml_get_errors();
				$mensaje = 'El xml seleccionado no es un comprobante válido';
				$type = 'danger';
				$titulo = "Error <hr class=\"kv-alert-separator\">";				
				\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON; 
				return \Yii::$app->response->data  = ['mensaje' => $mensaje, 'type'=>$type, 'titulo'=>$titulo, 'regresar'=>0];
			}	
			$datos_xml = $this->leerDatosComprobante($DOM);		
			$model->key = $datos_xml['key']; 
			$model->document_type = $datos_xml['document_type']; 
			$model->transmitter = $datos_xml['transmitter']; 
			$model->transmitter_email = $datos_xml['transmitter_email']; 
			$model->transmitter_identification_type = $datos_xml['transmitter_identification_type']; 			
			$model->transmitter_identification = $datos_xml['transmitter_identification']; 				
			$model->emission_date = date('Y-m-d H:i:s', strtotime($datos_xml['emission_date'])); 

			$model->xml_emission_date = $datos_xml['emission_date'];

			$model->reception_date = date('Y-m-d H:i:s');
			$model->currency = $datos_xml['currency']; 
			$model->total_tax = $datos_xml['total_tax']; 				
			$model->total_invoice = $datos_xml['total_invoice'];
			$model->condition_sale = $datos_xml['condition_sale'];	
            $receptor = Issuer::find()->one();		
            $model->receiver_id = $receptor->id;																						
			
			if (empty($datos_xml['mensaje'])) // Si no hay mensajes de error
			{
					$model->consecutive = $model->getConsecutivo();				
					$identificador_archivo = strtoupper($model->getDocumentType());				
					
					// Upload XML
                    if(!file_exists("uploads/documents/") || !is_dir("uploads/documents/")){
                        try{
                            FileHelper::createDirectory("uploads/documents/", 0777);
                        }catch (\Exception $exception){
                            Yii::info("Error handling documents folder resources");
                        }
            
                    }                    
					$filePath     = Yii::getAlias("@backend/web/uploads/documents/");
					$fileNameType = '';
					$fileName     = $identificador_archivo.'-'.$model->key;
					$fileField    = "url_xml";
		
					// Create UploadFile Instance
					$xml = $model->uploadFile($fileName,$fileNameType,$filePath,$fileField);
					if ($xml == false) // No subió el archivo
					{
						$mensaje = 'Ha ocurrido un error, no se ha podido subir el archivo, inténtelo nuevamente, si el error persiste contacte al administrador del sistema';
						$type = 'danger';
						$titulo = "Error <hr class=\"kv-alert-separator\">";				
						\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON; 
						return \Yii::$app->response->data  = ['mensaje' => $mensaje, 'type'=>$type, 'titulo'=>$titulo, 'regresar'=>0];		
					}
					else
					{
						// Upload PDF
						$filePath     = Yii::getAlias("@backend/web/uploads/documents/");
						$fileNameType = '';
						$fileName     = $identificador_archivo.'-'.$model->key;
						$fileField    = "url_pdf";
			
						// Create UploadFile Instance
						$pdf = $model->uploadFile($fileName,$fileNameType,$filePath,$fileField);
						$valido = $model->save();

						if ($valido == false){
							$mensaje = 'Ha ocurrido un error al guardar los datos';
							$type = 'danger';
							$titulo = "Error <hr class=\"kv-alert-separator\">";	
							\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON; 									
							return \Yii::$app->response->data  = ['mensaje' => $mensaje, 'type'=>$type, 'titulo'=>$titulo, 'regresar'=>0];
						}
						else
						{
							$documento_id = $model->id;
                            //$model->setRespuestaConsecutivo();
							if ($model->condition_sale == '02') // Crédito entonces crear una cuenta por pagar
							{
								AccountsPayable::addCuentaPorPagar($model);
							}
                            
                            $mensaje = 'Se ha registrado el documento. La validación del mismo y el envio de hacienda se ejecutará automáticamente en otro proceso del sistema';
                            $type = 'success';
                            $titulo = "información <hr class=\"kv-alert-separator\">";	
                            \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON; 									
                            return \Yii::$app->response->data  = ['mensaje' => $mensaje, 'type'=>$type, 'titulo'=>$titulo, 'regresar'=>1];								
						}
					}
			}
			else
			{
				$mensaje = $datos_xml['mensaje'];
				$type = 'danger';
				$titulo = "Error <hr class=\"kv-alert-separator\">";	
				\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON; 							
				return \Yii::$app->response->data  = ['mensaje' => $datos_xml['mensaje'], 'type'=>$type, 'titulo'=>$titulo, 'regresar'=>0];
			}
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }
	
    /**
     * Updates an existing Documents model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
		$oldestado = $model->status;
		$old_xml = $model->url_xml;
		$old_pdf = $model->url_pdf;		 
        if ($model->load(Yii::$app->request->post()))
		{			
			if (!$model->validate())
			{			
				$listErrors = ActiveForm::validate($model);
				$msg = '';
				$type = 'danger';
				$attributos = [];
				foreach ($listErrors as $key => $listmesg)
				{
					$attributos[] = $key;
					foreach ($listmesg as $ms){
						if (!empty($msg))
							$msg.= '<br />';	
						$msg.= '- '.$ms;
					}
				}
				$mensaje = $msg;
				$type = 'danger';
				$titulo = "Error <hr class=\"kv-alert-separator\">";	
				\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON; 
				return \Yii::$app->response->data  = ['mensaje' => $mensaje, 'type'=>$type, 'titulo'=>$titulo, 'regresar'=>0];
			}			
			
			\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON; 
			$model->url_xml = $old_xml;

            // Upload pdf
            $filePath   = Yii::getAlias("@backend/web/uploads/documents/");
            $fileNameType = '';
			$fileName     = strtoupper($model->getDocumentType()).'-'.$model->key;
            $fileField    = "url_pdf";

            // Create UploadFile Instance
            $pdf  = $model->uploadFile($fileName,$fileNameType,$filePath,$fileField);
			
			if($pdf === false) {
                $model->url_pdf = $old_pdf;	
            }
									
		    if ($model->save()) 
			{				
				if ($oldestado != $model->status)// Si el estado anterior era sin enviar
				{
					//$model->setRespuestaConsecutivo();
					$respuesta = $this->enviarMensajeReceptor($model);
					//CACERES
					$mensaje = 'Se ha actualizado la información del documento';
					$type = 'success';
					$titulo = "Información <hr class=\"kv-alert-separator\">";				
					return \Yii::$app->response->data  = ['mensaje' => $mensaje, 'type'=>$type, 'titulo'=>$titulo, 'documento_id'=>$model->id, 'regresar'=>1];					
					//return $this->redirect(['index']);								
				}
				else
				{				
					$mensaje = 'Se han guardado los datos satisfactoriamente';
					$type = 'success';
					$titulo = "Información <hr class=\"kv-alert-separator\">";				
					return \Yii::$app->response->data  = ['mensaje' => $mensaje, 'type'=>$type, 'titulo'=>$titulo, 'documento_id'=>$model->id, 'regresar'=>1];					
					//return $this->redirect(['index']);								
				}
			}
			else
			{
				$mensaje = 'Ha ocurrido un error, los datos no se han guardado';
				$type = 'danger';
				$titulo = "Error <hr class=\"kv-alert-separator\">";				
				return \Yii::$app->response->data  = ['mensaje' => $mensaje, 'type'=>$type, 'titulo'=>$titulo, 'documento_id'=>$model->id, 'regresar'=>0];				
			}
		}
		else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }	

    /**
     * Deletes an existing Documents model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Documents model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Documents the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Documents::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function leerDatosComprobante($DOM)
	{
		$comprobante = [
			'receptor'=> NULL,
			'mensaje'=> '',
			'key'=> '',
			'document_type'=> '',			
			'transmitter'=> '',
			'transmitter_email'=> '',
			'transmitter_identification_type' => '',
			'transmitter_identification' => '',	
			'condition_sale'=> '',
			'emission_date'=> '',
			'currency'=> '',
			'total_tax'=> 0,
			'total_invoice'=> 0,
		];
		//-------------------------------------------------------------
		//---------- Obtener el Receptor del Comproibante -------------
		//-------------------------------------------------------------
		$data = $DOM->getElementsByTagName("Receptor");
		$receptor = NULL;
		if ($data->length > 0){
			// Identificacion
			$d = $data[0]->getElementsByTagName("Identificacion");
			if ($d->length > 0){
				// Numero
				$t = $d[0]->getElementsByTagName("Numero");
				
				// Obtener el receptor
				$receptor = Issuer::find()->where(['identification'=>trim($t->item(0)->nodeValue)])->one();
				$comprobante['receptor'] = $receptor;
			}
			else
				$comprobante['mensaje'] .= 'No se encuentra la identificación del receptor del comprobante <br />';
		}
		else
			$comprobante['mensaje'] .= 'No se encuentra la información de receptor del comprobante <br />';		
			
		if (is_null($receptor)){
			$comprobante['mensaje'] .= 'El receptor del comprobante no pertenece a esta empresa<br />';			
		}
		
		
		//-------------------------------------------------------------
		//------------ Obtener la key del comprobante- --------------
		//-------------------------------------------------------------
		$data = $DOM->getElementsByTagName("Clave");
		if ($data->length > 0){ // Validar key
			$clave = $data->item(0)->nodeValue;
			$comprobante['key'] = $clave;
			$temp = Documents::find()->where(['key'=>$clave])->one();
			if (!is_null($temp))
			{
				$comprobante['mensaje'] .= 'Ya existe un comprobante con la clave: '.$clave. '<br />';
			}
			else
			{
				if (empty($clave)){
					$comprobante['mensaje'] .= 'La clave del comprobante no es válida. <br />';
				}
				else
				if (strlen($clave) < 50 || strlen($clave) > 50){
					$comprobante['mensaje'] .= 'La clave del comprobante no cumple con la longitud requerida de 50 posiciones. <br />';
				}
			}
		}
		else{
			$comprobante['mensaje'] .= 'No se encuentra la clave del comprobante <br />';			
		}	
		
		//-------------------------------------------------------------
		//------------ Obtener la clave del comprobante- --------------
		//-------------------------------------------------------------
		$data = $DOM->getElementsByTagName("NumeroConsecutivo");
		if (!empty($data)){
			$consecutive = 	$data->item(0)->nodeValue;				
			$tipo = substr($consecutive, 8, 2);
			switch ($tipo)
			{
				case '01':// Factura
						  $comprobante['document_type'] = 'FE';
						  break;	
				case '02': // Nota debito
						  $comprobante['document_type'] = 'ND';
						  break;	
				case '03':// Nota de credito
						  $comprobante['document_type'] = 'NC';
						  break;	
				case '04':
						  $comprobante['document_type'] = 'TE';
						  break;	
				case '08':
						  $comprobante['document_type'] = 'MR';
						  break;
				case '09':
						  $comprobante['document_type'] = 'MR';
						  break;
				default:
						  $comprobante['mensaje'] .= 'El xml seleccionado no es un comprobante válido <br />';
			}
		}			
		else
			$comprobante['mensaje'] .= 'No se encuentra el consecutive del Comprobante <br />';		
			

		//-------------------------------------------------------------
		//------------ Obtener la CondicionVenta del comprobante- --------------
		//-------------------------------------------------------------
		$data = $DOM->getElementsByTagName("CondicionVenta");
		if ($data->length > 0){ // Validar CondicionVenta
			$condition_sale = $data->item(0)->nodeValue;
			$comprobante['condition_sale'] = $condition_sale;			
		}
		else{
			$comprobante['mensaje'] .= 'No se encuentra la Condición de venta del comprobante <br />';			
		}	
			
		//-------------------------------------------------------------
		//------------ Obtener los datos del emisor -------------------
		//-------------------------------------------------------------		
		$data = $DOM->getElementsByTagName("Emisor");
		if ($data->length > 0) 
		{
			// Nombre
			$d = $data[0]->getElementsByTagName("Nombre");
			if ($d->length == 0 || empty($d->item(0)->nodeValue))
				$comprobante['mensaje'] .= 'No se encuentra el Nombre del emisor del comprobante <br />';					
			else
				$comprobante['transmitter'] = trim($d->item(0)->nodeValue);				

			// Email
			$d = $data[0]->getElementsByTagName("CorreoElectronico");			
			if ($d->length == 0 || empty($d->item(0)->nodeValue))
				$comprobante['mensaje'] .= 'No se encuentra el Correo electrónico del emisor del comprobante <br />';		
			else
				$comprobante['transmitter_email'] = trim($d->item(0)->nodeValue);				
				
			// Identificacion
			$d = $data[0]->getElementsByTagName("Identificacion");
			if ($d->length > 0) 
			{
				// Tipo
				$t = $d[0]->getElementsByTagName("Tipo");
				if ($t->length == 0 || empty($t->item(0)->nodeValue)) 
					$comprobante['mensaje'] .= 'No se encuentra el tipo de identificacion del emisor del comprobante <br />';					
				else
					$comprobante['transmitter_identification_type'] = $t->item(0)->nodeValue;
				
				// Numero
				$t = $d[0]->getElementsByTagName("Numero");
				if ($t->length == 0 || empty($t->item(0)->nodeValue)) 
					$comprobante['mensaje'] .= 'No se encuentra el número de identificacion del emisor del comprobante.';
				else
					$comprobante['transmitter_identification'] = $t->item(0)->nodeValue;
			}
			else
			{
				$valido = false;
				$msg = 'No se encuentra la identificación del emisor del comprobante.';
			}				
		}
		else
			$comprobante['mensaje'] .= 'No se encuentra el emisor del Comprobante <br />';		
			
			
		//-------------------------------------------------------------
		//-------- Obtener los datos de la fecha de emisión -----------
		//-------------------------------------------------------------		
		// Validar fecha de emision
		$data = $DOM->getElementsByTagName("FechaEmision");		
		if ($data->length == 0 || empty($data->item(0)->nodeValue))		
			$comprobante['mensaje'] .= 'La Fecha de Emisión del comprobante no es válida <br />';		
		else
			$comprobante['emission_date'] = trim($data->item(0)->nodeValue);						
			
		//-------------------------------------------------------------
		//------------ Obtener los datos de la moneda -----------------
		//-------------------------------------------------------------		
		// Validar la moneda
		$data = $DOM->getElementsByTagName("CodigoMoneda");		
		if ($data->length == 0 || empty($data->item(0)->nodeValue))	
		{		
		    // Si el comprobante no tiene la información de la moneda entonces es CRC, según documento de Hacienda
			$comprobante['currency'] = 'CRC';		
		}
		else
			$comprobante['currency'] = trim($data->item(0)->nodeValue);		
			
		//-------------------------------------------------------------
		//-------- Obtener los datos del total de impuesto ------------
		//-------------------------------------------------------------		
		$data = $DOM->getElementsByTagName("TotalImpuesto");
		if ($data->length > 0) 
			$comprobante['total_tax'] = trim($data->item(0)->nodeValue);			


		//-------------------------------------------------------------
		//------ Obtener los datos del total de comprobante -----------
		//-------------------------------------------------------------		
		$data = $DOM->getElementsByTagName("TotalComprobante");
		if ($data->length > 0) 
			$comprobante['total_invoice'] = trim($data->item(0)->nodeValue);			
		else
			$comprobante['mensaje'] .= 'El total del comprobante no es válido <br />';	
			
		return $comprobante;								
	}	    

	public function actionEnviarDocumentoEmail($id)
	{
		$model = new \backend\models\business\EnviarEmailForm();
		$model->id = $id;
		$msg = '';
		if ($model->load(Yii::$app->request->post()))
		{
			$model->id = Yii::$app->request->post()['EnviarEmailForm']['id'];
			//$ids = explode(',', $model->id);
			$documento = Documents::find()->where(['id'=>$id])->one();
			$model->nombrearchivo = '';
			$nombre_archivo = $documento->getTipoDocumento().'-MH-'.$documento->clave.'-'.$documento->consecutivo.'.xml';

			$model->nombrearchivo .= $nombre_archivo;
			$model->cc = Yii::$app->request->post()['EnviarEmailForm']['cc'];
			$model->cuerpo = Yii::$app->request->post()['EnviarEmailForm']['cuerpo'];

			$respuesta = $this->enviareamil($model, $documento);

			Yii::$app->response->format = 'json';
			if ($respuesta)
			{
				$msg .= 'Se ha enviado el xml de respuesta por correo electrónico';
				$type = 'success';			
			}
			else
			{
				$msg .= 'Ha ocurrido un error. No se ha podido enviar el correo electrónico';
				$type = 'danger';		
			}
			
			return \Yii::$app->response->data  = [			
				'message' => $msg,
				'type'=> $type,	
				'titulo'=>"Informaci&oacute;n <hr class=\"kv-alert-separator\">",									
			];				
		}
		else
		{
			$documento = Documents::find()->where(['id'=>$id])->one();
			$receptor = Issuer::find()->one();

			$model->de = $receptor->email;
			$model->para = $documento->emisor_email;

			$nombre_archivo = $documento->getTipoDocumento().'-MH-'.$documento->clave.'-'.$documento->consecutivo.'.xml';

			$file_no_found = false;
			$path = Yii::getAlias('@backend/web/xmlh/'.$nombre_archivo);

			if (!file_exists($path))	
			{	
			    Yii::$app->response->format = 'json';
				return \Yii::$app->response->data  = ['file_no_found'=>true]; 
			}
			$model->nombrearchivo = $nombre_archivo;
			$model->asunto = 'Envío de Aceptación de Comprobante del Receptor';
			$model->cc = '';
	
			
			return $this->renderAjax('_emailForm', [
				'model' => $model,
				'file_no_found'=>$file_no_found,
			]);
		}
	}	
	
	public function enviareamil($model, $documento)
	{
		$emisor = Issuer::find()->one();
		$user = yii::$app->user->identity;
		$respuesta = false;
		//$user = \common\models\User::find()->where(['id'=>Yii::$app->user->id])->one();
		if (strlen(trim($model->cc)) > 0) {
			$arr_cc = explode(';', $model->cc);
		}
		else
			$arr_cc = array();
		//$arr_cc[] = $user->email; // array('amhwolf.dimarzo@gmail.com');
        $direcciones_ok = true;
		foreach ($arr_cc as $ccs) {
			if (!filter_var($ccs, FILTER_VALIDATE_EMAIL)) {
				$direcciones_ok = false;
				break;
			}
		}
		if ($direcciones_ok == false) {
			$messageType = 'danger';
			$message = "<strong>Error!</strong> El correo no se pudo enviar, revise las direcciones de los destinatarios de copia ";
			return false;
		}
		if ($direcciones_ok == true) {
			if (!filter_var($model->de, FILTER_VALIDATE_EMAIL)) {
				$direcciones_ok = false;
				$messageType = 'danger';
				$message = "<strong>Error!</strong> El correo no se pudo enviar, revise la direccion del remitente ";
				return false;
			}
		}
		if ($direcciones_ok == true) {
			$to = explode(';', $model->para); 

			if (empty($model->from)) {
				$from = $user->email; 
			}

			$archivo = NULL;

			$from = [trim($emisor->email)=>$emisor->name];
			$mensage = Yii::$app->mailer->compose("layouts/html", ['content'=>$model->cuerpo])
				->setTo($to)
				//->setFrom($model->de)
				->setFrom($from)
				->setCc($arr_cc)
				->setSubject($model->asunto)
				->setTextBody($model->cuerpo)
				->setHtmlBody($model->cuerpo);				

			// Adjuntar PDF		
			$nombre_archivo = $documento->getTipoDocumento().'-'.$documento->clave.'.pdf';	
			$url_pdf = Yii::getAlias('@backend/web/uploads/documents/'.$nombre_archivo);					
			if (file_exists($url_pdf))	
			{
				$xml = Yii::getAlias('@backend/web/uploads/documents/'.$nombre_archivo);
				$mensage->attach($url_pdf, ['fileName' => $nombre_archivo, 'contentType' => 'text/plain']);			
			}	
			
			// Adjuntar XML		
			$nombre_archivo = $documento->getTipoDocumento().'-'.$documento->clave.'.xml';	
			$url_xml = Yii::getAlias('@backend/web/uploads/documents/'.$nombre_archivo);					
			if (file_exists($url_xml))	
			{
				$xml = Yii::getAlias('@backend/web/uploads/documents/'.$nombre_archivo);
				$mensage->attach($url_xml, ['fileName' => $nombre_archivo, 'contentType' => 'text/plain']);			
			}						
			
			// Adjuntar XML		
			$nombre_archivo = $documento->getTipoDocumento().'-MH-'.$documento->clave.'-'.$documento->consecutivo.'.xml';	
			$url_xml_hacienda_verificar = Yii::getAlias('@backend/web/xmlh/'.$nombre_archivo);					
			if (file_exists($url_xml_hacienda_verificar))	
			{
				$xml = Yii::getAlias('@backend/web/xmls/'.$nombre_archivo);
				$mensage->attach($url_xml_hacienda_verificar, ['fileName' => $nombre_archivo, 'contentType' => 'text/plain']);			
			}
			
			if ($mensage->send()) 
				$respuesta = true;
			else
				$respuesta = false;
			// fin proceso de generar archivos
		}
		return $respuesta;
	}

    /*
	public function actionEnviarDocumentoHacienda($id)
	{
		\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON; 		
		$documento = Documents::find()->where(['id'=>$id])->one();
		$apiAccess = NULL;
		$emisor = Issuer::find()->one();
		$error = 0;
		$mensaje = "-";	
		$type = 'success';
		$titulo = "Información <hr class=\"kv-alert-separator\">";						
		
		if (is_null($apiAccess))
		{
			// Si todas las validaciones son correctas, proceder al proceso
			// Logearse en la api y obtener el token;
			$apiAccess = new ApiAccess();		
			$datos = $apiAccess->loginHacienda($emisor);	
			$error = $datos['error'];
			$tiempo_token = date('Y-m-d H:i:s');
		}
		$segundos_transcurridos = strtotime('Y-m-d H:i:s') -  strtotime($tiempo_token);
		
		// Consultar el tiempo de expiración del token
		if ($segundos_transcurridos >= $apiAccess->expires_in)
		{
			// Refresacar el token
			$data = $apiAccess->refreshToken($emisor);
			if ($data['error'] == 1)
			{
				exit;
			}
			else
			{
				$tiempo_token = date('Y-m-d H:i:s');	
			}	
		}
		
		// Obtener el xml firmado electrónicamente
		$apiXML = new ApiXML();
		$xml = $apiXML->genXMLMr($documento, $emisor);			
		
		$p12Url = $emisor->getFilePath(); 
		$pinP12 = $emisor->certificate_pin;

		$tipoDocumento = '05'; // Mensaje de Receptor
		$apiFirma = new ApiFirmadoHacienda();
		$xmlFirmado = $apiFirma->firmar($p12Url, $pinP12, $xml, $tipoDocumento);
		
		// Enviar documento a hacienda
		$apiEnvioHacienda = new ApiEnvioHacienda();
		$datos = $apiEnvioHacienda->sendMensaje($xmlFirmado, $apiAccess->token, $documento, $emisor);
		
		// En $datos queda el mensaje de respuesta	
		$respuesta = $datos['response'];
		$code = $respuesta->getHeaders()->get('http-code');
		if ($code == '202' || $code == '201')
		{
			if ($documento->status == DocumentsState::ACEPTADO_RECEPTOR)
				$documento->status = DocumentsState::RECIBIDO_HACIENDA; // Recibido
			else
			if ($documento->status == DocumentsState::ACEPTADO_PARCIAL_RECEPTOR)
				$documento->status = DocumentsState::RECIBIDO_PARCIAL_HACIENDA; // Recibido					
			else
			if ($documento->status == DocumentsState::RECHAZADO_RECEPTOR)
				$documento->status = DocumentsState::RECIBIDO_RECHAZADO_HACIENDA; // Recibido	
				
			$documento->save();
			
			$mensaje = "El documento ha sido recibido por Hacienda";	
			$type = 'success';
			$titulo = "Información <hr class=\"kv-alert-separator\">";						
		}
		else
		if ($code == '400'){
			$error = 1;
			$mensaje = utf8_encode($respuesta->getHeaders()->get('X-Error-Cause'));
			
			if (strpos($mensaje, "ya fue recibido anteriormente") == true)  // Si devuelve verdadero
			{
				if ($documento->status == DocumentsState::ACEPTADO_RECEPTOR)
					$documento->status = DocumentsState::RECIBIDO_HACIENDA; // Recibido
				else
				if ($documento->status == DocumentsState::ACEPTADO_PARCIAL_RECEPTOR)
					$documento->status = DocumentsState::RECIBIDO_PARCIAL_HACIENDA; // Recibido					
				else
				if ($documento->status == DocumentsState::RECHAZADO_RECEPTOR)
					$documento->status = DocumentsState::RECIBIDO_RECHAZADO_HACIENDA; // Recibido	

				$documento->save();

				$mensaje = "El documento ya habia sido recibido por Hacienda";	
				$type = 'warning';
				$titulo = "Advertencia <hr class=\"kv-alert-separator\">";		
			}										
		}
		else
		{
			die(var_dump($respuesta));
			$mensaje = "Ha ocurrido un error, el documento no ha podido ser enviado a Hacienda";	
			$type = 'danger';
			$titulo = "Error <hr class=\"kv-alert-separator\">";		

			$error = 1;
		}				
		$apiAccess->CloseSesion($apiAccess->token, $emisor);
		return \Yii::$app->response->data  =  ['mensaje' => $mensaje, 'type'=>$type, 'titulo'=>$titulo, 'actualizar'=>0];		
	}*/
	
	public function actionEnviarDocumentoHacienda($id)
	{
		\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON; 		
		$documento = Documents::find()->where(['id'=>$id])->one();
		$apiAccess = NULL;
		$emisor = Issuer::find()->one();

		$error = 0;
		$mensaje = "-";	
		$type = 'success';
		$titulo = "Información <hr class=\"kv-alert-separator\">";						
		$tiempo_token ='';
		if (is_null($apiAccess))
		{
			// Si todas las validaciones son correctas, proceder al proceso
			// Logearse en la api y obtener el token;
			$apiAccess = new ApiAccess();		
			$datos = $apiAccess->loginHacienda($emisor);			
			$error = $datos['error'];
			$tiempo_token = date('Y-m-d H:i:s');

			if ($datos['error'] == 1){
				$mensaje = $datos['mensaje'];	
				$type = 'danger';
				$titulo = "Error <hr class=\"kv-alert-separator\">";	
				return \Yii::$app->response->data  =  ['mensaje' => $mensaje, 'type'=>$type, 'titulo'=>$titulo, 'Update'=>0];
			}
		}
		$segundos_transcurridos = strtotime('Y-m-d H:i:s') -  strtotime($tiempo_token);
		
		// Consultar el tiempo de expiración del token
		if ($segundos_transcurridos >= $apiAccess->expires_in)
		{
			// Refresacar el token
			$data = $apiAccess->refreshToken($emisor);
			if ($data['error'] == 1)
			{
				exit;
			}
			else
			{
				$tiempo_token = date('Y-m-d H:i:s');	
			}	
		}		
		// Obtener el xml firmado electrónicamente
		$apiXML = new ApiXML();
		$xml = $apiXML->genXMLMr($documento, $emisor);	
		
		$p12Url = $emisor->getFilePath(); 
		$pinP12 = $emisor->certificate_pin;

		$tipoDocumento = '05'; // Mensaje de Receptor
		$apiFirma = new ApiFirmadoHacienda();

		$xmlFirmado = $apiFirma->firmar($p12Url, $pinP12, $xml, $tipoDocumento);
		
		// Enviar documento a hacienda
		$apiEnvioHacienda = new ApiEnvioHacienda();
		$datos = $apiEnvioHacienda->sendMensaje($xmlFirmado, $apiAccess->token, $documento, $emisor);
		
		// En $datos queda el mensaje de respuesta	
		$respuesta = $datos['response'];		
		$code = $respuesta->getHeaders()->get('http-code');		
		if ($code == '202' || $code == '201')
		{
			if ($documento->status == DocumentsState::ACEPTADO_RECEPTOR)
				$documento->status = DocumentsState::RECIBIDO_HACIENDA; // Recibido
			else
			if ($documento->status == DocumentsState::ACEPTADO_PARCIAL_RECEPTOR)
				$documento->status = DocumentsState::RECIBIDO_PARCIAL_HACIENDA; // Recibido					
			else
			if ($documento->status == DocumentsState::RECHAZADO_RECEPTOR)
				$documento->status = DocumentsState::RECIBIDO_RECHAZADO_HACIENDA; // Recibido	
				
			$documento->save();
			
			$mensaje = "El documento ha sido recibido por Hacienda";	
			$type = 'success';
			$titulo = "Información <hr class=\"kv-alert-separator\">";						
		}
		else
		if ($code == '400'){
			$error = 1;
			$mensaje = utf8_encode($respuesta->getHeaders()->get('X-Error-Cause'));
			
			if (strpos($mensaje, "ya fue recibido anteriormente") == true)  // Si devuelve verdadero
			{
				if ($documento->status == DocumentsState::ACEPTADO_RECEPTOR)
					$documento->status = DocumentsState::RECIBIDO_HACIENDA; // Recibido
				else
				if ($documento->status == DocumentsState::ACEPTADO_PARCIAL_RECEPTOR)
					$documento->status = DocumentsState::RECIBIDO_PARCIAL_HACIENDA; // Recibido					
				else
				if ($documento->status == DocumentsState::RECHAZADO_RECEPTOR)
					$documento->status = DocumentsState::RECIBIDO_RECHAZADO_HACIENDA; // Recibido	

				$documento->save();

				$mensaje = "El documento ya habia sido recibido por Hacienda";	
				$type = 'warning';
				$titulo = "Advertencia <hr class=\"kv-alert-separator\">";		
			}										
		}
		else
		{
			$mensaje = "Ha ocurrido un error, el documento no ha podido ser enviado a Hacienda";	
			$type = 'danger';
			$titulo = "Error <hr class=\"kv-alert-separator\">";		

			$error = 1;
		}				
		$apiAccess->CloseSesion($apiAccess->token, $emisor);
		return \Yii::$app->response->data  =  ['mensaje' => $mensaje, 'type'=>$type, 'titulo'=>$titulo, 'Update'=>0];		
	}

	public function actionGetEstadoDocumentoHacienda($id)
    {
		$document = Documents::find()->where(['id'=>$id])->one();
		$this->getEstadoDocumento($document, $document->receiver);
	}
	
	public function getEstadoDocumento($document, $emisor)
	{
		\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON; 
		$actualizar = 0;

		// Si todas las validaciones son correctas, proceder al proceso
		// Logearse en la api y obtener el token;
		$apiAccess = new ApiAccess();
		$datos = $apiAccess->loginHacienda($emisor);
		$error = $datos['error'];
		if ($error == 0)
		{
			// consultar estado de documento en hacienda
			$apiConsultaHacienda = new ApiConsultaHacienda();
			$tipoDocumento = '05'; // Mensaje de Receptor
			$datos = $apiConsultaHacienda->getEstado($document, $emisor, $apiAccess->token, $tipoDocumento);
			// En $datos queda el mensaje de respuesta
			$apiAccess->CloseSesion($apiAccess->token, $emisor);
			$actualizar = $datos['actualizar'];
			$mensaje = $datos['mensaje'];
			$type = $datos['type'];
			$titulo = $datos['titulo'];
		}
		else
		{
			$mensaje = 'No se ha podido autenticar en la api de hacienda. Inténtelo nuevamente';
			$type = 'danger' ;
			$titulo = "Error <hr class=\"kv-alert-separator\">";
		}
		return \Yii::$app->response->data  =  ['mensaje' => $mensaje, 'type'=>$type, 'titulo'=>$titulo, 'actualizar'=>$actualizar];
	}	

    public function actionReport()
    {
		$model = new DocumentReportForm();
		$model->estado_id = UtilsConstants::HACIENDA_STATUS_ACEPTADO_HACIENDA;		
		$model->tipo = '01';
		$model->moneda = 'CRC';
		$fecha = new \DateTime();
		$fecha->modify('last day of this month');
		$ultimo_dia_mes = $fecha->format('d');		
		$model->fecha = '01'.'-'.date('m').'-'.date('Y').' - '.$ultimo_dia_mes.'-'.date('m').'-'.date('Y');		

		if ($model->load(Yii::$app->request->post()))
		{		
			$model->emisor = Yii::$app->request->post()['DocumentReportForm']['emisor'];
			$model->tipo = Yii::$app->request->post()['DocumentReportForm']['tipo'];
			$model->moneda = Yii::$app->request->post()['DocumentReportForm']['moneda'];			
			$model->estado_id = Yii::$app->request->post()['DocumentReportForm']['estado_id'];						
			$model->fecha = Yii::$app->request->post()['DocumentReportForm']['fecha'];
			
			$query = Documents::find();

			$filter_fechas = explode (" - ", $model->fecha);
			if (count($filter_fechas) == 2)
			{
				$DateStart = date('Y-m-d', strtotime($filter_fechas[0]));
				$DateEnd = date('Y-m-d', strtotime($filter_fechas[1]));
				$query->andFilterWhere(
					['between', 'emission_date', $DateStart.' 00:00:00', $DateEnd.' 23:59:59']);
			}	
			if (!is_null($model->emisor) && !empty($model->emisor))
				$query->andFilterWhere(['like', 'transmitter', $model->emisor]);		

			if (!is_null($model->moneda) && !empty($model->moneda))
				$query->andWhere(['currency'=>$model->moneda]);
				
			if (!is_null($model->estado_id) && !empty($model->estado_id))				
				$query->andWhere(['status'=>$model->estado_id]);
				
			if (!is_null($model->tipo) && !empty($model->tipo))				
				$query->andWhere(['document_type'=>$model->tipo]);				
			
			$documentos = $query->orderBy(['reception_date'=> SORT_DESC, 'transmitter'=> SORT_ASC])->all();
			
			$fecha_ini = date('d-m-Y', strtotime($filter_fechas[0]));
			$fecha_fin = date('d-m-Y', strtotime($filter_fechas[1]));			
			
			// get your HTML raw content without any layouts or scripts
			$content = $this->renderAjax('_reportView', ['datos'=>$documentos, 'model'=>$model, 'fecha_ini'=>$fecha_ini, 'fecha_fin'=>$fecha_fin]);
			echo $this->download($content);
			exit;
		}

        return $this->render('_reportForm', [
            'model' => $model,
        ]);
    }	
}
