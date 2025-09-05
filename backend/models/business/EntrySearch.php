<?php

namespace backend\models\business;

use common\models\GlobalFunctions;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\business\Entry;

/**
 * EntrySearch represents the model behind the search form of `backend\models\business\Entry`.
 */
class EntrySearch extends Entry
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'supplier_id', 'branch_office_id', 'invoice_type'], 'integer'],
            [['order_purchase', 'invoice_date', 'invoice_number', 'observations', 'currency', 'created_at', 'updated_at'], 'safe'],
            [['amount', 'total_tax'], 'number'],
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
        $query = Entry::find();

        // add conditions that should always apply here

        /*
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

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'supplier_id' => $this->supplier_id,
            'branch_office_id' => $this->branch_office_id,
            'invoice_type' => $this->invoice_type,
            'amount' => $this->amount,
            'total_tax' => $this->total_tax,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['LIKE', 'order_purchase', $this->order_purchase])
            ->andFilterWhere(['LIKE', 'invoice_number', $this->invoice_number])
            ->andFilterWhere(['LIKE', 'observations', $this->observations]);


        //Ejemplo de configuración para utilización de DATERANGE
        if(isset($this->invoice_date) && !empty($this->invoice_date))
        {
            $date_explode = explode(' - ',$this->invoice_date);
            $start_date = GlobalFunctions::formatDateToSaveInDB($date_explode[0]);
            $end_date = GlobalFunctions::formatDateToSaveInDB($date_explode[1]);

            $query->andFilterWhere(['>=', 'invoice_date', $start_date])
                ->andFilterWhere(['<=', 'invoice_date', $end_date]);

            $this->invoice_date = null;
        }


        return $dataProvider;
    }
}
