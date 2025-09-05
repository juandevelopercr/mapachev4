<?php

use yii\db\Migration;

/**
 * Class m210108_154925_create_table_item_entry
 */
class m210108_154925_create_table_item_entry extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            '{{%item_entry}}',
            [
                'id' => $this->primaryKey(),
                'entry_id' => $this->integer(),
                'product_code' => $this->string(),
                'product_description' => $this->string(),
                'product_id' => $this->integer(),
                'past_price' => $this->decimal(15, 2),
                'price' => $this->decimal(15, 2),
                'past_quantity' => $this->decimal(15, 2),
                'entry_quantity' => $this->decimal(15, 2),
                'new_quantity' => $this->decimal(15, 2),
                'observations' => $this->text(),
                'user_id' => $this->integer(),
                'created_at' => $this->dateTime(),
                'updated_at' => $this->dateTime(),
            ],
            $tableOptions
        );

        $this->addForeignKey(
            'fk_item_entry_entry1',
            '{{%item_entry}}',
            ['entry_id'],
            '{{%entry}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_item_entry_product1',
            '{{%item_entry}}',
            ['product_id'],
            '{{%product}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_item_entry_user1',
            '{{%item_entry}}',
            ['user_id'],
            '{{%user}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
    }

    public function down()
    {
        $this->dropTable('{{%item_entry}}');
    }
}
