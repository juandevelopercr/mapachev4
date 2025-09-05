<?php

use yii\db\Migration;
use backend\models\nomenclators\UtilsConstants;

/**
 * Class m210907_023122_create_table_debit_note
 */
class m210907_023122_create_table_debit_note extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            '{{%debit_note}}',
            [
                'id' => $this->primaryKey(),
                'branch_office_id' => $this->integer(),
                'customer_id' => $this->integer(),
                'condition_sale_id' => $this->integer(),
                'credit_days_id' => $this->integer(),
                'currency_id' => $this->integer(),
                'seller_id' => $this->integer(),
                'debit_note_type' => $this->integer(),
                'status_hacienda' => $this->integer(),
                'collector_id' => $this->integer(),
                'route_transport_id' => $this->integer(),
                'key' => $this->string(),
                'consecutive' => $this->string(),
                'emission_date' => $this->dateTime(),
                'change_type' => $this->decimal(18,5),
                'pay_date' => $this->date()->comment('fecha en la que se cancela la factura por un abono'),
                'observations' => $this->text(),
                'status_account_receivable_id' => $this->integer()->defaultValue(UtilsConstants::HACIENDA_STATUS_PENDING),
                'response_xml' => $this->string(),
                'contingency' => $this->tinyInteger()->defaultValue('0'),
                'ready_to_update_stock' => $this->tinyInteger()->defaultValue('0'),
                'ready_to_send_email' => $this->tinyInteger()->defaultValue('0'),
                'email_sent' => $this->tinyInteger()->defaultValue('0'),
                'correct_debit_note' => $this->tinyInteger()->defaultValue('0')->comment('1 Si corrige una factura'),
                'correct_debit_note_id' => $this->integer()->comment( 'id de la factura que corrige'),
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
            'fk_debit_note_branch_office1',
            '{{%debit_note}}',
            ['branch_office_id'],
            '{{%branch_office}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_debit_note_customer1',
            '{{%debit_note}}',
            ['customer_id'],
            '{{%customer}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_debit_note_condition_sale1',
            '{{%debit_note}}',
            ['condition_sale_id'],
            '{{%condition_sale}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_debit_note_credit_days',
            '{{%debit_note}}',
            ['credit_days_id'],
            '{{%credit_days}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_debit_note_currency',
            '{{%debit_note}}',
            ['currency_id'],
            '{{%currency}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_debit_note_user1',
            '{{%debit_note}}',
            ['seller_id'],
            '{{%user}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_debit_note_debit_note1',
            '{{%debit_note}}',
            ['correct_debit_note_id'],
            '{{%debit_note}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_debit_note_collector',
            '{{%debit_note}}',
            ['collector_id'],
            '{{%user}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_debit_note_route_transport1',
            '{{%debit_note}}',
            ['route_transport_id'],
            '{{%route_transport}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
    }

    public function down()
    {
        $this->dropTable('{{%debit_note}}');
    }
}
