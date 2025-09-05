<?php

namespace backend\models\business;

use backend\models\nomenclators\BranchOffice;
use common\models\User;
use Yii;
use backend\models\BaseModel;
use yii\helpers\StringHelper;
use common\models\GlobalFunctions;
use yii\helpers\Html;

/**
 * This is the model class for table "adjustment".
 *
 * @property int $id
 * @property string|null $consecutive
 * @property int|null $product_id
 * @property int|null $type
 * @property float|null $past_quantity
 * @property float|null $entry_quantity
 * @property float|null $new_quantity
 * @property string|null $observations
 * @property int|null $user_id
 * @property int|null $origin_branch_office_id
 * @property int|null $target_branch_office_id
 * @property string|null $invoice_number
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property int|null $origin_sector_location_id
 * @property int|null $target_sector_location_id
 *
 * @property BranchOffice $originBranchOffice
 * @property BranchOffice $targetBranchOffice
 * @property Product $product
 * @property SectorLocation $originSectorLocation
 * @property SectorLocation $targetSectorLocation
 * @property User $user

 */
class Adjustment extends BaseModel
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'adjustment';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['product_id', 'type', 'user_id', 'origin_sector_location_id','entry_quantity'], 'required'],
            [['product_id', 'type', 'user_id', 'origin_branch_office_id', 'target_branch_office_id','origin_sector_location_id', 'target_sector_location_id'], 'integer'],
            [['past_quantity', 'entry_quantity', 'new_quantity'], 'number'],
            [['observations'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['consecutive', 'invoice_number'], 'string', 'max' => 255],
            [['key'], 'string', 'max' => 50],
            [['origin_branch_office_id'], 'exist', 'skipOnError' => true, 'targetClass' => BranchOffice::className(), 'targetAttribute' => ['origin_branch_office_id' => 'id']],
            [['target_branch_office_id'], 'exist', 'skipOnError' => true, 'targetClass' => BranchOffice::className(), 'targetAttribute' => ['target_branch_office_id' => 'id']],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::className(), 'targetAttribute' => ['product_id' => 'id']],
            [['origin_sector_location_id'], 'exist', 'skipOnError' => true, 'targetClass' => SectorLocation::className(), 'targetAttribute' => ['origin_sector_location_id' => 'id']],
            [['target_sector_location_id'], 'exist', 'skipOnError' => true, 'targetClass' => SectorLocation::className(), 'targetAttribute' => ['target_sector_location_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'consecutive' => Yii::t('backend', 'Consecutivo'),
            'product_id' => Yii::t('backend', 'Producto'),
            'type' => Yii::t('backend', 'Tipo'),
            'past_quantity' => Yii::t('backend', 'Cantidad anterior'),
            'entry_quantity' => Yii::t('backend', 'Cantidad ingresada'),
            'new_quantity' => Yii::t('backend', 'Nueva cantidad'),
            'observations' => Yii::t('backend', 'Observaciones'),
            'user_id' => Yii::t('backend', 'Usuario'),
            'origin_branch_office_id' => Yii::t('backend', 'Sucursal origen'),
            'target_branch_office_id' => Yii::t('backend', 'Sucursal destino'),
            'invoice_number' => Yii::t('backend', 'No. factura'),
            'created_at' => Yii::t('backend', 'Fecha de creación'),
            'updated_at' => Yii::t('backend', 'Fecha de actualización'),
            'origin_sector_location_id' => Yii::t('backend', 'Ubicación origen'),
            'target_sector_location_id' => Yii::t('backend', 'Ubicación destino'),
            'key'=> Yii::t('backend', 'Key'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOriginBranchOffice()
    {
        return $this->hasOne(BranchOffice::className(), ['id' => 'origin_branch_office_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTargetBranchOffice()
    {
        return $this->hasOne(BranchOffice::className(), ['id' => 'target_branch_office_id']);
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
    public function getOriginSectorLocation()
    {
        return $this->hasOne(SectorLocation::className(), ['id' => 'origin_sector_location_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTargetSectorLocation()
    {
        return $this->hasOne(SectorLocation::className(), ['id' => 'target_sector_location_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
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
        return "/adjustment";
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
     * @return string
     */
    public function generateCode()
    {
        $max_code = self::find()->max('consecutive');
        $code = is_null($max_code) ? 1: ($max_code + 1);
        return GlobalFunctions::zeroFill($code,6);
    }

    public function beforeSave($insert)
    {
        if (is_null($this->user_id) || empty($this->user_id))
            $this->user_id = Yii::$app->user->id;
        if (parent::beforeSave($insert)) {            
            return true;
        } else {
            return false;
        }
    }    

    /**
     * @param $product_id
     * @param $type
     * @param $entry_quantity
     * @param $new_quantity
     * @param $past_quantity
     * @param $origin_branch_office_id
     * @param $origin_sector_location_id
     * @param null $invoice_number
     * @param null $target_branch_office_id
     * @param null $target_sector_location_id
     * @param null $observations
     * @return bool
     */
    public static function add($product_id, $type, $entry_quantity, $new_quantity, $past_quantity, $origin_branch_office_id, $origin_sector_location_id, $invoice_number = null, $target_branch_office_id = null, $target_sector_location_id = null, $observations = null, $key = null)
    {
        $user_id = is_null(Yii::$app->user->id) ? 1: Yii::$app->user->id;
        $model = new Adjustment([
            'product_id' => $product_id,
            'type' => $type,
            'entry_quantity' => $entry_quantity,
            'new_quantity' => $new_quantity,
            'past_quantity' => $past_quantity,
            'origin_branch_office_id' => $origin_branch_office_id,
            'user_id' => $user_id,
            'origin_sector_location_id' => $origin_sector_location_id,
            'key'=> $key
        ]);

        $model->consecutive = $model->generateCode();

        if($invoice_number !== null) {
            $model->invoice_number = $invoice_number;
        }

        if($target_branch_office_id !== null) {
            $model->target_branch_office_id = $target_branch_office_id;
        }

        if($target_sector_location_id !== null) {
            $model->target_sector_location_id = $target_sector_location_id;
        }

        if($observations !== null) {
            $model->observations = $observations;
        }

        return $model->save(false);
    }

    /**
     * @param $product_id
     * @param $type
     * @param $entry_quantity
     * @param $origin_branch_office_id
     * @param $origin_sector_location_id
     * @param $invoice_number
     * @param $past_quantity
     * @param null $observations
     * @return bool
     */
    public static function extract($product_id, $type, $entry_quantity, $origin_branch_office_id, $origin_sector_location_id, $invoice_number, $past_quantity, $observations = null, $key = null)
    {
        $new_quantity = 0;
        $user_id = is_null(Yii::$app->user->id) ? 1: Yii::$app->user->id;

        if($entry_quantity >= $past_quantity)
        {
            $new_quantity = 0;
        }
        elseif ($entry_quantity < $past_quantity)
        {
            $new_quantity = $past_quantity - $entry_quantity;
        }

        $model = new Adjustment([
            'product_id' => $product_id,
            'type' => $type,
            'entry_quantity' => $entry_quantity,
            'new_quantity' => $new_quantity,
            'past_quantity' => $past_quantity,
            'origin_branch_office_id' => $origin_branch_office_id,
            'user_id' => $user_id,
            'invoice_number' => $invoice_number,
            'origin_sector_location_id' => $origin_sector_location_id,
            'observations' => $observations,
            'key'=> $key,
        ]);

        $model->consecutive = $model->generateCode();

        return $model->save(false);
    }
}
