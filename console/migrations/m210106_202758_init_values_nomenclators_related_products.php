<?php

use yii\db\Migration;
use backend\models\nomenclators\Family;
use backend\models\nomenclators\Category;
use backend\models\nomenclators\InventoryType;

/**
 * Class m210106_202758_init_values_nomenclators_related_products
 */
class m210106_202758_init_values_nomenclators_related_products extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        /* Family */
        $array_family = [
            array('01', 'ACEITES'),
            array('02', 'SUMINISTROS OFICINA'),
            array('03', 'SERVICIOS PROFESIONALES'),
            array('04', 'HERRAMIENTAS'),
            array('05', 'SERVICIOS MÉDICOS'),
            array('06', 'SALUD OCUPACIONAL'),
            array('07', 'CONSUMIBLES'),
            array('08', 'MATERIALES'),
            array('09', 'TRATAMIENTOS TERMICOS'),
            array('10', 'GASTOS TAGOSA'),
            array('11', 'ELECTRICIDAD'),
            array('12', 'SERVICIOS SUBCONTRATADOS'),
        ];
        foreach ($array_family AS $family)
        {
            $model = new Family(['status' => 1,'code' => $family[0], 'name' => $family[1]]);
            if(!$model->save())
            {
                echo 'ERROR Family';
                print_r($model->getErrors());
                return false;
            }
            else
            {
                /* Categories x Family */
                if($model->code == '01')
                {
                    $array_category = [
                        array('01', 'ACEITE HIDRAULICO'),
                        array('02', 'ACEITE SOLUBLE'),
                        array('03', 'ACEITE DE MOTOR'),
                    ];
                }
                elseif($model->code == '02')
                {
                    $array_category = [
                        array('01', 'TINTAS'),
                        array('02', 'LLAVE MAYA'),
                        array('03', 'VARIOS'),
                    ];
                }
                elseif($model->code == '03')
                {
                    $array_category = [
                        array('01', 'ABOGADO'),
                        array('02', 'SERVICIO DE ENVIO'),
                        array('03', 'SERVICIOS PROFESIONALES'),
                        array('04', 'SERVICIOS DE RESTAURANTE'),
                    ];
                }
                elseif($model->code == '04')
                {
                    $array_category = [
                        array('01', 'BROCA DE CARBIDE RECTA'),
                        array('02', 'BROCA CARBIDE'),
                        array('03', 'FRESA CARBIDE PLANA'),
                        array('04', 'BROCA HSS'),
                        array('05', 'BOQUILLAS'),
                        array('06', 'TORNILLOS PARA PORTAS'),
                        array('07', 'MACHOS DE MAQUINA'),
                        array('08', 'SPOTT DRILL'),
                        array('09', 'PULLSTUD'),
                        array('10', 'PULLSTUD'),
                        array('11', 'CALZAS PARA TORNO'),
                        array('12', 'CALZAS PARA FRESA'),
                        array('13', 'MACHOS MANUALES'),
                        array('14', 'GENERAL'),
                        array('15', 'POLVO PARA TERMOROCIADO'),
                        array('16', 'MANERAL PARA DADOS'),
                    ];
                }
                elseif($model->code == '05')
                {
                    $array_category = [
                        array('01', 'MEDICAMENTOS VARIOS'),
                        array('02', 'LABORATORIO'),
                        array('03', 'CONSULTA MEDICA'),
                    ];
                }
                elseif($model->code == '06')
                {
                    $array_category = [
                        array('01', 'RECARGA Y REVISIONES DE EXTINTORES'),
                        array('02', 'TABLAS DISTINTIVAS DE SEGURIDAD'),
                    ];
                }
                elseif($model->code == '07')
                {
                    $array_category = [
                        array('01', 'PISTOLA PARA LIMPIEZA DE MOTORES'),
                        array('02', 'MANGUERAS DE AIRE'),
                        array('03', 'PISTOLA DE AIRE'),
                        array('04', 'PINTURA'),
                        array('05', 'LOCTITE SILICON'),
                        array('06', 'LOCTITE'),
                        array('07', 'TORNILLERIA'),
                        array('08', 'ALCOHOL'),
                        array('09', 'DISCO DE ESMERILAR'),
                        array('10', 'DISCO DE CORTE'),
                        array('11', 'ACOPPLES RAPIDO HEMBRA'),
                        array('12', 'ACOPLES RAPIDO MACHO'),
                        array('13', 'CINTAS'),
                        array('14', 'LIMPIEZA'),
                        array('15', 'INFLAMABLES'),
                        array('16', 'VARIOS'),
                        array('17', 'EMPAQUE'),
                    ];
                }
                elseif($model->code == '08')
                {
                    $array_category = [
                        array('01', 'BRONCE'),
                        array('02', 'TUBO DE ACERO INOXIDABLE'),
                        array('03', 'BARRA ACERO 4140'),
                        array('04', 'PLATINA DE ALUMINIO'),
                        array('05', 'BARRA DE ACERO INOXIDABLE'),
                        array('06', 'BARRA DE TEFLON'),
                        array('07', 'BARRA CUADRADA INOXIDABLE'),
                        array('08', 'CUÑA'),
                        array('09', 'PLACA DE HIERRO NEGRO'),
                        array('10', 'BOHLER'),
                        array('11', 'BARRA NYLON'),
                        array('12', 'BARRA CALIBRADA'),
                        array('13', 'BARRA ALUMINIO'),
                        array('14', 'BARRA ERTALYTE'),
                        array('15', 'LAMINA DE ALUMINIO LISO'),
                    ];
                }
                elseif($model->code == '09')
                {
                    $array_category = [
                        array('01', 'TEMPLE'),
                        array('02', 'ANODIZADOS'),
                        array('03', 'CROMADO'),
                    ];
                }
                elseif($model->code == '10')
                {
                    $array_category = [
                        array('01', 'PÓLIZAS Y SEGUROS'),
                        array('02', 'MATERIALES DE CONSTRUCCIÓN'),
                        array('03', 'CUMPLEAÑOS'),
                        array('04', 'SUMINISTROS DE LIMPIEZA'),
                        array('05', 'CONSUMIBLES OFICINA- PLANTA'),
                        array('06', 'SEGUROS'),
                        array('07', 'ELECTRICIDAD'),
                        array('08', 'AGUA'),
                        array('09', 'CABLE E INTERNET'),
                        array('10', 'VEHICULO'),
                        array('11', 'FIDEICOMISO'),
                        array('12', 'PRESTAMOS'),
                        array('13', 'TELEFONIA'),
                        array('14', 'MENSUALIDADES'),
                    ];
                }
                elseif($model->code == '11')
                {
                    $array_category = [
                        array('01', 'CABLES'),
                        array('02', 'CENTROS DE CARGA'),
                        array('03', 'ENCHUFES'),
                    ];
                }
                elseif($model->code == '12')
                {
                    $array_category = [
                        array('01', 'VARIOS'),
                    ];
                }

                foreach ($array_category AS $category)
                {
                    $model_category = new Category(['status' => 1,'code' => $category[0], 'name' => $category[1],'family_id' => $model->id]);
                    if(!$model_category->save())
                    {
                        echo 'ERROR Category';
                        print_r($model_category->getErrors());
                        return false;
                    }
                }
            }
        }

        /* Inventory type */
        $array_inventory = [
            array('01', 'Materiales'),
            array('02', 'Bodega Interna'),
            array('03', 'GASTOS GENERALES'),
            array('04', 'PRODUCCION')
        ];

        foreach ($array_inventory AS $inventory)
        {
            $model_category = new InventoryType(['status' => 1,'code' => $inventory[0], 'name' => $inventory[1]]);
            if(!$model_category->save())
            {
                echo 'ERROR Inventory';
                print_r($model_category->getErrors());
                return false;
            }
        }

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        Family::deleteAll();
        Category::deleteAll();
        InventoryType::deleteAll();
    }
}
