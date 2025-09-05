<?php

use yii\db\Migration;
use backend\models\settings\Issuer;

/**
 * Class m210623_042152_set_footers_issuer_settings
 */
class m210623_042152_set_footers_issuer_settings extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $model = Issuer::find()->one();
        $model->digital_invoice_footer = "Documento emitido conforme lo establecido en la resolución de Factura Electrónica, Nº; DGT-R-48-2016 del siete de octubre de dos mil dieciséis de la Dirección General de Tributación.";
        $model->electronic_invoice_footer = "Renuncio a mi domicilio y los trámites de juicio ejecutivo, al mismo tiempo doy por aceptadas las condiciones del Código de Comercio según artículo 460. Esta factura devengará intereses del 5% mensual después de 30 días de su emisión y su cancelación será únicamente contra nuestro recibo de dinero.";
        $model->footer_one_receipt = "CÓDIGO DE REGISTRO FISCAL BEBIDAS ALCÓHOLICAS DV0078";
        $model->save(false);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m210623_042152_set_footers_issuer_settings cannot be reverted.\n";
    }

}
