<?php

namespace backend\modules\v1\controllers;

use backend\controllers\InvoiceController;
use backend\models\business\CreditNote;
use backend\models\business\DebitNote;
use backend\models\business\Documents;
use backend\models\business\Invoice;
use backend\models\nomenclators\UtilsConstants;
use backend\modules\v1\ApiUtilsFunctions;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;

/**
 * HaciendaController for the `v1` module
 */
class HaciendaController extends ApiController
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

    public function actionCallbackSendInvoice()
    {
        $params = $this->getRequestParamsAsArray();
        $key = ArrayHelper::getValue($params, "clave");
        $status_hacienda = ArrayHelper::getValue($params, "ind-estado");
        $xml_response = ArrayHelper::getValue($params, "respuesta-xml");
        $xml_response_hacienda_decode = base64_decode($xml_response);

        /*
        $productjson = json_encode($params);
        $jsonfile= Yii::getAlias('@webroot/assets/aresult.json');
        $fp = fopen($jsonfile, 'w+');
        fwrite($fp, $productjson);
        fclose($fp);
        */
        Invoice::verifyResponseStatusHacienda($key, $status_hacienda, $xml_response_hacienda_decode);

        return true;
    }
    /*
    public function actionCallbackSendInvoice()
    {
        $params = $this->getRequestParamsAsArray();
        $key = ArrayHelper::getValue($params, "clave");
        $status_hacienda = ArrayHelper::getValue($params, "ind-estado");
        $xml_response = ArrayHelper::getValue($params, "respuesta-xml");
        $xml_response_hacienda_decode = base64_decode($xml_response);

        $file = Yii::getAlias('@webroot/assets/test.txt');
        $archivo = fopen($file,'a');  
        $contenido = 'Entró: '.date('d-m-Y');
        fputs($archivo, $contenido); 
        fclose($archivo);  


        $file = Yii::getAlias('@webroot/assets/archivo.txt');
        $archivo = fopen($file,'a');  
        $contenido = 'Entró: '.date('d-m-Y');
        fputs($archivo, $contenido); 
        fclose($archivo);  


        $productjson = json_encode($params);
        $jsonfile= Yii::getAlias('@webroot/assets/aresult.json');
        $fp = fopen($jsonfile, 'w+');
        fwrite($fp, $productjson);
        fclose($fp);

        Invoice::verifyResponseStatusHacienda($key, $status_hacienda, $xml_response_hacienda_decode);

        return true;
    }
    */

    public function actionCallbackSendCreditNote()
    {
        $params = $this->getRequestParamsAsArray();
        $key = ArrayHelper::getValue($params, "clave");
        $status_hacienda = ArrayHelper::getValue($params, "ind-estado");
        $xml_response = ArrayHelper::getValue($params, "respuesta-xml");
        $xml_response_hacienda_decode = base64_decode($xml_response);

        CreditNote::verifyResponseStatusHacienda($key, $status_hacienda, $xml_response_hacienda_decode);

        return true;
    }

    public function actionCallbackSendDebitNote()
    {
        $params = $this->getRequestParamsAsArray();
        $key = ArrayHelper::getValue($params, "clave");
        $status_hacienda = ArrayHelper::getValue($params, "ind-estado");
        $xml_response = ArrayHelper::getValue($params, "respuesta-xml");
        $xml_response_hacienda_decode = base64_decode($xml_response);

        DebitNote::verifyResponseStatusHacienda($key, $status_hacienda, $xml_response_hacienda_decode);

        return true;
    }
                                
    public function actionCallbackMensaje()
    {
        $params = $this->getRequestParamsAsArray();
        $key = ArrayHelper::getValue($params, "clave");
        $status_hacienda = ArrayHelper::getValue($params, "ind-estado");
        $xml_response = ArrayHelper::getValue($params, "respuesta-xml");
        $xml_response_hacienda_decode = base64_decode($xml_response);

        Documents::verifyResponseStatusHacienda($key, $status_hacienda, $xml_response_hacienda_decode);

        return true;
    }

    public function actionShowComprobante($key){
        $invoice = Invoice::find()->where(['key'=>$key])->one();
        if (!is_null($invoice))
        {
            $controller = new InvoiceController('invoice', Yii::$app->module);

            $type = (int) $invoice->invoice_type;
            if ($type === UtilsConstants::PRE_INVOICE_TYPE_INVOICE) {            
                return $controller->showPdf($invoice->id, true);
            } else {            
                return $controller->showTicketPdf($invoice->id, true);
            }
        }
        else
          return "Documento no encontrado";
    }
}

