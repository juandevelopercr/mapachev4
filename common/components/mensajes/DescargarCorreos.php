<?php
namespace common\components\mensajes;
use Yii;
use backend\models\settings\Issuer;

	include('PHPMailer/src/PHPMailer.php');
	include('PHPMailer/src/SMTP.php');

	set_time_limit(3000);

class DescargarCorreos{

	public function __construct(){

	}
	//////////////////////////////////////////////////////////////
	//	Descargar correos
	//////////////////////////////////////////////////////////////
	public function descargaCorreos(){
		$emisores = Issuer::find()->all();		
		foreach ($emisores as $e){				
			if (!is_null($e->host_smpt) && !is_null($e->user_smtp) && !is_null($e->pass_smtp) && !is_null($e->puerto_smpt) && !empty($e->host_smpt) && !empty($e->user_smtp) && !empty($e->pass_smtp) && !empty($e->puerto_smpt))
			{						
				//{facturaelectronicacrc.com:143/notls}INBOX				
				$host_smpt = '{'.trim($e->host_smpt).":".$e->puerto_smpt."/".$e->smtp_encryptation."}INBOX";
				$this->DownloadCorreos($host_smpt, $e->user_smtp, $e->pass_smtp, $e->email_notificacion_smtp, $e);
				// Verificar tambien en la carpeta SPAM
				$host_smpt_spam = '{'.trim($e->host_smpt).":".$e->puerto_smpt."/".$e->smtp_encryptation."}SPAM";
				$this->DownloadCorreos($host_smpt_spam, $e->user_smtp, $e->pass_smtp, $e->email_notificacion_smtp, $e);
			}
		}
	}


