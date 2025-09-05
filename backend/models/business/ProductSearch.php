<?php

namespace backend\models\business;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\business\Product;

/**
 * ProductSearch represents the model behind the search form of `backend\models\business\Product`.
 */
class ProductSearch extends Product
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'cabys_id', 'family_id', 'category_id', 'unit_type_id', 'inventory_type_id', 'tax_type_id', 'tax_rate_type_id', 'exoneration_document_type_id'], 'integer'],
            [['code', 'image', 'description', 'entry_date', 'bar_code', 'location', 'branch', 'nature_discount', 'number_exoneration_doc', 'name_institution_exoneration', 'exoneration_date', 'created_at', 'updated_at','supplier_code'], 'safe'],
            [['initial_existence', 'min_quantity', 'max_quantity', 'package_quantity', 'price', 'percent1', 'price1', 'percent2', 'price2', 'percent3', 'price3', 'percent4', 'price4', 'percent_detail', 'price_detail', 'price_custom', 'discount_amount', 'tax_rate_percent', 'exoneration_purchase_percent'], 'number'],
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
        $query = Product::find();

        // add conditions that should always apply here
        /*
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['code' => SORT_ASC]]
        ]);

        $this->load($params);
        */
		$dataProvider = new ActiveDataProvider([
			'query' => $query,
			'pagination' => [
				'params' => \common\widgets\GridView::getMergedFilterStateParams(),
			],
			'sort' => [
                'defaultOrder' => ['code' =>SORT_DESC],
				'params' => \common\widgets\GridView::getMergedFilterStateParams(),
			],
		]);
		// Filter model
		$this->load(\common\widgets\GridView::getMergedFilterStateParams());           

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
            'entry_date' => $this->entry_date,
            'cabys_id' => $this->cabys_id,
            'family_id' => $this->family_id,
            'category_id' => $this->category_id,
            'unit_type_id' => $this->unit_type_id,
            'inventory_type_id' => $this->inventory_type_id,
            'initial_existence' => $this->initial_existence,
            'min_quantity' => $this->min_quantity,
            'max_quantity' => $this->max_quantity,
            'package_quantity' => $this->package_quantity,
            'price' => $this->price,
            'percent1' => $this->percent1,
            'price1' => $this->price1,
            'percent2' => $this->percent2,
            'price2' => $this->price2,
            'percent3' => $this->percent3,
            'price3' => $this->price3,
            'percent4' => $this->percent4,
            'price4' => $this->price4,
            'percent_detail' => $this->percent_detail,
            'price_detail' => $this->price_detail,
            'price_custom' => $this->price_custom,
            'discount_amount' => $this->discount_amount,
            'tax_type_id' => $this->tax_type_id,
            'tax_rate_type_id' => $this->tax_rate_type_id,
            'tax_rate_percent' => $this->tax_rate_percent,
            'exoneration_document_type_id' => $this->exoneration_document_type_id,
            'exoneration_date' => $this->exoneration_date,
            'exoneration_purchase_percent' => $this->exoneration_purchase_percent,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['LIKE', 'code', $this->code])
            ->andFilterWhere(['LIKE', 'supplier_code', $this->supplier_code])
            ->andFilterWhere(['LIKE', 'image', $this->image])
            ->andFilterWhere(['LIKE', 'description', $this->description])
            ->andFilterWhere(['LIKE', 'bar_code', $this->bar_code])
            ->andFilterWhere(['LIKE', 'location', $this->location])
            ->andFilterWhere(['LIKE', 'branch', $this->branch])
            ->andFilterWhere(['LIKE', 'nature_discount', $this->nature_discount])
            ->andFilterWhere(['LIKE', 'number_exoneration_doc', $this->number_exoneration_doc])
            ->andFilterWhere(['LIKE', 'name_institution_exoneration', $this->name_institution_exoneration]);

        return $dataProvider;
    }
}
