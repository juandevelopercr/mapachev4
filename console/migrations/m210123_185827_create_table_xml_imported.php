<?php

use yii\db\Migration;

/**
 * Class m210123_185827_create_table_xml_imported
 */
class m210123_185827_create_table_xml_imported extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            '{{%xml_imported}}',
            [
                'id' => $this->primaryKey(),
                'currency_code' => $this->string(),
                'currency_change_value' => $this->decimal(18, 5),
                'invoice_key' => $this->string(),
                'invoice_activity_code' => $this->string(),
                'invoice_consecutive_number' => $this->string(),
                'invoice_date' => $this->string(),
                'user_id' => $this->integer(),
                'entry_id' => $this->integer(),
                'xml_file' => $this->string(),
                'created_at' => $this->dateTime(),
                'updated_at' => $this->dateTime(),
                'supplier_identification' => $this->string(),
                'supplier_identification_type' => $this->string(),
                'supplier_name' => $this->string(),
                'supplier_province_code' => $this->string(),
                'supplier_canton_code' => $this->string(),
                'supplier_district_code' => $this->string(),
                'supplier_barrio_code' => $this->string(),
                'supplier_other_signals' => $this->text(),
                'supplier_phone_country_code' => $this->string(),
                'supplier_phone' => $this->string(),
                'supplier_email' => $this->string(),
                'invoice_condition_sale_code' => $this->string(),
                'invoice_credit_time_code' => $this->string(),
                'invoice_payment_method_code' => $this->string(),
                'supplier_id' => $this->integer(),
                'branch_office_id' => $this->integer(),
            ],
            $tableOptions
        );

        $this->addForeignKey(
            'fk_xml_imported_entry1',
            '{{%xml_imported}}',
            ['entry_id'],
            '{{%entry}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_xml_imported_user1',
            '{{%xml_imported}}',
            ['user_id'],
            '{{%user}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_xml_imported_branch_office1',
            '{{%xml_imported}}',
            ['branch_office_id'],
            '{{%branch_office}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_xml_imported_supplier1',
            '{{%xml_imported}}',
            ['supplier_id'],
            '{{%supplier}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
    }

    public function down()
    {
        $this->dropTable('{{%xml_imported}}');
    }
}
