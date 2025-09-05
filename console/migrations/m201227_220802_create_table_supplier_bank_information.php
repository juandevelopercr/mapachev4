<?php

use yii\db\Migration;

class m201227_220802_create_table_supplier_bank_information extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            '{{%supplier_bank_information}}',
            [
                'id' => $this->primaryKey(),
                'banck_name' => $this->string(),
                'checking_account' => $this->string(),
                'customer_account' => $this->string(),
                'mobile_account' => $this->string(),
                'supplier_id' => $this->integer(),
                'status' => $this->boolean()->defaultValue('1'),
                'created_at' => $this->dateTime(),
                'updated_at' => $this->dateTime(),
            ],
            $tableOptions
        );

        $this->addForeignKey(
            'fk_supplier_bank_information_supplier1',
            '{{%supplier_bank_information}}',
            ['supplier_id'],
            '{{%supplier}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
    }

    public function down()
    {
        $this->dropTable('{{%supplier_bank_information}}');
    }
}
