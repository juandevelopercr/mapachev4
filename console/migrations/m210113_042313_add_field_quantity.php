<?php

use yii\db\Migration;

/**
 * Class m210113_042313_add_field_quantity
 */
class m210113_042313_add_field_quantity extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('product_has_branch_office','quantity', $this->decimal(15,2));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('product_has_branch_office','quantity');
    }

}
