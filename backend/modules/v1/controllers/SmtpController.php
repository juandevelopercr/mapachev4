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
 * SmtpController for the `v1` module
 */
class SmtpController extends ApiController
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
   
    public function actionProcessEmail()
    {
        //1 Eliminar carpetas
        $ruta = Yii::getAlias("@backend/web/uploads/smtp/descargados/");
        $this->deleteSubfolders($ruta);

   		// 2 Descargar Correos			
		$correos = new DescargarCorreos();               
		$correos->descargaCorreos();
		
		// 3 Procesar Documentos
		$archivos = new ProcesarCorreos();
                                
		$ruta = Yii::getAlias("@backend/web/uploads/smtp/descargados/");
		$archivos->procesarDirectoriosCorreos($ruta);
        
        return true;
    }

    function deleteSubfolders($folderPath) {
        // Obtener todas las entradas dentro de la carpeta
        $files = scandir($folderPath);
    
        // Recorrer cada entrada dentro de la carpeta
        foreach ($files as $file) {
            // Ignorar los enlaces "." y ".."
            if ($file !== "." && $file !== "..") {
                $fullPath = $folderPath . DIRECTORY_SEPARATOR . $file;
    
                // Si la entrada es una carpeta, eliminarla recursivamente
                if (is_dir($fullPath)) {
                    // Eliminar el contenido dentro de la subcarpeta
                    self::deleteSubfolders($fullPath);
    
                    // Luego, eliminar la subcarpeta vacía
                    rmdir($fullPath);
                } else {
                    // Si es un archivo, eliminarlo
                    unlink($fullPath);
                }
            }
        }
    }
}

