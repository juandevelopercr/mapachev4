<?php

use yii\db\Migration;

class m210109_031002_create_table_product_has_supplier extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            '{{%product_has_supplier}}',
            [
                'product_id' => $this->integer()->notNull(),
                'supplier_id' => $this->integer()->notNull(),
                'physical_location' => $this->text(),
            ],
            $tableOptions
        );

        $this->addPrimaryKey('PRIMARYKEY_product_has_supplier', '{{%product_has_supplier}}', ['product_id', 'supplier_id']);

        $this->addForeignKey(
            'fk_product_has_supplier_product1',
            '{{%product_has_supplier}}',
            ['product_id'],
            '{{%product}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_product_has_supplier_supplier1',
            '{{%product_has_supplier}}',
            ['supplier_id'],
            '{{%supplier}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
    }

    public function down()
    {
        $this->dropTable('{{%product_has_supplier}}');
    }
}
