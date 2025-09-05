<?php

use yii\db\Migration;

/**
 * Class m210130_050147_change_field_of_currency
 */
class m210130_050147_change_field_of_currency extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('currency','change_type', $this->decimal(18,5));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn('currency','change_type', $this->decimal(10,5));
    }
}
