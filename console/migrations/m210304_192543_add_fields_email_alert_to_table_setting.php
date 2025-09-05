<?php

use yii\db\Migration;

/**
 * Class m210304_192543_add_fields_email_alert_to_table_setting
 */
class m210304_192543_add_fields_email_alert_to_table_setting extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%setting}}','product_price_change_mails', $this->text());
        $this->addColumn('{{%setting}}','proforma_stock_alert_mails', $this->text());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%setting}}','product_price_change_mails');
        $this->dropColumn('{{%setting}}','proforma_stock_alert_mails');
    }
}
