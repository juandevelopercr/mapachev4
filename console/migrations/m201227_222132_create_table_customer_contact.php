<?php

use yii\db\Migration;

class m201227_222132_create_table_customer_contact extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            '{{%customer_contact}}',
            [
                'id' => $this->primaryKey(),
                'name' => $this->string(),
                'last_name' => $this->string(),
                'email' => $this->string(),
                'phone' => $this->string(),
                'fax' => $this->string(),
                'ext' => $this->string(),
                'cellphone' => $this->string(),
                'customer_id' => $this->integer(),
                'department_id' => $this->integer(),
                'status' => $this->boolean()->defaultValue('1'),
                'created_at' => $this->dateTime(),
                'updated_at' => $this->dateTime(),
            ],
            $tableOptions
        );

        $this->addForeignKey(
            'fk_customer_contact_department1',
            '{{%customer_contact}}',
            ['department_id'],
            '{{%department}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_identification_type_copy1_customer1',
            '{{%customer_contact}}',
            ['customer_id'],
            '{{%customer}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
    }

    public function down()
    {
        $this->dropTable('{{%customer_contact}}');
    }
}
