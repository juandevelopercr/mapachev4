<?php

use yii\db\Migration;

/**
 * Class m210102_204459_add_field_percent_to_table_tax_rate_type
 */
class m210102_204459_add_field_percent_to_table_tax_rate_type extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('tax_rate_type','percent',$this->decimal(5,2));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('tax_rate_type','percent');
    }

}
