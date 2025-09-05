<?php

namespace backend\models\business;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\business\Service;

/**
 * ServiceSearch represents the model behind the search form of `backend\models\business\Service`.
 */
class ServiceSearch extends Service
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'cabys_id', 'unit_type_id', 'tax_type_id', 'tax_rate_type_id', 'exoneration_document_type_id'], 'integer'],
            [['code', 'name', 'nature_discount', 'number_exoneration_doc', 'name_institution_exoneration', 'exoneration_date', 'created_at', 'updated_at'], 'safe'],
            [['price', 'discount_amount', 'tax_rate_percent', 'tax_amount', 'exoneration_purchase_percent', 'exonerated_tax_amount'], 'number'],
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
        $query = Service::find();

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
            'cabys_id' => $this->cabys_id,
            'unit_type_id' => $this->unit_type_id,
            'price' => $this->price,
            'discount_amount' => $this->discount_amount,
            'tax_type_id' => $this->tax_type_id,
            'tax_rate_type_id' => $this->tax_rate_type_id,
            'tax_rate_percent' => $this->tax_rate_percent,
            'tax_amount' => $this->tax_amount,
            'exoneration_document_type_id' => $this->exoneration_document_type_id,
            'exoneration_date' => $this->exoneration_date,
            'exoneration_purchase_percent' => $this->exoneration_purchase_percent,
            'exonerated_tax_amount' => $this->exonerated_tax_amount,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['LIKE', 'code', $this->code])
            ->andFilterWhere(['LIKE', 'name', $this->name])
            ->andFilterWhere(['LIKE', 'nature_discount', $this->nature_discount])
            ->andFilterWhere(['LIKE', 'number_exoneration_doc', $this->number_exoneration_doc])
            ->andFilterWhere(['LIKE', 'name_institution_exoneration', $this->name_institution_exoneration]);

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