	public function DownloadCorreos($hostname, $username, $password, $email_notificacion_smtp, $emisor){
			//$numDelete=1;
			/*
			$hostname = '{softwaresolutions.co.cr:143/notls}INBOX';
			$username = 'prueba@softwaresolutions.co.cr';//fe.aceptadas
			$password = 'Pru2019cr@';//
			*/
			/*
			$hostname = '{facturaelectronicacrc.com:143/notls}INBOX';
			$username = 'test2@facturaelectronicacrc.com';//fe.aceptadas
			$password = 'jA26mE1K';//
			*/
			
			//$ruta=__DIR__."/Documentos_Descargados/";
			$ruta = Yii::getAlias("@backend/web/uploads/smtp/descargados/");

			/* try to connect */
			$inbox = imap_open($hostname,$username,$password) or exit;//die('No se puede conectar con webmail: ' . imap_last_error());
			$emails = imap_search($inbox, "ALL");//'FROM "mariorubi7@yahoo.com"'
			
			$totalMsj=imap_num_msg($inbox);
			if($emails) {
				$count = 1;

				rsort($emails);
				foreach($emails as $email_number) 
				{
					$message = imap_fetchbody($inbox,$email_number,2);
					$structure = imap_fetchstructure($inbox, $email_number);
					$attachments = array();

					$header = imap_header($inbox, $email_number);
					
					$remitente=imap_utf8($header->from[0]->mailbox.'@'.$header->from[0]->host);
					//echo imap_utf8($header->from[0]->personal)."2<br>";
					$destinatario=@imap_utf8($header->to[0]->mailbox);
					$subject=$fwd=imap_utf8($header->subject);
					//echo $m=imap_utf8($header->message_id)."5---<br>";
					//echo $m=imap_utf8($header->udate)."6<br>";

					//////////////////////////////////////////////////////////////////////
					//
					//////////////////////////////////////////////////////////////////////
					if(isset($structure->parts) && count($structure->parts)){

						for($i = 0; $i < count($structure->parts); $i++){

							if(strtolower($structure->subtype)== "mixed"){
								$attachments[$i] = array(
									'is_attachment' => false,
									'filename' => '',
									'name' => '',
									'attachment' => ''
								);
								///////////////////////
								//
								///////////////////////
								if($structure->parts[$i]->ifdparameters){
									foreach($structure->parts[$i]->dparameters as $object){
										if(strtolower($object->attribute) == 'filename') 
										{
											$attachments[$i]['is_attachment'] = true;
											$attachments[$i]['filename'] = $object->value;
											$attachments[$i]['subtype']=$structure->parts[$i]->encoding;
										}
									}
								}
								///////////////////////
								//
								///////////////////////
								if($structure->parts[$i]->ifparameters){
									foreach($structure->parts[$i]->parameters as $object) 
									{
										if(strtolower($object->attribute) == 'name') 
										{
											$attachments[$i]['is_attachment'] = true;
											$attachments[$i]['name'] = $object->value;
											$attachments[$i]['subtype']=$structure->parts[$i]->encoding;
										}
									}
								}

								////////////////////////
								//
								////////////////////////

								if($attachments[$i]['is_attachment']){
									$attachments[$i]['attachment'] = imap_fetchbody($inbox, $email_number, $i+1);

									/* 3 = BASE64 encoding */
									if($structure->parts[$i]->encoding == 3){ 
										$attachments[$i]['attachment'] = base64_decode($attachments[$i]['attachment']);
									}

									/* 4 = QUOTED-PRINTABLE encoding */
									elseif($structure->parts[$i]->encoding == 4){ 
										$attachments[$i]['attachment'] = quoted_printable_decode($attachments[$i]['attachment']);
									}
								}
							}

							if(strtolower($structure->subtype)== "alternative"){
								//echo "Porfavor reenviar este correo. <br>";
							}
						}
					}
					//////////////////////////////////////////////////////////////////////
					//
					//////////////////////////////////////////////////////////////////////
					$check = false;
					if(!empty($attachments)){
						foreach($attachments as $ata){
							if($ata['filename'] != "" && $ata['name'] != "")
							{
								if($ata['filename'] == ""){$ata['filename'] = $ata['name'];}

								$file = imap_utf8($ata['filename']);
								
								$pos = strrpos($file, ".");
									if($pos == true){
										$ext=strtolower(substr($file, $pos+1,3));
									}//echo $ext;
								if($ext=="pdf" || $ext=="xml"){$check=true; break;}
							}
						}
					}
					if(!$check){ $attachments = array();}		


					$control = false;
					if(!empty($attachments)){

						$dif=0;
						foreach($attachments as $attachment){

							$dif+=1;

							if($attachment['is_attachment'] == 1){

								$filename = imap_utf8($attachment['filename']);

								//Extraigo la extención
								$pos = strrpos($filename, ".");

								if($pos==true){ 
									$ext=strtolower(substr($filename, $pos+1,3));
								}

								//echo $ext;
								if($ext=="pdf" || $ext=="xml"){// 

									if($ext=="xml"){ $control = true;}

									if(empty($filename)){$filename = $attachment['filename'];}

									if(empty($filename)){$filename = time() . ".dat";}

									if($ext=="xml"){
										$elfile=imap_utf8($attachment['attachment']);
										@$b64XMl=simplexml_load_string($elfile);
									}

									if((isset($b64XMl) && isset($b64XMl->Receptor) && isset($b64XMl->Receptor->Identificacion) && 
										isset($b64XMl->Receptor->Identificacion->Numero)) || $ext=="pdf"){
										//die(var_dump($b64XMl));
										//$b64XMl->Receptor->Identificacion->Numero;
										$folder = $ruta.$email_number;

										if(!is_dir($folder)){
											@mkdir($folder);
										}

										$fp = fopen($folder."/".$email_number."-"."(".$dif.")".date("Y-m-d").".".$ext, "w+");
										
										$escribe=fwrite($fp, imap_utf8($attachment['attachment']));

										fclose($fp);

									}

								}
							}

						}
					}else{
						//echo "NO TIENE ADJUNTOS<BR><BR>";
						usleep(377);
						//imap_mail_move($inbox,$email_number,'INBOX.REVISAR');
						if (!imap_mail_move($inbox,$email_number,'REVISAR'))
							imap_mail_move($inbox,$email_number,'INBOX.REVISAR');
						imap_expunge($inbox); 						

						$asunto = "Archivos Adjuntos No encontrados";
						$msg="El correo electrónico no tiene adjuntos.<br />Revise el correo electrónico con asunto: ".$subject." ". ' '. "que se encuentra en el buzón REVISAR";

						//$this->enviarEmail($asunto, $msg, $email_notificacion_smtp, $emisor);
					}


					if(!$check){
						//echo "Adjuntos NO son XML ni PDF<BR><BR>";
						usleep(377);
						//imap_mail_move($inbox,$email_number,'INBOX.REVISAR');
						if (!imap_mail_move($inbox,$email_number,'REVISAR'))
							imap_mail_move($inbox,$email_number,'INBOX.REVISAR');
						imap_expunge($inbox); 

						$asunto = "Archivo xml Inexistente";
						$msg="Los adjuntos en el correo electrónico no son de tipo XML válido.<br />Revise el correo electrónico con asunto: ".$subject." ". ' '. "que se encuentra en el buzón REVISAR";

						//$this->enviarEmail($asunto, $msg, $email_notificacion_smtp, $emisor);
						 
					}else{
						usleep(377);
						//imap_mail_move($inbox,$email_number,'INBOX.CORRECTAS');		
						if (!imap_mail_move($inbox,$email_number,'CORRECTAS'))
							imap_mail_move($inbox,$email_number,'INBOX.CORRECTAS');
						imap_expunge($inbox); 						
					}

					
				}//echo $salida;
			}
			imap_expunge($inbox); 
			imap_close($inbox);
			//echo "<BR>- $totalMsj Correos descargados.<BR>";
	}
		
