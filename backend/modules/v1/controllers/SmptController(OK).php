<?php
namespace backend\modules\v1\controllers;

use Yii;
use yii\filters\auth\HttpBearerAuth;
use yii\web\ForbiddenHttpException;
use yii\filters\ContentNegotiator;
use yii\web\Response;
use backend\modules\facturacion\models\DocumentosRecibidos;
use backend\modules\facturacion\models\DocumentosRecibidosSearch;
use backend\modules\facturacion\models\Emisores;
use backend\modules\facturacion\models\DocumentosEstados;
use common\models\UserEmpresa;
use kartik\mpdf\Pdf;
use Mpdf\QrCode\QrCode;
use Mpdf\QrCode\Output;
use yii\helpers\Url;
use common\libs\Utiles;
use common\components\ApiV43\ApiAccess;
use common\components\ApiV43\ApiXML;
use common\components\ApiV43\ApiFirmadoHacienda;
use common\components\ApiV43\ApiEnvioHacienda;
use common\components\ApiV43\ApiConsultaHacienda;

use common\components\mensajes\DescargarCorreos;
use common\components\mensajes\ProcesarCorreos;

/**
 * Site controller
 */
class SmptController extends ApiController
{
    public $modelClass = 'backend\modules\business\Invoice';

    public $serializer = [
        'class' => 'yii\rest\Serializer',
        'collectionEnvelope' => 'items',
    ];

	public function actions() {
		$actions = parent::actions();
		unset($actions['index']);
		unset($actions['delete']);
		unset($actions['update']);		
		unset($actions['create']);		
		return $actions;
	}
		
	public function actionIndex(){	
		// 1 Descargar Correos	
		die(var_dump("OK"));
		$correos = new DescargarCorreos();
		$correos->descargaCorreos();
		
		// 2 Procesar Documentos
		$archivos = new ProcesarCorreos();
		$ruta = Yii::getAlias("@backend/web/documentos/smtp/descargados/");
		$archivos->procesarDirectoriosCorreos($ruta);
		return 200;		
	}	
}