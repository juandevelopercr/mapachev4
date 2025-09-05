<?php

use yii\db\Migration;

/**
 * Class m210204_024849_add_field_supplier_code_to_table_product
 */
class m210204_024849_add_field_supplier_code_to_table_product extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('product','supplier_code', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('product','supplier_code');
    }
}
