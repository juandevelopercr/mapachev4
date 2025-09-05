<?php

use yii\db\Migration;

/**
 * Class m210829_013803_create_table_payment_method_has_credit_note
 */
class m210829_013803_create_table_payment_method_has_credit_note extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            '{{%payment_method_has_credit_note}}',
            [
                'credit_note_id' => $this->integer()->notNull(),
                'payment_method_id' => $this->integer()->notNull(),
            ],
            $tableOptions
        );

        $this->addPrimaryKey('PRIMARYKEY_payment_method_has_credit_note', '{{%payment_method_has_credit_note}}', ['credit_note_id', 'payment_method_id']);

        $this->addForeignKey(
            'fk_payment_method_has_credit_note_credit_note1',
            '{{%payment_method_has_credit_note}}',
            ['credit_note_id'],
            '{{%credit_note}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_payment_method_has_credit_note_payment_method1',
            '{{%payment_method_has_credit_note}}',
            ['payment_method_id'],
            '{{%payment_method}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
    }

    public function down()
    {
        $this->dropTable('{{%payment_method_has_credit_note}}');
    }
}
