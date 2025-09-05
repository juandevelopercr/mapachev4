<?php

use kartik\number\NumberControl;

/* @var $this yii\web\View */
/* @var $model backend\models\business\Product */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="row">
    <div class="col-md-3">
        <?=
        $form->field($model, "price")->widget(NumberControl::classname(), [
            "maskedInputOptions" => [
                "allowMinus" => false,
                "groupSeparator" => ".",
                "radixPoint" => ",",
                "digits" => 2
            ],
            "displayOptions" => ["class" => "form-control kv-monospace"],
            "saveInputContainer" => ["class" => "kv-saved-cont"]
        ])
        ?>
    </div>   
    <div class="col-md-3">
        <?=
        $form->field($model, "price_custom")->widget(NumberControl::classname(), [
            "maskedInputOptions" => [
                "allowMinus" => false,
                "groupSeparator" => ".",
                "radixPoint" => ",",
                "digits" => 2
            ],
            "displayOptions" => ["class" => "form-control kv-monospace"],
            "saveInputContainer" => ["class" => "kv-saved-cont"]
        ])
        ?>
    </div>
    <div class="col-md-3">
        <?=
        $form->field($model, "price_bulto")->widget(NumberControl::classname(), [
            'readonly'=>true,
            "maskedInputOptions" => [
                "allowMinus" => false,
                "groupSeparator" => ".",
                "radixPoint" => ",",
                "digits" => 2
            ],
            "displayOptions" => ["class" => "form-control kv-monospace"],
            "saveInputContainer" => ["class" => "kv-saved-cont"]
        ])
        ?>
    </div> 
    <div class="col-md-3">
        <?=
        $form->field($model, "price_bulto_with_iva")->widget(NumberControl::classname(), [
            'readonly'=>true,
            "maskedInputOptions" => [
                "allowMinus" => false,
                "groupSeparator" => ".",
                "radixPoint" => ",",
                "digits" => 2
            ],
            "displayOptions" => ["class" => "form-control kv-monospace"],
            "saveInputContainer" => ["class" => "kv-saved-cont"]
        ])->label("Precio bulto + iva (".(int)$model->tax_rate_percent."%)")
        ?>
    </div>        
</div>

<div class="row">
    <div class="col-md-12">
        <fieldset style="width: 100%; border: 1px solid #C0C0C0; padding: 10px;">
            <legend style="width: auto; margin: 8px; border: 0; padding-right: 1%; padding-left: 1%; font-size: 16px; font-weight: bold; border: 1px solid #C0C0C0;"><?= Yii::t('backend','Calculadora') ?></legend>
            <div class="row">
                <div class="col-md-3" style="border-right: 1px solid black;">
                    <?=
                    $form->field($model, "calc_percent1")->widget(NumberControl::classname(), [
                        "maskedInputOptions" => [
                            "allowMinus" => false,
                            "groupSeparator" => ".",
                            "radixPoint" => ",",
                            "digits" => 2,
                        ],
                        "displayOptions" => ["class" => "form-control kv-monospace"],
                        "saveInputContainer" => ["class" => "kv-saved-cont"]
                    ])
                    ?>
                </div>
                <div class="col-md-3">
                    <?=
                    $form->field($model, "calc_price1")->widget(NumberControl::classname(), [
                        'readonly'=>true,
                        "maskedInputOptions" => [
                            "allowMinus" => false,
                            "groupSeparator" => ".",
                            "radixPoint" => ",",
                            "digits" => 5,
                        ],
                        "displayOptions" => ["class" => "form-control kv-monospace"],
                        "saveInputContainer" => ["class" => "kv-saved-cont"]
                    ])
                    ?>
                </div>
                <div class="col-md-3">
                    <?=
                    $form->field($model, "calc_utility1")->widget(NumberControl::classname(), [
                        'readonly'=>true,
                        "maskedInputOptions" => [
                            "allowMinus" => false,
                            "groupSeparator" => ".",
                            "radixPoint" => ",",
                            "digits" => 5,
                        ],
                        "displayOptions" => ["class" => "form-control kv-monospace"],
                        "saveInputContainer" => ["class" => "kv-saved-cont"]
                    ])
                    ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3" style="border-right: 1px solid black;">
                    <?=
                    $form->field($model, "calc_price2")->widget(NumberControl::classname(), [
                        "maskedInputOptions" => [
                            "allowMinus" => false,
                            "groupSeparator" => ".",
                            "radixPoint" => ",",
                            "digits" => 5,
                        ],
                        "displayOptions" => ["class" => "form-control kv-monospace"],
                        "saveInputContainer" => ["class" => "kv-saved-cont"]
                    ])
                    ?>

                </div>
                <div class="col-md-3">
                    <?=
                    $form->field($model, "calc_percent2")->widget(NumberControl::classname(), [
                        'readonly'=>true,
                        "maskedInputOptions" => [
                            "allowMinus" => false,
                            "groupSeparator" => ".",
                            "radixPoint" => ",",
                            "digits" => 2,
                        ],
                        "displayOptions" => ["class" => "form-control kv-monospace"],
                        "saveInputContainer" => ["class" => "kv-saved-cont"]
                    ])
                    ?>
                </div>
                <div class="col-md-3">
                    <?=
                    $form->field($model, "calc_utility2")->widget(NumberControl::classname(), [
                        'readonly'=>true,
                        "maskedInputOptions" => [
                            "allowMinus" => false,
                            "groupSeparator" => ".",
                            "radixPoint" => ",",
                            "digits" => 5,
                        ],
                        "displayOptions" => ["class" => "form-control kv-monospace"],
                        "saveInputContainer" => ["class" => "kv-saved-cont"]
                    ])
                    ?>
                </div>
            </div>

        </fieldset>
    </div>
