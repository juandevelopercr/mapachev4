<?php

namespace backend\models\business;

use backend\models\nomenclators\BranchOffice;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\StringHelper;
use common\models\GlobalFunctions;
use yii\helpers\Html;

/**
 * This is the model class for table "product_has_branch_office".
 *
 * @property int $product_id
 * @property int $branch_office_id
 * @property float $quantity
 * @property string $location
 *
 * @property BranchOffice $branchOffice
 * @property Product $product

 */
class ProductHasBranchOffice extends \yii\db\ActiveRecord
{
    const CHANGE_QUANTITY_PLUS = 1;
    const CHANGE_QUANTITY_MINUS = 2;
    const CHANGE_QUANTITY_SET = 3;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'product_has_branch_office';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['product_id', 'branch_office_id'], 'required'],
            [['product_id', 'branch_office_id'], 'integer'],
            ['quantity','number'],
            ['location','string'],
            [['product_id', 'branch_office_id'], 'unique', 'targetAttribute' => ['product_id', 'branch_office_id']],
            [['branch_office_id'], 'exist', 'skipOnError' => true, 'targetClass' => BranchOffice::className(), 'targetAttribute' => ['branch_office_id' => 'id']],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::className(), 'targetAttribute' => ['product_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'product_id' => Yii::t('backend', 'Producto'),
            'branch_office_id' => Yii::t('backend', 'Sucursal'),
            'quantity' => Yii::t('backend', 'Cantidad'),
            'location' => Yii::t('backend', 'Ubicación física'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBranchOffice()
    {
        return $this->hasOne(BranchOffice::className(), ['id' => 'branch_office_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(Product::className(), ['id' => 'product_id']);
    }

    /** :::::::::::: START > Abstract Methods and Overrides ::::::::::::*/

    /**
    * @return string The base name for current model, it must be implemented on each child
    */
    public function getBaseName()
    {
        return StringHelper::basename(get_class($this));
    }

    /**
    * @return string base route to model links, default to '/'
    */
    public function getBaseLink()
    {
        return "/product-has-branch-office";
    }

    /**
    * Returns a link that represents current object model
    * @return string
    *
    */
    public function getIDLinkForThisModel()
    {
        $id = $this->getRepresentativeAttrID();
        if (isset($this->$id)) {
            $name = $this->getRepresentativeAttrName();
            return Html::a($this->$name, [$this->getBaseLink() . "/view", 'id' => $this->getId()]);
        } else {
            return GlobalFunctions::getNoValueSpan();
        }
    }

    /** :::::::::::: END > Abstract Methods and Overrides ::::::::::::*/

    /**
     * @param $product_id
     * @param $branch_office_id
     * @param float $quantity
     */
    public static function addRelation($product_id, $branch_office_id, $quantity = 0)
    {
        $model= new ProductHasBranchOffice();
        $model->product_id = $product_id;
        $model->branch_office_id = $branch_office_id;
        $model->quantity = $quantity;
        $model->save();
    }

    /**
     * @param $product_id
     * @param $branch_office_id
     * @return bool|float|int|mixed
     */
    public static function getQuantity($product_id, $branch_office_id = null,$return_in_units = true)
    {
        if($branch_office_id !== null)
        {
            $model = ProductHasBranchOffice::find()->where(['product_id' => $product_id, 'branch_office_id' => $branch_office_id])->one();

            if($model !== null)
            {
                return (isset($model->quantity) && !empty($model->quantity))? $model->quantity : 0;
            }
            else
            {
                return 0;
            }
        }
        else
        {
            $sum = ProductHasBranchOffice::find()->where(['product_id' => $product_id])->sum('quantity');
            $product = Product::findOne($product_id);

            if($return_in_units && isset($product->unit_type_id) && !empty($product->unit_type_id))
            {
                $unit_type_code = $product->unitType->code;
                $quantity_by_box = (isset($product->quantity_by_box) && !empty($product->quantity_by_box))? $product->quantity_by_box : 1;
                $package_quantity = (isset($product->package_quantity) && !empty($product->package_quantity))? $product->package_quantity : 1;

                if($unit_type_code === 'CAJ' || $unit_type_code === 'CJ')
                {
                    $sum *= $quantity_by_box;
                }
                elseif($unit_type_code === 'BULT' || $unit_type_code === 'PAQ')
                {
                    $sum *= $package_quantity;
                }
            }

            return (isset($sum) && !empty($sum))? $sum : 0;
        }

    }

    /**
     * @param int $product_id
     * @param int $branch_office_id
     * @param float $quantity
     * @param int $type // 1: sum, 2: minus, 3:set
     */
    public static function updateQuantity($product_id, $branch_office_id, $quantity, $type)
    {
        $model = self::findOne(['product_id'=>$product_id, 'branch_office_id'=>$branch_office_id]);

        if($model !== null)
        {
            $model->quantity = (isset($model->quantity))? $model->quantity : 0;

            if($model !== null)
            {
                if($type === self::CHANGE_QUANTITY_PLUS) {
                    $model->quantity = $model->quantity + $quantity;
                }
                elseif ($type === self::CHANGE_QUANTITY_MINUS) {
                    $model->quantity = $model->quantity - $quantity;
                }
                elseif ($type === self::CHANGE_QUANTITY_SET) {
                    $model->quantity = $quantity;
                }

                $model->save();
            }
        }
        else
        {
            $model = new self(['product_id'=>$product_id, 'branch_office_id'=>$branch_office_id, 'quantity' => $quantity]);
            $model->save();
        }
    }

}
