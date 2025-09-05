<?php

namespace backend\models\business;

use Yii;
use backend\models\BaseModel;
use yii\helpers\StringHelper;
use common\models\GlobalFunctions;
use yii\helpers\Html;

/**
 * This is the model class for table "sector_location".
 *
 * @property int $id
 * @property int|null $sector_id
 * @property string|null $code
 * @property string|null $name
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property Adjustment[] $adjustments
 * @property Adjustment[] $adjustments0
 * @property ItemEntry[] $itemEntries
 * @property PhysicalLocation[] $physicalLocations
 * @property Sector $sector

 */
class SectorLocation extends BaseModel
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'sector_location';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['code','name'],'required'],
            [['sector_id'], 'default', 'value' => null],
            [['sector_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['code', 'name'], 'string', 'max' => 255],
            [['sector_id'], 'exist', 'skipOnError' => true, 'targetClass' => Sector::className(), 'targetAttribute' => ['sector_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'sector_id' => Yii::t('backend', 'Sector'),
            'code' => Yii::t('backend', 'Código'),
            'name' => Yii::t('backend', 'Nombre'),
            'created_at' => Yii::t('backend', 'Fecha de creación'),
            'updated_at' => Yii::t('backend', 'Fecha de actualización'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAdjustments()
    {
        return $this->hasMany(Adjustment::className(), ['origin_sector_location_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAdjustments0()
    {
        return $this->hasMany(Adjustment::className(), ['target_sector_location_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItemEntries()
    {
        return $this->hasMany(ItemEntry::className(), ['sector_location_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPhysicalLocations()
    {
        return $this->hasMany(PhysicalLocation::className(), ['sector_location_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSector()
    {
        return $this->hasOne(Sector::className(), ['id' => 'sector_id']);
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
        return "/sector-location";
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
     * @param bool $check_status
     * @param null $product_id
     * @param null $branch_office_id
     * @return array
     */
    public static function getSelectMap($check_status = false, $product_id = null, $branch_office_id = null)
    {

        $query = self::find()
            ->select([
                'sector_location.id',
                'sector_location.code AS code',
                'sector_location.name AS name',
                'sector.code AS sector_code',
                'sector.name AS sector_name',
                'branch_office.code AS branch_office_code',
                'branch_office.name AS branch_office_name',
            ])

        ;

        if($branch_office_id !== null)
        {
            $query->innerJoin('sector', 'sector_location.sector_id = sector.id');
            $query->innerJoin('branch_office', 'sector.branch_office_id = branch_office.id');
            $query->andFilterWhere(['sector.branch_office_id' => $branch_office_id]);
        }
        else
        {
            $query->innerJoin('sector', 'sector_location.sector_id = sector.id');
            $query->innerJoin('branch_office', 'sector.branch_office_id = branch_office.id');
        }

        if($check_status)
        {
            $query->where(['sector_location.status' => self::STATUS_ACTIVE]);
        }

        if($product_id)
        {
            $query->andWhere(['NOT IN','sector_location.id',self::getNotAvailables($product_id)]);
        }

        $models = $query->asArray()->all();

        $array_map = [];

        if(count($models)>0)
        {
            foreach ($models AS $index => $model)
            {
                if($branch_office_id !== null)
                {
                    $label = $model['code'].' - '.$model['name'].
                        ' ['.$model['sector_code'].' - '.$model['sector_name'].']';
                }
                else
                {
                    $label = $model['code'].' - '.$model['name'].
                        ' ['.$model['sector_code'].' - '.$model['sector_name'].']'.
                        ' ('.$model['branch_office_code'].' - '.$model['branch_office_name'].')';
                }

                $array_map[$model['id']] = $label;
            }
        }

        return $array_map;
    }

    /**
     * @param $product_id
     * @return array
     */
    public static function getNotAvailables($product_id)
    {
        $array = [];
        $models = PhysicalLocation::find()
            ->select(['sector_location.id'])
            ->innerJoin('sector_location','physical_location.sector_location_id = sector_location.id')
            ->where(['physical_location.product_id' => $product_id])
            ->all();

        if($models !== null)
        {
            foreach ($models AS $key => $item)
            {
                $array[] = $item->id;
            }
        }

        return $array;
    }

    /**
     * @param $branch_office_id
     * @return array|SectorLocation|null|\yii\db\ActiveRecord
     */
    public static function getDefaultSectorLocation($branch_office_id)
    {
        $sector_location = SectorLocation::find()
            ->select(['sector_location.id'])
            ->innerJoin('sector','sector_location.sector_id = sector.id')
            ->where(['sector.branch_office_id' => $branch_office_id])
            ->orderBy('sector_location.code')
            ->one();

        return $sector_location;
    }
}
