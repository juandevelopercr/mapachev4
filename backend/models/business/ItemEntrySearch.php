<?php

namespace backend\models\business;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\business\ItemEntry;

/**
 * ItemEntrySearch represents the model behind the search form of `backend\models\business\ItemEntry`.
 */
class ItemEntrySearch extends ItemEntry
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'entry_id', 'product_id', 'user_id'], 'integer'],
            [['product_code', 'product_description', 'observations', 'created_at', 'updated_at'], 'safe'],
            [['past_price', 'price', 'past_quantity', 'entry_quantity', 'new_quantity'], 'number'],
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
        $query = ItemEntry::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
           // 'sort' => ['defaultOrder' => ['id' => SORT_DESC]]
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
            'entry_id' => $this->entry_id,
            'product_id' => $this->product_id,
            'past_price' => $this->past_price,
            'price' => $this->price,
            'past_quantity' => $this->past_quantity,
            'entry_quantity' => $this->entry_quantity,
            'new_quantity' => $this->new_quantity,
            'user_id' => $this->user_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['LIKE', 'product_code', $this->product_code])
            ->andFilterWhere(['LIKE', 'product_description', $this->product_description])
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
