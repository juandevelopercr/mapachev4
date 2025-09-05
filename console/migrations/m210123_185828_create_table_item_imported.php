<?php

use yii\db\Migration;

/**
 * Class m210123_185828_create_table_item_imported
 */
class m210123_185828_create_table_item_imported extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            '{{%item_imported}}',
            [
                'id' => $this->primaryKey(),
                //item_details
                'code' => $this->string(),
                'quantity' => $this->decimal(18, 5),
                'unit_measure' => $this->string(),
                'unit_measure_commercial' => $this->string(),
                'name' => $this->string(),
                'price_by_unit' => $this->string(),
                'amount_total' => $this->decimal(18, 5),
                'discount_amount' => $this->decimal(18, 5),
                'tax_amount' => $this->decimal(18, 5),
                'tax_neto' => $this->decimal(18, 5),
                'amount_total_line' => $this->decimal(18, 5),
                //extra
                'status' => $this->integer(),
                'xml_imported_id' => $this->integer(),
                'created_at' => $this->dateTime(),
                'updated_at' => $this->dateTime(),
            ],
            $tableOptions
        );

        $this->addForeignKey(
            'fk_item_imported_xml_imported1',
            '{{%item_imported}}',
            ['xml_imported_id'],
            '{{%xml_imported}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
    }

    public function down()
    {
        $this->dropTable('{{%item_imported}}');
    }
}
