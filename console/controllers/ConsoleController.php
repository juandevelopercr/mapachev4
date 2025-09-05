<?php

namespace console\controllers;

use backend\models\business\ItemInvoice;
use backend\models\nomenclators\UtilsConstants;
use backend\models\support\CronjobLog;
use backend\models\support\CronjobTask;
use common\components\ApiV43\ApiAccess;
use common\components\ApiV43\ApiConsultaHacienda;
use console\models\ConsoleHelper;
use Yii;
use yii\console\Controller;
use backend\models\business\Invoice;
use backend\models\settings\Issuer;

class ConsoleController extends Controller
{
    /**
     * Action for run test action
     */
    public function actionVerifyStatusHacienda()
    {
        $execution_date = date('Y-m-d H:i:s');

        set_time_limit(0);

        try
        {
            $message = Yii::t("backend", "Ejecución correcta");

            $invoice_list = Invoice::find()->all();
            $issuer = Issuer::find()->one();

            $api_access = new ApiAccess();
            $connection_api = $api_access->loginHacienda($issuer);
            if($connection_api !== null)
            {
                $error = $connection_api['error'];
                if ($error == 0)
                {
                    foreach ($invoice_list AS $index => $invoice)
                    {
                        $old_status_hacienda = (int) $invoice->status_hacienda;
                        $items_exists = ItemInvoice::find()->where(['invoice_id' => $invoice->id])->exists();

                        if ($items_exists)
                        {
                            $apiConsultaHacienda = new ApiConsultaHacienda();
                            $document_type = '01'; // Factura
                            $data = $apiConsultaHacienda->getEstado($invoice, $issuer, $api_access->token, $document_type);
                            if($data !== null)
                            {
                                $status_hacienda = $data['estado'];

                                if ($status_hacienda == 'rechazado')
                                {
                                    if($old_status_hacienda !== UtilsConstants::HACIENDA_STATUS_REJECTED)
                                    {
                                        Invoice::setStatusHacienda($invoice->id, UtilsConstants::HACIENDA_STATUS_REJECTED);
                                    }
                                }
                                elseif ($status_hacienda == 'aceptado')
                                {
                                    if($old_status_hacienda !== UtilsConstants::HACIENDA_STATUS_ACCEPTED)
                                    {
                                        Invoice::setStatusHacienda($invoice->id, UtilsConstants::HACIENDA_STATUS_ACCEPTED);
                                    }
                                }
                                elseif ($status_hacienda == 'recibido')
                                {
                                    if($old_status_hacienda !== UtilsConstants::HACIENDA_STATUS_RECEIVED)
                                    {
                                        Invoice::setStatusHacienda($invoice->id, UtilsConstants::HACIENDA_STATUS_RECEIVED);
                                    }
                                }
                            }
                        }

                        ConsoleHelper::printMessage('     --->  Estado de hacienda verificado para la factura ['.$invoice->key.']');
                        echo PHP_EOL;
                    }

                    $api_access->CloseSesion($api_access->token, $issuer);
                }
                else
                {
                    $message = 'No se ha podido autenticar en la API de Hacienda';
                    ConsoleHelper::printMessage('     --->  '.$message);
                    echo PHP_EOL;
                }
            }
            else
            {
                $message = 'No se ha podido autenticar en la API de Hacienda';
                ConsoleHelper::printMessage('     --->  '.$message);
                echo PHP_EOL;
            }

        }
        catch (\Exception $exception)
        {
            $message = Yii::t("backend", "Ejecución incorrecta: ") . $exception->getMessage();
            ConsoleHelper::printMessage('     --->  '.$message);
            echo PHP_EOL;
        }

        $taskId = CronjobTask::getTaskIdByName(CronjobTask::JOB_VERIFY_STATUS_HACIENDA);

        if($taskId > 0)
        {
            CronjobLog::registerJob($taskId, $execution_date, $message);
        }

        $total_invoices = count($invoice_list);

        ConsoleHelper::printMessage('---> Tarea completada, total de elementos procesados, '.$total_invoices.' con fecha, '.$execution_date);

        echo PHP_EOL;
    }
}