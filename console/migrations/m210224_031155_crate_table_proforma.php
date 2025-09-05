<?php

use yii\db\Migration;

/**
 * Class m210224_031155_crate_table_proforma
 */
class m210224_031155_crate_table_proforma extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            '{{%proforma}}',
            [
                'id' => $this->primaryKey(),
                'consecutive' => $this->string(),
                'branch_office_id' => $this->integer(),
                'customer_id' => $this->integer(),
                'credit_days_id' => $this->integer(),
                'condition_sale_id' => $this->integer(),
                'request_date' => $this->date(),
                'change_type' => $this->decimal(18,5),
                'currency_id' => $this->integer(),
                'status' => $this->integer(),
                'delivery_time' => $this->string(),
                'delivery_time_type' => $this->integer(),
                'discount_percent' => $this->decimal(18,5),
                'seller_id' => $this->integer(),
                'observations' => $this->text(),
                'is_editable' => $this->boolean(),
                'created_at' => $this->dateTime(),
                'updated_at' => $this->dateTime(),
            ],
            $tableOptions
        );

        $this->addForeignKey(
            'fk_proforma_branch_office1',
            '{{%proforma}}',
            ['branch_office_id'],
            '{{%branch_office}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_proforma_customer1',
            '{{%proforma}}',
            ['customer_id'],
            '{{%customer}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_proforma_condition_sale1',
            '{{%proforma}}',
            ['condition_sale_id'],
            '{{%condition_sale}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_proforma_credit_days',
            '{{%proforma}}',
            ['credit_days_id'],
            '{{%credit_days}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_proforma_currency',
            '{{%proforma}}',
            ['currency_id'],
            '{{%currency}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_proforma_seller',
            '{{%proforma}}',
            ['seller_id'],
            '{{%seller}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );

    }

    public function down()
    {
        $this->dropTable('{{%proforma}}');
    }
}
