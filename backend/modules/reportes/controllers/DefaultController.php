<?php

namespace backend\modules\reportes\controllers;

use yii\web\Controller;

/**
 * Default controller for the `reportes` module
 */
class DefaultController extends Controller
{
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');    
        // Prueba de nuevo    
    }
}
