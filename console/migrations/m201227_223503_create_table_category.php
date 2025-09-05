<?php

use yii\db\Migration;

class m201227_223503_create_table_category extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            '{{%category}}',
            [
                'id' => $this->primaryKey(),
                'name' => $this->string(),
                'code' => $this->string(),
                'description' => $this->text(),
                'status' => $this->boolean()->defaultValue('1'),
                'created_at' => $this->dateTime(),
                'updated_at' => $this->dateTime(),
                'family_id' => $this->integer(),
            ],
            $tableOptions
        );

        $this->addForeignKey(
            'fk_category_family1',
            '{{%category}}',
            ['family_id'],
            '{{%family}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
    }

    public function down()
    {
        $this->dropTable('{{%category}}');
    }
}
