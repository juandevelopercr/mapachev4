<?php

use yii\db\Migration;

/**
 * Class m210302_193923_add_count_box_to_table_product
 */
class m210302_193923_add_count_box_to_table_product extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%product}}','quantity_by_box', $this->integer());
        $this->alterColumn('{{%product}}','min_quantity', $this->integer());
        $this->alterColumn('{{%product}}','max_quantity', $this->integer());
        $this->alterColumn('{{%product}}','package_quantity', $this->integer());

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%product}}','quantity_by_box');
        $this->alterColumn('{{%product}}','min_quantity', $this->decimal(18,5));
        $this->alterColumn('{{%product}}','max_quantity', $this->decimal(18,5));
        $this->alterColumn('{{%product}}','package_quantity', $this->decimal(18,5));
    }
}
