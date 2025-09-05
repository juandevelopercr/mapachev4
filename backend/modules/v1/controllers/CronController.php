<?php

namespace backend\modules\v1\controllers;

use backend\models\business\DebitNote;
use backend\models\business\CreditNote;
use backend\models\business\Invoice;
use backend\models\business\Documents;
use backend\models\nomenclators\UtilsConstants;
use backend\modules\v1\ApiUtilsFunctions;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use common\components\mensajes\DescargarCorreos;
use common\components\mensajes\ProcesarCorreos;
use Yii;

//http://www.herbavic.net/v1/smtp/process-email
/**
 * CronController for the `v1` module
 */
class CronController extends ApiController
{
    public $modelClass = '';

    /**
     * Format response to JSON
     * @return array
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        unset($behaviors['authenticator']);

        return $behaviors;
    }
   
    public function actionSendInvoice()
    {
        UtilsConstants::sendInvoicesToHacienda();
   		
        return true;
    }

    public function actionGetStatusInvoice()
    {
        UtilsConstants::getStatusInvoiceInHacienda();
   		
        return true;
    }

    public function actionSendDocument()
    {
        UtilsConstants::sendDocumentToHacienda();
   		
        return true;
    }   
    
    public function actionGetStatusDocument()
    {
        UtilsConstants::getStatusDocumentInHacienda();
   		
        return true;
    }

    public function actionSendCreditNote()
    {
        UtilsConstants::sendCreditNoteToHacienda();
   		
        return true;
    }  
    
    public function actionSendDebitNote()
    {
        UtilsConstants::sendDebitNoteToHacienda();
   		
        return true;
    }      

}

