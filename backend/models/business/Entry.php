<?php

namespace backend\models\business;

use backend\models\nomenclators\BranchOffice;
use Yii;
use backend\models\BaseModel;
use yii\helpers\StringHelper;
use common\models\GlobalFunctions;
use yii\helpers\Html;

/**
 * This is the model class for table "entry".
 *
 * @property int $id
 * @property string|null $order_purchase
 * @property int|null $supplier_id
 * @property int|null $branch_office_id
 * @property string|null $invoice_date
 * @property string|null $invoice_number
 * @property int|null $invoice_type
 * @property float|null $amount
 * @property string|null $observations
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property BranchOffice $branchOffice
 * @property Supplier $supplier
 * @property ItemEntry[] $itemEntries

 */
class Entry extends BaseModel
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'entry';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['supplier_id', 'branch_office_id', 'invoice_type', 'invoice_date', 'order_purchase', 'invoice_number', 'currency'], 'required',],
            [['supplier_id', 'branch_office_id', 'invoice_type'], 'integer'],
            [['invoice_date', 'created_at', 'updated_at'], 'safe'],
            [['amount', 'total_tax'], 'number'],
            [['observations'], 'string'],
            [['order_purchase', 'invoice_number'], 'string', 'max' => 255],
            [['currency'], 'string', 'max' => 4],
            [['branch_office_id'], 'exist', 'skipOnError' => true, 'targetClass' => BranchOffice::className(), 'targetAttribute' => ['branch_office_id' => 'id']],
            [['supplier_id'], 'exist', 'skipOnError' => true, 'targetClass' => Supplier::className(), 'targetAttribute' => ['supplier_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'order_purchase' => Yii::t('backend', 'Orden de compra'),
            'supplier_id' => Yii::t('backend', 'Proveedor'),
            'branch_office_id' => Yii::t('backend', 'Sucursal'),
            'invoice_date' => Yii::t('backend', 'Fecha de factura'),
            'invoice_number' => Yii::t('backend', 'No. factura'),
            'invoice_type' => Yii::t('backend', 'Tipo de factura'),
            'amount' => Yii::t('backend', 'Monto'),
            'total_tax' => Yii::t('backend', 'IVA'),
            'currency' => Yii::t('backend', 'Moneda'),
            'observations' => Yii::t('backend', 'Observaciones'),
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
    public function getSupplier()
    {
        return $this->hasOne(Supplier::className(), ['id' => 'supplier_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItemEntries()
    {
        return $this->hasMany(ItemEntry::className(), ['entry_id' => 'id']);
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
        return "/entry";
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
    public function generateOrdenPurchase()
    {
        $max_code = self::find()->max('id');
        $code = is_null($max_code) ? 1: ($max_code + 1);
        $month = date('m');
        $year = date('Y');
        return GlobalFunctions::zeroFill($code,6).'-'.$month.''.$year;
    }

    public function beforeSave($insert)
    {
        $data = $this->getResumen();

        if (parent::beforeSave($insert)) {
            $this->total_tax = $data['tax_amount'];
            $this->amount = $data['subtotal'];
            return true;
        } else {
            return false;
        }
    }

    public function getResumen(){
        $result = ItemEntry::find()->select(['SUM(tax_amount) AS tax_amount', 'SUM(subtotal) AS subtotal'])
                                   ->where(['entry_id'=>$this->id])
                                   ->one();

        // Ahora puedes acceder a la suma de los campos 'tax' y 'amount' utilizando las alias definidas en la consulta
        return [
            'tax_amount' => !is_null($result) ? $result->tax_amount: 0,
            'subtotal' => !is_null($result) ? $result->subtotal: 0,
        ];
    }
}