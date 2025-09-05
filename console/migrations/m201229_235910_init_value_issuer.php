<?php

use yii\db\Migration;
use backend\models\settings\Issuer;
use backend\models\nomenclators\IdentificationType;
/**
 * Class m201229_235910_init_value_issuer
 */
class m201229_235910_init_value_issuer extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $identification = IdentificationType::find()->select(['id'])->where(['code' => '02'])->one();

        $issuer = new Issuer([
            'code' => '000001',
            'identification_type_id' => $identification->id,
            'identification' => '3101649959',
            'name' => 'HERBAVI',
            'country_code_phone' =>  '506',
            'address' => 'DE COOPEMONTECILLOS 2 KM. OESTE FRENTE AL INA BODEGAS DE URGELLES',
            'phone' => '24331507',
            'country_code_fax' => '',
            'fax' => '',
            'name_brach_office' => 'Sucursal1',
            'number_brach_office' => '1',
            'number_box' => '1',
            'other_signs' => 'DE COOPEMONTECILLOS 2 KM. OESTE FRENTE AL INA BODEGAS DE URGELLES',
            //'email' => 'facturamarboca@gmail.com',
            //'certificate_pin' => '1234',
            //'api_user_hacienda' => 'cpj-3-101-649959@prod.comprobanteselectronicos.go.cr',
            //'api_password' => 'g]Qz$*>[/4fehY[^PX|!',
            'enable_prod_enviroment' => 0,
            'change_type_dollar' => 611.54,
            'digital_proforma_footer' => '<p>&quot;Documento emitido conforme lo establecido en la resoluci&oacute;n de Factura Electr&oacute;nica, N&ordm; DGT-R-48-2016 del siete de octubre de dos mil diecis&eacute;is de la Direcci&oacute;n General de Tributaci&oacute;n.&quot;</p>',
            'digital_invoice_footer' => '<p>&quot;Documento emitido conforme lo establecido en la resoluci&oacute;n de Factura Electr&oacute;nica, N&ordm; DGT-R-48-2016 del siete de octubre de dos mil diecis&eacute;is de la Direcci&oacute;n General de Tributaci&oacute;n.&quot;</p>
                <p>ORDEN DE COMPRA&nbsp;</p>
                <p>TALLER GONZALEZ S.A CEDULA JURIDICA: 3-101-143237<br />
                CUENTAS BNCR<br />
                COLONES<br />
                CC:100-01-056-000267-8 // CCL:15105610010002672<br />
                DOLARES<br />
                CC:100-02-056-600059-3 // CCL:15105610026000592</p>',
            'account_status_footer' => '<p>Estimado cliente le recordamos nuestro n&uacute;mero de cuenta donde pueden realizar sus pagos:</p>
                <p>TALLER GONZALEZ S.A CEDULA JURIDICA: 3-101-143237<br />
                CUENTAS BNCR<br />
                COLONES<br />
                CC:100-01-056-000267-8 // CCL:15105610010002672<br />
                DOLARES<br />
                CC:100-02-056-600059-3 // CCL:15105610026000592</p>',
            'electronic_proforma_footer' => '<p>TALLER GONZALEZ S.A CEDULA JURIDICA: 3-101-143237<br />
                CUENTAS BNCR<br />
                COLONES<br />
                CC:100-01-056-000267-8 // CCL:15105610010002672<br />
                DOLARES<br />
                CC:100-02-056-600059-3 // CCL:15105610026000592</p>',
            'electronic_invoice_footer' => '<p>** Presupuesto V&aacute;lido por 8 d&iacute;as **<br />
                EL CALCULO DEL MATERIAL ES APROXIMADO Y QUEDA BAJO RESPONSABILIDAD DEL CLIENTE SI FALTA O<br />
                SOBRA MATERIAL.<br />
                ** NO SE ACEPTAN DEVOLUCIONES **</p>',
            'code_economic_activity' => '502004'
        ]);

        if(!$issuer->save())
        {
            print_r($issuer->getErrors());
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        Issuer::deleteAll();
    }
}
