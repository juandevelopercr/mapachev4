<?php
namespace common\components\ApiV43;

use common\components\ApiV43\firmador\hacienda\Firmador;

class ApiFirmadoHacienda
{
  public function firmar($pfx, $pin,$xml,$tipodoc) {

	// Nuevo firmador
	$firmador = new Firmador();
	
	$xml = base64_decode($xml);
	// Se firma XML y se recibe un string resultado en Base64
	$base64 = $firmador->firmarXml($pfx, $pin, $xml, $firmador::TO_BASE64_STRING);

	return $base64;
  }

}
