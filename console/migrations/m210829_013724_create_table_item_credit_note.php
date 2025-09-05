<?php

use yii\db\Migration;

/**
 * Class m210829_013724_create_table_item_credit_note
 */
class m210829_013724_create_table_item_credit_note extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            '{{%item_credit_note}}',
            [
                'id' => $this->primaryKey(),
                'credit_note_id' => $this->integer(),
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
            'fk_item_credit_note_credit_note1',
            '{{%item_credit_note}}',
            ['credit_note_id'],
            '{{%credit_note}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_item_credit_note_product1',
            '{{%item_credit_note}}',
            ['product_id'],
            '{{%product}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_item_credit_note_service1',
            '{{%item_credit_note}}',
            ['service_id'],
            '{{%service}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_item_credit_note_user1',
            '{{%item_credit_note}}',
            ['user_id'],
            '{{%user}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_item_credit_note_unit_type1',
            '{{%item_credit_note}}',
            ['unit_type_id'],
            '{{%unit_type}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
    }

    public function down()
    {
        $this->dropTable('{{%item_credit_note}}');
    }
}
