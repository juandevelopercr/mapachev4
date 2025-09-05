<?php

use yii\db\Migration;

/**
 * Class m210212_035553_add_field_to_item_entry
 */
class m210212_035553_add_field_to_item_entry extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%item_entry}}','sector_location_id', $this->integer());

        $this->addForeignKey(
            'fk_item_entry_sector_location1',
            '{{%item_entry}}',
            ['sector_location_id'],
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
        $this->dropForeignKey('fk_item_entry_sector_location1','{{%item_entry}}');
        $this->dropColumn('{{%item_entry}}','sector_location_id');
    }

}
