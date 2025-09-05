<?php

use yii\db\Migration;

/**
 * Class m210628_151805_add_field_max_capacity
 */
class m210628_151805_add_field_max_capacity extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('physical_location','max_capacity',$this->decimal(18,5)->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('physical_location','max_capacity');
    }


}
