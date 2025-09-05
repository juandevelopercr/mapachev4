<?php

use yii\db\Migration;

class m201227_221044_create_table_supplier_contact extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            '{{%supplier_contact}}',
            [
                'id' => $this->primaryKey(),
                'name' => $this->string(),
                'email' => $this->string(),
                'phone' => $this->string(),
                'ext' => $this->string(),
                'cellphone' => $this->string(),
                'supplier_id' => $this->integer(),
                'department_id' => $this->integer(),
                'job_position_id' => $this->integer(),
                'status' => $this->boolean()->defaultValue('1'),
                'created_at' => $this->dateTime(),
                'updated_at' => $this->dateTime(),
            ],
            $tableOptions
        );

        $this->addForeignKey(
            'fk_payment_type_copy1_supplier1',
            '{{%supplier_contact}}',
            ['supplier_id'],
            '{{%supplier}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_supplier_contact_department1',
            '{{%supplier_contact}}',
            ['department_id'],
            '{{%department}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_supplier_contact_job_position1',
            '{{%supplier_contact}}',
            ['job_position_id'],
            '{{%job_position}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
    }

    public function down()
    {
        $this->dropTable('{{%supplier_contact}}');
    }
}
