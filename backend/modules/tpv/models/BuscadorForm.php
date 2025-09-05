<?php
namespace backend\modules\tpv\models;

use yii\base\InvalidParamException;
use yii\base\Model;
use Yii;

/**
 * Password reset form
 */
class BuscadorForm extends Model
{
    public $customer_id;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
			[['customer_id'], 'required'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'customer_id' => 'Cliente',
        ];
    }

}
