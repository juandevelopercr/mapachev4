<?php

use yii\db\Migration;

/**
 * Class m210217_023659_add_field_status_payment_to_table_payment_order
 */
class m210217_023659_add_field_status_payment_to_table_payment_order extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%payment_order}}','payout_status', $this->integer());
        $this->addColumn('{{%payment_order}}','is_editable', $this->boolean());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%payment_order}}','payout_status');
        $this->dropColumn('{{%payment_order}}','is_editable');
    }
}
