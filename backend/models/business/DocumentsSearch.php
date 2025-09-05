<?php

namespace backend\models\business;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\business\Documents;

/**
 * DocumentsSearch represents the model behind the search form of `backend\models\business\Documents`.
 */
class DocumentsSearch extends Documents
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'receiver_id', 'attempts_making_set', 'attempts_making_get', 'status', 'type'], 'integer'],
            [['key', 'consecutive', 'transmitter', 'transmitter_identification_type', 'transmitter_identification', 'document_type', 'emission_date', 'reception_date', 'url_xml', 'url_pdf', 'url_ahc', 'currency', 'transmitter_email', 'message_detail', 'condition_sale', 'type', 'proveedor'], 'safe'],
            [['change_type', 'total_tax', 'total_invoice', 'total_amount_tax_credit', 'total_amount_applicable_expense'], 'number'],
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
        $query = Documents::find()->orderBy('id DESC');

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
            /*
			'sort' => [                
				'params' => \common\widgets\GridView::getMergedFilterStateParams(),
			],
            */
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
            'receiver_id' => $this->receiver_id,
            'emission_date' => $this->emission_date,
            'xml_emission_date' => $this->xml_emission_date,
            'reception_date' => $this->reception_date,
            'change_type' => $this->change_type,
            'total_tax' => $this->total_tax,
            'total_invoice' => $this->total_invoice,
            'total_amount_tax_credit' => $this->total_amount_tax_credit,
            'total_amount_applicable_expense' => $this->total_amount_applicable_expense,
            'attempts_making_set' => $this->attempts_making_set,
            'attempts_making_get' => $this->attempts_making_get,
            'type'=>$this->type,
            'document_type'=>$this->document_type,
            'status' => $this->status,
        ]);

        $query->andFilterWhere(['LIKE', 'key', $this->key])
            ->andFilterWhere(['LIKE', 'consecutive', $this->consecutive])
            ->andFilterWhere(['LIKE', 'transmitter', $this->transmitter])
            ->andFilterWhere(['LIKE', 'transmitter_identification_type', $this->transmitter_identification_type])
            ->andFilterWhere(['LIKE', 'transmitter_identification', $this->transmitter_identification])
            ->andFilterWhere(['LIKE', 'url_xml', $this->url_xml])
            ->andFilterWhere(['LIKE', 'url_pdf', $this->url_pdf])
            ->andFilterWhere(['LIKE', 'url_ahc', $this->url_ahc])
            ->andFilterWhere(['LIKE', 'currency', $this->currency])
            ->andFilterWhere(['LIKE', 'transmitter_email', $this->transmitter_email])
            ->andFilterWhere(['LIKE', 'message_detail', $this->message_detail])
            ->andFilterWhere(['LIKE', 'condition_sale', $this->condition_sale]);

        return $dataProvider;
    }
}
