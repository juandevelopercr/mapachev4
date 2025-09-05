<?php

namespace backend\models\business;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\business\ItemProforma;

/**
 * ItemProformaSearch represents the model behind the search form of `backend\models\business\ItemProforma`.
 */
class ItemProformaSearch extends ItemProforma
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'proforma_id', 'product_id', 'service_id', 'user_id', 'price_type','unit_type_id', 'tax_type_id', 'tax_rate_type_id', 'exoneration_purchase_percent'], 'integer'],
            [['code', 'description', 'created_at', 'updated_at', 'nature_discount', 'number_exoneration_doc', 'name_institution_exoneration', 'exoneration_date'], 'safe'],
            [['quantity', 'price_unit', 'subtotal', 'tax_amount', 'discount_amount', 'exonerate_amount', 'price_total', 'tax_rate_percent'], 'number'],
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
        $query = ItemProforma::find();

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
            'proforma_id' => $this->proforma_id,
            'product_id' => $this->product_id,
            'service_id' => $this->service_id,
            'quantity' => $this->quantity,
            'price_unit' => $this->price_unit,
            'subtotal' => $this->subtotal,
            'tax_amount' => $this->tax_amount,
            'discount_amount' => $this->discount_amount,
            'exonerate_amount' => $this->exonerate_amount,
            'price_total' => $this->price_total,
            'user_id' => $this->user_id,
            'price_type' => $this->price_type,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'unit_type_id' => $this->unit_type_id,
            'tax_type_id' => $this->tax_type_id,
            'tax_rate_type_id' => $this->tax_rate_type_id,            
        ]);

        $query->andFilterWhere(['LIKE', 'code', $this->code])
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
