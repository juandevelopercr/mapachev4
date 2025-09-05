<?php
namespace common\models;

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
        ];
    }
}
