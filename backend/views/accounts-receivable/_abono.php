<?php

/* @var $this yii\web\View */
/* @var $model backend\models\business\Invoice */

$this->title = Yii::t('backend', 'Crear').' '. Yii::t('backend', 'Abono');
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Cuentas [por Cobrar'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="box-body">

    <div class="row">
        <div class="col-md-7">
            <?= $this->render('_form_abono', [
                        'model' => $model,
                        'invoice'=>$invoice,
                        'total'=>$total,
                        'abonado'=> $abonado,
                        'pendiente'=> $pendiente,
                    ])
                    ?>
        </div>
        <div class="col-md-5"> 
            <?php
            /*            
            <?= $this->render('_list_abonos', [
                    'model' => $model,
                    'abonos'=>$abonos,
                ]) ?>
                */
                ?>
        </div>
    </div>                    
</div>