	public function enviarEmail($asunto, $msg, $email_notificacion_smtp, $emisor)
	{
		if (!is_null($emisor))
		{
			$str = "<table width=\"70%\" align=\"center\" style=\"border-collapse:collapse;\">
				<tr>
					<td align=\"center\"> 
						<hr />
						<br />        
						<h1 style=\"text-align:center; color:#3157F2\"><a href=\"https://herbavicr.com\" style=\"color:#3157F2; text-decoration:none;\">herbavicr.com</a></h1>
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
			$to = $email_notificacion_smtp;
			$from = 'facturas@herbavicr.com'; 	
			$subject = $asunto;

			$mensage = Yii::$app->mailer->compose("layouts/html", ['content'=>$str])
							->setTo($to)
							->setFrom($from)
							//->setCc($arr_cc)
							->setSubject($subject)
							->setTextBody($str)
							->setHtmlBody($str);
			if ($mensage->send()) 
				$respuesta = true;
			else
				$respuesta = false;
			
			return $respuesta;
		}
	}	
	
	/*
	public function enviarEmail($msg){
		$mail= new PHPMailer();
	
		$hostSMTP='mail.sudominio.com';
		$userSMTP='cont@sudominio.com'; //cuenta de correo acargo de envio de la factura
		$claveSMTP='clave correo';
		$fromSMTP='cont@sudominio.com';
		$fromNameSMTP='Notificación automática de aceptación de facturas.';
		$ccSMTP='cont@sudominio.com'; //Correo donde se recibiran copia de las Facturas para efecto de respaldo
		$nombreCcSMTP='Arlene Solis';

		$ccSMTP2='mariorubi7@yahoo.com'; //Correo donde se recibiran copia2 de las Facturas para efecto de respaldo
		$nombreCcSMTP2='Mario Rubí';
		
		$hostSMTP='softwaresolutions.co.cr';
		$userSMTP='prueba@softwaresolutions.co.cr'; //cuenta de correo acargo de envio de la factura
		$claveSMTP='Pru2019cr@';
		
		$fromSMTP='cont@sudominio.com';
		$fromNameSMTP='Notificación automática de aceptación de facturas.';
		$ccSMTP='caceresvega@gmail.com'; //Correo donde se recibiran copia de las Facturas para efecto de respaldo
		$nombreCcSMTP='Juan Alberto';

		$ccSMTP2='caceres@nauta.cu'; //Correo donde se recibiran copia2 de las Facturas para efecto de respaldo
		$nombreCcSMTP2='Juan';		
		
		//$mail->SMTPDebug = 2;
		//$mail->isSMTP();
		$mail->Host = $hostSMTP;
		$mail->SMTPAuth = true;
		$mail->Username = $userSMTP;
		$mail->Password = $claveSMTP;
		$mail->SMTPSecure = 'ssl';
		$mail->Port = 465; 

		$mail->From     = $fromSMTP;
		$mail->FromName = $fromNameSMTP; 

		$mail->addAddress($ccSMTP2,$nombreCcSMTP2);
		//$mail->addCC($ccSMTP,$nombreCcSMTP);

		$mail->ErrorInfo;   
		$mail->WordWrap= 50; 
		$mail->IsHTML(true);     
		$mail->CharSet= 'UTF-8';
		$mail->Subject=  "Notificación";
		$mail->Body=$msg;

		if($mail->Send()){
			echo "Bien";
		}else{
			echo "Mal";
		}
	}
	*/	
		
}
/*
$correos=new Correos();
$correos->descargagCorreos();
*/
?>