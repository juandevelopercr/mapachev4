<?php

use yii\db\Migration;

/**
 * Class m210130_022334_create_table_payment_order
 */
class m210130_022334_create_table_payment_order extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            '{{%payment_order}}',
            [
                'id' => $this->primaryKey(),
                'number' => $this->string(),
                'request_date' => $this->date(),
                'require_date' => $this->date(),
                'change_type' => $this->decimal(18,5),
                'observations' => $this->text(),
                'project_id' => $this->integer(),
                'supplier_id' => $this->integer(),
                'status_payment_order_id' => $this->integer(),
                'condition_sale_id' => $this->integer(),
                'credit_days_id' => $this->integer(),
                'currency_id' => $this->integer(),
                'created_at' => $this->dateTime(),
                'updated_at' => $this->dateTime(),
            ],
            $tableOptions
        );

        $this->addForeignKey(
            'fk_payment_order_project1',
            '{{%payment_order}}',
            ['project_id'],
            '{{%project}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_payment_order_supplier1',
            '{{%payment_order}}',
            ['supplier_id'],
            '{{%supplier}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_payment_order_condition_sale1',
            '{{%payment_order}}',
            ['condition_sale_id'],
            '{{%condition_sale}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_payment_order_credit_days',
            '{{%payment_order}}',
            ['credit_days_id'],
            '{{%credit_days}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_payment_order_currency',
            '{{%payment_order}}',
            ['currency_id'],
            '{{%currency}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
    }

    public function down()
    {
        $this->dropTable('{{%payment_order}}');
    }
}
