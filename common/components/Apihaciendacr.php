<?php
namespace common\components;
use Yii;
use yii\httpclient\Client;
use yii\helpers\Json;
use yii\helpers\Url;
use backend\modules\facturacion\models\Estados;
use backend\modules\facturacion\models\Configuracion;
use backend\modules\facturacion\models\Facturas;
use backend\modules\facturacion\models\FacturasDetalles;
use backend\modules\facturacion\models\NotasCreditoElectronicasDetalles;
use backend\modules\facturacion\models\NotasDebitoElectronicasDetalles;

/**
 * Firmado para Costa Rica xades EPES
 * Este archivo contiene el proceso de firma en PHP de acuerdo a las especificaciones de Hacienda
 *
 **/
class Apihaciendacr {
  const POLITICA_FIRMA = array(
    "name" 		=> "",
    "url" 		=> "https://tribunet.hacienda.go.cr/docs/esquemas/2016/v4/Resolucion%20Comprobantes%20Electronicos%20%20DGT-R-48-2016.pdf",
    "digest" 	=> "V8lVVNGDCPen6VELRD1Ja8HARFk=" //digest en sha1 y base64
  );
  private $signTime = NULL;
  private $signPolicy = NULL;
  private $publicKey = NULL;
  private $privateKey = NULL;
  private $cerROOT = NULL;
  private $cerINTERMEDIO = NULL;
  private $tipoDoc = '01';
  
  // Definido por caceres
  public $token = NULL;
    
  public function retC14DigestSha1($strcadena){
	$strcadena = str_replace("\r", "", str_replace("\n", "", $strcadena));
	$d1p = new \DomDocument('1.0','UTF-8');
	$d1p->loadXML($strcadena);
	$strcadena=$d1p->C14N();
    return base64_encode(hash('sha256' , $strcadena, true ));
  }
  
