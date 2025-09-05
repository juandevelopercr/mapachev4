<?php

namespace backend\modules\tpv;

/**
 * tpv module definition class
 */
class Module extends \yii\base\Module
{
    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'backend\modules\tpv\controllers';

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        $this->layout = '@backend/views/layouts/main-tpv';
        // custom initialization code goes here
    }
}
