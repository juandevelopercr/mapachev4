<?php

use yii\db\Migration;
use backend\models\support\CronjobTask;

/**
 * Class m210530_160257_add_crontask_base
 */
class m210530_160257_add_crontask_base extends Migration
{

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $model = new CronjobTask(['name' => CronjobTask::JOB_VERIFY_STATUS_HACIENDA, 'status' => 1]);
        if(!$model->save())
        {
            print_r($model->getErrors());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        CronjobTask::deleteAll();
    }

}
