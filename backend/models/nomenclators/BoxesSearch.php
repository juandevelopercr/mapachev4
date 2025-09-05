<?php

namespace backend\models\nomenclators;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\nomenclators\Boxes;
use common\models\GlobalFunctions;
use common\models\User;
use Yii;
/**
 * BoxesSearch represents the model behind the search form of `backend\models\nomenclators\Boxes`.
 */
class BoxesSearch extends Boxes
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'branch_office_id', 'is_point_sale'], 'integer'],
            [['numero', 'name'], 'safe'],
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
        $query = Boxes::find();

        if (GlobalFunctions::getRol() === User::ROLE_AGENT) {
            $usuario = User::findOne(Yii::$app->user->id);
            $this->id = $usuario->box_id;
            $query->andFilterWhere([
                'id' => $this->id,
            ]);            
        }              

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
            'branch_office_id' => $this->branch_office_id,
            'is_point_sale'=> $this->is_point_sale,
        ]);

        $query->andFilterWhere(['LIKE', 'numero', $this->numero])
            ->andFilterWhere(['LIKE', 'name', $this->name]);

        return $dataProvider;
    }
}
