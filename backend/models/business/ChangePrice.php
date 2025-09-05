<?php

namespace backend\models\business;

use common\models\GlobalFunctions;
use Yii;
use yii\base\Model;
use yii\db\Exception;

/**
 * ChangePrice form
 */
class ChangePrice extends Model
{
    public $product_id;
    public $current_price;
    public $new_price;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            // username and password are both required
            [['product_id', 'new_price'], 'required'],
            [['current_price','new_price'],'number'],
        ];
    }


	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [
			'product_id' => Yii::t('common','Producto'),
			'current_price' => Yii::t('common','Precio actual'),
			'new_price' => Yii::t('common','Nuevo precio'),
		];
	}

	public function change_price()
    {
        $transaction = \Yii::$app->db->beginTransaction();
        try
        {
            $product = Product::findOne($this->product_id);
            if($product->updatePrices($this->new_price))
            {
                $items_pending = ItemImported::findAll(['code' => $product->bar_code,'status'=> ItemImported::STATUS_ALERT_PRICE_DISTINCT]);
                foreach ($items_pending AS $key => $item)
                {
                    $item->price_by_unit = $this->new_price;
                    $new_amount_total = $item->quantity * $this->new_price;
                    $item->amount_total = GlobalFunctions::formatNumber($new_amount_total,5,true);
                    $item->status = ItemImported::STATUS_READY_TO_APPROV;
                    $item->save();
                }
               $transaction->commit();

                return true;
            }
        }
        catch (Exception $e)
        {
            $transaction->rollBack();
        }

        return false;
    }
}
