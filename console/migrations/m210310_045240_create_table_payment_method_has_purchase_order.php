<?php

use yii\db\Migration;

/**
 * Class m210310_045240_create_table_payment_method_has_purchase_order
 */
class m210310_045240_create_table_payment_method_has_purchase_order extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            '{{%payment_method_has_purchase_order}}',
            [
                'purchase_order_id' => $this->integer()->notNull(),
                'payment_method_id' => $this->integer()->notNull(),
            ],
            $tableOptions
        );

        $this->addPrimaryKey('PRIMARYKEY_payment_method_has_purchase_order', '{{%payment_method_has_purchase_order}}', ['purchase_order_id', 'payment_method_id']);

        $this->addForeignKey(
            'fk_payment_method_has_purchase_order_purchase_order1',
            '{{%payment_method_has_purchase_order}}',
            ['purchase_order_id'],
            '{{%purchase_order}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_payment_method_has_purchase_order_payment_method1',
            '{{%payment_method_has_purchase_order}}',
            ['payment_method_id'],
            '{{%payment_method}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
    }

    public function down()
    {
        $this->dropTable('{{%payment_method_has_purchase_order}}');
    }
}
