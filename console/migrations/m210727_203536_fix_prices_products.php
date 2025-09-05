<?php

use yii\db\Migration;
use backend\models\business\Product;

/**
 * Class m210727_203536_fix_prices_products
 */
class m210727_203536_fix_prices_products extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $products = Product::find()->all();
        foreach ($products AS $key => $product)
        {
            $product->price1 = (isset($product->percent1) && !empty($product->percent1))? (($product->price * $product->percent1 / 100) + $product->price) : $product->price;
            $product->price2 = (isset($product->percent2) && !empty($product->percent2))? (($product->price * $product->percent2 / 100) + $product->price) : $product->price;
            $product->price3 = (isset($product->percent3) && !empty($product->percent3))? (($product->price * $product->percent3 / 100) + $product->price) : $product->price;
            $product->price4 = (isset($product->percent4) && !empty($product->percent4))? (($product->price * $product->percent4 / 100) + $product->price) : $product->price;
            $product->price_detail = (isset($product->percent_detail) && !empty($product->percent_detail))? (($product->price * $product->percent_detail / 100) + $product->price) : $product->price;
            if(is_null($product->price_custom))
            {
                $product->price_custom = $product->price;
            }

            $product->save(false);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m210727_203536_fix_prices_products cannot be reverted.\n";
    }

}
