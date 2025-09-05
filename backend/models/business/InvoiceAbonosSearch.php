<?php

namespace backend\models\business;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\business\InvoiceAbonos;

/**
 * InvoiceAbonosSearch represents the model behind the search form of `backend\models\business\InvoiceAbonos`.
 */
class InvoiceAbonosSearch extends InvoiceAbonos
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'invoice_id', 'payment_method_id', 'bank_id'], 'integer'],
            [['emission_date', 'reference', 'comment'], 'safe'],
            [['amount'], 'number'],
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
    public function search($params)
    {
        $query = InvoiceAbonos::find();

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
                'defaultOrder' => ['id' =>SORT_DESC],
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
            'invoice_id' => $this->invoice_id,
            'emission_date' => $this->emission_date,
            'payment_method_id' => $this->payment_method_id,
            'bank_id' => $this->bank_id,
            'amount' => $this->amount,
        ]);

        $query->andFilterWhere(['LIKE', 'reference', $this->reference])
            ->andFilterWhere(['LIKE', 'comment', $this->comment]);

        return $dataProvider;
    }
}
