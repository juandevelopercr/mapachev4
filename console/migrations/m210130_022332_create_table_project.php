<?php

use yii\db\Migration;

/**
 * Class m210130_022332_create_table_project
 */
class m210130_022332_create_table_project extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            '{{%project}}',
            [
                'id' => $this->primaryKey(),
                'name' => $this->string(),
                'code' => $this->string(),
                'description' => $this->text(),
                'status' => $this->boolean()->defaultValue('1'),
                'created_at' => $this->dateTime(),
                'updated_at' => $this->dateTime(),
            ],
            $tableOptions
        );


    }

    public function down()
    {
        $this->dropTable('{{%project}}');
    }
}
