<?php

namespace backend\models\business;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\business\CashRegister;

/**
 * CashRegisteSearch represents the model behind the search form of `backend\models\business\CashRegister`.
 */
class CashRegisteSearch extends CashRegister
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'box_id', 'seller_id'], 'integer'],
            [['opening_date', 'opening_time', 'closing_date', 'closing_time'], 'safe'],
            [['initial_amount', 'end_amount', 'total_sales'], 'number'],
            [['status'], 'boolean'],
        ];
    }

    /**
     * {@inheritdoc}
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
    public function search($params, $box_id = NULL)
    {
        $query =  CashRegister::find()->select('cash_register.*, boxes.numero, boxes.name')
                                    ->join("INNER JOIN", "boxes", "cash_register.box_id = boxes.id")
                                    ->orderBy('id DESC');
        if (!is_null($box_id))
            $query->where(['box_id'=>$box_id]);

        // add conditions that should always apply here
        /*
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);
        */
		$dataProvider = new ActiveDataProvider([
			'query' => $query,
			'pagination' => [
				'params' => \common\widgets\GridView::getMergedFilterStateParams(),
			],
			'sort' => [
				'params' => \common\widgets\GridView::getMergedFilterStateParams(),
			],
		]);
		// Filter model
		$this->load(\common\widgets\GridView::getMergedFilterStateParams());              

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'box_id' => $this->box_id,
            'seller_id' => $this->seller_id,
            'opening_date' => $this->opening_date,
            'opening_time' => $this->opening_time,
            'closing_date' => $this->closing_date,
            'closing_time' => $this->closing_time,
            'initial_amount' => $this->initial_amount,
            'end_amount' => $this->end_amount,
            'total_sales' => $this->total_sales,
            'status' => $this->status,
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
    public function searchMovement($params, $box_id, $type)
    {
        $query =  CashRegister::find()->select('cash_register.*, boxes.numero as box_numero, boxes.name as box_name, movement_cash_register.movement_date, 
                                                movement_cash_register.movement_time, movement_cash_register_detail.comment,
                                                movement_cash_register_detail.count, movement_cash_register_detail.value')
                                      ->join("INNER JOIN", "boxes", "cash_register.box_id = boxes.id")
                                      ->join("INNER JOIN", "movement_cash_register", "movement_cash_register.cash_register_id = cash_register.id")
                                      ->join("INNER JOIN", "movement_cash_register_detail", "movement_cash_register_detail.movement_cash_register_id = movement_cash_register.cash_register_id")
                                      ->where(['cash_register.box_id'=>$box_id, 'movement_type_id'=>$type]);

        // add conditions that should always apply here
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'box_id' => $this->box_id,
            'seller_id' => $this->seller_id,
            'opening_date' => $this->opening_date,
            'opening_time' => $this->opening_time,
            'closing_date' => $this->closing_date,
            'closing_time' => $this->closing_time,
            'initial_amount' => $this->initial_amount,
            'end_amount' => $this->end_amount,
            'total_sales' => $this->total_sales,
            'status' => $this->status,
        ]);

        return $dataProvider;
    }  
}