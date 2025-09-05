<?
error_reporting(E_ALL);
ini_set('display_errors', '1');

date_default_timezone_set("America/Costa_Rica");
session_start();

include("almacenar-datos.php");

class Archivos{

	private $rutaDescargados;
	private $rutaProcesados;
	private $rutaRevision;
	private $almacenar;

	public function __construct(){
		$this->rutaDescargados=__DIR__."/Documentos_Descargados/";
		$this->rutaProcesados=__DIR__."/Documentos_Procesados/";
		$this->rutaRevision=__DIR__."/Documentos_Revisar/";
		$this->rutaDuplicados=__DIR__."/Documentos_Duplicados/";

		$this->almacenar=new DatosXml();
	}


///////////////////////////////////////////////////////////////////////////////
//
///////////////////////////////////////////////////////////////////////////////

	public function procesarDirectoriosCorreos($rutaDir){
		try{

			$elemento=array();

			if (is_dir($rutaDir)){ 

			  	if ($open1 = opendir($rutaDir)){ 

			     	while(($carpeta = readdir($open1)) !== false){

			     		$ruta2=$rutaDir.$carpeta;

			     		if(is_dir($ruta2) && $carpeta!=".." && $carpeta!="."){

							if($open2 = opendir($ruta2)){

								while(($archivo = readdir($open2)) !== false){

									 if($archivo!='.' && $archivo!='..'){
									 	$this->procesarArchivos($archivo,$carpeta);
									 }

								}

							}

							closedir($open2);
			     		}

        			}

       			 	closedir($open1);
			  	} 
			}
		}
		catch(Exception $e){
			die($e->getMessage());
		}
	}

///////////////////////////////////////////////////////////////////////////////
//
///////////////////////////////////////////////////////////////////////////////

