<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use kartik\dynagrid\DynaGrid;
use yii\helpers\Url;
use backend\models\business\ItemCreditNoteForm;
use common\models\GlobalFunctions;
use backend\models\nomenclators\UnitType;
use backend\models\nomenclators\UtilsConstants;
use mdm\admin\components\Helper;

/* @var $this yii\web\View */
/* @var $searchModelItems \backend\models\business\ItemCreditNoteSearch */
/* @var $dataProviderItems yii\data\ActiveDataProvider */

$title = Yii::t('backend', 'Items');
?>

<?= GlobalFunctions::showModalHtmlContent(Yii::t('backend', 'Imagen'), 'modal-lg') ?>

<div class="item-credit-note-index" style="margin-top:15px;" id="panel-grid-item_creditnote">
    <input type="hidden" id="credit_note_id" value="<?= $model->id ?>" />



    <table class="kv-grid-table table table-bordered table-striped kv-table-wrap">
        <colgroup>
            <col>
            <col class="skip-export">
            <col>
            <col>
            <col>
            <col>
            <col>
            <col>
            <col>
            <col class="skip-export">
        </colgroup>
        <thead>
            <tr>
                <th class="kv-align-center kv-align-middle kv-merged-header" style="width:50px;" rowspan="2" data-col-seq="0">#</th>
                <th class="kv-align-center" data-col-seq="2"><a href="http://www.herbavic.net/credit-note/update?id=47&amp;_pjax=%23grid_creditnote-pjax&amp;sort=code" data-sort="code">Código</a></th>
                <th class="custom_width kv-align-center" data-col-seq="3"><a href="http://www.herbavic.net/credit-note/update?id=47&amp;_pjax=%23grid_creditnote-pjax&amp;sort=description" data-sort="description">Descripción</a></th>
                <th class="kv-align-center kv-align-middle" data-col-seq="4"><a href="http://www.herbavic.net/credit-note/update?id=47&amp;_pjax=%23grid_creditnote-pjax&amp;sort=quantity" data-sort="quantity">Cantidad</a></th>
                <th class="kv-align-center" data-col-seq="5"><a href="http://www.herbavic.net/credit-note/update?id=47&amp;_pjax=%23grid_creditnote-pjax&amp;sort=unit_type_id" data-sort="unit_type_id">Cantidad a devolver</a></th>
                <th class="kv-align-center" data-col-seq="5"><a href="http://www.herbavic.net/credit-note/update?id=47&amp;_pjax=%23grid_creditnote-pjax&amp;sort=unit_type_id" data-sort="unit_type_id">Tipo/Unidad</a></th>
                <th class="kv-align-center" data-col-seq="6"><a href="http://www.herbavic.net/credit-note/update?id=47&amp;_pjax=%23grid_creditnote-pjax&amp;sort=price_type" data-sort="price_type">Lista precio</a></th>
                <th class="kv-align-center kv-align-middle" data-col-seq="7"><a href="http://www.herbavic.net/credit-note/update?id=47&amp;_pjax=%23grid_creditnote-pjax&amp;sort=price_unit" data-sort="price_unit">Precio unidad</a></th>
                <th class="kv-align-center kv-align-middle" data-col-seq="8"><a href="http://www.herbavic.net/credit-note/update?id=47&amp;_pjax=%23grid_creditnote-pjax&amp;sort=subtotal" data-sort="subtotal">Importe</a></th>
            </tr>
        </thead>
        <tbody>
            <?php
            $index = 1;
            foreach ($items as $item) :?>
            <tr class="itemcreditnote" data-key="97">
                <input type="hidden" name="row_id[]" value="<?= $item->id ?>">
                <input type="hidden" name="quantity[]" value="<?= (int)$item->quantity ?>">

                <td class="kv-align-center kv-align-middle itemcreditnote" style="width: 50px; mso-number-format: \@;" data-col-seq="0"><?= $index ?></td>
                <td class="kv-align-left kv-align-middle kv-align-center itemcreditnote" data-col-seq="2" style="mso-number-format: \@;"><?= $item->code ?></td>
                <td class="custom_width kv-align-center itemcreditnote" data-col-seq="3" style="mso-number-format: \@;"><?= $item->description ?></td>
                <td class="kv-align-center kv-align-middle itemcreditnote" data-col-seq="4" style="mso-number-format: \@;"><?= (int)$item->quantity ?></td>
                <td class="kv-align-center kv-align-middle itemcreditnote" data-col-seq="4" style="mso-number-format: \@;">
                    <input type="number" name="quantity_dev[]" max="<?= (int)$item->quantity ?>" min="0" size="10" value="0">
                </td>
                <td class="kv-align-left kv-align-middle kv-align-center itemcreditnote" data-col-seq="5" style="mso-number-format: \@;"><?= $item->unitType->code ?></td>
                <td class="kv-align-left kv-align-middle kv-align-center itemcreditnote" data-col-seq="6" style="mso-number-format: \@;"><?= UtilsConstants::getCustomerAsssignPriceSelectType($item->price_type)?></td>
                <td class="kv-align-left kv-align-middle kv-align-center itemcreditnote" data-col-seq="7" style="mso-number-format: \@;"><?= GlobalFunctions::formatNumber($item->price_unit,2) ?></td>
                <td class="kv-align-left kv-align-middle kv-align-center itemcreditnote" data-col-seq="8" style="mso-number-format: \@;"><?= GlobalFunctions::formatNumber($item->subtotal,2) ?></td>
            </tr>
            <?php
            $index++;
            endforeach;
            ?>
        </tbody>
    </table>







