<?php

use yii\db\Migration;

/**
 * Class m210207_015447_create_table_sector_location
 */
class m210207_015447_create_table_sector_location extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            '{{%sector_location}}',
            [
                'id' => $this->primaryKey(),
                'sector_id' => $this->integer(),
                'code' => $this->string(),
                'name' => $this->string(),
                'created_at' => $this->dateTime(),
                'updated_at' => $this->dateTime(),
            ],
            $tableOptions
        );

        $this->addForeignKey(
            'fk_sector_location_sector1',
            '{{%sector_location}}',
            ['sector_id'],
            '{{%sector}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
    }

    public function down()
    {
        $this->dropTable('{{%sector_location}}');
    }

}
