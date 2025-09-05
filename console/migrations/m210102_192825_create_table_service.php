<?php

use yii\db\Migration;

/**
 * Class m210102_192825_create_table_service
 */
class m210102_192825_create_table_service extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            '{{%service}}',
            [
                'id' => $this->primaryKey(),
                'code' => $this->string(),
                'cabys_id' => $this->integer(),
                'name' => $this->string(),
                'unit_type_id' => $this->integer(),
                'price' => $this->decimal(18,5),
                'discount_amount' => $this->decimal(18, 5),
                'nature_discount' => $this->string(),
                'tax_type_id' => $this->integer(),
                'tax_rate_type_id' => $this->integer(),
                'tax_rate_percent' => $this->decimal(5, 2),
                'tax_amount' => $this->decimal(18, 5),
                'exoneration_document_type_id' => $this->integer(),
                'number_exoneration_doc' => $this->string(),
                'name_institution_exoneration' => $this->string(),
                'exoneration_date' => $this->date(),
                'exoneration_purchase_percent' => $this->decimal(5, 2),
                'exonerated_tax_amount' => $this->decimal(18, 5),
                'created_at' => $this->dateTime(),
                'updated_at' => $this->dateTime(),
            ],
            $tableOptions
        );

        $this->addForeignKey(
            'fk_service_cabys1',
            '{{%service}}',
            ['cabys_id'],
            '{{%cabys}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_service_unit_type1',
            '{{%service}}',
            ['unit_type_id'],
            '{{%unit_type}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_service_exoneration_document_type1',
            '{{%service}}',
            ['exoneration_document_type_id'],
            '{{%exoneration_document_type}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_service_tax_rate_type1',
            '{{%service}}',
            ['tax_rate_type_id'],
            '{{%tax_rate_type}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_service_tax_type1',
            '{{%service}}',
            ['tax_type_id'],
            '{{%tax_type}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );

    }

    public function down()
    {
        $this->dropTable('{{%service}}');
    }
}
