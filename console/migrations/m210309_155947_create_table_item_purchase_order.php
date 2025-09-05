<?php

use yii\db\Migration;

/**
 * Class m210309_155947_create_table_item_purchase_order
 */
class m210309_155947_create_table_item_purchase_order extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            '{{%item_purchase_order}}',
            [
                'id' => $this->primaryKey(),
                'purchase_order_id' => $this->integer(),
                'code' => $this->string(),
                'description' => $this->string(),
                'product_id' => $this->integer(),
                'service_id' => $this->integer(),
                'quantity' => $this->decimal(18, 5),
                'price_unit' => $this->decimal(18, 5),
                'subtotal' => $this->decimal(18, 5),
                'tax_amount' => $this->decimal(18, 5),
                'discount_amount' => $this->decimal(18,5),
                'exonerate_amount' => $this->decimal(18,5),
                'price_total' => $this->decimal(18,5),
                'user_id' => $this->integer(),
                'price_type' => $this->integer(),
                'unit_type_id' => $this->integer(),
                'created_at' => $this->dateTime(),
                'updated_at' => $this->dateTime(),
            ],
            $tableOptions
        );

        $this->addForeignKey(
            'fk_item_purchase_order_purchase_order1',
            '{{%item_purchase_order}}',
            ['purchase_order_id'],
            '{{%purchase_order}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_item_purchase_order_product1',
            '{{%item_purchase_order}}',
            ['product_id'],
            '{{%product}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_item_purchase_order_service1',
            '{{%item_purchase_order}}',
            ['service_id'],
            '{{%service}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_item_purchase_order_user1',
            '{{%item_purchase_order}}',
            ['user_id'],
            '{{%user}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_item_purchase_order_unit_type1',
            '{{%item_purchase_order}}',
            ['unit_type_id'],
            '{{%unit_type}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
    }

    public function down()
    {
        $this->dropTable('{{%item_purchase_order}}');
    }
}
