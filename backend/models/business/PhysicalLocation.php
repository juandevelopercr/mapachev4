<?php

namespace backend\models\business;

use backend\models\nomenclators\BranchOffice;
use Yii;
use backend\models\BaseModel;
use yii\helpers\StringHelper;
use common\models\GlobalFunctions;
use yii\helpers\Html;

/**
 * This is the model class for table "physical_location".
 *
 * @property int $id
 * @property int|null $product_id
 * @property int|null $sector_location_id
 * @property float|null $quantity
 * @property float|null $max_capacity
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property Product $product
 * @property SectorLocation $sectorLocation

 */
class PhysicalLocation extends BaseModel
{
    const CHANGE_QUANTITY_PLUS = 1;
    const CHANGE_QUANTITY_MINUS = 2;
    const CHANGE_QUANTITY_SET = 3;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'physical_location';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['sector_location_id','quantity'],'required'],
            [['product_id', 'sector_location_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::className(), 'targetAttribute' => ['product_id' => 'id']],
            [['sector_location_id'], 'exist', 'skipOnError' => true, 'targetClass' => SectorLocation::className(), 'targetAttribute' => ['sector_location_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'product_id' => Yii::t('backend', 'Producto'),
            'sector_location_id' => Yii::t('backend', 'Ubicación'),
            'quantity' => Yii::t('backend', 'Cantidad'),
            'max_capacity' => Yii::t('backend', 'Capacidad máxima'),
            'created_at' => Yii::t('backend', 'Fecha de creación'),
            'updated_at' => Yii::t('backend', 'Fecha de actualización'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(Product::className(), ['id' => 'product_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSectorLocation()
    {
        return $this->hasOne(SectorLocation::className(), ['id' => 'sector_location_id']);
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
        return "/physical-location";
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

    public static function getTotalQuantityByBranchOfficeAndProduct($branch_office_id, $product_id)
    {
        return PhysicalLocation::find()
            ->innerJoinWith('sector_location')
            ->innerJoinWith('sector')
            ->where([
                'sector.branch_office_id' => $branch_office_id,
                'physical_location.product_id' => $product_id
            ])
            ->sum('physical_location.quantity');
    }

    /**
     * @return array|SectorLocation|null|\yii\db\ActiveRecord
     */
    public static function getDefaultLocation()
    {
        $sector_location = SectorLocation::find()
            ->select(['id'])
            ->orderBy('code')
            ->one();

        if($sector_location !== null)
        {
            return $sector_location;
        }
        return null;
    }

    /**
     * @param int $product_id
     * @param int $sector_location_id
     * @param float $quantity
     * @param int $type // 1: sum, 2: minus, 3:set
     */
    public static function updateQuantity($product_id, $sector_location_id, $quantity, $type)
    {
        $model = self::findOne(['product_id'=>$product_id, 'sector_location_id'=>$sector_location_id]);

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

                if($model->quantity > $model->max_capacity)
                {
                    $model->max_capacity = $model->quantity;
                }

                $model->save();
            }
        }
        else
        {
            $model = new PhysicalLocation(['product_id' => $product_id, 'sector_location_id' => $sector_location_id, 'quantity' => $quantity, 'max_capacity' => $quantity ]);
            $model->save();
        }
    }

    /**
     * @param $product_id
     * @param $sector_location_id
     * @return bool|float|int|mixed
     */
    public static function getQuantity($product_id, $sector_location_id)
    {
        $model = self::find()->where(['product_id' => $product_id, 'sector_location_id' => $sector_location_id])->one();

        if($model !== null)
        {
            return (isset($model->quantity) && !empty($model->quantity))? $model->quantity : 0;
        }
        else
        {
            return 0;
        }
    }
}
