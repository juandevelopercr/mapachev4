<?php

namespace backend\models\business;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\business\Customer;

/**
 * CustomerSearch represents the model behind the search form of `backend\models\business\Customer`.
 */
class CustomerSearch extends Customer
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'status', 'identification_type_id', 'customer_type_id', 'customer_classification_id', 'province_id', 'canton_id', 'disctrict_id', 'condition_sale_id', 'credit_days_id', 'enable_credit_max', 'price_assigned', 'is_exonerate', 'exoneration_document_type_id'], 'integer'],
            [['name', 'commercial_name', 'code', 'description', 'created_at', 'updated_at', 'identification', 'country_code_phone', 'phone', 'country_code_fax', 'fax', 'email', 'address', 'other_signs', 'number_exoneration_doc', 'name_institution_exoneration', 'exoneration_date', 'route_transport_id', 'sellers', 'collectors', 'user_id', 'economicActivity'], 'safe'],
            [['credit_amount_colon', 'credit_amount_usd', 'exoneration_purchase_percent'], 'number'],
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
        $query = Customer::find();

		$dataProvider = new ActiveDataProvider([
			'query' => $query,
			'pagination' => [
				'params' => \common\widgets\GridView::getMergedFilterStateParams(),
			],
			'sort' => [
                'defaultOrder' => ['code' =>SORT_DESC],
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
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'identification_type_id' => $this->identification_type_id,
            'customer_type_id' => $this->customer_type_id,
            'customer_classification_id' => $this->customer_classification_id,
            'province_id' => $this->province_id,
            'canton_id' => $this->canton_id,
            'disctrict_id' => $this->disctrict_id,
            'condition_sale_id' => $this->condition_sale_id,
            'credit_amount_colon' => $this->credit_amount_colon,
            'credit_amount_usd' => $this->credit_amount_usd,
            'credit_days_id' => $this->credit_days_id,
            'enable_credit_max' => $this->enable_credit_max,
            'price_assigned' => $this->price_assigned,
            'route_transport_id'=> $this->route_transport_id,
            'is_exonerate' => $this->is_exonerate,
            'exoneration_document_type_id' => $this->exoneration_document_type_id,
            'exoneration_date' => $this->exoneration_date,
            'exoneration_purchase_percent' => $this->exoneration_purchase_percent,
            'seller_has_customer.seller_id'=> $this->sellers,
            'collector_has_customer.seller_id'=> $this->collectors,
            'user_id'=> $this->user_id,  
        ]);

        $query->andFilterWhere(['LIKE', 'name', $this->name])
            ->andFilterWhere(['LIKE', 'commercial_name', $this->commercial_name])
            ->andFilterWhere(['LIKE', 'code', $this->code])
            ->andFilterWhere(['LIKE', 'description', $this->description])
            ->andFilterWhere(['LIKE', 'identification', $this->identification])            
            ->andFilterWhere(['LIKE', 'country_code_phone', $this->country_code_phone])
            ->andFilterWhere(['LIKE', 'phone', $this->phone])
            ->andFilterWhere(['LIKE', 'country_code_fax', $this->country_code_fax])
            ->andFilterWhere(['LIKE', 'fax', $this->fax])
            ->andFilterWhere(['LIKE', 'email', $this->email])
            ->andFilterWhere(['LIKE', 'address', $this->address])
            ->andFilterWhere(['LIKE', 'other_signs', $this->other_signs])
            ->andFilterWhere(['LIKE', 'number_exoneration_doc', $this->number_exoneration_doc])
            ->andFilterWhere(['LIKE', 'name_institution_exoneration', $this->name_institution_exoneration]);

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
