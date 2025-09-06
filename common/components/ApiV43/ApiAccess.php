<?php
namespace common\components\ApiV43;

use backend\models\settings\Issuer;
use Yii;
use yii\base\InvalidParamException;
use yii\httpclient\Client;
use yii\helpers\Json;
use yii\helpers\Url;

/**
 * Acceso a la Api
 * Este archivo contiene el proceso login y acceso al token
 *
 **/
class ApiAccess 
{
  	public $token = NULL;
	public $expires_in = NULL;          // Tiempo de expiración en segundos del access_token
	public $refresh_token = NULL;       // Este es el token utilizado para el proceso de Refresh, este token tiene un tiempo de expiraci;on mucho mayor que el access_token
	public $refresh_expires_in = NULL;  // Tiempo de expiración en segundos del refresh_token, cuando el refresh_token expira se acabó la sessión y es necesario crear una nueva sesión			


    /**
     * @param Issuer $emisor
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
	public function loginHacienda($emisor)
	{			
		if ($emisor->enable_prod_enviroment){
			$url_api = 	'https://idp.comprobanteselectronicos.go.cr/auth/realms/rut/protocol/openid-connect/token';
			$api_client = 'api-prod';
		}
		else
		{
			//$url_api = 'https://idp.comprobanteselectronicos.go.cr/auth/realms/rut-stag/protocol/openid-connect/token';
			$url_api = 'https://idp.comprobanteselectronicos.go.cr/auth/realms/rut-stag/protocol/openid-connect/token';
			$api_client = 'api-stag';
		}
		$username = $emisor->api_user_hacienda;
		$password = $emisor->api_password;
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
					  'scopes' =>''
				])		
				->send();					
		} 
		catch (\Exception $e) {			
			$error = 1;
			$mensaje = 'Ha ocurrido un error en el proceso de inicio de sesión en la API de Hacienda. Por favor inténtelo nuevamente, si el error persiste póngase en contacto con el administrador del sistema';
			$type = 'danger';
			$titulo = "Error <hr class=\"kv-alert-separator\">";						
			return ['error'=>$error, 'mensaje' => $mensaje, 'type'=>$type, 'titulo'=>$titulo];
		}
			
		// La api devolvió una respuesta				
		if ($response->getIsOk()) {
			$error = 0;
			$mensaje = 'ok';
			$type = '';
			$titulo = '';
			$data = Json::decode($response->content);
			$this->token = $data['access_token'];
			$this->expires_in = $data['expires_in'];
			$this->refresh_token = $data['refresh_token'];
			$this->refresh_expires_in = $data['refresh_expires_in'];			
			return  ['error'=>$error, 'mensaje' => $mensaje, 'type'=>$type, 'titulo'=>$titulo];
		}
		else
		{
			$error = 1;			
			try {
				$data = Json::decode($response->content);
				$errorcode = $data['error'];
				if ($errorcode == 'invalid_grant')
				{
					$mensaje = 'Ha ocurrido un error. Las credenciales de autenticación en la API de Hacienda son incorrectas. Verifique la información en los datos del Emisor e inténtelo nuevamente';
					$type = 'danger';
					$titulo = "Error <hr class=\"kv-alert-separator\">";						
				}	
				else
				{
					$mensaje = 'Ha ocurrido un error desconocido.';	
					$type = 'danger';
					$titulo = "Error <hr class=\"kv-alert-separator\">";						
				}				
				return ['error'=>$error, 'mensaje' => $mensaje, 'type'=>$type, 'titulo'=>$titulo];
				
			} catch (InvalidParamException $e) {
				$mensaje = 'Ha ocurrido un error Desconocido';	
				$type = 'danger';
				$titulo = "Informaci&oacute;n <hr class=\"kv-alert-separator\">";						
				return ['error'=>$error, 'mensaje' => $mensaje, 'type'=>$type, 'titulo'=>$titulo];
			}
		}			
	}

    /**
     * @param Issuer $emisor
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
	public function refreshToken($emisor)
	{			
		if ($emisor->enable_prod_enviroment){
			$url_api = 	'https://idp.comprobanteselectronicos.go.cr/auth/realms/rut/protocol/openid-connect/token';
			$api_client = 'api-prod';
		}
		else
		{
			$url_api = 	'https://idp.comprobanteselectronicos.go.cr/auth/realms/rut-stag/protocol/openid-connect/token';
			$api_client = 'api-stag';
		}
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
					  'refresh_token' => $this->refresh_token,
					  'grant_type' => 'refresh_token',
					  'client_secret' => '',//always empty
					  'scope' =>''
				])		
				->send();	
		} 
		catch (\Exception $e) {
			$error = 1;
			$mensaje = 'Ha ocurrido un error en el proceso de inicio de sesión en la api de hacienda. Por favor inténtelo nuevamente,
						si el error persiste póngase en contacto con el administrador del sistema';
			$type = 'danger';
			$titulo = "Error <hr class=\"kv-alert-separator\">";						
			return ['error'=>$error, 'mensaje' => $mensaje, 'type'=>$type, 'titulo'=>$titulo];				
		}
			
		// La api devolvió una respuesta				
		if ($response->getIsOk()) {
			$error = 0;
			$mensaje = 'ok';
			$type = '';
			$titulo = '';
			$data = Json::decode($response->content);
			$this->token = $data['access_token'];
			$this->expires_in = $data['expires_in'];
			$this->refresh_token = $data['refresh_token'];
			$this->refresh_expires_in = $data['refresh_expires_in'];			
			return ['error'=>$error, 'mensaje' => $mensaje, 'type'=>$type, 'titulo'=>$titulo];
		}
		else
		{
			$error = 1;			
			try {
				$data = Json::decode($response->content);
				$errorcode = $data['error'];
				if ($errorcode == 'invalid_grant')
				{
					$mensaje = 'Ha ocurrido un error. Las credenciales de autenticación en la API de Hacienda son incorrectas. Verifique la información en los datos del Emisor e inténtelo nuevamente';
					$type = 'danger';
					$titulo = "Error <hr class=\"kv-alert-separator\">";						
				}	
				else
				{
					$mensaje = 'Ha ocurrido un error desconocido.';	
					$type = 'danger';
					$titulo = "Error <hr class=\"kv-alert-separator\">";						
				}				
				return ['error'=>$error, 'mensaje' => $mensaje, 'type'=>$type, 'titulo'=>$titulo];
				
			} catch (InvalidParamException $e) {
				$mensaje = 'Ha ocurrido un error Desconocido';	
				$type = 'danger';
				$titulo = "Informaci&oacute;n <hr class=\"kv-alert-separator\">";						
				return ['error'=>$error, 'mensaje' => $mensaje, 'type'=>$type, 'titulo'=>$titulo];				
			}
		}			
	}

    /**
     * @param $token
     * @param Issuer $emisor
     * @throws \yii\base\InvalidConfigException
     */
	public function CloseSesion($token, $emisor)
	{
		if ($emisor->enable_prod_enviroment){
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