</div>
<br>
<div class="row">
    <div class="col-md-2">
        <?=
        $form->field($model, "percent1")->widget(NumberControl::classname(), [
            "maskedInputOptions" => [
                "allowMinus" => false,
                "groupSeparator" => ".",
                "radixPoint" => ",",
                "digits" => 2,
            ],
            "displayOptions" => ["class" => "form-control kv-monospace"],
            "saveInputContainer" => ["class" => "kv-saved-cont"]
        ])
        ?>
    </div>
    <div class="col-md-2">
        <?=
        $form->field($model, "price1")->widget(NumberControl::classname(), [
            'readonly' => true,
            "maskedInputOptions" => [
                "allowMinus" => false,
                "groupSeparator" => ".",
                "radixPoint" => ",",
                "digits" => 2
            ],
            "displayOptions" => ["class" => "form-control kv-monospace"],
            "saveInputContainer" => ["class" => "kv-saved-cont"]
        ])
        ?>
    </div>
    <div class="col-md-2">
        <?=
        $form->field($model, "price1_with_iva")->widget(NumberControl::classname(), [
            'readonly' => true,
            "maskedInputOptions" => [
                "allowMinus" => false,
                "groupSeparator" => ".",
                "radixPoint" => ",",
                "digits" => 2
            ],
            "displayOptions" => ["class" => "form-control kv-monospace"],
            "saveInputContainer" => ["class" => "kv-saved-cont"]
        ])->label("Precio 1 + iva (".(int)$model->tax_rate_percent."%)")
        ?>
    </div>    
    <div class="col-md-2">
        <?=
        $form->field($model, "utility1")->widget(NumberControl::classname(), [
            'readonly' => true,
            "maskedInputOptions" => [
                "allowMinus" => false,
                "groupSeparator" => ".",
                "radixPoint" => ",",
                "digits" => 2
            ],
            "displayOptions" => ["class" => "form-control kv-monospace"],
            "saveInputContainer" => ["class" => "kv-saved-cont"]
        ])
        ?>
    </div>
    <div class="col-md-2">
        <?=
        $form->field($model, "pricebulto1")->widget(NumberControl::classname(), [
            'readonly' => true,
            "maskedInputOptions" => [
                "allowMinus" => false,
                "groupSeparator" => ".",
                "radixPoint" => ",",
                "digits" => 2
            ],
            "displayOptions" => ["class" => "form-control kv-monospace"],
            "saveInputContainer" => ["class" => "kv-saved-cont"]
        ])
        ?>
    </div>
    <div class="col-md-2">
        <?=
        $form->field($model, "price_bulto1_with_iva")->widget(NumberControl::classname(), [
            'readonly' => true,
            "maskedInputOptions" => [
                "allowMinus" => false,
                "groupSeparator" => ".",
                "radixPoint" => ",",
                "digits" => 2
            ],
            "displayOptions" => ["class" => "form-control kv-monospace"],
            "saveInputContainer" => ["class" => "kv-saved-cont"]
        ])->label("Precio bulto 1 + iva (".(int)$model->tax_rate_percent."%)")
        ?>
    </div>          
