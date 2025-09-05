<?php
use common\models\User;
$customer = $this->params['customer'];
?>
<input id="symbol_right" type="hidden" value="" name="currency_symbol_left">
<input id="symbol_left" type="hidden" value="¢" name="currency_symbol_right">
<input id="decimal_point" type="hidden" value="." name="currency_decimal_point">
<input id="thousand_point" type="hidden" value="," name="currency_thousand_point">
<input id="decimal_place" type="hidden" value="2" name="currency_decimal_place">
<input id="receiptPrinting" type="hidden" value="1" name="receiptPrinting">
<input type='hidden' id='prodName'>
<input type='hidden' id='orderCustomerId' value="<?= !is_null($customer) ? $customer->id: '' ?>" />
<input type='hidden' id='orderCustomerName' value=<?= !is_null($customer) ? $customer->name: '' ?> />
<input type='hidden' id='mpantalla' value='ini' />
<input type='hidden' id='morder_save' value='0' />
<input type='hidden' id='all_percentage_discount' value='0' />
<input type='hidden' id='printer_default' value='0' />
<input type='hidden' id='default_invoice' value='FacSimpli' />

<nav2 class="nav2">
    <div class="row" style="margin-left: 7%;">
        <div class="col-md-10 col-sm-10 col-xs-10 side_mn_tittle">
            <?php
            $user = User::find()->where(['id'=>Yii::$app->user->id])->one();
            ?>
            Herbavic <nobr>(<?= $user->box->numero.'-'.$user->box->name ?>)</nobr>
        </div>
        <div class="col-md-2 col-sm-2 col-xs-2 mn_nav2" style="text-align: left;">
            <a href="#" class="ssm-toggle-nav" title="closed" id="m-ssm-toggle">
                <span class="glyphicon glyphicon-remove-circle"></span>
            </a>

        </div>
    </div>
    <div class="row" style="margin-bottom: 25px; margin-top: 20px; padding-bottom: 8px;">
        <div class="text-center" style="padding: 8px 0; text-align: center;">
            <img src="/images/p1.jpg" class="img-rounded" alt="<?= $user->name ?>" style="max-width: 90px; margin: 0 auto;">
        </div>
        <div id="us_name" class="text-center" style="padding: 2px 0;"></div>
        <div id="us_vent" class="text-center" style="padding: 2px 0;"></div>
    </div>

    <div class="row" style="margin-left: 6%;">
        <div class="col-sm-12 mn_side">
            <ul class="lst_side">
                <a class="ssm-toggle-nav" href="#" onClick="dir_ini()">
                    <li>Inicio</li>
                </a>
                <a class="ssm-toggle-nav" href="#" onClick="m_ventas()">
                    <li>Últimas ventas</li>
                </a>
                <a class="ssm-toggle-nav" href="#" onClick="m_historicoventas()">
                    <li>Histórico de ventas</li>
                </a>
                <a class="ssm-toggle-nav" href="#" onClick="lst_box_operations();">
                    <li>Caja</li>
                </a>
                <a class="ssm-toggle-nav" href="" onClick="closebox(); return false;">
                    <li>Salir</li>
                </a>
            </ul>
        </div>
    </div>
</nav2>