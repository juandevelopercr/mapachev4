<?php

use yii\db\Migration;
use common\models\User;

/**
 * Class m210423_033445_create_role_agent
 */
class m210423_033445_create_role_agent extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $auth = Yii::$app->authManager;

        $basic_role = $auth->getRole(User::ROLE_BASIC);
        $agent_role = $auth->createRole(User::ROLE_AGENT);
        $agent_role->description = 'Rol aociado a los usuarios de tipo Agentes';
        $auth->add($agent_role);
        $auth->addChild($agent_role, $basic_role);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $auth = Yii::$app->authManager;
        $role_agent = $auth->getRole(User::ROLE_AGENT);
        $auth->remove($role_agent);
    }
}
