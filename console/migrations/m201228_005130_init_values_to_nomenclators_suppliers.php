<?php

use yii\db\Migration;
use backend\models\nomenclators\CreditDays;
use backend\models\nomenclators\ConditionSale;
use backend\models\nomenclators\IdentificationType;
use backend\models\nomenclators\CustomerType;
use backend\models\nomenclators\CustomerClassification;
use backend\models\nomenclators\Province;
use backend\models\nomenclators\Canton;
use backend\models\nomenclators\Disctrict;
use backend\models\nomenclators\ExonerationDocumentType;
use common\models\GlobalFunctions;

/**
 * Class m201228_005130_init_values_to_nomenclators_suppliers
 */
class m201228_005130_init_values_to_nomenclators_suppliers extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        /* CREDIT DAYS */
        $array_credit_days = [8,15,22,30,45,60];
        foreach ($array_credit_days AS $days)
        {
            $model = new CreditDays(['name' => (string) $days]);
            if(!$model->save())
            {
                print_r($model->getErrors());
                return false;
            }
        }

        /* ConditionSale */
        $array_condition_sales = [
            ['01', 'Contado'],
            ['02', 'Crédito'],
            ['03', 'Consignación'],
            ['04', 'Apartado'],
            ['05', 'Arrendamiento con opción de compra'],
            ['06', 'Arrendamiento en función financiera'],
            ['99', 'Otros (se debe indicar la condición de la venta)'],
        ];
        foreach ($array_condition_sales AS $key=> $condition)
        {
            $model = new ConditionSale(['code' => $condition[0],'name' => $condition[1]]);
            if(!$model->save())
            {
                print_r($model->getErrors());
                return false;
            }
        }

        /* Identification types */
        $array_identification_types = [
            ['01', 'Cédula Física'],
            ['02', 'Cédula Jurídica'],
            ['03', 'DIMEX'],
            ['04', 'NITE'],
            ['05', 'Pasaporte'],
        ];
        foreach ($array_identification_types AS $key=> $condition)
        {
            $model = new IdentificationType(['code' => $condition[0],'name' => $condition[1]]);
            if(!$model->save())
            {
                print_r($model->getErrors());
                return false;
            }
        }

        /* Customer types */
        $array_customer_types = [
            ['01', 'Cliente General'],
            ['02', 'Cliente Exclusivo'],
            ['03', 'Cliente Corporativo'],
        ];
        foreach ($array_customer_types AS $key=> $condition)
        {
            $model = new CustomerType(['code' => $condition[0],'name' => $condition[1]]);
            if(!$model->save())
            {
                print_r($model->getErrors());
                return false;
            }
        }

        /* Customer classification */
        $array_customer_classifications = [
            ['01', 'A'],
            ['02', 'B'],
            ['03', 'C'],
        ];
        foreach ($array_customer_classifications AS $key=> $condition)
        {
            $model = new CustomerClassification(['code' => $condition[0],'name' => $condition[1]]);
            if(!$model->save())
            {
                print_r($model->getErrors());
                return false;
            }
        }


        /* Exonerate doc types */
        $array_exonerate_doc_types = [
            ['01', 'Compras autorizadas'],
            ['02', 'Ventas exentas a diplomáticos'],
            ['03', 'Autorizado por Ley especial'],
            ['04', 'Exenciones Dirección Generale Hacienda'],
            ['05', 'Transitorio V'],
            ['06', 'Transitorio IX'],
            ['07', 'Transitorio XVII'],
            ['99', 'Otros'],
        ];
        foreach ($array_exonerate_doc_types AS $key=> $condition)
        {
            $model = new ExonerationDocumentType(['code' => $condition[0],'name' => $condition[1]]);
            if(!$model->save())
            {
                print_r($model->getErrors());
                return false;
            }
        }

        /* Provinces y cantones */
        $array_provinces = [
            ['01', 'San José'],
            ['02', 'Alajuela'],
            ['03', 'Cartago'],
            ['04', 'Heredia'],
            ['05', 'Guanacaste'],
            ['06', 'Puntarenas'],
            ['07', 'Limón'],
        ];
        foreach ($array_provinces AS $key=> $condition)
        {
            $model = new Province(['code' => $condition[0],'name' => $condition[1]]);
            if(!$model->save())
            {
                print_r($model->getErrors());
                return false;
            }
            else
            {

                /* Cantones x provinces */
                $array_cantones = [];

                if($model->code === '01')
                {
                    $array_cantones = [
                        ['01', 'San José'],
                        ['02', 'Escazú'],
                        ['03', 'Desamparados'],
                        ['04', 'Puriscal'],
                        ['05', 'Tarrazú'],
                        ['06', 'Aserrí'],
                        ['07', 'Mora'],
                        ['08', 'Goicoechea'],
                        ['09', 'Santa Ana'],
                        ['10', 'Alajuelita'],
                        ['11', 'Vásquez de Coronado'],
                        ['12', 'Acosta'],
                        ['13', 'Tibás'],
                        ['14', 'Moravia'],
                        ['15', 'Montes de Oca'],
                        ['16', 'Turrubares'],
                        ['17', 'Dota'],
                        ['18', 'Curridabat'],
                        ['19', 'Pérez Zeledón'],
                        ['20', 'León Cortéz Castro'],
                    ];
                }
                elseif($model->code === '02')
                {
                    $array_cantones = [
                        ['01', 'Alajuela'],
                        ['02', 'San Ramón'],
                        ['03', 'Grecia'],
                        ['04', 'San Mateo'],
                        ['05', 'Atenas'],
                        ['06', 'Naranjo'],
                        ['07', 'Palmares'],
                        ['08', 'Poás'],
                        ['09', 'Orotina'],
                        ['10', 'San Carlos'],
                        ['11', 'Zarcero'],
                        ['12', 'Valverde Vega'],
                        ['13', 'Upala'],
                        ['14', 'Los Chiles'],
                        ['15', 'Guatuso'],
                    ];
                }
                elseif($model->code === '03')
                {
                    $array_cantones = [
                        ['01', 'Cartago'],
                        ['02', 'Paraíso'],
                        ['03', 'La Unión'],
                        ['04', 'Jiménez'],
                        ['05', 'Turrialba'],
                        ['06', 'Alvarado'],
                        ['07', 'Oreamuno'],
                        ['08', 'El Guarco'],
                    ];
                }
                elseif($model->code === '04')
                {
                    $array_cantones = [
                        ['01', 'Heredia'],
                        ['02', 'Barva'],
                        ['03', 'Santo Domingo'],
                        ['04', 'Santa Bárbara'],
                        ['05', 'San Rafaél'],
                        ['06', 'San Isidro'],
                        ['07', 'Belén'],
                        ['08', 'Flores'],
                        ['09', 'San Pablo'],
                        ['10', 'Sarapiquí'],
                    ];
                }
                elseif($model->code === '05')
                {
                    $array_cantones = [
                        ['01', 'Liberia'],
                        ['02', 'Nicoya'],
                        ['03', 'Santa Cruz'],
                        ['04', 'Bagaces'],
                        ['05', 'Carrillo'],
                        ['06', 'Cañas'],
                        ['07', 'Abangáres'],
                        ['08', 'Tilarán'],
                        ['09', 'Nandayure'],
                        ['10', 'La Cruz'],
                        ['11', 'Hojancha'],
                    ];
                }
                elseif($model->code === '06')
                {
                    $array_cantones = [
                        ['01', 'Puntarenas'],
                        ['02', 'Esparza'],
                        ['03', 'Buenos Aires'],
                        ['04', 'Montes de Oro'],
                        ['05', 'Osa'],
                        ['06', 'Aguirre'],
                        ['07', 'Golfito'],
                        ['08', 'Coto Brus'],
                        ['09', 'Parrita'],
                        ['10', 'Corredores'],
                        ['11', 'Garabito'],
                    ];
                }
                elseif($model->code === '07')
                {
                    $array_cantones = [
                        ['01', 'Limón'],
                        ['02', 'Pococí'],
                        ['03', 'Siquirres'],
                        ['04', 'Talamanca'],
                        ['05', 'Matina'],
                        ['06', 'Guácimo'],
                    ];
                }

                foreach ($array_cantones AS $index=> $value)
                {
                    $model_canton = new Canton(['code' => $value[0],'name' => $value[1],'province_id' => $model->id]);
                    if(!$model_canton->save())
                    {
                        print_r($model_canton->getErrors());
                        return false;
                    }
                }
            }
        }

        /* Distritos */
        $disctrict_array = [
            [1, 1, 1, 'CARMEN'],
            [2, 1, 2, 'MERCED'],
            [3, 1, 3, 'HOSPITAL'],
            [4, 1, 4, 'CATEDRAL'],
            [5, 1, 5, 'ZAPOTE'],
            [6, 1, 6, 'SAN FRANCISCO DE DOS RÍOS'],
            [7, 1, 7, 'URUCA'],
            [8, 1, 8, 'MATA REDONDA'],
            [9, 1, 9, 'PAVAS'],
            [10, 1, 10, 'HATILLO'],
            [11, 1, 11, 'SAN SEBASTIÁN'],
            [12, 2, 1, 'ESCAZÚ'],
            [13, 2, 2, 'SAN ANTONIO'],
            [14, 2, 3, 'SAN RAFAEL'],
            [15, 3, 1, 'DESAMPARADOS'],
            [16, 3, 2, 'SAN MIGUEL'],
            [17, 3, 3, 'SAN JUAN DE DIOS'],
            [18, 3, 4, 'SAN RAFAEL ARRIBA'],
            [19, 3, 5, 'SAN ANTONIO'],
            [20, 3, 6, 'FRAILES'],
            [21, 3, 7, 'PATARRÁ'],
            [22, 3, 8, 'SAN CRISTÓBAL'],
            [23, 3, 9, 'ROSARIO'],
            [24, 3, 10, 'DAMAS'],
            [25, 3, 11, 'SAN RAFAEL ABAJO'],
            [26, 3, 12, 'GRAVILIAS'],
            [27, 3, 13, 'LOS GUIDO'],
            [28, 4, 1, 'SANTIAGO'],
            [29, 4, 2, 'MERCEDES SUR'],
            [30, 4, 3, 'BARBACOAS'],
            [31, 4, 4, 'GRIFO ALTO'],
            [32, 4, 5, 'SAN RAFAEL'],
            [33, 4, 6, 'CANDELARITA'],
            [34, 4, 7, 'DESAMPARADITOS'],
            [35, 4, 8, 'SAN ANTONIO'],
            [36, 4, 9, 'CHIRES'],
            [37, 5, 1, 'SAN MARCOS'],
            [38, 5, 2, 'SAN LORENZO'],
            [39, 5, 3, 'SAN CARLOS'],
            [40, 6, 1, 'ASERRI'],
            [41, 6, 2, 'TARBACA'],
            [42, 6, 3, 'VUELTA DE JORCO'],
            [43, 6, 4, 'SAN GABRIEL'],
            [44, 6, 5, 'LEGUA'],
            [45, 6, 6, 'MONTERREY'],
            [46, 6, 7, 'SALITRILLOS'],
            [47, 7, 1, 'COLÓN'],
            [48, 7, 2, 'GUAYABO'],
            [49, 7, 3, 'TABARCIA'],
            [50, 7, 4, 'PIEDRAS NEGRAS'],
            [51, 7, 5, 'PICAGRES'],
            [52, 7, 6, 'JARIS'],
            [53, 7, 7, 'QUITIRRISI'],
            [54, 8, 1, 'GUADALUPE'],
            [55, 8, 2, 'SAN FRANCISCO'],
            [56, 8, 3, 'CALLE BLANCOS'],
            [57, 8, 4, 'MATA DE PLÁTANO'],
            [58, 8, 5, 'IPÍS'],
            [59, 8, 6, 'RANCHO REDONDO'],
            [60, 8, 7, 'PURRAL'],
            [61, 9, 1, 'SANTA ANA'],
            [62, 9, 2, 'SALITRAL'],
            [63, 9, 3, 'POZOS'],
            [64, 9, 4, 'URUCA'],
            [65, 9, 5, 'PIEDADES'],
            [66, 9, 6, 'BRASIL'],
            [67, 10, 1, 'ALAJUELITA'],
            [68, 10, 2, 'SAN JOSECITO'],
            [69, 10, 3, 'SAN ANTONIO'],
            [70, 10, 4, 'CONCEPCIÓN'],
            [71, 10, 5, 'SAN FELIPE'],
            [72, 11, 1, 'SAN ISIDRO'],
            [73, 11, 2, 'SAN RAFAEL'],
            [74, 11, 3, 'DULCE NOMBRE DE JESÚS'],
            [75, 11, 4, 'PATALILLO'],
            [76, 11, 5, 'CASCAJAL'],
            [77, 12, 1, 'SAN IGNACIO'],
            [78, 12, 2, 'GUAITIL Villa'],
            [79, 12, 3, 'PALMICHAL'],
            [80, 12, 4, 'CANGREJAL'],
            [81, 12, 5, 'SABANILLAS'],
            [82, 13, 1, 'SAN JUAN'],
            [83, 13, 2, 'CINCO ESQUINAS'],
            [84, 13, 3, 'ANSELMO LLORENTE'],
            [85, 13, 4, 'LEON XIII'],
            [86, 13, 5, 'COLIMA'],
            [87, 14, 1, 'SAN VICENTE'],
            [88, 14, 2, 'SAN JERÓNIMO'],
            [89, 14, 3, 'LA TRINIDAD'],
            [90, 15, 1, 'SAN PEDRO'],
            [91, 15, 2, 'SABANILLA'],
            [92, 15, 3, 'MERCEDES'],
            [93, 15, 4, 'SAN RAFAEL'],
            [94, 16, 1, 'SAN PABLO'],
            [95, 16, 2, 'SAN PEDRO'],
            [96, 16, 3, 'SAN JUAN DE MATA'],
            [97, 16, 4, 'SAN LUIS'],
            [98, 16, 5, 'CARARA'],
            [99, 17, 1, 'SANTA MARÍA'],
            [100, 17, 2, 'JARDÍN'],
            [101, 17, 3, 'COPEY'],
            [102, 18, 1, 'CURRIDABAT'],
            [103, 18, 2, 'GRANADILLA'],
            [104, 18, 3, 'SÁNCHEZ'],
            [105, 18, 4, 'TIRRASES'],
            [106, 19, 1, 'SAN ISIDRO DE EL GENERAL'],
            [107, 19, 2, 'EL GENERAL'],
            [108, 19, 3, 'DANIEL FLORES'],
            [109, 19, 4, 'RIVAS'],
            [110, 19, 5, 'SAN PEDRO'],
            [111, 19, 6, 'PLATANARES'],
            [112, 19, 7, 'PEJIBAYE'],
            [113, 19, 8, 'CAJÓN'],
            [114, 19, 9, 'BARÚ'],
            [115, 19, 10, 'RÍO NUEVO'],
            [116, 19, 11, 'PÁRAMO'],
            [117, 20, 1, 'SAN PABLO'],
            [118, 20, 2, 'SAN ANDRÉS'],
            [119, 20, 3, 'LLANO BONITO'],
            [120, 20, 4, 'SAN ISIDRO'],
            [121, 20, 5, 'SANTA CRUZ'],
            [122, 20, 6, 'SAN ANTONIO'],
            [123, 21, 1, 'ALAJUELA'],
            [124, 21, 2, 'SAN JOSÉ'],
            [125, 21, 3, 'CARRIZAL'],
            [126, 21, 4, 'SAN ANTONIO'],
            [127, 21, 5, 'GUÁCIMA'],
            [128, 21, 6, 'SAN ISIDRO'],
            [129, 21, 7, 'SABANILLA'],
            [130, 21, 8, 'SAN RAFAEL'],
            [131, 21, 9, 'RÍO SEGUNDO'],
            [132, 21, 10, 'DESAMPARADOS'],
            [133, 21, 11, 'TURRÚCARES'],
            [134, 21, 12, 'TAMBOR'],
            [135, 21, 13, 'GARITA'],
            [136, 21, 14, 'SARAPIQUÍ'],
            [137, 22, 1, 'SAN RAMÓN'],
            [138, 22, 2, 'SANTIAGO'],
            [139, 22, 3, 'SAN JUAN'],
            [140, 22, 4, 'PIEDADES NORTE'],
            [141, 22, 5, 'PIEDADES SUR'],
            [142, 22, 6, 'SAN RAFAEL'],
            [143, 22, 7, 'SAN ISIDRO'],
            [144, 22, 8, 'ÁNGELES'],
            [145, 22, 9, 'ALFARO'],
            [146, 22, 10, 'VOLIO'],
            [147, 22, 11, 'CONCEPCIÓN'],
            [148, 22, 12, 'ZAPOTAL'],
            [149, 22, 13, 'PEÑAS BLANCAS'],
            [150, 23, 1, 'GRECIA'],
            [151, 23, 2, 'SAN ISIDRO'],
            [152, 23, 3, 'SAN JOSÉ'],
            [153, 23, 4, 'SAN ROQUE'],
            [154, 23, 5, 'TACARES'],
            [155, 23, 6, 'RÍO CUARTO'],
            [156, 23, 7, 'PUENTE DE PIEDRA'],
            [157, 23, 8, 'BOLÍVAR'],
            [158, 24, 1, 'SAN MATEO'],
            [159, 24, 2, 'DESMONTE'],
            [160, 24, 3, 'JESÚS MARÍA'],
            [161, 24, 4, 'LABRADOR'],
            [162, 25, 1, 'ATENAS'],
            [163, 25, 2, 'JESÚS'],
            [164, 25, 3, 'MERCEDES'],
            [165, 25, 4, 'SAN ISIDRO'],
            [166, 25, 5, 'CONCEPCIÓN'],
            [167, 25, 6, 'SAN JOSE'],
            [168, 25, 7, 'SANTA EULALIA'],
            [169, 25, 8, 'ESCOBAL'],
            [170, 26, 1, 'NARANJO'],
            [171, 26, 2, 'SAN MIGUEL'],
            [172, 26, 3, 'SAN JOSÉ'],
            [173, 26, 4, 'CIRRÍ SUR'],
            [174, 26, 5, 'SAN JERÓNIMO'],
            [175, 26, 6, 'SAN JUAN'],
            [176, 26, 7, 'EL ROSARIO'],
            [177, 26, 8, 'PALMITOS'],
            [178, 27, 1, 'PALMARES'],
            [179, 27, 2, 'ZARAGOZA'],
            [180, 27, 3, 'BUENOS AIRES'],
            [181, 27, 4, 'SANTIAGO'],
            [182, 27, 5, 'CANDELARIA'],
            [183, 27, 6, 'ESQUÍPULAS'],
            [184, 27, 7, 'LA GRANJA'],
            [185, 28, 1, 'SAN PEDRO'],
            [186, 28, 2, 'SAN JUAN'],
            [187, 28, 3, 'SAN RAFAEL'],
            [188, 28, 4, 'CARRILLOS'],
            [189, 28, 5, 'SABANA REDONDA'],
            [190, 29, 1, 'OROTINA'],
            [191, 29, 2, 'EL MASTATE'],
            [192, 29, 3, 'HACIENDA VIEJA'],
            [193, 29, 4, 'COYOLAR'],
            [194, 29, 5, 'LA CEIBA'],
            [195, 30, 1, 'QUESADA'],
            [196, 30, 2, 'FLORENCIA'],
            [197, 30, 3, 'BUENAVISTA'],
            [198, 30, 4, 'AGUAS ZARCAS'],
            [199, 30, 5, 'VENECIA'],
            [200, 30, 6, 'PITAL'],
            [201, 30, 7, 'LA FORTUNA'],
            [202, 30, 8, 'LA TIGRA'],
            [203, 30, 9, 'LA PALMERA'],
            [204, 30, 10, 'VENADO'],
            [205, 30, 11, 'CUTRIS'],
            [206, 30, 12, 'MONTERREY'],
            [207, 30, 13, 'POCOSOL'],
            [208, 31, 1, 'ZARCERO'],
            [209, 31, 2, 'LAGUNA'],
            [210, 31, 4, 'GUADALUPE'],
            [211, 31, 5, 'PALMIRA'],
            [212, 31, 6, 'ZAPOTE'],
            [213, 31, 7, 'BRISAS'],
            [214, 32, 1, 'SARCHÍ NORTE'],
            [215, 32, 2, 'SARCHÍ SUR'],
            [216, 32, 3, 'TORO AMARILLO'],
            [217, 32, 4, 'SAN PEDRO'],
            [218, 32, 5, 'RODRÍGUEZ'],
            [219, 33, 1, 'UPALA'],
            [220, 33, 2, 'AGUAS CLARAS'],
            [221, 33, 3, 'SAN JOSÉ o PIZOTE'],
            [222, 33, 4, 'BIJAGUA'],
            [223, 33, 5, 'DELICIAS'],
            [224, 33, 6, 'DOS RÍOS'],
            [225, 33, 7, 'YOLILLAL'],
            [226, 33, 8, 'CANALETE'],
            [227, 34, 1, 'LOS CHILES'],
            [228, 34, 2, 'CAÑO NEGRO'],
            [229, 34, 3, 'EL AMPARO'],
            [230, 34, 4, 'SAN JORGE'],
            [231, 35, 2, 'BUENAVISTA'],
            [232, 35, 3, 'COTE'],
            [233, 35, 4, 'KATIRA'],
            [234, 36, 1, 'ORIENTAL'],
            [235, 36, 2, 'OCCIDENTAL'],
            [236, 36, 3, 'CARMEN'],
            [237, 36, 4, 'SAN NICOLÁS'],
            [238, 36, 5, 'AGUACALIENTE o SAN FRANCISCO'],
            [239, 36, 6, 'GUADALUPE o ARENILLA'],
            [240, 36, 7, 'CORRALILLO'],
            [241, 36, 8, 'TIERRA BLANCA'],
            [242, 36, 9, 'DULCE NOMBRE'],
            [243, 36, 10, 'LLANO GRANDE'],
            [244, 36, 11, 'QUEBRADILLA'],
            [245, 37, 1, 'PARAÍSO'],
            [246, 37, 2, 'SANTIAGO'],
            [247, 37, 3, 'OROSI'],
            [248, 37, 4, 'CACHÍ'],
            [249, 37, 5, 'LLANOS DE SANTA LUCÍA'],
            [250, 38, 1, 'TRES RÍOS'],
            [251, 38, 2, 'SAN DIEGO'],
            [252, 38, 3, 'SAN JUAN'],
            [253, 38, 4, 'SAN RAFAEL'],
            [254, 38, 5, 'CONCEPCIÓN'],
            [255, 38, 6, 'DULCE NOMBRE'],
            [256, 38, 7, 'SAN RAMÓN'],
            [257, 38, 8, 'RÍO AZUL'],
            [258, 39, 1, 'JUAN VIÑAS'],
            [259, 39, 2, 'TUCURRIQUE'],
            [260, 39, 3, 'PEJIBAYE'],
            [261, 40, 1, 'TURRIALBA'],
            [262, 40, 2, 'LA SUIZA'],
            [263, 40, 3, 'PERALTA'],
            [264, 40, 4, 'SANTA CRUZ'],
            [265, 40, 5, 'SANTA TERESITA'],
            [266, 40, 6, 'PAVONES'],
            [267, 40, 7, 'TUIS'],
            [268, 40, 8, 'TAYUTIC'],
            [269, 40, 9, 'SANTA ROSA'],
            [270, 40, 10, 'TRES EQUIS'],
            [271, 40, 11, 'LA ISABEL'],
            [272, 40, 12, 'CHIRRIPÓ'],
            [273, 41, 1, 'PACAYAS'],
            [274, 41, 2, 'CERVANTES'],
            [275, 41, 3, 'CAPELLADES'],
            [276, 42, 1, 'SAN RAFAEL'],
            [277, 42, 2, 'COT'],
            [278, 42, 3, 'POTRERO CERRADO'],
            [279, 42, 4, 'CIPRESES'],
            [280, 42, 5, 'SANTA ROSA'],
            [281, 43, 1, 'EL TEJAR'],
            [282, 43, 2, 'SAN ISIDRO'],
            [283, 43, 3, 'TOBOSI'],
            [284, 43, 4, 'PATIO DE AGUA'],
            [285, 44, 1, 'HEREDIA'],
            [286, 44, 2, 'MERCEDES'],
            [287, 44, 3, 'SAN FRANCISCO'],
            [288, 44, 4, 'ULLOA'],
            [289, 44, 5, 'VARABLANCA'],
            [290, 45, 1, 'BARVA'],
            [291, 45, 2, 'SAN PEDRO'],
            [292, 45, 3, 'SAN PABLO'],
            [293, 45, 4, 'SAN ROQUE'],
            [294, 45, 5, 'SANTA LUCÍA'],
            [295, 45, 6, 'SAN JOSÉ DE LA MONTAÑA'],
            [296, 46, 2, 'SAN VICENTE'],
            [297, 46, 3, 'SAN MIGUEL'],
            [298, 46, 4, 'PARACITO'],
            [299, 46, 5, 'SANTO TOMÁS'],
            [300, 46, 6, 'SANTA ROSA'],
            [301, 46, 7, 'TURES'],
            [302, 46, 8, 'PARÁ'],
            [303, 47, 1, 'SANTA BÁRBARA'],
            [304, 47, 2, 'SAN PEDRO'],
            [305, 47, 3, 'SAN JUAN'],
            [306, 47, 4, 'JESÚS'],
            [307, 47, 5, 'SANTO DOMINGO'],
            [308, 47, 6, 'PURABÁ'],
            [309, 48, 1, 'SAN RAFAEL'],
            [310, 48, 2, 'SAN JOSECITO'],
            [311, 48, 3, 'SANTIAGO'],
            [312, 48, 4, 'ÁNGELES'],
            [313, 48, 5, 'CONCEPCIÓN'],
            [314, 49, 1, 'SAN ISIDRO'],
            [315, 49, 2, 'SAN JOSÉ'],
            [316, 49, 3, 'CONCEPCIÓN'],
            [317, 49, 4, 'SAN FRANCISCO'],
            [318, 50, 1, 'SAN ANTONIO'],
            [319, 50, 2, 'LA RIBERA'],
            [320, 50, 3, 'LA ASUNCIÓN'],
            [321, 51, 1, 'SAN JOAQUÍN'],
            [322, 51, 2, 'BARRANTES'],
            [323, 51, 3, 'LLORENTE'],
            [324, 52, 1, 'SAN PABLO'],
            [325, 53, 1, 'PUERTO VIEJO'],
            [326, 53, 2, 'LA VIRGEN'],
            [327, 53, 3, 'LAS HORQUETAS'],
            [328, 53, 4, 'LLANURAS DEL GASPAR'],
            [329, 53, 5, 'CUREÑA'],
            [330, 54, 1, 'LIBERIA'],
            [331, 54, 2, 'CAÑAS DULCES'],
            [332, 54, 3, 'MAYORGA'],
            [333, 54, 4, 'NACASCOLO'],
            [334, 54, 5, 'CURUBANDÉ'],
            [335, 55, 1, 'NICOYA'],
            [336, 55, 2, 'MANSIÓN'],
            [337, 55, 3, 'SAN ANTONIO'],
            [338, 55, 4, 'QUEBRADA HONDA'],
            [339, 55, 5, 'SÁMARA'],
            [340, 55, 6, 'NOSARA'],
            [341, 55, 7, 'BELÉN DE NOSARITA'],
            [342, 56, 1, 'SANTA CRUZ'],
            [343, 56, 2, 'BOLSÓN'],
            [344, 56, 3, 'VEINTISIETE DE ABRIL'],
            [345, 56, 4, 'TEMPATE'],
            [346, 56, 5, 'CARTAGENA'],
            [347, 56, 6, 'CUAJINIQUIL'],
            [348, 56, 7, 'DIRIÁ'],
            [349, 56, 8, 'CABO VELAS'],
            [350, 56, 9, 'TAMARINDO'],
            [351, 57, 1, 'BAGACES'],
            [352, 57, 2, 'LA FORTUNA'],
            [353, 57, 3, 'MOGOTE'],
            [354, 57, 4, 'RÍO NARANJO'],
            [355, 58, 1, 'FILADELFIA'],
            [356, 58, 2, 'PALMIRA'],
            [357, 58, 3, 'SARDINAL'],
            [358, 58, 4, 'BELÉN'],
            [359, 59, 1, 'CAÑAS'],
            [360, 59, 2, 'PALMIRA'],
            [361, 59, 3, 'SAN MIGUEL'],
            [362, 59, 4, 'BEBEDERO'],
            [363, 59, 5, 'POROZAL'],
            [364, 60, 1, 'LAS JUNTAS'],
            [365, 60, 2, 'SIERRA'],
            [366, 60, 3, 'SAN JUAN'],
            [367, 60, 4, 'COLORADO'],
            [368, 61, 1, 'TILARÁN'],
            [369, 61, 2, 'QUEBRADA GRANDE'],
            [370, 61, 3, 'TRONADORA'],
            [371, 61, 4, 'SANTA ROSA'],
            [372, 61, 5, 'LÍBANO'],
            [373, 61, 6, 'TIERRAS MORENAS'],
            [374, 61, 7, 'ARENAL'],
            [375, 62, 1, 'CARMONA'],
            [376, 62, 2, 'SANTA RITA'],
            [377, 62, 3, 'ZAPOTAL'],
            [378, 62, 4, 'SAN PABLO'],
            [379, 62, 5, 'PORVENIR'],
            [380, 62, 6, 'BEJUCO'],
            [381, 63, 1, 'LA CRUZ'],
            [382, 63, 2, 'SANTA CECILIA'],
            [383, 63, 3, 'LA GARITA'],
            [384, 63, 4, 'SANTA ELENA'],
            [385, 64, 1, 'HOJANCHA'],
            [386, 64, 2, 'MONTE ROMO'],
            [387, 64, 3, 'PUERTO CARRILLO'],
            [388, 64, 4, 'HUACAS'],
            [389, 65, 1, 'PUNTARENAS'],
            [390, 65, 2, 'PITAHAYA'],
            [391, 65, 3, 'CHOMES'],
            [392, 65, 4, 'LEPANTO'],
            [393, 65, 5, 'PAQUERA'],
            [394, 65, 6, 'MANZANILLO'],
            [395, 65, 7, 'GUACIMAL'],
            [396, 65, 8, 'BARRANCA'],
            [397, 65, 9, 'MONTE VERDE'],
            [398, 65, 11, 'CÓBANO'],
            [399, 65, 12, 'CHACARITA'],
            [400, 65, 13, 'CHIRA'],
            [401, 65, 14, 'ACAPULCO'],
            [402, 65, 15, 'EL ROBLE'],
            [403, 65, 16, 'ARANCIBIA'],
            [404, 66, 1, 'ESPÍRITU SANTO'],
            [405, 66, 2, 'SAN JUAN GRANDE'],
            [406, 66, 3, 'MACACONA'],
            [407, 66, 4, 'SAN RAFAEL'],
            [408, 66, 5, 'SAN JERÓNIMO'],
            [409, 66, 6, 'CALDERA'],
            [410, 67, 1, 'BUENOS AIRES'],
            [411, 67, 2, 'VOLCÁN'],
            [412, 67, 3, 'POTRERO GRANDE'],
            [413, 67, 4, 'BORUCA'],
            [414, 67, 5, 'PILAS'],
            [415, 67, 6, 'COLINAS'],
            [416, 67, 7, 'CHÁNGUENA'],
            [417, 67, 8, 'BIOLLEY'],
            [418, 67, 9, 'BRUNKA'],
            [419, 68, 1, 'MIRAMAR'],
            [420, 68, 2, 'LA UNIÓN'],
            [421, 68, 3, 'SAN ISIDRO'],
            [422, 69, 1, 'PUERTO CORTÉS'],
            [423, 69, 2, 'PALMAR'],
            [424, 69, 3, 'SIERPE'],
            [425, 69, 4, 'BAHÍA BALLENA'],
            [426, 69, 5, 'PIEDRAS BLANCAS'],
            [427, 69, 6, 'BAHÍA DRAKE'],
            [428, 70, 1, 'QUEPOS'],
            [429, 70, 2, 'SAVEGRE'],
            [430, 70, 3, 'NARANJITO'],
            [431, 71, 1, 'GOLFITO'],
            [432, 71, 2, 'PUERTO JIMÉNEZ'],
            [433, 71, 3, 'GUAYCARÁ'],
            [434, 71, 4, 'PAVÓN'],
            [435, 72, 1, 'SAN VITO'],
            [436, 72, 2, 'SABALITO'],
            [437, 72, 3, 'AGUABUENA'],
            [438, 72, 4, 'LIMONCITO'],
            [439, 72, 5, 'PITTIER'],
            [440, 72, 6, 'GUTIERREZ BRAUN'],
            [441, 73, 1, 'PARRITA'],
            [442, 74, 1, 'CORREDOR'],
            [443, 74, 2, 'LA CUESTA'],
            [444, 74, 3, 'CANOAS'],
            [445, 74, 4, 'LAUREL'],
            [446, 75, 1, 'JACÓ'],
            [447, 75, 2, 'TÁRCOLES'],
            [448, 76, 1, 'LIMÓN'],
            [449, 76, 2, 'VALLE LA ESTRELLA'],
            [450, 76, 4, 'MATAMA'],
            [451, 77, 1, 'GUÁPILES'],
            [452, 77, 2, 'JIMÉNEZ'],
            [453, 77, 3, 'RITA'],
            [454, 77, 4, 'ROXANA'],
            [455, 77, 5, 'CARIARI'],
            [456, 77, 6, 'COLORADO'],
            [457, 77, 7, 'LA COLONIA'],
            [458, 78, 1, 'SIQUIRRES'],
            [459, 78, 2, 'PACUARITO'],
            [460, 78, 3, 'FLORIDA'],
            [461, 78, 4, 'GERMANIA'],
            [462, 78, 5, 'EL CAIRO'],
            [463, 78, 6, 'ALEGRÍA'],
            [464, 79, 1, 'BRATSI'],
            [465, 79, 2, 'SIXAOLA'],
            [466, 79, 3, 'CAHUITA'],
            [467, 79, 4, 'TELIRE'],
            [468, 80, 1, 'MATINA'],
            [469, 80, 2, 'BATÁN'],
            [470, 80, 3, 'CARRANDI'],
            [471, 81, 1, 'GUÁCIMO'],
            [472, 81, 2, 'MERCEDES'],
            [473, 81, 3, 'POCORA'],
            [474, 81, 4, 'RÍO JIMÉNEZ'],
            [475, 81, 5, 'DUACARÍ'],
        ];
        $province_1 = Province::find()->where(['code' => '01'])->one();
        $province_2 = Province::find()->where(['code' => '02'])->one();
        $province_3 = Province::find()->where(['code' => '03'])->one();
        $province_4 = Province::find()->where(['code' => '04'])->one();
        $province_5 = Province::find()->where(['code' => '05'])->one();
        $province_6 = Province::find()->where(['code' => '06'])->one();
        $province_7 = Province::find()->where(['code' => '07'])->one();
        foreach ($disctrict_array AS $index => $disctrict)
        {
            if($disctrict[1] <= 20)  //P1
            {
                $code_canton = GlobalFunctions::zeroFill($disctrict[1],2);
                $canton = Canton::find()->where(['province_id' => $province_1->id ,'code' => $code_canton])->one();

                $code_disct = GlobalFunctions::zeroFill($disctrict[2],2);
                $dist = new Disctrict(['canton_id' => $canton->id,'code' => $code_disct, 'name' => $disctrict[3]]);
                $dist->save();
            }
            elseif($disctrict[1] > 20 && $disctrict[1] <= 35)  //P2
            {
                $temp_code = $disctrict[1] - 20;
                $code_canton = GlobalFunctions::zeroFill($temp_code,2);
                $canton = Canton::find()->where(['province_id' => $province_2->id ,'code' => $code_canton])->one();

                $code_disct = GlobalFunctions::zeroFill($disctrict[2],2);
                $dist = new Disctrict(['canton_id' => $canton->id,'code' => $code_disct, 'name' => $disctrict[3]]);
                $dist->save();
            }
            elseif($disctrict[1] > 35 && $disctrict[1] <= 43)  //P3
            {
                $temp_code = $disctrict[1] - 35;
                $code_canton = GlobalFunctions::zeroFill($temp_code,2);
                $canton = Canton::find()->where(['province_id' => $province_3->id ,'code' => $code_canton])->one();

                $code_disct = GlobalFunctions::zeroFill($disctrict[2],2);
                $dist = new Disctrict(['canton_id' => $canton->id,'code' => $code_disct, 'name' => $disctrict[3]]);
                $dist->save();
            }
            elseif($disctrict[1] > 43 && $disctrict[1] <= 53)  //P4
            {
                $temp_code = $disctrict[1] - 43;
                $code_canton = GlobalFunctions::zeroFill($temp_code,2);
                $canton = Canton::find()->where(['province_id' => $province_4->id ,'code' => $code_canton])->one();

                $code_disct = GlobalFunctions::zeroFill($disctrict[2],2);
                $dist = new Disctrict(['canton_id' => $canton->id,'code' => $code_disct, 'name' => $disctrict[3]]);
                $dist->save();
            }
            elseif($disctrict[1] > 53 && $disctrict[1] <= 64)  //P5
            {
                $temp_code = $disctrict[1] - 53;
                $code_canton = GlobalFunctions::zeroFill($temp_code,2);
                $canton = Canton::find()->where(['province_id' => $province_5->id ,'code' => $code_canton])->one();

                $code_disct = GlobalFunctions::zeroFill($disctrict[2],2);
                $dist = new Disctrict(['canton_id' => $canton->id,'code' => $code_disct, 'name' => $disctrict[3]]);
                $dist->save();
            }
            elseif($disctrict[1] > 64 && $disctrict[1] <= 75)  //P6
            {
                $temp_code = $disctrict[1] - 64;
                $code_canton = GlobalFunctions::zeroFill($temp_code,2);
                $canton = Canton::find()->where(['province_id' => $province_6->id ,'code' => $code_canton])->one();

                $code_disct = GlobalFunctions::zeroFill($disctrict[2],2);
                $dist = new Disctrict(['canton_id' => $canton->id,'code' => $code_disct, 'name' => $disctrict[3]]);
                $dist->save();
            }
            elseif($disctrict[1] > 75 && $disctrict[1] <= 81)  //P7
            {
                $temp_code = $disctrict[1] - 75;
                $code_canton = GlobalFunctions::zeroFill($temp_code,2);
                $canton = Canton::find()->where(['province_id' => $province_7->id ,'code' => $code_canton])->one();

                $code_disct = GlobalFunctions::zeroFill($disctrict[2],2);
                $dist = new Disctrict(['canton_id' => $canton->id,'code' => $code_disct, 'name' => $disctrict[3]]);
                $dist->save();
            }
        }

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        CreditDays::deleteAll();
        ConditionSale::deleteAll();
        IdentificationType::deleteAll();
        CustomerType::deleteAll();
        CustomerClassification::deleteAll();
        Disctrict::deleteAll();
        Canton::deleteAll();
        Province::deleteAll();
        ExonerationDocumentType::deleteAll();
    }

}