</div>

<?php
// Register action buttons js
$this->registerJs('
$(document).ready(function(e) {
	function init_click_handlers_item_invoice(){
	
	$("a.btn-update_item_creditnote").click(function(e) {
			e.preventDefault();
				$.ajax({
					type: "GET",
					url : $(this).attr("href"),
					success : function(data) {
						$("#panel-grid-item_creditnote").hide(500);
						$("#panel-form-item_creditnote").html(data);
					},
					error: function(XMLHttpRequest, textStatus, errorThrown) {
						$.notify({
							"message": "Ha ocurrido un error. Inténtelo nuevamente, si el error persiste, póngase en contacto con el administrador del sistema",
							"icon": "glyphicon glyphicon-remove text-danger-sign",
							"title": "Error <hr class=\"kv-alert-separator\">",							
							"showProgressbar": false,
							"url":"",						
							"target":"_blank"},{"type": "danger"}
						);
					}				
				});					
            
        });
        
        $("a.btn-simple_delete_item_invoice").click(function(e) {
			e.preventDefault();
			var url = $(this).attr("href");
			bootbox.confirm({
					message: "¿Est&aacute; seguro que desea eliminar este registro?",
					buttons: {
						confirm: {
							label: "Si",
							className: "btn-success"
						},
						cancel: {
							label: "No",
							className: "btn-danger"
						}
					},
					callback: function (result) {
						 if (result)
						 {
							$.ajax({
								type: "POST",
								url : url,
								success : function(response) {
									$.pjax.reload({container:"#grid_creditnote-pjax"});
									$.notify({
										"message": response.message,
										"icon": "glyphicon glyphicon-ok-sign",
										"title": response.titulo,										
										"showProgressbar": false,
										"url":"",						
										"target":"_blank"},{"type": response.type}
									);
								},
								error: function(XMLHttpRequest, textStatus, errorThrown) {
									$.notify({
										"message": "Ha ocurrido un error. Inténtelo nuevamente, si el error persiste, póngase en contacto con el administrador del sistema",
										"icon": "glyphicon glyphicon-remove text-danger-sign",
										"title": "Error <hr class=\"kv-alert-separator\">",								
										"showProgressbar": false,
										"url":"",						
										"target":"_blank"},{"type": "danger"}
									);
								}				
							});							 
						}
					}
				});	
        });
               
        $("a.btn-delete_item_invoice").click(function(e) {
			e.preventDefault();
            var selectedId = $("#itemcreditnote").yiiGridView("getSelectedRows");

            if(selectedId.length == 0) {
				bootbox.alert("Seleccione al menos un elemento"); 
            } else {
				var url = "' . Url::to(['/item-credit-note/deletemultiple_ajax'], GlobalFunctions::URLTYPE) . '";				
				bootbox.confirm({
					message: "¿Est&aacute; seguro que desea eliminar este registro?",
					buttons: {
						confirm: {
							label: "Si",
							className: "btn-success"
						},
						cancel: {
							label: "No",
							className: "btn-danger"
						}
					},
					callback: function (result) {
						 if (result)
						 {
							$.ajax({
								type: "POST",
								url : url,
								data : {ids: selectedId},
								success : function(response) {
									$.pjax.reload({container:"#grid_creditnote-pjax"});
									$.notify({
										"message": response.message,
										"icon": "glyphicon glyphicon-ok-sign",
										"title": response.titulo,										
										"showProgressbar": false,
										"url":"",						
										"target":"_blank"},{"type": response.type}
									);
								},
								error: function(XMLHttpRequest, textStatus, errorThrown) {
									$.notify({
										"message": "Ha ocurrido un error. Inténtelo nuevamente, si el error persiste, póngase en contacto con el administrador del sistema",
										"icon": "glyphicon glyphicon-remove text-danger-sign",
										"title": "Error <hr class=\"kv-alert-separator\">",								
										"showProgressbar": false,
										"url":"",						
										"target":"_blank"},{"type": "danger"}
									);
								}				
							});							 
						}
					}
				});				
            }
        });
	
		$("a.btn-refresh_item_invoice").click(function(e) {
			e.preventDefault();
            $.pjax.reload({container:"#grid_creditnote-pjax"});
        });
        
        function updateValores()
		{
			var fID = "' . $model->id . '";
			if(fID != "")
			{
                $.ajax({
                    type: \'POST\',			
                    dataType: "json",
                    url : "' . Url::to(['/credit-note/get-resume-credit-note?id=' . $model->id], GlobalFunctions::URLTYPE) . '",
                    data : {id: fID},
                    success : function(json) {			
                        $("#total_subtotal").html(json.total_subtotal);					
                        $("#total_tax").html(json.total_tax);					
                        $("#total_discount").html(json.total_discount);					
                        $("#total_exonerate").html(json.total_exonerate);
                        $("#total_price").html(json.total_price);				
                    },
                    error: function(XMLHttpRequest, textStatus, errorThrown) {
                        $.notify({
                            "message": "Ha ocurrido un error. Inténtelo nuevamente, si el error persiste, póngase en contacto con el administrador del sistema",
                            "icon": "glyphicon glyphicon-remove text-danger-sign",
                            "title": "Error <hr class=\"kv-alert-separator\">",					
                            "showProgressbar": false,
                            "url":"",						
                            "target":"_blank"},{"type": "danger"}
                        );
                    }					
                });
            }    		
		}
	
		updateValores();	
        
	}
	
	function appendClickImage(){
            $(\'.modalClickImage\').click(function (e) {
                e.preventDefault();
                var img = \'<div class="text-center img-bordered"><img style="width: 100%;" src="\'+$(this).attr(\'data-href\')+\'"></div>\';
                $(\'#modal\').modal(\'show\').find(\'#modalContent\').html(img);
            });
        }

	init_click_handlers_item_invoice(); //first run
	appendClickImage();
	$("#grid_creditnote-pjax").on("pjax:success", function() {
	    init_click_handlers_item_invoice(); //reactivate links in grid after pjax update
	    appendClickImage();
	});
});
');
?>