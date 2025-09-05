<?php
namespace backend\models\business;

use Yii;
use yii\base\Model;

/**
 * ItemManualInvoiceForm
 */
class ItemManualInvoiceForm extends Model
{
    public $service;
    public $quantity;
    public $invoice_id;
    public $price;
    public $unit_type_id;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['service', 'quantity','invoice_id','unit_type_id','price'], 'required'],
            [['quantity', 'unit_type_id'], 'integer'],
            [['service'], 'string'],
        ];
    }


	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [
			'service' => Yii::t('backend','Servicio'),
			'quantity' => Yii::t('backend','Cantidad'),
			'invoice_id' => Yii::t('backend','Factura'),
            'price' => Yii::t('backend', 'Precio'),
            'unit_type_id' => Yii::t('backend', 'Tipo/Unidad'),
		];
	}

    /**
     * Returns a mapped array for using on Select widget
     *
     * @param boolean $check_status
     * @return array
     */
    public static function getSelectMap($show_code = true)
    {
        $array_map = [];

        //Agregar todos los servicios
        $services = Service::find()->select(['service.id','service.name','service.code'])->asArray()->all();

        if(count($services) > 0)
        {
            foreach ($services AS $key => $service)
            {
                if($show_code)
                {
                    $array_map['S-'.$service['id']] = $service['code'].' - '.$service['name'];
                }
                else
                {
                    $array_map['S-'.$service['id']] = $service['name'];
                }
            }
        }

        return $array_map;
    }

    /**
     * @param $name
     * @return array
     */
    public static function searchByName($name, $show_code = false)
    {
        $array_map = [];

        //Agregar todos los servicios
        $services = Service::find()
            ->select(['service.id','service.name','service.code'])
            ->andFilterWhere(['LIKE', 'name', $name])
            ->asArray()
            ->all();

        if(count($services) > 0)
        {
            foreach ($services AS $key => $service)
            {
                if($show_code)
                {
                    $array_map['S-'.$service['id']] = $service['code'].' - '.$service['name'];
                }
                else
                {
                    $array_map['S-'.$service['id']] = $service['name'];
                }
            }
        }

        return $array_map;
    }

}