</div>
<div class="row">
    <div class="col-md-2">
        <?=
        $form->field($model, "percent2")->widget(NumberControl::classname(), [
            "maskedInputOptions" => [
                "allowMinus" => false,
                "groupSeparator" => ".",
                "radixPoint" => ",",
                "digits" => 2,
            ],
            "displayOptions" => ["class" => "form-control kv-monospace"],
            "saveInputContainer" => ["class" => "kv-saved-cont"]
        ])
        ?>
    </div>
    <div class="col-md-2">
        <?=
        $form->field($model, "price2")->widget(NumberControl::classname(), [
            'readonly' => true,
            "maskedInputOptions" => [
                "allowMinus" => false,
                "groupSeparator" => ".",
                "radixPoint" => ",",
                "digits" => 2
            ],
            "displayOptions" => ["class" => "form-control kv-monospace"],
            "saveInputContainer" => ["class" => "kv-saved-cont"]
        ])
        ?>
    </div>
    <div class="col-md-2">
        <?=
        $form->field($model, "price2_with_iva")->widget(NumberControl::classname(), [
            'readonly' => true,
            "maskedInputOptions" => [
                "allowMinus" => false,
                "groupSeparator" => ".",
                "radixPoint" => ",",
                "digits" => 2
            ],
            "displayOptions" => ["class" => "form-control kv-monospace"],
            "saveInputContainer" => ["class" => "kv-saved-cont"]
        ])->label("Precio 2 + iva (".(int)$model->tax_rate_percent."%)")
        ?>
    </div>        
    <div class="col-md-2">
        <?=
        $form->field($model, "utility2")->widget(NumberControl::classname(), [
            'readonly' => true,
            "maskedInputOptions" => [
                "allowMinus" => false,
                "groupSeparator" => ".",
                "radixPoint" => ",",
                "digits" => 2
            ],
            "displayOptions" => ["class" => "form-control kv-monospace"],
            "saveInputContainer" => ["class" => "kv-saved-cont"]
        ])
        ?>
    </div>
    <div class="col-md-2">
        <?=
        $form->field($model, "pricebulto2")->widget(NumberControl::classname(), [
            'readonly' => true,
            "maskedInputOptions" => [
                "allowMinus" => false,
                "groupSeparator" => ".",
                "radixPoint" => ",",
                "digits" => 2
            ],
            "displayOptions" => ["class" => "form-control kv-monospace"],
            "saveInputContainer" => ["class" => "kv-saved-cont"]
        ])
        ?>
    </div>  
    <div class="col-md-2">
        <?=
        $form->field($model, "price_bulto2_with_iva")->widget(NumberControl::classname(), [
            'readonly' => true,
            "maskedInputOptions" => [
                "allowMinus" => false,
                "groupSeparator" => ".",
                "radixPoint" => ",",
                "digits" => 2
            ],
            "displayOptions" => ["class" => "form-control kv-monospace"],
            "saveInputContainer" => ["class" => "kv-saved-cont"]
        ])->label("Precio bulto 2 + iva (".(int)$model->tax_rate_percent."%)")
        ?>
    </div>          
</div>

<div class="row">
    <div class="col-md-2">
        <?=
        $form->field($model, "percent3")->widget(NumberControl::classname(), [
            "maskedInputOptions" => [
                "allowMinus" => false,
                "groupSeparator" => ".",
                "radixPoint" => ",",
                "digits" => 2,
            ],
            "displayOptions" => ["class" => "form-control kv-monospace"],
            "saveInputContainer" => ["class" => "kv-saved-cont"]
        ])
        ?>
    </div>
    <div class="col-md-2">
        <?=
        $form->field($model, "price3")->widget(NumberControl::classname(), [
            'readonly' => true,
            "maskedInputOptions" => [
                "allowMinus" => false,
                "groupSeparator" => ".",
                "radixPoint" => ",",
                "digits" => 2
            ],
            "displayOptions" => ["class" => "form-control kv-monospace"],
            "saveInputContainer" => ["class" => "kv-saved-cont"]
        ])
        ?>
    </div>
    <div class="col-md-2">
        <?=
        $form->field($model, "price3_with_iva")->widget(NumberControl::classname(), [
            'readonly' => true,
            "maskedInputOptions" => [
                "allowMinus" => false,
                "groupSeparator" => ".",
                "radixPoint" => ",",
                "digits" => 2
            ],
            "displayOptions" => ["class" => "form-control kv-monospace"],
            "saveInputContainer" => ["class" => "kv-saved-cont"]
        ])->label("Precio 3 + iva (".(int)$model->tax_rate_percent."%)")
        ?>
    </div>      
    <div class="col-md-2">
        <?=
        $form->field($model, "utility3")->widget(NumberControl::classname(), [
            'readonly' => true,
            "maskedInputOptions" => [
                "allowMinus" => false,
                "groupSeparator" => ".",
                "radixPoint" => ",",
                "digits" => 2
            ],
            "displayOptions" => ["class" => "form-control kv-monospace"],
            "saveInputContainer" => ["class" => "kv-saved-cont"]
        ])
        ?>
    </div>
    <div class="col-md-2">
        <?=
        $form->field($model, "pricebulto3")->widget(NumberControl::classname(), [
            'readonly' => true,
            "maskedInputOptions" => [
                "allowMinus" => false,
                "groupSeparator" => ".",
                "radixPoint" => ",",
                "digits" => 2
            ],
            "displayOptions" => ["class" => "form-control kv-monospace"],
            "saveInputContainer" => ["class" => "kv-saved-cont"]
        ])
        ?>
    </div> 
    <div class="col-md-2">
        <?=
        $form->field($model, "price_bulto3_with_iva")->widget(NumberControl::classname(), [
            'readonly' => true,
            "maskedInputOptions" => [
                "allowMinus" => false,
                "groupSeparator" => ".",
                "radixPoint" => ",",
                "digits" => 2
            ],
            "displayOptions" => ["class" => "form-control kv-monospace"],
            "saveInputContainer" => ["class" => "kv-saved-cont"]
        ])->label("Precio bulto 3 + iva (".(int)$model->tax_rate_percent."%)")
        ?>
    </div>          
