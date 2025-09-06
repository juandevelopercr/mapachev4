<?php
namespace common\components\ApiV43;

use backend\models\business\Invoice;
use common\models\GlobalFunctions;
use Yii;
use yii\base\InvalidParamException;
use yii\httpclient\Client;
use yii\helpers\Json;
use yii\helpers\Url;
use backend\models\settings\Issuer;


class ApiEnvioHacienda 
{
    /**
     * @param $facturaXML
     * @param $token
     * @param Invoice$factura
     * @param Issuer $emisor
     * @param $tipo_documento
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
	function send($facturaXML, $token, $factura, $emisor, $tipo_documento)
    {
		if ($emisor->enable_prod_enviroment)
		{
			$url_api = 	'https://api.comprobanteselectronicos.go.cr/recepcion/v1/recepcion/';
		}
		else
        {
			$url_api = 'https://api-sandbox.comprobanteselectronicos.go.cr/recepcion/v1/recepcion/';
		}		
				
		$client = new Client([
						'requestConfig' => [
							'format' => Client::FORMAT_JSON
						],
						'responseConfig' => [
							'format' => Client::FORMAT_JSON
						],
					]);
		// Fecha en // ISO 8601 (http://php.net/manual/en/function.date.php
		$fecha = date('c');
		
		//$CallBackUrl = 'http://facturaelectronica.softwaresolutions.co.cr/api/web/callback';		
	    $documento = '';
		$callbackurl = '';	
		
        $url = GlobalFunctions::BASE_URL;

		switch ($tipo_documento)
		{
			case "01":
			          $callbackurl = $url.'/v1/hacienda/callback-send-invoice';
					  $documento = 'la Factura';
					  break;	
			case "02":
			          $callbackurl = $url.'/v1/hacienda/callback-send-debit-note';
					  $documento = 'la Nota de Débito';					  
					  break;	
			case "03":
			          $callbackurl = $url.'/v1/hacienda/callback-send-credit-note';
					  $documento = 'la Nota de Crédito';					  					  
					  break;	
			case "05":
			          $callbackurl = $url.'/api/mensaje-call-back';
					  $documento = 'el mensaje';					  					  
					  break;
		}

		$autorization = 'bearer ' . $token;	

		$error = 0;
		$mensaje = '';	
		$type = '';
		$titulo = '';						
		$response =  NULL;

		try 
		{
			$response = $client->createRequest()
							   ->setMethod('POST')
							   ->setFormat(Client::FORMAT_JSON)
							   ->setUrl($url_api)	
							   ->setHeaders(['Authorization' => $autorization])		
							   ->setData(['clave' => $factura->key,
										  'fecha' => $fecha,
										  'emisor' => [
												'tipoIdentificacion' => $emisor->identificationType->code,
												'numeroIdentificacion' => $emisor->identification
										  ],
										  'receptor' => [
												'tipoIdentificacion' => $factura->customer->identificationType->code,
												'numeroIdentificacion' => $factura->customer->identification
										  ],
										  'callbackUrl' => $callbackurl,
										  'comprobanteXml' => $facturaXML
									  ])		
							   ->send();		
							   
							  // die(var_dump($response));
		} 
		catch (InvalidParamException $e){
			//die(var_dump($response));
			$error = 1;
			$mensaje = "Ha ocurrido un error al tratar de enviar ".$documento." electrónica a la API de Hacienda. Inténtento nuevamente 	y si el error persiste póngase en contacto con el administrador del sistema";
			$type = 'danger';
			$titulo = "Error <hr class=\"kv-alert-separator\">";						
			return ['error'=>$error, 'mensaje' => $mensaje, 'type'=>$type, 'titulo'=>$titulo, 'response'=>$response];				
		}					
		return ['error'=>$error, 'mensaje' => $mensaje, 'type'=>$type, 'titulo'=>$titulo, 'response'=>$response];				
	}

	function sendMensaje($comprobanteXML, $token, $documento, $receptor) {

		if ($receptor->enable_prod_enviroment){
			$url_api = 	'https://api.comprobanteselectronicos.go.cr/recepcion/v1/recepcion/';
		}
		else{
			$url_api = 	'https://api-sandbox.comprobanteselectronicos.go.cr/recepcion/v1/recepcion/';
		}		

		$client = new Client([
						'requestConfig' => [
							'format' => Client::FORMAT_JSON
						],
						'responseConfig' => [
							'format' => Client::FORMAT_JSON
						],
					]);
		// Fecha en // ISO 8601 (http://php.net/manual/en/function.date.php
		$fecha = date('c');
		
		$url = GlobalFunctions::BASE_URL;
		$callbackurl = $url.'/v1/hacienda/callback-mensaje';

		$autorization = 'bearer ' . $token;	

		$error = 0;
		$mensaje = '';	
		$type = '';
		$titulo = '';						
		$response =  NULL;

		$payLoad = [
			'clave' => $documento->key,
			'fecha' => $fecha,
			'emisor' => [
				  'tipoIdentificacion' => $documento->transmitter_identification_type,
				  'numeroIdentificacion' => $documento->transmitter_identification					  
			],
			'receptor' => [
				  'tipoIdentificacion' => $receptor->identificationType->code,
				  'numeroIdentificacion' => $receptor->identification
			],
			'consecutivoReceptor' => $documento->consecutive,
			'callbackUrl' => $callbackurl,
			'comprobanteXml' => $comprobanteXML
		  ];

		try 
		{		
			/*	
			$response = $client->createRequest()
							   ->setMethod('POST')
							   ->setFormat(Client::FORMAT_JSON)
							   ->setUrl($url_api)	
							   ->setHeaders(['Authorization' => $autorization])		
							   ->setData(['clave' => $documento->key,
										  'fecha' => $fecha,
										  'emisor' => [
												'tipoIdentificacion' => $documento->transmitter_identification_type,
												'numeroIdentificacion' => $documento->transmitter_identification					  
										  ],
										  'receptor' => [
												'tipoIdentificacion' => $receptor->identificationType->code,
												'numeroIdentificacion' => $receptor->identification
										  ],
  										  'consecutivoReceptor' => $documento->consecutive,
										  'callbackUrl' => $callbackurl,
										  'comprobanteXml' => $comprobanteXML
									  ])	
							   ->send();							   			
			*/
			$response = $client->createRequest()
								->setMethod('POST')
								->setFormat(Client::FORMAT_JSON)
								->setUrl($url_api)	
								->setHeaders(['Authorization' => $autorization])		
								->setData($payLoad)	
								->send();	
		} 
		catch (InvalidParamException $e){
			$error = 1;
			$mensaje = "Ha ocurrido un error al tratar de enviar el mensaje electrónico a la api de hacienda. Inténtento nuevamente 
						y si el error persiste póngase en contacto con el administrador del sistema";	
			$type = 'danger';
			$titulo = "Error <hr class=\"kv-alert-separator\">";						
			return ['error'=>$error, 'mensaje' => $mensaje, 'type'=>$type, 'titulo'=>$titulo, 'response'=>$response];				
		}					
		return ['error'=>$error, 'mensaje' => $mensaje, 'type'=>$type, 'titulo'=>$titulo, 'response'=>$response];				
	}		


	/*
	function sendMensaje($comprobanteXML, $token, $documento, $receptor) {
		if ($receptor->enable_prod_enviroment){
			$url_api = 	'https://api.comprobanteselectronicos.go.cr/recepcion/v1/recepcion/';
		}
		else{
			$url_api = 	'https://api.comprobanteselectronicos.go.cr/recepcion-sandbox/v1/recepcion/';
		}		
				
		$client = new Client([
						'requestConfig' => [
							'format' => Client::FORMAT_JSON
						],
						'responseConfig' => [
							'format' => Client::FORMAT_JSON
						],
					]);
		// Fecha en // ISO 8601 (http://php.net/manual/en/function.date.php
		$fecha = date('c');
		
		$url = GlobalFunctions::BASE_URL;
		$callbackurl = $url.'/v1/hacienda/callback-mensaje';

		$autorization = 'bearer ' . $token;	

		$error = 0;
		$mensaje = '';	
		$type = '';
		$titulo = '';						
		$response =  NULL;
		try 
		{
			$response = $client->createRequest()
							   ->setMethod('POST')
							   ->setFormat(Client::FORMAT_JSON)
							   ->setUrl($url_api)	
							   ->setHeaders(['Authorization' => $autorization])		
							   ->setData(['clave' => $documento->key,
										  'fecha' => $fecha,
										  'emisor' => [
												'tipoIdentificacion' => $documento->transmitter_identification_type,
												'numeroIdentificacion' => $documento->transmitter_identification					  
										  ],
										  'receptor' => [
												'tipoIdentificacion' => $receptor->identificationType->code,
												'numeroIdentificacion' => $receptor->identification
										  ],
  										  'consecutivoReceptor' => $documento->consecutive,
										  'callbackUrl' => $callbackurl,
										  'comprobanteXml' => $comprobanteXML
									  ])	
							   ->send();							   
		} 
		catch (InvalidParamException $e){
			$error = 1;
			$mensaje = "Ha ocurrido un error al tratar de enviar el mensaje electrónico a la api de hacienda. Inténtento nuevamente 
						y si el error persiste póngase en contacto con el administrador del sistema";	
			$type = 'danger';
			$titulo = "Error <hr class=\"kv-alert-separator\">";						
			return ['error'=>$error, 'mensaje' => $mensaje, 'type'=>$type, 'titulo'=>$titulo, 'response'=>$response];				
		}					
		return ['error'=>$error, 'mensaje' => $mensaje, 'type'=>$type, 'titulo'=>$titulo, 'response'=>$response];				
	}
	*/		

}