<?php

namespace backend\components;

use backend\models\settings\Issuer;

class ApiBCCR
{
    /*
     * Funcion para conectar al API del BCCR y obtener el tipo de cambio del dolar en la fecha actual
    */
    public static function getChangeValueFromApiBccr()
    {
        $valor = false;
        $indicador = "318";
        $FechaInicio = date('Y/m/d');
        $FechaFinal = date('Y/m/d');
        $nombre = "Henry";
        $SubNiveles = "S";
        $CorreoElectronico = "hromancr@gmail.com";
        $Token = "AI9RRO74HA";
        //$url_1 = "https://gee.bccr.fi.cr/Indicadores/Suscripciones/WS/wsindicadoreseconomicos.asmx/ObtenerIndicadoresEconomicosXML?Indicador=318&FechaInicio=2020/01/07&FechaFinal=2020/01/07&Nombre=Henry%20Roman&SubNiveles=S&CorreoElectronico=hromancr@gmail.com&Token=AI9RRO74HA";
        $url = "https://gee.bccr.fi.cr/Indicadores/Suscripciones/WS/wsindicadoreseconomicos.asmx/ObtenerIndicadoresEconomicosXML?Indicador=".$indicador."&FechaInicio=".$FechaInicio."&FechaFinal=".$FechaFinal."&Nombre=".$nombre."&SubNiveles=".$SubNiveles."&CorreoElectronico=".$CorreoElectronico."&Token=".$Token."";

        // Extrae el tipo cambio
        $data_file = simplexml_load_string(@file_get_contents($url));

        if ($data_file !== false) {
            $valor = trim(strip_tags(substr($data_file, strpos($data_file, "<NUM_VALOR>"), strripos($data_file, "</NUM_VALOR>"))));
        }

        return ($valor !== false)? (float)$valor : $valor;
    }

    /**
     * Función para obtener el tipo de cambio general del emisor verificando si hay diferencias en el tipo de cambio del sistema con respecto al API del BCCR
     * @return bool|float|int|mixed|null
     */
    public static function getChangeTypeOfIssuer()
    {
        $new_vale = self::getChangeValueFromApiBccr();
        $current_value_issuer = Issuer::getChange_type_dollar();

        if($new_vale !== false && $new_vale !== $current_value_issuer)
        {
            $current_value_issuer = $new_vale;
            Issuer::setChange_type_dollar($new_vale);
        }

        return $current_value_issuer;
    }
}
?>