</div>

<div class="row">
    <div class="col-md-2">
        <?=
        $form->field($model, "percent4")->widget(NumberControl::classname(), [
            "maskedInputOptions" => [
                "allowMinus" => false,
                "groupSeparator" => ".",
                "radixPoint" => ",",
                "digits" => 2,
            ],
            "displayOptions" => ["class" => "form-control kv-monospace"],
            "saveInputContainer" => ["class" => "kv-saved-cont"]
        ])
        ?>
    </div>
    <div class="col-md-2">
        <?=
        $form->field($model, "price4")->widget(NumberControl::classname(), [
            'readonly' => true,
            "maskedInputOptions" => [
                "allowMinus" => false,
                "groupSeparator" => ".",
                "radixPoint" => ",",
                "digits" => 2
            ],
            "displayOptions" => ["class" => "form-control kv-monospace"],
            "saveInputContainer" => ["class" => "kv-saved-cont"]
        ])
        ?>
    </div>
    <div class="col-md-2">
        <?=
        $form->field($model, "price4_with_iva")->widget(NumberControl::classname(), [
            'readonly' => true,
            "maskedInputOptions" => [
                "allowMinus" => false,
                "groupSeparator" => ".",
                "radixPoint" => ",",
                "digits" => 2
            ],
            "displayOptions" => ["class" => "form-control kv-monospace"],
            "saveInputContainer" => ["class" => "kv-saved-cont"]
        ])->label("Precio 4 + iva (".(int)$model->tax_rate_percent."%)")
        ?>
    </div>      
    <div class="col-md-2">
        <?=
        $form->field($model, "utility4")->widget(NumberControl::classname(), [
            'readonly' => true,
            "maskedInputOptions" => [
                "allowMinus" => false,
                "groupSeparator" => ".",
                "radixPoint" => ",",
                "digits" => 2
            ],
            "displayOptions" => ["class" => "form-control kv-monospace"],
            "saveInputContainer" => ["class" => "kv-saved-cont"]
        ])
        ?>
    </div>
    <div class="col-md-2">
        <?=
        $form->field($model, "pricebulto4")->widget(NumberControl::classname(), [
            'readonly' => true,
            "maskedInputOptions" => [
                "allowMinus" => false,
                "groupSeparator" => ".",
                "radixPoint" => ",",
                "digits" => 2
            ],
            "displayOptions" => ["class" => "form-control kv-monospace"],
            "saveInputContainer" => ["class" => "kv-saved-cont"]
        ])
        ?>
    </div>  
    <div class="col-md-2">
        <?=
        $form->field($model, "price_bulto4_with_iva")->widget(NumberControl::classname(), [
            'readonly' => true,
            "maskedInputOptions" => [
                "allowMinus" => false,
                "groupSeparator" => ".",
                "radixPoint" => ",",
                "digits" => 2
            ],
            "displayOptions" => ["class" => "form-control kv-monospace"],
            "saveInputContainer" => ["class" => "kv-saved-cont"]
        ])->label("Precio bulto 4 + iva (".(int)$model->tax_rate_percent."%)")
        ?>
    </div>      
</div>

