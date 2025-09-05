<?php

use yii\db\Migration;

/**
 * Class m210423_032227_delete_relations_seller_and_collector
 */
class m210423_032227_delete_relations_seller_and_collector extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        //PROFORMA
        $this->dropForeignKey('fk_proforma_seller','proforma');
        $this->dropColumn('proforma', 'seller_id');

        $this->addColumn('proforma', 'seller_id',$this->integer());
        $this->addForeignKey(
            'fk_proforma_user1',
            '{{%proforma}}',
            ['seller_id'],
            '{{%user}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );

        //PURCHASE ORDER
        $this->dropForeignKey('fk_purchase_order_collector','purchase_order');
        $this->dropColumn('purchase_order', 'collector_id');

        $this->addColumn('purchase_order', 'collector_id',$this->integer());
        $this->addForeignKey(
            'fk_purchase_order_user1',
            '{{%purchase_order}}',
            ['collector_id'],
            '{{%user}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );

        //CUSTOMER
        $this->dropForeignKey('fk_customer_collector1','customer');
        $this->dropColumn('customer', 'collector_id');

        $this->addColumn('customer', 'collector_id',$this->integer());
        $this->addForeignKey(
            'fk_customer_user1',
            '{{%customer}}',
            ['collector_id'],
            '{{%user}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );

        $this->addColumn('customer', 'seller_id',$this->integer());
        $this->addForeignKey(
            'fk_customer_user2',
            '{{%customer}}',
            ['seller_id'],
            '{{%user}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );

        //Eliminar las tablas collector y seller
        $this->dropTable('route_transport_has_collector');
        $this->dropTable('collector');
        $this->dropTable('seller');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m210423_032227_delete_relations_seller_and_collector cannot be reverted.\n";
    }

}
