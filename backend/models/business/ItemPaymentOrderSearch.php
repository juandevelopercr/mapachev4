<?php

namespace backend\models\business;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\business\ItemPaymentOrder;

/**
 * ItemPaymentOrderSearch represents the model behind the search form of `backend\models\business\ItemPaymentOrder`.
 */
class ItemPaymentOrderSearch extends ItemPaymentOrder
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'payment_order_id', 'product_id', 'service_id', 'user_id','unit_type_id'], 'integer'],
            [['code', 'description', 'created_at', 'updated_at', 'supplier_code','unit_type_id'], 'safe'],
            [['quantity', 'price_unit', 'subtotal', 'tax_amount', 'discount_amount', 'exonerate_amount', 'price_total'], 'number'],
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
        $query = ItemPaymentOrder::find()
            ->select(['item_payment_order.*', 'product.supplier_code AS supplier_code', 'product.unit_type_id AS unit_type_id'])
            ->innerJoin('product','item_payment_order.product_id = product.id');

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'code' => SORT_ASC
                ],
                'attributes' => [
                    'id',
                    'payment_order_id',
                    'product_id',
                    'service_id',
                    'quantity',
                    'price_unit',
                    'subtotal',
                    'tax_amount',
                    'discount_amount',
                    'exonerate_amount',
                    'price_total',
                    'unit_type_id',
                    'code',
                    'supplier_code',
                    'description',
                ]
            ]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'item_payment_order.id' => $this->id,
            'item_payment_order.payment_order_id' => $this->payment_order_id,
            'item_payment_order.product_id' => $this->product_id,
            'item_payment_order.service_id' => $this->service_id,
            'item_payment_order.quantity' => $this->quantity,
            'item_payment_order.price_unit' => $this->price_unit,
            'item_payment_order.subtotal' => $this->subtotal,
            'item_payment_order.tax_amount' => $this->tax_amount,
            'item_payment_order.discount_amount' => $this->discount_amount,
            'item_payment_order.exonerate_amount' => $this->exonerate_amount,
            'item_payment_order.price_total' => $this->price_total,
            'item_payment_order.user_id' => $this->user_id,
            'item_payment_order.created_at' => $this->created_at,
            'item_payment_order.updated_at' => $this->updated_at,
            'product.unit_type_id' => $this->unit_type_id,
        ]);

        $query
            ->andFilterWhere(['LIKE', 'item_payment_order.code', $this->code])
            ->andFilterWhere(['LIKE', 'product.supplier_code', $this->supplier_code])
            ->andFilterWhere(['LIKE', 'item_payment_order.description', $this->description]);

        return $dataProvider;
    }
}
