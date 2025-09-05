<?php
namespace backend\modules\reportes\models;

use yii\base\InvalidParamException;
use yii\base\Model;
use Yii;

/**
 * Password reset form
 */
class LiquidacionReportForm extends Model
{
    public $fecha;
    public $cliente;
    public $collector;	
    public $estado;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['fecha', 'collector'], 'required'],
			[['fecha', 'cliente', 'collector', 'estado'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'fecha' => 'Fecha de Pago',
            'cliente' => 'Cliente',
            'collector'=> 'Agente Cobrador',
            'estado'=> 'Estado',
        ];
    }

}
