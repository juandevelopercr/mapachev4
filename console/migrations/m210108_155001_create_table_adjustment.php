<?php

use yii\db\Migration;

/**
 * Class m210108_155001_create_table_adjustment
 */
class m210108_155001_create_table_adjustment extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            '{{%adjustment}}',
            [
                'id' => $this->primaryKey(),
                'consecutive' => $this->string(),
                'product_id' => $this->integer(),
                'type' => $this->integer(),
                'past_quantity' => $this->decimal(15, 2),
                'entry_quantity' => $this->decimal(15, 2),
                'new_quantity' => $this->decimal(15, 2),
                'observations' => $this->text(),
                'user_id' => $this->integer(),
                'origin_branch_office_id' => $this->integer(),
                'target_branch_office_id' => $this->integer(),
                'invoice_number' => $this->string(),
                'created_at' => $this->dateTime(),
                'updated_at' => $this->dateTime(),

            ],
            $tableOptions
        );

        $this->addForeignKey(
            'fk_adjustment_product1',
            '{{%adjustment}}',
            ['product_id'],
            '{{%product}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_adjustment_user1',
            '{{%adjustment}}',
            ['user_id'],
            '{{%user}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_adjustment_branch_office1',
            '{{%adjustment}}',
            ['origin_branch_office_id'],
            '{{%branch_office}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_adjustment_branch_office2',
            '{{%adjustment}}',
            ['target_branch_office_id'],
            '{{%branch_office}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
    }

    public function down()
    {
        $this->dropTable('{{%adjustment}}');
    }
}
