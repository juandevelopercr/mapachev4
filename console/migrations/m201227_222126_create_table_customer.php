<?php

use yii\db\Migration;

class m201227_222126_create_table_customer extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            '{{%customer}}',
            [
                'id' => $this->primaryKey(),
                'name' => $this->string(),
                'code' => $this->string(),
                'description' => $this->text(),
                'status' => $this->boolean()->defaultValue('1'),
                'created_at' => $this->dateTime(),
                'updated_at' => $this->dateTime(),
                'identification_type_id' => $this->integer(),
                'identification' => $this->string(),
                'foreign_identification' => $this->string(),
                'customer_type_id' => $this->integer(),
                'customer_classification_id' => $this->integer(),
                'country_code_phone' => $this->string(),
                'phone' => $this->string(),
                'country_code_fax' => $this->string(),
                'fax' => $this->string(),
                'email' => $this->string(),
                'province_id' => $this->integer(),
                'canton_id' => $this->integer(),
                'disctrict_id' => $this->integer(),
                'address' => $this->string(),
                'other_signs' => $this->string(),
                'condition_sale_id' => $this->integer(),
                'credit_amount_colon' => $this->decimal(15, 2),
                'credit_amount_usd' => $this->decimal(15, 2),
                'credit_days_id' => $this->integer(),
                'enable_credit_max' => $this->boolean(),
                'price_assigned' => $this->integer(),
                'collector_id' => $this->integer(),
                'is_exonerate' => $this->boolean(),
                'exoneration_document_type_id' => $this->integer(),
                'number_exoneration_doc' => $this->string(),
                'name_institution_exoneration' => $this->string(),
                'exoneration_date' => $this->date(),
                'exoneration_purchase_percent' => $this->decimal(5, 2),
            ],
            $tableOptions
        );

        $this->addForeignKey(
            'fk_customer_canton1',
            '{{%customer}}',
            ['canton_id'],
            '{{%canton}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_customer_collector1',
            '{{%customer}}',
            ['collector_id'],
            '{{%collector}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_customer_condition_sale1',
            '{{%customer}}',
            ['condition_sale_id'],
            '{{%condition_sale}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_customer_credit_days1',
            '{{%customer}}',
            ['credit_days_id'],
            '{{%credit_days}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_customer_customer_classification1',
            '{{%customer}}',
            ['customer_classification_id'],
            '{{%customer_classification}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_customer_customer_type1',
            '{{%customer}}',
            ['customer_type_id'],
            '{{%customer_type}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_customer_disctrict1',
            '{{%customer}}',
            ['disctrict_id'],
            '{{%disctrict}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_customer_exoneration_document_type1',
            '{{%customer}}',
            ['exoneration_document_type_id'],
            '{{%exoneration_document_type}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_customer_identification_type1',
            '{{%customer}}',
            ['identification_type_id'],
            '{{%identification_type}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_customer_province1',
            '{{%customer}}',
            ['province_id'],
            '{{%province}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
    }

    public function down()
    {
        $this->dropTable('{{%customer}}');
    }
}
