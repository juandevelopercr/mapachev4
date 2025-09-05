<?php

use yii\db\Migration;

/**
 * Class m210305_040950_create_table_payment_method_has_proforma
 */
class m210305_040950_create_table_payment_method_has_proforma extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            '{{%payment_method_has_proforma}}',
            [
                'proforma_id' => $this->integer()->notNull(),
                'payment_method_id' => $this->integer()->notNull(),
            ],
            $tableOptions
        );

        $this->addPrimaryKey('PRIMARYKEY_payment_method_has_proforma', '{{%payment_method_has_proforma}}', ['proforma_id', 'payment_method_id']);

        $this->addForeignKey(
            'fk_payment_method_has_proforma_proforma1',
            '{{%payment_method_has_proforma}}',
            ['proforma_id'],
            '{{%proforma}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_payment_method_has_proforma_payment_method1',
            '{{%payment_method_has_proforma}}',
            ['payment_method_id'],
            '{{%payment_method}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
    }

    public function down()
    {
        $this->dropTable('{{%payment_method_has_proforma}}');
    }
}
