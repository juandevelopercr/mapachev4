<?php

namespace backend\models\business;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\business\ReceptionItemPo;

/**
 * ReceptionItemPoSearch represents the model behind the search form of `backend\models\business\ReceptionItemPo`.
 */
class ReceptionItemPoSearch extends ReceptionItemPo
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'item_payment_order_id', 'user_id','payment_order_id'], 'integer'],
            [['received'], 'number'],
            [['created_at', 'updated_at','payment_order_id','bar_code','supplier_code','description','quantity'], 'safe'],
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
        $query = ReceptionItemPo::find()
            ->select([
                'reception_item_po.*',
                'item_payment_order.payment_order_id',
                'product.bar_code',
                'product.supplier_code',
                'item_payment_order.description',
                'item_payment_order.quantity',
            ])
            ->innerJoin('item_payment_order', 'reception_item_po.item_payment_order_id = item_payment_order.id')
            ->innerJoin('product','item_payment_order.product_id = product.id')
        ;

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
            'item_payment_order_id' => $this->item_payment_order_id,
            'received' => $this->received,
            'user_id' => $this->user_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'item_payment_order.quantity' => $this->quantity,
            'item_payment_order.payment_order_id' => $this->payment_order_id,
        ]);

        $query
            ->andFilterWhere(['LIKE', 'product.bar_code', $this->bar_code])
            ->andFilterWhere(['LIKE', 'product.supplier_code', $this->supplier_code])
            ->andFilterWhere(['LIKE', 'item_payment_order.description', $this->description])
        ;

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
