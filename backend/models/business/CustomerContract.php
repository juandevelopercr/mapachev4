<?php

namespace backend\models\business;

use Yii;

/**
 * This is the model class for table "customer_contract".
 *
 * @property int $id
 * @property int $customer_id
 * @property string $contract
 * @property string|null $confirmation_number
 * @property string|null $lugar_recogida lugar de recogida
 * @property string|null $unidad_asignada
 * @property string|null $placa_unidad_asignada
 * @property string|null $fecha_recogida
 * @property string|null $fecha_devolucion
 * @property float|null $iva
 * @property float|null $porciento_descuento
 * @property string|null $naturaleza_descuento
 * @property float|null $decuento_fijo
 * @property float|null $total_comprobante
 * @property string $estado
 */
class CustomerContract extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'customer_contract';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['customer_id', 'contract', 'estado'], 'required'],
            [['customer_id'], 'integer'],
            [['fecha_recogida', 'fecha_devolucion'], 'safe'],
            [['iva', 'porciento_descuento', 'decuento_fijo', 'total_comprobante'], 'number'],
            [['estado'], 'string'],
            [['contract', 'confirmation_number', 'placa_unidad_asignada'], 'string', 'max' => 10],
            [['lugar_recogida', 'unidad_asignada'], 'string', 'max' => 50],
            [['naturaleza_descuento'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'customer_id' => 'Customer ID',
            'contract' => 'Contract',
            'confirmation_number' => 'Confirmation Number',
            'lugar_recogida' => 'Lugar Recogida',
            'unidad_asignada' => 'Unidad Asignada',
            'placa_unidad_asignada' => 'Placa Unidad Asignada',
            'fecha_recogida' => 'Fecha Recogida',
            'fecha_devolucion' => 'Fecha Devolucion',
            'iva' => 'Iva',
            'porciento_descuento' => 'Porciento Descuento',
            'naturaleza_descuento' => 'Naturaleza Descuento',
            'decuento_fijo' => 'Decuento Fijo',
            'total_comprobante' => 'Total Comprobante',
            'estado' => 'Estado',
        ];
    }
}
