<?php

use yii\db\Migration;

/**
 * Class m201229_205542_create_table_issuer
 */
class m201229_205542_create_table_issuer extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            '{{%issuer}}',
            [
                'id' => $this->primaryKey(),
                'code' => $this->string(),
                'identification_type_id' => $this->integer(),
                'identification' => $this->string(),
                'code_economic_activity' => $this->string(),
                'name' => $this->string(),
                'address' => $this->string(),
                'country_code_phone' => $this->string(),
                'phone' => $this->string(),
                'country_code_fax' => $this->string(),
                'fax' => $this->string(),
                'name_brach_office' => $this->string(),
                'number_brach_office' => $this->string(),
                'number_box' => $this->string(),
                'province_id' => $this->integer(),
                'canton_id' => $this->integer(),
                'disctrict_id' => $this->integer(),
                'other_signs' => $this->string(),
                'email' => $this->string(),
                'change_type_dollar' => $this->decimal(15, 2),
                'certificate_pin' => $this->string(),
                'api_user_hacienda' => $this->string(),
                'api_password' => $this->string(),
                'enable_prod_enviroment' => $this->boolean()->defaultValue('1'),
                'logo_file' => $this->string(),
                'certificate_digital_file' => $this->string(),
                'signature_digital_file' => $this->string(),
                'footer_one_receipt' => $this->string(),
                'footer_two_receipt' => $this->string(),
                'digital_proforma_footer' => $this->text(),
                'digital_invoice_footer' => $this->text(),
                'electronic_proforma_footer' => $this->text(),
                'electronic_invoice_footer' => $this->text(),
                'account_status_footer' => $this->text(),
                'invoice_header' => $this->text(),

                'created_at' => $this->dateTime(),
                'updated_at' => $this->dateTime(),

            ],
            $tableOptions
        );

        $this->addForeignKey(
            'fk_issuer_canton1',
            '{{%issuer}}',
            ['canton_id'],
            '{{%canton}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_issuer_disctrict1',
            '{{%issuer}}',
            ['disctrict_id'],
            '{{%disctrict}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_issuer_identification_type1',
            '{{%issuer}}',
            ['identification_type_id'],
            '{{%identification_type}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_issuer_province1',
            '{{%issuer}}',
            ['province_id'],
            '{{%province}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
    }

    public function down()
    {
        $this->dropTable('{{%issuer}}');
    }
}
