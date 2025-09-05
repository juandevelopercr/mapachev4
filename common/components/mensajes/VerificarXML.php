<?php 
namespace common\components\mensajes;

use backend\models\business\Documents;
use backend\models\business\AccountsPayable;
use Yii;
use common\components\mensajes\AlmacenarDatos;
use backend\models\settings\Issuer;
use common\models\GlobalFunctions;
//date_default_timezone_set('America/Costa_Rica');
//include("almacenar-datos.php");

class VerificarXML{

	private $almacenar;
	private $rutaDescargados;
	private $rutaProcesados;
	private $rutaRevision;
	private $rutaDuplicados;	

	public function __construct(){
		$this->almacenar = new AlmacenarDatos();

		$this->rutaDescargados = Yii::getAlias("@backend/web/uploads/smtp/descargados/");
		$this->rutaProcesados = Yii::getAlias("@backend/web/uploads/documents/");
		$this->rutaRevision = Yii::getAlias("@backend/web/uploads/smtp/revisar/");
		$this->rutaDuplicados = Yii::getAlias("@backend/web/uploads/smtp/duplicados/");
	}

	public function verificarXml($elXML,$elPDF,$control){
		$fileParts = pathinfo($elXML);
		$fileParts['extension'];
		$url = explode("/", $elXML);
		$arr = array();

		if (GlobalFunctions::BASE_URL == 'http://rentcarv4.test')
			$position_folder = 6;
		else
			$position_folder = 9;	

		if(strtolower($fileParts['extension'])=='xml'){
			@$xml=simplexml_load_file($elXML);
		}

		if(
			isset($xml->NumeroConsecutivo) &&
			!empty($xml->Clave) &&
			strlen($xml->Clave)==50 &&
			!empty($xml->NumeroConsecutivo) &&
			strlen($xml->NumeroConsecutivo) == 20 &&
			!empty($xml->FechaEmision) &&
			!empty($xml->Emisor->Nombre) &&
			!empty($xml->Emisor->Identificacion->Tipo) &&
			!empty($xml->Receptor->Identificacion->Tipo) &&
			!empty($xml->Receptor->Identificacion->Numero) &&
			!empty($xml->ResumenFactura->TotalComprobante)
		){
		
			if(count($xml->DetalleServicio->LineaDetalle)>0){
				foreach($xml->DetalleServicio->LineaDetalle AS $keynodos){
					$arr[]="Cantidad: ".$keynodos->Cantidad." Detalle: ".$keynodos->Detalle." Total: ".$keynodos->MontoTotalLinea."<br>";
			 	}
			}

			//$checker=true;
			$tipoDoc=substr($xml->Clave,29,2);
			switch($tipoDoc){
				case "01":$tipo="FE";
				break;
				case "02":$tipo="ND";
				break;
				case "03":$tipo="NC";
				break;
				case "04":$tipo="TE";
				break;
				case "08":$tipo="FEC";
				break;				
				case "09":$tipo="FEE";
				break;				
			}
			
			$nodo['clave']=(string)$xml->Clave;
			$nodo['consecutivo']=(string)$xml->NumeroConsecutivo;
			$nodo['emisor']=(string)$xml->Emisor->Nombre;
			$nodo['emisor_email']=(string)$xml->Emisor->CorreoElectronico;			
			$nodo['emisor_tipo_identificacion']=(string)$xml->Emisor->Identificacion->Tipo;
			$nodo['emisor_identificacion']=(string)$xml->Emisor->Identificacion->Numero;
			$nodo['receptor_identificacion'] = (string)$xml->Receptor->Identificacion->Numero;
			
			$nodo['tipo']=$tipo;			
			$nodo['fecha_emision']=(string)$xml->FechaEmision;
			$nodo['fecha_recepcion']=date('Y-m-d H:i:s');
			
			$nodo['moneda'] = $this->validarNodos((string)$xml->ResumenFactura->CodigoTipoMoneda->CodigoMoneda, 'CRC');
			$nodo['total_impuesto'] = $this->validarNodos((string)$xml->ResumenFactura->TotalImpuesto,"0.00000");
			$nodo['total_factura'] = (string)$xml->ResumenFactura->TotalComprobante;
			$nodo['condicionImpuesto'] = '05';
			$nodo['condition_sale'] = isset($xml->CondicionVenta) ? (string)$xml->CondicionVenta: '';
			$nodo['monto_total_impuesto_acreditar'] = 0;
			$nodo['monto_total_de_gasto_aplicable'] = 0;
			
			$infoxml = pathinfo($elXML);
			$infopdf = pathinfo($elPDF);
		
			$nodo['url_xml'] = $elXML; 
			$nodo['url_pdf'] = $elPDF;
						
			//comprueba si el registro existe y devuelve el número de filas
			$comprobar = $this->almacenar->comprobarMensajeAceptacion($nodo['clave']);

			if($comprobar==0){  // No existe el documento en la bd
				//Guarda el registro en la BD y devuelve 1 si lo logró y vacío si no fue así.

				$guardar=$this->almacenar->almacenarMensajeAceptacion($nodo);

				if(!$guardar){//si no guarda lo mando a revisión

					//echo "No se guardó el archivo con clave ".$nodo['clave']."<br>";
					$this->crearDirectorios($this->rutaRevision.$nodo['clave']);
					$this->moverArchivos($this->rutaDescargados.$url[$position_folder],$this->rutaRevision.$nodo['clave'], $nodo);

					$asunto = "Error al Procesar Mensaje del Receptor";
					$msg="No se guardó el archivo, revise el xml del mensaje de receptor con clave ".$nodo['clave']." que se adjunta a este correo<br />";
					
					//$this->enviarEmail($asunto, $msg, $nodo);	
				}else{//si guarda muevo los files

					//echo "Se guardó el archivo con clave ".$nodo['clave']."<br>";
					//$this->crearDirectorios($this->rutaProcesados.$nodo['clave']);	
					//die(var_dump('Mover de '.$this->rutaDescargados.$url[$position_folder]. '    hasta   '.$this->rutaProcesados));									
					$this->moverArchivos($this->rutaDescargados.$url[$position_folder],$this->rutaProcesados, $nodo);
					$documento = new Documents;
					$documento->key = $nodo['clave'];
					$documento->emission_date = $nodo['fecha_emision'];
					$documento->currency = $nodo['moneda'];
					$documento->total_invoice = $nodo['total_factura'];	

					AccountsPayable::addCuentaPorPagar($documento);
				}

			}else{// existe en BD se pasa a revisión
				//echo "La clave ".$nodo['clave']." ya existe en la BD <br>";

				$this->crearDirectorios($this->rutaDuplicados.$nodo['clave']);
				$this->moverArchivos($this->rutaDescargados.$url[$position_folder],$this->rutaDuplicados.$nodo['clave'], $nodo);
				file_put_contents($this->rutaDuplicados.$nodo['clave']."/".$nodo['clave'].".txt", "La clave ".$nodo['clave']." ya fue agregado en la tabla de MR, esta factura fue enviada por ". $nodo['emisor']." por un monto de ".$nodo['moneda']." ".$nodo['total_factura']);

				$asunto = "Error al Procesar Mensaje del Receptor";
				$msg="No se guardó el archivo, el mensaje de receptor con clave ".$nodo['clave']." ya fue registrado en la base de datos. Revise el xml que se adjunta a este correo<br />";
				
				$this->enviarEmail($asunto, $msg, $nodo);				
			}

		}

		//if($data["tipo_documento"][0] == "R"){
		//	$control = true;
		//	unlink($elXML); // Elimina la Respuesta
		//	echo "Respuesta o Factura No valida<BR><BR>";  
		//}
	}

