<?php

namespace backend\models\business;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\business\ItemImported;

/**
 * ItemImportedSearch represents the model behind the search form of `backend\models\business\ItemImported`.
 */
class ItemImportedSearch extends ItemImported
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'status', 'xml_imported_id', 'entry_id'], 'integer'],
            [['code', 'unit_measure', 'unit_measure_commercial', 'name', 'price_by_unit', 'created_at', 'updated_at', 'entry_id'], 'safe'],
            [['quantity', 'amount_total', 'discount_amount', 'tax_amount', 'tax_neto', 'amount_total_line'], 'number'],
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
        $query = ItemImported::find()->select(['item_imported.*','xml_imported.entry_id']);

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            //'sort' => ['defaultOrder' => ['id' => SORT_DESC]]
        ]);

        $this->load($params);

        // descomenta y utiliza tu relación con las traducciones para poder cargar los atributos de traducción
         $query->innerJoin('xml_imported',"xml_imported.id = item_imported.xml_imported_id");

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'item_imported.id' => $this->id,
            'item_imported.quantity' => $this->quantity,
            'item_imported.amount_total' => $this->amount_total,
            'item_imported.discount_amount' => $this->discount_amount,
            'item_imported.tax_amount' => $this->tax_amount,
            'item_imported.tax_neto' => $this->tax_neto,
            'item_imported.amount_total_line' => $this->amount_total_line,
            'item_imported.status' => $this->status,
            'item_imported.xml_imported_id' => $this->xml_imported_id,
            'item_imported.created_at' => $this->created_at,
            'item_imported.updated_at' => $this->updated_at,
            'xml_imported.entry_id' => $this->entry_id,
        ]);

        $query->andFilterWhere(['LIKE', 'item_imported.code', $this->code])
            ->andFilterWhere(['LIKE', 'item_imported.unit_measure', $this->unit_measure])
            ->andFilterWhere(['LIKE', 'item_imported.unit_measure_commercial', $this->unit_measure_commercial])
            ->andFilterWhere(['LIKE', 'item_imported.name', $this->name])
            ->andFilterWhere(['LIKE', 'item_imported.price_by_unit', $this->price_by_unit]);

        return $dataProvider;
    }
}
