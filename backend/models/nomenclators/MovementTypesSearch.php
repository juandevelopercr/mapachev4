<?php

namespace backend\models\nomenclators;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\nomenclators\MovementTypes;

/**
 * MovementTypesSearch represents the model behind the search form of `backend\models\nomenclators\MovementTypes`.
 */
class MovementTypesSearch extends MovementTypes
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['nombre'], 'safe'],
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
        $query = MovementTypes::find();

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
        ]);

        $query->andFilterWhere(['LIKE', 'nombre', $this->nombre]);

        return $dataProvider;
    }
}
