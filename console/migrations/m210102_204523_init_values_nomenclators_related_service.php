<?php

use yii\db\Migration;
use backend\models\nomenclators\UnitType;
use backend\models\nomenclators\TaxType;
use backend\models\nomenclators\TaxRateType;

/**
 * Class m210102_204523_init_values_nomenclators_related_service
 */
class m210102_204523_init_values_nomenclators_related_service extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        /* UnitType() */
        $array_unit = [
            [1, 'Sp', 'Servicios Profesionales'],
            [2, 'm', 'Metro'],
            [3, 'kg', 'Kilogramo'],
            [4, 's', 'Segundo'],
            [5, 'A', 'Ampere'],
            [6, 'mol', 'Mol'],
            [7, 'cd', 'Candela'],
            [8, 'm²', 'metro cuadrado'],
            [9, 'm³', 'metro cúbico'],
            [10, 'm/s', 'metro por segundo'],
            [11, 'm/s²', 'metro por segundo cuadrado'],
            [12, '1/m', '1 por metro'],
            [13, 'kg/m³', 'kilogramo por metro cúbico'],
            [14, 'A/m²', 'ampere por metro cuadrado'],
            [15, 'A/m', 'ampere por metro'],
            [16, 'mol/m³', 'mol por metro cúbico'],
            [17, 'cd/m²', 'candela por metro cuadrado'],
            [18, '1', 'uno (indice de refracción)'],
            [19, 'rad', 'radián'],
            [20, 'sr', 'estereorradián'],
            [21, 'Hz', 'hertz'],
            [22, 'N', 'newton'],
            [23, 'Pa', 'pascal'],
            [24, 'J', 'Joule'],
            [25, 'W', 'Watt'],
            [26, 'C', 'coulomb'],
            [27, 'V', 'volt'],
            [28, 'F', 'farad'],
            [29, '?', 'ohm'],
            [30, 'S', 'siemens'],
            [31, 'Wb', 'weber'],
            [32, 'T', 'tesla'],
            [33, 'H', 'henry'],
            [34, '°C', 'grado Celsius'],
            [35, 'lm', 'lumen'],
            [36, 'lx', 'lux'],
            [37, 'Bq', 'Becquerel'],
            [38, 'Gy', 'gray'],
            [39, 'Sv', 'sievert'],
            [40, 'kat', 'katal'],
            [41, 'Pa·s', 'pascal segundo'],
            [42, 'N·m', 'newton metro'],
            [43, 'N/m', 'newton por metro'],
            [44, 'rad/s', 'radián por segundo'],
            [45, 'rad/s²', 'radián por segundo cuadrado'],
            [46, 'W/m²', 'watt por metro cuadrado'],
            [47, 'J/K', 'joule por kelvin'],
            [48, 'J/(kg·K)', 'joule por kilogramo kelvin'],
            [49, 'J/kg', 'joule por kilogramo'],
            [50, 'W/(m·K)', 'watt por metro kevin'],
            [51, 'J/m³', 'joule por metro cúbico'],
            [52, 'V/m', 'volt por metro'],
            [53, 'C/m³', 'coulomb por metro cúbico'],
            [54, 'C/m²', 'coulomb por metro cuadrado'],
            [55, 'F/m', 'farad por metro'],
            [56, 'H/m', 'henry por metro'],
            [57, 'J/mol', 'joule por mol'],
            [58, 'J/(mol·K)', 'joule por mol kelvin'],
            [59, 'C/kg', 'coulomb por kilogramo'],
            [60, 'Gy/s', 'gray por segundo'],
            [61, 'W/sr', 'watt por estereorradián'],
            [62, 'W/(m²·sr)', 'watt por metro cuadrado estereorradián'],
            [63, 'kat/m³', 'katal por metro cúbico'],
            [64, 'min', 'minuto'],
            [65, 'h', 'hora'],
            [66, 'd', 'día'],
            [67, 'º', 'grado'],
            [68, 'min', 'minuto'],
            [69, 's', 'segundo'],
            [70, 'L', 'litro'],
            [71, 't', 'tonelada'],
            [72, 'Np', 'neper'],
            [73, 'B', 'bel'],
            [74, 'eV', 'electronvolt'],
            [75, 'u', 'unidad de masa atómica unificada'],
            [76, 'ua', 'unidad astronómica'],
            [77, 'Unid', 'Unidad'],
            [78, 'Gal', 'Galón'],
            [79, 'g', 'Gramo'],
            [80, 'km', 'Kilometro'],
            [81, 'ln', 'pulgada'],
            [82, 'cm', 'centímetro'],
            [83, 'mL', 'mililitro'],
            [84, 'mm', 'Milímetro'],
            [85, 'Oz', 'Onzas'],
            [86, 'Otros', 'Se debe indicar la descripción de la medida a utilizar'],
        ];
        foreach ($array_unit AS $unit)
        {
            $model = new UnitType(['status' => 1,'code' => $unit[1], 'name' => $unit[2]]);
            if(!$model->save())
            {
                print_r($model->getErrors());
                return false;
            }
        }

        /* TaxType() */
        $array_taxtype = [
            [1, '01', 'Impuesto al Valor Agregado'],
            [2, '02', 'Impuesto Selectivo de Consumo'],
            [3, '03', 'Impuesto Único a los Combustibles'],
            [4, '04', 'Impuesto específico de Bebidas Alcohólicas'],
            [5, '05', 'Impuesto Específico sobre las bebidas envasadas sin contenido alcohólico y jabones de tocador'],
            [6, '06', 'Impuesto a los Productos de Tabaco'],
            [7, '07', 'IVA (cálculo especial)'],
            [8, '08', 'IVA Régimen de Bienes Usados (Factor)'],
            [9, '09', 'Impuesto Específico al Cemento'],
            [10, '99', 'Otros'],
        ];
        foreach ($array_taxtype AS $tax)
        {
            $model = new TaxType(['status' => 1,'code' => $tax[1], 'name' => $tax[2]]);
            if(!$model->save())
            {
                print_r($model->getErrors());
                return false;
            }
        }

        /* TaxRateType() */
        $array_tax_rate_type = [
            [1, '01', 0, 'Tarifa 0% (Exento)'],
            [2, '02', 1, 'Tarifa reducida 1%'],
            [3, '03', 2, 'Tarifa reducida 2%'],
            [4, '04', 4, 'Tarifa reducida 4%'],
            [5, '05', 0, 'Transitorio 0%'],
            [6, '06', 4, 'Transitorio 4%'],
            [7, '07', 8, 'Transitorio 8%'],
            [8, '08', 13, 'Tarifa general 13%'],
        ];
        foreach ($array_tax_rate_type AS $tax_rate)
        {
            $model = new TaxRateType(['status' => 1,'code' => $tax_rate[1], 'percent' => $tax_rate[2], 'name' => $tax_rate[3]]);
            if(!$model->save())
            {
                print_r($model->getErrors());
                return false;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        UnitType::deleteAll();
        TaxRateType::deleteAll();
        TaxType::deleteAll();
    }


}
