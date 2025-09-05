<?php
namespace backend\modules\reportes\models;

use yii\base\InvalidParamException;
use yii\base\Model;
use Yii;

/**
 * Password reset form
 */
class SinpeReportForm extends Model
{
    public $fecha;
    public $cliente;
	public $seller;	
    public $collector;	
    public $bank;	
    public $estado;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['fecha'], 'required'],
			[['fecha', 'cliente', 'collector', 'bank', 'estado'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'fecha' => 'Rango de Fechas de abonos',
            'cliente' => 'Cliente',
            'bank' => 'Banco',
            'collector'=> 'Agente Cobrador',
            'estado'=> 'Estado',
        ];
    }

}
