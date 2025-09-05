<?php
namespace backend\modules\reportes\models;

use yii\base\InvalidParamException;
use yii\base\Model;
use Yii;

/**
 * Password reset form
 */
class InventarioReportForm extends Model
{
    public $family;
    public $category;
	public $country;	
    public $tipo;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
			[['family', 'category', 'country', 'tipo'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'family' => 'Familia',
            'category' => 'Categoría',
            'country' => 'País',
            'tipo'=> 'Tipo de inventario',
        ];
    }

}
