<?php
namespace common\components\ApiV43;
use Yii;
use yii\httpclient\Client;
use yii\helpers\Json;
use yii\helpers\Url;
use common\components\ApiAccess;
use common\components\ApiXML;
use common\components\ApiFirmadoHacienda;
use common\components\ApiEnvioHacienda;
use common\components\ApiConsultaHacienda;

/**
 * Acceso a la Api
 * Este archivo contiene el proceso login y acceso al token
 *
 **/
class Api 
{
	public $ApiAccess;
	public $ApiXML;
	public $ApiFirmadoHacienda;
	public $ApiEnvioHacienda;
	public $ApiConsultaHacienda;
	
	function __construct() {
	   $this->ApiAccess = new ApiAccess();
	}	
	
	
  	public $token = NULL;
  	
	public function loginHacienda($emisor)
	{			
		if ($emisor->activar_produccion){
			$url_api = 	'https://idp.comprobanteselectronicos.go.cr/auth/realms/rut/protocol/openid-connect/token';
			$api_client = 'api-prod';
		}
		else
		{
			$url_api = 	'https://idp.comprobanteselectronicos.go.cr/auth/realms/rut-stag/protocol/openid-connect/token';
			$api_client = 'api-stag';
		}
		$username = $emisor->usuario_api_hacienda;
		$password = $emisor->password_api_hacienda;
		$error = 0;
		
		$client = new Client();	
		try 
		{				
			$response = $client->createRequest()
				->setMethod('POST')
				->setUrl($url_api)				
				->setHeaders(['Content-Type' => 'application/x-www-form-urlencoded; charset=utf-8'])
				->setData([
					  'client_id' => $api_client, 
					  'username' => $username,
					  'password' => $password,
					  'grant_type' => 'password',
					  'client_secret' => '',//always empty
					  'scope' =>''
				])		
				->send();	
		} 
		catch (Exception $e) {
			$error = 1;
			$mensaje = 'Ha ocurrido un error en el proceso de inicio de sesión en la api de hacienda. Por favor inténtelo nuevamente,
						si el error persiste póngase en contacto con el administrador del sistema';
			$type = 'danger';
			$titulo = "Error <hr class=\"kv-alert-separator\">";						
			return \Yii::$app->response->data  = ['error'=>$error, 'mensaje' => $mensaje, 'type'=>$type, 'titulo'=>$titulo];				
		}
			
		// La api devolvió una respuesta				
		if ($response->getIsOk()) {
			$error = 0;
			$mensaje = 'ok';
			$type = '';
			$titulo = '';
			$data = Json::decode($response->content);
			$this->token = $data['access_token'];
			return \Yii::$app->response->data  =  ['error'=>$error, 'mensaje' => $mensaje, 'type'=>$type, 'titulo'=>$titulo];
		}
		else
		{
			$error = 1;			
			try {
				$data = Json::decode($response->content);
				$errorcode = $data['error'];
				if ($errorcode == 'invalid_grant')
				{
					$mensaje = 'Ha ocurrido un error. Las credenciales de autenticación en la Api de Hacienda son incorrectas. Verifique la información en los datos del Emisor e inténtelo nuevamente';	
					$type = 'danger';
					$titulo = "Error <hr class=\"kv-alert-separator\">";						
				}	
				else
				{
					$mensaje = 'Ha ocurrido un error desconocido.';	
					$type = 'danger';
					$titulo = "Error <hr class=\"kv-alert-separator\">";						
				}				
				return \Yii::$app->response->data  =  ['error'=>$error, 'mensaje' => $mensaje, 'type'=>$type, 'titulo'=>$titulo];
				
			} catch (InvalidParamException $e) {
				$mensaje = 'Ha ocurrido un error Desconocido';	
				$type = 'danger';
				$titulo = "Informaci&oacute;n <hr class=\"kv-alert-separator\">";						
				return \Yii::$app->response->data  =  ['error'=>$error, 'mensaje' => $mensaje, 'type'=>$type, 'titulo'=>$titulo];				
			}
		}			
	}	
	
	public function CloseSesion($token, $emisor)
	{
		if ($emisor->activar_produccion){
			$url_api = 	'https://idp.comprobanteselectronicos.go.cr/auth/realms/rut/protocol/openid-connect/logout';
			$api_client = 'api-prod';
		}
		else
		{
			$url_api = 	'https://idp.comprobanteselectronicos.go.cr/auth/realms/rut-stag/protocol/openid-connect/logout';
			$api_client = 'api-stag';
		}
				
		$client = new Client();			
		$response = $client->createRequest()
			->setMethod('POST')
			->setUrl($url_api)				
			->setHeaders(['Content-Type' => 'application/x-www-form-urlencoded; charset=utf-8'])
		    ->setData([
						  'client_id' => $api_client, 
						  'refresh_token' => $token,
					  ])		
			->send();
		if ($response->isOk) {

		}		
	}	
}