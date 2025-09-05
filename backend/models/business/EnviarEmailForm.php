<?php
namespace backend\models\business;
use yii\base\InvalidParamException;
use yii\base\Model;
use Yii;
/**
 * Password reset form
 */
class EnviarEmailForm extends Model
{
    public $de;
    public $para;
    public $cc;
    public $nombrearchivo;
    public $archivo;
    public $asunto;
    public $cuerpo;
	public $id;
	public $ids;	
	public $estado_id;
	public $tipo;
	public $receptor;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
			[['de', 'para', 'nombrearchivo', 'archivo', 'asunto'], 'required'],
        ];
    }
    public function attributeLabels()
    {
        return [
            'de' => 'De',
            'para' => 'Para',
            'nombrearchivo' => 'Nombre de Archivo',
            'archivo' => 'Archivo',
            'asunto' => 'Asunto',
            'cuerpo' => 'Cuerpo',
			'cc'=> 'cc (Puede adicionar varios separados por ;)',
			'tipo'=> 'Tipo',
        ];
    }
    /**
     * Resets password.
     *
     * @return boolean if password was reset.
     */
	/*
    public function enviar($proformas = array())
    {
		$user = User::find()->where(['id'=>Yii::$app->user->id])->one();
        if (strlen(trim($this->cc)) > 0) {
            $arr_cc = explode(';', $this->cc);
        }
		else
			$arr_cc = array();
        $arr_cc[] = $user->email; // array('amhwolf.dimarzo@gmail.com');
        $direcciones_ok = true;
        foreach ($arr_cc as $ccs) {
            if (!filter_var($ccs, FILTER_VALIDATE_EMAIL)) {
                $direcciones_ok = false;
                break;
            }
        }
        if ($direcciones_ok == false) {
            $messageType = 'danger';
            $message = "<strong>Error!</strong> El correo no se pudo enviar, revise las direcciones de los destinatarios de copia ";
			Yii::$app->session->setFlash($messageType, $message);
            return false;
        }
        if ($direcciones_ok == true) {
            if (!filter_var($this->de, FILTER_VALIDATE_EMAIL)) {
                $direcciones_ok = false;
                $messageType = 'danger';
                $message = "<strong>Error!</strong> El correo no se pudo enviar, revise la direccion del remitente ";
                Yii::$app->session->setFlash($messageType, $message);
   	            return false;
            }
        }
        if ($direcciones_ok == true) {
            $to = explode(';', $this->para); // $cliente_contacto->email
            if (empty($this->from)) {
                $from = $user->email; //'proyectos@softwaresolutions.co.cr';
            }
			// Proceso de generar los archivos
			$ids = explode(',', $this->ids);
			$proformas = Proformas::find()->where(['id'=>$ids])->all();
			$model->para = '';
			foreach ($proformas as $p)
			{
				$archivo = 'proforma-'.$p->numero.'.pdf';
				$pdf[] = $this->ImprimirProformas($p->id, $destino = 'file', $archivo);
				if (empty($nombrearchivo))
					$nombrearchivo .= $archivo;
				else
					$nombrearchivo .= ', '.$archivo;
				$cliente_contacto = ClientesContactos::find()->where(['id'=>$p->cliente_contacto_id])->one();
				if (!is_null($cliente_contacto)) {
					$model->para .= empty($model->para) ? $cliente_contacto->email: ';'.$cliente_contacto->email;
				}

				$mensage = Yii::$app->mailer->compose("layouts/html", ['content'=>$this->cuerpo])
											->setTo($to)
											->setFrom($this->de)
											->setCc($arr_cc)
											->setSubject($this->asunto)
											->setHtmlBody($this->cuerpo);
				$mensage->attach($this->archivo, ['fileName'=>$this->nombrearchivo]);
				$mensage->send();
				if (!empty($proformas)) {
					foreach ($proformas as $p){
						$p->enviada = 1;
						$p->save();
					}
				}
			}
			// fin proceso de generar archivos
            $messageType = 'success';
            $message = "<strong>Bien hecho!</strong> El correo fue enviado satisfactoriamente ";
            Yii::$app->session->setFlash($messageType, $message);
			return true;
        }
    }
	*/
}