<div class="row">
    <div class="col-md-2">
        <?=
        $form->field($model, "percent_detail")->widget(NumberControl::classname(), [
            "maskedInputOptions" => [
                "allowMinus" => false,
                "groupSeparator" => ".",
                "radixPoint" => ",",
                "digits" => 2,
            ],
            "displayOptions" => ["class" => "form-control kv-monospace"],
            "saveInputContainer" => ["class" => "kv-saved-cont"]
        ])
        ?>
    </div>
    <div class="col-md-2">
        <?=
        $form->field($model, "price_detail")->widget(NumberControl::classname(), [
            'readonly' => true,
            "maskedInputOptions" => [
                "allowMinus" => false,
                "groupSeparator" => ".",
                "radixPoint" => ",",
                "digits" => 2
            ],
            "displayOptions" => ["class" => "form-control kv-monospace"],
            "saveInputContainer" => ["class" => "kv-saved-cont"]
        ])
        ?>
    </div>
    <div class="col-md-2">
        <?=
        $form->field($model, "price5_with_iva")->widget(NumberControl::classname(), [
            'readonly' => true,
            "maskedInputOptions" => [
                "allowMinus" => false,
                "groupSeparator" => ".",
                "radixPoint" => ",",
                "digits" => 2
            ],
            "displayOptions" => ["class" => "form-control kv-monospace"],
            "saveInputContainer" => ["class" => "kv-saved-cont"]
        ])->label("Precio detalle + iva (".(int)$model->tax_rate_percent."%)")
        ?>
    </div>      
    <div class="col-md-2">
        <?=
        $form->field($model, "utility5")->widget(NumberControl::classname(), [
            'readonly' => true,
            "maskedInputOptions" => [
                "allowMinus" => false,
                "groupSeparator" => ".",
                "radixPoint" => ",",
                "digits" => 2
            ],
            "displayOptions" => ["class" => "form-control kv-monospace"],
            "saveInputContainer" => ["class" => "kv-saved-cont"]
        ])
        ?>
    </div>
    <div class="col-md-2">
        <?=
        $form->field($model, "pricebulto5")->widget(NumberControl::classname(), [
            'readonly' => true,
            "maskedInputOptions" => [
                "allowMinus" => false,
                "groupSeparator" => ".",
                "radixPoint" => ",",
                "digits" => 2
            ],
            "displayOptions" => ["class" => "form-control kv-monospace"],
            "saveInputContainer" => ["class" => "kv-saved-cont"]
        ])
        ?>
    </div>  
    <div class="col-md-2">
        <?=
        $form->field($model, "price_bulto5_with_iva")->widget(NumberControl::classname(), [
            'readonly' => true,
            "maskedInputOptions" => [
                "allowMinus" => false,
                "groupSeparator" => ".",
                "radixPoint" => ",",
                "digits" => 2
            ],
            "displayOptions" => ["class" => "form-control kv-monospace"],
            "saveInputContainer" => ["class" => "kv-saved-cont"]
        ])->label("Precio detalle bulto + iva (".(int)$model->tax_rate_percent."%)")
        ?>
    </div>          
</div>
<input type="hidden" name="hpackage_quantity" id="hpackage_quantity" value="<?= (is_null($model->package_quantity) || empty($model->package_quantity)) ? 0: $model->package_quantity ?>">
<?php

