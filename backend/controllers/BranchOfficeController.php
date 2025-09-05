<?php

namespace backend\controllers;

use backend\models\business\PhysicalLocation;
use backend\models\business\SectorLocation;
use backend\models\Model;
use backend\models\nomenclators\BranchOfficeAutomaticForm;
use backend\models\nomenclators\UtilsConstants;
use common\models\User;
use Yii;
use backend\models\nomenclators\BranchOffice;
use backend\models\nomenclators\BranchOfficeSearch;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\models\GlobalFunctions;
use yii\helpers\Url;
use yii\db\Exception;
use backend\models\business\Sector;

/**
 * BranchOfficeController implements the CRUD actions for BranchOffice model.
 */
class BranchOfficeController extends Controller
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
     * Lists all BranchOffice models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new BranchOfficeSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single BranchOffice model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);

        return $this->render('view', [
            'model' => $model,
        ]);
    }

    /**
     * Creates a new BranchOffice model.
     * If creation is successful, the browser will be redirected to the 'index' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new BranchOffice();
        $model->loadDefaultValues();
        $model->status = 1;
        $model->code = $model->generateCode();
        $modelsSector = [new Sector];
        $modelsSectorLocation = [[new SectorLocation]];

        if ($model->load(Yii::$app->request->post())) {

            $modelsSector = Model::createMultiple(Sector::classname());
            Model::loadMultiple($modelsSector, Yii::$app->request->post());

            // validate
            $valid = $model->validate();
            $valid = Model::validateMultiple($modelsSector) && $valid;

            if (isset($_POST['SectorLocation'][0][0])) {
                foreach ($_POST['SectorLocation'] as $indexSector => $sectorLocations) {
                    foreach ($sectorLocations as $indexSectorLocation => $sectorLocation) {
                        $data['SectorLocation'] = $sectorLocation;
                        $modelSectorLocation = new SectorLocation;
                        $modelSectorLocation->load($data);
                        $modelsSectorLocation[$indexSector][$indexSectorLocation] = $modelSectorLocation;
                        $valid = $modelSectorLocation->validate();
                    }
                }
            }

            if ($valid) {
                $transaction = \Yii::$app->db->beginTransaction();

                try {
                    if (BranchOffice::find()->select(['code'])->where(['code' => $model->code])->exists()) {
                        $model->code = $model->generateCode();
                    }

                    if ($flag = $model->save(false)) {
                        foreach ($modelsSector as $indexSector => $modelSector) {

                            if ($flag === false) {
                                break;
                            }

                            $modelSector->branch_office_id = $model->id;

                            if (!($flag = $modelSector->save(false))) {
                                break;
                            }

                            if (isset($modelsSectorLocation[$indexSector]) && is_array($modelsSectorLocation[$indexSector])) {
                                foreach ($modelsSectorLocation[$indexSector] as $indexSectorLocation => $modelSectorLocation) {
                                    $modelSectorLocation->sector_id = $modelSector->id;
                                    if (!($flag = $modelSectorLocation->save(false))) {
                                        break;
                                    }
                                }
                            }
                        }
                    }

                    if ($flag) {
                        $transaction->commit();

                        GlobalFunctions::addFlashMessage('success', Yii::t('backend', 'Elemento creado correctamente'));

                        return $this->redirect(['view', 'id' => $model->id]);
                    } else {
                        GlobalFunctions::addFlashMessage('danger', Yii::t('backend', 'Error creando el elemento'));
                    }

                } catch (Exception $e) {
                    $transaction->rollBack();
                    GlobalFunctions::addFlashMessage('danger', Yii::t('backend', 'Error, ha ocurrido una excepción creando el elemento'));
                }
            } else {
                GlobalFunctions::addFlashMessage('danger', Yii::t('backend', 'Error creando el elemento'));
            }
        }

        return $this->render('create', [
            'model' => $model,
            'modelsSector' => (empty($modelsSector)) ? [new Sector] : $modelsSector,
            'modelsSectorLocation' => (empty($modelsSectorLocation)) ? [[new SectorLocation]] : $modelsSectorLocation,

        ]);
    }

    /**
     * Updates an existing BranchOffice model.
     * If update is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $modelsSector = $model->sectors;
        $modelsSectorLocation = [];
        $oldSectorLocations = [];

        if (!empty($modelsSector)) {
            foreach ($modelsSector as $indexSector => $modelSector) {
                $sectorLocations = $modelSector->sectorLocations;
                $modelsSectorLocation[$indexSector] = $sectorLocations;
                $oldSectorLocations = ArrayHelper::merge(ArrayHelper::index($sectorLocations, 'id'), $oldSectorLocations);
            }
        }

        if ($model->load(Yii::$app->request->post())) {
            // reset
            $modelsSectorLocation = [];

            $oldSectorIDs = ArrayHelper::map($modelsSector, 'id', 'id');
            $modelsSector = Model::createMultiple(Sector::classname(), $modelsSector);
            Model::loadMultiple($modelsSector, Yii::$app->request->post());
            $deletedSectorIDs = array_diff($oldSectorIDs, array_filter(ArrayHelper::map($modelsSector, 'id', 'id')));

            // validate
            $valid = $model->validate();
            $valid = Model::validateMultiple($modelsSector) && $valid;

            $sectorLocationsIDs = [];
            if (isset($_POST['SectorLocation'][0][0])) {
                foreach ($_POST['SectorLocation'] as $indexSector => $sectorLocations) {
                    $sectorLocationsIDs = ArrayHelper::merge($sectorLocationsIDs, array_filter(ArrayHelper::getColumn($sectorLocations, 'id')));
                    foreach ($sectorLocations as $indexSectorLocation => $sectorLocation) {
                        $data['SectorLocation'] = $sectorLocation;
                        $modelSectorLocation = (isset($sectorLocation['id']) && isset($oldSectorLocations[$sectorLocation['id']])) ? $oldSectorLocations[$sectorLocation['id']] : new SectorLocation;
                        $modelSectorLocation->load($data);
                        $modelsSectorLocation[$indexSector][$indexSectorLocation] = $modelSectorLocation;
                        $valid = $modelSectorLocation->validate();
                    }
                }
            }

            $oldSectorLocationsIDs = ArrayHelper::getColumn($oldSectorLocations, 'id');
            $deletedSectorLocationsIDs = array_diff($oldSectorLocationsIDs, $sectorLocationsIDs);


            if ($valid) {
                $transaction = \Yii::$app->db->beginTransaction();
                try {
                    if ($flag = $model->save(false)) {

                        if (!empty($deletedSectorLocationsIDs)) {
                            SectorLocation::deleteAll(['id' => $deletedSectorLocationsIDs]);
                        }

                        if (!empty($deletedSectorIDs)) {
                            Sector::deleteAll(['id' => $deletedSectorIDs]);
                        }

                        foreach ($modelsSector as $indexSector => $modelSector) {

                            if ($flag === false) {
                                break;
                            }

                            $modelSector->branch_office_id = $model->id;

                            if (!($flag = $modelSector->save(false))) {
                                break;
                            }

                            if (isset($modelsSectorLocation[$indexSector]) && is_array($modelsSectorLocation[$indexSector])) {
                                foreach ($modelsSectorLocation[$indexSector] as $indexSectorLocation => $modelSectorLocation) {
                                    $modelSectorLocation->sector_id = $modelSector->id;
                                    if (!($flag = $modelSectorLocation->save(false))) {
                                        break;
                                    }
                                }
                            }
                        }
                    }

                    if ($flag) {
                        $transaction->commit();
                        GlobalFunctions::addFlashMessage('success', Yii::t('backend', 'Elemento actualizado correctamente'));

                        return $this->redirect(['view', 'id' => $model->id]);
                    }
                } catch (Exception $e) {
                    $transaction->rollBack();
                    GlobalFunctions::addFlashMessage('danger', Yii::t('backend', 'Error, ha ocurrido una excepción actualizando el elemento'));
                }
            } else {
                GlobalFunctions::addFlashMessage('danger', Yii::t('backend', 'Error actualizando el elemento'));
            }
        }

        return $this->render('update', [
            'model' => $model,
            'modelsSector' => (empty($modelsSector)) ? [new Sector] : $modelsSector,
            'modelsSectorLocation' => (empty($modelsSectorLocation)) ? [[new SectorLocation]] : $modelsSectorLocation
        ]);
    }

    /**
     * Deletes an existing BranchOffice model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        if (User::find()->where(['branch_office_id' => $id])->exists()) {
            GlobalFunctions::addFlashMessage('danger', Yii::t('backend', 'Error eliminando el elemento, existen usuarios asociados a esta sucursal'));
        } else {
            $transaction = \Yii::$app->db->beginTransaction();

            try {
                if ($model->delete()) {
                    $transaction->commit();

                    GlobalFunctions::addFlashMessage('success', Yii::t('backend', 'Elemento eliminado correctamente'));
                } else {
                    GlobalFunctions::addFlashMessage('danger', Yii::t('backend', 'Error eliminando el elemento'));
                }
            } catch (Exception $e) {
                GlobalFunctions::addFlashMessage('danger', Yii::t('backend', 'Error, ha ocurrido una excepción eliminando el elemento'));
                $transaction->rollBack();
            }
        }

        return $this->redirect(['index']);
    }

    /**
     * Finds the BranchOffice model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return BranchOffice the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = BranchOffice::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('backend', 'La página solicitada no existe'));
        }
    }

    /**
     * Bulk Deletes for existing BranchOffice models.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @return mixed
     */
    public function actionMultiple_delete()
    {
        if (Yii::$app->request->post('row_id')) {
            $transaction = \Yii::$app->db->beginTransaction();

            try {
                $pk = Yii::$app->request->post('row_id');
                $count_elements = count($pk);

                $deleteOK = true;
                $nameErrorDelete = '';
                $contNameErrorDelete = 0;

                foreach ($pk as $key => $value) {
                    $model = $this->findModel($value);

                    if (User::find()->where(['branch_office_id' => $value])->exists() || !$model->delete()) {
                        $deleteOK = false;
                        $nameErrorDelete = $nameErrorDelete . '[' . $model->name . '] ';
                        $contNameErrorDelete++;
                    }
                }

                if ($deleteOK) {
                    if ($count_elements === 1) {
                        GlobalFunctions::addFlashMessage('success', Yii::t('backend', 'Elemento eliminado correctamente'));
                    } else {
                        GlobalFunctions::addFlashMessage('success', Yii::t('backend', 'Elementos eliminados correctamente'));
                    }

                    $transaction->commit();
                } else {
                    if ($count_elements === 1) {
                        if ($contNameErrorDelete === 1) {
                            GlobalFunctions::addFlashMessage('danger', Yii::t('backend', 'Error eliminando el elemento') . ': <b>' . $nameErrorDelete . '</b>');
                        }
                    } else {
                        if ($contNameErrorDelete === 1) {
                            GlobalFunctions::addFlashMessage('danger', Yii::t('backend', 'Error eliminando el elemento') . ': <b>' . $nameErrorDelete . '</b>');
                        } elseif ($contNameErrorDelete > 1) {
                            GlobalFunctions::addFlashMessage('danger', Yii::t('backend', 'Error eliminando los elementos') . ': <b>' . $nameErrorDelete . '</b>');
                        }
                    }
                }
            } catch (Exception $e) {
                GlobalFunctions::addFlashMessage('danger', Yii::t('backend', 'Error, ha ocurrido una excepción eliminando el elemento'));
                $transaction->rollBack();
            }

            return $this->redirect(['index']);
        }
    }

    /**
     * Generate a new BranchOffice model.
     * If creation is successful, the browser will be redirected to the 'index' page.
     * @return mixed
     */
    public function actionGenerate()
    {
        $model = new BranchOffice();
        $model->loadDefaultValues();
        $model->status = 1;
        $model->code = $model->generateCode();

        $model_auto = new BranchOfficeAutomaticForm([
            'sector_name' => 'Sector',
            'sector_code_start' => 'A',
            'sector_code_end' => 'A',
            'location_name' => 'Ubicación',
            'location_code_start' => 1,
            'location_code_end' => 10
        ]);

        if ($model->load(Yii::$app->request->post()) && $model_auto->load(Yii::$app->request->post())) {
            $transaction = \Yii::$app->db->beginTransaction();

            try {
                if (BranchOffice::find()->select(['code'])->where(['code' => $model->code])->exists()) {
                    $model->code = $model->generateCode();
                }

                if ($model_auto->validate() && $model->save()) {

                    $alphabet = GlobalFunctions::getArrayAlphabet();
                    $index = 1;
                    while ($alphabet[$index] <= $model_auto->sector_code_end) {
                        $temp_sector = new Sector([
                            'code' => $alphabet[$index],
                            'name' => $model_auto->sector_name . ' ' . $index,
                            'branch_office_id' => $model->id
                        ]);

                        if ($temp_sector->save()) {
                            for ($i = $model_auto->location_code_start; $i <= $model_auto->location_code_end; $i++) {
                                $temp_location = new SectorLocation([
                                    'code' => GlobalFunctions::zeroFill($i, 2),
                                    'name' => $model_auto->location_name . ' ' . $i,
                                    'sector_id' => $temp_sector->id
                                ]);

                                $temp_location->save();
                            }
                        }

                        $index++;
                    }

                    $transaction->commit();

                    GlobalFunctions::addFlashMessage('success', Yii::t('backend', 'Elemento creado correctamente'));

                    return $this->redirect(['update', 'id' => $model->id]);
                } else {
                    GlobalFunctions::addFlashMessage('danger', Yii::t('backend', 'Error creando el elemento'));
                }

            } catch (Exception $e) {
                $transaction->rollBack();
                GlobalFunctions::addFlashMessage('danger', Yii::t('backend', 'Error, ha ocurrido una excepción creando el elemento'));
            }
        }

        return $this->render('generate', [
            'model' => $model,
            'model_auto' => $model_auto,
        ]);
    }
}
