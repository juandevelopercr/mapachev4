<?php

namespace backend\models\nomenclators;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\nomenclators\Cabys;

/**
 * CabysSearch represents the model behind the search form of `backend\models\nomenclators\Cabys`.
 */
class CabysSearch extends Cabys
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['category1', 'description1', 'category2', 'description2', 'category3', 'description3', 'category4', 'description4', 'category5', 'description5', 'category6', 'description6', 'category7', 'description7', 'category8', 'description8', 'code', 'description_service', 'tax', 'created_at', 'updated_at'], 'safe'],
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
        $query = Cabys::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['code' => SORT_ASC]]
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
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['LIKE', 'category1', $this->category1])
            ->andFilterWhere(['LIKE', 'description1', $this->description1])
            ->andFilterWhere(['LIKE', 'category2', $this->category2])
            ->andFilterWhere(['LIKE', 'description2', $this->description2])
            ->andFilterWhere(['LIKE', 'category3', $this->category3])
            ->andFilterWhere(['LIKE', 'description3', $this->description3])
            ->andFilterWhere(['LIKE', 'category4', $this->category4])
            ->andFilterWhere(['LIKE', 'description4', $this->description4])
            ->andFilterWhere(['LIKE', 'category5', $this->category5])
            ->andFilterWhere(['LIKE', 'description5', $this->description5])
            ->andFilterWhere(['LIKE', 'category6', $this->category6])
            ->andFilterWhere(['LIKE', 'description6', $this->description6])
            ->andFilterWhere(['LIKE', 'category7', $this->category7])
            ->andFilterWhere(['LIKE', 'description7', $this->description7])
            ->andFilterWhere(['LIKE', 'category8', $this->category8])
            ->andFilterWhere(['LIKE', 'description8', $this->description8])
            ->andFilterWhere(['LIKE', 'code', $this->code])
            ->andFilterWhere(['LIKE', 'description_service', $this->description_service])
            ->andFilterWhere(['LIKE', 'tax', $this->tax]);

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
