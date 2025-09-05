<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model backend\models\business\Documents */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Documents', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="documents-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'receiver_id',
            'key',
            'consecutive',
            'transmitter',
            'transmitter_identification_type',
            'transmitter_identification',
            'document_type',
            'emission_date',
            'reception_date',
            [
                'attribute'=> 'url_xml',
                'value'=> function($data){
                    $url_xml = $data->getFileUrlXML();

                    $color1 = 'blue';
                    $tipo = '';
                    $link = '';                                     
                    $url_verificar = Yii::getAlias('@backend/web/uploads/documents/'.$data->url_xml);
                    switch ($data->document_type)
                    {
                        case '01':$tipo = 'FE';
                                  break;
                        case '02':$tipo = 'ND';
                                  break;
                        case '03':$tipo = 'NC';
                                  break;
                        case '04':$tipo = 'TE';
                                  break;
                        case '05':$tipo = 'MR';
                                  break;
                        case '06':$tipo = 'MR';
                                  break;
                        case '07':$tipo = 'MR';
                                  break;
                        case '08':$tipo = 'FEC';
                                  break;
                        case '09':$tipo = 'FEE';								  
                    }                      

                    if (!is_null($url_xml) && !empty($url_xml) && file_exists($url_verificar))
                        $link = "<a href=\"".$url_xml."\" title=\"".$data->key."\" data-method=\"post\" data-pjax=\"0\" target=\"_blank\" class=\"badge bg-".$color1."\"><i class=\"fa fa-fw fa-download\"></i> ".$tipo."-XML</a>";
                    return $link;                    
                },
                'format'=> 'html',
            ],
            [
                'attribute'=> 'url_pdf',
                'value'=> function($data){
                    $url_pdf = $data->getFileUrlPDF();   
                    $color1 = 'blue';
                    $tipo = '';
                    $link = '';                                     
                    $url_verificar = Yii::getAlias('@backend/web/uploads/documents/'.$data->url_pdf);
                    switch ($data->document_type)
                    {
                        case '01':$tipo = 'FE';
                                  break;
                        case '02':$tipo = 'ND';
                                  break;
                        case '03':$tipo = 'NC';
                                  break;
                        case '04':$tipo = 'TE';
                                  break;
                        case '05':$tipo = 'MR';
                                  break;
                        case '06':$tipo = 'MR';
                                  break;
                        case '07':$tipo = 'MR';
                                  break;
                        case '08':$tipo = 'FEC';
                                  break;
                        case '09':$tipo = 'FEE';								  
                    }                      

                    if (!is_null($url_pdf) && !empty($url_pdf) && file_exists($url_verificar))
                        $link = "<a href=\"".$url_pdf."\" title=\"".$data->key."\" data-method=\"post\" data-pjax=\"0\" target=\"_blank\" class=\"badge bg-".$color1."\"><i class=\"fa fa-fw fa-download\"></i> ".$tipo."-PDF</a>";
                    return $link;
                        
                },
                'format'=> 'html',
            ], 
            [
                'attribute'=> 'url_ahc',
                'value'=> function($data){
                    $url_xml_hacienda_verificar = Yii::getAlias('@backend/web/uploads/xmlh/'.$data->url_ahc);
                    $url = Yii::getAlias('/backend/web/uploads/xmlh/'.$data->url_ahc);
                    $color1 = 'blue';
                    $tipo = '';
                    $link = '';
                    switch ($data->document_type)
                    {
                        case '01':$tipo = 'FE';
                                  break;
                        case '02':$tipo = 'ND';
                                  break;
                        case '03':$tipo = 'NC';
                                  break;
                        case '04':$tipo = 'TE';
                                  break;
                        case '05':$tipo = 'MR';
                                  break;
                        case '06':$tipo = 'MR';
                                  break;
                        case '07':$tipo = 'MR';
                                  break;
                        case '08':$tipo = 'FEC';
                                  break;
                        case '09':$tipo = 'FEE';								  
                    }                    
                    if (!is_null($data->url_ahc) && !empty($data->url_ahc) && file_exists($url_xml_hacienda_verificar))
                    {
                        $link = "<a href=\"".$url."\" title=\"".$data->key."\" data-method=\"post\" data-pjax=\"0\" target=\"_blank\" class=\"badge bg-".$color1."\"><i class=\"fa fa-fw fa-download\"></i> ".$tipo."-XML-H</a>";
                    }   
                    return $link;
                },
                'format'=> 'html',
            ],                          
            'currency',
            'change_type',
            'total_tax',
            'total_invoice',
            'transmitter_email:email',
            'message_detail',
            'tax_condition',
            'total_amount_tax_credit',
            'total_amount_applicable_expense',
            'attempts_making_set',
            'attempts_making_get',
            'state_id',
        ],
    ]) ?>

</div>
