<?php

namespace backend\models\business;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\business\ItemManualInvoice;

/**
 * ItemManualInvoiceSearch represents the model behind the search form of `backend\models\business\ItemManualInvoice`.
 */
class ItemManualInvoiceSearch extends ItemManualInvoice
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'invoice_id', 'service_id', 'user_id', 'unit_type_id'], 'integer'],
            [['description', 'created_at', 'updated_at'], 'safe'],
            [['quantity', 'price'], 'number'],
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
        $query = ItemManualInvoice::find();

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
            'invoice_id' => $this->invoice_id,
            'service_id' => $this->service_id,
            'quantity' => $this->quantity,
            'price' => $this->price,
            'user_id' => $this->user_id,
            'unit_type_id' => $this->unit_type_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['LIKE', 'description', $this->description]);

        return $dataProvider;
    }
}
