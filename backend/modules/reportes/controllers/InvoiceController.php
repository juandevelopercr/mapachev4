<?php

namespace backend\modules\reportes\controllers;

use backend\models\business\Invoice;
use backend\models\business\ItemInvoice;
use Yii;
use backend\models\business\Product;
use backend\models\business\ProductHasBranchOffice;
use backend\modules\reportes\models\InvoiceReportForm;
use backend\models\nomenclators\Family;
use backend\models\nomenclators\Category;
use backend\models\nomenclators\Country;
use backend\models\nomenclators\UtilsConstants;
use common\models\GlobalFunctions;
use backend\models\settings\Issuer;
use kartik\form\ActiveForm;
use yii\helpers\Url;
use yii\web\UploadedFile;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\FileHelper;

/**
 * EntregasDigitalesController implements the CRUD actions for Documents model.
 */
class InvoiceController extends ExportController
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
		$model = new InvoiceReportForm();
		$fecha = new \DateTime();
		$fecha->modify('last day of this month');
		$ultimo_dia_mes = $fecha->format('d');		
		$model->fecha = '01'.'-'.date('m').'-'.date('Y').' - '.$ultimo_dia_mes.'-'.date('m').'-'.date('Y');		
		$model->estado = 3;
		$date_title = '';
		if ($model->load(Yii::$app->request->post())) {
			$query = Invoice::find()->select("invoice.id, invoice.consecutive, invoice.emission_date,
													  invoice.status, customer.name as cliente, invoice.invoice_type")
											->join('INNER JOIN', 'customer', 'invoice.customer_id = customer.id');
											//->join('INNER JOIN', 'seller_has_invoice', "seller_has_invoice.invoice_id = invoice.id")
											//->join('LEFT JOIN', 'collector_has_invoice', "collector_has_invoice.invoice_id = invoice.id");
			
			$filter_fechas = explode (" - ", $model->fecha);
			if (count($filter_fechas) == 2)
			{
				$DateStart = date('Y-m-d', strtotime($filter_fechas[0]));
				$DateEnd = date('Y-m-d', strtotime($filter_fechas[1]));
				$query->andFilterWhere(
					['between', 'emission_date', $DateStart, $DateEnd]);

				//$date_title	= date('d-M-Y', strtotime($filter_fechas[0])) . ' hasta '. date('d-M-Y', strtotime($filter_fechas[1]));
				if ($filter_fechas[0] == $filter_fechas[1]) {
					$date_title = 'DEL DÍA ' . date('d-M-Y', strtotime($filter_fechas[0]));
				} else {
					$date_title = 'DESDE ' . date('d-M-Y', strtotime($filter_fechas[0])) . ' hasta ' . date('d-M-Y', strtotime($filter_fechas[1]));
				}
			}	
			
			//Customer
			if (!is_null($model->cliente) && !empty($model->cliente)) {
				$query->andWhere(['invoice.customer_id' => $model->cliente]);
			}

			if (!is_null($model->tipo) && !empty($model->tipo)) {
				$query->andWhere(['invoice.invoice_type' => $model->tipo]);
			}
			

			/*
			//Vendedor
			if (!is_null($model->seller) && !empty($model->seller)) {
				$query->andWhere(['seller_has_invoice.seller_id' => $model->seller]);
			}

			//Cobrador
			if (!is_null($model->collector) && !empty($model->collector)) {
				$query->andWhere(['collector_has_invoice.collector_id' => $model->collector]);
			}	
				*/

			//Estado
			if (!is_null($model->estado) && !empty($model->estado)) {
				$query->andWhere(['invoice.status_hacienda' => $model->estado]);
			}

			$query->orderBy('invoice.consecutive ASC, customer.name ASC');
			$datos = $query->all();

			$content = $this->renderAjax('_header_xls', ['date_title'=> $date_title]);
			$content .= $this->renderAjax('_rowHeader', []);

			$total_tax = 0;
			$total_discount = 0;
			$total_exonerate = 0;
			$total_invoice = 0;

			$total_item = 0;
			foreach ($datos as $invoice) {

				$tax = number_format($invoice->totalImpuesto, 2, '.', ',');
				$discount = number_format($invoice->totalDescuentos, 2, '.', ',');
				$exonerate = number_format($invoice->totalExonerado, 2, '.', ',');
				$total =number_format($invoice->total_comprobante, 2, '.', ',');

				$total_tax += $invoice->totalImpuesto;
				$total_discount += $invoice->totalDescuentos;
				$total_exonerate += $invoice->totalExonerado;
				$total_invoice += $invoice->total_comprobante;
				
				$cantidad_item = ItemInvoice::find()->where(['invoice_id'=>$invoice->id])->sum('quantity');
				if (is_null($cantidad_item))
					$cantidad_item = 0;

				$total_item	+= $cantidad_item;
				$content .= $this->renderAjax('_rowContent', [
					'model' => $model, 
					'invoice' => $invoice, 
					'tax'=>$tax, 
					'discount'=>$discount, 
					'exonerate'=>$exonerate,
					'total'=>$total,
					'cantidad_item'=>$cantidad_item,
				]);
			}

			$total_tax = number_format($total_tax, 2, '.', ',');
			$total_discount = number_format($total_discount, 2, '.', ',');
			$total_exonerate = number_format($total_exonerate, 2, '.', ',');
			$total_invoice =number_format($total_invoice, 2, '.', ',');			

			$content .= $this->renderAjax('_rowTotal', [				
				'tax'=>$total_tax, 
				'discount'=>$total_discount, 
				'exonerate'=>$total_exonerate,
				'total'=>$total_invoice,
				'total_item' => $total_item,
			]);			

			$content .= $this->renderAjax('_footer_xls', []);
			echo $this->download($content);
			exit;
		}

		return $this->render('_reportForm', [
			'model' => $model,
		]);
	}
}
