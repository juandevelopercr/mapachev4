<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use mdm\admin\components\Helper;
use yii\web\View;
use yii\helpers\Url;
use backend\components\Footer_Bulk_Delete;
use backend\components\Custom_Settings_Column_GridView;
use common\models\GlobalFunctions;
use backend\models\business\Customer;
use backend\models\business\InvoiceAbonos;
use backend\models\nomenclators\ConditionSale;
use backend\models\nomenclators\CreditDays;
use backend\models\nomenclators\Currency;
use backend\models\nomenclators\RouteTransport;
use backend\models\nomenclators\UtilsConstants;
use kartik\form\ActiveForm;
use kartik\tabs\TabsX;
use common\models\User;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\business\InvoiceSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$controllerId = '/' . $this->context->uniqueId . '/';
$this->title = Yii::t('backend', 'Cuentas por cobrar');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="accounts-receivable">    
    <?=TabsX::widget([
        'position' => TabsX::POS_ABOVE,
        'encodeLabels' => false,
        'items' => [
            [
                'label' => '<i class="glyphicon glyphicon-list-alt"></i> '.Yii::t('backend', 'Pendientes'),
                'content' => $this->render('_tab_pendientes', [
                        'searchModel' => $searchModelPendientes,
                        'dataProvider' => $dataProviderPendientes
                ]),
                'active' => true
            ],

            [
                'label' => '<i class="glyphicon glyphicon-globe"></i> '.Yii::t('backend', 'Canceladas'),
                'content' => $this->render('_tab_canceladas', [
                        'searchModel' => $searchModelCanceladas,
                        'dataProvider' => $dataProviderCanceladas
                ]),
                'active' => false
            ],
   
            [
                'label' => '<i class="glyphicon glyphicon-user"></i> '.Yii::t('backend', 'Canceladas por Nota'),
                'content' => $this->render('_tab_canceladas_notas', [
                        'searchModel' => $searchModelCanceladasNotas,
                        'dataProvider' => $dataProviderCanceladasNotas
                ]),
                'active' => false
            ],           
            [
                'label' => '<i class="glyphicon glyphicon-user"></i> '.Yii::t('backend', 'Con Abonos'),
                'content' => $this->render('_tab_abonos', [
                        'searchModel' => $searchModelAbonos,
                        'dataProvider' => $dataProviderAbonos
                ]),
                'active' => false
            ],    
            
            [
                'label' => '<i class="glyphicon glyphicon-user"></i> '.Yii::t('backend', 'Con Abonos Sinpe'),
                'content' => $this->render('_tab_abonos_simpe', [
                        'searchModel' => $searchModelAbonosSinpe,
                        'dataProvider' => $dataProviderAbonosSinpe,
                ]),
                'active' => false
            ],                

        ],
    ]);
    ?>
</div>