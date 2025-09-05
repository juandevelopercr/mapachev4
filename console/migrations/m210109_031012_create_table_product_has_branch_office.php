<?php

use yii\db\Migration;

class m210109_031012_create_table_product_has_branch_office extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            '{{%product_has_branch_office}}',
            [
                'product_id' => $this->integer()->notNull(),
                'branch_office_id' => $this->integer()->notNull(),
            ],
            $tableOptions
        );

        $this->addPrimaryKey('PRIMARYKEY_product_has_branch_office', '{{%product_has_branch_office}}', ['product_id', 'branch_office_id']);

        $this->addForeignKey(
            'fk_product_has_branch_office_branch_office1',
            '{{%product_has_branch_office}}',
            ['branch_office_id'],
            '{{%branch_office}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_product_has_branch_office_product1',
            '{{%product_has_branch_office}}',
            ['product_id'],
            '{{%product}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
    }

    public function down()
    {
        $this->dropTable('{{%product_has_branch_office}}');
    }
}
