<?php

use yii\db\Migration;

/**
 * Class m201126_032107_create_table_department
 */
class m201126_032107_create_table_department extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%department}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(),
            'code' => $this->string(),
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
        if (in_array('department', Yii::$app->db->schema->getTableNames()))
        {
            $this->dropTable('{{%department}}');
        }
    }
}
