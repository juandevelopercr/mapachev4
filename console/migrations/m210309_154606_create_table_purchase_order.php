<?php

use yii\db\Migration;

/**
 * Class m210309_154606_create_table_purchase_order
 */
class m210309_154606_create_table_purchase_order extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            '{{%purchase_order}}',
            [
                'id' => $this->primaryKey(),
                'consecutive' => $this->string(),
                'branch_office_id' => $this->integer(),
                'customer_id' => $this->integer(),
                'credit_days_id' => $this->integer(),
                'condition_sale_id' => $this->integer(),
                'route_transport_id' => $this->integer(),
                'request_date' => $this->date(),
                'change_type' => $this->decimal(18,5),
                'currency_id' => $this->integer(),
                'status' => $this->integer(),
                'delivery_time' => $this->string(),
                'delivery_time_type' => $this->integer(),
                'discount_percent' => $this->decimal(18,5),
                'collector_id' => $this->integer(),
                'observations' => $this->text(),
                'is_editable' => $this->boolean(),
                'created_at' => $this->dateTime(),
                'updated_at' => $this->dateTime(),
            ],
            $tableOptions
        );

        $this->addForeignKey(
            'fk_purchase_order_branch_office1',
            '{{%purchase_order}}',
            ['branch_office_id'],
            '{{%branch_office}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_purchase_order_customer1',
            '{{%purchase_order}}',
            ['customer_id'],
            '{{%customer}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_purchase_order_condition_sale1',
            '{{%purchase_order}}',
            ['condition_sale_id'],
            '{{%condition_sale}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_purchase_order_credit_days',
            '{{%purchase_order}}',
            ['credit_days_id'],
            '{{%credit_days}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_purchase_order_currency',
            '{{%purchase_order}}',
            ['currency_id'],
            '{{%currency}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_purchase_order_collector',
            '{{%purchase_order}}',
            ['collector_id'],
            '{{%collector}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_purchase_order_route_transport1',
            '{{%purchase_order}}',
            ['route_transport_id'],
            '{{%route_transport}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );

    }

    public function down()
    {
        $this->dropTable('{{%purchase_order}}');
    }
}
