<?php

namespace backend\models\business;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\GlobalFunctions;
use common\models\User;
use backend\models\nomenclators\ConditionSale;
use backend\models\nomenclators\UtilsConstants;
use backend\models\nomenclators\PaymentMethod;
use Yii;

/**
 * InvoiceSearch represents the model behind the search form of `backend\models\business\Invoice`.
 */
class InvoiceSearch extends Invoice
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'branch_office_id', 'customer_id', 'credit_days_id', 'condition_sale_id', 'currency_id', 'status', 'status_hacienda', 'route_transport_id', 'invoice_type'], 'integer'],
            [['consecutive', 'emission_date', 'delivery_time', 'observations', 'created_at', 'updated_at', 'box_id', 'commercial_name', 'sellers', 'collectors', 'printed', 'printed_user', 'user_id', 'contract', 'confirmation_number'], 'safe'],
            [['change_type', 'total_tax', 'total_discount', 'total_exonerado', 'total_comprobante'], 'number'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params, $is_point_sale)
    {                
        $query = Invoice::find()->join('INNER JOIN', 'customer', 'invoice.customer_id = customer.id')
                                ->join('INNER JOIN', 'branch_office', 'invoice.branch_office_id = branch_office.id')
                                ->join('INNER JOIN', 'boxes', "invoice.box_id = boxes.id AND boxes.is_point_sale = ".$is_point_sale."")
                                ->orderBy('invoice.id DESC, emission_date DESC');
                                
		$dataProvider = new ActiveDataProvider([
			'query' => $query,
			'pagination' => [
				'params' => \common\widgets\GridView::getMergedFilterStateParams(),
			],
			'sort' => [
                'defaultOrder' => ['id' =>SORT_DESC],
				'params' => \common\widgets\GridView::getMergedFilterStateParams(),
			],
		]);
		// Filter model
		$this->load(\common\widgets\GridView::getMergedFilterStateParams());                 

        // descomenta y utiliza tu relación con las traducciones para poder cargar los atributos de traducción
        // $query->leftJoin('table_lang',"table.id = table_lang.table_id AND table_lang.language='".Yii::$app->language."'");


        // Check if any filter is applied
        $filterApplied = $this->validate() && (
            !empty($this->id) || !empty($this->branch_office_id) || !empty($this->customer_id) ||
            !empty($this->credit_days_id) || !empty($this->condition_sale_id) || !empty($this->change_type) ||
            !empty($this->currency_id) || !empty($this->status) || !empty($this->status_hacienda) ||
            !empty($this->route_transport_id) || !empty($this->invoice_type) || !empty($this->created_at) ||
            !empty($this->updated_at) || !empty($this->box_id) || !empty($this->status_cuenta_cobrar) ||
            !empty($this->total_tax) || !empty($this->total_discount) || !empty($this->total_exonerado) ||
            !empty($this->total_comprobante) || !empty($this->sellers) || !empty($this->collectors) ||
            !empty($this->printed) || !empty($this->contract) || !empty($this->confirmation_number) ||
            !empty($this->consecutive) || !empty($this->commercial_name) || !empty($this->printed_user) ||
            !empty($this->observations) || !empty($this->emission_date)
        );

        // Reset pagination if any filter is applied
        if ($filterApplied) {
            $dataProvider->pagination->page = 0;
        }


        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $total_comprobante = $this->total_comprobante;
        if (isset($this->total_comprobante) && !empty($this->total_comprobante)){
            $total_comprobante = str_replace('.', '', $this->total_comprobante);
            $total_comprobante = str_replace(',', '.', $total_comprobante);
        }      
        
    	$filter_fechas = explode (" - ", $this->emission_date);       
    	if (count($filter_fechas) == 2)
    	{
    		$DateStart = date('Y-m-d', strtotime($filter_fechas[0])).' 00:00:00';
    		$DateEnd = date('Y-m-d', strtotime($filter_fechas[1])). ' 23:59:59';
    		$query->andFilterWhere(
    			['between', 'emission_date', $DateStart, $DateEnd]);
    	}	        

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'branch_office_id' => $this->branch_office_id,
            'customer_id' => $this->customer_id,
            'credit_days_id' => $this->credit_days_id,
            'invoice.condition_sale_id' => $this->condition_sale_id,
            'change_type' => $this->change_type,
            'currency_id' => $this->currency_id,
            'invoice.status' => $this->status,
            'invoice.status_hacienda' => $this->status_hacienda,
            'invoice.route_transport_id' => $this->route_transport_id,
            'invoice_type' => $this->invoice_type,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'box_id' => $this->box_id,
            'status_cuenta_cobrar'=>$this->status_cuenta_cobrar,            
            'total_tax' => $this->total_tax,
            'total_discount'=> $this->total_discount,
            'total_exonerado'=> $this->total_exonerado, 
            'total_comprobante'=> $total_comprobante,
            'seller_has_invoice.seller_id'=> $this->sellers,
            'collector_has_invoice.collector_id'=> $this->collectors,
            'printed' => $this->printed,
            'contract' => $this->contract,
            'confirmation_number' => $this->confirmation_number,
            'invoice.user_id'=> $this->user_id,
        ]);

        $query->andFilterWhere(['LIKE', 'consecutive', $this->consecutive])
            ->andFilterWhere(['LIKE', 'customer.commercial_name', $this->commercial_name])
            ->andFilterWhere(['LIKE', 'printed_user', $this->printed_user])            
            ->andFilterWhere(['LIKE', 'observations', $this->observations]);

        return $dataProvider;
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function AccountReceivablePendientesSearch($params)
    {
        $is_point_sale = 0; // facturas de almacen
        $dias_credito = 8;
        $query = Invoice::find()->select("invoice.*, CAST(NOW() AS date) - CAST(emission_date AS date) as dias_trascurridos, 
                                               (CAST(CAST(NOW() AS date) - CAST(emission_date AS date) as integer)) - ".$dias_credito." AS dias_vencidos")
                                //->join('INNER JOIN', 'credit_days', 'invoice.credit_days_id = credit_days.id')
                                ->join('INNER JOIN', 'customer', 'invoice.customer_id = customer.id')
                                ->join('INNER JOIN', 'boxes', "invoice.box_id = boxes.id AND boxes.is_point_sale = ".$is_point_sale."")
                                ->Where([
                                    //'invoice.condition_sale_id' => ConditionSale::CREDITO,
                                    'invoice.status'=>UtilsConstants::INVOICE_STATUS_PENDING,
                                    'status_hacienda'=> [UtilsConstants::HACIENDA_STATUS_ACCEPTED, UtilsConstants::HACIENDA_STATUS_CREDIT_NOTE, 
                                                            UtilsConstants::HACIENDA_STATUS_DEBIT_NOTE, UtilsConstants::HACIENDA_STATUS_CREDIT_NOTE_PARTIAL,
                                                                UtilsConstants::HACIENDA_STATUS_DEBIT_NOTE_PARTIAL]
                                ])
                                ->join('INNER JOIN', 'seller_has_invoice', "seller_has_invoice.invoice_id = invoice.id")
                                ->join('LEFT JOIN', 'collector_has_invoice', "collector_has_invoice.invoice_id = invoice.id")
                                ->orderBy('status ASC, dias_vencidos DESC');
                                //->orderBy('status_cuenta_cobrar ASC, dias_vencidos DESC');
                                          
		// DataProvider
		$dataProvider = new ActiveDataProvider([
			'query' => $query,
			'pagination' => [
				'params' => \common\widgets\GridView::getMergedFilterStateParams(),
			],
			'sort' => [
                'defaultOrder' => ['status' =>SORT_ASC],
				'params' => \common\widgets\GridView::getMergedFilterStateParams(),
			],
		]);
		// Filter model
		$this->load(\common\widgets\GridView::getMergedFilterStateParams());    


        // descomenta y utiliza tu relación con las traducciones para poder cargar los atributos de traducción
        // $query->leftJoin('table_lang',"table.id = table_lang.table_id AND table_lang.language='".Yii::$app->language."'");

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $total_comprobante = $this->total_comprobante;
        if (isset($this->total_comprobante) && !empty($this->total_comprobante)){
            $total_comprobante = str_replace('.', '', $this->total_comprobante);
            $total_comprobante = str_replace(',', '.', $total_comprobante);
        }        

    	$filter_fechas = explode (" - ", $this->emission_date);       
    	if (count($filter_fechas) == 2)
    	{
    		$DateStart = date('Y-m-d', strtotime($filter_fechas[0])).' 00:00:00';
    		$DateEnd = date('Y-m-d', strtotime($filter_fechas[1])). ' 23:59:59';
    		$query->andFilterWhere(
    			['between', 'invoice.emission_date', $DateStart, $DateEnd]);
    	}	        

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'branch_office_id' => $this->branch_office_id,
            'customer_id' => $this->customer_id,
            'credit_days_id' => $this->credit_days_id,
            'invoice.condition_sale_id' => $this->condition_sale_id,
            'change_type' => $this->change_type,
            'currency_id' => $this->currency_id,
            'invoice.status' => $this->status,
            'invoice.status_hacienda' => $this->status_hacienda,
            'invoice.route_transport_id' => $this->route_transport_id,
            'invoice_type' => $this->invoice_type,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'box_id' => $this->box_id,
            'status_cuenta_cobrar'=>$this->status_cuenta_cobrar,
            'total_comprobante'=> $total_comprobante,
            'seller_has_invoice.seller_id'=> $this->sellers,
            'collector_has_invoice.collector_id'=> $this->collectors,
            'printed' => $this->printed,
        ]);

        $query->andFilterWhere(['LIKE', 'consecutive', $this->consecutive])
            ->andFilterWhere(['LIKE', 'customer.commercial_name', $this->commercial_name])
            ->andFilterWhere(['LIKE', 'printed_user', $this->printed_user])        
            ->andFilterWhere(['LIKE', 'observations', $this->observations]);

        return $dataProvider;
    }  
    
    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function AccountReceivableCanceladasSearch($params)
    {
        $is_point_sale = 0; // facturas de almacen
        $dias_credito = 8;
        $query = Invoice::find()->select("invoice.*, CAST(NOW() AS date) - CAST(emission_date AS date) as dias_trascurridos, 
                                               (CAST(CAST(NOW() AS date) - CAST(emission_date AS date) as integer)) - ".$dias_credito." AS dias_vencidos")
                                //->join('INNER JOIN', 'credit_days', 'invoice.credit_days_id = credit_days.id')
                                ->join('INNER JOIN', 'customer', 'invoice.customer_id = customer.id')
                                ->join('INNER JOIN', 'boxes', "invoice.box_id = boxes.id AND boxes.is_point_sale = ".$is_point_sale."")
                                ->Where([
                                    //'invoice.condition_sale_id' => ConditionSale::CREDITO,
                                    'invoice.status'=>UtilsConstants::INVOICE_STATUS_CANCELLED,
                                    'status_hacienda'=> [UtilsConstants::HACIENDA_STATUS_ACCEPTED, UtilsConstants::HACIENDA_STATUS_CREDIT_NOTE, 
                                                            UtilsConstants::HACIENDA_STATUS_DEBIT_NOTE, UtilsConstants::HACIENDA_STATUS_CREDIT_NOTE_PARTIAL,
                                                                UtilsConstants::HACIENDA_STATUS_DEBIT_NOTE_PARTIAL]
                                ])
                                ->join('INNER JOIN', 'seller_has_invoice', "seller_has_invoice.invoice_id = invoice.id")
                                ->join('LEFT JOIN', 'collector_has_invoice', "collector_has_invoice.invoice_id = invoice.id")
                                ->orderBy('invoice.id DESC');
                                //->orderBy('status_cuenta_cobrar ASC, dias_vencidos DESC');
          
        /*                            
        if (isset($_REQUEST['InvoiceSearch']['sellers']) && !empty($_REQUEST['InvoiceSearch']['sellers'])){
            $query->join('INNER JOIN', 'seller_has_invoice', "seller_has_invoice.invoice_id = invoice.id");
            $query->where(['seller_has_invoice.seller_id' => $_REQUEST['InvoiceSearch']['sellers']]); 
        }       

        if (isset($_REQUEST['InvoiceSearch']['collectors']) && !empty($_REQUEST['InvoiceSearch']['collectors'])){
            $query->join('INNER JOIN', 'collector_has_invoice', "collector_has_invoice.invoice_id = invoice.id");
            $query->where(['collector_has_invoice.collector_id' => $_REQUEST['InvoiceSearch']['collectors']]); 
        } 
        */
        /*
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['status' =>SORT_ASC]]
        ]);

        $this->load($params);
        */
		$dataProvider = new ActiveDataProvider([
			'query' => $query,
			'pagination' => [
				'params' => \common\widgets\GridView::getMergedFilterStateParams(),
			],
			'sort' => [
                'defaultOrder' => ['status' =>SORT_ASC],
				'params' => \common\widgets\GridView::getMergedFilterStateParams(),
			],
		]);
		// Filter model
		$this->load(\common\widgets\GridView::getMergedFilterStateParams());            

        // descomenta y utiliza tu relación con las traducciones para poder cargar los atributos de traducción
        // $query->leftJoin('table_lang',"table.id = table_lang.table_id AND table_lang.language='".Yii::$app->language."'");

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $total_comprobante = $this->total_comprobante;
        if (isset($this->total_comprobante) && !empty($this->total_comprobante)){
            $total_comprobante = str_replace('.', '', $this->total_comprobante);
            $total_comprobante = str_replace(',', '.', $total_comprobante);
        }        

    	$filter_fechas = explode (" - ", $this->emission_date);       
    	if (count($filter_fechas) == 2)
    	{
    		$DateStart = date('Y-m-d', strtotime($filter_fechas[0])).' 00:00:00';
    		$DateEnd = date('Y-m-d', strtotime($filter_fechas[1])). ' 23:59:59';
    		$query->andFilterWhere(
    			['between', 'invoice.emission_date', $DateStart, $DateEnd]);
    	}	        

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'branch_office_id' => $this->branch_office_id,
            'customer_id' => $this->customer_id,
            'credit_days_id' => $this->credit_days_id,
            'invoice.condition_sale_id' => $this->condition_sale_id,
            'change_type' => $this->change_type,
            'currency_id' => $this->currency_id,
            'invoice.status' => $this->status,
            'invoice.status_hacienda' => $this->status_hacienda,
            'invoice.route_transport_id' => $this->route_transport_id,
            'invoice_type' => $this->invoice_type,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'box_id' => $this->box_id,
            'status_cuenta_cobrar'=>$this->status_cuenta_cobrar,
            'total_comprobante'=> $total_comprobante,
            'seller_has_invoice.seller_id'=> $this->sellers,
            'collector_has_invoice.collector_id'=> $this->collectors,
            'printed' => $this->printed,        
        ]);

        $query->andFilterWhere(['LIKE', 'consecutive', $this->consecutive])
            ->andFilterWhere(['LIKE', 'customer.commercial_name', $this->commercial_name])
            ->andFilterWhere(['LIKE', 'printed_user', $this->printed_user])        
            ->andFilterWhere(['LIKE', 'observations', $this->observations]);

        return $dataProvider;
    }     

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function AccountReceivableCanceladasNotasSearch($params)
    {
        $is_point_sale = 0; // facturas de almacen
        $dias_credito = 8;
        $query = Invoice::find()->select("invoice.*, CAST(NOW() AS date) - CAST(emission_date AS date) as dias_trascurridos, 
                                               (CAST(CAST(NOW() AS date) - CAST(emission_date AS date) as integer)) - ".$dias_credito." AS dias_vencidos")
                                //->join('INNER JOIN', 'credit_days', 'invoice.credit_days_id = credit_days.id')
                                ->join('INNER JOIN', 'customer', 'invoice.customer_id = customer.id')
                                ->join('INNER JOIN', 'boxes', "invoice.box_id = boxes.id AND boxes.is_point_sale = ".$is_point_sale."")
                                ->Where([
                                    //'invoice.condition_sale_id' => ConditionSale::CREDITO,
                                    'invoice.status'=>UtilsConstants::INVOICE_STATUS_CANCELLED,
                                    'status_hacienda'=> UtilsConstants::HACIENDA_STATUS_CREDIT_NOTE
                                ])
                                /*
                                ->OrWhere([
                                    //'invoice.condition_sale_id' => ConditionSale::CREDITO,
                                    'invoice.status'=>UtilsConstants::INVOICE_STATUS_CANCELLED,
                                    'status_hacienda'=> UtilsConstants::HACIENDA_STATUS_CREDIT_NOTE_PARTIAL,
                                ])
                                */
                                ->join('INNER JOIN', 'seller_has_invoice', "seller_has_invoice.invoice_id = invoice.id")
                                ->join('LEFT JOIN', 'collector_has_invoice', "collector_has_invoice.invoice_id = invoice.id")                                
                                ->orderBy('status ASC, dias_vencidos DESC');
                                //->orderBy('status_cuenta_cobrar ASC, dias_vencidos DESC');
        /*                                    
        if (isset($_REQUEST['InvoiceSearch']['sellers']) && !empty($_REQUEST['InvoiceSearch']['sellers'])){
            $query->join('INNER JOIN', 'seller_has_invoice', "seller_has_invoice.invoice_id = invoice.id");
            $query->where(['seller_has_invoice.seller_id' => $_REQUEST['InvoiceSearch']['sellers']]); 
        }       

        if (isset($_REQUEST['InvoiceSearch']['collectors']) && !empty($_REQUEST['InvoiceSearch']['collectors'])){
            $query->join('INNER JOIN', 'collector_has_invoice', "collector_has_invoice.invoice_id = invoice.id");
            $query->where(['collector_has_invoice.collector_id' => $_REQUEST['InvoiceSearch']['collectors']]); 
        } 
        */
        /*
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['status' =>SORT_ASC]]
        ]);

        $this->load($params);
        */
		$dataProvider = new ActiveDataProvider([
			'query' => $query,
			'pagination' => [
				'params' => \common\widgets\GridView::getMergedFilterStateParams(),
			],
			'sort' => [
                'defaultOrder' => ['status' =>SORT_ASC],
				'params' => \common\widgets\GridView::getMergedFilterStateParams(),
			],
		]);
		// Filter model
		$this->load(\common\widgets\GridView::getMergedFilterStateParams());            

        // descomenta y utiliza tu relación con las traducciones para poder cargar los atributos de traducción
        // $query->leftJoin('table_lang',"table.id = table_lang.table_id AND table_lang.language='".Yii::$app->language."'");

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $total_comprobante = $this->total_comprobante;
        if (isset($this->total_comprobante) && !empty($this->total_comprobante)){
            $total_comprobante = str_replace('.', '', $this->total_comprobante);
            $total_comprobante = str_replace(',', '.', $total_comprobante);
        }        

    	$filter_fechas = explode (" - ", $this->emission_date);       
    	if (count($filter_fechas) == 2)
    	{
    		$DateStart = date('Y-m-d', strtotime($filter_fechas[0])).' 00:00:00';
    		$DateEnd = date('Y-m-d', strtotime($filter_fechas[1])). ' 23:59:59';
    		$query->andFilterWhere(
    			['between', 'invoice.emission_date', $DateStart, $DateEnd]);
    	}	        

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'branch_office_id' => $this->branch_office_id,
            'customer_id' => $this->customer_id,
            'credit_days_id' => $this->credit_days_id,
            'invoice.condition_sale_id' => $this->condition_sale_id,
            'change_type' => $this->change_type,
            'currency_id' => $this->currency_id,
            'invoice.status' => $this->status,
            'invoice.status_hacienda' => $this->status_hacienda,
            'invoice.route_transport_id' => $this->route_transport_id,
            'invoice_type' => $this->invoice_type,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'box_id' => $this->box_id,
            'status_cuenta_cobrar'=>$this->status_cuenta_cobrar,
            'total_comprobante'=> $total_comprobante,
            'seller_has_invoice.seller_id'=> $this->sellers,
            'collector_has_invoice.collector_id'=> $this->collectors,
            'printed' => $this->printed,            
        ]);

        $query->andFilterWhere(['LIKE', 'consecutive', $this->consecutive])
            ->andFilterWhere(['LIKE', 'customer.commercial_name', $this->commercial_name])
            ->andFilterWhere(['LIKE', 'printed_user', $this->printed_user])        
            ->andFilterWhere(['LIKE', 'observations', $this->observations]);

        return $dataProvider;
    }         

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function AccountReceivableAbonosSearch($params)
    {
        $is_point_sale = 0; // facturas de almacen
        $dias_credito = 8;
        $query = Invoice::find()->select("invoice.*, CAST(NOW() AS date) - CAST(invoice.emission_date AS date) as dias_trascurridos, 
                                               (CAST(CAST(NOW() AS date) - CAST(invoice.emission_date AS date) as integer)) - ".$dias_credito." AS dias_vencidos")
                                //->join('INNER JOIN', 'credit_days', 'invoice.credit_days_id = credit_days.id')
                                ->join('INNER JOIN', 'invoice_abonos', 'invoice_abonos.invoice_id = invoice.id')
                                ->join('INNER JOIN', 'customer', 'invoice.customer_id = customer.id')
                                ->join('INNER JOIN', 'boxes', "invoice.box_id = boxes.id AND boxes.is_point_sale = ".$is_point_sale."")
                                ->Where([
                                    //'invoice.condition_sale_id' => ConditionSale::CREDITO,
                                    'invoice.status'=>UtilsConstants::INVOICE_STATUS_PENDING,
                                    'status_hacienda'=> [UtilsConstants::HACIENDA_STATUS_ACCEPTED, UtilsConstants::HACIENDA_STATUS_CREDIT_NOTE, 
                                                            UtilsConstants::HACIENDA_STATUS_DEBIT_NOTE, UtilsConstants::HACIENDA_STATUS_CREDIT_NOTE_PARTIAL,
                                                                UtilsConstants::HACIENDA_STATUS_DEBIT_NOTE_PARTIAL]
                                ])
                                ->join('INNER JOIN', 'seller_has_invoice', "seller_has_invoice.invoice_id = invoice.id")
                                ->join('LEFT JOIN', 'collector_has_invoice', "collector_has_invoice.invoice_id = invoice.id")                                  
                                ->orderBy('status ASC, dias_vencidos DESC');

		$dataProvider = new ActiveDataProvider([
			'query' => $query,
			'pagination' => [
				'params' => \common\widgets\GridView::getMergedFilterStateParams(),
			],
			'sort' => [
                'defaultOrder' => ['status' =>SORT_ASC],
				'params' => \common\widgets\GridView::getMergedFilterStateParams(),
			],
		]);
		// Filter model
		$this->load(\common\widgets\GridView::getMergedFilterStateParams());               

        // descomenta y utiliza tu relación con las traducciones para poder cargar los atributos de traducción
        // $query->leftJoin('table_lang',"table.id = table_lang.table_id AND table_lang.language='".Yii::$app->language."'");

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $total_comprobante = $this->total_comprobante;
        if (isset($this->total_comprobante) && !empty($this->total_comprobante)){
            $total_comprobante = str_replace('.', '', $this->total_comprobante);
            $total_comprobante = str_replace(',', '.', $total_comprobante);
        }        

    	$filter_fechas = explode (" - ", $this->emission_date);       
    	if (count($filter_fechas) == 2)
    	{
    		$DateStart = date('Y-m-d', strtotime($filter_fechas[0])).' 00:00:00';
    		$DateEnd = date('Y-m-d', strtotime($filter_fechas[1])). ' 23:59:59';
    		$query->andFilterWhere(
    			['between', 'invoice.emission_date', $DateStart, $DateEnd]);
    	}	        

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'branch_office_id' => $this->branch_office_id,
            'customer_id' => $this->customer_id,
            'credit_days_id' => $this->credit_days_id,
            'invoice.condition_sale_id' => $this->condition_sale_id,
            'change_type' => $this->change_type,
            'currency_id' => $this->currency_id,
            'invoice.status' => $this->status,
            'invoice.status_hacienda' => $this->status_hacienda,
            'invoice.route_transport_id' => $this->route_transport_id,
            'invoice_type' => $this->invoice_type,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'box_id' => $this->box_id,
            'status_cuenta_cobrar'=>$this->status_cuenta_cobrar,
            'total_comprobante'=> $total_comprobante,
            'seller_has_invoice.seller_id'=> $this->sellers,
            'collector_has_invoice.collector_id'=> $this->collectors,
            'printed' => $this->printed, 
        ]);

        $query->andFilterWhere(['LIKE', 'consecutive', $this->consecutive])
            ->andFilterWhere(['LIKE', 'customer.commercial_name', $this->commercial_name])
            ->andFilterWhere(['LIKE', 'printed_user', $this->printed_user])        
            ->andFilterWhere(['LIKE', 'observations', $this->observations]);

        return $dataProvider;
    }
    
