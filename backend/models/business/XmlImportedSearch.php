<?php

namespace backend\models\business;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\business\XmlImported;

/**
 * XmlImportedSearch represents the model behind the search form of `backend\models\business\XmlImported`.
 */
class XmlImportedSearch extends XmlImported
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'user_id', 'entry_id', 'supplier_id', 'branch_office_id'], 'integer'],
            [['currency_code', 'invoice_key', 'invoice_activity_code', 'invoice_consecutive_number', 'invoice_date', 'xml_file', 'created_at', 'updated_at', 'supplier_identification', 'supplier_identification_type', 'supplier_name', 'supplier_province_code', 'supplier_canton_code', 'supplier_district_code', 'supplier_barrio_code', 'supplier_other_signals', 'supplier_phone_country_code', 'supplier_phone', 'supplier_email', 'invoice_condition_sale_code', 'invoice_credit_time_code', 'invoice_payment_method_code'], 'safe'],
            [['currency_change_value'], 'number'],
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
        $query = XmlImported::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['id' => SORT_DESC]]
        ]);

        $this->load($params);

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
            'currency_change_value' => $this->currency_change_value,
            'user_id' => $this->user_id,
            'entry_id' => $this->entry_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'supplier_id' => $this->supplier_id,
            'branch_office_id' => $this->branch_office_id,
        ]);

        $query->andFilterWhere(['LIKE', 'currency_code', $this->currency_code])
            ->andFilterWhere(['LIKE', 'invoice_key', $this->invoice_key])
            ->andFilterWhere(['LIKE', 'invoice_activity_code', $this->invoice_activity_code])
            ->andFilterWhere(['LIKE', 'invoice_consecutive_number', $this->invoice_consecutive_number])
            ->andFilterWhere(['LIKE', 'invoice_date', $this->invoice_date])
            ->andFilterWhere(['LIKE', 'xml_file', $this->xml_file])
            ->andFilterWhere(['LIKE', 'supplier_identification', $this->supplier_identification])
            ->andFilterWhere(['LIKE', 'supplier_identification_type', $this->supplier_identification_type])
            ->andFilterWhere(['LIKE', 'supplier_name', $this->supplier_name])
            ->andFilterWhere(['LIKE', 'supplier_province_code', $this->supplier_province_code])
            ->andFilterWhere(['LIKE', 'supplier_canton_code', $this->supplier_canton_code])
            ->andFilterWhere(['LIKE', 'supplier_district_code', $this->supplier_district_code])
            ->andFilterWhere(['LIKE', 'supplier_barrio_code', $this->supplier_barrio_code])
            ->andFilterWhere(['LIKE', 'supplier_other_signals', $this->supplier_other_signals])
            ->andFilterWhere(['LIKE', 'supplier_phone_country_code', $this->supplier_phone_country_code])
            ->andFilterWhere(['LIKE', 'supplier_phone', $this->supplier_phone])
            ->andFilterWhere(['LIKE', 'supplier_email', $this->supplier_email])
            ->andFilterWhere(['LIKE', 'invoice_condition_sale_code', $this->invoice_condition_sale_code])
            ->andFilterWhere(['LIKE', 'invoice_credit_time_code', $this->invoice_credit_time_code])
            ->andFilterWhere(['LIKE', 'invoice_payment_method_code', $this->invoice_payment_method_code]);

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