	////////////////////////////////////////////
	//
	////////////////////////////////////////////

	private function validarNodos($nodo,$valor){
		if(!isset($nodo) || empty($nodo)){
			return $valor;
		}else{
			return $nodo;
		}
	}


	////////////////////////////////////////////
	//
	////////////////////////////////////////////

	private function moverArchivos($origen,$destino,$nodo = ''){	
		if(file_exists($origen) && file_exists($destino)){

			$from = $origen;
			$to = $destino;
			
			//Abro el directorio que voy a leer
			$dir = opendir($from);
			
			//Recorro el directorio para leer los archivos que tiene
			while(($file = readdir($dir)) !== false){
				//Leo todos los archivos excepto . y ..
				if(strpos($file, '.') !== 0){
					//Copio el archivo manteniendo el mismo nombre en la nueva carpeta
					$file_new = $file;					
					if (!empty($nodo['tipo']) && !empty($nodo['clave']))
					{
						//Extraigo la extención
						$pos = strrpos($file, ".");
						if($pos==true){ 
							$ext=strtolower(substr($file, $pos+1,3));
							// Renombrar el archivo
							
							$file_new = $nodo['tipo'].'-'.$nodo['clave'].'.'.$ext;								
							//if ($ext == 'xml' || $ext == '.xml')
								//die(var_dump($file_new));
						}
					}					
					if (copy($from.'/'.$file, $to.'/'.$file_new))
						@unlink($from.'/'.$file);
					//die(var_dump($file_new));	
				}
			}
			// Elimino el directorio
			@rmdir($origen);
		}
		/*
		if(file_exists($origen) && file_exists($destino)){
			@rename($origen,$destino);
		}
		*/
	}


