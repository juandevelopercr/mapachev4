<?php

use yii\db\Migration;

/**
 * Class m210207_015446_create_table_sector
 */
class m210207_015446_create_table_sector extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            '{{%sector}}',
            [
                'id' => $this->primaryKey(),
                'branch_office_id' => $this->integer(),
                'code' => $this->string(),
                'name' => $this->string(),
                'status' => $this->boolean()->defaultValue('1'),
                'created_at' => $this->dateTime(),
                'updated_at' => $this->dateTime(),
            ],
            $tableOptions
        );

        $this->addForeignKey(
            'fk_sector_branch_office1',
            '{{%sector}}',
            ['branch_office_id'],
            '{{%branch_office}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
    }

    public function down()
    {
        $this->dropTable('{{%sector}}');
    }

}
