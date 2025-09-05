<?php

namespace backend\models\business;

use common\models\GlobalFunctions;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\business\PaymentOrder;

/**
 * PaymentOrderSearch represents the model behind the search form of `backend\models\business\PaymentOrder`.
 */
class PaymentOrderSearch extends PaymentOrder
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'project_id', 'supplier_id', 'status_payment_order_id', 'condition_sale_id', 'credit_days_id', 'currency_id', 'payout_status'], 'integer'],
            [['is_editable'], 'boolean'],
            [['number', 'request_date', 'require_date', 'observations', 'created_at', 'updated_at'], 'safe'],
            [['change_type'], 'number'],
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
        $query = PaymentOrder::find();

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

        // descomenta y utiliza tu relación con las traducciones para poder cargar los atributos de traducción
        // $query->leftJoin('table_lang',"table.id = table_lang.table_id AND table_lang.language='".Yii::$app->language."'");

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'change_type' => $this->change_type,
            'project_id' => $this->project_id,
            'supplier_id' => $this->supplier_id,
            'status_payment_order_id' => $this->status_payment_order_id,
            'condition_sale_id' => $this->condition_sale_id,
            'credit_days_id' => $this->credit_days_id,
            'currency_id' => $this->currency_id,
            'payout_status' => $this->payout_status,
            'is_editable' => $this->is_editable,
        ]);

        $query->andFilterWhere(['LIKE', 'number', $this->number])
            ->andFilterWhere(['LIKE', 'observations', $this->observations]);


        if(isset($this->request_date) && !empty($this->request_date))
        {
            $date_explode = explode(' - ',$this->request_date);
            $start_date = GlobalFunctions::formatDateToSaveInDB($date_explode[0]);
            $end_date = GlobalFunctions::formatDateToSaveInDB($date_explode[1]);

            $query->andFilterWhere(['>=', 'request_date', $start_date])
                ->andFilterWhere(['<=', 'request_date', $end_date]);

            $this->request_date = null;
        }

        if(isset($this->require_date) && !empty($this->require_date))
        {
            $date_explode = explode(' - ',$this->require_date);
            $start_date = GlobalFunctions::formatDateToSaveInDB($date_explode[0]);
            $end_date = GlobalFunctions::formatDateToSaveInDB($date_explode[1]);

            $query->andFilterWhere(['>=', 'require_date', $start_date])
                ->andFilterWhere(['<=', 'require_date', $end_date]);

            $this->require_date = null;
        }


        return $dataProvider;
    }
}
