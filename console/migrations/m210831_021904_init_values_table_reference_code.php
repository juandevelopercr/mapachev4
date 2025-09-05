<?php

use yii\db\Migration;
use backend\models\nomenclators\ReferenceCode;

/**
 * Class m210831_021904_init_values_table_reference_code
 */
class m210831_021904_init_values_table_reference_code extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        /* Reference Codes */
        $array_reference_codes = [
            ['01', 'Anula Documento de Referencia'],
            ['02', 'Corrige texto documento de referencia'],
            ['03', 'Corrige monto'],
            ['04', 'Referencia a otro documento'],
            ['05', 'Sustituye comprobante provisional por contingencia'],
            ['99', 'Otros'],
        ];
        foreach ($array_reference_codes AS $key=> $condition)
        {
            $model = new ReferenceCode(['code' => $condition[0],'name' => $condition[1]]);
            if(!$model->save())
            {
                print_r($model->getErrors());
                return false;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        ReferenceCode::deleteAll();
    }
}
