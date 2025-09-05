<?php

use yii\db\Migration;

/**
 * Class m210907_023133_create_table_item_debit_note
 */
class m210907_023133_create_table_item_debit_note extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            '{{%item_debit_note}}',
            [
                'id' => $this->primaryKey(),
                'debit_note_id' => $this->integer(),
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
            'fk_item_debit_note_debit_note1',
            '{{%item_debit_note}}',
            ['debit_note_id'],
            '{{%debit_note}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_item_debit_note_product1',
            '{{%item_debit_note}}',
            ['product_id'],
            '{{%product}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_item_debit_note_service1',
            '{{%item_debit_note}}',
            ['service_id'],
            '{{%service}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_item_debit_note_user1',
            '{{%item_debit_note}}',
            ['user_id'],
            '{{%user}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_item_debit_note_unit_type1',
            '{{%item_debit_note}}',
            ['unit_type_id'],
            '{{%unit_type}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
    }

    public function down()
    {
        $this->dropTable('{{%item_debit_note}}');
    }
}
