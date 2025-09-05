<?php

use yii\db\Migration;

/**
 * Class m210520_232757_add_fields_to_table_invoice
 */
class m210520_232757_add_fields_to_table_invoice extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('invoice','status_hacienda',$this->integer());
        $this->addColumn('invoice','collector_id',$this->integer());
        $this->addColumn('invoice','route_transport_id',$this->integer());

        $this->addForeignKey(
            'fk_invoice_collector',
            '{{%invoice}}',
            ['collector_id'],
            '{{%user}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_invoice_route_transport1',
            '{{%invoice}}',
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
        $this->dropColumn('invoice','status_hacienda');
        $this->dropColumn('invoice','collector_id');
        $this->dropColumn('invoice','route_transport_id');
    }
}
