<?php

use yii\db\Migration;

/**
 * Class m210723_043325_add_fields_to_table_invoice
 */
class m210723_043325_add_fields_to_table_invoice extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('invoice','ready_to_send_email', $this->tinyInteger()->defaultValue('0'));
        $this->addColumn('invoice','email_sent', $this->tinyInteger()->defaultValue('0'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('invoice','ready_to_send_email');
        $this->dropColumn('invoice','email_sent');
    }
}
