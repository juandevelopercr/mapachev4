<?php

namespace backend\models\nomenclators;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\nomenclators\CoinDenominations;

/**
 * CoinDenominationsSearch represents the model behind the search form of `backend\models\nomenclators\CoinDenominations`.
 */
class CoinDenominationsSearch extends CoinDenominations
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['description'], 'safe'],
            [['value'], 'number'],
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
        $query = CoinDenominations::find()->where(['<>', 'id', 17])->orderBy('value DESC');

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
            'value' => $this->value,
        ]);

        $query->andFilterWhere(['LIKE', 'description', $this->description]);

        return $dataProvider;
    }
}
