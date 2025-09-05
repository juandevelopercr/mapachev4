<?php

namespace backend\models\business;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\business\SupplierBankInformation;

/**
 * SupplierBankInformationSearch represents the model behind the search form of `backend\models\business\SupplierBankInformation`.
 */
class SupplierBankInformationSearch extends SupplierBankInformation
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'supplier_id'], 'integer'],
            [['banck_name', 'checking_account', 'customer_account', 'mobile_account', 'created_at', 'updated_at'], 'safe'],
            [['status'], 'boolean'],
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
        $query = SupplierBankInformation::find();

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
            'supplier_id' => $this->supplier_id,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['LIKE', 'banck_name', $this->banck_name])
            ->andFilterWhere(['LIKE', 'checking_account', $this->checking_account])
            ->andFilterWhere(['LIKE', 'customer_account', $this->customer_account])
            ->andFilterWhere(['LIKE', 'mobile_account', $this->mobile_account]);

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
