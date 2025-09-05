<?php
namespace common\components;

use backend\modules\facturacion\models\Configuracion;

class ApiBCCR
{
	/*
	La siguiente Funcion debe recibir por parametro la fecha en formato dd/mm/YYYY
	*/
	public static function getCambio($fecha){
		
		$indicador = "318";
		$FechaInicio = date('Y/m/d');
		$FechaFinal = date('Y/m/d');
		$nombre = "Henry";
		$SubNiveles = "S";
		$CorreoElectronico = "hromancr@gmail.com";
 		$Token = "AI9RRO74HA";
		//$url_1 = "https://gee.bccr.fi.cr/Indicadores/Suscripciones/WS/wsindicadoreseconomicos.asmx/ObtenerIndicadoresEconomicosXML?Indicador=318&FechaInicio=2020/01/07&FechaFinal=2020/01/07&Nombre=Henry%20Roman&SubNiveles=S&CorreoElectronico=hromancr@gmail.com&Token=AI9RRO74HA";
		$url = "https://gee.bccr.fi.cr/Indicadores/Suscripciones/WS/wsindicadoreseconomicos.asmx/ObtenerIndicadoresEconomicosXML?Indicador=".$indicador."&FechaInicio=".$FechaInicio."&FechaFinal=".$FechaFinal."&Nombre=".$nombre."&SubNiveles=".$SubNiveles."&CorreoElectronico=".$CorreoElectronico."&Token=".$Token."";

		$valor = '';  
		/* 
		// Extrae el tipo cambio con el valor de COMPRA	
		$data_file = file_get_contents($file["compra"]);
		$ainfo = self::parser_extractor($data_file,false);
		$fuente = self::parser_extractor($ainfo["STRING"][0]);
		$tipo["compra"] = $fuente["NUM_VALOR"][0];
		*/
		
		// Extrae el tipo cambio
		$data_file = simplexml_load_string(@file_get_contents($url));
		if ($data_file != false)
			$valor = trim(strip_tags(substr($data_file, strpos($data_file, "<NUM_VALOR>"), strripos($data_file, "</NUM_VALOR>"))));

		// Retornando valor del dolar
		if ($valor == ''){
			return false;
		}else{
			return (float)$valor;
		}
	}
	 
	public static function getTipoCambio()
	{
		$configuracion = Configuracion::find()->where(['id'=>1])->one();
		$fecha = date('Y-m-d');
		if ($configuracion->fecha_tipo_cambio != $fecha)
		{

			$fecha = date('d/m/Y');
			$valor = self::getCambio($fecha);

			if ($valor == 0)
				$valor = $configuracion->tipo_cambio_dolar;			
			else
			{
				$configuracion->tipo_cambio_dolar = $valor;
				$configuracion->fecha_tipo_cambio = date('Y-m-d');							
				$configuracion->save();
			}	
		}	
		else
			$valor = $configuracion->tipo_cambio_dolar;
		return $valor;
	} 
}
?>