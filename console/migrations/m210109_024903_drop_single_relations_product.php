<?php

use yii\db\Migration;

/**
 * Class m210109_024903_drop_single_relations_product
 */
class m210109_024903_drop_single_relations_product extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropForeignKey('fk_product_supplier1','product');
        $this->dropColumn('product','supplier_id');

        $this->dropForeignKey('fk_product_branch_office1','product');
        $this->dropColumn('product','branch_office_id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m210109_024903_drop_single_relations_product cannot be reverted.\n";

        return true;
    }
}
