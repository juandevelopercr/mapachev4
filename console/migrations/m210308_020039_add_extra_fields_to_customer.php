<?php

use yii\db\Migration;

/**
 * Class m210308_020039_add_extra_fields_to_customer
 */
class m210308_020039_add_extra_fields_to_customer extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%customer}}','route_transport_id', $this->integer());
        $this->addColumn('{{%customer}}','pre_invoice_type', $this->integer());

        $this->addForeignKey(
            'fk_customer_route_transport1',
            '{{%customer}}',
            ['route_transport_id'],
            '{{%route_transport}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%customer}}','route_transport_id');
        $this->dropColumn('{{%customer}}','pre_invoice_type');
    }
}
