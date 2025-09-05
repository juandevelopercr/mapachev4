<?php

use yii\db\Migration;

/**
 * Class m210305_040941_create_table_payment_method_has_po
 */
class m210305_040941_create_table_payment_method_has_po extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            '{{%payment_method_has_payment_order}}',
            [
                'payment_order_id' => $this->integer()->notNull(),
                'payment_method_id' => $this->integer()->notNull(),
            ],
            $tableOptions
        );

        $this->addPrimaryKey('PRIMARYKEY_payment_method_has_payment_order', '{{%payment_method_has_payment_order}}', ['payment_order_id', 'payment_method_id']);

        $this->addForeignKey(
            'fk_payment_method_has_payment_order_payment_order1',
            '{{%payment_method_has_payment_order}}',
            ['payment_order_id'],
            '{{%payment_order}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_payment_method_has_payment_order_payment_method1',
            '{{%payment_method_has_payment_order}}',
            ['payment_method_id'],
            '{{%payment_method}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
    }

    public function down()
    {
        $this->dropTable('{{%payment_method_has_payment_order}}');
    }
}
