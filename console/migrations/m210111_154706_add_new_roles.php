<?php

use yii\db\Migration;
use common\models\User;

/**
 * Class m210111_154706_add_new_roles
 */
class m210111_154706_add_new_roles extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $auth = Yii::$app->authManager;
        $admin_role = $auth->createRole(User::ROLE_ADMIN);
        $admin_role->description = 'Rol con permisos de administración';
        $auth->add($admin_role);

        $employee_role = $auth->createRole(User::ROLE_FACTURADOR);
        $employee_role->description = 'Rol con permisos de empleados';
        $auth->add($employee_role);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $auth = Yii::$app->authManager;
        $auth->remove($auth->getRole(User::ROLE_ADMIN));
        $auth->remove($auth->getRole(User::ROLE_FACTURADOR));
    }
}
