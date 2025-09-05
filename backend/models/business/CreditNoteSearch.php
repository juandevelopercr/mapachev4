<?php

namespace backend\models\business;

use yii\base\Model;
use common\models\GlobalFunctions;
use common\models\User;
use backend\models\nomenclators\UtilsConstants;
use yii\data\ActiveDataProvider;
use Yii;

/**
 * CreditNoteSearch represents the model behind the search form of `backend\models\business\CreditNote`.
 */
class CreditNoteSearch extends CreditNote
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'branch_office_id', 'customer_id', 'credit_days_id', 'condition_sale_id', 'currency_id', 'status', 'status_hacienda', 
              'route_transport_id', 'credit_note_type', 'box_id'], 'integer'],
            [['consecutive', 'emission_date', 'delivery_time', 'observations', 'created_at', 'updated_at', 'sellers', 'contract', 'confirmation_number'], 'safe'],
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
        $query = CreditNote::find()->join('INNER JOIN', 'branch_office', 'credit_note.branch_office_id = branch_office.id')
                                ->join('INNER JOIN', 'boxes', "credit_note.box_id = boxes.id AND boxes.is_point_sale = ".$is_point_sale."");

        if (GlobalFunctions::getRol() === User::ROLE_AGENT) {            
            $this->sellers = Yii::$app->user->id;
            //$query->where(['seller_has_credit_note.seller_id' => Yii::$app->user->id]);
        }    
        /*    
        else
        {
            if (isset($_REQUEST['CreditNoteSearch']['sellers']) && !empty($_REQUEST['CreditNoteSearch']['sellers'])){
                $query->join('INNER JOIN', 'seller_has_credit_note', "seller_has_credit_note.credit_note_id = credit_note.id");
                $query->where(['seller_has_credit_note.seller_id' => $_REQUEST['CreditNoteSearch']['sellers']]); 
            }

            if (isset($_REQUEST['CreditNoteSearch']['collectors']) && !empty($_REQUEST['CreditNoteSearch']['collectors'])){
                $query->join('INNER JOIN', 'collector_has_credit_note', "collector_has_credit_note.credit_note_id = credit_note.id");
                $query->where(['collector_has_credit_note.collector_id' => $_REQUEST['CreditNoteSearch']['collectors']]); 
            }               
        }   
        */
        /*
        // add conditions that should always apply here
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['id' => SORT_DESC]]
        ]);

        $this->load($params);
        */
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
            'condition_sale_id' => $this->condition_sale_id,
            'change_type' => $this->change_type,
            'currency_id' => $this->currency_id,
            'status' => $this->status,
            'status_hacienda' => $this->status_hacienda,
            'route_transport_id' => $this->route_transport_id,
            'credit_note_type' => $this->credit_note_type,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'box_id'=> $this->id,
            'total_comprobante'=> $total_comprobante,
            'seller_has_credit_note.seller_id'=> $this->sellers,
            'collector_has_credit_note.seller_id'=> $this->collectors  
        ]);

        $query->andFilterWhere(['LIKE', 'consecutive', $this->consecutive])
            ->andFilterWhere(['LIKE', 'observations', $this->observations]);

        return $dataProvider;
    }

    
}
