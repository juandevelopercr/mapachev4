<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\User;

/**
 * UserSearch represents the model behind the search form about `common\models\User`.
 */
class UserSearch extends User
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'status', 'switch_status','branch_office_id'], 'integer'],
            [['username', 'auth_key', 'password_hash', 'password_reset_token', 'email', 'name', 'last_name', 'avatar', 'position', 'seniority', 'skills', 'personal_stuff', 'created_at', 'updated_at', 'switch_status', 'role'], 'safe'],
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
        $query = User::find()
            ->select([
                'user.*',
                'auth_assignment.item_name'
            ])
            ->leftJoin('auth_assignment','auth_assignment.user_id = id')
        ;

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['id' => SORT_DESC]]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'user.id' => $this->id,
            'user.status' => $this->status,
            'user.created_at' => $this->created_at,
            'user.updated_at' => $this->updated_at,
            'user.branch_office_id' => $this->branch_office_id,
            'auth_assignment.item_name' => $this->role,
        ]);

        $query->andFilterWhere(['LIKE', 'user.username', $this->username])
            ->andFilterWhere(['LIKE', 'user.auth_key', $this->auth_key])
            ->andFilterWhere(['LIKE', 'user.password_hash', $this->password_hash])
            ->andFilterWhere(['LIKE', 'user.password_reset_token', $this->password_reset_token])
            ->andFilterWhere(['LIKE', 'user.email', $this->email])
            ->andFilterWhere(['LIKE', 'user.name', $this->name])
            ->andFilterWhere(['LIKE', 'user.last_name', $this->last_name])
            ->andFilterWhere(['LIKE', 'user.avatar', $this->avatar])
            ->andFilterWhere(['LIKE', 'user.position', $this->position])
            ->andFilterWhere(['LIKE', 'user.seniority', $this->seniority])
            ->andFilterWhere(['LIKE', 'user.skills', $this->skills])
            ->andFilterWhere(['LIKE', 'user.personal_stuff', $this->personal_stuff]);

        return $dataProvider;
    }
}
