<?php

use yii\db\Migration;
use backend\models\nomenclators\UtilsConstants;

/**
 * Class m210501_202139_create_table_invoice
 */
class m210501_202139_create_table_invoice extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            '{{%invoice}}',
            [
                'id' => $this->primaryKey(),
                'branch_office_id' => $this->integer(),
                'customer_id' => $this->integer(),
                'condition_sale_id' => $this->integer(),
                'credit_days_id' => $this->integer(),
                'currency_id' => $this->integer(),
                'seller_id' => $this->integer(),
                'invoice_type' => $this->integer(),
                'key' => $this->string(),
                'consecutive' => $this->string(),
                'emission_date' => $this->dateTime(),
                'change_type' => $this->decimal(18,5),
                'pay_date' => $this->date()->comment('fecha en la que se cancela la factura por un abono'),
                'observations' => $this->text(),
                'status_account_receivable_id' => $this->integer()->defaultValue(UtilsConstants::HACIENDA_STATUS_PENDING),
                'response_xml' => $this->string(),
                'contingency' => $this->tinyInteger()->defaultValue('0'),
                'correct_invoice' => $this->tinyInteger()->defaultValue('0')->comment('1 Si corrige una factura'),
                'correct_invoice_id' => $this->integer()->comment( 'id de la factura que corrige'),
                'reference_number' => $this->string(),
                'reference_emission_date' => $this->dateTime(),
                'reference_code' => $this->string()->comment( '05 Sustituye comprobante provisional por contingencia'),
                'reference_reason' => $this->string(),
                'access_token' => $this->string(),
                'erased_by_note' => $this->tinyInteger()->defaultValue('0'),
                'num_request_hacienda_set' => $this->tinyInteger()->defaultValue('0'),
                'num_request_hacienda_get' => $this->tinyInteger()->defaultValue('0'),
                'total_tax_crc' => $this->decimal(18,5)->defaultValue('0.00000'),
                'total_tax_usd' => $this->decimal(18,5)->defaultValue('0.00000'),
                'total_discount_crc' => $this->decimal(18,5)->defaultValue('0.00000'),
                'total_discount_usd' => $this->decimal(18,5)->defaultValue('0.00000'),
                'total_proof_crc' => $this->decimal(18,5)->defaultValue('0.00000'),
                'total_proof_usd' => $this->decimal(18,5)->defaultValue('0.00000'),
                'status' => $this->integer(),
                'created_at' => $this->dateTime(),
                'updated_at' => $this->dateTime(),
            ],
            $tableOptions
        );

        $this->addForeignKey(
            'fk_invoice_branch_office1',
            '{{%invoice}}',
            ['branch_office_id'],
            '{{%branch_office}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_invoice_customer1',
            '{{%invoice}}',
            ['customer_id'],
            '{{%customer}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_invoice_condition_sale1',
            '{{%invoice}}',
            ['condition_sale_id'],
            '{{%condition_sale}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_invoice_credit_days',
            '{{%invoice}}',
            ['credit_days_id'],
            '{{%credit_days}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_invoice_currency',
            '{{%invoice}}',
            ['currency_id'],
            '{{%currency}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_invoice_user1',
            '{{%invoice}}',
            ['seller_id'],
            '{{%user}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_invoice_invoice1',
            '{{%invoice}}',
            ['correct_invoice_id'],
            '{{%invoice}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
    }

    public function down()
    {
        $this->dropTable('{{%invoice}}');
    }
}
