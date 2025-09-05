<?php

use yii\db\Migration;

/**
 * Class m210504_021134_create_table_payment_method_has_invoice
 */
class m210504_021134_create_table_payment_method_has_invoice extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            '{{%payment_method_has_invoice}}',
            [
                'invoice_id' => $this->integer()->notNull(),
                'payment_method_id' => $this->integer()->notNull(),
            ],
            $tableOptions
        );

        $this->addPrimaryKey('PRIMARYKEY_payment_method_has_invoice', '{{%payment_method_has_invoice}}', ['invoice_id', 'payment_method_id']);

        $this->addForeignKey(
            'fk_payment_method_has_invoice_invoice1',
            '{{%payment_method_has_invoice}}',
            ['invoice_id'],
            '{{%invoice}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_payment_method_has_invoice_payment_method1',
            '{{%payment_method_has_invoice}}',
            ['payment_method_id'],
            '{{%payment_method}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
    }

    public function down()
    {
        $this->dropTable('{{%payment_method_has_invoice}}');
    }
}
