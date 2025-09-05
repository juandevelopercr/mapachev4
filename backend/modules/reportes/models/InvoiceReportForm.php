<?php
namespace backend\modules\reportes\models;

use yii\base\InvalidParamException;
use yii\base\Model;
use Yii;

/**
 * Password reset form
 */
class InvoiceReportForm extends Model
{
    public $fecha;
    public $cliente;
    public $tipo;	
    public $zona;
	public $seller;	
    public $collector;	
    public $estado;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['fecha'], 'required'],
			[['fecha', 'cliente', 'zona', 'seller', 'collector', 'estado', 'tipo'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'fecha' => 'Rango de Fechas de Emisión',
            'cliente' => 'Cliente',
            'zona' => 'Zona',
            'seller'=> 'Agente Vendedor',
            'collector'=> 'Agente Cobrador',
            'estado'=> 'Estado',
            'tipo' => 'Tipo',
        ];
    }
}