$js = <<<JS
// get the form id and set the event
$(document).ready(function(e) {
        
    refresh_prices();
    
	$("#product-price-disp").keyup(function (e) {
		refresh_prices();
	});	
	
	$("#product-percent_detail-disp").keyup(function (e) {
		refresh_detail();
	});	
	
	$("#product-percent1-disp").keyup(function (e) {
		refresh_price1();
	});	
	
	$("#product-percent2-disp").keyup(function (e) {
		refresh_price2();
	});	
	
	$("#product-percent3-disp").keyup(function (e) {
		refresh_price3();
	});	
	
	$("#product-percent4-disp").keyup(function (e) {
		refresh_price4();
	});
	
	function refresh_prices() {
        refresh_detail();
        refresh_price1();
        refresh_price2();
        refresh_price3();
        refresh_price4();
        refresh_calculates1();
        refresh_calculates2();
	}
	
	function refresh_detail() {
        price = $("#product-price-disp").val().replace(/\./g,"");
        price = price.replace(",",".");
        price = parseFloat(price);
        
        //DETAIL
        percent_detail = $("#product-percent_detail-disp").val();
        if(percent_detail != null && percent_detail != '')
        {            
            percent_detail = percent_detail.replace(/\./g,"");
            percent_detail = percent_detail.replace(",",".");
            percent_detail = parseFloat(percent_detail);
            
            var change_value_price_detail = ((price * percent_detail / 100) + price);
            $("#product-price_detail-disp").val(change_value_price_detail.toFixed(2));
            $("#product-price_detail").val(change_value_price_detail.toFixed(2));

            var change_value_price5_with_iva = change_value_price_detail * parseFloat($("#hpackage_quantity").val());
            $("#product-price5_with_iva-disp").val(change_value_price5_with_iva.toFixed(2));            
            $("#product-price5_with_iva").val(change_value_price5_with_iva.toFixed(2));              

            var change_value_utility5 = change_value_price_detail - price;
            $("#product-utility5-disp").val(change_value_utility5.toFixed(2));
            $("#product-utility5").val(change_value_utility5.toFixed(2));
            
            var change_value_price_bulto = change_value_price_detail * parseFloat($("#hpackage_quantity").val());
            $("#product-pricebulto5-disp").val(change_value_price_bulto.toFixed(2));            
            $("#product-pricebulto5").val(change_value_price_bulto.toFixed(2)); 

            var change_value_price_bulto_with_iva = change_value_price_bulto +  (change_value_price_bulto * parseFloat($("#hfield_tax_rate_percent").val() / 100));
            $("#product-price_bulto5_with_iva-disp").val(change_value_price_bulto_with_iva.toFixed(2));            
            $("#product-price_bulto5_with_iva").val(change_value_price_bulto_with_iva.toFixed(2)); 
        }
        else 
        {
           $("#product-percent_detail-disp").val(0);
           $("#product-percent_detail").val(0);
           
           $("#product-price_detail-disp").val(0);
           $("#product-price_detail").val(0); 
           
           $("#product-utility5-disp").val(0);
           $("#product-utility5").val(0);

           $("#product-pricebulto5-disp").val(0);            
           $("#product-pricebulto5").val(0);  

           $("#product-price_bulto-disp").val(0);            
           $("#product-price_bulto").val(0); 

           $("#product-price_bulto_with_iva-disp").val(0);            
           $("#product-price_bulto_with_iva").val(0);            
        }
	}
	
	function refresh_price1() {
        price = $("#product-price-disp").val().replace(/\./g,"");
        price = price.replace(",",".");
        price = parseFloat(price);
        
        //PRICE1
        percent1 = $("#product-percent1-disp").val();
        
        if(percent1 != null && percent1 != '' && parseFloat(percent1) > 0)
        {            
            percent1 = percent1.replace(/\./g,"");
            percent1 = percent1.replace(",",".");
            percent1 = parseFloat(percent1);
            
            var change_value_price1 = ((price * percent1 / 100) + price);
            $("#product-price1-disp").val(change_value_price1.toFixed(2));
            $("#product-price1").val(change_value_price1.toFixed(2));

            var change_value_price1_with_iva = change_value_price1 +  (change_value_price1 * parseFloat($("#hfield_tax_rate_percent").val() / 100));
            $("#product-price1_with_iva-disp").val(change_value_price1_with_iva.toFixed(2));
            $("#product-price1_with_iva").val(change_value_price1_with_iva.toFixed(2));
            
            var change_value_utility1 = change_value_price1 - price;
            $("#product-utility1-disp").val(change_value_utility1.toFixed(2));
            $("#product-utility1").val(change_value_utility1.toFixed(2));            

            var change_value_pricebulto1 = change_value_price1 * parseFloat($("#hpackage_quantity").val());
            $("#product-pricebulto1-disp").val(change_value_pricebulto1.toFixed(2));            
            $("#product-pricebulto1").val(change_value_pricebulto1.toFixed(2));      
                        
            var change_value_price_bulto1_with_iva = change_value_pricebulto1 +  (change_value_pricebulto1 * parseFloat($("#hfield_tax_rate_percent").val() / 100));
            $("#product-price_bulto1_with_iva-disp").val(change_value_price_bulto1_with_iva.toFixed(2));
            $("#product-price_bulto1_with_iva").val(change_value_price_bulto1_with_iva.toFixed(2));            
        }
        else 
        {
           $("#product-percent1-disp").val(0);
           $("#product-percent1").val(0);
           
           $("#product-price1-disp").val(0);
           $("#product-price1").val(0); 

           $("#product-price1_with_iva-disp").val(0);
           $("#product-price1_with_iva").val(0);
           
           $("#product-utility1-disp").val(0);
           $("#product-utility1").val(0);

           $("#product-pricebulto1-disp").val(0);          
           $("#product-pricebulto1").val(0);   
           
           $("#product-price_bulto1_with_iva-disp").val(0);
           $("#product-price_bulto1_with_iva").val(0);             
        }
	}
	
	function refresh_price2() {
        price = $("#product-price-disp").val().replace(/\./g,"");
        price = price.replace(",",".");
        price = parseFloat(price);
        
        //PRICE2
        percent2 = $("#product-percent2-disp").val();
        
        if(percent2 != null && percent2 != '' && parseFloat(percent2) > 0)
        {
            percent2 = percent2.replace(/\./g,"");
            percent2 = percent2.replace(",",".");
            percent2 = parseFloat(percent2);
            
            var change_value_price2 = ((price * percent2 / 100) + price);
            $("#product-price2-disp").val(change_value_price2.toFixed(2));
            $("#product-price2").val(change_value_price2.toFixed(2));

            var change_value_price2_with_iva = change_value_price2 +  (change_value_price2 * parseFloat($("#hfield_tax_rate_percent").val() / 100));
            $("#product-price2_with_iva-disp").val(change_value_price2_with_iva.toFixed(2));
            $("#product-price2_with_iva").val(change_value_price2_with_iva.toFixed(2));              
            
            var change_value_utility2 = change_value_price2 - price;
            $("#product-utility2-disp").val(change_value_utility2.toFixed(2));
            $("#product-utility2").val(change_value_utility2.toFixed(2));

            var change_value_pricebulto2 = change_value_price2 * parseFloat($("#hpackage_quantity").val());
            $("#product-pricebulto2-disp").val(change_value_pricebulto2.toFixed(2));            
            $("#product-pricebulto2").val(change_value_pricebulto2.toFixed(2));    
            
            var change_value_price_bulto2_with_iva = change_value_pricebulto2 +  (change_value_pricebulto2 * parseFloat($("#hfield_tax_rate_percent").val() / 100));
            $("#product-price_bulto2_with_iva-disp").val(change_value_price_bulto2_with_iva.toFixed(2));
            $("#product-price_bulto2_with_iva").val(change_value_price_bulto2_with_iva.toFixed(2));             
        }
        else 
        {
           $("#product-percent2-disp").val(0);
           $("#product-percent2").val(0);
           
           $("#product-price2-disp").val(0);
           $("#product-price2").val(0); 

           $("#product-price2_with_iva-disp").val(0);
           $("#product-price2_with_iva").val(0);           
           
           $("#product-utility2-disp").val(0);
           $("#product-utility2").val(0); 

           $("#product-pricebulto2-disp").val(0);          
           $("#product-pricebulto2").val(0);   
           
           $("#product-price_bulto2_with_iva-disp").val(0);
           $("#product-price_bulto2_with_iva").val(0);             
        }
	}
	
	function refresh_price3() {
        price = $("#product-price-disp").val().replace(/\./g,"");
        price = price.replace(",",".");
        price = parseFloat(price);
       
        //PRICE3
        percent3 = $("#product-percent3-disp").val();
        
        if(percent3 != null && percent3 != '' && parseFloat(percent3) > 0)
        {
            percent3 = percent3.replace(/\./g,"");
            percent3 = percent3.replace(",",".");
            percent3 = parseFloat(percent3);
            
            var change_value_price3 = ((price * percent3 / 100) + price);
            $("#product-price3-disp").val(change_value_price3.toFixed(2));
            $("#product-price3").val(change_value_price3.toFixed(2));

            var change_value_price3_with_iva = change_value_price3 +  (change_value_price3 * parseFloat($("#hfield_tax_rate_percent").val() / 100));
            $("#product-price3_with_iva-disp").val(change_value_price3_with_iva.toFixed(2));
            $("#product-price3_with_iva").val(change_value_price3_with_iva.toFixed(2));            
            
            var change_value_utility3 = change_value_price3 - price;
            $("#product-utility3-disp").val(change_value_utility3.toFixed(2));
            $("#product-utility3").val(change_value_utility3.toFixed(2));

            var change_value_pricebulto3 = change_value_price3 * parseFloat($("#hpackage_quantity").val());
            $("#product-pricebulto3-disp").val(change_value_pricebulto3.toFixed(2));            
            $("#product-pricebulto3").val(change_value_pricebulto3.toFixed(2));   
            
            var change_value_price_bulto3_with_iva = change_value_pricebulto3 +  (change_value_pricebulto3 * parseFloat($("#hfield_tax_rate_percent").val() / 100));
            $("#product-price_bulto3_with_iva-disp").val(change_value_price_bulto3_with_iva.toFixed(2));
            $("#product-price_bulto3_with_iva").val(change_value_price_bulto3_with_iva.toFixed(2));             
        }
        else 
        {
           $("#product-percent3-disp").val(0);
           $("#product-percent3").val(0);
           
           $("#product-price3-disp").val(0);
           $("#product-price3").val(0);  
           
           $("#product-price3_with_iva-disp").val(0);
           $("#product-price3_with_iva").val(0);             
           
           $("#product-utility3-disp").val(0);
           $("#product-utility3").val(0); 

           $("#product-pricebulto3-disp").val(0);          
           $("#product-pricebulto3").val(0); 
           
           $("#product-price_bulto3_with_iva-disp").val(0);
           $("#product-price_bulto3_with_iva").val(0);             
        }
	}
	
	function refresh_price4() {
        price = $("#product-price-disp").val().replace(/\./g,"");
        price = price.replace(",",".");
        price = parseFloat(price);
      
        //PRICE4
        percent4 = $("#product-percent4-disp").val();
        
        if(percent4 != null && percent4 != '' && parseFloat(percent4) > 0)
        {
            percent4 = percent4.replace(/\./g,"");
            percent4 = percent4.replace(",",".");
            percent4 = parseFloat(percent4);
            
            var change_value_price4 = ((price * percent4 / 100) + price);
            $("#product-price4-disp").val(change_value_price4.toFixed(2));
            $("#product-price4").val(change_value_price4.toFixed(2));

            var change_value_price4_with_iva = change_value_price4 +  (change_value_price4 * parseFloat($("#hfield_tax_rate_percent").val() / 100));
            $("#product-price4_with_iva-disp").val(change_value_price4_with_iva.toFixed(2));
            $("#product-price4_with_iva").val(change_value_price4_with_iva.toFixed(2));              
            
            var change_value_utility4 = change_value_price4 - price;
            $("#product-utility4-disp").val(change_value_utility4.toFixed(2));
            $("#product-utility4").val(change_value_utility4.toFixed(2));

            var change_value_pricebulto4 = change_value_price4 * parseFloat($("#hpackage_quantity").val());
            $("#product-pricebulto4-disp").val(change_value_pricebulto4.toFixed(2));            
            $("#product-pricebulto4").val(change_value_pricebulto4.toFixed(2));  
            
            var change_value_price_bulto4_with_iva = change_value_pricebulto4 +  (change_value_pricebulto4 * parseFloat($("#hfield_tax_rate_percent").val() / 100));
            $("#product-price_bulto4_with_iva-disp").val(change_value_price_bulto4_with_iva.toFixed(2));
            $("#product-price_bulto4_with_iva").val(change_value_price_bulto4_with_iva.toFixed(2));               
        }
        else 
        {
           $("#product-percent4-disp").val(0);
           $("#product-percent4").val(0);
           
           $("#product-price4-disp").val(0);
           $("#product-price4").val(0); 

           $("#product-price4_with_iva-disp").val(0);
           $("#product-price4_with_iva").val(0);             
           
           $("#product-utility4-disp").val(0);
           $("#product-utility4").val(0); 

           $("#product-pricebulto4-disp").val(0);          
           $("#product-pricebulto4").val(0);    
           
           $("#product-price_bulto4_with_iva-disp").val(0);
           $("#product-price_bulto4_with_iva").val(0);            
        }
	}
		
	$("#product-calc_percent1-disp").keyup(function (e) {
		refresh_calculates1();
	});	
	
	$("#product-calc_price2-disp").keyup(function (e) {
		refresh_calculates2();
	});	
	
	function refresh_calculates1() {
        price = $("#product-price-disp").val().replace(/\./g,"");
        price = price.replace(",",".");
        price = parseFloat(price);
        
        calc_percent1 = $("#product-calc_percent1-disp").val();
        if(calc_percent1 != null && calc_percent1 != '')
        {
            calc_percent1 = calc_percent1.replace(/\./g,"");
            calc_percent1 = calc_percent1.replace(",",".");
            calc_percent1 = parseFloat(calc_percent1);
            
            var change_value_price1 = ((price * calc_percent1 / 100) + price);
            $("#product-calc_price1-disp").val(change_value_price1.toFixed(2));
            $("#product-calc_price1").val(change_value_price1.toFixed(2));
                   
            var change_value_utility1 = change_value_price1 - price;
            $("#product-calc_utility1-disp").val(change_value_utility1.toFixed(2));
            $("#product-calc_utility1").val(change_value_utility1.toFixed(2));             
        }
	}
	
	function refresh_calculates2() {
        price = $("#product-price-disp").val().replace(/\./g,"");
        price = price.replace(",",".");
        price = parseFloat(price);
        
        calc_price2 = $("#product-calc_price2-disp").val();
        if(calc_price2 != null && calc_price2 != '')
        {
            calc_price2 = calc_price2.replace(/\./g,"");
            calc_price2 = calc_price2.replace(",",".");
            calc_price2 = parseFloat(calc_price2);
            
            var change_value_utility2 = calc_price2 - price;
            $("#product-calc_utility2-disp").val(change_value_utility2.toFixed(2));
            $("#product-calc_utility2").val(change_value_utility2.toFixed(2));
            
            var change_value_percent2 = (change_value_utility2 / price)*100;
            $("#product-calc_percent2-disp").val(change_value_percent2.toFixed(2));
            $("#product-calc_percent2").val(change_value_percent2.toFixed(2));
        }
	}
});
JS;
$this->registerJs($js);
?>


