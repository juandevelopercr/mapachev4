<?php

namespace backend\models\business;

use backend\models\nomenclators\BranchOffice;
use Yii;
use backend\models\BaseModel;
use yii\helpers\StringHelper;
use common\models\GlobalFunctions;
use yii\helpers\Html;

/**
 * This is the model class for table "sector".
 *
 * @property int $id
 * @property int|null $branch_office_id
 * @property string|null $code
 * @property string|null $name
 * @property bool|null $status
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property BranchOffice $branchOffice
 * @property SectorLocation[] $sectorLocations

 */
class Sector extends BaseModel
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'sector';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name','code'],'required'],
            [['branch_office_id'], 'integer'],
            [['status'], 'boolean'],
            [['created_at', 'updated_at'], 'safe'],
            [['code', 'name'], 'string', 'max' => 255],
            [['branch_office_id'], 'exist', 'skipOnError' => true, 'targetClass' => BranchOffice::className(), 'targetAttribute' => ['branch_office_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'branch_office_id' => Yii::t('backend', 'Sucursal'),
            'code' => Yii::t('backend', 'Código'),
            'name' => Yii::t('backend', 'Nombre'),
            'status' => Yii::t('backend', 'Estado'),
            'created_at' => Yii::t('backend', 'Fecha de creación'),
            'updated_at' => Yii::t('backend', 'Fecha de actualización'),
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
    public function getSectorLocations()
    {
        return $this->hasMany(SectorLocation::className(), ['sector_id' => 'id']);
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
        return "/sector";
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
     * Returns a mapped array for using on Select widget
     *
     * @param boolean $check_status
     * @return array
     */
    public static function getSelectMap($check_status = false, $show_branch_office = false)
    {
        $total_branch_office = BranchOffice::find()->count();
        if($total_branch_office <= 1)
        {
            $show_branch_office = false;
        }

        $query = self::find();

        if($show_branch_office)
        {
            $query->select(['sector.id','sector.code','sector.name','branch_office.code AS branch_office_code', 'branch_office.name AS branch_office_name'])
            ->innerJoin('branch_office','sector.branch_office_id = branch_office.id');
        }
        else
        {
            $query->select(['id','code','name']);
        }

        if($check_status)
        {
            $query->where(['sector.status' => self::STATUS_ACTIVE]);
        }

        $models = $query->asArray()->all();

        $array_map = [];

        if(count($models)>0)
        {
            foreach ($models AS $index => $model)
            {
                if($show_branch_office)
                {
                    $array_map[$model['id']] = $model['code'].' - '.$model['name']. ' ('.$model['branch_office_code'] . ' - '. $model['branch_office_name'].')';
                }
                else
                {
                    $array_map[$model['id']] = $model['code'].' - '.$model['name'];
                }
            }
        }

        return $array_map;
    }

    public function getBranchOfficeId()
    {
        return $this->branch_office_id;
    }
}