	private function procesarArchivos($xml,$carpeta){
	 	
	 	try{

	 		$checker=false;

	 		$file_parts = pathinfo($xml);
	 		
			$elxml=trim($this->rutaDescargados.$carpeta."/".$xml);

			if(!empty($this->rutaDescargados) && !empty($carpeta) && !empty($xml)){
				
			if(strtolower($file_parts['extension'])=='xml'){
				@$xml= simplexml_load_file($elxml);
			}

	    	if(
	    		isset($xml->NumeroConsecutivo) &&
	    		!empty($xml->Clave) &&
	    		strlen($xml->Clave)==50 &&
	    		!empty($xml->NumeroConsecutivo) &&
	    		strlen($xml->NumeroConsecutivo) == 20 &&
	    		!empty($xml->ResumenFactura->CodigoMoneda) &&
	    		!empty($xml->FechaEmision) &&
	    		!empty($xml->ResumenFactura->TipoCambio) && 
	    		!empty($xml->Emisor->Nombre) &&
	    		!empty($xml->Emisor->Identificacion->Tipo) &&
	    		!empty($xml->Receptor->Identificacion->Tipo) &&
	    		!empty($xml->Receptor->Identificacion->Numero) &&
	    		!empty($xml->ResumenFactura->TotalComprobante)
	    	){
				$checker=true;

				$tipoDoc=substr($xml->Clave,29,2);
				switch($tipoDoc){
					case 01:$tipo="FE";
					break;
					case 02:$tipo="ND";
					break;
					case 03:$tipo="NC";
					break;
					case 04:$tipo="TE";
					break;
				}

				$nodo['clave']=$xml->Clave;
				$nodo['consec']=$xml->NumeroConsecutivo;
				$nodo['codmoneda']=$xml->ResumenFactura->CodigoMoneda;
				$nodo['fechaemision']=$xml->FechaEmision;
				$nodo['tipocambio']=$xml->ResumenFactura->TipoCambio;
				$nodo['nombreemisor']=$xml->Emisor->Nombre;
				$nodo['nombrecomersialemisor']=$xml->Emisor->NombreComercial;
				$nodo['tipoidemisor']=$xml->Emisor->Identificacion->Tipo;
				$nodo['idemisor']=$xml->Emisor->Identificacion->Numero;
				$nodo['correoemisor']=$xml->Emisor->CorreoElectronico;
				$nodo['telemisor']=$xml->Emisor->Telefono->NumTelefono;
				$nodo['condicionventa']=$xml->CondicionVenta;
				$nodo['mediopago']=$xml->MedioPago;
				$nodo['plazocredito']=$xml->PlazoCredito;
				$nodo['tipoidreceptor']=$xml->Receptor->Identificacion->Tipo;
				$nodo['idreceptor']=$xml->Receptor->Identificacion->Numero;
				$nodo['nombrereceptor']=$xml->Receptor->Nombre;
				$nodo['nombrecomercialreceptor']=$xml->Receptor->NombreComercial;
				$nodo['telreceptor']=$xml->Receptor->Telefono->NumTelefono;
				$nodo['correoreceptor']=$xml->Receptor->CorreoElectronico;
				$nodo['totalservgravado']=$this->validarNodos($xml->ResumenFactura->TotalServGravados,"0.00000");
				$nodo['totalmercgravada']=$xml->ResumenFactura->TotalMercanciasGravadas;
				$nodo['totalgravado']=$xml->ResumenFactura->TotalGravado;
				$nodo['totalservexento']=$this->validarNodos($xml->ResumenFactura->TotalServExentos,"0.00000");
				$nodo['totalmercexenta']=$xml->ResumenFactura->TotalMercanciasExentas;
				$nodo['totalexento']=$xml->ResumenFactura->TotalExento;
				$nodo['totalventa']=$xml->ResumenFactura->TotalVenta;
				$nodo['totaldescuento']=$this->validarNodos($xml->ResumenFactura->TotalDescuentos,"0.00000");
				$nodo['totalventaneta']=$xml->ResumenFactura->TotalVentaNeta;
				$nodo['totalimpuesto']=$this->validarNodos($xml->ResumenFactura->TotalImpuesto,"0.00000");
				$nodo['totalcomprobante']=$xml->ResumenFactura->TotalComprobante;
				$nodo['tipo']=$tipo;


				//Devuelve 0 si no existe y dos si ya existe en la BD
				$comprobar=$this->almacenar->comprobarMensajeAceptacion($nodo['clave']);

				//compruebo que el mensaje no existe en la BD para guardarlo
				if($comprobar==0){

					//Guarda el registro en la BD y devuelve 1 si lo logró y vacío si no fue así.
					$guardar=$this->almacenar->almacenarMensajeAceptacion($nodo['clave'],$nodo);

					//Comprueba si  lo guardó y mueve al directorio de procesados si fue así.
					if($guardar>=1){
						$this->crearDirectorios($this->rutaProcesados.$nodo['clave']);
						$this->moverArchivos($this->rutaDescargados.$carpeta,$this->rutaProcesados.$nodo['clave']);
						echo "Los archivos se movieron a procesados";
					}
					
					//Comprueba si  lo guardó y mueve al directorio de revisión si no fue así.
					if(empty($guardar) || $guardar<1){
					   $rand=microtime();
					   echo $this->rutaRevision.$carpeta.$rand;
					   $this->crearDirectorios($this->rutaRevision.$carpeta.$rand);
					   $this->moverArchivos($this->rutaDescargados.$carpeta,$this->rutaRevision.$carpeta."/".$rand);
					   echo "Los archivos se movieron a revisión";
					}

				}

				//Si se comprueba que existe en la bd entonces se pasa a carpeta Duplicados
				if($comprobar==1){
					$this->crearDirectorios($this->rutaDuplicados.$nodo['clave']);
					$this->moverArchivos($this->rutaDescargados.$carpeta,$this->rutaDuplicados.$nodo['clave']);
					file_put_contents($this->rutaDuplicados.$nodo['clave']."/".$nodo['clave'].".txt", "La clave ".$nodo['clave']." ya fue agregado en la tabla de MR, esta fatura fue enviada por ". $nodo['nombreemisor']." por un monto de ".$nodo['codmoneda']." ".$nodo['totalcomprobante']);
					echo "Los archivos ya existen en la base de datos";
				}

			}

			$checker=false;
			$comprobar=null;
			$guardar=null;
			}
 		    
	 	}

	    catch(Exception $e){
	    	die($e->getMessage());
	    } 
	}



///////////////////////////////////////////////////////////////////////////////
//
///////////////////////////////////////////////////////////////////////////////

	private function crearDirectorios($carpeta){

		if(!file_exists($carpeta)){
			@mkdir($carpeta);
		}

		return $carpeta."<br>";
	}

///////////////////////////////////////////////////////////////////////////////
//
///////////////////////////////////////////////////////////////////////////////

	private function moverArchivos($origen,$destino){

		if(file_exists($origen) && file_exists($destino)){
			@rename($origen,$destino);
		}

		return $origen."<br>";
	}

///////////////////////////////////////////////////////////////////////////////
//
///////////////////////////////////////////////////////////////////////////////

	private function validarNodos($nodo,$valor){
		if(!isset($nodo) || empty($nodo)){
			return $valor;
		}else{
			return $nodo;
		}
	}


///////////////////////////////////////////////////////////////////////////////
//
///////////////////////////////////////////////////////////////////////////////


	private function borrarDirectoriosVacios(){

		try{
			$rutas=array("desc"=>$this->rutaDescargados,"proc"=>$this->rutaProcesados,"rev"=>$this->rutaRevision);

			foreach($rutas as $keyruta){

				$file=@scandir($keyruta);

				foreach($file AS $dir){

					if($dir!="." && $dir!=".."){

						@rmdir($keyruta.$dir);

					}
				}
			}
		}

		catch(Exception $e){
			die($e->getMessage());
		} 
	}


} //Fin de la clase


$archivos=new Archivos();
$archivos->procesarDirectoriosCorreos(__DIR__."/Documentos_Descargados/");
?>