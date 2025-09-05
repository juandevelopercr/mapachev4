<?php
namespace backend\models\business;

use Yii;
use yii\base\Model;

/**
 * ItemInvoiceTpvForm
 */
class ItemInvoiceTpvForm extends Model
{
    public $product_service;
    public $product_code;
    public $quantity;
    public $invoice_id;
    public $price_type;
    public $unit_type_id;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['product_service', 'quantity','invoice_id','unit_type_id','price_type'], 'required'],
            [['quantity', 'price_type', 'unit_type_id'], 'integer'],
            [['product_service', 'product_code'], 'string'],
        ];
    }


	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [
            'product_code'=> Yii::t('backend','Código'),
			'product_service' => Yii::t('backend','Descripción producto/servicio'),
			'quantity' => Yii::t('backend','Cantidad'),
			'invoice_id' => Yii::t('backend','Factura'),
            'price_type' => Yii::t('backend', 'Lista precio'),
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

        //Agregar todos los productos
        $products = Product::find()
            ->select(['id','description','bar_code'])
            ->andFilterWhere(['LIKE', 'description', $name])
            ->asArray()
            ->all();

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
