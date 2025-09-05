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
 * AccountsPayableSearch represents the model behind the search form of `backend\models\business\Invoice`.
 */
class AccountsPayableSearch extends AccountsPayable
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['key', 'emission_date', 'currency', 'status', 'transmitter', 'proveedor'], 'safe'],
            [['status'], 'integer'],
            [['emission_date', ], 'safe'],
            [['total_invoice'], 'number'],
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
    /*
    public function search($params)
    {                
        $query = AccountsPayable::find();

        $dias_credito = 8;
        $query = AccountsPayable::find()->select("accounts_payable.*, CAST(NOW() AS date) - CAST(emission_date AS date) as dias_trascurridos, 
                                               (CAST(CAST(NOW() AS date) - CAST(emission_date AS date) as integer)) - ".$dias_credito." AS dias_vencidos")
                                ->Where([
                                    'accounts_payable.status'=>UtilsConstants::ACCOUNT_PAYABLE_PENDING,
                                ])
                                ->orderBy('status ASC, dias_vencidos DESC');

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

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $filter_fechas = explode (" - ", $this->emission_date);       
    	if (count($filter_fechas) == 2)
    	{
    		$DateStart = date('Y-m-d', strtotime($filter_fechas[0])).' 00:00:00';
    		$DateEnd = date('Y-m-d', strtotime($filter_fechas[1])). ' 23:59:59';
    		$query->andFilterWhere(
    			['between', 'emission_date', $DateStart, $DateEnd]);
    	}	        

        $total_invoice = $this->total_invoice;
        if (isset($this->total_invoice) && !empty($this->total_invoice)){
            $total_invoice = str_replace('.', '', $this->total_invoice);
            $total_invoice = str_replace(',', '.', $total_invoice);
        }             


        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'currency' => $this->currency,
            'change_type' => $this->change_type,
            'status' => $this->status,
            'total_invoice'=> $total_invoice,                
        ]);

        return $dataProvider;
    }
    */

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function AccountReceivablePendientesSearch($params)
    {
        $query = AccountsPayable::find()->where(['accounts_payable.status'=>UtilsConstants::ACCOUNT_PAYABLE_PENDING]);
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

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        if (isset($this->transmitter) && !empty($this->transmitter)){
            $query->join('INNER JOIN', 'documents', 'accounts_payable.key = documents.key');
            $query->andFilterWhere(['LIKE', 'documents.transmitter', $this->transmitter]);
        }

        $filter_fechas = explode (" - ", $this->emission_date);       
    	if (count($filter_fechas) == 2)
    	{
    		$DateStart = date('Y-m-d', strtotime($filter_fechas[0])).' 00:00:00';
    		$DateEnd = date('Y-m-d', strtotime($filter_fechas[1])). ' 00:00:59';
    		$query->andFilterWhere(
    			['between', 'emission_date', $DateStart, $DateEnd]);
    	}	        

        $total_invoice = $this->total_invoice;
        if (isset($this->total_invoice) && !empty($this->total_invoice)){
            $total_invoice = str_replace('.', '', $this->total_invoice);
            $total_invoice = str_replace(',', '.', $total_invoice);
        }             

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'currency' => $this->currency,
            'accounts_payable.status' => $this->status,
            'total_invoice'=> $total_invoice,                
        ]);

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
        $query = AccountsPayable::find()->where(['accounts_payable.status'=>UtilsConstants::ACCOUNT_PAYABLE_CANCELLED]);
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

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        if (isset($this->transmitter) && !empty($this->transmitter)){
            $query->join('INNER JOIN', 'documents', 'accounts_payable.key = documents.key');
            $query->andFilterWhere(['LIKE', 'documents.transmitter', $this->transmitter]);
        }

        $filter_fechas = explode (" - ", $this->emission_date);       
    	if (count($filter_fechas) == 2)
    	{
    		$DateStart = date('Y-m-d', strtotime($filter_fechas[0])).' 00:00:00';
    		$DateEnd = date('Y-m-d', strtotime($filter_fechas[1])). ' 00:00:59';
    		$query->andFilterWhere(
    			['between', 'emission_date', $DateStart, $DateEnd]);
    	}	        

        $total_invoice = $this->total_invoice;
        if (isset($this->total_invoice) && !empty($this->total_invoice)){
            $total_invoice = str_replace('.', '', $this->total_invoice);
            $total_invoice = str_replace(',', '.', $total_invoice);
        }             

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'currency' => $this->currency,
            'accounts_payable.status' => $this->status,
            'total_invoice'=> $total_invoice,                
        ]);

        return $dataProvider;
    }     
}