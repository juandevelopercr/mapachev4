<?php

use yii\db\Migration;

/**
 * Class m210308_014657_create_table_route_transport_has_collector
 */
class m210308_014657_create_table_route_transport_has_collector extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            '{{%route_transport_has_collector}}',
            [
                'collector_id' => $this->integer()->notNull(),
                'route_transport_id' => $this->integer()->notNull(),
            ],
            $tableOptions
        );

        $this->addPrimaryKey('PRIMARYKEY_route_transport_has_collector', '{{%route_transport_has_collector}}', ['collector_id', 'route_transport_id']);

        $this->addForeignKey(
            'fk_route_transport_has_collector_route_transport1',
            '{{%route_transport_has_collector}}',
            ['route_transport_id'],
            '{{%route_transport}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_route_transport_has_collector_collector1',
            '{{%route_transport_has_collector}}',
            ['collector_id'],
            '{{%collector}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
    }

    public function down()
    {
        $this->dropTable('{{%route_transport_has_collector}}');
    }
}
