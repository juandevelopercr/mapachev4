<?php

use yii\db\Migration;

class m201227_220801_create_table_supplier extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            '{{%supplier}}',
            [
                'id' => $this->primaryKey(),
                'name' => $this->string(),
                'code' => $this->string(),
                'identification' => $this->string(),
                'phone' => $this->string(),
                'address' => $this->text(),
                'web_site' => $this->string(),
                'entry_date' => $this->date(),
                'colon_credit' => $this->decimal(15, 2),
                'dollar_credit' => $this->decimal(15, 2),
                'max_credit' => $this->boolean()->defaultValue('0'),
                'credit_days_id' => $this->integer(),
                'condition_sale_id' => $this->integer(),
                'status' => $this->boolean()->defaultValue('1'),
                'updated_at' => $this->dateTime(),
                'created_at' => $this->dateTime(),
            ],
            $tableOptions
        );

        $this->addForeignKey(
            'fk_supplier_condition_sale1',
            '{{%supplier}}',
            ['condition_sale_id'],
            '{{%condition_sale}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_supplier_credit_days',
            '{{%supplier}}',
            ['credit_days_id'],
            '{{%credit_days}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
    }

    public function down()
    {
        $this->dropTable('{{%supplier}}');
    }
}
