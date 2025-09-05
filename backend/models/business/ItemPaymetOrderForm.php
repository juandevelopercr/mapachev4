<?php
namespace backend\models\business;

use Yii;
use yii\base\Model;

/**
 * ItemPaymetOrderForm
 */
class ItemPaymetOrderForm extends Model
{
    public $product_code;
    public $product_service;
    public $quantity;
    public $payment_order_id;
    public $unit_type_id;
    public $price_unit;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['product_code', 'quantity', 'unit_type_id', 'payment_order_id', 'price_unit'], 'required'],
            [['quantity', 'unit_type_id'], 'integer'],
        ];
    }


	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [
			'product_code' => Yii::t('backend','Código de barras'),
			'product_service' => Yii::t('backend','Descripción de productos'),
			'quantity' => Yii::t('backend','Cantidad'),
			'payment_order_id' => Yii::t('backend','Orden de compra'),
            'unit_type_id' => Yii::t('backend', 'Tipo/Unidad'),
            'price_unit' => Yii::t('backend', 'Precio unitario'),
		];
	}

    /**
     * Returns a mapped array for using on Select widget
     *
     * @param boolean $check_status
     * @return array
     */
    public static function getSelectMap($only_products = false, $show_code = true)
    {
        $array_map = [];

        //Agregar todos los productos
        $products = Product::find()->select(['id','description','bar_code'])->asArray()->all();

        if(count($products) > 0)
        {
            foreach ($products AS $index => $product)
            {
                if($show_code)
                {
                    $array_map['P-'.$product['id']] = $product['bar_code'].' - '.$product['description'];
                }
                else
                {
                    $array_map['P-'.$product['id']] = $product['description'];

                }
            }
        }

        if(!$only_products)
        {
            //Agregar todos los servicios
            $services = Service::find()->select(['service.id','service.name','service.code'])->asArray()->all();

            if(count($services) > 0)
            {
                foreach ($services AS $key => $service)
                {
                    $array_map['S-'.$service['id']] = $service['code'].' - '.$service['name'];
                }
            }
        }


        return $array_map;
    }


}
