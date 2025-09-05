<?php

namespace backend\modules\reportes\controllers;

use Yii;
use backend\models\business\Invoice;
use backend\models\business\ItemInvoice;
use backend\models\business\InvoiceAbonos;
use backend\models\business\CollectorHasInvoice;
use backend\models\business\Product;
use backend\models\business\ProductHasBranchOffice;
use backend\modules\reportes\models\LiquidacionReportForm;
use backend\models\nomenclators\PaymentMethod;
use backend\models\nomenclators\Family;
use backend\models\nomenclators\Category;
use backend\models\nomenclators\Country;
use backend\models\nomenclators\UtilsConstants;
use common\models\User;
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
class LiquidacionController extends ExportController
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
		$model = new LiquidacionReportForm();
		$fecha = new \DateTime();
		$fecha->modify('last day of this month');		
		//$ultimo_dia_mes = $fecha->format('d');
		$model->fecha = date('d') . '-' . date('m') . '-' . date('Y') . ' - ' . date('d') . '-' . date('m') . '-' . date('Y');
		/*
		$model->fecha = date('Y-m-d');
		*/
		$model->estado = 3;

		$total_saldo = 0;
		$total_abonado = 0;
		$total_abonado_dia = 0;
		$total_saldofinal = 0;

		$total_efectivo = 0;
		$total_deposito =  0;
		$total_sinpe = 0;				
		$date_title = '';

		if ($model->load(Yii::$app->request->post())) {

			// Mostrar Facturas con pagos en el dia
			$datos = $this->getInvoiceWithPayToDay($model);			
			$agente = User::getFullNameByUserId($model->collector);
			$content = '';

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

			$content .= $this->renderAjax('_01header_xls', ['agente'=>$agente, 'date_title'=>$date_title]);
			$title = 'COBROS '.$date_title;
			$content .= $this->renderAjax('_rowTitle', ['title'=>$title]);

			$content .= $this->renderAjax('_02rowHeader', []);
			$listdata = $this->DrawHtmlData($datos, $model);
			$content .= $listdata['content'];

			$total_saldo = $listdata['saldo'];
			$total_abonado = $listdata['abonado'];
			$total_abonado_dia = $listdata['abonado_dia'];
			$total_saldofinal = $listdata['saldofinal'];

			$total_efectivo = $listdata['total_efectivo'];
			$total_deposito =  $listdata['total_deposito'];
			$total_sinpe =  $listdata['total_sinpe'];
			
			$content .= $this->renderAjax('_04footer_xls', [				
				'total_saldo'=>$total_saldo, 
				'total_abonado'=>$total_abonado,
				'total_abonado_dia'=>$total_abonado_dia,
				'total_saldofinal'=>$total_saldofinal,

				'total_efectivo'=>$total_efectivo,
				'total_deposito'=>$total_deposito,
				'total_sinpe'=>$total_sinpe
			]);			

			if (!empty($datos)){
				// Fila vacia
				$content .= $this->renderAjax('_05rowEmpty', []);			
			}
			/*
			// Mostrar Facturas pendientes sin pagos en el dia	
			$title = 'COBROS PENDIENTES';
			$content .= $this->renderAjax('_rowTitle', ['title'=>$title]);

			$content .= $this->renderAjax('_02rowHeader', []);		
			$pendientes = $this->getInvoicePendientes($model);	
			$list = $this->DrawHtmlDataSinAbonos($pendientes, $model);
			$content .= $list['content'];

			$total_saldo += $list['saldo'];
			$total_abonado += $list['abonado'];
			$total_saldofinal += $list['saldofinal'];

			$total_efectivo += $list['total_efectivo'];
			$total_deposito +=  $list['total_deposito'];
			$total_sinpe += $list['total_sinpe'];			
		
			
			$content .= $this->renderAjax('_06footer_xls', [
				'total_saldo'=>$total_saldo, 
				'total_abonado'=>$total_abonado,
				'total_abonado_dia'=>$total_abonado_dia,
				'total_saldofinal'=>$total_saldofinal,

				'total_efectivo'=>$total_efectivo,
				'total_deposito'=>$total_deposito,
				'total_sinpe'=>$total_sinpe
			]);
			*/
			
			echo $this->download($content);
			exit;
		}

		return $this->render('_reportForm', [
			'model' => $model,
		]);
	}

	public function getInvoiceWithPayToDay($model)
	{
		$is_point_sale = 0; // facturas de almacen
		$dias_credito = 8;

		$query_with_pay = Invoice::find()->select([
			"invoice.*", new \yii\db\Expression("CAST(NOW() AS date) - CAST(invoice.emission_date AS date) as dias_trascurridos"),
			new \yii\db\Expression("CONCAT_WS('-', customer.name, customer.commercial_name) as customer"),
			new \yii\db\Expression("(CAST(CAST(NOW() AS date) - CAST(invoice.emission_date AS date) as integer))") . " - " . $dias_credito . " AS dias_vencidos"
		])
			//->join('INNER JOIN', 'credit_days', 'invoice.credit_days_id = credit_days.id')
			->join('INNER JOIN', 'customer', 'invoice.customer_id = customer.id')
			->join('INNER JOIN', 'boxes', "invoice.box_id = boxes.id AND boxes.is_point_sale = " . $is_point_sale . "")
			->join('INNER JOIN', 'invoice_abonos', 'invoice_abonos.invoice_id = invoice.id')
			//->join('LEFT JOIN', 'route_transport', 'invoice.route_transport_id = route_transport.id')
			/*
			->Where([
			'status_hacienda' => UtilsConstants::HACIENDA_STATUS_ACCEPTED,
			])
			*/
			->join('LEFT JOIN', 'collector_has_invoice', "collector_has_invoice.invoice_id = invoice.id")
			->orderBy('invoice_abonos.emission_date ASC, dias_vencidos DESC');

		//fecha de pago
		$filter_fechas = explode (" - ", $model->fecha);
		if (count($filter_fechas) == 2)
		{
			$DateStart = date('Y-m-d', strtotime($filter_fechas[0])).' 00:00:00';
			$DateEnd = date('Y-m-d', strtotime($filter_fechas[1])). ' 23:59:59';

			$query_with_pay->andFilterWhere(
				['between', 'invoice_abonos.emission_date', $DateStart, $DateEnd]);
		}	
		/*
		if (!is_null($model->fecha) && !empty($model->fecha)) {
			$query_with_pay->andWhere(['invoice_abonos.emission_date' => date('Y-m-d', strtotime($model->fecha))]);
		}
		*/

		//Customer
		if (!is_null($model->cliente) && !empty($model->cliente)) {
			$query_with_pay->andWhere(['invoice.customer_id' => $model->cliente]);
		}

		//Cobrador
		if (!is_null($model->collector) && !empty($model->collector)) {
			$query_with_pay->andWhere(['invoice_abonos.collector_id' => $model->collector]);
		}

		//Estado
		if (!is_null($model->estado) && !empty($model->estado)) {
			$query_with_pay->andWhere(['invoice.status_hacienda' => $model->estado]);
		}

		//$query_with_pay->orderBy('invoice.consecutive ASC, customer.name ASC');
		$datos = $query_with_pay->asArray()->all();

		return $datos;
	}
	
	public function getInvoicePendientes($model)
	{
		$is_point_sale = 0; // facturas de almacen
		$dias_credito = 8;

		$subquery = InvoiceAbonos::find()->select('invoice_id')->where('invoice_id = invoice.id')
															   ->andWhere(['=', 'emission_date', date('Y-m-d', strtotime($model->fecha))])
															   ->groupBy('invoice_id');

		$query_with_pay = Invoice::find()->select([
			"invoice.*", new \yii\db\Expression("CAST(NOW() AS date) - CAST(invoice.emission_date AS date) as dias_trascurridos"),
			new \yii\db\Expression("CONCAT_WS('-', customer.name, customer.commercial_name) as customer"),
			new \yii\db\Expression("(CAST(CAST(NOW() AS date) - CAST(invoice.emission_date AS date) as integer))") . " - " . $dias_credito . " AS dias_vencidos"
		])
			//->join('INNER JOIN', 'credit_days', 'invoice.credit_days_id = credit_days.id')
			->join('INNER JOIN', 'customer', 'invoice.customer_id = customer.id')
			->join('INNER JOIN', 'boxes', "invoice.box_id = boxes.id AND boxes.is_point_sale = " . $is_point_sale . "")
			->join('LEFT JOIN', 'invoice_abonos', 'invoice_abonos.invoice_id = invoice.id')
			//->join('LEFT JOIN', 'route_transport', 'invoice.route_transport_id = route_transport.id')
			->Where([
				'invoice.status'=>UtilsConstants::INVOICE_STATUS_PENDING,
			])			
			->join('LEFT JOIN', 'collector_has_invoice', "collector_has_invoice.invoice_id = invoice.id")
			->orderBy('invoice.emission_date ASC, dias_vencidos DESC');

		// Que no tenga abonos
		$query_with_pay->andWhere(['NOT IN', 'invoice.id', $subquery]);		

		/*
		//fecha de pago
		if (!is_null($model->fecha) && !empty($model->fecha)) {
			$query_with_pay->andWhere(['invoice_abonos.emission_date' => date('Y-m-d', strtotime($model->fecha))]);
		}
		*/

		//Customer
		if (!is_null($model->cliente) && !empty($model->cliente)) {
			$query_with_pay->andWhere(['invoice.customer_id' => $model->cliente]);
		}

		//Cobrador
		if (!is_null($model->collector) && !empty($model->collector)) {
			$query_with_pay->andWhere(['collector_has_invoice.collector_id' => $model->collector]);
		}

		//Estado
		if (!is_null($model->estado) && !empty($model->estado)) {
			$query_with_pay->andWhere(['invoice.status_hacienda' => $model->estado]);
		}

		//$query_with_pay->orderBy('invoice.consecutive ASC, customer.name ASC');
		$datos = $query_with_pay->asArray()->all();

		return $datos;
	}	

	public function DrawHtmlData($datos, $model)
	{
		$content = '';
		$total_saldo = 0;
		$total_abonado = 0;
		$total_abonado_dia = 0;
		$total_saldofinal = 0;

		$total_efectivo = 0;
		$total_deposito = 0;
		$total_sinpe = 0;

		foreach ($datos as $data) {

			$abonado = InvoiceAbonos::find()->where(['invoice_id' => $data['id']])->sum('amount');
			//$abonado_dia = InvoiceAbonos::find()->where(['invoice_id' => $data['id'], 'emission_date' => date('Y-m-d', strtotime($model->fecha))])->sum('amount');
			$items = InvoiceAbonos::find()->where(['invoice_id' => $data['id'], 'collector_id' => $model->collector])->orderBy('id ASC')->asArray()->all();

			$abonado = is_null($abonado) ? 0: $abonado;

			//die(var_dump($items));
			$data['invoice_type'] = UtilsConstants::getPreInvoiceSelectType($data['invoice_type']);
			$data['status'] = UtilsConstants::getInvoiceStatusSelectType($data['status']);

			$index = 1;
			$saldofinal = 0;
			foreach ($items as $item) {

				$abonado_dia = 0;

				$filter_fechas = explode(" - ", $model->fecha);
				$DateStart = -1;
				$DateEnd = -1;
				if (count($filter_fechas) == 2) {
					$DateStart = strtotime($filter_fechas[0] . ' 00:00:00');
					$DateEnd = strtotime($filter_fechas[1] . ' 23:59:59'); // Corregido aquí
				}   
				
				if ($DateStart != -1 && $DateEnd != -1 && strtotime($item['emission_date']) >= $DateStart && strtotime($item['emission_date']) <= $DateEnd) {
					$abonado_dia = is_null($item['amount']) ? 0 : $item['amount'];
				} else {
					$abonado_dia = 0; // Esto asegura que $abonado_dia esté definido incluso si no se cumple la condición
				}
				/*
				if (date('Y-m-d', strtotime($item['emission_date'])) == date('Y-m-d', strtotime($model->fecha)))
					$abonado_dia = is_null($item['amount']) ? 0: $item['amount'];
				*/
				
				$total_abonado_dia += $abonado_dia;

				if ($index == 1) {
					$saldo = $data['total_comprobante'];
					$abonado = $item['amount'];
					$saldofinal = $saldo - $abonado;
				} else {
					$saldo = $saldofinal;
					$abonado = $item['amount'];
					$saldofinal = $saldo - $abonado;
				}

				$total_saldo += $saldo;
				$total_abonado += $abonado;
				$total_saldofinal += $saldofinal;

				switch ($item['payment_method_id']) {
					case '1':
						$total_efectivo += $abonado_dia;
						break;
					case '2':
						$total_deposito += $abonado_dia;
						break;
					case '4':
						$total_deposito += $abonado_dia;
						break;	
					case '5':
						$total_efectivo += $abonado_dia;
						break;	
					case '7':
						$total_sinpe += $abonado_dia;
						break;																												
				}

				$content .= $this->renderAjax('_03rowContent', [
					'data' => $data,
					'abono' => $item,
					'saldo' => $saldo,
					'abonado' => $item['amount'],
					'abonado_dia' => $abonado_dia,
					'saldofinal' => $saldofinal,						
				]);
				$index++;
			}
		}

		$result = [
			'saldo'=>$total_saldo,
			'abonado'=>$total_abonado,
			'abonado_dia'=> $total_abonado_dia,
			'saldofinal'=>$total_saldofinal,

			'total_efectivo' => $total_efectivo,
			'total_deposito' => $total_deposito,
			'total_sinpe' => $total_sinpe,

			'content'=> $content
		];

		return $result;
	}

	public function DrawHtmlDataSinAbonos($datos, $model)
	{
		$content = '';
		$total_saldo = 0;
		$total_abonado = 0;
		$total_abonado_dia = 0;
		$total_saldofinal = 0;

		$total_efectivo = 0;
		$total_deposito = 0;
		$total_sinpe = 0;		

		foreach ($datos as $data) {

			$abonado = InvoiceAbonos::find()->where(['invoice_id' => $data['id']])->sum('amount');
			//$abonado_dia = InvoiceAbonos::find()->where(['invoice_id' => $data['id'], 'emission_date' => date('Y-m-d', strtotime($model->fecha))])->sum('amount');
			$items = InvoiceAbonos::find()->where(['invoice_id' => $data['id'], 'collector_id' => $model->collector])->orderBy('id ASC')->asArray()->all();

			$abonado = is_null($abonado) ? 0: $abonado;

			//die(var_dump($items));
			$data['invoice_type'] = UtilsConstants::getPreInvoiceSelectType($data['invoice_type']);
			$data['status'] = UtilsConstants::getInvoiceStatusSelectType($data['status']);

			$index = 1;
			$saldofinal = 0;
			if (!empty($items))
			{
				foreach ($items as $item) {

					$abonado_dia = 0;
					if (date('Y-m-d', strtotime($item['emission_date'])) == date('Y-m-d', strtotime($model->fecha)))
						$abonado_dia = is_null($item['amount']) ? 0: $item['amount'];
					
					$total_abonado_dia += $abonado_dia;

					if ($index == 1) {
						$saldo = $data['total_comprobante'];
						$abonado = $item['amount'];
						$saldofinal = $saldo - $abonado;
					} else {
						$saldo = $saldofinal;
						$abonado = $item['amount'];
						$saldofinal = $saldo - $abonado;
					}

					$total_saldo += $saldo;
					$total_abonado += $abonado;
					$total_saldofinal += $saldofinal;

					switch ($item['payment_method_id']) {
						case '1':
							$total_efectivo += $abonado_dia;
							break;
						case '2':
							$total_deposito += $abonado_dia;
							break;
						case '4':
							$total_deposito += $abonado_dia;
							break;	
						case '5':
							$total_efectivo += $abonado_dia;
							break;	
						case '7':
							$total_sinpe += $abonado_dia;
							break;																												
					}

					$content .= $this->renderAjax('_03rowContent', [
						'data' => $data,
						'abono' => $item,
						'saldo' => $saldo,
						'abonado' => $item['amount'],
						'abonado_dia' => $abonado_dia,
						'saldofinal' => $saldofinal,					
					]);
					$index++;
				}
			}
			else
			{
				$saldo = $data['total_comprobante'];			
				$saldofinal = $saldo;
	
				$abono['emission_date'] = date('d-m-Y', strtotime($model->fecha));
				$abono['amount'] = 0;
	
				$total_saldo += $saldo;
				$total_abonado += 0;
				$total_saldofinal += $saldofinal;			
	
				$content .= $this->renderAjax('_03rowContent', [
					'data' => $data,
					'saldo' => $saldo,
					'abonado' => 0,
					'abonado_dia' => 0,
					'saldofinal' => $saldofinal,
					'abono'=> $abono
				]);				
			}
		}

		$result = [
			'saldo'=>$total_saldo,
			'abonado'=>$total_abonado,
			'abonado_dia'=> $total_abonado_dia,
			'saldofinal'=>$total_saldofinal,

			'total_efectivo' => $total_efectivo,
			'total_deposito' => $total_deposito,
			'total_sinpe' => $total_sinpe,

			'content'=> $content
		];

		return $result;
	}

	/*
	public function DrawHtmlDataSinAbonos($datos, $model)
	{
		$content = '';
		$total_saldo = 0;
		$total_abonado = 0;
		$total_abonado_dia = 0;
		$total_saldofinal = 0;

		foreach ($datos as $data) {

			$data['invoice_type'] = UtilsConstants::getPreInvoiceSelectType($data['invoice_type']);
			$data['status'] = UtilsConstants::getInvoiceStatusSelectType($data['status']);

			$saldo = $data['total_comprobante'];			
			$saldofinal = $saldo;

			$abono['emission_date'] = date('d-m-Y', strtotime($model->fecha));
			$abono['amount'] = 0;

			$total_saldo += $saldo;
			$total_abonado += 0;
			$total_saldofinal += $saldofinal;			

			$content .= $this->renderAjax('_03rowContent', [
				'data' => $data,
				'saldo' => $saldo,
				'abonado' => 0,
				'abonado_dia' => 0,
				'saldofinal' => $saldofinal,
				'abono'=> $abono
			]);
		}

		$result = [
			'saldo'=>$total_saldo,
			'abonado'=>$total_abonado,
			'abonado_dia'=> $total_abonado_dia,
			'saldofinal'=>$total_saldofinal,
			'content'=> $content
		];

		return $result;
	}
	*/	
}