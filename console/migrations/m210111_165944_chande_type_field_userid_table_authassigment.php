<?php

use yii\db\Migration;

/**
 * Class m210111_165944_chande_type_field_userid_table_authassigment
 */
class m210111_165944_chande_type_field_userid_table_authassigment extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $query = Yii::$app->db->createCommand('ALTER TABLE auth_assignment ALTER COLUMN user_id TYPE integer USING (trim(user_id)::integer)');
        $query->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn('auth_assignment','user_id',$this->string());
    }
}
