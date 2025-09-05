<?php
namespace common\components\mensajes;
use Yii;
use common\components\mensajes\VerificarXML;
use yii\base\Exception;
//error_reporting(E_ALL);
//ini_set('display_errors', '1');

//date_default_timezone_set("America/Costa_Rica");
//session_start();

class ProcesarCorreos{

	private $rutaDescargados;
	private $rutaProcesados;
	private $rutaRevision;
	private $rutaDuplicados;

	public function __construct(){
		$this->rutaDescargados = Yii::getAlias("@backend/web/documentos/smtp/descargados/");
		$this->rutaProcesados = Yii::getAlias("@backend/web/documentos/smtp/procesados/");
		$this->rutaRevision = Yii::getAlias("@backend/web/documentos/smtp/revisar/");
		$this->rutaDuplicados = Yii::getAlias("@backend/web/documentos/smtp/duplicados/");

		$this->verificar = new VerificarXML();
	}


///////////////////////////////////////////////////////////////////////////////
//
///////////////////////////////////////////////////////////////////////////////

	public function procesarDirectoriosCorreos($ruta){
		try{

			if (is_dir($ruta)){ 
			    if ($dh = opendir($ruta)){ 
			        while (($file = readdir($dh)) !== false){ 
			          
			            if (is_dir($ruta . $file) && $file!="." && $file!=".."){ 

						   		//echo "<br>Directorio: $ruta$file:<BR>"; 
						   
						    	$dir = $ruta.$file;
								if (is_dir($dir)) {
								if ($dg = opendir($dir)) {
								 $stack = array();
								    //$i = 0;
									while (($file = readdir($dg)) !== false){
										
									  	$file_parts = pathinfo($file);
									   	$stack["ruta"][0] = $dir;	

										if($file_parts['extension'] == "xml" || $file_parts['extension'] == "pdf"){

											if($file_parts['extension'] == "xml"){
											  $stack["xml"][] = $file;	
											}

											if($file_parts['extension'] == "pdf"){
											  $stack["pdf"][0] = $file;	
											}		
										}
									}

									//ACA TENEMOS LAS RUTAS DE LOS ARCHIVOS Q QUEREMOS
									$this->procesarArchivos($stack);
								}
							}
			            } 
			        } 
			    closedir($dh); 
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

	private function procesarArchivos($stack){
	 	
	 	try{

	 		if(!isset($stack["xml"][0])){
	 			$control = true;
	 		}else{
	 			foreach($stack["xml"] AS $st){

	 				$elXML=$stack["ruta"][0]."/".$st;
					$elPDF = '';
					if (isset($stack["pdf"]) && !empty($stack["pdf"]))
					{
						$elPDF=$stack["ruta"][0]."/".$stack["pdf"][0];
					}
	 				$control = false;

	 				if (file_exists($elXML) && !empty($elXML)){
	 					$this->verificar->verificarXml($elXML, $elPDF, $control);	 					
	 				}
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

	private function crearDirectorios($carpeta){

		if(!file_exists($carpeta)){
			@mkdir($carpeta);
		}

		return $carpeta."<br>";
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

/*
$archivos=new Archivos();
$archivos->procesarDirectoriosCorreos(__DIR__."/Documentos_Descargados/");
*/
?>