  public function firmar($certificadop12, $clavecertificado,$xmlsinfirma,$tipodoc) {
	if (!$pfx = file_get_contents($certificadop12)) {
		echo "Error: No se puede leer el fichero del certificado o no existe en la ruta especificada\n";
		exit;
	}
	if (openssl_pkcs12_read($pfx, $key, $clavecertificado)) {
		$this->publicKey    =$key["cert"];
		$this->privateKey   =$key["pkey"];
		$complem = openssl_pkey_get_details(openssl_pkey_get_private($this->privateKey));
		$this->Modulus = base64_encode($complem['rsa']['n']);
		$this->Exponent= base64_encode($complem['rsa']['e']);
	} else {
		echo "Error: No se puede leer el almacén de certificados o la clave no es la correcta.\n";
		exit;
	}
	$this->signPolicy = self::POLITICA_FIRMA;
	$this->signatureID 		= "Signature-ddb543c7-ea0c-4b00-95b9-d4bfa2b4e411";
	$this->signatureValue 	= "SignatureValue-ddb543c7-ea0c-4b00-95b9-d4bfa2b4e411";
	$this->XadesObjectId 	= "XadesObjectId-43208d10-650c-4f42-af80-fc889962c9ac";
	$this->KeyInfoId 		= "KeyInfoId-".$this->signatureID;
	
	$this->Reference0Id		= "Reference-0e79b719-635c-476f-a59e-8ac3ba14365d";
	$this->Reference1Id		= "ReferenceKeyInfo";
	
	$this->SignedProperties	= "SignedProperties-".$this->signatureID;

    $this->tipoDoc = $tipodoc;
	$xml1 = base64_decode($xmlsinfirma);
	$xml1 = $this->insertaFirma($xml1);

	return base64_encode($xml1);
  }
  /**
   * Función que Inserta la firma e
   * @parametros  archivo xml sin firma según UBL de DIAN
   * @retorna el documento firmando
   */
  public function insertaFirma($xml) {
    if (is_null($this->publicKey) || is_null($this->privateKey)) return $xml;
	//canoniza todo el documento  para el digest
	$d = new \DomDocument('1.0','UTF-8');
	$d->loadXML($xml);
	$canonizadoreal=$d->C14N(); 
	// Definir los namespace para los diferentes nodos
    $xmlns_keyinfo;$xmnls_signedprops;$xmnls_signeg;
    if ($this->tipoDoc == '01'){
        $xmlns_keyinfo='xmlns="https://tribunet.hacienda.go.cr/docs/esquemas/2017/v4.2/facturaElectronica" ';
        $xmnls_signedprops='xmlns="https://tribunet.hacienda.go.cr/docs/esquemas/2017/v4.2/facturaElectronica" ';
        $xmnls_signeg='xmlns="https://tribunet.hacienda.go.cr/docs/esquemas/2017/v4.2/facturaElectronica" ';
    } elseif ($this->tipoDoc == '02'){
        $xmlns_keyinfo='xmlns="https://tribunet.hacienda.go.cr/docs/esquemas/2017/v4.2/notaDebitoElectronica" ';
        $xmnls_signedprops='xmlns="https://tribunet.hacienda.go.cr/docs/esquemas/2017/v4.2/notaDebitoElectronica" ';
        $xmnls_signeg='xmlns="https://tribunet.hacienda.go.cr/docs/esquemas/2017/v4.2/notaDebitoElectronica" ';
    } elseif ($this->tipoDoc == '03'){
        $xmlns_keyinfo='xmlns="https://tribunet.hacienda.go.cr/docs/esquemas/2017/v4.2/notaCreditoElectronica" ';
        $xmnls_signedprops='xmlns="https://tribunet.hacienda.go.cr/docs/esquemas/2017/v4.2/notaCreditoElectronica" ';
        $xmnls_signeg='xmlns="https://tribunet.hacienda.go.cr/docs/esquemas/2017/v4.2/notaCreditoElectronica" ';
    } elseif ($this->tipoDoc == '04'){
        $xmlns_keyinfo='xmlns="https://tribunet.hacienda.go.cr/docs/esquemas/2017/v4.2/tiqueteElectronico" ';
        $xmnls_signedprops='xmlns="https://tribunet.hacienda.go.cr/docs/esquemas/2017/v4.2/tiqueteElectronico" ';
        $xmnls_signeg='xmlns="https://tribunet.hacienda.go.cr/docs/esquemas/2017/v4.2/tiqueteElectronico" ';
    } elseif ($this->tipoDoc == '05' || $this->tipoDoc == '06' || $this->tipoDoc == '07'){
        $xmlns_keyinfo='xmlns="https://tribunet.hacienda.go.cr/docs/esquemas/2017/v4.2/mensajeReceptor" ';
        $xmnls_signedprops='xmlns="https://tribunet.hacienda.go.cr/docs/esquemas/2017/v4.2/mensajeReceptor" ';
        $xmnls_signeg='xmlns="https://tribunet.hacienda.go.cr/docs/esquemas/2017/v4.2/mensajeReceptor" ';
    }
    $xmlns= 'xmlns:ds="http://www.w3.org/2000/09/xmldsig#" '.
            'xmlns:fe="http://www.dian.gov.co/contratos/facturaelectronica/v1" ' .
            'xmlns:xades="http://uri.etsi.org/01903/v1.3.2#"';
    $xmlns_keyinfo .= 'xmlns:ds="http://www.w3.org/2000/09/xmldsig#" '.
                      'xmlns:xsd="http://www.w3.org/2001/XMLSchema" '.
                      'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"';
    $xmnls_signedprops .= 'xmlns:ds="http://www.w3.org/2000/09/xmldsig#" '.
                          'xmlns:xades="http://uri.etsi.org/01903/v1.3.2#" '.
                          'xmlns:xsd="http://www.w3.org/2001/XMLSchema" '.
                          'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"';
    $xmnls_signeg .= 'xmlns:ds="http://www.w3.org/2000/09/xmldsig#" '.
                     'xmlns:xsd="http://www.w3.org/2001/XMLSchema" '.
                     'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"';

	//date_default_timezone_set("America/Costa_Rica");
	//$signTime1='2018-01-30T17:16:42-06:00';
	//$signTime1 = date('Y-m-d\TH:i:s.vT:00');
	//$signTime1 = date('Y-m-d\TH:i:s-06:00');
	
	//$dt = new \DateTime();
	//$signTime1 = $dt->format(\DateTime::ATOM);
	$signTime1 = date('c');

    $certData   = openssl_x509_parse($this->publicKey);
    $certDigest =base64_encode(openssl_x509_fingerprint($this->publicKey, "sha256", true));
    
    $certIssuer = array();
    foreach ($certData['issuer'] as $item=>$value) {
      $certIssuer[] = $item . '=' . $value;
    }
	$certIssuer = implode(', ', array_reverse($certIssuer));

    $prop = '<xades:SignedProperties Id="' . $this->SignedProperties .  '">' .
      '<xades:SignedSignatureProperties>'.
		  '<xades:SigningTime>' .  $signTime1 . '</xades:SigningTime>' .     
		  '<xades:SigningCertificate>'.
			  '<xades:Cert>'.
				  '<xades:CertDigest>' .
					  '<ds:DigestMethod Algorithm="http://www.w3.org/2001/04/xmlenc#sha256" />'.
					  '<ds:DigestValue>' . $certDigest . '</ds:DigestValue>'.
				  '</xades:CertDigest>'.
				  '<xades:IssuerSerial>' .
					  '<ds:X509IssuerName>'   . $certIssuer       . '</ds:X509IssuerName>'.
					  '<ds:X509SerialNumber>' . $certData['serialNumber'] . '</ds:X509SerialNumber>' .
				  '</xades:IssuerSerial>'.
			  '</xades:Cert>'.
		  '</xades:SigningCertificate>' .
		  '<xades:SignaturePolicyIdentifier>'.
			  '<xades:SignaturePolicyId>' .
				  '<xades:SigPolicyId>'.
					  '<xades:Identifier>' . $this->signPolicy['url'] .  '</xades:Identifier>'.
					  '<xades:Description />'.
				  '</xades:SigPolicyId>'.
				  '<xades:SigPolicyHash>' .
					  '<ds:DigestMethod Algorithm="http://www.w3.org/2000/09/xmldsig#sha1" />'. 
					  '<ds:DigestValue>' . $this->signPolicy['digest'] . '</ds:DigestValue>'.
				  '</xades:SigPolicyHash>'.
			  '</xades:SignaturePolicyId>' .
		  '</xades:SignaturePolicyIdentifier>'.
	  '</xades:SignedSignatureProperties>'.
	  '<xades:SignedDataObjectProperties>'.
		  '<xades:DataObjectFormat ObjectReference="#'. $this->Reference0Id . '">'.
			  '<xades:MimeType>text/xml</xades:MimeType>'.
			  '<xades:Encoding>UTF-8</xades:Encoding>'.
		  '</xades:DataObjectFormat>'.
	  '</xades:SignedDataObjectProperties>'.
	  '</xades:SignedProperties>';

    // Prepare key info
    $publicPEM = "";
    openssl_x509_export($this->publicKey, $publicPEM);
    $publicPEM = str_replace("-----BEGIN CERTIFICATE-----", "", $publicPEM);
    $publicPEM = str_replace("-----END CERTIFICATE-----", "", $publicPEM);
	$publicPEM = str_replace("\r", "", str_replace("\n", "", $publicPEM));	
 
	$kInfo = '<ds:KeyInfo Id="'.$this->KeyInfoId.'">' . 
				'<ds:X509Data>'  .  
					'<ds:X509Certificate>'  . $publicPEM .'</ds:X509Certificate>' .
				'</ds:X509Data>' .
				'<ds:KeyValue>'.				
				'<ds:RSAKeyValue>'.
					'<ds:Modulus>'.$this->Modulus .'</ds:Modulus>'.
					'<ds:Exponent>'.$this->Exponent .'</ds:Exponent>'.
				'</ds:RSAKeyValue>'.
				'</ds:KeyValue>'.
			 '</ds:KeyInfo>';

	
	$aconop=str_replace('<xades:SignedProperties', '<xades:SignedProperties ' . $xmnls_signedprops, $prop);
	$propDigest=$this->retC14DigestSha1($aconop);

	
	$keyinfo_para_hash1=str_replace('<ds:KeyInfo', '<ds:KeyInfo ' . $xmlns_keyinfo, $kInfo);
	$kInfoDigest=$this->retC14DigestSha1($keyinfo_para_hash1);

    
	
    $documentDigest = base64_encode(hash('sha256' , $canonizadoreal, true ));

    // Prepare signed info
    $sInfo = '<ds:SignedInfo>' . 
	  '<ds:CanonicalizationMethod Algorithm="http://www.w3.org/TR/2001/REC-xml-c14n-20010315" />' . 
	  '<ds:SignatureMethod Algorithm="http://www.w3.org/2001/04/xmldsig-more#rsa-sha256" />' . 
      '<ds:Reference Id="' . $this->Reference0Id . '" URI="">' . 
	  '<ds:Transforms>' . 
	  '<ds:Transform Algorithm="http://www.w3.org/2000/09/xmldsig#enveloped-signature" />' .  
	  '</ds:Transforms>' . 
      '<ds:DigestMethod Algorithm="http://www.w3.org/2001/04/xmlenc#sha256" />' .
	  '<ds:DigestValue>' . $documentDigest . '</ds:DigestValue>' . 
	  '</ds:Reference>' . 
	  '<ds:Reference Id="'.  $this->Reference1Id . '" URI="#'.$this->KeyInfoId .'">' . 
      '<ds:DigestMethod Algorithm="http://www.w3.org/2001/04/xmlenc#sha256" />' .
	  '<ds:DigestValue>' . $kInfoDigest . '</ds:DigestValue>' . 
	  '</ds:Reference>' . 
	  '<ds:Reference Type="http://uri.etsi.org/01903#SignedProperties" URI="#' . $this->SignedProperties . '">' . 
      '<ds:DigestMethod Algorithm="http://www.w3.org/2001/04/xmlenc#sha256" />' . 
	  '<ds:DigestValue>' . $propDigest . '</ds:DigestValue>' . 
	  '</ds:Reference>' . 
	  '</ds:SignedInfo>';
	  
	  /*
	  
	  '<ds:Transform Algorithm="http://www.w3.org/TR/1999/REC-xpath-19991116">' .
	  '<ds:XPath>not(ancestor-or-self::ds:Signature)</ds:XPath>' .
	  '</ds:Transform>' .
	  
	  */


    $signaturePayload = str_replace('<ds:SignedInfo', '<ds:SignedInfo ' . $xmnls_signeg, $sInfo);

	
	$d1p = new \DomDocument('1.0','UTF-8');
	$d1p->loadXML($signaturePayload);
	$signaturePayload=$d1p->C14N();
	
    $signatureResult = "";
    $algo = "SHA256";


	openssl_sign($signaturePayload, $signatureResult, $this->privateKey,$algo);
	
	$signatureResult = base64_encode($signatureResult);

    $sig = '<ds:Signature xmlns:ds="http://www.w3.org/2000/09/xmldsig#" Id="' . $this->signatureID . '">'. 
	   $sInfo . 
      '<ds:SignatureValue Id="' . $this->signatureValue . '">' . 
      $signatureResult .  '</ds:SignatureValue>'  . $kInfo . 
      '<ds:Object Id="'.$this->XadesObjectId .'">'.
	  '<xades:QualifyingProperties xmlns:xades="http://uri.etsi.org/01903/v1.3.2#" Id="QualifyingProperties-012b8df6-b93e-4867-9901-83447ffce4bf" Target="#' . $this->signatureID . '">' . $prop .
      '</xades:QualifyingProperties></ds:Object></ds:Signature>';

	$buscar = '';
	$remplazar = '';
    if ($this->tipoDoc == '01'){
        $buscar = '</FacturaElectronica>';
        $remplazar = $sig."</FacturaElectronica>";
    } elseif ($this->tipoDoc == '02'){
        $buscar = '</NotaDebitoElectronica>';
        $remplazar = $sig."</NotaDebitoElectronica>";
    } elseif ($this->tipoDoc == '03'){
        $buscar = '</NotaCreditoElectronica>';
        $remplazar = $sig."</NotaCreditoElectronica>";
    } elseif ($this->tipoDoc == '04'){
    	$buscar = '</TiqueteElectronico>';
        $remplazar = $sig."</TiqueteElectronico>";
    } elseif ($this->tipoDoc == '05' || $this->tipoDoc == '06' || $this->tipoDoc == '07'){
    	$buscar = '</MensajeReceptor>';
        $remplazar = $sig."</MensajeReceptor>";
    }
  	$pos = strrpos($xml, $buscar);
    if($pos !== false){
        $xml = substr_replace($xml, $remplazar, $pos, strlen($buscar));
    }

    /*
	$documento_xml = new \DomDocument('1.0','UTF-8');
	$documento_xml->loadXML($xml);
	$documento_xml->formatOutput = true;
	$documento_xml->saveXML();
	$nombre = 'archivo'.time();
	$path = Yii::getAlias('@backend/web/xmls/'.$nombre.'.xml');
	$documento_xml->save($path);	
	*/
	
    return $xml;
  }
  
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
				if (!isset($data['message'])) {
					$mensaje = 'Ha ocurrido un error. '.$data['error_description'];	
					$type = 'danger';
					$titulo = "Informaci&oacute;n <hr class=\"kv-alert-separator\">";						
					return \Yii::$app->response->data  =  ['error'=>$error, 'mensaje' => $mensaje, 'type'=>$type, 'titulo'=>$titulo];
				}
				else
				{
					$mensaje = 'Ha ocurrido un error: '.$data['message'];	
					$type = 'danger';
					$titulo = "Informaci&oacute;n <hr class=\"kv-alert-separator\">";						
					return \Yii::$app->response->data  =  ['error'=>$error, 'mensaje' => $mensaje, 'type'=>$type, 'titulo'=>$titulo];
				}				
			} catch (InvalidParamException $e) {
				$mensaje = 'Ha ocurrido un error';	
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
  
	public function EnviarFacturaHacienda($factura)
    {
		//$inXmlUrl debe de ser en Base64 
		//$p12Url es un downloadcode previamente suministrado al subir el certificado en el modulo 
		  //fileUploader -> subir_certif
		//Tipo es el tipo de documento 
		//01 FE
		//02 ND
		//03 NC
		//04 TE
		//05 06 07 Mensaje Receptor
		$emisor = Configuracion::findOne(1);
		$factura_detalles = $factura->facturasDetalles;
		$p12Url = $emisor->getFilePath(); 
		$pinP12 = $emisor->pin_certificado;   //'1972';				
		$inXml = $this->getXml($factura, $factura_detalles, $emisor);
		$tipoDocumento = '01'; // Factura
		$returnFile = $this->firmar($p12Url, $pinP12, $inXml, $tipoDocumento);
		$error = 0;
		
		$data = $this->sendFactura($returnFile, $this->token, $factura, $emisor);
		if ($data['error'] == 1) // Ocurrio un Error
		{
			$mensaje = $data['mensaje'];
			$type = $data['type'];
			$titulo = $data['titulo']; 
			//return \Yii::$app->response->data = ['error'=>1, 'mensaje' => $mensaje, 'type'=>$type, 'titulo'=>$titulo];
			return ['error'=>1, 'mensaje' => $mensaje, 'type'=>$type, 'titulo'=>$titulo];			
		}
		
		$respuesta = $data['response'];
		
		$code = $respuesta->getHeaders()->get('http-code');
		if ($code == '202' || $code == '201')
		{
			$mensaje = "La factura electrónica con clave: [".$factura->clave."] se recibió correctamente, queda pendiente la validación de esta y el 
						envío de la respuesta de parte de Hacienda.";
			$factura->estado_id = Estados::STATUS_RECIBIDO; // Recibido
			$factura->save();
			$type = 'success';
			$titulo = "Informaci&oacute;n <hr class=\"kv-alert-separator\">";					
		}
		else
		if ($code == '400'){
			$error = 1;
			$mensaje = $respuesta->getHeaders()->get('X-Error-Cause');
			$type = 'danger';
			$titulo = "Error <hr class=\"kv-alert-separator\">";	
		}
		else
		{
			$error = 1;
			$mensaje = "Ha ocurrido un error desconocido al enviar la factura electrónica con clave: [".$factura->clave."]. Póngase en contacto con el administrador del sistema";	
			$type = 'danger';
			$titulo = "Error <hr class=\"kv-alert-separator\">";	
		}
		$this->CloseSesion($this->token, $emisor);
		return ['error'=>$error, 'mensaje' => $mensaje, 'type'=>$type, 'titulo'=>$titulo];
    }
	
	function sendFactura($comprobanteXML, $token, $factura, $emisor) {
		if ($emisor->activar_produccion){
			$url_api = 	'https://api.comprobanteselectronicos.go.cr/recepcion/v1/recepcion/';
		}
		else{
			$url_api = 	'https://api.comprobanteselectronicos.go.cr/recepcion-sandbox/v1/recepcion/';
		}		
				
		$client = new Client([
						'requestConfig' => [
							'format' => Client::FORMAT_JSON
						],
						'responseConfig' => [
							'format' => Client::FORMAT_JSON
						],
					]);
		// Fecha en // ISO 8601 (http://php.net/manual/en/function.date.php
		$fecha = date('c');
		
		$CallBackUrl = 'http://facturaelectronica.softwaresolutions.co.cr/api/web/callback';		
		
		$autorization = 'bearer ' . $token;	

		$error = 0;
		$mensaje = '';	
		$type = '';
		$titulo = '';						
		$response =  NULL;
		try 
		{
			$response = $client->createRequest()
							   ->setMethod('POST')
							   ->setFormat(Client::FORMAT_JSON)
							   ->setUrl($url_api)	
							   ->setHeaders(['Authorization' => $autorization])		
							   ->setData(['clave' => $factura->clave,
										  'fecha' => $fecha,
										  'emisor' => [
												'tipoIdentificacion' => $emisor->tipoIdentificacion->codigo,
												'numeroIdentificacion' => $emisor->identificacion					  
										  ],
										  'receptor' => [
												'tipoIdentificacion' => $factura->cliente->tipoIdentificacion->codigo,
												'numeroIdentificacion' => $factura->cliente->identificacion
										  ],
										  'callbackUrl' => $CallBackUrl,
										  'comprobanteXml' => $comprobanteXML
									  ])		
							   ->send();							   
		} 
		catch (InvalidParamException $e){
			$error = 1;
			$mensaje = 'Ha ocurrido un error al tratar de enviar la factura a la api de hacienda. Inténtento nuevamente 
						y si el error persiste póngase en contacto con el administrador del sistema';	
			$type = 'danger';
			$titulo = "Error <hr class=\"kv-alert-separator\">";						
			return ['error'=>$error, 'mensaje' => $mensaje, 'type'=>$type, 'titulo'=>$titulo, 'response'=>$response];				
		}					
		return ['error'=>$error, 'mensaje' => $mensaje, 'type'=>$type, 'titulo'=>$titulo, 'response'=>$response];				
	}	
		
	function getEstado($factura) {
		$emisor = Configuracion::findOne(1);		
		if ($emisor->activar_produccion){
			$url_api = 	'https://api.comprobanteselectronicos.go.cr/recepcion/v1/recepcion/';
		}
		else
		{
			$url_api = 	'https://api.comprobanteselectronicos.go.cr/recepcion-sandbox/v1/recepcion/';
		}		
		$client = new Client([
						'requestConfig' => [
							'format' => Client::FORMAT_JSON
						],
						'responseConfig' => [
							'format' => Client::FORMAT_JSON
						],
					]);
		$autorization = 'bearer ' . $this->token;		
		
		$response = $client->createRequest()
			->setMethod('POST')
			->setFormat(Client::FORMAT_JSON)
			->setUrl($url_api.'/'.$factura->clave)	
			->setHeaders(['Authorization' => $autorization])		
			->send();

		$actualizar = 0;
		$factura_id = 0;
		$estado = '';
		
		/*		
		// Probar este código CACERES		
		$code = $respuesta->getHeaders()->get('http-code');
		switch ($code) {
			case 200:
			  // Acá se debe procesar la respuesta para determinar si el atributo "ind-estado"
			  // del JSON. de respuesta da por aceptado o rechazado el documento. Si no está
			  // en ese estado se debe reintentar posteriormente.
			  break;
			case 404:
			  // Se presenta si no se localiza la clave brindada
			  LOG.log(Level.SEVERE, "La clave no esta registrada");
			  break;
		}	
		*/		
		
		
		// Si llega aqui no hubo error
		$data = Json::decode($response->content);
		if (is_array($data))
		{
			if ($data['ind-estado'] == 'rechazado')
			{
				$factura->estado_id = Estados::STATUS_RECHAZADO; // Rechazada
				$type = 'danger';
				$titulo = "Error <hr class=\"kv-alert-separator\">";
				$actualizar = 1;	
				$to = [
					'caceresvega@gmail.com' => 'Juan Alberto Cáceres Vega',
					'hromancr@gmail.com' => 'Henry Ricardo Román Solis',
					'hroman@softwaresolutions.co.cr' => 'Henry Ricardo Román Solis',					
				];				
				//$to = 'caceresvega@gmail.com';
				$from = 'portal@gmail.com';				
				$asunto = 'Portal Error en factura: '.$factura->clave;
				$xml_respuesta_hacienda = base64_decode($data['respuesta-xml']);
				$mensaje = "El comprobante electrónico con clave: [".$factura->clave."] fue rechazado por Hacienda. <br >Por la siguiente causa ". $xml_respuesta_hacienda.'<br >Revise el archivo xml de respuesta de Hacienda para más detalles';

				// Crear el xml de respuesta
				$nombre_archivo = 'FE-MH-'.$factura->clave.'.xml';
				$path = Yii::getAlias('@backend/web/xmlh/'.$nombre_archivo);
				file_put_contents($path, $xml_respuesta_hacienda);
				$factura->respuesta_xml = $path;
				$factura->save();

				//$cuerpo = '<pre>'.base64_decode($xml).'</pre>';
				$mensage = Yii::$app->mailer->compose("layouts/html", ['content'=>$xml_respuesta_hacienda])
					->setTo($to)
					->setFrom($from)
					//->setCc($arr_cc)
					->setSubject($asunto)
					->setHtmlBody($xml_respuesta_hacienda);
				$mensage->send();	
			}
			else
			if ($data['ind-estado'] == 'aceptado'){
				$mensaje = "La factura electrónica con clave: [".$factura->clave."] fue aceptada por Hacienda. "."<br >Revise el archivo xml de respuesta de Hacienda para más detalles";
				$factura->estado_id = Estados::STATUS_ACEPTADO; // Aceptada
				$type = 'success';
				$titulo = "Informaci&oacute;n <hr class=\"kv-alert-separator\">";	
				$actualizar = 1;	
				
				$xml_respuesta_hacienda = base64_decode($data['respuesta-xml']);
				// Crear el xml de respuesta
				$nombre_archivo = 'FE-MH-'.$factura->clave.'.xml';
				$path = Yii::getAlias('@backend/web/xmlh/'.$nombre_archivo);
				file_put_contents($path, $xml_respuesta_hacienda);
				$factura->respuesta_xml = $nombre_archivo;				
				$factura->save();
			}
			else
			if ($data['ind-estado'] == 'recibido'){
				$mensaje = "La factura electrónica con clave: [".$factura->clave."] aún se encuentra en estado Recibida.";
				$factura->estado_id = Estados::STATUS_RECIBIDO;; // Recibida
				$factura->save();
				$type = 'warning';
				$titulo = "Advertencia <hr class=\"kv-alert-separator\">";	
				$actualizar = 0;				
			}			
			else
			if ($data['ind-estado'] == 'error'){
				$mensaje = "Error";
				$type = 'warning';
				$titulo = "Advertencia <hr class=\"kv-alert-separator\">";	
				$actualizar = 0;				
			}			
			else
			{
				$mensaje = "Ha ocurrido un error desconocido al consultar el estado de la factura electrónica con clave: [".$factura->clave."]. Póngase en contacto con el administrador del sistema";	
				$type = 'warning';
				$titulo = "Informaci&oacute;n <hr class=\"kv-alert-separator\">";	
			}			
		}
		else
		{
			$mensaje = $response->content;	
			$type = 'danger';
			$titulo = "Error <hr class=\"kv-alert-separator\">";	
		}

		return ['mensaje' => $mensaje, 'type'=>$type, 'titulo'=>$titulo, 'actualizar'=>$actualizar];			
	}	
	
	public function ExportXml($factura)
	{
		//$inXmlUrl debe de ser en Base64 
		//$p12Url es un downloadcode previamente suministrado al subir el certificado en el modulo 
		  //fileUploader -> subir_certif
		//Tipo es el tipo de documento 
		//01 FE
		//02 ND
		//03 NC
		//04 TE
		//05 06 07 Mensaje Receptor
		$emisor = Configuracion::findOne(1);
		$factura_detalles = $factura->facturasDetalles;
		$p12Url = $emisor->getFilePath(); 

		$pinP12 = $emisor->pin_certificado;   //'1972';				
		$inXml = $this->getXml($factura, $factura_detalles, $emisor);
		$tipoDocumento = '01'; // Factura
		$returnFile = $this->firmar($p12Url, $pinP12, $inXml, $tipoDocumento);
		$xml = base64_decode($returnFile);
		return $xml;		
	}
		
	public function getXml($factura, $factura_detalles, $emisor)
	{
		// Fecha en // ISO 8601 (http://php.net/manual/en/function.date.php
		$fecha = date('c');		
		$plazo_credito = $factura->condicionVenta->codigo == '02' ? $factura->plazo_credito: '0';
		
		$doc  = new \DomDocument('1.0','UTF-8');
		$doc->formatOutput = true;		                               
		$root = $doc->createElementNS('https://tribunet.hacienda.go.cr/docs/esquemas/2017/v4.2/facturaElectronica', 'FacturaElectronica');
		$doc->appendChild($root);
		$root->setAttributeNS('http://www.w3.org/2000/xmlns/' ,'xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
		$root->setAttributeNS('http://www.w3.org/2000/xmlns/' ,'xmlns:xsd', 'http://www.w3.org/2001/XMLSchema');
		$root->setAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'xsi:schemaLocation', 'https://tribunet.hacienda.go.cr/docs/esquemas/2017/v4.2/facturaElectronica'.' '.'https://tribunet.hacienda.go.cr/docs/esquemas/2017/v4.2/facturaElectronica.xsd');		
		
		$nodo = $doc->createElement('Clave', $factura->clave);
		$root->appendChild($nodo);
		
		$nodo = $doc->createElement('NumeroConsecutivo', $factura->consecutivo);
		$root->appendChild($nodo);
		
		$nodo = $doc->createElement('FechaEmision', $fecha);
		$root->appendChild($nodo);
		
		// Datos del Emisor
		$nodoemisor = $doc->createElement('Emisor');
		$root->appendChild($nodoemisor);
		
		$nodo = $doc->createElement('Nombre', $emisor->nombre);
		$nodoemisor->appendChild($nodo);
		
		$identificacion = $doc->createElement('Identificacion');
		$nodoemisor->appendChild($identificacion);
		
		$nodo = $doc->createElement('Tipo', trim($emisor->tipoIdentificacion->codigo));
		$identificacion->appendChild($nodo);
		
		$nodo = $doc->createElement('Numero', trim($emisor->identificacion));
		$identificacion->appendChild($nodo);
		
		if (!is_null($emisor->nombre_comercial) && !empty($emisor->nombre_comercial))
		{
			$nodo = $doc->createElement('NombreComercial', $emisor->nombre_comercial);
			$nodoemisor->appendChild($nodo);
		}

		$ubicacion = $doc->createElement('Ubicacion');
		$nodoemisor->appendChild($ubicacion);
		
		$nodo = $doc->createElement('Provincia', $emisor->provincia->codigo);
		$ubicacion->appendChild($nodo);
		
		$nodo = $doc->createElement('Canton', str_pad($emisor->canton->codigo, 2, '0', STR_PAD_LEFT));
		$ubicacion->appendChild($nodo);
		
		$nodo = $doc->createElement('Distrito', str_pad($emisor->distrito->codigo, 2, '0', STR_PAD_LEFT));
		$ubicacion->appendChild($nodo);
		
		if (!is_null($emisor->otras_senas) && !empty($emisor->otras_senas)){
			$nodo = $doc->createElement('OtrasSenas', $emisor->otras_senas);
			$ubicacion->appendChild($nodo);
		}
		
		if (!is_null($emisor->codigo_telefono) && !empty($emisor->codigo_telefono) && !is_null($emisor->telefono) && !empty($emisor->telefono)){	
			$telefono = $doc->createElement('Telefono');
			$nodoemisor->appendChild($telefono);
		
			$nodo = $doc->createElement('CodigoPais', $emisor->codigo_telefono);
			$telefono->appendChild($nodo);
			
			$nodo = $doc->createElement('NumTelefono', $emisor->telefono);
			$telefono->appendChild($nodo);
		}
		
		if (!is_null($emisor->codigo_fax) && !empty($emisor->codigo_fax) && !is_null($emisor->fax) && !empty($emisor->fax)){
			$fax = $doc->createElement('Fax');
			$nodoemisor->appendChild($fax);
			
			$nodo = $doc->createElement('CodigoPais', $emisor->codigo_fax);
			$fax->appendChild($nodo);
			
			$nodo = $doc->createElement('NumTelefono', $emisor->fax);
			$fax->appendChild($nodo);
		}
		
		$nodo = $doc->createElement('CorreoElectronico', $emisor->email);
		$nodoemisor->appendChild($nodo);
		
		
		// Datos Receptor
		$receptor = $doc->createElement('Receptor');
		$root->appendChild($receptor);
		
		$nodo = $doc->createElement('Nombre', $factura->cliente->nombre);
		$receptor->appendChild($nodo);
		
		$identificacion = $doc->createElement('Identificacion');
		$receptor->appendChild($identificacion);
		
		$nodo = $doc->createElement('Tipo', trim($factura->cliente->tipoIdentificacion->codigo));
		$identificacion->appendChild($nodo);
		
		$nodo = $doc->createElement('Numero', trim($factura->cliente->identificacion));
		$identificacion->appendChild($nodo);
		
		if (!is_null($factura->cliente->nombre_comercial) && !empty($factura->cliente->nombre_comercial))
		{
			$nodo = $doc->createElement('NombreComercial', $factura->cliente->nombre_comercial);
			$receptor->appendChild($nodo);
		}		
		
		$ubicacion = $doc->createElement('Ubicacion');
		$receptor->appendChild($ubicacion);
		
		$nodo = $doc->createElement('Provincia', $factura->cliente->provincia->codigo);
		$ubicacion->appendChild($nodo);
		
		$nodo = $doc->createElement('Canton', str_pad($factura->cliente->canton->codigo, 2, '0', STR_PAD_LEFT));
		$ubicacion->appendChild($nodo);
		
		$nodo = $doc->createElement('Distrito', str_pad($factura->cliente->distrito->codigo, 2, '0', STR_PAD_LEFT));
		$ubicacion->appendChild($nodo);
		
		if (!is_null($factura->cliente->otras_senas) && !empty($factura->cliente->otras_senas)){		
			$nodo = $doc->createElement('OtrasSenas', $factura->cliente->otras_senas);
			$ubicacion->appendChild($nodo);		
		}
		
		if (!is_null($factura->cliente->codigo_telefono) && !empty($factura->cliente->codigo_telefono) && !is_null($factura->cliente->telefono) && !empty($factura->cliente->telefono)){		
			$telefono = $doc->createElement('Telefono');
			$receptor->appendChild($telefono);
		
			$nodo = $doc->createElement('CodigoPais', $factura->cliente->codigo_telefono);
			$telefono->appendChild($nodo);
		
			$nodo = $doc->createElement('NumTelefono', $factura->cliente->telefono);
			$telefono->appendChild($nodo);
		}
		
		if (!is_null($factura->cliente->codigo_fax) && !empty($factura->cliente->codigo_fax) && !is_null($factura->cliente->fax) && !empty($factura->cliente->fax)){		
			$fax = $doc->createElement('Fax');
			$receptor->appendChild($fax);
		
			$nodo = $doc->createElement('CodigoPais', $factura->cliente->codigo_fax);
			$fax->appendChild($nodo);
		
			$nodo = $doc->createElement('NumTelefono', $factura->cliente->fax);
			$fax->appendChild($nodo);
		}
		
		$nodo = $doc->createElement('CorreoElectronico', $factura->cliente->email);
		$receptor->appendChild($nodo);

		// Otros elementos
		$nodo = $doc->createElement('CondicionVenta', $factura->condicionVenta->codigo);
		$root->appendChild($nodo);
		
		if ($factura->condicion_venta_id == 2) // Crédito		
		{
			$nodo = $doc->createElement('PlazoCredito', $factura->plazo_credito);
			$root->appendChild($nodo);
		}
		
	    $datos = $factura->facturasMediosPagos;
		$i = 1;
	    foreach ($datos as $mp){
			if ($i <= 4){
				$nodo = $doc->createElement('MedioPago', $mp->medioPago->codigo);
				$root->appendChild($nodo);
			}
			$i++;
		}						

		// Datos Del servicio
		$detalle = $doc->createElement('DetalleServicio');
		$root->appendChild($detalle);
		
		$i = 1;
		foreach ($factura_detalles as $fdetalle)
		{		
			$linea = $doc->createElement('LineaDetalle');
			$detalle->appendChild($linea);
		
			$nodo = $doc->createElement('NumeroLinea', $i);
			$linea->appendChild($nodo);
			
			$codigo = $doc->createElement('Codigo');
			$linea->appendChild($codigo);
			
			$nodo = $doc->createElement('Tipo', '01');
			$codigo->appendChild($nodo);
			
			$nodo = $doc->createElement('Codigo', $fdetalle->codigo);
			$codigo->appendChild($nodo);
			
			$nodo = $doc->createElement('Cantidad', number_format($fdetalle->cantidad, 3, '.', ''));
			$linea->appendChild($nodo);
			
			$nodo = $doc->createElement('UnidadMedida', $fdetalle->unidadMedida->codigo);
			$linea->appendChild($nodo);
			
			$nodo = $doc->createElement('Detalle', $fdetalle->descripcion);
			$linea->appendChild($nodo);
			
			$nodo = $doc->createElement('PrecioUnitario', number_format($fdetalle->precio, 5, '.', ''));
			$linea->appendChild($nodo);
			
			$nodo = $doc->createElement('MontoTotal', number_format($fdetalle->getMonto(), 5, '.', ''));
			$linea->appendChild($nodo);
			
			if (!is_null($fdetalle->monto_descuento) && $fdetalle->monto_descuento > 0 && !empty($fdetalle->naturaleza_descuento) && !is_null($fdetalle->naturaleza_descuento)){			
				$nodo = $doc->createElement('MontoDescuento', number_format($fdetalle->getDescuento(), 5, '.', ''));
				$linea->appendChild($nodo);
			
				$nodo = $doc->createElement('NaturalezaDescuento', $fdetalle->naturaleza_descuento);
				$linea->appendChild($nodo);
			}
			
			$nodo = $doc->createElement('SubTotal', number_format($fdetalle->getSubtotal(), 5, '.', ''));
			$linea->appendChild($nodo);
			
			if (($fdetalle->aplicar_impuesto && !is_null($fdetalle->impuesto_id)) || ($fdetalle->exonerado == 1 && !is_null($fdetalle->tipo_documento_exoneracion_id)))
			{			
				$impuesto = $doc->createElement('Impuesto');
				$linea->appendChild($impuesto);
				
				if ($fdetalle->aplicar_impuesto && !is_null($fdetalle->impuesto_id))
				{				
					$nodo = $doc->createElement('Codigo', $fdetalle->impuesto->codigo);
					$impuesto->appendChild($nodo);
					
					$nodo = $doc->createElement('Tarifa', number_format($fdetalle->impuesto_tarifa, 2, '.', ''));
					$impuesto->appendChild($nodo);
					
					$nodo = $doc->createElement('Monto', number_format($fdetalle->getMontoImpuesto(), 5, '.', ''));
					$impuesto->appendChild($nodo);			
				}

				if ($fdetalle->exonerado == 1 && !is_null($fdetalle->tipo_documento_exoneracion_id)){										
					$exoneracion = $doc->createElement('Exoneracion');													
					$impuesto->appendChild($exoneracion);
					
					$nodo = $doc->createElement('TipoDocumento', $fdetalle->tipoDocumentoExoneracion->codigo);
					$exoneracion->appendChild($nodo);
					
					$nodo = $doc->createElement('NumeroDocumento', $fdetalle->num_documento_exoneracion);

					$exoneracion->appendChild($nodo);
					
					$nodo = $doc->createElement('NombreInstitucion', $fdetalle->nombre_institucion_emite_exoneracion);
					$exoneracion->appendChild($nodo);
					
					$fecha_exonerado = date('c', strtotime($fdetalle->fecha_emision_exoneracion));
					$nodo = $doc->createElement('FechaEmision', $fecha_exonerado);
					$exoneracion->appendChild($nodo);
					
					$nodo = $doc->createElement('MontoImpuesto', number_format($fdetalle->getServExento() + $fdetalle->getMercanciaExenta(), 5, '.', ''));
					$exoneracion->appendChild($nodo);
					
					$nodo = $doc->createElement('PorcentajeCompra', $fdetalle->porcentaje_compra_exoneracion);
					$exoneracion->appendChild($nodo);
				}				
			}

			$nodo = $doc->createElement('MontoTotalLinea', number_format($fdetalle->getMontoTotalLinea(), 5, '.', ''));
			$linea->appendChild($nodo);
			
			$i++;
		}

		// Resumen de la factura
		$resumen = $doc->createElement('ResumenFactura');
		$root->appendChild($resumen);
		
		$nodo = $doc->createElement('CodigoMoneda', $factura->moneda->codigo);
		$resumen->appendChild($nodo);
		
		$nodo = $doc->createElement('TipoCambio', number_format($factura->tipo_cambio, 5, '.', ''));
		$resumen->appendChild($nodo);
		
		$nodo = $doc->createElement('TotalServGravados', number_format($factura->totalServGravados, 5, '.', ''));
		$resumen->appendChild($nodo);
		
		$nodo = $doc->createElement('TotalServExentos', number_format($factura->totalServExentos, 5, '.', ''));
		$resumen->appendChild($nodo);
		
		$nodo = $doc->createElement('TotalMercanciasGravadas', number_format($factura->totalMercanciasGravadas, 5, '.', ''));
		$resumen->appendChild($nodo);
		
		$nodo = $doc->createElement('TotalMercanciasExentas', number_format($factura->totalMercanciasExentas, 5, '.', ''));
		$resumen->appendChild($nodo);
		
		$nodo = $doc->createElement('TotalGravado', number_format($factura->totalGravado, 5, '.', ''));
		$resumen->appendChild($nodo);
		
		$nodo = $doc->createElement('TotalExento', number_format($factura->totalExento, 5, '.', ''));
		$resumen->appendChild($nodo);
		
		$nodo = $doc->createElement('TotalVenta', number_format($factura->totalVenta, 5, '.', ''));
		$resumen->appendChild($nodo);
		
		$nodo = $doc->createElement('TotalDescuentos', number_format($factura->totalDescuentos, 5, '.', ''));
		$resumen->appendChild($nodo);
		
		$nodo = $doc->createElement('TotalVentaNeta', number_format($factura->totalVentaNeta, 5, '.', ''));
		$resumen->appendChild($nodo);
		
		$nodo = $doc->createElement('TotalImpuesto', number_format($factura->totalImpuesto, 5, '.', ''));
		$resumen->appendChild($nodo);
		
		$nodo = $doc->createElement('TotalComprobante', number_format($factura->totalComprobante, 5, '.', ''));
		$resumen->appendChild($nodo);
		
		// Aqui se coloca la información de referencia en caso de emitir una factura de contingencia
		if ($factura->contingencia == 1 && !empty($factura->referencia_numero))
		{
			$referencia = $doc->createElement('InformacionReferencia');
			$root->appendChild($referencia);
		
			$nodo = $doc->createElement('TipoDoc', '01');
			$referencia->appendChild($nodo);

			$nodo = $doc->createElement('Numero', $factura->referencia_numero);
			$referencia->appendChild($nodo);
			
			$nodo = $doc->createElement('FechaEmision', $factura->referencia_fecha_emision);
			$referencia->appendChild($nodo);
			
			$nodo = $doc->createElement('Codigo', $factura->referencia_codigo);
			$referencia->appendChild($nodo);
			
			$nodo = $doc->createElement('Razon', $factura->referencia_razon);
			$referencia->appendChild($nodo);
		}
		

		// Normativa
		$normativa = $doc->createElement('Normativa');
		$root->appendChild($normativa);
		
		$nodo = $doc->createElement('NumeroResolucion', 'DGT-R-48-2016');
		$normativa->appendChild($nodo);
		
		$nodo = $doc->createElement('FechaResolucion', '20-02-2017 13:22:22');
		$normativa->appendChild($nodo);

		$xml = $doc->saveXML();

		return base64_encode($xml);				
	}
	
	//************************************************************************************************************************
	//*******************************************FUNCIONES PARA NOTAS DE CREDITO**********************************************
	//************************************************************************************************************************
	
	public function EnviarNCHacienda($nota)
    {
		//$inXmlUrl debe de ser en Base64 
		//$p12Url es un downloadcode previamente suministrado al subir el certificado en el modulo 
		  //fileUploader -> subir_certif
		//Tipo es el tipo de documento 
		//01 FE
		//02 ND
		//03 NC
		//04 TE
		//05 06 07 Mensaje Receptor
		$emisor = Configuracion::findOne(1);
		$nota_detalles = $nota->notasCreditoDetalles;
		$p12Url = $emisor->getFilePath(); 
		$pinP12 = $emisor->pin_certificado;   //'1972';				
		$inXml = $this->getXmlNC($nota, $nota_detalles, $emisor);
		$tipoDocumento = '03'; // NC
		$returnFile = $this->firmar($p12Url, $pinP12, $inXml, $tipoDocumento);
		
		$data = $this->sendNC($returnFile, $this->token, $nota, $emisor);
		if ($data['error'] == 1) // Ocurrio un Error
		{
			$mensaje = $data['mensaje'];
			$type = $data['type'];
			$titulo = $data['titulo']; 
			return \Yii::$app->response->data  =  ['mensaje' => $mensaje, 'type'=>$type, 'titulo'=>$titulo];
		}
		
		$respuesta = $data['response'];
		
		$code = $respuesta->getHeaders()->get('http-code');
		if ($code == '202' || $code == '201')
		{
			$mensaje = "La Nota de Crédito electrónica con clave: [".$nota->clave."] se recibió correctamente, queda pendiente la validación de esta y el 
						envío de la respuesta de parte de Hacienda.";
			$nota->estado_id = Estados::STATUS_RECIBIDO; // Recibido
			$nota->save();
			$type = 'success';
			$titulo = "Informaci&oacute;n <hr class=\"kv-alert-separator\">";					
		}
		else
		if ($code == '400'){
			$mensaje = $respuesta->getHeaders()->get('X-Error-Cause');
			$type = 'danger';
			$titulo = "Error <hr class=\"kv-alert-separator\">";	
		}
		else
		{
			$mensaje = "Ha ocurrido un error desconocido al enviar la Nota de Crédito electrónica con clave: [".$nota->clave."]. Póngase en contacto con el administrador del sistema";	
			$type = 'danger';
			$titulo = "Error <hr class=\"kv-alert-separator\">";	
		}
		$this->CloseSesion($this->token, $emisor);
		return ['mensaje' => $mensaje, 'type'=>$type, 'titulo'=>$titulo];
    }
	
	function sendNC($comprobanteXML, $token, $nota, $emisor) {
		if ($emisor->activar_produccion){
			$url_api = 	'https://api.comprobanteselectronicos.go.cr/recepcion/v1/recepcion/';
		}
		else{
			$url_api = 	'https://api.comprobanteselectronicos.go.cr/recepcion-sandbox/v1/recepcion/';
		}		
				
		$client = new Client([
						'requestConfig' => [
							'format' => Client::FORMAT_JSON
						],
						'responseConfig' => [
							'format' => Client::FORMAT_JSON
						],
					]);
		// Fecha en // ISO 8601 (http://php.net/manual/en/function.date.php
		$fecha = date('c');
		
		$CallBackUrl = Url::base('http');
		$CallBackUrl = $CallBackUrl.'/haciendacallback/'.$emisor->id;		
		
		$autorization = 'bearer ' . $token;	

		$error = 0;
		$mensaje = '';	
		$type = '';
		$titulo = '';						
		$response =  NULL;
		try 
		{
			$response = $client->createRequest()
							   ->setMethod('POST')
							   ->setFormat(Client::FORMAT_JSON)
							   ->setUrl($url_api)	
							   ->setHeaders(['Authorization' => $autorization])		
							   ->setData(['clave' => $nota->clave,
										  'fecha' => $fecha,
										  'emisor' => [
												'tipoIdentificacion' => $emisor->tipoIdentificacion->codigo,
												'numeroIdentificacion' => $emisor->identificacion					  
										  ],
										  'receptor' => [
												'tipoIdentificacion' => $nota->cliente->tipoIdentificacion->codigo,
												'numeroIdentificacion' => $nota->cliente->identificacion
										  ],
										  'callbackUrl' => $CallBackUrl,
										  'comprobanteXml' => $comprobanteXML
									  ])		
							   ->send();							   
		} 
		catch (InvalidParamException $e){
			$error = 1;
			$mensaje = 'Ha ocurrido un error al tratar de enviar la Nota de Crédito a la api de hacienda. Inténtento nuevamente 
						y si el error persiste póngase en contacto con el administrador del sistema';	
			$type = 'danger';
			$titulo = "Error <hr class=\"kv-alert-separator\">";						
			return ['error'=>$error, 'mensaje' => $mensaje, 'type'=>$type, 'titulo'=>$titulo, 'response'=>$response];				
		}					
		return ['error'=>$error, 'mensaje' => $mensaje, 'type'=>$type, 'titulo'=>$titulo, 'response'=>$response];				
	}	
	
	function getEstadoNC($nota) {
		$emisor = Configuracion::findOne(1);		
		if ($emisor->activar_produccion){
			$url_api = 	'https://api.comprobanteselectronicos.go.cr/recepcion/v1/recepcion/';
		}
		else
		{
			$url_api = 	'https://api.comprobanteselectronicos.go.cr/recepcion-sandbox/v1/recepcion/';
		}		
		$client = new Client([
						'requestConfig' => [
							'format' => Client::FORMAT_JSON
						],
						'responseConfig' => [
							'format' => Client::FORMAT_JSON
						],
					]);
		$autorization = 'bearer ' . $this->token;		
		
		try 
		{
			$response = $client->createRequest()
				->setMethod('POST')
				->setFormat(Client::FORMAT_JSON)
				->setUrl($url_api.'/'.$nota->clave)	
				->setHeaders(['Authorization' => $autorization])		
				->send();

		} catch (InvalidParamException $e) {
			$mensaje = 'Ha ocurrido un error desconocido al consultar el estado de la Nota de Crédito. Póngase en contacto con el administrador del sistema';	
			$type = 'danger';
			$titulo = "Informaci&oacute;n <hr class=\"kv-alert-separator\">";						
			return ['mensaje' => $mensaje, 'type'=>$type, 'titulo'=>$titulo];				
		}

		$actualizar = 0;
		$nota_credito_id = 0;
		$estado = '';
		

		// Si llega aqui no hubo error
		$data = Json::decode($response->content);
		if (is_array($data))
		{
			if ($data['ind-estado'] == 'rechazado')
			{
				$nota->estado_id = Estados::STATUS_RECHAZADO; // Rechazada
				$nota->save();
				$type = 'danger';
				$titulo = "Error <hr class=\"kv-alert-separator\">";
				$actualizar = 1;	
				$nota_credito_id = $nota->id;
				$estado = '<small class="label label-danger"> RECHAZADA</small>';				
				$mensaje = "El comprobante electrónico con clave: [".$nota->clave."] fue rechazado por Hacienda.";

				$xml_respuesta_hacienda = base64_decode($data['respuesta-xml']);

				// Crear el xml de respuesta
				$nombre_archivo = 'NC-MH-'.$nota->clave.'.xml';
				$path = Yii::getAlias('@backend/web/xmlh/'.$nombre_archivo);
				file_put_contents($path, $xml_respuesta_hacienda);

				/*
				//$cuerpo = '<pre>'.base64_decode($xml).'</pre>';
				$mensage = Yii::$app->mailer->compose("layouts/html", ['content'=>$cuerpo])
					->setTo($to)
					->setFrom($from)
					//->setCc($arr_cc)
					->setSubject($asunto)
					->setHtmlBody($cuerpo);
				$mensage->send();	
				*/
			}
			else
			if ($data['ind-estado'] == 'aceptado'){
				$mensaje = "La Nota de Crédito electrónica con clave: [".$nota->clave."] fue aceptada por Hacienda.";
				$nota->estado_id = Estados::STATUS_ACEPTADO; // Aceptada
				$nota->save();
				// Si es aceptada la nota de crédito entonces se debe proceder a eliminar la factura si el código de referencia es 01
				$factura = Facturas::find()->where(['id'=>$nota->factura_id])->one();
				if (!is_null($factura) && $nota->codigo_referencia == '01'){   // Anula Documento de Referencia
					//$factura->borrada_by_nota = 1; // Esto me lo habia pedido inicialmente, 
												   // pero luego me dijo que le cambiara el estado a anulada
					$factura->estado_id = Estados::STATUS_ANULADA;
					$factura->estado_cuenta_cobrar_id = Estados::STATUS_ANULADA;
					$factura->save();
				}
				else
				{
				    // Es una nota de crédito parcial	
					$notas_detalles = NotasCreditoElectronicasDetalles::find()->where(['nota_credito_id'=>$nota->id])->all();	
					
					// Eliminar los detalles de la factura
					FacturasDetalles::deleteAll(['factura_id'=>$factura->id]);
					
					foreach ($notas_detalles as $d)
					{
						$data = new FacturasDetalles;
						$data->attributes = $d->attributes;
						$data->factura_id = $factura->id;
						$data->servicio_id = $d->servicio_id;
						$data->producto_id = $d->producto_id;
						$data->codigo = $d->codigo;						
						$data->descripcion = $d->descripcion;						
						$data->precio = $d->precio;						
						$data->cantidad = $d->cantidad;																								
						$data->monto_descuento = $d->monto_descuento;
						$data->naturaleza_descuento = $d->naturaleza_descuento;
						$data->aplicar_impuesto = $d->aplicar_impuesto;
						$data->impuesto_id = $d->impuesto_id;
						$data->impuesto_tarifa = $d->impuesto_tarifa;
						$data->exonerado = $d->exonerado;																																								
						$data->tipo_documento_exoneracion_id = $d->tipo_documento_exoneracion_id;
						$data->num_documento_exoneracion = $d->num_documento_exoneracion;																																								
						$data->nombre_institucion_emite_exoneracion = $d->nombre_institucion_emite_exoneracion;
						$data->fecha_emision_exoneracion = $d->fecha_emision_exoneracion;																																								
						$data->monto_impuesto_exonerado = $d->monto_impuesto_exonerado;
						$data->porcentaje_compra_exoneracion = $d->porcentaje_compra_exoneracion;																																								
						$data->tipo = $d->tipo;
						$data->tipo_precio = $d->tipo_precio;																																								
						$data->save();	
					}
				}
				
				$type = 'success';
				$titulo = "Informaci&oacute;n <hr class=\"kv-alert-separator\">";	
				$actualizar = 1;	
				$estado = '<small class="label label-success"> ACEPTADA</small>';				

				$xml_respuesta_hacienda = base64_decode($data['respuesta-xml']);
				// Crear el xml de respuesta
				$nombre_archivo = 'NC-MH-'.$nota->clave.'.xml';
				$path = Yii::getAlias('@backend/web/xmlh/'.$nombre_archivo);
				file_put_contents($path, $xml_respuesta_hacienda);				
			}
			else
			if ($data['ind-estado'] == 'recibido'){
				$mensaje = "La Nota de Crédito electrónica con clave: [".$nota->clave."] aún se encuentra en estado Recibida.";
				$nota->estado_id = Estados::STATUS_RECIBIDO; // Recibida
				$nota->save();
				$type = 'warning';
				$titulo = "Advertencia <hr class=\"kv-alert-separator\">";	
				$actualizar = 1;				
			}						
			else
			{
				$mensaje = "Ha ocurrido un error desconocido al consultar el estado de la Nota de Crédito electrónica con clave: [".$nota->clave."]. Póngase en contacto con el administrador del sistema";	
				$type = 'warning';
				$titulo = "Informaci&oacute;n <hr class=\"kv-alert-separator\">";	
			}			
		}
		else
		{
			$mensaje = $response->getHeaders()->get('X-Error-Cause');
			$type = 'warning';
			$titulo = "Error <hr class=\"kv-alert-separator\">";	
		}

		return ['mensaje' => $mensaje, 'type'=>$type, 'titulo'=>$titulo, 'actualizar'=>$actualizar, 'nota_credito_id'=>$nota_credito_id, 'estado'=>$estado];			
	}	
	
	
	public function ExportXmlNC($nota)
	{
		//$inXmlUrl debe de ser en Base64 
		//$p12Url es un downloadcode previamente suministrado al subir el certificado en el modulo 
		  //fileUploader -> subir_certif
		//Tipo es el tipo de documento 
		//01 FE
		//02 ND
		//03 NC
		//04 TE
		//05 06 07 Mensaje Receptor
		$emisor = Configuracion::findOne(1);	
		$nota_detalles = $nota->notasCreditoDetalles;
		$p12Url = $emisor->getFilePath(); 

		$pinP12 = $emisor->pin_certificado;   //'1972';				
		$inXml = $this->getXmlNC($nota, $nota_detalles, $emisor);
		$tipoDocumento = '03'; // Nota de Crédito
		$returnFile = $this->firmar($p12Url, $pinP12, $inXml, $tipoDocumento);
		$xml = base64_decode($returnFile);
		return $xml;		
	}
	
	public function getXmlNC($nota, $nota_credito_detalles, $emisor)
	{
		// Fecha en // ISO 8601 (http://php.net/manual/en/function.date.php
		$fecha = date('c');		
		$plazo_credito = $nota->condicionVenta->codigo == '02' ? $nota->plazo_credito: '0';
		
		$doc  = new \DomDocument('1.0','UTF-8');
		$doc->formatOutput = true;		                               
		$root = $doc->createElementNS('https://tribunet.hacienda.go.cr/docs/esquemas/2017/v4.2/notaCreditoElectronica', 'NotaCreditoElectronica');
		$doc->appendChild($root);
		$root->setAttributeNS('http://www.w3.org/2000/xmlns/' ,'xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
		$root->setAttributeNS('http://www.w3.org/2000/xmlns/' ,'xmlns:ds', 'http://www.w3.org/2000/09/xmldsig#');
		$root->setAttributeNS('http://www.w3.org/2000/xmlns/' ,'xmlns:xsd', 'http://www.w3.org/2001/XMLSchema');
		$root->setAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'xsi:schemaLocation', 'https://tribunet.hacienda.go.cr/docs/esquemas/2017/v4.2/notaCreditoElectronica'.' '.'https://tribunet.hacienda.go.cr/docs/esquemas/2017/v4.2/NotaCreditoElectronica_V4.2.xsd');		
		
		$nodo = $doc->createElement('Clave', $nota->clave);
		$root->appendChild($nodo);
		
		$nodo = $doc->createElement('NumeroConsecutivo', $nota->consecutivo);
		$root->appendChild($nodo);
		
		$nodo = $doc->createElement('FechaEmision', $fecha);
		$root->appendChild($nodo);
		
		// Datos del Emisor
		$nodoemisor = $doc->createElement('Emisor');
		$root->appendChild($nodoemisor);
		
		$nodo = $doc->createElement('Nombre', $emisor->nombre);
		$nodoemisor->appendChild($nodo);
		
		$identificacion = $doc->createElement('Identificacion');
		$nodoemisor->appendChild($identificacion);
		
		$nodo = $doc->createElement('Tipo', trim($emisor->tipoIdentificacion->codigo));
		$identificacion->appendChild($nodo);
		
		$nodo = $doc->createElement('Numero', trim($emisor->identificacion));
		$identificacion->appendChild($nodo);
		
		if (!is_null($emisor->nombre_comercial) && !empty($emisor->nombre_comercial))
		{
			$nodo = $doc->createElement('NombreComercial', $emisor->nombre_comercial);
			$nodoemisor->appendChild($nodo);
		}

		$ubicacion = $doc->createElement('Ubicacion');
		$nodoemisor->appendChild($ubicacion);
		
		$nodo = $doc->createElement('Provincia', $emisor->provincia->codigo);
		$ubicacion->appendChild($nodo);
		
		$nodo = $doc->createElement('Canton', str_pad($emisor->canton->codigo, 2, '0', STR_PAD_LEFT));
		$ubicacion->appendChild($nodo);
		
		$nodo = $doc->createElement('Distrito', str_pad($emisor->distrito->codigo, 2, '0', STR_PAD_LEFT));
		$ubicacion->appendChild($nodo);
		
		if (!is_null($emisor->otras_senas) && !empty($emisor->otras_senas)){
			$nodo = $doc->createElement('OtrasSenas', $emisor->otras_senas);
			$ubicacion->appendChild($nodo);
		}
		
		if (!is_null($emisor->codigo_telefono) && !empty($emisor->codigo_telefono) && !is_null($emisor->telefono) && !empty($emisor->telefono)){	
			$telefono = $doc->createElement('Telefono');
			$nodoemisor->appendChild($telefono);
		
			$nodo = $doc->createElement('CodigoPais', $emisor->codigo_telefono);
			$telefono->appendChild($nodo);
			
			$nodo = $doc->createElement('NumTelefono', $emisor->telefono);
			$telefono->appendChild($nodo);
		}
		
		if (!is_null($emisor->codigo_fax) && !empty($emisor->codigo_fax) && !is_null($emisor->fax) && !empty($emisor->fax)){
			$fax = $doc->createElement('Fax');
			$nodoemisor->appendChild($fax);
			
			$nodo = $doc->createElement('CodigoPais', $emisor->codigo_fax);
			$fax->appendChild($nodo);
			
			$nodo = $doc->createElement('NumTelefono', $emisor->fax);
			$fax->appendChild($nodo);
		}
		
		$nodo = $doc->createElement('CorreoElectronico', $emisor->email);
		$nodoemisor->appendChild($nodo);
		
		
		// Datos Receptor
		$receptor = $doc->createElement('Receptor');
		$root->appendChild($receptor);
		
		$nodo = $doc->createElement('Nombre', $nota->cliente->nombre);
		$receptor->appendChild($nodo);
		
		$identificacion = $doc->createElement('Identificacion');
		$receptor->appendChild($identificacion);
		
		$nodo = $doc->createElement('Tipo', trim($nota->cliente->tipoIdentificacion->codigo));
		$identificacion->appendChild($nodo);

		
		$nodo = $doc->createElement('Numero', trim($nota->cliente->identificacion));
		$identificacion->appendChild($nodo);
		
		if (!is_null($nota->cliente->nombre_comercial) && !empty($nota->cliente->nombre_comercial))
		{
			$nodo = $doc->createElement('NombreComercial', $nota->cliente->nombre_comercial);
			$receptor->appendChild($nodo);
		}
		
		$ubicacion = $doc->createElement('Ubicacion');
		$receptor->appendChild($ubicacion);
		
		$nodo = $doc->createElement('Provincia', $nota->cliente->provincia->codigo);
		$ubicacion->appendChild($nodo);
		
		$nodo = $doc->createElement('Canton', str_pad($nota->cliente->canton->codigo, 2, '0', STR_PAD_LEFT));
		$ubicacion->appendChild($nodo);
		
		$nodo = $doc->createElement('Distrito', str_pad($nota->cliente->distrito->codigo, 2, '0', STR_PAD_LEFT));
		$ubicacion->appendChild($nodo);
		
		if (!is_null($nota->cliente->otras_senas) && !empty($nota->cliente->otras_senas)){		
			$nodo = $doc->createElement('OtrasSenas', $nota->cliente->otras_senas);
			$ubicacion->appendChild($nodo);		
		}
		
		if (!is_null($nota->cliente->codigo_telefono) && !empty($nota->cliente->codigo_telefono) && !is_null($nota->cliente->telefono) && !empty($nota->cliente->telefono)){		
			$telefono = $doc->createElement('Telefono');
			$receptor->appendChild($telefono);
		
			$nodo = $doc->createElement('CodigoPais', $nota->cliente->codigo_telefono);
			$telefono->appendChild($nodo);
		
			$nodo = $doc->createElement('NumTelefono', $nota->cliente->telefono);
			$telefono->appendChild($nodo);
		}
		
		if (!is_null($nota->cliente->codigo_fax) && !empty($nota->cliente->codigo_fax) && !is_null($nota->cliente->fax) && !empty($nota->cliente->fax)){		
			$fax = $doc->createElement('Fax');
			$receptor->appendChild($fax);
		
			$nodo = $doc->createElement('CodigoPais', $nota->cliente->codigo_fax);
			$fax->appendChild($nodo);
		
			$nodo = $doc->createElement('NumTelefono', $nota->cliente->fax);
			$fax->appendChild($nodo);
		}
		
		$nodo = $doc->createElement('CorreoElectronico', $nota->cliente->email);
		$receptor->appendChild($nodo);

		// Otros elementos
		$nodo = $doc->createElement('CondicionVenta', $nota->condicionVenta->codigo);
		$root->appendChild($nodo);
		
		if ($nota->condicion_venta_id == 2) // Crédito		
		{
			$nodo = $doc->createElement('PlazoCredito', $nota->plazo_credito);
			$root->appendChild($nodo);
		}
		
	    $datos = $nota->notasCreditoMediosPagos;
		$i = 1;
	    foreach ($datos as $mp){
			if ($i <= 4){
				$nodo = $doc->createElement('MedioPago', $mp->medioPago->codigo);
				$root->appendChild($nodo);
			}
			$i++;
		}						

		// Datos Del servicio
		$detalle = $doc->createElement('DetalleServicio');
		$root->appendChild($detalle);
		
		$i = 1;
		foreach ($nota_credito_detalles as $fdetalle)
		{		
			$linea = $doc->createElement('LineaDetalle');
			$detalle->appendChild($linea);
		
			$nodo = $doc->createElement('NumeroLinea', $i);
			$linea->appendChild($nodo);
			
			$codigo = $doc->createElement('Codigo');
			$linea->appendChild($codigo);
			
			$nodo = $doc->createElement('Tipo', '01');
			$codigo->appendChild($nodo);
			
			$nodo = $doc->createElement('Codigo', $fdetalle->codigo);
			$codigo->appendChild($nodo);
			
			$nodo = $doc->createElement('Cantidad', number_format($fdetalle->cantidad, 3, '.', ''));
			$linea->appendChild($nodo);
						
			$nodo = $doc->createElement('UnidadMedida', $fdetalle->unidadMedida->codigo);
			$linea->appendChild($nodo);
			
			$nodo = $doc->createElement('Detalle', $fdetalle->descripcion);
			$linea->appendChild($nodo);
			
			$nodo = $doc->createElement('PrecioUnitario', number_format($fdetalle->precio, 5, '.', ''));
			$linea->appendChild($nodo);
			
			$nodo = $doc->createElement('MontoTotal', number_format($fdetalle->getMonto(), 5, '.', ''));
			$linea->appendChild($nodo);
			
			if (!is_null($fdetalle->monto_descuento) && $fdetalle->monto_descuento > 0 && !empty($fdetalle->naturaleza_descuento) && !is_null($fdetalle->naturaleza_descuento)){			
				$nodo = $doc->createElement('MontoDescuento', number_format($fdetalle->getDescuento(), 5, '.', ''));
				$linea->appendChild($nodo);
			
				$nodo = $doc->createElement('NaturalezaDescuento', $fdetalle->naturaleza_descuento);
				$linea->appendChild($nodo);
			}
			
			$nodo = $doc->createElement('SubTotal', number_format($fdetalle->getSubtotal(), 5, '.', ''));
			$linea->appendChild($nodo);
			
			if (($fdetalle->aplicar_impuesto && !is_null($fdetalle->impuesto_id)) || ($fdetalle->exonerado == 1 && !is_null($fdetalle->tipo_documento_exoneracion_id)))
			{			
				$impuesto = $doc->createElement('Impuesto');
				$linea->appendChild($impuesto);
				
				if ($fdetalle->aplicar_impuesto && !is_null($fdetalle->impuesto_id))
				{				
					$nodo = $doc->createElement('Codigo', $fdetalle->impuesto->codigo);
					$impuesto->appendChild($nodo);
					
					$nodo = $doc->createElement('Tarifa', number_format($fdetalle->impuesto_tarifa, 2, '.', ''));
					$impuesto->appendChild($nodo);
					
					$nodo = $doc->createElement('Monto', number_format($fdetalle->getMontoImpuesto(), 5, '.', ''));
					$impuesto->appendChild($nodo);			
				}

				if ($fdetalle->exonerado == 1 && !is_null($fdetalle->tipo_documento_exoneracion_id)){										
					$exoneracion = $doc->createElement('Exoneracion');													
					$impuesto->appendChild($exoneracion);
					
					$nodo = $doc->createElement('TipoDocumento', $fdetalle->tipoDocumentoExoneracion->codigo);
					$exoneracion->appendChild($nodo);
					
					$nodo = $doc->createElement('NumeroDocumento', $fdetalle->num_documento_exoneracion);
					$exoneracion->appendChild($nodo);
					
					$nodo = $doc->createElement('NombreInstitucion', $fdetalle->nombre_institucion_emite_exoneracion);
					$exoneracion->appendChild($nodo);
					
					$fecha_exonerado = date('c', strtotime($fdetalle->fecha_emision_exoneracion));
					$nodo = $doc->createElement('FechaEmision', $fecha_exonerado);
					$exoneracion->appendChild($nodo);
					
					$nodo = $doc->createElement('MontoImpuesto', number_format($fdetalle->getServExento() + $fdetalle->getMercanciaExenta(), 5, '.', ''));
					$exoneracion->appendChild($nodo);
					
					$nodo = $doc->createElement('PorcentajeCompra', $fdetalle->porcentaje_compra_exoneracion);
					$exoneracion->appendChild($nodo);
				}				
			}

			$nodo = $doc->createElement('MontoTotalLinea', number_format($fdetalle->getMontoTotalLinea(), 5, '.', ''));
			$linea->appendChild($nodo);
			
			$i++;
		}

		// Resumen de la factura
		$resumen = $doc->createElement('ResumenFactura');
		$root->appendChild($resumen);
		
		$nodo = $doc->createElement('CodigoMoneda', $nota->moneda->codigo);
		$resumen->appendChild($nodo);
		
		$nodo = $doc->createElement('TipoCambio', number_format($nota->tipo_cambio, 5, '.', ''));
		$resumen->appendChild($nodo);
		
		$nodo = $doc->createElement('TotalServGravados', number_format($nota->totalServGravados, 5, '.', ''));
		$resumen->appendChild($nodo);
		
		$nodo = $doc->createElement('TotalServExentos', number_format($nota->totalServExentos, 5, '.', ''));
		$resumen->appendChild($nodo);
		
		$nodo = $doc->createElement('TotalMercanciasGravadas', number_format($nota->totalMercanciasGravadas, 5, '.', ''));
		$resumen->appendChild($nodo);
		
		$nodo = $doc->createElement('TotalMercanciasExentas', number_format($nota->totalMercanciasExentas, 5, '.', ''));
		$resumen->appendChild($nodo);
		
		$nodo = $doc->createElement('TotalGravado', number_format($nota->totalGravado, 5, '.', ''));
		$resumen->appendChild($nodo);
		
		$nodo = $doc->createElement('TotalExento', number_format($nota->totalExento, 5, '.', ''));
		$resumen->appendChild($nodo);
		
		$nodo = $doc->createElement('TotalVenta', number_format($nota->totalVenta, 5, '.', ''));
		$resumen->appendChild($nodo);
		
		$nodo = $doc->createElement('TotalDescuentos', number_format($nota->totalDescuentos, 5, '.', ''));
		$resumen->appendChild($nodo);
		
		$nodo = $doc->createElement('TotalVentaNeta', number_format($nota->totalVentaNeta, 5, '.', ''));
		$resumen->appendChild($nodo);
		
		$nodo = $doc->createElement('TotalImpuesto', number_format($nota->totalImpuesto, 5, '.', ''));
		$resumen->appendChild($nodo);
		
		$nodo = $doc->createElement('TotalComprobante', number_format($nota->totalComprobante, 5, '.', ''));
		$resumen->appendChild($nodo);
		
		// Referencia
		$referencia = $doc->createElement('InformacionReferencia');
		$root->appendChild($referencia);		
		
		$nodo = $doc->createElement('TipoDoc', '01');
		$referencia->appendChild($nodo);
		
		$nodo = $doc->createElement('Numero', $nota->factura->clave);
		$referencia->appendChild($nodo);		

		$fecha = date('c', strtotime($nota->factura->fecha_emision));
		$nodo = $doc->createElement('FechaEmision', $fecha);
		$referencia->appendChild($nodo);	
		
		$nodo = $doc->createElement('Codigo', $nota->codigo_referencia);
		$referencia->appendChild($nodo);	
		
		$nodo = $doc->createElement('Razon', $nota->razon);
		$referencia->appendChild($nodo);						
				
		// Normativa
		$normativa = $doc->createElement('Normativa');
		$root->appendChild($normativa);
		
		$nodo = $doc->createElement('NumeroResolucion', 'DGT-R-48-2016');
		$normativa->appendChild($nodo);
		
		$nodo = $doc->createElement('FechaResolucion', '20-02-2017 13:22:22');
		$normativa->appendChild($nodo);

		$xml = $doc->saveXML();

		return base64_encode($xml);				
	}
	
	
	
	//************************************************************************************************************************
	//*******************************************FUNCIONES PARA NOTAS DE DEBITO**********************************************
	//************************************************************************************************************************
	
	public function EnviarNDHacienda($nota)
    {
		//$inXmlUrl debe de ser en Base64 
		//$p12Url es un downloadcode previamente suministrado al subir el certificado en el modulo 
		  //fileUploader -> subir_certif
		//Tipo es el tipo de documento 
		//01 FE
		//02 ND
		//03 NC
		//04 TE
		//05 06 07 Mensaje Receptor
		$emisor = Configuracion::findOne(1);	
		$nota_detalles = $nota->notasDebitoDetalles;
		$p12Url = $emisor->getFilePath(); 
		$pinP12 = $emisor->pin_certificado;   //'1972';				
		$inXml = $this->getXmlND($nota, $nota_detalles, $emisor);
		$tipoDocumento = '02'; // ND
		$returnFile = $this->firmar($p12Url, $pinP12, $inXml, $tipoDocumento);
		
		$data = $this->sendND($returnFile, $this->token, $nota, $emisor);
		if ($data['error'] == 1) // Ocurrio un Error
		{
			$mensaje = $data['mensaje'];
			$type = $data['type'];
			$titulo = $data['titulo']; 
			return \Yii::$app->response->data  =  ['mensaje' => $mensaje, 'type'=>$type, 'titulo'=>$titulo];
		}
		
		$respuesta = $data['response'];
		
		$code = $respuesta->getHeaders()->get('http-code');
		if ($code == '202' || $code == '201')
		{
			$mensaje = "La Nota de Débito electrónica con clave: [".$nota->clave."] se recibió correctamente, queda pendiente la validación de esta y el 
						envío de la respuesta de parte de Hacienda.";
			$nota->estado_id = Estados::STATUS_RECIBIDO; // Recibido
			$nota->save();
			$type = 'success';
			$titulo = "Informaci&oacute;n <hr class=\"kv-alert-separator\">";					
		}
		else
		if ($code == '400'){
			$mensaje = $respuesta->getHeaders()->get('X-Error-Cause');
			$type = 'danger';
			$titulo = "Error <hr class=\"kv-alert-separator\">";	
		}
		else
		{
			$mensaje = "Ha ocurrido un error desconocido al enviar la Nota de Débito electrónica con clave: [".$nota->clave."]. Póngase en contacto con el administrador del sistema";	
			$type = 'danger';
			$titulo = "Error <hr class=\"kv-alert-separator\">";	
		}
		$this->CloseSesion($this->token, $emisor);
		return ['mensaje' => $mensaje, 'type'=>$type, 'titulo'=>$titulo];
    }
	
	function sendND($comprobanteXML, $token, $nota, $emisor) {
		if ($emisor->activar_produccion){
			$url_api = 	'https://api.comprobanteselectronicos.go.cr/recepcion/v1/recepcion/';
		}
		else{
			$url_api = 	'https://api.comprobanteselectronicos.go.cr/recepcion-sandbox/v1/recepcion/';
		}		
				
		$client = new Client([
						'requestConfig' => [
							'format' => Client::FORMAT_JSON
						],
						'responseConfig' => [
							'format' => Client::FORMAT_JSON
						],
					]);
		// Fecha en // ISO 8601 (http://php.net/manual/en/function.date.php
		$fecha = date('c');
		
		$CallBackUrl = Url::base('http');
		$CallBackUrl = $CallBackUrl.'/haciendacallback/'.$emisor->id;		
		
		$autorization = 'bearer ' . $token;	

		$error = 0;
		$mensaje = '';	
		$type = '';
		$titulo = '';						
		$response =  NULL;
		try 
		{
			$response = $client->createRequest()
							   ->setMethod('POST')
							   ->setFormat(Client::FORMAT_JSON)
							   ->setUrl($url_api)	
							   ->setHeaders(['Authorization' => $autorization])		
							   ->setData(['clave' => $nota->clave,
										  'fecha' => $fecha,
										  'emisor' => [
												'tipoIdentificacion' => $emisor->tipoIdentificacion->codigo,
												'numeroIdentificacion' => $emisor->identificacion					  
										  ],
										  'receptor' => [
												'tipoIdentificacion' => $nota->cliente->tipoIdentificacion->codigo,
												'numeroIdentificacion' => $nota->cliente->identificacion
										  ],
										  'callbackUrl' => $CallBackUrl,
										  'comprobanteXml' => $comprobanteXML
									  ])		
							   ->send();							   
		} 
		catch (InvalidParamException $e){
			$error = 1;
			$mensaje = 'Ha ocurrido un error al tratar de enviar la Nota de Débito a la api de hacienda. Inténtento nuevamente 
						y si el error persiste póngase en contacto con el administrador del sistema';	
			$type = 'danger';
			$titulo = "Error <hr class=\"kv-alert-separator\">";						
			return ['error'=>$error, 'mensaje' => $mensaje, 'type'=>$type, 'titulo'=>$titulo, 'response'=>$response];				
		}					
		return ['error'=>$error, 'mensaje' => $mensaje, 'type'=>$type, 'titulo'=>$titulo, 'response'=>$response];				
	}	
	
	function getEstadoND($nota) {
		$emisor = Configuracion::findOne(1);			
		if ($emisor->activar_produccion){
			$url_api = 	'https://api.comprobanteselectronicos.go.cr/recepcion/v1/recepcion/';
		}
		else
		{
			$url_api = 	'https://api.comprobanteselectronicos.go.cr/recepcion-sandbox/v1/recepcion/';
		}		
		$client = new Client([
						'requestConfig' => [
							'format' => Client::FORMAT_JSON
						],
						'responseConfig' => [
							'format' => Client::FORMAT_JSON
						],
					]);
		$autorization = 'bearer ' . $this->token;		
		
		try 
		{
			$response = $client->createRequest()
				->setMethod('POST')
				->setFormat(Client::FORMAT_JSON)
				->setUrl($url_api.'/'.$nota->clave)	
				->setHeaders(['Authorization' => $autorization])		
				->send();

		} catch (InvalidParamException $e) {
			$mensaje = 'Ha ocurrido un error desconocido al consultar el estado de la Nota de Crédito. Póngase en contacto con el administrador del sistema';	
			$type = 'danger';
			$titulo = "Informaci&oacute;n <hr class=\"kv-alert-separator\">";						
			return ['mensaje' => $mensaje, 'type'=>$type, 'titulo'=>$titulo];				
		}

		$actualizar = 0;
		$nota_debito_id = 0;
		$estado = '';
		
		// Si llega aqui no hubo error
		$data = Json::decode($response->content);
		if (is_array($data))
		{
			if ($data['ind-estado'] == 'rechazado')
			{
				$nota->estado_id = Estados::STATUS_RECHAZADO; // Rechazada
				$nota->save();
				$type = 'danger';
				$titulo = "Error <hr class=\"kv-alert-separator\">";
				$actualizar = 1;	
				$nota_debito_id = $nota->id;
				$estado = '<small class="label label-danger"> RECHAZADA</small>';				
				$mensaje = "El comprobante electrónico con clave: [".$nota->clave."] fue rechazado por Hacienda";

				$xml_respuesta_hacienda = base64_decode($data['respuesta-xml']);

				// Crear el xml de respuesta
				$nombre_archivo = 'ND-MH-'.$nota->clave.'.xml';
				$path = Yii::getAlias('@backend/web/xmlh/'.$nombre_archivo);
				file_put_contents($path, $xml_respuesta_hacienda);

				/*
				//$cuerpo = '<pre>'.base64_decode($xml).'</pre>';
				$mensage = Yii::$app->mailer->compose("layouts/html", ['content'=>$cuerpo])
					->setTo($to)
					->setFrom($from)
					//->setCc($arr_cc)
					->setSubject($asunto)
					->setHtmlBody($cuerpo);
				$mensage->send();	
				*/
			}
			else
			if ($data['ind-estado'] == 'aceptado'){
				$mensaje = "La Nota de Débito electrónica con clave: [".$nota->clave."] fue aceptada por Hacienda.";
				$nota->estado_id = Estados::STATUS_ACEPTADO; // Aceptada
				$nota->save();
				// Si es aceptada la nota de crédito entonces se debe proceder a eliminar la factura
				$factura = Facturas::find()->where(['id'=>$nota->factura_id])->one();
				if (!is_null($factura) && $nota->codigo_referencia == '01'){   // Anula Documento de Referencia
					//$factura->borrada_by_nota = 1; // Esto me lo habia pedido inicialmente, 
												   // pero luego me dijo que le cambiara el estado a anulada
					$factura->estado_id = Estados::STATUS_ANULADA;
					$factura->estado_cuenta_cobrar_id = Estados::STATUS_ANULADA;					
					$factura->save();
				}
				else
				{
				    // Es una nota de crédito parcial	
					$notas_detalles = NotasDebitoElectronicasDetalles::find()->where(['nota_debito_id'=>$nota->id])->all();	
					
					// Eliminar los detalles de la factura
					FacturasDetalles::deleteAll(['factura_id'=>$factura->id]);
					
					foreach ($notas_detalles as $d)
					{
						$data = new FacturasDetalles;
						$data->attributes = $d->attributes;
						$data->factura_id = $factura->id;
						$data->servicio_id = $d->servicio_id;
						$data->producto_id = $d->producto_id;
						$data->codigo = $d->codigo;						
						$data->descripcion = $d->descripcion;						
						$data->precio = $d->precio;						
						$data->cantidad = $d->cantidad;																								
						$data->monto_descuento = $d->monto_descuento;
						$data->naturaleza_descuento = $d->naturaleza_descuento;
						$data->aplicar_impuesto = $d->aplicar_impuesto;
						$data->impuesto_id = $d->impuesto_id;
						$data->impuesto_tarifa = $d->impuesto_tarifa;
						$data->exonerado = $d->exonerado;																																								
						$data->tipo_documento_exoneracion_id = $d->tipo_documento_exoneracion_id;
						$data->num_documento_exoneracion = $d->num_documento_exoneracion;																																								
						$data->nombre_institucion_emite_exoneracion = $d->nombre_institucion_emite_exoneracion;
						$data->fecha_emision_exoneracion = $d->fecha_emision_exoneracion;																																								
						$data->monto_impuesto_exonerado = $d->monto_impuesto_exonerado;
						$data->porcentaje_compra_exoneracion = $d->porcentaje_compra_exoneracion;																																								
						$data->tipo = $d->tipo;
						$data->tipo_precio = $d->tipo_precio;																																								
						$data->save();	
					}
				}				
				
				
				$type = 'success';
				$titulo = "Informaci&oacute;n <hr class=\"kv-alert-separator\">";	
				$actualizar = 1;	
				$factura_id = $factura->id;
				$estado = '<small class="label label-success"> ACEPTADA</small>';				
				$xml_respuesta_hacienda = base64_decode($data['respuesta-xml']);

				// Crear el xml de respuesta
				$nombre_archivo = 'ND-MH-'.$nota->clave.'.xml';
				$path = Yii::getAlias('@backend/web/xmlh/'.$nombre_archivo);
				file_put_contents($path, $xml_respuesta_hacienda);				
			}
			else
			if ($data['ind-estado'] == 'recibido'){
				$mensaje = "La Nota de Débito electrónica con clave: [".$nota->clave."] aún se encuentra en estado Recibida.";
				$nota->estado_id = Estados::STATUS_RECIBIDO; // Recibida
				$nota->save();
				$type = 'warning';
				$titulo = "Advertencia <hr class=\"kv-alert-separator\">";	
				$actualizar = 1;				
			}						
			else
			{
				$mensaje = "Ha ocurrido un error desconocido al consultar el estado de la Nota de Débito electrónica con clave: [".$nota->clave."]. Póngase en contacto con el administrador del sistema";	
				$type = 'warning';
				$titulo = "Informaci&oacute;n <hr class=\"kv-alert-separator\">";	
			}			
		}
		else
		{
			$mensaje = $response->content;	
			$type = 'danger';
			$titulo = "Error <hr class=\"kv-alert-separator\">";	
		}

		return ['mensaje' => $mensaje, 'type'=>$type, 'titulo'=>$titulo, 'actualizar'=>$actualizar, 'nota_debito_id'=>$nota_debito_id, 'estado'=>$estado];			
	}	
	
	
	public function ExportXmlND($nota)
	{
		//$inXmlUrl debe de ser en Base64 
		//$p12Url es un downloadcode previamente suministrado al subir el certificado en el modulo 
		  //fileUploader -> subir_certif
		//Tipo es el tipo de documento 
		//01 FE
		//02 ND
		//03 NC
		//04 TE
		//05 06 07 Mensaje Receptor
		$emisor = Configuracion::findOne(1);	
		$nota_detalles = $nota->notasDebitoDetalles;
		$p12Url = $emisor->getFilePath(); 

		$pinP12 = $emisor->pin_certificado;   //'1972';				
		$inXml = $this->getXmlND($nota, $nota_detalles, $emisor);
		$tipoDocumento = '02'; // Nota de Débito
		$returnFile = $this->firmar($p12Url, $pinP12, $inXml, $tipoDocumento);
		$xml = base64_decode($returnFile);
		return $xml;		
	}
	
	public function getXmlND($nota, $nota_debito_detalles, $emisor)
	{
		// Fecha en // ISO 8601 (http://php.net/manual/en/function.date.php
		$fecha = date('c');		
		$plazo_credito = $nota->condicionVenta->codigo == '02' ? $nota->plazo_credito: '0';
		
		$doc  = new \DomDocument('1.0','UTF-8');
		$doc->formatOutput = true;		                               
		$root = $doc->createElementNS('https://tribunet.hacienda.go.cr/docs/esquemas/2017/v4.2/notaDebitoElectronica', 'NotaDebitoElectronica');
		$doc->appendChild($root);
		$root->setAttributeNS('http://www.w3.org/2000/xmlns/' ,'xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
		$root->setAttributeNS('http://www.w3.org/2000/xmlns/' ,'xmlns:ds', 'http://www.w3.org/2000/09/xmldsig#');
		$root->setAttributeNS('http://www.w3.org/2000/xmlns/' ,'xmlns:xsd', 'http://www.w3.org/2001/XMLSchema');
		$root->setAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'xsi:schemaLocation', 'https://tribunet.hacienda.go.cr/docs/esquemas/2017/v4.2/notaDebitoElectronica'.' '.'https://tribunet.hacienda.go.cr/docs/esquemas/2017/v4.2/NotaDebitoElectronica_V4.2.xsd');		
		
		$nodo = $doc->createElement('Clave', $nota->clave);
		$root->appendChild($nodo);
		
		$nodo = $doc->createElement('NumeroConsecutivo', $nota->consecutivo);
		$root->appendChild($nodo);
		
		$nodo = $doc->createElement('FechaEmision', $fecha);
		$root->appendChild($nodo);
		
		// Datos del Emisor
		$nodoemisor = $doc->createElement('Emisor');
		$root->appendChild($nodoemisor);
		
		$nodo = $doc->createElement('Nombre', $emisor->nombre);
		$nodoemisor->appendChild($nodo);
		
		$identificacion = $doc->createElement('Identificacion');
		$nodoemisor->appendChild($identificacion);
		
		$nodo = $doc->createElement('Tipo', trim($emisor->tipoIdentificacion->codigo));
		$identificacion->appendChild($nodo);
		
		$nodo = $doc->createElement('Numero', trim($emisor->identificacion));
		$identificacion->appendChild($nodo);
		
		if (!is_null($emisor->nombre_comercial) && !empty($emisor->nombre_comercial))
		{
			$nodo = $doc->createElement('NombreComercial', $emisor->nombre_comercial);
			$nodoemisor->appendChild($nodo);
		}

		$ubicacion = $doc->createElement('Ubicacion');
		$nodoemisor->appendChild($ubicacion);
		
		$nodo = $doc->createElement('Provincia', $emisor->provincia->codigo);
		$ubicacion->appendChild($nodo);
		
		$nodo = $doc->createElement('Canton', str_pad($emisor->canton->codigo, 2, '0', STR_PAD_LEFT));
		$ubicacion->appendChild($nodo);
		
		$nodo = $doc->createElement('Distrito', str_pad($emisor->distrito->codigo, 2, '0', STR_PAD_LEFT));
		$ubicacion->appendChild($nodo);
		
		if (!is_null($emisor->otras_senas) && !empty($emisor->otras_senas)){
			$nodo = $doc->createElement('OtrasSenas', $emisor->otras_senas);
			$ubicacion->appendChild($nodo);
		}
		
		if (!is_null($emisor->codigo_telefono) && !empty($emisor->codigo_telefono) && !is_null($emisor->telefono) && !empty($emisor->telefono)){	
			$telefono = $doc->createElement('Telefono');
			$nodoemisor->appendChild($telefono);
		
			$nodo = $doc->createElement('CodigoPais', $emisor->codigo_telefono);
			$telefono->appendChild($nodo);
			
			$nodo = $doc->createElement('NumTelefono', $emisor->telefono);
			$telefono->appendChild($nodo);
		}
		
		if (!is_null($emisor->codigo_fax) && !empty($emisor->codigo_fax) && !is_null($emisor->fax) && !empty($emisor->fax)){
			$fax = $doc->createElement('Fax');
			$nodoemisor->appendChild($fax);
			
			$nodo = $doc->createElement('CodigoPais', $emisor->codigo_fax);
			$fax->appendChild($nodo);
			
			$nodo = $doc->createElement('NumTelefono', $emisor->fax);
			$fax->appendChild($nodo);
		}
		
		$nodo = $doc->createElement('CorreoElectronico', $emisor->email);
		$nodoemisor->appendChild($nodo);
		
		
		// Datos Receptor
		$receptor = $doc->createElement('Receptor');
		$root->appendChild($receptor);
		
		$nodo = $doc->createElement('Nombre', $nota->cliente->nombre);
		$receptor->appendChild($nodo);
		
		$identificacion = $doc->createElement('Identificacion');
		$receptor->appendChild($identificacion);
		
		$nodo = $doc->createElement('Tipo', trim($nota->cliente->tipoIdentificacion->codigo));
		$identificacion->appendChild($nodo);
		
		$nodo = $doc->createElement('Numero', trim($nota->cliente->identificacion));
		$identificacion->appendChild($nodo);
		
		if (!is_null($nota->cliente->nombre_comercial) && !empty($nota->cliente->nombre_comercial))
		{
			$nodo = $doc->createElement('NombreComercial', $nota->cliente->nombre_comercial);
			$receptor->appendChild($nodo);
		}
		
		$ubicacion = $doc->createElement('Ubicacion');
		$receptor->appendChild($ubicacion);
		
		$nodo = $doc->createElement('Provincia', $nota->cliente->provincia->codigo);
		$ubicacion->appendChild($nodo);
		
		$nodo = $doc->createElement('Canton', str_pad($nota->cliente->canton->codigo, 2, '0', STR_PAD_LEFT));
		$ubicacion->appendChild($nodo);
		
		$nodo = $doc->createElement('Distrito', str_pad($nota->cliente->distrito->codigo, 2, '0', STR_PAD_LEFT));
		$ubicacion->appendChild($nodo);
		
		if (!is_null($nota->cliente->otras_senas) && !empty($nota->cliente->otras_senas)){		
			$nodo = $doc->createElement('OtrasSenas', $nota->cliente->otras_senas);
			$ubicacion->appendChild($nodo);		
		}
		
		if (!is_null($nota->cliente->codigo_telefono) && !empty($nota->cliente->codigo_telefono) && !is_null($nota->cliente->telefono) && !empty($nota->cliente->telefono)){		
			$telefono = $doc->createElement('Telefono');
			$receptor->appendChild($telefono);
		
			$nodo = $doc->createElement('CodigoPais', $nota->cliente->codigo_telefono);
			$telefono->appendChild($nodo);
		
			$nodo = $doc->createElement('NumTelefono', $nota->cliente->telefono);
			$telefono->appendChild($nodo);
		}
		
		if (!is_null($nota->cliente->codigo_fax) && !empty($nota->cliente->codigo_fax) && !is_null($nota->cliente->fax) && !empty($nota->cliente->fax)){		
			$fax = $doc->createElement('Fax');
			$receptor->appendChild($fax);
		
			$nodo = $doc->createElement('CodigoPais', $nota->cliente->codigo_fax);
			$fax->appendChild($nodo);
		
			$nodo = $doc->createElement('NumTelefono', $nota->cliente->fax);
			$fax->appendChild($nodo);
		}
		
		$nodo = $doc->createElement('CorreoElectronico', $nota->cliente->email);
		$receptor->appendChild($nodo);

		// Otros elementos
		$nodo = $doc->createElement('CondicionVenta', $nota->condicionVenta->codigo);
		$root->appendChild($nodo);
		
		if ($nota->condicion_venta_id == 2) // Crédito		
		{
			$nodo = $doc->createElement('PlazoCredito', $nota->plazo_credito);
			$root->appendChild($nodo);
		}
		
	    $datos = $nota->notasDebitoMediosPagos;
		$i = 1;
	    foreach ($datos as $mp){
			if ($i <= 4){
				$nodo = $doc->createElement('MedioPago', $mp->medioPago->codigo);
				$root->appendChild($nodo);
			}
			$i++;
		}						

		// Datos Del servicio
		$detalle = $doc->createElement('DetalleServicio');
		$root->appendChild($detalle);
		
		$i = 1;
		foreach ($nota_debito_detalles as $fdetalle)
		{		
			$linea = $doc->createElement('LineaDetalle');
			$detalle->appendChild($linea);

			$nodo = $doc->createElement('NumeroLinea', $i);
			$linea->appendChild($nodo);
			
			$codigo = $doc->createElement('Codigo');
			$linea->appendChild($codigo);
			
			$nodo = $doc->createElement('Tipo', '01');
			$codigo->appendChild($nodo);
			
			$nodo = $doc->createElement('Codigo', $fdetalle->codigo);
			$codigo->appendChild($nodo);
			
			$nodo = $doc->createElement('Cantidad', number_format($fdetalle->cantidad, 3, '.', ''));
			$linea->appendChild($nodo);
			
			$nodo = $doc->createElement('UnidadMedida', $fdetalle->unidadMedida->codigo);
			$linea->appendChild($nodo);
			
			$nodo = $doc->createElement('Detalle', $fdetalle->descripcion);
			$linea->appendChild($nodo);
			
			$nodo = $doc->createElement('PrecioUnitario', number_format($fdetalle->precio, 5, '.', ''));
			$linea->appendChild($nodo);
			
			$nodo = $doc->createElement('MontoTotal', number_format($fdetalle->getMonto(), 5, '.', ''));
			$linea->appendChild($nodo);
			
			if (!is_null($fdetalle->monto_descuento) && $fdetalle->monto_descuento > 0 && !empty($fdetalle->naturaleza_descuento) && !is_null($fdetalle->naturaleza_descuento)){			
				$nodo = $doc->createElement('MontoDescuento', number_format($fdetalle->getDescuento(), 5, '.', ''));
				$linea->appendChild($nodo);
			
				$nodo = $doc->createElement('NaturalezaDescuento', $fdetalle->naturaleza_descuento);
				$linea->appendChild($nodo);
			}
			
			$nodo = $doc->createElement('SubTotal', number_format($fdetalle->getSubtotal(), 5, '.', ''));
			$linea->appendChild($nodo);
			
			if (($fdetalle->aplicar_impuesto && !is_null($fdetalle->impuesto_id)) || ($fdetalle->exonerado == 1 && !is_null($fdetalle->tipo_documento_exoneracion_id)))
			{			
				$impuesto = $doc->createElement('Impuesto');
				$linea->appendChild($impuesto);
				
				if ($fdetalle->aplicar_impuesto && !is_null($fdetalle->impuesto_id))
				{				
					$nodo = $doc->createElement('Codigo', $fdetalle->impuesto->codigo);
					$impuesto->appendChild($nodo);
					
					$nodo = $doc->createElement('Tarifa', number_format($fdetalle->impuesto_tarifa, 2, '.', ''));
					$impuesto->appendChild($nodo);
					
					$nodo = $doc->createElement('Monto', number_format($fdetalle->getMontoImpuesto(), 5, '.', ''));
					$impuesto->appendChild($nodo);			
				}

				if ($fdetalle->exonerado == 1 && !is_null($fdetalle->tipo_documento_exoneracion_id)){										
					$exoneracion = $doc->createElement('Exoneracion');													
					$impuesto->appendChild($exoneracion);
					
					$nodo = $doc->createElement('TipoDocumento', $fdetalle->tipoDocumentoExoneracion->codigo);
					$exoneracion->appendChild($nodo);
					
					$nodo = $doc->createElement('NumeroDocumento', $fdetalle->num_documento_exoneracion);
					$exoneracion->appendChild($nodo);
					
					$nodo = $doc->createElement('NombreInstitucion', $fdetalle->nombre_institucion_emite_exoneracion);
					$exoneracion->appendChild($nodo);
					
					$fecha_exonerado = date('c', strtotime($fdetalle->fecha_emision_exoneracion));
					$nodo = $doc->createElement('FechaEmision', $fecha_exonerado);
					$exoneracion->appendChild($nodo);
					
					$nodo = $doc->createElement('MontoImpuesto', number_format($fdetalle->getServExento() + $fdetalle->getMercanciaExenta(), 5, '.', ''));
					$exoneracion->appendChild($nodo);
					
					$nodo = $doc->createElement('PorcentajeCompra', $fdetalle->porcentaje_compra_exoneracion);
					$exoneracion->appendChild($nodo);
				}				
			}

			$nodo = $doc->createElement('MontoTotalLinea', number_format($fdetalle->getMontoTotalLinea(), 5, '.', ''));
			$linea->appendChild($nodo);
			
			$i++;
		}

		// Resumen de la factura
		$resumen = $doc->createElement('ResumenFactura');
		$root->appendChild($resumen);
		
		$nodo = $doc->createElement('CodigoMoneda', $nota->moneda->codigo);
		$resumen->appendChild($nodo);
		
		$nodo = $doc->createElement('TipoCambio', number_format($nota->tipo_cambio, 5, '.', ''));
		$resumen->appendChild($nodo);
		
		$nodo = $doc->createElement('TotalServGravados', number_format($nota->totalServGravados, 5, '.', ''));

		$resumen->appendChild($nodo);
		
		$nodo = $doc->createElement('TotalServExentos', number_format($nota->totalServExentos, 5, '.', ''));
		$resumen->appendChild($nodo);
		
		$nodo = $doc->createElement('TotalMercanciasGravadas', number_format($nota->totalMercanciasGravadas, 5, '.', ''));
		$resumen->appendChild($nodo);
		
		$nodo = $doc->createElement('TotalMercanciasExentas', number_format($nota->totalMercanciasExentas, 5, '.', ''));
		$resumen->appendChild($nodo);
		
		$nodo = $doc->createElement('TotalGravado', number_format($nota->totalGravado, 5, '.', ''));
		$resumen->appendChild($nodo);
		
		$nodo = $doc->createElement('TotalExento', number_format($nota->totalExento, 5, '.', ''));
		$resumen->appendChild($nodo);
		
		$nodo = $doc->createElement('TotalVenta', number_format($nota->totalVenta, 5, '.', ''));
		$resumen->appendChild($nodo);
		
		$nodo = $doc->createElement('TotalDescuentos', number_format($nota->totalDescuentos, 5, '.', ''));
		$resumen->appendChild($nodo);
		
		$nodo = $doc->createElement('TotalVentaNeta', number_format($nota->totalVentaNeta, 5, '.', ''));
		$resumen->appendChild($nodo);
		
		$nodo = $doc->createElement('TotalImpuesto', number_format($nota->totalImpuesto, 5, '.', ''));
		$resumen->appendChild($nodo);
		
		$nodo = $doc->createElement('TotalComprobante', number_format($nota->totalComprobante, 5, '.', ''));
		$resumen->appendChild($nodo);
		
		// Referencia
		$referencia = $doc->createElement('InformacionReferencia');
		$root->appendChild($referencia);		
		
		$nodo = $doc->createElement('TipoDoc', '01');
		$referencia->appendChild($nodo);
		
		$nodo = $doc->createElement('Numero', $nota->factura->clave);
		$referencia->appendChild($nodo);		

		$fecha = date('c', strtotime($nota->factura->fecha_emision));
		$nodo = $doc->createElement('FechaEmision', $fecha);
		$referencia->appendChild($nodo);	
		
		$nodo = $doc->createElement('Codigo', $nota->codigo_referencia);
		$referencia->appendChild($nodo);	
		
		$nodo = $doc->createElement('Razon', $nota->razon);
		$referencia->appendChild($nodo);						
				
		// Normativa
		$normativa = $doc->createElement('Normativa');
		$root->appendChild($normativa);
		
		$nodo = $doc->createElement('NumeroResolucion', 'DGT-R-48-2016');
		$normativa->appendChild($nodo);
		
		$nodo = $doc->createElement('FechaResolucion', '20-02-2017 13:22:22');
		$normativa->appendChild($nodo);

		$xml = $doc->saveXML();

		return base64_encode($xml);				
	}	
	
	public function EnviarRespuestaReceptorHacienda($documento, $receptor)
    {
		//$inXmlUrl debe de ser en Base64 
		//$p12Url es un downloadcode previamente suministrado al subir el certificado en el modulo 
		  //fileUploader -> subir_certif
		//Tipo es el tipo de documento 
		//01 FE
		//02 ND
		//03 NC
		//04 TE
		//05 06 07 Mensaje Receptor
		//$emisor = $factura->emisor;
		$p12Url = $receptor->getFilePath(); 
		$pinP12 = $receptor->pin_certificado;   //'1972';				
		$inXml = $this->getXmlRespuestaReceptor($documento, $receptor);
		$tipoDocumento = '05'; // Mensaje de Receptor
		
		$returnFile = $this->firmar($p12Url, $pinP12, $inXml, $tipoDocumento);
		$error = 0;
		
		$data = $this->sendRespuestaDocumento($returnFile, $this->token, $documento, $receptor);
		if ($data['error'] == 1) // Ocurrio un Error
		{
			$mensaje = $data['mensaje'];
			$type = $data['type'];
			$titulo = $data['titulo']; 
			return \Yii::$app->response->data = ['error'=>1, 'mensaje' => $mensaje, 'type'=>$type, 'titulo'=>$titulo];
			//return ['error'=>1, 'mensaje' => $mensaje, 'type'=>$type, 'titulo'=>$titulo];			
		}
		
		$respuesta = $data['response'];
		

		/*
		return $respuesta->getIsOk();
		return $respuesta;
		*/

		if ($respuesta->getIsOk()) {
			
			//die(var_dump($respuesta));
			$error = 0;
			$mensaje = "El comprobante electrónico con clave: [".$documento->clave."] se recibió correctamente por hacienda, queda pendiente la validación de esta y el 
						envío de la respuesta de parte de Hacienda.";
			//$documento->estado_id = 2; // Recibido
			//$docum->save();
			$type = 'success';
			$titulo = "Informaci&oacute;n <hr class=\"kv-alert-separator\">";					
		}
		else
		{
			$error = 1;
			$mensaje = $respuesta->getHeaders()->get('X-Error-Cause');
			//$mensaje =	Json::decode($respuesta->content);;							   

			$type = 'danger';
			$titulo = "Error <hr class=\"kv-alert-separator\">";	
		}
		//$this->CloseSesion($this->token, $receptor);
		return \Yii::$app->response->data = ['error'=>$error, 'mensaje' => $mensaje, 'type'=>$type, 'titulo'=>$titulo];
    }
	
	function sendRespuestaDocumento($comprobanteXML, $token, $documento, $receptor) {
		
		if ($receptor->activar_produccion){
			$url_api = 	'https://api.comprobanteselectronicos.go.cr/recepcion/v1/recepcion/';
		}
		else{
			$url_api = 	'https://api.comprobanteselectronicos.go.cr/recepcion-sandbox/v1/recepcion/';
		}		
				
		$client = new Client([
						'requestConfig' => [
							'format' => Client::FORMAT_JSON
						],
						'responseConfig' => [
							'format' => Client::FORMAT_JSON
						],
					]);
		// Fecha en // ISO 8601 (http://php.net/manual/en/function.date.php
		$fecha = date('c');
		
		$CallBackUrl = Url::base('http');
		$CallBackUrl = $CallBackUrl.'/haciendacallback/'.$receptor->id;		
		
		$autorization = 'bearer ' . $token;	

		$error = 0;
		$mensaje = '';	
		$type = '';
		$titulo = '';						
		$response =  NULL;
		try 
		{
			$response = $client->createRequest()
							   ->setMethod('POST')
							   ->setFormat(Client::FORMAT_JSON)
							   ->setUrl($url_api)	
							   ->setHeaders(['Authorization' => $autorization])		
							   ->setData(['clave' => $documento->clave,
										  'fecha' => $fecha,
										  'emisor' => [
												'tipoIdentificacion' => $documento->emisor_tipo_identificacion,
												'numeroIdentificacion' => $documento->emisor_identificacion					  
										  ],
										  'receptor' => [
												'tipoIdentificacion' => $receptor->tipoIdentificacion->codigo,
												'numeroIdentificacion' => $receptor->identificacion
										  ],
										  'consecutivoReceptor' => $documento->consecutivo,
										  'callbackUrl' => $CallBackUrl,
										  'comprobanteXml' => $comprobanteXML
									  ])		
							   ->send();							   
		} 
		catch (InvalidParamException $e){
			$error = 1;
			$mensaje = 'Ha ocurrido un error al tratar de enviar el comprobante a la api de hacienda. Inténtento nuevamente 
						y si el error persiste póngase en contacto con el administrador del sistema';	
			$type = 'danger';
			$titulo = "Error <hr class=\"kv-alert-separator\">";						
			return ['error'=>$error, 'mensaje' => $mensaje, 'type'=>$type, 'titulo'=>$titulo, 'response'=>$response];				
		}					
		return ['error'=>$error, 'mensaje' => $mensaje, 'type'=>$type, 'titulo'=>$titulo, 'response'=>$response];				
	}	
	

	public function getXmlRespuestaReceptor($documento, $receptor)
	{
		// Fecha en // ISO 8601 (http://php.net/manual/en/function.date.php
		$fecha = date('c');		
		
		$doc  = new \DomDocument('1.0','UTF-8');
		$doc->formatOutput = true;		           

		$root = $doc->createElementNS('https://tribunet.hacienda.go.cr/docs/esquemas/2017/v4.2/mensajeReceptor', 'MensajeReceptor');
		$doc->appendChild($root);
		$root->setAttributeNS('http://www.w3.org/2000/xmlns/' ,'xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
		$root->setAttributeNS('http://www.w3.org/2000/xmlns/' ,'xmlns:xsd', 'http://www.w3.org/2001/XMLSchema');
		$root->setAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'xsi:schemaLocation', 'https://tribunet.hacienda.go.cr/docs/esquemas/2017/v4.2/mensajeReceptor'.' '.'https://tribunet.hacienda.go.cr/docs/esquemas/2017/v4.2/mensajeReceptor.xsd');		
		
		$nodo = $doc->createElement('Clave', $documento->clave);
		$root->appendChild($nodo);
		
		$nodo = $doc->createElement('NumeroCedulaEmisor', $documento->emisor_identificacion);
		$root->appendChild($nodo);
		
		$nodo = $doc->createElement('FechaEmisionDoc', $fecha);
		$root->appendChild($nodo);
		
		$mensaje = 1;
		if ($documento->estado_id == 2) // Aceptado
			$mensaje = 1;
			
		if ($documento->estado_id == 3) // Aceptado Parcial
			$mensaje = 2;

		if ($documento->estado_id == 4) // Rechazado
			$mensaje = 3;
			
		$nodo = $doc->createElement('Mensaje', $mensaje);
		$root->appendChild($nodo);

		if (!is_null($documento->detalle_mensaje) && !empty($documento->detalle_mensaje))
		{
			$nodo = $doc->createElement('DetalleMensaje', $documento->detalle_mensaje);
			$root->appendChild($nodo);
		}

		if (!is_null($documento->total_impuesto) && !empty($documento->total_impuesto) && $documento->total_impuesto > 0)
		{
			$nodo = $doc->createElement('MontoTotalImpuesto', $documento->total_impuesto);
			$root->appendChild($nodo);
		}
		
		$nodo = $doc->createElement('TotalFactura', $documento->total_factura);
		$root->appendChild($nodo);		
		
		$nodo = $doc->createElement('NumeroCedulaReceptor', $receptor->identificacion);
		$root->appendChild($nodo);
		
		$nodo = $doc->createElement('NumeroConsecutivoReceptor', $documento->consecutivo);
		$root->appendChild($nodo);
		
		$xml = $doc->saveXML();

		return base64_encode($xml);				
	}

	function getEstadoDocumento($documento) {
		$emisor = Configuracion::findOne(1);
		if ($emisor->activar_produccion){
			$url_api = 	'https://api.comprobanteselectronicos.go.cr/recepcion/v1/recepcion/';
		}
		else
		{
			$url_api = 	'https://api.comprobanteselectronicos.go.cr/recepcion-sandbox/v1/recepcion/';
		}		
		$client = new Client([
						'requestConfig' => [
							'format' => Client::FORMAT_JSON
						],
						'responseConfig' => [
							'format' => Client::FORMAT_JSON
						],
					]);
		$autorization = 'bearer ' . $this->token;		
		
		$response = $client->createRequest()
			->setMethod('POST')
			->setFormat(Client::FORMAT_JSON)
			->setUrl($url_api.'/'.$documento->clave.'-'.$documento->consecutivo)	
			->setHeaders(['Authorization' => $autorization])		
			->send();

		$actualizar = 0;
		$factura_id = 0;
		$estado = '';
		
		/*		
		// Probar este código CACERES		
		$code = $respuesta->getHeaders()->get('http-code');
		switch ($code) {
			case 200:
			  // Acá se debe procesar la respuesta para determinar si el atributo "ind-estado"
			  // del JSON. de respuesta da por aceptado o rechazado el documento. Si no está
			  // en ese estado se debe reintentar posteriormente.
			  break;
			case 404:
			  // Se presenta si no se localiza la clave brindada
			  LOG.log(Level.SEVERE, "La clave no esta registrada");
			  break;
		}	
		*/		

		$data = Json::decode($response->content);
		if (is_array($data))
		{
			if ($data['ind-estado'] == 'rechazado')
			{
				$documento->estado_id = 7; // Rechazado Hacienda
				$type = 'danger';
				$titulo = "Error <hr class=\"kv-alert-separator\">";
				$actualizar = 1;	
				$to = [
					'caceresvega@gmail.com' => 'Juan Alberto Cáceres Vega',
					'hromancr@gmail.com' => 'Henry Ricardo Román Solis',
					'hroman@softwaresolutions.co.cr' => 'Henry Ricardo Román Solis',					
				];				
				//$to = 'caceresvega@gmail.com';
				$from = 'portal@gmail.com';				
				$asunto = 'Portal Error en Respuesta de Documento: '.$documento->clave.'-'.$documento->consecutivo;
				$xml_respuesta_hacienda = base64_decode($data['respuesta-xml']);
				$mensaje = "El comprobante electrónico con clave: [".$documento->clave.'-'.$documento->consecutivo."] fue rechazado por Hacienda. <br >Por la siguiente causa ". $xml_respuesta_hacienda.'<br >Revise el archivo xml de respuesta de Hacienda para más detalles';

				// Crear el xml de respuesta
				$nombre_archivo = $documento->getTipoDocumento().'-MH-'.$documento->clave.'-'.$documento->consecutivo.'.xml';
				$path = Yii::getAlias('@backend/web/xmlh/'.$nombre_archivo);
				file_put_contents($path, $xml_respuesta_hacienda);
				$documento->url_ahc = $nombre_archivo;
				$documento->save();


				//$cuerpo = '<pre>'.base64_decode($xml).'</pre>';
				$mensage = Yii::$app->mailer->compose("layouts/html", ['content'=>$xml_respuesta_hacienda])
					->setTo($to)
					->setFrom($from)
					//->setCc($arr_cc)
					->setSubject($asunto)
					->setHtmlBody($xml_respuesta_hacienda);
				$mensage->send();	
			}
			else
			if ($data['ind-estado'] == 'aceptado'){
				$mensaje = "El comprobante electrónico con clave: [".$documento->clave.'-'.$documento->consecutivo."] fue aceptada por Hacienda. "."<br >Revise el archivo xml de respuesta de Hacienda para más detalles";
				switch ($documento->estado_id)
				{
					case 2: $documento->estado_id = 5; // Aceptado Hacienda
							break;
					case 3: $documento->estado_id = 6; // Aceptado Parcial Hacienda
							break;
					case 4: $documento->estado_id = 7; // Rechazado Hacienda
							break;
				}
				$documento->save();				
				
				$type = 'success';
				$titulo = "Informaci&oacute;n <hr class=\"kv-alert-separator\">";	
				$actualizar = 1;	

				$xml_respuesta_hacienda = base64_decode($data['respuesta-xml']);
				$mensaje = "El comprobante electrónico con clave: [".$documento->clave.'-'.$documento->consecutivo."] fue aceptado por Hacienda. Revise el archivo xml de respuesta de Hacienda para más detalles";
				
				// Crear el xml de respuesta
				$nombre_archivo = $documento->getTipoDocumento().'-MH-'.$documento->clave.'-'.$documento->consecutivo.'.xml';
				$path = Yii::getAlias('@backend/web/xmlh/'.$nombre_archivo);
				file_put_contents($path, $xml_respuesta_hacienda);
				$documento->url_ahc = $nombre_archivo;
				$documento->save();				
			}
			else
			if ($data['ind-estado'] == 'recibido'){
				$mensaje = "El comprobante electrónico con clave: [".$documento->clave.'-'.$documento->consecutivo."] aún se encuentra en estado Recibido. Inténtelo más tarde";
				$type = 'warning';
				$titulo = "Advertencia <hr class=\"kv-alert-separator\">";	
				$actualizar = 0;				
			}	
			else
			if ($data['ind-estado'] == 'error'){
				$mensaje = "Error";
				$type = 'warning';
				$titulo = "Advertencia <hr class=\"kv-alert-separator\">";	
				$actualizar = 0;				
			}			
			else
			{
				$mensaje = "El comprobante electrónico con clave: [".$documento->clave.'-'.$documento->consecutivo."]. Tiene el siguiente error";	
//				$mensaje .= 
				$type = 'warning';
				$titulo = "Informaci&oacute;n <hr class=\"kv-alert-separator\">";	
			}			
		}
		else
		{
			$mensaje = $response->content;	
			$type = 'danger';
			$titulo = "Error <hr class=\"kv-alert-separator\">";	
		}			  
		return ['mensaje' => $mensaje, 'type'=>$type, 'titulo'=>$titulo, 'actualizar'=>$actualizar];			
	}	
}
