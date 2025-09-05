<?php

use yii\db\Migration;

/**
 * Class m210109_032004_add_field_branch_office_id_to_table_user
 */
class m210109_032004_add_field_branch_office_id_to_table_user extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('user','branch_office_id', $this->integer());
        $this->addForeignKey(
            'fk_user_branch_office1',
            '{{%user}}',
            ['branch_office_id'],
            '{{%branch_office}}',
            ['id'],
            'NO ACTION',
            'NO ACTION'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk_user_branch_office1','user');
        $this->dropColumn('user','branch_office_id');
    }

}
