<?php

namespace backend\models\business;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\business\PurchaseOrder;
use common\models\GlobalFunctions;
use common\models\User;

/**
 * PurchaseOrderSearch represents the model behind the search form of `backend\models\business\PurchaseOrder`.
 */
class PurchaseOrderSearch extends PurchaseOrder
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'branch_office_id', 'box_id', 'customer_id', 'credit_days_id', 'condition_sale_id', 'currency_id', 'status', 'delivery_time_type', 'route_transport_id','invoice_type'], 'integer'],
            [['consecutive', 'request_date', 'delivery_time', 'observations', 'created_at', 'updated_at', 'commercial_name', 'collectors'], 'safe'],
            [['change_type', 'discount_percent'], 'number'],
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
    public function search($params)
    {
        $query = PurchaseOrder::find()->join('INNER JOIN', 'customer', 'purchase_order.customer_id = customer.id')
                                      ->join('INNER JOIN', 'collector_has_purchase_order', "collector_has_purchase_order.purchase_order_id = purchase_order.id")
                                      ->orderBy('purchase_order.status ASC, request_date DESC');

        // add conditions that should always apply here
        /*
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['id' => SORT_DESC]]
        ]);

        $this->load($params);
        */
        /*
        if (isset($_REQUEST['PurchaseOrderSearch']['collectors']) && (int)$_REQUEST['PurchaseOrderSearch']['collectors'] > 0)
        {
            $query->join('INNER JOIN', 'collector_has_purchase_order', "collector_has_purchase_order.purchase_order_id = purchase_order.id AND collector_has_purchase_order.collector_id = ".$_REQUEST['PurchaseOrderSearch']['collectors']."");  
        }
        */
        if (GlobalFunctions::getRol() === User::ROLE_FACTURADOR) {
            $userHasSeller = UserHasSeller::find()->select('seller_id')->where(['user_id'=>Yii::$app->user->id])->column();            
            if (empty($userHasSeller))
                $userHasSeller = -1;
            $this->collectors = $userHasSeller; 
            $query->where(['collector_has_purchase_order.collector_id' => $userHasSeller]);                       
        }

		$dataProvider = new ActiveDataProvider([
			'query' => $query,
			'pagination' => [
				'params' => \common\widgets\GridView::getMergedFilterStateParams(),
			],
			'sort' => [
                //'defaultOrder' => ['id' =>SORT_DESC],
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

    	$filter_fechas = explode (" - ", $this->request_date);       
    	if (count($filter_fechas) == 2)
    	{
    		$DateStart = date('Y-m-d', strtotime($filter_fechas[0])).' 00:00:00';
    		$DateEnd = date('Y-m-d', strtotime($filter_fechas[1])). ' 00:00:59';
    		$query->andFilterWhere(
    			['between', 'request_date', $DateStart, $DateEnd]);
    	}	               

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'branch_office_id' => $this->branch_office_id,
            'customer_id' => $this->customer_id,
            'credit_days_id' => $this->credit_days_id,
            'purchase_order.condition_sale_id' => $this->condition_sale_id,
            'change_type' => $this->change_type,
            'currency_id' => $this->currency_id,
            'purchase_order.status' => $this->status,
            'delivery_time_type' => $this->delivery_time_type,
            'discount_percent' => $this->discount_percent,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'purchase_order.route_transport_id' => $this->route_transport_id,
            'box_id' => $this->box_id,
            'collector_has_purchase_order.collector_id'=> $this->collectors,
            'invoice_type'=> $this->invoice_type
        ]);

        $query->andFilterWhere(['LIKE', 'consecutive', $this->consecutive])
            ->andFilterWhere(['LIKE', 'delivery_time', $this->delivery_time])
            ->andFilterWhere(['LIKE', 'customer.commercial_name', $this->commercial_name])
            ->andFilterWhere(['LIKE', 'observations', $this->observations]);

        return $dataProvider;
    }
}
