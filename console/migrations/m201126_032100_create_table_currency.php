<?php

use yii\db\Migration;

/**
 * Class m201126_032100_create_table_currency
 */
class m201126_032100_create_table_currency extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%currency}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(),
            'symbol' => $this->string(),
            'change_type' => $this->decimal(10,5),
            'description' => $this->text(),
            'status' => $this->tinyInteger(1)->notNull()->defaultValue('1'),
            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime()->notNull(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        if (in_array('currency', Yii::$app->db->schema->getTableNames())) {
            $this->dropTable('{{%currency}}');
        }
    }
}
