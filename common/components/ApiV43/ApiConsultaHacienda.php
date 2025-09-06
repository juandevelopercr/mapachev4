<?php
namespace common\components\ApiV43;

use backend\models\business\Invoice;
use backend\models\business\CreditNote;
use backend\models\business\DebitNote;
use backend\models\business\Documents;
use backend\models\nomenclators\UtilsConstants;
use backend\models\settings\Issuer;
use Yii;
use yii\helpers\FileHelper;
use yii\httpclient\Client;
use yii\helpers\Json;
use yii\helpers\Url;
use backend\models\old_erp\DocumentosEstados;

/**
 * Acceso a la Api
 * Este archivo contiene el proceso login y acceso al token
 *
 **/
class ApiConsultaHacienda 
{
    /**
     * @param $comprobante
     * @param Issuer $emisor
     * @param $token
     * @param $tipo_documento
     * @return array|string
     */
	function getEstado($comprobante, $emisor, $token, $tipo_documento) {

        $doc_id = $comprobante->id;
		$key = $comprobante->key;

		if ($emisor->enable_prod_enviroment){
			$url = 	'https://api.comprobanteselectronicos.go.cr/recepcion/v1/recepcion/';
		}
		else
		{
			$url = 'https://api-sandbox.comprobanteselectronicos.go.cr/recepcion/v1/recepcion/';
		}		
		
		$curl   = curl_init();
		$clave  = $comprobante->key;
	
		if ($clave == "" || strlen($clave) == 0)
			return "La clave no puede ser en blanco";
	
		curl_setopt_array($curl, array(
			CURLOPT_URL             => $url . $clave,
			CURLOPT_RETURNTRANSFER  => true,
			CURLOPT_ENCODING        => "",
			CURLOPT_MAXREDIRS       => 10,
			CURLOPT_SSL_VERIFYHOST  => 0,
			CURLOPT_SSL_VERIFYPEER  => 0,
			CURLOPT_TIMEOUT         => 30,
			CURLOPT_HTTP_VERSION    => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST   => "GET",
			CURLOPT_HTTPHEADER      => array(
				"Authorization: Bearer " . $token,
				"Cache-Control: no-cache",
				"Content-Type: application/x-www-form-urlencoded",
				"Postman-Token: bf8dc171-5bb7-fa54-7416-56c5cda9bf5c"
			),
		));
	
		$response   = curl_exec($curl);
		$status     = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$err        = curl_error($curl);
		curl_close($curl);		

		$actualizar = 0;
		$estado = '';
		$prefijo_respuesta = '';

		switch ($tipo_documento)
		{
			case "01":
					  $documento = 'La Factura Electrónica';
					  $prefijo_respuesta = 'FE-MH-';
					  break;	
			case "02":
					  $documento = 'La Nota de Débito Electrónica';					  
					  $prefijo_respuesta = 'ND-MH-';					  
					  break;	
			case "03":
					  $documento = 'La Nota de Crédito Electrónica';					  					  
					  $prefijo_respuesta = 'NC-MH-';					  
					  break;	
			case "05":
					  $documento = 'El mensaje de Receptor Electrónico';					  					  
					  $prefijo_respuesta = 'MR-MH-';					  
					  break;						  
			case "08":
					  $documento = 'La factura Electrónica de Compra';					  					  
					  $prefijo_respuesta = 'FEC-MH-';					  
					  break;						  
		}

        if(!file_exists("uploads/xmlh/") || !is_dir("uploads/xmlh/")){
            try{
                FileHelper::createDirectory("uploads/xmlh/", 0777);
            }catch (\Exception $exception){
                Yii::info("Error handling xmlh folder resources");
            }
        }		

		if ($err)
		{
			$mensaje = 'Estado: '. $status.'  <br/ >'.  ' para'. $documento.': '.$comprobante->key.'  <br/ >'.  'Mensaje: '.$err;
			$type = 'danger';
			$titulo = "Error <hr class=\"kv-alert-separator\">";	
			$actualizar = 0;
		}
		else
		{
            $response = str_replace("ind-estado", "ind_estado", $response);
            $response = str_replace("respuesta-xml", "respuesta_xml", $response);
			$response = json_decode($response);
			
			if ($response->ind_estado == 'rechazado')
			{
				$estado = 'rechazado';
				$xml_response_hacienda_decode = base64_decode($response->respuesta_xml);				
				if($tipo_documento == '01')
                {
					Invoice::verifyResponseStatusHacienda($key, $estado, $xml_response_hacienda_decode);
                }
				elseif($tipo_documento == '02')
                {
					DebitNote::verifyResponseStatusHacienda($key, $estado, $xml_response_hacienda_decode);
                }				
                elseif($tipo_documento == '03')
                {                    
					CreditNote::verifyResponseStatusHacienda($key, $estado, $xml_response_hacienda_decode);
                }
                elseif($tipo_documento == '05')
                {										
					Documents::verifyResponseStatusHacienda($key, $estado, $xml_response_hacienda_decode);                    
                }
                // Rechazada
				$type = 'danger';
				$titulo = "Error <hr class=\"kv-alert-separator\">";
				$actualizar = 1;	
				$mensaje = $documento." con clave: [".$comprobante->key."] fue rechazado por Hacienda. <br />Revise el archivo xml de respuesta de Hacienda para más detalles";
			}
			elseif ($response->ind_estado == 'aceptado') {
				$estado = 'aceptado';
				$xml_response_hacienda_decode = base64_decode($response->respuesta_xml);
				if($tipo_documento == '01')
                {
					Invoice::verifyResponseStatusHacienda($key, $estado, $xml_response_hacienda_decode);
                }
				elseif($tipo_documento == '02')
                {
					DebitNote::verifyResponseStatusHacienda($key, $estado, $xml_response_hacienda_decode);
                }				
                elseif($tipo_documento == '03')
                {                    
					CreditNote::verifyResponseStatusHacienda($key, $estado, $xml_response_hacienda_decode);
                }
                elseif($tipo_documento == '05')
                {										
					Documents::verifyResponseStatusHacienda($key, $estado, $xml_response_hacienda_decode);                    
                }				

				$mensaje = $documento." con clave: [".$comprobante->key."] fue aceptada por Hacienda. "."<br >Revise el archivo xml de respuesta de Hacienda para más detalles";
                $type = 'success';
				$titulo = "Informaci&oacute;n <hr class=\"kv-alert-separator\">";	
				$actualizar = 1;					
			}
			elseif ($response->ind_estado == 'recibido'){
				$estado = 'recibido';
				if($tipo_documento == '01')
                {
					Invoice::setStatusHacienda($doc_id, UtilsConstants::HACIENDA_STATUS_RECEIVED); // Recibida
                }
				elseif($tipo_documento == '02')
                {
					DebitNote::setStatusHacienda($doc_id, UtilsConstants::HACIENDA_STATUS_RECEIVED); // Recibida					
                }				
                elseif($tipo_documento == '03')
                {                    
					CreditNote::setStatusHacienda($doc_id, UtilsConstants::HACIENDA_STATUS_REJECTED); // Rechazada					
                }
                elseif($tipo_documento == '05')
                {			
					$status = UtilsConstants::HACIENDA_STATUS_RECIBIDO_HACIENDA; 
                    switch ($comprobante->status)
                    {
                        case 7: $status = UtilsConstants::HACIENDA_STATUS_RECIBIDO_HACIENDA; 
                                break;
                        case 8: $status = UtilsConstants::HACIENDA_STATUS_RECIBIDO_PARCIAL_HACIENDA; 
                                break;
                        case 9: $status = UtilsConstants::HACIENDA_STATUS_RECIBIDO_RECHAZADO_HACIENDA; 
                                break;							
                    }
                    Documents::setStatusHacienda($doc_id, $status); // Recibida										
                }	

				$mensaje = $documento." con clave: [".$comprobante->key."] aún se encuentra en estado Recibida.";
				$type = 'warning';
				$titulo = "Advertencia <hr class=\"kv-alert-separator\">";	
				$actualizar = 0;				
			}
			elseif ($response->ind_estado == 'procesando'){
				$mensaje = $documento." con clave: [".$comprobante->key."] se encuentra en estado Procesando.";
				$type = 'warning';
				$titulo = "Advertencia <hr class=\"kv-alert-separator\">";	
				$actualizar = 0;				
			}
			elseif ($response->ind_estado == 'error'){
				$mensaje = "Error";
				$type = 'warning';
				$titulo = "Advertencia <hr class=\"kv-alert-separator\">";	
				$actualizar = 0;				
			}			
			else {
				die(var_dump($response));
				$mensaje = "Ha ocurrido un error desconocido al consultar el estado de ". $documento ." electrónica con clave: [".$comprobante->key."]. Póngase en contacto con el administrador del sistema";
				$type = 'warning';
				$titulo = "Informaci&oacute;n <hr class=\"kv-alert-separator\">";	
			}						
		}
		return ['mensaje' => $mensaje, 'type'=>$type, 'titulo'=>$titulo, 'actualizar'=>$actualizar, 'estado'=>$estado];			
	}
}