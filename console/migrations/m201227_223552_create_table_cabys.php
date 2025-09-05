<?php

use yii\db\Migration;

class m201227_223552_create_table_cabys extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            '{{%cabys}}',
            [
                'id' => $this->primaryKey(),
                'category1' => $this->string(),
                'description1' => $this->text(),
                'category2' => $this->string(),
                'description2' => $this->text(),
                'category3' => $this->string(),
                'description3' => $this->text(),
                'category4' => $this->string(),
                'description4' => $this->text(),
                'category5' => $this->string(),
                'description5' => $this->text(),
                'category6' => $this->string(),
                'description6' => $this->text(),
                'category7' => $this->string(),
                'description7' => $this->text(),
                'category8' => $this->string(),
                'description8' => $this->text(),
                'code' => $this->string(),
                'description_service' => $this->text(),
                'tax' => $this->string(),
                'status' => $this->boolean()->defaultValue('1'),
                'created_at' => $this->dateTime(),
                'updated_at' => $this->dateTime(),
            ],
            $tableOptions
        );
    }

    public function down()
    {
        $this->dropTable('{{%cabys}}');
    }
}
