<?php
namespace common\components\mensajes;

use Yii;
use backend\models\business\Documents;
use backend\models\settings\Issuer;
use backend\models\nomenclators\UtilsConstants;

class AlmacenarDatos{
	public function __construct(){

	}

	public function almacenarMensajeAceptacion($nodo){
		$model = new Documents();
		$model->LoadDefaultValues();
		
		$emisor = $this->getEmisor($nodo['receptor_identificacion']);

		// Revisar si ya existe un documento con esa llave
		$doc = Documents::find()->where(['key'=>$nodo['clave']])->one();

		if (is_null($doc) && !is_null($emisor))
		{
			$model->receiver_id = $emisor->id;
			$model->key = $nodo['clave']; 
			$model->type = 1;  // Gastos 
			$model->document_type = $nodo['tipo']; 
			$model->transmitter = $nodo['emisor']; 
			$model->transmitter_identification_type = $nodo['emisor_tipo_identificacion']; 			
			$model->transmitter_identification = $nodo['emisor_identificacion']; 				
			$model->transmitter_email = $nodo['emisor_email']; 
			//$model->emission_date = date('Y-m-d H:i:s', strtotime($nodo['fecha_emision'])); 
			$model->emission_date = substr($nodo['fecha_emision'], 0, 10);  
			$model->xml_emission_date = $nodo['fecha_emision'];
			//$model->proveedor = $nodo['fecha_emision'];  
			
			$model->reception_date = date('Y-m-d H:i:s');
			$model->currency = $nodo['moneda']; 
			$model->total_tax = $nodo['total_impuesto']; 				
			$model->total_invoice = $nodo['total_factura'];
			$model->condition_sale = $nodo['condition_sale']; 																									
			$model->consecutive = $model->getConsecutivo();
			$model->total_amount_tax_credit = 0;
			$model->total_amount_applicable_expense = 0;			
			$model->attempts_making_set = 0;
			$model->attempts_making_get = 0;	
			$model->message_detail = '-';

			// copiar tanto el pdf como el xml en la carpeta backend\web\uploads\documents 
			$model->url_xml = $model->document_type.'-'.$nodo['clave'].'.xml';
			$model->url_pdf = $model->document_type.'-'.$nodo['clave'].'.pdf';						
			$model->status = UtilsConstants::HACIENDA_STATUS_ACEPTADO_RECEPTOR; 
			//die(var_dump($model));
			if ($model->save()){
				$resultado = true;						
			}
			else
				$resultado = false;
			return $resultado;
		}
		else
			return false;
	}


////////////////////////////////////////////////////
//
////////////////////////////////////////////////////


	public function comprobarMensajeAceptacion($clave){
		$documento = Documents::find()->where(['key'=>$clave])->one();
		if (!is_null($documento))
			return 1;
		else
			return 0;	
	}


////////////////////////////////////////////////////
//
////////////////////////////////////////////////////


	public function getEmisor($identificacion){
		$emisor = Issuer::find()->where(['identification'=>$identificacion])->one();
		return $emisor;	
	}

}//fin de la clase
?>