<?php

use yii\db\Migration;

/**
 * Class m210207_020348_create_table_physical_location
 */
class m210207_020348_create_table_physical_location extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            '{{%physical_location}}',
            [
                'id' => $this->primaryKey(),
                'product_id' => $this->integer(),
                'sector_location_id' => $this->integer(),
                'quantity' => $this->decimal(18, 5),
                'created_at' => $this->dateTime(),
                'updated_at' => $this->dateTime(),
            ],
            $tableOptions
        );

        $this->addForeignKey(
            'fk_physical_location_sector_location1',
            '{{%physical_location}}',
            ['sector_location_id'],
            '{{%sector_location}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_physical_location_product1',
            '{{%physical_location}}',
            ['product_id'],
            '{{%product}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
    }

    public function down()
    {
        $this->dropTable('{{%physical_location}}');
    }
}
