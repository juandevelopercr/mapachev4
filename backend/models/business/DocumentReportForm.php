<?php
namespace backend\models\business;

use yii\base\InvalidParamException;
use yii\base\Model;
use Yii;

/**
 * Password reset form
 */
class DocumentReportForm extends Model
{
    public $emisor;
    public $fecha;
	public $tipo;
	public $moneda;
	public $estado_id;
	

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
			[['fecha'], 'required'],
			[['emisor', 'fecha', 'tipo', 'tipo', 'moneda', 'estado_id'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'tipo_cambio' => 'Tipo de Cambio',
            'emisor' => 'Emisor',
            'fecha' => 'Rango de Fechas de Emisión',
			'tipo'=> 'Tipo de Documento',
			'moneda'=> 'Moneda',
			'estado_id'=> 'Estado',
        ];
    }

}