	////////////////////////////////////////////
	//
	////////////////////////////////////////////

	private function crearDirectorios($carpeta){
		if(!file_exists($carpeta)){
			@mkdir($carpeta);
		}

		return $carpeta."<br>";
	}
	
	
	////////////////////////////////////////////
	//
	////////////////////////////////////////////	
	
	public function enviarEmail($asunto, $msg, $nodo)
	{
		$emisor = NULL;
		$emisor = Issuer::find()->where(['identification'=>$nodo['receptor_identificacion']])->one();
		if (!is_null($emisor))
		{
			$str = "<table width=\"70%\" align=\"center\" style=\"border-collapse:collapse;\">
				<tr>
					<td align=\"center\"> 
						<hr />
						<br />        
						<h1 style=\"text-align:center; color:#3157F2\"><a href=\"http://www.facturaelectronicacrc.com\" style=\"color:#3157F2; text-decoration:none;\">FACTURA ELECTRONICA CRC.COM</a></h1>
						<span style=\"text-align:center; font-weight:bold; font-size:18px\">Portal de Factura Electrónica</span><br />
						<br />
						<hr />
					</td>
				</tr>
				<tr>
					<td align=\"center\">
						<br />        
						<span style=\"text-align:center;color:#3157F2;font-size:20px;\">Notificación de Procesamiento de Mensaje de Receptor</span><br />
						<br />    
						<br />                
					</td>    
				</tr>			
				<tr>
					<td>
						".$msg."
					</td>
				</tr>
				<tr>
					<td>
						<br />
						<p style=\"text-align:center\">
							Este correo electrónico y cualquier anexo al mismo, contiene información de caracter confidencial
							exclusivamente dirigida a su destinatario o destinatarios. En el caso de haber recibido este correo electrónico
							por error, se ruega la destrucción del mismo.
						</p>
						<p style=\"text-align:center\">
							Copyright © 2019 facturaelectronicacrc.com Powered By <a href=\"https://www.softwaresolutions.co.cr\">softwaresolutions S.A</a><br />
							Todos los derechos reservados
						</p>            
					</td>
				</tr>
			</table>";
			$to = [
				$emisor->email_notificacion_smtp => $emisor->name,
				'caceresvega@gmail.com'=>'Juan Alberto Cáceres Vega',
			];
			$from = $emisor->email; 	
			$subject = $asunto;
		
			$mensage = Yii::$app->mailer->compose("layouts/html", ['content'=>$str])
				->setTo($to)
				->setFrom($from)
				//->setCc($arr_cc)
				->setSubject($subject)
				->setTextBody($str)
				->setHtmlBody($str);	
			
			
			//die(var_dump($archivo_xml));	
			/*	
			$url_xml = Yii::getAlias('@backend/web/documentos/'.$archivo_xml);					
			$nombre_archivo = $clave.'.xml';
			if (file_exists($url_xml))		
				$mensage->attach($url_xml, ['fileName' => $nombre_archivo, 'contentType' => 'text/plain']);	
				
			$url_pdf = Yii::getAlias('@backend/web/documentos/'.$archivo_pdf);					
			$nombre_archivo_pdf = $clave.'.pdf';
			if (file_exists($url_pdf))		
				$mensage->attach($url_pdf, ['fileName' => $nombre_archivo_pdf, 'contentType' => 'text/plain']);											
			*/
			if ($mensage->send()) 
				$respuesta = true;
			else
				$respuesta = false;
			
			return $respuesta;
		}
	}	
	
	

}// Fin de la clase

?>