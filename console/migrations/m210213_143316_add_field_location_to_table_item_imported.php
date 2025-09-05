<?php

use yii\db\Migration;

/**
 * Class m210213_143316_add_field_location_to_table_item_imported
 */
class m210213_143316_add_field_location_to_table_item_imported extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%item_imported}}','sector_location_id', $this->integer());

        $this->addForeignKey(
            'fk_item_imported_sector_location1',
            '{{%item_imported}}',
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
        $this->dropForeignKey('fk_item_imported_sector_location1','{{%item_imported}}');
        $this->dropColumn('{{%item_imported}}','sector_location_id');
    }
}
