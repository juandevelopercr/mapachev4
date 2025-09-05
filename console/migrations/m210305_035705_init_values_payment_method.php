<?php

use yii\db\Migration;
use backend\models\nomenclators\PaymentMethod;

/**
 * Class m210305_035705_init_values_payment_method
 */
class m210305_035705_init_values_payment_method extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $array = [
            [1, '01', 'Efectivo'],
            [2, '02', 'Tarjeta'],
            [3, '03', 'Cheque'],
            [4, '04', 'Transferencia – depósito bancario'],
            [5, '05', 'Recaudado por terceros'],
            [6, '99', 'Otros (se debe indicar el medio de pago)']
        ];

        foreach ($array AS $index => $value)
        {
            $model = new PaymentMethod();
            $model->code = $value[1];
            $model->name = $value[2];
            if(!$model->save())
            {
                print_r($model->getFirstErrors());
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        PaymentMethod::deleteAll();
    }
}
