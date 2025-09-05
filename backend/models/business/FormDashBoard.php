<?php
namespace backend\models\business;

use Yii;
use yii\base\Model;

class FormDashBoard extends Model
{
    public $anno;    
    public $mes; 
    public $moneda;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['anno', 'mes', 'moneda'], 'required'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'anno' => 'Año', 
            'mes'=> 'Mes',
            'moneda'=> 'Moneda',           
        ];
    }
	
}