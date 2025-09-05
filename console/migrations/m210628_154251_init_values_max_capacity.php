<?php

use yii\db\Migration;
use backend\models\business\PhysicalLocation;

/**
 * Class m210628_154251_init_values_max_capacity
 */
class m210628_154251_init_values_max_capacity extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $models = PhysicalLocation::find()->all();

        foreach ($models AS $key => $location)
        {
            $location->max_capacity = $location->quantity;
            $location->save(false);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        PhysicalLocation::updateAll(['max_capacity' => 0]);
    }

}
