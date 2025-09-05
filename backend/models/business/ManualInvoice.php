<?php

namespace backend\models\business;

use Yii;
use common\models\User;
use backend\models\nomenclators\Currency;
use backend\models\nomenclators\BranchOffice;
use backend\models\business\ItemManualInvoice;
use backend\models\BaseModel;
use yii\helpers\StringHelper;
use common\models\GlobalFunctions;
use yii\helpers\Html;
/**
 * This is the model class for table "manual_invoice".
 *
 * @property int $id
 * @property int|null $branch_office_id
 * @property int|null $supplier_id
 * @property int|null $currency_id
 * @property string|null $consecutive
 * @property string|null $emission_date
 * @property string|null $observations
 * @property int|null $status
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property float|null $total_comprobante
 *
 * @property ItemManualInvoice[] $itemManualInvoices
 * @property BranchOffice $branchOffice
 * @property Currency $currency
 * @property Supplier $supplier
 */
class ManualInvoice extends BaseModel
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'manual_invoice';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['supplier_id', 'currency_id', 'status'], 'required'],
            [['branch_office_id', 'supplier_id', 'currency_id', 'status'], 'integer'],
            [['emission_date', 'created_at', 'updated_at'], 'safe'],
            [['observations'], 'string'],
            [['total_comprobante'], 'number'],
            [['consecutive'], 'string', 'max' => 255],
            [['branch_office_id'], 'exist', 'skipOnError' => true, 'targetClass' => BranchOffice::className(), 'targetAttribute' => ['branch_office_id' => 'id']],
            [['currency_id'], 'exist', 'skipOnError' => true, 'targetClass' => Currency::className(), 'targetAttribute' => ['currency_id' => 'id']],
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
            'branch_office_id' => 'Branch Office',
            'supplier_id' => 'Proveedor',
            'currency_id' => 'Moneda',
            'consecutive' => 'Consecutive',
            'emission_date' => 'Fecha de creado',
            'observations' => 'Observaciones',
            'status' => 'Estado',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'total_comprobante' => 'Total',
        ];
    }

    /**
     * Gets query for [[ItemManualInvoices]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getItemManualInvoices()
    {
        return $this->hasMany(ItemManualInvoice::className(), ['invoice_id' => 'id']);
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

    public function getItemCount(){
        return ItemManualInvoice::find()->where(['invoice_id'=>$this->id])->count();
    } 

    /**
     * Gets query for [[Currency]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCurrency()
    {
        return $this->hasOne(Currency::className(), ['id' => 'currency_id']);
    }

    /**
     * Gets query for [[Supplier]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSupplier()
    {
        return $this->hasOne(Supplier::className(), ['id' => 'supplier_id']);
    }

    function afterFind()
    {
        $this->emission_date = date('Y-m-d H:i:s', strtotime($this->emission_date));
        $this->getResumeInvoice();
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            $this->getResumeInvoice();
            if (is_null($this->id) || empty($this->id)) {
                $this->emission_date = (isset($this->emission_date) && !empty($this->emission_date)) ? date('Y-m-d H:i:s', strtotime($this->emission_date)) : date('Y-m-d H:i:s');
            }
            return true;
        } else {
            return false;
        }
    }

    public function getResumeInvoice()
    {
        $resume = ItemManualInvoice::find()
            ->select([
                'SUM(price * quantity) AS total',
            ])
            ->where(['invoice_id' => $this->id])
            ->asArray()
            ->one();

        if (!is_null($resume))    
            $this->total_comprobante = $resume['total'];

        return $resume;
    }

    public function getResumeInvoiceById($id)
    {
        $resume = ItemManualInvoice::find()
            ->select([
                'SUM(price * quantity) AS total',
            ])
            ->where(['invoice_id' => $id])
            ->asArray()
            ->one();

        if (!is_null($resume))    
            $this->total_comprobante = $resume['total'];

        return $resume;
    }    

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
        return "/manual-invoice";
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

    /**
     * @return string
     */
    public function generateConsecutive()
    {
        $year = date('Y');
        $connection = \Yii::$app->db;
        $sql = "SELECT MAX(SUBSTRING(consecutive, 1, 6)) AS consecutive FROM manual_invoice WHERE SUBSTRING(consecutive, 10, 13)='".$year."'";
        $data = $connection->createCommand($sql);
        $consecutive = $data->queryOne();
        $code = (isset($consecutive))? (int)$consecutive['consecutive'] + 1 : 1;

        return GlobalFunctions::zeroFill($code,6).'-'.date('mY');
    }
}
