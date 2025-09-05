<?php

namespace backend\modules\reportes\controllers;

use Yii;
use backend\models\business\Documents;
use backend\models\business\DocumentsSearch;
use backend\modules\reportes\models\DocumentReportForm;
use backend\models\nomenclators\UtilsConstants;
use backend\models\settings\Issuer;
use kartik\form\ActiveForm;
use yii\helpers\Url;
use yii\web\UploadedFile;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\FileHelper;

/**
 * DocumentsController implements the CRUD actions for Documents model.
 */
class DocumentsController extends ExportController
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    } 

    public function actionIndex()
    {
		$model = new DocumentReportForm();
		$model->estado_id = UtilsConstants::HACIENDA_STATUS_ACEPTADO_HACIENDA;		
		$model->tipo = '01';
		$model->moneda = 'CRC';
		$fecha = new \DateTime();
		$fecha->modify('last day of this month');
		$ultimo_dia_mes = $fecha->format('d');		
		$model->fecha = '01'.'-'.date('m').'-'.date('Y').' - '.$ultimo_dia_mes.'-'.date('m').'-'.date('Y');		

		if ($model->load(Yii::$app->request->post()))
		{		
			$model->emisor = Yii::$app->request->post()['DocumentReportForm']['emisor'];
			$model->tipo = Yii::$app->request->post()['DocumentReportForm']['tipo'];
			$model->moneda = Yii::$app->request->post()['DocumentReportForm']['moneda'];			
			$model->estado_id = Yii::$app->request->post()['DocumentReportForm']['estado_id'];						
			$model->fecha = Yii::$app->request->post()['DocumentReportForm']['fecha'];
			
			$query = Documents::find();

			$filter_fechas = explode (" - ", $model->fecha);
			if (count($filter_fechas) == 2)
			{
				$DateStart = date('Y-m-d', strtotime($filter_fechas[0]));
				$DateEnd = date('Y-m-d', strtotime($filter_fechas[1]));
				$query->andFilterWhere(
					['between', 'emission_date', $DateStart.' 00:00:00', $DateEnd.' 23:59:59']);
			}	
			if (!is_null($model->emisor) && !empty($model->emisor))
				$query->andFilterWhere(['transmitter'=> $model->emisor]);		

			if (!is_null($model->moneda) && !empty($model->moneda))
				$query->andWhere(['currency'=>$model->moneda]);
				
			if (!is_null($model->estado_id) && !empty($model->estado_id))				
				$query->andWhere(['status'=>$model->estado_id]);
				
			if (!is_null($model->tipo) && !empty($model->tipo))				
				$query->andWhere(['document_type'=>$model->tipo]);				
			
			$documentos = $query->orderBy(['reception_date'=> SORT_DESC, 'transmitter'=> SORT_ASC])->all();
			
			$fecha_ini = date('d-m-Y', strtotime($filter_fechas[0]));
			$fecha_fin = date('d-m-Y', strtotime($filter_fechas[1]));			
			
			// get your HTML raw content without any layouts or scripts
			$content = $this->renderAjax('_reportView', ['datos'=>$documentos, 'model'=>$model, 'fecha_ini'=>$fecha_ini, 'fecha_fin'=>$fecha_fin]);
			echo $this->download($content);
			exit;
		}

        return $this->render('_reportForm', [
            'model' => $model,
        ]);
    }	

}
