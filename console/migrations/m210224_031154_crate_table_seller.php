<?php

use yii\db\Migration;

/**
 * Class m210224_031154_crate_table_seller
 */
class m210224_031154_crate_table_seller extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            '{{%seller}}',
            [
                'id' => $this->primaryKey(),
                'code' => $this->string(),
                'name' => $this->string(),
                'description' => $this->text(),
                'commission_percentage' => $this->decimal(5,2),
                'status' => $this->boolean()->defaultValue(true),
                'created_at' => $this->dateTime(),
                'updated_at' => $this->dateTime(),
            ],
            $tableOptions
        );

    }

    public function down()
    {
        $this->dropTable('{{%seller}}');
    }
}
