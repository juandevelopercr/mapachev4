<?php

namespace backend\controllers;

use Yii;
use yii\helpers\Url;
use yii\db\Exception;
use common\models\User;
use yii\web\Controller;
use yii\filters\VerbFilter;
use backend\models\business\Entry;
use common\models\GlobalFunctions;
use yii\web\NotFoundHttpException;
use backend\models\business\Supplier;
use backend\models\business\Documents;
use backend\models\business\ItemEntry;
use backend\models\business\EntrySearch;
use backend\models\business\XmlImported;
use backend\models\business\ItemImported;
use backend\models\business\AccountsPayable;
use backend\models\business\ItemEntrySearch;
use backend\models\nomenclators\BranchOffice;
use backend\models\business\ItemImportedSearch;
use backend\models\nomenclators\UtilsConstants;

/**
 * EntryController implements the CRUD actions for Entry model.
 */
class EntryController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                    'multiple_delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Entry models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new EntrySearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Entry model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);

        $searchModelItemEntry = new ItemEntrySearch(['entry_id' => $id]);
        $dataProviderItemEntry = $searchModelItemEntry->search(Yii::$app->request->queryParams);
        $total_items = ItemEntry::find()->where(['entry_id' => $id])->count();

        return $this->render('view', [
            'model' => $model,
            'total_items' => $total_items,
            'dataProviderItemEntry' => $dataProviderItemEntry,
        ]);
    }

    /**
     * Creates a new Entry model.
     * If creation is successful, the browser will be redirected to the 'index' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Entry();
        $model->loadDefaultValues();
        $model->invoice_date = date('Y-m-d');
        $model->order_purchase = $model->generateOrdenPurchase();
        $model->currency = 'CRC';
        if(GlobalFunctions::getRol() === User::ROLE_FACTURADOR)
        {
            $model->branch_office_id = User::getBranchOfficeIdOfActiveUser();
        }

        $default_branch_office = BranchOffice::find()->orderBy('code')->one();
        if($default_branch_office !== null)
        {
            $model->branch_office_id = $default_branch_office->id;
        }

        if ($model->load(Yii::$app->request->post()))
        {
            $transaction = \Yii::$app->db->beginTransaction();

            try
            {
                if($model->save())
                {
                    if ($model->invoice_type == UtilsConstants::INVOICE_TYPE_CREDIT){
                        $accountPayable = new Documents;
                        $accountPayable->key = $model->invoice_number;
                        $accountPayable->proveedor = $model->supplier->name;
                        $accountPayable->emission_date = date('Y-m-d H:i:s', strtotime($model->invoice_date));
                        $accountPayable->currency = $model->currency;
                        $accountPayable->total_invoice = $model->amount;
                        $accountPayable->status = UtilsConstants::ACCOUNT_PAYABLE_PENDING;	
    
                        AccountsPayable::addCuentaPorPagar($accountPayable);
                    }


                    $transaction->commit();

                    GlobalFunctions::addFlashMessage('success',Yii::t('backend','Elemento creado correctamente'));

                    return $this->redirect(['update','id'=> $model->id]);
                }
                else
                {
                    GlobalFunctions::addFlashMessage('danger',Yii::t('backend','Error creando el elemento'));
                }
            }
            catch (Exception $e)
            {
                GlobalFunctions::addFlashMessage('danger',Yii::t('backend','Error, ha ocurrido una excepción creando el elemento'));
                $transaction->rollBack();
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);

    }

    /**
     * Updates an existing Entry model.
     * If update is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if(isset($model) && !empty($model))
        {
            $searchModelItemEntry = new ItemEntrySearch();
            $searchModelItemEntry->entry_id = $model->id;
            $dataProviderItemEntry  = $searchModelItemEntry->search(Yii::$app->request->queryParams);

            $searchModelItemImported = new ItemImportedSearch();
            $searchModelItemImported->entry_id = $model->id;
            $dataProviderItemImported  = $searchModelItemImported->search(Yii::$app->request->queryParams);


            if ($model->load(Yii::$app->request->post()))
            {
                $transaction = \Yii::$app->db->beginTransaction();

                try
                {
                    if($model->save())
                    {
                        $transaction->commit();

                        GlobalFunctions::addFlashMessage('success',Yii::t('backend','Elemento actualizado correctamente'));

                        return $this->redirect(['index']);
                    }
                    else
                    {
                        GlobalFunctions::addFlashMessage('danger',Yii::t('backend','Error actualizando el elemento'));
                    }
                }
                catch (Exception $e)
                {
                    GlobalFunctions::addFlashMessage('danger',Yii::t('backend','Error, ha ocurrido una excepción actualizando el elemento'));
                    $transaction->rollBack();
                }
            }
        }
        else
        {
            GlobalFunctions::addFlashMessage('warning',Yii::t('backend','El elemento buscado no existe'));
        }

        return $this->render('update', [
            'model' => $model,
            'searchModelItemEntry' => $searchModelItemEntry,
            'dataProviderItemEntry' => $dataProviderItemEntry,
            'searchModelItemImported' => $searchModelItemImported,
            'dataProviderItemImported' => $dataProviderItemImported,
        ]);

    }

    /**
     * Deletes an existing Entry model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        $transaction = \Yii::$app->db->beginTransaction();

        try
        {
            $xml_list = XmlImported::findAll(['entry_id' => $model->id]);
            foreach ($xml_list AS $index => $xml)
            {
                $xml->deleteXml();

                $items_pending = ItemImported::findAll(['xml_imported_id' => $xml->id]);
                foreach ($items_pending AS $ref => $item)
                {
                    $item->delete();
                }

                $xml->delete();
            }

            if($model->delete())
            {
                $transaction->commit();

                GlobalFunctions::addFlashMessage('success',Yii::t('backend','Elemento eliminado correctamente'));
            }
            else
            {
                GlobalFunctions::addFlashMessage('danger',Yii::t('backend','Error eliminando el elemento'));
            }

            return $this->redirect(['index']);
        }
        catch (Exception $e)
        {
            GlobalFunctions::addFlashMessage('danger',Yii::t('backend','Error, ha ocurrido una excepción eliminando el elemento'));
            $transaction->rollBack();
        }
    }

    /**
     * Finds the Entry model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Entry the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Entry::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('backend','La página solicitada no existe'));
        }
    }

    /**
    * Bulk Deletes for existing Entry models.
    * If deletion is successful, the browser will be redirected to the 'index' page.
    * @return mixed
    */
    public function actionMultiple_delete()
    {
        if(Yii::$app->request->post('row_id'))
        {
            $transaction = \Yii::$app->db->beginTransaction();

            try
            {
                $pk = Yii::$app->request->post('row_id');
                $count_elements = count($pk);

                $deleteOK = true;
                $nameErrorDelete = '';
                $contNameErrorDelete = 0;

                foreach ($pk as $key => $value)
                {
                    $model= $this->findModel($value);

                    $xml_list = XmlImported::findAll(['entry_id' => $model->id]);
                    foreach ($xml_list AS $index => $xml)
                    {
                        $xml->deleteXml();

                        $items_pending = ItemImported::findAll(['xml_imported_id' => $xml->id]);
                        foreach ($items_pending AS $ref => $item)
                        {
                            $item->delete();
                        }

                        $xml->delete();
                    }

                    if(!$model->delete())
                    {
                        $deleteOK=false;
                        $nameErrorDelete= $nameErrorDelete.'['.$model->name.'] ';
                        $contNameErrorDelete++;
                    }
                }

                if($deleteOK)
                {
                    if($count_elements === 1)
                    {
                        GlobalFunctions::addFlashMessage('success',Yii::t('backend','Elemento eliminado correctamente'));
                    }
                    else
                    {
                        GlobalFunctions::addFlashMessage('success',Yii::t('backend','Elementos eliminados correctamente'));
                    }

                    $transaction->commit();
                }
                else
                {
                    if($count_elements === 1)
                    {
                        if($contNameErrorDelete===1)
                        {
                            GlobalFunctions::addFlashMessage('danger',Yii::t('backend','Error eliminando el elemento').': <b>'.$nameErrorDelete.'</b>');
                        }
                    }
                    else
                    {
                        if($contNameErrorDelete===1)
                        {
                            GlobalFunctions::addFlashMessage('danger',Yii::t('backend','Error eliminando el elemento').': <b>'.$nameErrorDelete.'</b>');
                        }
                        elseif($contNameErrorDelete>1)
                        {
                            GlobalFunctions::addFlashMessage('danger',Yii::t('backend','Error eliminando los elementos').': <b>'.$nameErrorDelete.'</b>');
                        }
                    }
                }
            }
            catch (Exception $e)
            {
                GlobalFunctions::addFlashMessage('danger',Yii::t('backend','Error, ha ocurrido una excepción eliminando el elemento'));
                $transaction->rollBack();
            }

            return $this->redirect(['index']);
        }
    }

    /**
     * Import xml.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionImport_xml($supplier_id = null)
    {
        $model = new XmlImported();
        $model->scenario = 'import';

        if($supplier_id !== null)
        {
            $model->supplier_id = $supplier_id;
        }

        $model->user_id = Yii::$app->user->id;
        if(GlobalFunctions::getRol() === User::ROLE_FACTURADOR)
        {
            $model->branch_office_id = User::getBranchOfficeIdOfActiveUser();
        }
        else
        {
            $model->branch_office_id = BranchOffice::find()->one()->id;
        }

        if ($model->load(Yii::$app->request->post()))
        {
            $transaction = \Yii::$app->db->beginTransaction();
            try
            {
                $xml = $model->uploadXml();
                if($xml === false)
                {
                    GlobalFunctions::addFlashMessage('danger',Yii::t('backend','Error, el xml seleccionado no cumple con la estructura necesaria'));

                    return $this->render('xml_import', [
                        'model' => $model,
                    ]);
                }
                else
                {
                    $errors = 0;

                    if($model->array_xml['Emisor'])
                    {
                        $supplier_model = Supplier::findOne(['identification' => $model->array_xml['Emisor']['Identificacion']['Numero']]);
                        if($supplier_model !== null)
                        {
                            $model->supplier_id = $supplier_model->id;
                        }
                        else
                        {
                            $errors++;
                        }
                    }
                    else{
                        $errors++;
                    }

                    if($errors > 0)
                    {
                        $model->addError('supplier_id', 'Proveedor no puede estar vacío');

                        GlobalFunctions::addFlashMessage('danger',Yii::t('backend','Error creando el elemento'));

                        return $this->render('xml_import', [
                            'model' => $model,
                        ]);
                    }

                    $array_xml = GlobalFunctions::convertXmlInArrayPhp($xml->tempName);
                    $model->currency_code = (isset($array_xml['ResumenFactura']['CodigoTipoMoneda']['CodigoMoneda']))? $array_xml['ResumenFactura']['CodigoTipoMoneda']['CodigoMoneda'] : null;
                    $model->currency_change_value = (isset($array_xml['ResumenFactura']['CodigoTipoMoneda']['TipoCambio']))? $array_xml['ResumenFactura']['CodigoTipoMoneda']['TipoCambio'] : null;
                    $amount = (isset($array_xml['ResumenFactura']['TotalGravado']))? $array_xml['ResumenFactura']['TotalGravado'] : null;
                    $model->invoice_key = (isset($array_xml['Clave']))? $array_xml['Clave'] : null;
                    $model->invoice_activity_code = (isset($array_xml['CodigoActividad']))? $array_xml['CodigoActividad'] : null;
                    $model->invoice_consecutive_number = (isset($array_xml['NumeroConsecutivo']))? $array_xml['NumeroConsecutivo'] : null;
                    $model->invoice_date = (isset($array_xml['FechaEmision']))? $array_xml['FechaEmision'] : null;
                    $model->supplier_identification_type = (isset($array_xml['Emisor']['Identificacion']['Tipo']))? $array_xml['Emisor']['Identificacion']['Tipo'] : null;
                    $model->supplier_identification = (isset($array_xml['Emisor']['Identificacion']['Numero']))? $array_xml['Emisor']['Identificacion']['Numero'] : null;
                    $model->supplier_province_code = (isset($array_xml['Emisor']['Ubicacion']['Provincia']))? $array_xml['Emisor']['Ubicacion']['Provincia'] : null;
                    $model->supplier_canton_code = (isset($array_xml['Emisor']['Ubicacion']['Canton']))? $array_xml['Emisor']['Ubicacion']['Canton'] : null;
                    $model->supplier_district_code = (isset($array_xml['Emisor']['Ubicacion']['Distrito']))? $array_xml['Emisor']['Ubicacion']['Distrito'] : null;
                    $model->supplier_barrio_code = (isset($array_xml['Emisor']['Ubicacion']['Barrio']))? $array_xml['Emisor']['Ubicacion']['Barrio'] : null;
                    $model->supplier_other_signals = (isset($array_xml['Emisor']['Ubicacion']['OtrasSenas']))? $array_xml['Emisor']['Ubicacion']['OtrasSenas'] : null;
                    $model->supplier_phone_country_code = (isset($array_xml['Emisor']['Telefono']['CodigoPais']))? $array_xml['Emisor']['Telefono']['CodigoPais'] : null;
                    $model->supplier_phone = (isset($array_xml['Emisor']['Telefono']['NumTelefono']))? $array_xml['Emisor']['Telefono']['NumTelefono'] : null;
                    $model->supplier_email = (isset($array_xml['Emisor']['CorreoElectronico']))? $array_xml['Emisor']['CorreoElectronico'] : null;
                    $model->invoice_condition_sale_code = (isset($array_xml['CondicionVenta']))? $array_xml['CondicionVenta'] : null;
                    $model->invoice_credit_time_code = (isset($array_xml['PlazoCredito']))? $array_xml['PlazoCredito'] : null;
                    $model->invoice_payment_method_code = (isset($array_xml['MedioPago']))? $array_xml['MedioPago'] : null;

                }

                $entry_model = new Entry([
                    'supplier_id' => $model->supplier_id,
                    'branch_office_id' => $model->branch_office_id,
                    'invoice_date' => $model->invoice_date,
                    'invoice_number' => $model->invoice_key,
                    'invoice_type' => ($model->invoice_condition_sale_code !== '01')? UtilsConstants::INVOICE_TYPE_CREDIT : UtilsConstants::INVOICE_TYPE_COUNTED,
                    'amount' => $amount,
                ]);
                $entry_model->order_purchase = $entry_model->generateOrdenPurchase();

                if($entry_model->save())
                {
                    $model->entry_id = $entry_model->id;

                    if ($model->save())
                    {
                        if($xml){
                            $path = $model->getXmlFile();
                            $xml->saveAs($path);
                        }

                        if($array_xml !== null)
                        {
                            ItemImported::addItems($model->id,$array_xml);
                        }

                        $transaction->commit();

                        GlobalFunctions::addFlashMessage('success',Yii::t('backend','Elemento creado correctamente'));
                        return $this->redirect(['update','id' => $entry_model->id]);
                    }
                }

            }
            catch (Exception $e)
            {
                GlobalFunctions::addFlashMessage('danger',Yii::t('backend','Error, ha ocurrido una excepción importando el xml'));
                $transaction->rollBack();
            }
        }

        return $this->render('xml_import', [
            'model' => $model,
        ]);
    }

}
