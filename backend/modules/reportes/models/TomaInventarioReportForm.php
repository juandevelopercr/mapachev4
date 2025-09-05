<?php
namespace backend\modules\reportes\models;

use yii\base\InvalidParamException;
use yii\base\Model;
use Yii;

/**
 * Password reset form
 */
class TomaInventarioReportForm extends Model
{
    public $family;
    public $category;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
			[['family', 'category'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'family' => 'Familia',
            'category' => 'Categoría',
        ];
    }

}
