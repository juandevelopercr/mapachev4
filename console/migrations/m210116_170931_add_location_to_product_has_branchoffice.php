<?php

use yii\db\Migration;

/**
 * Class m210116_170931_add_location_to_product_has_branchoffice
 */
class m210116_170931_add_location_to_product_has_branchoffice extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('product_has_branch_office','location', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('product_has_branch_office','location');
    }

}
