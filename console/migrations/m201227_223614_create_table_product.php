<?php

use yii\db\Migration;

class m201227_223614_create_table_product extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            '{{%product}}',
            [
                'id' => $this->primaryKey(),
                'code' => $this->string(),
                'image' => $this->string(),
                'description' => $this->string(),
                'entry_date' => $this->date(),
                'bar_code' => $this->string(),
                'cabys_id' => $this->integer(),
                'family_id' => $this->integer(),
                'category_id' => $this->integer(),
                'unit_type_id' => $this->integer(),
                'branch_office_id' => $this->integer(),
                'supplier_id' => $this->integer(),
                'inventory_type_id' => $this->integer(),
                'location' => $this->string(),
                'branch' => $this->string(),
                'initial_existence' => $this->decimal(16, 3),
                'min_quantity' => $this->decimal(16, 3),
                'max_quantity' => $this->decimal(16, 3),
                'package_quantity' => $this->decimal(16, 3),
                'price' => $this->decimal(15, 2),
                'percent1' => $this->decimal(5, 2),
                'price1' => $this->decimal(15, 2),
                'percent2' => $this->decimal(5, 2),
                'price2' => $this->decimal(15, 2),
                'percent3' => $this->decimal(5, 2),
                'price3' => $this->decimal(15, 2),
                'percent4' => $this->decimal(5, 2),
                'price4' => $this->decimal(15, 2),
                'percent_detail' => $this->decimal(5, 2),
                'price_detail' => $this->decimal(15, 2),
                'price_custom' => $this->decimal(15, 2),
                'discount_amount' => $this->decimal(18, 5),
                'nature_discount' => $this->string(),
                'tax_type_id' => $this->integer(),
                'tax_rate_type_id' => $this->integer(),
                'tax_rate_percent' => $this->decimal(5, 2),
                'exoneration_document_type_id' => $this->integer(),
                'number_exoneration_doc' => $this->string(),
                'name_institution_exoneration' => $this->string(),
                'exoneration_date' => $this->date(),
                'exoneration_purchase_percent' => $this->decimal(5, 2),
                'created_at' => $this->dateTime(),
                'updated_at' => $this->dateTime(),
            ],
            $tableOptions
        );

        $this->addForeignKey(
            'fk_product_inventory_type1',
            '{{%product}}',
            ['inventory_type_id'],
            '{{%inventory_type}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_product_supplier1',
            '{{%product}}',
            ['supplier_id'],
            '{{%supplier}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_product_tax_rate_type1',
            '{{%product}}',
            ['tax_rate_type_id'],
            '{{%tax_rate_type}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_product_tax_type1',
            '{{%product}}',
            ['tax_type_id'],
            '{{%tax_type}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_product_unit_type1',
            '{{%product}}',
            ['unit_type_id'],
            '{{%unit_type}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_product_branch_office1',
            '{{%product}}',
            ['branch_office_id'],
            '{{%branch_office}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_product_cabys1',
            '{{%product}}',
            ['cabys_id'],
            '{{%cabys}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_product_category1',
            '{{%product}}',
            ['category_id'],
            '{{%category}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_product_exoneration_document_type1',
            '{{%product}}',
            ['exoneration_document_type_id'],
            '{{%exoneration_document_type}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_product_family1',
            '{{%product}}',
            ['family_id'],
            '{{%family}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
    }

    public function down()
    {
        $this->dropTable('{{%product}}');
    }
}
