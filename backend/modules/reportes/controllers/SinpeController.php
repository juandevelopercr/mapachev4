<?php

namespace backend\modules\reportes\controllers;

use Yii;
use backend\models\business\Invoice;
use backend\models\business\ItemInvoice;
use backend\models\business\InvoiceAbonos;
use common\models\User;
use backend\models\business\Product;
use backend\models\business\ProductHasBranchOffice;
use backend\modules\reportes\models\SinpeReportForm;
use backend\models\nomenclators\PaymentMethod;
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
class SinpeController extends ExportController
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

	/*
	public function actionIndex()
	{
		$model = new SinpeReportForm();
		$fecha = new \DateTime();
		$fecha->modify('last day of this month');
		$ultimo_dia_mes = $fecha->format('d');
		$model->fecha = '01' . '-' . date('m') . '-' . date('Y') . ' - ' . $ultimo_dia_mes . '-' . date('m') . '-' . date('Y');
		$model->estado = 3;

		$is_point_sale = 0; // facturas de almacen
		$dias_credito = 8;
		if ($model->load(Yii::$app->request->post())) {

			$query = Invoice::find()->select("invoice.*, CAST(NOW() AS date) - CAST(invoice.emission_date AS date) as dias_trascurridos, 
										(CAST(CAST(NOW() AS date) - CAST(invoice.emission_date AS date) as integer)) - " . $dias_credito . " AS dias_vencidos")
				->join('INNER JOIN', 'invoice_abonos', 'invoice_abonos.invoice_id = invoice.id')
				->join('INNER JOIN', 'customer', 'invoice.customer_id = customer.id')
				->join('INNER JOIN', 'boxes', "invoice.box_id = boxes.id AND boxes.is_point_sale = " . $is_point_sale . "")
				->Where([
					'invoice.status' => UtilsConstants::INVOICE_STATUS_PENDING,
					'status_hacienda' => UtilsConstants::HACIENDA_STATUS_ACCEPTED,
					'invoice_abonos.payment_method_id' => PaymentMethod::PAYMENT_SINPE_MOVIL
				])
				->join('INNER JOIN', 'seller_has_invoice', "seller_has_invoice.invoice_id = invoice.id")
				->join('LEFT JOIN', 'collector_has_invoice', "collector_has_invoice.invoice_id = invoice.id")
				->orderBy('status ASC, dias_vencidos DESC');


			$filter_fechas = explode(" - ", $model->fecha);
			if (count($filter_fechas) == 2) {
				$DateStart = date('Y-m-d', strtotime($filter_fechas[0]));
				$DateEnd = date('Y-m-d', strtotime($filter_fechas[1]));
				$query->andFilterWhere(
					['between', 'invoice.emission_date', $DateStart, $DateEnd]
				);
			}

			//bank
			if (!is_null($model->bank) && !empty($model->bank)) {
				$query->andWhere(['invoice_abonos.bank_id' => $model->bank]);
			}

			//Customer
			if (!is_null($model->cliente) && !empty($model->cliente)) {
				$query->andWhere(['invoice.customer_id' => $model->cliente]);
			}

			//Vendedor
			if (!is_null($model->seller) && !empty($model->seller)) {
				$query->andWhere(['seller_has_invoice.seller_id' => $model->seller]);
			}

			//Cobrador
			if (!is_null($model->collector) && !empty($model->collector)) {
				$query->andWhere(['collector_has_invoice.collector_id' => $model->collector]);
			}

			//Estado
			if (!is_null($model->estado) && !empty($model->estado)) {
				$query->andWhere(['invoice.status_hacienda' => $model->estado]);
			}

			$query->orderBy('invoice.consecutive ASC, customer.name ASC');
			$datos = $query->asArray()->all();

			$content = $this->renderAjax('_header_xls', []);
			$content .= $this->renderAjax('_rowHeader', []);

			$total_tax = 0;
			$total_discount = 0;
			$total_exonerate = 0;
			$total_invoice = 0;

			$total_item = 0;
			foreach ($datos as $data) {

				$invoice = Invoice::find()->where(['id'=>$data['id']])->one();
				$tax = number_format($invoice->totalImpuesto, 2, '.', ',');
				$discount = number_format($invoice->totalDescuentos, 2, '.', ',');
				$exonerate = number_format($invoice->totalExonerado, 2, '.', ',');
				$total = number_format($invoice->total_comprobante, 2, '.', ',');

				$total_tax += $invoice->totalImpuesto;
				$total_discount += $invoice->totalDescuentos;
				$total_exonerate += $invoice->totalExonerado;
				$total_invoice += $invoice->total_comprobante;

				$queryItem = InvoiceAbonos::find()->where(['invoice_id' => $invoice->id]);
				//bank
				if (!is_null($model->bank) && !empty($model->bank)) {
					$queryItem->andWhere(['bank_id' => $model->bank]);
				}

				//Vendedor
				if (!is_null($model->seller) && !empty($model->seller)) {
					$query->andWhere(['seller_has_invoice.seller_id' => $model->seller]);
				}

				//Cobrador
				if (!is_null($model->collector) && !empty($model->collector)) {
					$query->andWhere(['collector_has_invoice.collector_id' => $model->collector]);
				}				
				$items = $queryItem->all();

				$cantidad_item = count($items);

				$total_item	+= $cantidad_item;
				$content .= $this->renderAjax('_rowContent', [
					'model' => $model,
					'invoice' => $invoice,
					'tax' => $tax,
					'discount' => $discount,
					'exonerate' => $exonerate,
					'total' => $total,
					'cantidad_item' => $cantidad_item,
				]);

				
				$content .= $this->renderAjax('_rowHeaderItem', []);
				$total_abonado = 0;				
				foreach ($items as $item) {
					$content .= $this->renderAjax('_rowContentItem', [
						'invoice' => $invoice,
						'item' => $item,
					]);
					$total_abonado += $item->amount;
				}
				$content .= $this->renderAjax('_rowTotalItem', [
					'total_abonado' => number_format($total_abonado, 2, '.', ','),
				]);

				// Adicionar una fila vacia como separador
				$content .= $this->renderAjax('_rowEmpty', [
				]);			
			}

			$content .= $this->renderAjax('_footer_xls', []);
			echo $this->download($content);
			exit;
		}
		*/

	public function actionIndex()
	{
		$model = new SinpeReportForm();
		$fecha = new \DateTime();
		$fecha->modify('last day of this month');
		$ultimo_dia_mes = $fecha->format('d');
		$model->fecha = '01' . '-' . date('m') . '-' . date('Y') . ' - ' . $ultimo_dia_mes . '-' . date('m') . '-' . date('Y');
		$model->estado = 3;
		$date_title = '';

		$total_factura_amount = 0;
		$total_amount = 0; 

		if ($model->load(Yii::$app->request->post())) {

			//fecha de pago
			$filter_fechas = explode (" - ", $model->fecha);
			if (count($filter_fechas) == 2)
			{
				if ($filter_fechas[0] == $filter_fechas[1]) {
					$date_title = 'DEL DÍA ' . date('d-M-Y', strtotime($filter_fechas[0]));
				} else {
					$date_title = 'DESDE ' . date('d-M-Y', strtotime($filter_fechas[0])) . ' hasta ' . date('d-M-Y', strtotime($filter_fechas[1]));
				}
			}

			$agente = User::getFullNameByUserId($model->collector);
			$content = $this->renderAjax('_01header_xls', ['agente'=>$agente, 'date_title'=>$date_title]);

		
			$datos = $this->getBancos($model);
			foreach ($datos as $dato) {
				
				$content .= $this->renderAjax('_02rowHeaderItem', ['banco'=>$dato['name']]);
				$content .= $this->renderAjax('_03rowHeader', []);
				$abonos = $this->getAbonos($model, $dato['bank_id']);

				$subtotal = 0;
				foreach ($abonos as $b) 
				{
					$total_factura_amount += $b['amount'];
					$total_amount += $b['amount'];
					$subtotal += $b['amount'];
					$content .= $this->renderAjax('_04rowContent', [
						'data' => $b,
					]);
				}

				$content .= $this->renderAjax('_041rowSubTotal', [
					'titulo' => 'TOTAL',
					'total' => number_format($subtotal, 2, '.', ','),
				]);

				$content .= $this->renderAjax('_rowEmpty', []);			
			}

			if (!empty($datos)) {
				$content .= $this->renderAjax('_05rowTotal', [
					'titulo' => 'TOTAL GENERAL',
					'total' => number_format($total_amount, 2, '.', ','),
				]);
			}

			//$content .= $this->renderAjax('_footer_xls', []);
			echo $this->download($content);
			exit;
		}

		return $this->render('_reportForm', [
			'model' => $model,
		]);
	}

	public function getBancos($model)
	{
		$is_point_sale = 0; // facturas de almacen
		$datos = [];
		$query = InvoiceAbonos::find()->select([new \yii\db\Expression("DISTINCT invoice_abonos.bank_id"), 'banks.name'])
			->join('INNER JOIN', 'invoice', 'invoice.id = invoice_abonos.invoice_id')
			->join('INNER JOIN', 'customer', 'invoice.customer_id = customer.id')
			->join('LEFT JOIN', 'banks', 'invoice_abonos.bank_id = banks.id')
			->join('INNER JOIN', 'payment_method', 'invoice_abonos.payment_method_id = payment_method.id')
			->join('INNER JOIN', 'boxes', "invoice.box_id = boxes.id AND boxes.is_point_sale = " . $is_point_sale . "")
			->join('INNER JOIN', 'seller_has_invoice', "seller_has_invoice.invoice_id = invoice.id")
			->join('LEFT JOIN', 'collector_has_invoice', "collector_has_invoice.invoice_id = invoice.id")
			->join('LEFT JOIN', 'user', "collector_has_invoice.collector_id = user.id")
			->Where([
				//'invoice.status' => UtilsConstants::INVOICE_STATUS_PENDING,
				'status_hacienda' => UtilsConstants::HACIENDA_STATUS_ACCEPTED,
				'invoice_abonos.payment_method_id' => PaymentMethod::PAYMENT_SINPE_MOVIL
			])
			->orderBy('name ASC');



		$filter_fechas = explode(" - ", $model->fecha);
		if (count($filter_fechas) == 2) {
			$DateStart = date('Y-m-d', strtotime($filter_fechas[0]));
			$DateEnd = date('Y-m-d', strtotime($filter_fechas[1]));
			$query->andFilterWhere(
				['between', 'invoice_abonos.emission_date', $DateStart, $DateEnd]
			);
		}

		//bank
		if (!is_null($model->bank) && !empty($model->bank)) {
			$query->andWhere(['invoice_abonos.bank_id' => $model->bank]);
		}

		//Customer
		if (!is_null($model->cliente) && !empty($model->cliente)) {
			$query->andWhere(['invoice.customer_id' => $model->cliente]);
		}

		//Vendedor
		if (!is_null($model->seller) && !empty($model->seller)) {
			$query->andWhere(['seller_has_invoice.seller_id' => $model->seller]);
		}

		//Cobrador
		if (!is_null($model->collector) && !empty($model->collector)) {
			$query->andWhere(['collector_has_invoice.collector_id' => $model->collector]);
		}

		//Estado
		if (!is_null($model->estado) && !empty($model->estado)) {
			$query->andWhere(['invoice.status_hacienda' => $model->estado]);
		}

		//$query->orderBy('invoice.consecutive ASC, customer.name ASC');
		$datos = $query->asArray()->all();
		return $datos;
	}

	public function getAbonos($model, $bank_id)
	{
		$is_point_sale = 0; // facturas de almacen
		$datos = [];

		$query = InvoiceAbonos::find()->select([
			new \yii\db\Expression("DISTINCT invoice_abonos.id"), 'invoice_abonos.bank_id', 'invoice.id as factura_id', 'invoice.consecutive', 'invoice_abonos.emission_date',
			new \yii\db\Expression("CONCAT_WS('-', customer.name, customer.commercial_name) as customer"),
			'invoice_abonos.reference', 'payment_method.name as payment_method', 'banks.name as bank',
			new \yii\db\Expression("CONCAT_WS('-', user.name, user.last_name) as collector"), 'invoice_abonos.amount'
		])
			->join('INNER JOIN', 'invoice', 'invoice.id = invoice_abonos.invoice_id')
			->join('INNER JOIN', 'customer', 'invoice.customer_id = customer.id')
			->join('LEFT JOIN', 'banks', 'invoice_abonos.bank_id = banks.id')
			->join('INNER JOIN', 'payment_method', 'invoice_abonos.payment_method_id = payment_method.id')
			->join('INNER JOIN', 'boxes', "invoice.box_id = boxes.id AND boxes.is_point_sale = " . $is_point_sale . "")
			->join('INNER JOIN', 'seller_has_invoice', "seller_has_invoice.invoice_id = invoice.id")
			->join('LEFT JOIN', 'collector_has_invoice', "collector_has_invoice.invoice_id = invoice.id")
			->join('LEFT JOIN', 'user', "collector_has_invoice.collector_id = user.id")
			->Where([
				//'invoice.status' => UtilsConstants::INVOICE_STATUS_PENDING,
				'status_hacienda' => UtilsConstants::HACIENDA_STATUS_ACCEPTED,
				'invoice_abonos.payment_method_id' => PaymentMethod::PAYMENT_SINPE_MOVIL,
				'invoice_abonos.bank_id'=>$bank_id
			])
			->orderBy('invoice.consecutive, invoice_abonos.bank_id, invoice_abonos.reference ASC');


		$filter_fechas = explode(" - ", $model->fecha);
		if (count($filter_fechas) == 2) {
			$DateStart = date('Y-m-d', strtotime($filter_fechas[0])). ' 00:00:00';
			$DateEnd = date('Y-m-d', strtotime($filter_fechas[1])). ' 23:59:59';
			$query->andFilterWhere(
				['between', 'invoice_abonos.emission_date', $DateStart, $DateEnd]
			);
		}

		//bank
		if (!is_null($model->bank) && !empty($model->bank)) {
			$query->andWhere(['invoice_abonos.bank_id' => $model->bank]);
		}

		//Customer
		if (!is_null($model->cliente) && !empty($model->cliente)) {
			$query->andWhere(['invoice.customer_id' => $model->cliente]);
		}

		//Vendedor
		if (!is_null($model->seller) && !empty($model->seller)) {
			$query->andWhere(['seller_has_invoice.seller_id' => $model->seller]);
		}

		//Cobrador
		if (!is_null($model->collector) && !empty($model->collector)) {
			$query->andWhere(['collector_has_invoice.collector_id' => $model->collector]);
		}

		//Estado
		if (!is_null($model->estado) && !empty($model->estado)) {
			$query->andWhere(['invoice.status_hacienda' => $model->estado]);
		}

		//$query->orderBy('invoice.consecutive ASC, customer.name ASC');
		$datos = $query->asArray()->all();
		return $datos;
	}	
}
