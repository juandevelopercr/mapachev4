<?php

namespace backend\models\business;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\business\MovementCashRegisterDetail;

/**
 * MovementCashRegisterDetailSearch represents the model behind the search form of `backend\models\business\MovementCashRegisterDetail`.
 */
class MovementCashRegisterDetailSearch extends MovementCashRegisterDetail
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'movement_cash_register_id', 'count', 'coin_denomination_id', 'invoice_id'], 'integer'],
            [['value'], 'number'],
            [['comment'], 'safe'],
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
    public function search($params, $cash_register_id, $movement_type_id)
    {
        $query = MovementCashRegisterDetail::find()->select('movement_cash_register_detail.*, movement_cash_register.movement_date, movement_cash_register.movement_time')
                                                   ->join('INNER JOIN', 'movement_cash_register', 'movement_cash_register_detail.movement_cash_register_id = movement_cash_register.id')
                                                   ->join('INNER JOIN', 'cash_register', 'movement_cash_register.cash_register_id = cash_register.id')
                                                   ->where(['cash_register.id'=>$cash_register_id, 'movement_type_id'=>$movement_type_id]);
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
            'movement_cash_register_id' => $this->movement_cash_register_id,
            'value' => $this->value,
            'count' => $this->count,
            'coin_denomination_id'=> $this->coin_denomination_id,
        ]);

        $query->andFilterWhere(['LIKE', 'comment', $this->comment]);

        return $dataProvider;
    }
}
