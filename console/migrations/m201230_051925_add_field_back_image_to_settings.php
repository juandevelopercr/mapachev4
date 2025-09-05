<?php

use yii\db\Migration;

/**
 * Class m201230_051925_add_field_back_image_to_settings
 */
class m201230_051925_add_field_back_image_to_settings extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('setting', 'back_image_login', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn("setting", "back_image_login");
    }

}
