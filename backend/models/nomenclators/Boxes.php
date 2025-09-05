<?php

namespace backend\models\nomenclators;

use Yii;
use backend\models\business\CashRegister;
/**
 * This is the model class for table "boxes".
 *
 * @property int $id
 * @property int $branch_office_id
 * @property string $numero
 * @property string $name
 *
 * @property BranchOffice $branchOffice
 * @property CashRegister[] $cashRegisters
 */
class Boxes extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'boxes';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['branch_office_id', 'numero', 'name'], 'required'],
            [['branch_office_id', 'is_point_sale'], 'integer'],
            [['numero'], 'string', 'max' => 5],
            ['numero', 'string', 'length' => 5],
            [['name'], 'string', 'max' => 50],            
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
            'branch_office_id' => 'Sucursal',
            'numero' => 'Número',
            'name' => 'Nombre',
            'is_point_sale'=> '¿Facturar como punto de venta?',
        ];
    }

    /**
     * Gets query for [[BranchOffice]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBranchOffice()
    {
        return $this->hasOne(BranchOffice::className(), ['id' => 'branch_office_id']);
    }

    /**
     * Gets query for [[CashRegisters]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCashRegisters()
    {
        return $this->hasMany(CashRegister::className(), ['box_id' => 'id']);
    }

   /**
     * Returns a mapped array for using on Select widget
     *
     * @param boolean $check_status
     * @return array
     */
    public static function getSelectMap($branch_office_id, $onlyId = NULL)
    {
        $query = self::find()->where(['branch_office_id'=>$branch_office_id, 'is_point_sale'=>1]);
        if (!is_null($onlyId)){
            $query->andWhere(['id' => $onlyId]);
        }
        $models = $query->asArray()->all();
        $array_map = [];

        if(count($models)>0)
        {
            foreach ($models AS $index => $model)
            {
                //$sucursal = BranchOffice::find()->where(['id'=>$model['branch_office_id']])->one();
                $array_map[$model['id']] = $model['numero'].' - '.$model['name'];
            }
        }

        return $array_map;
    } 

    public static function getAllSelectMap($branch_office_id, $onlyId = NULL)
    {
        $query = self::find()->where(['branch_office_id'=>$branch_office_id]);
        if (!is_null($onlyId)){
            $query->andWhere(['id' => $onlyId]);
        }
        $models = $query->asArray()->all();
        $array_map = [];

        if(count($models)>0)
        {
            foreach ($models AS $index => $model)
            {
                //$sucursal = BranchOffice::find()->where(['id'=>$model['branch_office_id']])->one();
                $array_map[$model['id']] = $model['numero'].' - '.$model['name'];
            }
        }

        return $array_map;
    }    
    
    /**
     * @param $branch_office_id
     * @return array
     */
    public static function getSelectMapSpecific($branch_office_id, $is_point_sale = false)
    {
        $query = self::find()
            ->where(['branch_office_id'=> $branch_office_id])
            ->asArray();

        if ($is_point_sale){
            $query->andWhere(['is_point_sale' => 1]);
        }   
        else
            $query->andWhere(['is_point_sale' => 0]);

        
        $models = $query->all();

        $array_map = [];

        if(count($models)>0)
        {
            foreach ($models AS $index => $model)
            {
                $array_map[$model['id']] = $model['name'].' - '.$model['numero'];
            }
        }

        return $array_map;
    }    
    
}