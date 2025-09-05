<?php

namespace backend\models\business;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * ProformaSearch represents the model behind the search form of `backend\models\business\Proforma`.
 */
class ProformaSearch extends Proforma
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'branch_office_id', 'customer_id', 'credit_days_id', 'condition_sale_id', 'currency_id', 'status', 'delivery_time_type', 'seller_id', 'invoice_type'], 'integer'],
            [['consecutive', 'request_date', 'delivery_time', 'observations', 'created_at', 'updated_at', 'facturada'], 'safe'],
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
        $query = Proforma::find();

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
            'branch_office_id' => $this->branch_office_id,
            'customer_id' => $this->customer_id,
            'credit_days_id' => $this->credit_days_id,
            'condition_sale_id' => $this->condition_sale_id,
            'request_date' => $this->request_date,
            'change_type' => $this->change_type,
            'currency_id' => $this->currency_id,
            'status' => $this->status,
            'invoice_type'=> $this->invoice_type,
            'delivery_time_type' => $this->delivery_time_type,
            'discount_percent' => $this->discount_percent,
            'seller_id' => $this->seller_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['LIKE', 'consecutive', $this->consecutive])
            ->andFilterWhere(['LIKE', 'delivery_time', $this->delivery_time])
            ->andFilterWhere(['LIKE', 'observations', $this->observations]);

        /*
        //Ejemplo de configuración para utilización de DATERANGE
        if(isset($this->created_at) && !empty($this->created_at))
        {
            $date_explode = explode(' - ',$this->created_at);
            $start_date = GlobalFunctions::formatDateToSaveInDB($date_explode[0]).' 00:00:00';
            $end_date = GlobalFunctions::formatDateToSaveInDB($date_explode[1]).' 23:59:59';

            $query->andFilterWhere(['>=', 'created_at', $start_date])
                ->andFilterWhere(['<=', 'created_at', $end_date]);

            $this->created_at = null;
        }
        */

        return $dataProvider;
    }
}
