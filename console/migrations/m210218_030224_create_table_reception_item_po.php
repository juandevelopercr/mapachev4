<?php

use yii\db\Migration;

/**
 * Class m210218_030224_create_table_reception_item_po
 */
class m210218_030224_create_table_reception_item_po extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            '{{%reception_item_po}}',
            [
                'id' => $this->primaryKey(),
                'item_payment_order_id' => $this->integer(),
                'received' => $this->decimal(18,5),
                'user_id' => $this->integer(),
                'created_at' => $this->dateTime(),
                'updated_at' => $this->dateTime(),
            ],
            $tableOptions
        );

        $this->addForeignKey(
            'fk_reception_item_po_user1',
            '{{%reception_item_po}}',
            ['user_id'],
            '{{%user}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_reception_item_po_item_payment_order1',
            '{{%reception_item_po}}',
            ['item_payment_order_id'],
            '{{%item_payment_order}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
    }

    public function down()
    {
        $this->dropTable('{{%reception_item_po}}');
    }
}
