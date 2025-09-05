<?php

namespace backend\models\business;

use backend\models\nomenclators\UtilsConstants;
use backend\models\settings\Setting;
use Yii;
use backend\models\BaseModel;
use yii\helpers\StringHelper;
use common\models\GlobalFunctions;
use yii\helpers\Html;
use yii\rbac\Item;

/**
 * This is the model class for table "item_imported".
 *
 * @property int $id
 * @property string|null $code
 * @property float|null $quantity
 * @property string|null $unit_measure
 * @property string|null $unit_measure_commercial
 * @property string|null $name
 * @property string|null $price_by_unit
 * @property float|null $amount_total
 * @property float|null $discount_amount
 * @property float|null $tax_amount
 * @property float|null $tax_neto
 * @property float|null $amount_total_line
 * @property int|null $status
 * @property int|null $xml_imported_id
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property int|null $sector_location_id
 *
 * @property SectorLocation $sectorLocation
 * @property XmlImported $xmlImported

 */
class ItemImported extends BaseModel
{
    const STATUS_READY_TO_APPROV = 1;
    const STATUS_ALERT_PRICE_DISTINCT = 2;
    const STATUS_PRODUCT_NOT_FOUND = 3;
    const STATUS_PRODUCT_FORCE_APROV = 4;
    const STATUS_NOT_LOCATION = 5;

