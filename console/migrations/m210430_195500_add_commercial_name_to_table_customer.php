<?php

use yii\db\Migration;

/**
 * Class m210430_195500_add_commercial_name_to_table_customer
 */
class m210430_195500_add_commercial_name_to_table_customer extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('customer','commercial_name',$this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('customer','commercial_name');
    }

}
