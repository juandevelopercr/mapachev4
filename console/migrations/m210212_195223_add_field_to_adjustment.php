<?php

use yii\db\Migration;

/**
 * Class m210212_195223_add_field_to_adjustment
 */
class m210212_195223_add_field_to_adjustment extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%adjustment}}','origin_sector_location_id', $this->integer());
        $this->addColumn('{{%adjustment}}','target_sector_location_id', $this->integer());

        $this->addForeignKey(
            'fk_adjustment_sector_location1',
            '{{%adjustment}}',
            ['origin_sector_location_id'],
            '{{%sector_location}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_adjustment_target_sector_location1',
            '{{%adjustment}}',
            ['target_sector_location_id'],
            '{{%sector_location}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk_adjustment_origin_sector_location1','{{%adjustment}}');
        $this->dropColumn('{{%adjustment}}','origin_sector_location_id');

        $this->dropForeignKey('fk_adjustment_target_sector_location1','{{%adjustment}}');
        $this->dropColumn('{{%adjustment}}','target_sector_location_id');
    }
}