    public $entry_id;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'item_imported';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['quantity','code','name','amount_total','price_by_unit', 'sector_location_id'],'required','on' => 'update'],
            [['quantity', 'amount_total', 'discount_amount', 'tax_amount', 'tax_neto', 'amount_total_line'], 'number'],
            [['xml_imported_id'], 'default', 'value' => null],
            [['status'], 'default', 'value' => self::STATUS_READY_TO_APPROV],
            [['status', 'xml_imported_id','entry_id', 'sector_location_id'], 'integer'],
            [['created_at', 'updated_at','entry_id'], 'safe'],
            [['code', 'unit_measure', 'unit_measure_commercial', 'name', 'price_by_unit'], 'string', 'max' => 255],
            [['sector_location_id'], 'exist', 'skipOnError' => true, 'targetClass' => SectorLocation::className(), 'targetAttribute' => ['sector_location_id' => 'id']],
            [['xml_imported_id'], 'exist', 'skipOnError' => true, 'targetClass' => XmlImported::className(), 'targetAttribute' => ['xml_imported_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'code' => Yii::t('backend', 'Código'),
            'quantity' => Yii::t('backend', 'Cantidad'),
            'unit_measure' => Yii::t('backend', 'Unidad de medida'),
            'unit_measure_commercial' => Yii::t('backend', 'Unidad de medida comercial'),
            'name' => Yii::t('backend', 'Descripción'),
            'price_by_unit' => Yii::t('backend', 'Precio unitario'),
            'amount_total' => Yii::t('backend', 'Monto total'),
            'discount_amount' => Yii::t('backend', 'Monto descuento'),
            'tax_amount' => Yii::t('backend', 'Monto impuesto'),
            'tax_neto' => Yii::t('backend', 'Impuesto neto'),
            'amount_total_line' => Yii::t('backend', 'Monto total línea'),
            'status' => Yii::t('backend', 'Estado'),
            'xml_imported_id' => Yii::t('backend', 'Xml referecia'),
            'created_at' => Yii::t('backend', 'Fecha de creación'),
            'updated_at' => Yii::t('backend', 'Fecha de actualización'),
            'entry_id' => Yii::t('backend', 'Entrada'),
            'sector_location_id' => Yii::t('backend', 'Ubicación destino'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getXmlImported()
    {
        return $this->hasOne(XmlImported::className(), ['id' => 'xml_imported_id']);
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
        return "/item-imported";
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
    public static function getSelectMap($check_status = false,$branch_office_id = null)
    {
        $query = self::find()->select(['item_imported.id','item_imported.code','item_imported.name']);
        if($check_status)
        {
            $query->where(['item_imported.status' => self::STATUS_ACTIVE]);
        }

        if($branch_office_id !== null)
        {
            $query->innerJoin('xml_imported', 'item_imported.xml_imported_id = xml_imported.id');
            $query->innerJoin('entry','xml_imported.entry_id = entry.id');
            $query->andWhere(['entry.branch_office_id' => $branch_office_id]);
        }

        $models = $query->asArray()->all();

        $array_map = [];

        if(count($models)>0)
        {
            foreach ($models AS $index => $model)
            {
                $array_map[$model['id']] = $model['code'].' - '.$model['name'];
            }
        }

        return $array_map;
    }

    /**
     * @param $array
     * @param $value
     * @param $optional_value
     * @param $is_label
     * @return null|string
     */
    public static function getValueOfArray($array, $value, $optional_value,$is_label)
    {
        if ($value !== null) {
            if(!$is_label)
            {
                return (isset($array[$value]) && !empty($array[$value]))? $array[$value] : null;
            }
            else
            {
                if(isset($value) && !empty($value))
                {
                    $status = (int) $value;
                    if($status === ItemImported::STATUS_READY_TO_APPROV)
                    {
                        return '<span class="badge bg-green">'.$array[$value].'</span>';
                    }
                    elseif($status === ItemImported::STATUS_ALERT_PRICE_DISTINCT)
                    {
                        return '<span class="badge bg-orange">'.$array[$value].'</span>';
                    }
                    elseif($status === ItemImported::STATUS_PRODUCT_NOT_FOUND)
                    {
                        return '<span class="badge bg-red">'.$array[$value].'</span>';
                    }
                    elseif($status === ItemImported::STATUS_PRODUCT_FORCE_APROV)
                    {
                        return '<span class="badge bg-blue">'.$array[$value].'</span>';
                    }
                    elseif($status === ItemImported::STATUS_NOT_LOCATION)
                    {
                        return '<span class="badge bg-custom-yellow">'.$array[$value].'</span>';
                    }
                }

                return null;
            }
        } else {
            if($optional_value)
                return null;
            else
                return $array;
        }
    }

    /**
     * Tipo de estados
     *
     * @param null|integer $value
     * @param boolean $optional_value Poner este valor en true cuando se quiere mostrar en los index el valor específico pero este es opcional, evita dar error y devuelve null
     * @return array|mixed
     */
    public static function getStatusSelectType($value = null, $optional_value = false,$is_label = false)
    {
        $array = [];

        $array[self::STATUS_READY_TO_APPROV] = Yii::t('backend', 'Listo para aprobar');
        $array[self::STATUS_ALERT_PRICE_DISTINCT] = Yii::t('backend', 'Diferencias en el precio');
        $array[self::STATUS_PRODUCT_NOT_FOUND] = Yii::t('backend', 'Producto no registrado');
        $array[self::STATUS_PRODUCT_FORCE_APROV] = Yii::t('backend', 'Aprobado por usuario');
        $array[self::STATUS_NOT_LOCATION] = Yii::t('backend', 'Sin ubicación destino');

        return self::getValueOfArray($array,$value,$optional_value,$is_label);
    }

    /**
     * @param int $xml_imported_id
     * @param array $array_items
     */
    public static function addItems($xml_imported_id, $array_xml)
    {
        $single = (isset($array_xml['DetalleServicio']['LineaDetalle']['NumeroLinea']))? true : false;
        if($single)
        {
             $model = new ItemImported([
                'xml_imported_id' => $xml_imported_id,
                'code' => $array_xml['DetalleServicio']['LineaDetalle']['CodigoComercial']['Codigo'],
                'quantity' => $array_xml['DetalleServicio']['LineaDetalle']['Cantidad'],
                'unit_measure' => $array_xml['DetalleServicio']['LineaDetalle']['UnidadMedida'],
                'unit_measure_commercial' => (isset($array_xml['DetalleServicio']['LineaDetalle']['UnidadMedidaComercial']))? $array_xml['DetalleServicio']['LineaDetalle']['UnidadMedidaComercial'] : 'Unid',
                'name' => $array_xml['DetalleServicio']['LineaDetalle']['Detalle'],
                'price_by_unit' => $array_xml['DetalleServicio']['LineaDetalle']['PrecioUnitario'],
                'amount_total' => $array_xml['DetalleServicio']['LineaDetalle']['MontoTotal'],
                'discount_amount' => (isset($array_xml['DetalleServicio']['LineaDetalle']['Descuento']['MontoDescuento']))? $array_xml['DetalleServicio']['LineaDetalle']['Descuento']['MontoDescuento'] : 0,
                'tax_amount' => (isset($array_xml['DetalleServicio']['LineaDetalle']['Impuesto']['Monto']))? $array_xml['DetalleServicio']['LineaDetalle']['Impuesto']['Monto'] : 0,
                'tax_neto' => (isset($array_xml['DetalleServicio']['LineaDetalle']['ImpuestoNeto']))? $array_xml['DetalleServicio']['LineaDetalle']['ImpuestoNeto'] : 0,
                'amount_total_line' => (isset($array_xml['DetalleServicio']['LineaDetalle']['MontoTotalLinea']))? $array_xml['DetalleServicio']['LineaDetalle']['MontoTotalLinea'] : 0,
            ]);

            if(!Product::find()->where(['supplier_code' => $model->code])->exists())
            {
                $model->status = ItemImported::STATUS_PRODUCT_NOT_FOUND;
            }
            else
            {
                $product = Product::find()->select(['price'])->where(['supplier_code' => $model->code])->one();
                if($product->price !== $model->price_by_unit)
                {
                    $model->status = ItemImported::STATUS_ALERT_PRICE_DISTINCT;
                }
                else
                {
                    $model->status = ItemImported::STATUS_NOT_LOCATION;
                }
            }

            $model->save();
        }
        else
        {
            foreach ($array_xml['DetalleServicio']['LineaDetalle'] AS $key => $item)
            {
                $code_commercial = (isset($item['CodigoComercial']['Codigo']))? $item['CodigoComercial']['Codigo'] : null;
                if($code_commercial !== null)
                {
                    $model = new ItemImported([
                        'xml_imported_id' => $xml_imported_id,
                        'code' => $code_commercial,
                        'quantity' => $item['Cantidad'],
                        'unit_measure' => $item['UnidadMedida'],
                        'unit_measure_commercial' => (isset($item['UnidadMedidaComercial']))? $item['UnidadMedidaComercial'] : 'Unid',
                        'name' => $item['Detalle'],
                        'price_by_unit' => $item['PrecioUnitario'],
                        'amount_total' => $item['MontoTotal'],
                        'discount_amount' => (isset($item['Descuento']['MontoDescuento']))? $item['Descuento']['MontoDescuento'] : 0,
                        'tax_amount' => (isset($item['Impuesto']['Monto']))? $item['Impuesto']['Monto'] : 0,
                        'tax_neto' => (isset($item['ImpuestoNeto']))? $item['ImpuestoNeto'] : 0,
                        'amount_total_line' => (isset($item['MontoTotalLinea']))? $item['MontoTotalLinea'] : 0,
                    ]);

                    if(!Product::find()->where(['supplier_code' => $model->code])->exists())
                    {
                        $model->status = ItemImported::STATUS_PRODUCT_NOT_FOUND;
                    }
                    else
                    {
                        $product = Product::find()->select(['price'])->where(['supplier_code' => $model->code])->one();
                        if($product->price !== $model->price_by_unit)
                        {
                            $model->status = ItemImported::STATUS_ALERT_PRICE_DISTINCT;
                        }
                        else
                        {
                            $model->status = ItemImported::STATUS_NOT_LOCATION;
                        }
                    }

                    $model->save();
                }

            }
        }
    }

    /**
     * @param $bar_code
     */
    public static function scanAndUpdateStatus($supplier_code, $price)
    {
        $models = ItemImported::find()
            ->where(['code'=> $supplier_code])
            ->andWhere(['<>','status',self::STATUS_PRODUCT_FORCE_APROV])
            ->all();

        foreach ($models AS $key => $model)
        {
            $price_compare = GlobalFunctions::formatNumber($price,5,true);

            if($model->price_by_unit !== $price_compare)
            {
                $model->status = ItemImported::STATUS_ALERT_PRICE_DISTINCT;
            }
            else
            {
                if(isset($model->sector_location_id )&& !empty($model->sector_location_id))
                {
                    $model->status = ItemImported::STATUS_READY_TO_APPROV;
                }
                else
                {
                    $model->status = ItemImported::STATUS_NOT_LOCATION;
                }
            }

            $model->save();
        }
    }

    /**
     * @param $email
     */
    public function sendEmail($old_price,$product_id)
    {
        $cc_mails = Setting::getValueByField('product_price_change_mails');

        if($cc_mails !== '' && GlobalFunctions::validateCCMails($cc_mails))
        {
            $cc_mails_explode = explode(';', $cc_mails);

            if (count($cc_mails_explode) > 0)
            {
                $mails_to_send = [];

                foreach ($cc_mails_explode AS $email) {
                    $mails_to_send[] = trim($email);
                }
            }
            else
            {
                $mails_to_send = $cc_mails;
            }


            $subject = Yii::t('backend','Notificación sobre cambio de precio de producto');

            $mailer = Yii::$app->mail->compose(['html' => 'alert_change_price-html'], ['old_price' => $old_price,'product_id'=>$product_id])
                ->setTo($mails_to_send)
                ->setFrom([Setting::getEmail() => Setting::getName()])
                ->setSubject($subject);

            try
            {
                if($mailer->send())
                {
                    return UtilsConstants::SEND_MAIL_RESPONSE_TYPE_SUCCESS;
                }
                else
                {
                    return UtilsConstants::SEND_MAIL_RESPONSE_TYPE_ERROR;
                }
            }
            catch (\Swift_TransportException $e)
            {
                return UtilsConstants::SEND_MAIL_RESPONSE_TYPE_EXCEPTION;
            }
        }
    }
}
