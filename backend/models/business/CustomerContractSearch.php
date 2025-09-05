<?php

namespace backend\models\business;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\business\CustomerContract;

/**
 * CustomerContractSearch represents the model behind the search form of `backend\models\business\CustomerContract`.
 */
class CustomerContractSearch extends CustomerContract
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'customer_id'], 'integer'],
            [['contract', 'confirmation_number', 'lugar_recogida', 'unidad_asignada', 'placa_unidad_asignada', 'fecha_recogida', 'fecha_devolucion', 'naturaleza_descuento', 'estado'], 'safe'],
            [['iva', 'porciento_descuento', 'decuento_fijo', 'total_comprobante'], 'number'],
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
        $query = CustomerContract::find();

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
            'customer_id' => $this->customer_id,
            'fecha_recogida' => $this->fecha_recogida,
            'fecha_devolucion' => $this->fecha_devolucion,
            'iva' => $this->iva,
            'porciento_descuento' => $this->porciento_descuento,
            'decuento_fijo' => $this->decuento_fijo,
            'total_comprobante' => $this->total_comprobante,
        ]);

        $query->andFilterWhere(['like', 'contract', $this->contract])
            ->andFilterWhere(['like', 'confirmation_number', $this->confirmation_number])
            ->andFilterWhere(['like', 'lugar_recogida', $this->lugar_recogida])
            ->andFilterWhere(['like', 'unidad_asignada', $this->unidad_asignada])
            ->andFilterWhere(['like', 'placa_unidad_asignada', $this->placa_unidad_asignada])
            ->andFilterWhere(['like', 'naturaleza_descuento', $this->naturaleza_descuento])
            ->andFilterWhere(['like', 'estado', $this->estado]);

        return $dataProvider;
    }
}
