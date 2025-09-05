<?php

use yii\db\Migration;

/**
 * Class m210218_025939_create_table_attach_po
 */
class m210218_025939_create_table_attach_po extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            '{{%attach_po}}',
            [
                'id' => $this->primaryKey(),
                'payment_order_id' => $this->integer(),
                'user_id' => $this->integer(),
                'document_file' => $this->string(),
                'observations' => $this->text(),
                'created_at' => $this->dateTime(),
                'updated_at' => $this->dateTime(),
            ],
            $tableOptions
        );

        $this->addForeignKey(
            'fk_attach_po_user1',
            '{{%attach_po}}',
            ['user_id'],
            '{{%user}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_attach_po_payment_order1',
            '{{%attach_po}}',
            ['payment_order_id'],
            '{{%payment_order}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
    }

    public function down()
    {
        $this->dropTable('{{%attach_po}}');
    }
}
