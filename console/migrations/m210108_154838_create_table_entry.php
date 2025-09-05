<?php

use yii\db\Migration;

/**
 * Class m210108_154838_create_table_entry
 */
class m210108_154838_create_table_entry extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            '{{%entry}}',
            [
                'id' => $this->primaryKey(),
                'order_purchase' => $this->string(),
                'supplier_id' => $this->integer(),
                'branch_office_id' => $this->integer(),
                'invoice_date' => $this->date(),
                'invoice_number' => $this->string(),
                'invoice_type' => $this->integer(),
                'amount' => $this->decimal(15, 2),
                'observations' => $this->text(),
                'created_at' => $this->dateTime(),
                'updated_at' => $this->dateTime(),
            ],
            $tableOptions
        );

        $this->addForeignKey(
            'fk_entry_supplier1',
            '{{%entry}}',
            ['supplier_id'],
            '{{%supplier}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_entry_branch_office1',
            '{{%entry}}',
            ['branch_office_id'],
            '{{%branch_office}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
    }

    public function down()
    {
        $this->dropTable('{{%entry}}');
    }
}
