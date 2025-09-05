<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use mdm\admin\components\Helper;
use yii\web\View;
use yii\helpers\Url;
use backend\components\Footer_Bulk_Delete;
use backend\components\Custom_Settings_Column_GridView;
use backend\models\business\MovementCashRegister;
use backend\models\nomenclators\MovementTypes;
use common\models\GlobalFunctions;
use yii\helpers\BaseStringHelper;
use backend\models\nomenclators\BranchOffice;
/* @var $this yii\web\View */
/* @var $searchModel backend\models\business\CashRegisteSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$controllerId = '/' . $this->context->uniqueId . '/';
$this->title = Yii::t('backend', $titulo) . ' ' . $cashRegister->box->numero . '-' . $cashRegister->box->name;
$this->params['breadcrumbs'][] = ['label' => 'Arqueo de Caja', 'url' => ['/cash-register/arqueo', 'box_id'=>$cashRegister->box_id]];
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="box-body">
    <?= $this->render('_form_cierre_caja', [
            'cashRegister' => $cashRegister, 
            'movement_type_id' => $movement_type_id, 
            'movement'=>$movement,
            'model' => $model, 
    ]) ?>
</div>
