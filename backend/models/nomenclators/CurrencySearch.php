<?php

namespace backend\models\nomenclators;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\nomenclators\Currency;

/**
 * CurrencySearch represents the model behind the search form of `backend\models\nomenclators\Currency`.
 */
class CurrencySearch extends Currency
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'status'], 'integer'],
            [['name', 'symbol', 'description', 'created_at', 'updated_at'], 'safe'],
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
        $query = Currency::find();

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
            'change_type' => $this->change_type,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['LIKE', 'name', $this->name])
            ->andFilterWhere(['LIKE', 'symbol', $this->symbol])
            ->andFilterWhere(['LIKE', 'description', $this->description]);

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