/**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function AccountReceivableAbonosSinpeSearch($params)
    {
        $is_point_sale = 0; // facturas de almacen
        $dias_credito = 8;
        $query = Invoice::find()->select("invoice.*, CAST(NOW() AS date) - CAST(invoice.emission_date AS date) as dias_trascurridos, 
                                               (CAST(CAST(NOW() AS date) - CAST(invoice.emission_date AS date) as integer)) - ".$dias_credito." AS dias_vencidos")
                                //->join('INNER JOIN', 'credit_days', 'invoice.credit_days_id = credit_days.id')
                                ->join('INNER JOIN', 'invoice_abonos', 'invoice_abonos.invoice_id = invoice.id')
                                ->join('INNER JOIN', 'customer', 'invoice.customer_id = customer.id')
                                ->join('INNER JOIN', 'boxes', "invoice.box_id = boxes.id AND boxes.is_point_sale = ".$is_point_sale."")
                                ->Where([
                                    //'invoice.condition_sale_id' => ConditionSale::CREDITO,
                                    'invoice.status'=>UtilsConstants::INVOICE_STATUS_PENDING,
                                    'status_hacienda'=> [UtilsConstants::HACIENDA_STATUS_ACCEPTED, UtilsConstants::HACIENDA_STATUS_CREDIT_NOTE, 
                                                            UtilsConstants::HACIENDA_STATUS_DEBIT_NOTE, UtilsConstants::HACIENDA_STATUS_CREDIT_NOTE_PARTIAL,
                                                                UtilsConstants::HACIENDA_STATUS_DEBIT_NOTE_PARTIAL],
                                    'invoice_abonos.payment_method_id'=>PaymentMethod::PAYMENT_SINPE_MOVIL
                                ])
                                ->join('INNER JOIN', 'seller_has_invoice', "seller_has_invoice.invoice_id = invoice.id")
                                ->join('LEFT JOIN', 'collector_has_invoice', "collector_has_invoice.invoice_id = invoice.id")                                  
                                ->orderBy('status ASC, dias_vencidos DESC');

		$dataProvider = new ActiveDataProvider([
			'query' => $query,
			'pagination' => [
				'params' => \common\widgets\GridView::getMergedFilterStateParams(),
			],
			'sort' => [
                'defaultOrder' => ['status' =>SORT_ASC],
				'params' => \common\widgets\GridView::getMergedFilterStateParams(),
			],
		]);
		// Filter model
		$this->load(\common\widgets\GridView::getMergedFilterStateParams());               

        // descomenta y utiliza tu relación con las traducciones para poder cargar los atributos de traducción
        // $query->leftJoin('table_lang',"table.id = table_lang.table_id AND table_lang.language='".Yii::$app->language."'");

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $total_comprobante = $this->total_comprobante;
        if (isset($this->total_comprobante) && !empty($this->total_comprobante)){
            $total_comprobante = str_replace('.', '', $this->total_comprobante);
            $total_comprobante = str_replace(',', '.', $total_comprobante);
        }        

    	$filter_fechas = explode (" - ", $this->emission_date);       
    	if (count($filter_fechas) == 2)
    	{
    		$DateStart = date('Y-m-d', strtotime($filter_fechas[0])).' 00:00:00';
    		$DateEnd = date('Y-m-d', strtotime($filter_fechas[1])). ' 23:59:59';
    		$query->andFilterWhere(
    			['between', 'invoice.emission_date', $DateStart, $DateEnd]);
    	}	        

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'branch_office_id' => $this->branch_office_id,
            'customer_id' => $this->customer_id,
            'credit_days_id' => $this->credit_days_id,
            'invoice.condition_sale_id' => $this->condition_sale_id,
            'change_type' => $this->change_type,
            'currency_id' => $this->currency_id,
            'invoice.status' => $this->status,
            'invoice.status_hacienda' => $this->status_hacienda,
            'invoice.route_transport_id' => $this->route_transport_id,
            'invoice_type' => $this->invoice_type,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'box_id' => $this->box_id,
            'status_cuenta_cobrar'=>$this->status_cuenta_cobrar,
            'total_comprobante'=> $total_comprobante,
            'seller_has_invoice.seller_id'=> $this->sellers,
            'collector_has_invoice.collector_id'=> $this->collectors,
            'printed' => $this->printed, 
        ]);

        $query->andFilterWhere(['LIKE', 'consecutive', $this->consecutive])
            ->andFilterWhere(['LIKE', 'customer.commercial_name', $this->commercial_name])
            ->andFilterWhere(['LIKE', 'printed_user', $this->printed_user])        
            ->andFilterWhere(['LIKE', 'observations', $this->observations]);

        return $dataProvider;
    }  
    
/**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function PaymentSettlementSearch($params)
    {
        $is_point_sale = 0; // facturas de almacen
        $dias_credito = 8;
        $query = Invoice::find()->select("invoice.*, CAST(NOW() AS date) - CAST(emission_date AS date) as dias_trascurridos, 
                                               (CAST(CAST(NOW() AS date) - CAST(emission_date AS date) as integer)) - ".$dias_credito." AS dias_vencidos")
                                //->join('INNER JOIN', 'credit_days', 'invoice.credit_days_id = credit_days.id')
                                ->join('INNER JOIN', 'customer', 'invoice.customer_id = customer.id')
                                ->join('INNER JOIN', 'boxes', "invoice.box_id = boxes.id AND boxes.is_point_sale = ".$is_point_sale."")
                                ->Where([
                                    //'invoice.condition_sale_id' => ConditionSale::CREDITO,
                                    //'invoice.status'=>UtilsConstants::INVOICE_STATUS_PENDING,
                                    'status_hacienda'=> UtilsConstants::HACIENDA_STATUS_ACCEPTED,
                                ])
                                ->join('INNER JOIN', 'seller_has_invoice', "seller_has_invoice.invoice_id = invoice.id")
                                ->join('LEFT JOIN', 'collector_has_invoice', "collector_has_invoice.invoice_id = invoice.id")
                                ->orderBy('status ASC, dias_vencidos DESC');
                                //->orderBy('status_cuenta_cobrar ASC, dias_vencidos DESC');
                                          
		// DataProvider
		$dataProvider = new ActiveDataProvider([
			'query' => $query,
			'pagination' => [
				'params' => \common\widgets\GridView::getMergedFilterStateParams(),
			],
			'sort' => [
                'defaultOrder' => ['status' =>SORT_ASC],
				'params' => \common\widgets\GridView::getMergedFilterStateParams(),
			],
		]);
		// Filter model
		$this->load(\common\widgets\GridView::getMergedFilterStateParams());    


        // descomenta y utiliza tu relación con las traducciones para poder cargar los atributos de traducción
        // $query->leftJoin('table_lang',"table.id = table_lang.table_id AND table_lang.language='".Yii::$app->language."'");

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $total_comprobante = $this->total_comprobante;
        if (isset($this->total_comprobante) && !empty($this->total_comprobante)){
            $total_comprobante = str_replace('.', '', $this->total_comprobante);
            $total_comprobante = str_replace(',', '.', $total_comprobante);
        }        

    	$filter_fechas = explode (" - ", $this->emission_date);       
    	if (count($filter_fechas) == 2)
    	{
    		$DateStart = date('Y-m-d', strtotime($filter_fechas[0])).' 00:00:00';
    		$DateEnd = date('Y-m-d', strtotime($filter_fechas[1])). ' 23:59:59';
    		$query->andFilterWhere(
    			['between', 'invoice.emission_date', $DateStart, $DateEnd]);
    	}	        

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'branch_office_id' => $this->branch_office_id,
            'customer_id' => $this->customer_id,
            'credit_days_id' => $this->credit_days_id,
            'invoice.condition_sale_id' => $this->condition_sale_id,
            'change_type' => $this->change_type,
            'currency_id' => $this->currency_id,
            'invoice.status' => $this->status,
            'invoice.status_hacienda' => $this->status_hacienda,
            'invoice.route_transport_id' => $this->route_transport_id,
            'invoice_type' => $this->invoice_type,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'box_id' => $this->box_id,
            'status_cuenta_cobrar'=>$this->status_cuenta_cobrar,
            'total_comprobante'=> $total_comprobante,
            'seller_has_invoice.seller_id'=> $this->sellers,
            'collector_has_invoice.collector_id'=> $this->collectors,
            'printed' => $this->printed,
        ]);

        $query->andFilterWhere(['LIKE', 'consecutive', $this->consecutive])
            ->andFilterWhere(['LIKE', 'customer.commercial_name', $this->commercial_name])
            ->andFilterWhere(['LIKE', 'printed_user', $this->printed_user])        
            ->andFilterWhere(['LIKE', 'observations', $this->observations]);

        return $dataProvider;
    }    
}