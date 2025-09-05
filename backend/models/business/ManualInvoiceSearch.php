<?php

namespace backend\models\business;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\business\ManualInvoice;

/**
 * ManualInvoiceSearch represents the model behind the search form of `backend\models\business\ManualInvoice`.
 */
class ManualInvoiceSearch extends ManualInvoice
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'branch_office_id', 'supplier_id', 'currency_id', 'status'], 'integer'],
            [['consecutive', 'emission_date', 'observations', 'created_at', 'updated_at'], 'safe'],
            [['total_comprobante'], 'number'],
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
        $query = ManualInvoice::find();

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
            'branch_office_id' => $this->branch_office_id,
            'supplier_id' => $this->supplier_id,
            'currency_id' => $this->currency_id,
            'emission_date' => $this->emission_date,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'total_comprobante' => $this->total_comprobante,
        ]);

        $query->andFilterWhere(['LIKE', 'consecutive', $this->consecutive])
            ->andFilterWhere(['LIKE', 'observations', $this->observations]);

        return $dataProvider;
    }
}
