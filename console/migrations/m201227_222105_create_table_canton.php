<?php

use yii\db\Migration;

class m201227_222105_create_table_canton extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            '{{%canton}}',
            [
                'id' => $this->primaryKey(),
                'name' => $this->string(),
                'code' => $this->string(),
                'description' => $this->text(),
                'status' => $this->boolean()->defaultValue('1'),
                'created_at' => $this->dateTime(),
                'updated_at' => $this->dateTime(),
                'province_id' => $this->integer(),
            ],
            $tableOptions
        );

        $this->addForeignKey(
            'fk_canton_province1',
            '{{%canton}}',
            ['province_id'],
            '{{%province}}',
            ['id'],
            'NO ACTION',
            'NO ACTION'
        );
    }

    public function down()
    {
        $this->dropTable('{{%canton}}');
    }
}
