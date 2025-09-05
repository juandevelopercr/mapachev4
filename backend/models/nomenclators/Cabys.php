<?php

namespace backend\models\nomenclators;

use backend\models\business\Product;
use phpDocumentor\Reflection\DocBlock\Description;
use Yii;
use backend\models\BaseModel;
use yii\helpers\ArrayHelper;
use yii\helpers\StringHelper;
use common\models\GlobalFunctions;
use yii\helpers\Html;

/**
 * This is the model class for table "cabys".
 *
 * @property int $id
 * @property string|null $category1
 * @property string|null $description1
 * @property string|null $category2
 * @property string|null $description2
 * @property string|null $category3
 * @property string|null $description3
 * @property string|null $category4
 * @property string|null $description4
 * @property string|null $category5
 * @property string|null $description5
 * @property string|null $category6
 * @property string|null $description6
 * @property string|null $category7
 * @property string|null $description7
 * @property string|null $category8
 * @property string|null $description8
 * @property string|null $code
 * @property string|null $description_service
 * @property string|null $tax
 * @property int $status
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Product[] $products

 */
class Cabys extends BaseModel
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'cabys';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['description1', 'description2', 'description3', 'description4', 'description5', 'description6', 'description7', 'description8', 'description_service'], 'string'],
            [['status'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['category1', 'category2', 'category3', 'category4', 'category5', 'category6', 'category7', 'category8', 'code', 'tax'], 'string', 'max' => 255],
            [['description_service','code', 'tax'], 'required',],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'category1' => Yii::t('backend', 'Categoría').' 1',
            'description1' => Yii::t('backend', 'Descripción').' 1',
            'category2' => Yii::t('backend', 'Categoría').' 2',
            'description2' => Yii::t('backend', 'Descripción').' 2',
            'category3' => Yii::t('backend', 'Categoría').' 3',
            'description3' => Yii::t('backend', 'Descripción').' 3',
            'category4' => Yii::t('backend', 'Categoría').' 4',
            'description4' => Yii::t('backend', 'Descripción').' 4',
            'category5' => Yii::t('backend', 'Categoría').' 5',
            'description5' => Yii::t('backend', 'Descripción').' 5',
            'category6' => Yii::t('backend', 'Categoría').' 6',
            'description6' => Yii::t('backend', 'Descripción').' 6',
            'category7' => Yii::t('backend', 'Categoría').' 7',
            'description7' => Yii::t('backend', 'Descripción').' 7',
            'category8' => Yii::t('backend', 'Categoría').' 8',
            'description8' => Yii::t('backend', 'Descripción').' 8',
            'code' => Yii::t('backend', 'Código'),
            'description_service' => Yii::t('backend', 'Descripción del servicio'),
            'tax' => Yii::t('backend', 'Impuesto'),
            'status' => Yii::t('backend', 'Estado'),
            'created_at' => Yii::t('backend', 'Fecha de creación'),
            'updated_at' => Yii::t('backend', 'Fecha de actualización'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProducts()
    {
        return $this->hasMany(Product::className(), ['cabys_id' => 'id']);
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
        return "/cabys";
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

    public static function getLabelSelectById($id)
    {
        $model = self::find()
            ->select(['code','description_service','tax'])
            ->where(['id' => $id])
            ->asArray()
            ->one();
        ;

        if($model !== null)
        {
            $temp_name = $model['code'];
            if(isset($model['description_service']) && !empty($model['description_service'])){
                $temp_name = "{$temp_name} - {$model['description_service']}";
            }

            if(isset($model['tax']) && !empty($model['tax'])){
                $temp_name = "{$temp_name} - {$model['tax']}";
            }

            return "{$temp_name}";
        }
        else
        {
            return '';
        }
    }

    /**
     * Returns a mapped array for using on Select widget
     *
     * @param boolean $check_status
     * @return array
     */
    public static function getSelectMap($check_status = false)
    {
        $query = self::find()->select(['id','code']);
        if($check_status)
        {
            $query->where(['status' => self::STATUS_ACTIVE]);
        }

        $models = $query->asArray()->all();

        $results = ( count( $models ) === 0 ) ? [ '' => '' ] : ArrayHelper::map($models, 'id', 'code');

        return $results;
    }

    /**
     * Returns a mapped array for using on Select widget
     *
     * @param boolean $check_status
     * @return array
     */
    public static function getSelectMapIndex($table_name_join)
    {
        $query = self::find()->select(['cabys.id','cabys.code'])->innerJoin($table_name_join,$table_name_join.'.cabys_id = cabys.id');

        $models = $query->asArray()->all();

        $results = ( count( $models ) === 0 ) ? [ '' => '' ] : ArrayHelper::map($models, 'id', 'code');

        return $results;
    }
}
