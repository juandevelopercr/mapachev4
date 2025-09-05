<?php

use yii\db\Migration;
use backend\models\nomenclators\Department;
use backend\models\nomenclators\JobPosition;

/**
 * Class m210102_012718_init_values_tables_related_suppliers
 */
class m210102_012718_init_values_tables_related_suppliers extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        /* Department */
        $array_dptos = [
            ['01', 'Administrativo'],
            ['02', 'Contabilidad'],
            ['03', 'Proveeduría'],
            ['04', 'Ventas'],
        ];
        foreach ($array_dptos AS $dpto)
        {
            $model = new Department(['status' => 1,'code' => $dpto[0], 'name' => $dpto[1]]);
            if(!$model->save())
            {
                print_r($model->getErrors());
                return false;
            }
        }

        /* JobPosition */
        $array_job = [
            ['01', 'Ingeniero'],
            ['02', 'Gerente'],
            ['03', 'Contador'],
            ['04', 'Vendedor'],
        ];
        foreach ($array_job AS $job)
        {
            $model = new JobPosition(['status' => 1,'code' => $job[0], 'name' => $job[1]]);
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
        Department::deleteAll();
        JobPosition::deleteAll();
    }
}
