<?php

namespace backend\controllers;

use Yii;
use common\models\GlobalFunctions;
use common\models\User;
use yii\helpers\FileHelper;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\Cookie;
use backend\models\business\FormDashBoard;
use backend\models\nomenclators\UtilsConstants;

/**
 * Site controller
 */
class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['logout', 'index', 'error', 'change_lang', "ckeditorupload", 'phpinfo'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    public function actionIndex()
    {        
        /*
        if (GlobalFunctions::getRol() === User::ROLE_AGENT) {            
            return $this->redirect("/tpv/invoice/index");
        }
        else{            
            return $this->render('index');
        }
        */
        $model = new FormDashBoard();
        $model->anno = date('Y');
        $model->mes = date('m');
        $model->moneda = 2;
        $listaMeses = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
        $dataMeses = [
            '01' => 'Enero', '02' => 'Febrero', '03' => 'Marzo', '04' => 'Abril', '05' => 'Mayo', '06' => 'Junio', '07' => 'Julio',
            '08' => 'Agosto', '09' => 'Septiembre', '10' => 'Octubre', '11' => 'Noviembre', '12' => 'Diciembre'
        ];

        if ($model->load(Yii::$app->request->post())) {

        }
        $dataannos = UtilsConstants::getAnnosWithData();

        $mes = $model->mes;
        $annoActual = $model->anno;
        $annoAnterior = (int)$model->anno - 1;

        $nombre_mes = UtilsConstants::getMes((int)$mes);

        $data = UtilsConstants::getDatosFacturacion($annoActual, $mes, $model->moneda);

        $data_total = UtilsConstants::getDatosFacturasMeses($annoActual, $model->moneda);

		foreach ($listaMeses as $m){
			$facturacion[] = ['value'=>is_null($data_total['data']['total_'.$m]) ? 0: $data_total['data']['total_'.$m]];  
		}

		$facturas_meses[] = ['seriesname'=>'Facturación '.$annoActual, 'data'=>$facturacion];		
		$facturacion_meses = json_encode($facturas_meses);

        /*
        $ventasR = UtilsConstants::getVentasRutasAndMes($annoActual, $mes, $model->moneda);
        $ventasByRutas = json_encode($ventasR['ventas']);

        $ventasV = UtilsConstants::getVentasByVendedor($annoActual, $mes, $model->moneda);
        $ventasByVendedor = json_encode($ventasV['ventas']);
        */

        if (GlobalFunctions::getRol() === User::ROLE_SUPERADMIN || GlobalFunctions::getRol() === User::ROLE_ADMIN)
        {
            return $this->render('index_dashboard', [
                'model' => $model,
                'mes' => $nombre_mes,
                'dataannos' => $dataannos,
                'dataMeses' => $dataMeses,

                'nombre_mes' => $nombre_mes,
                'anno' => $annoActual,
                'moneda'=> $model->moneda == 2 ? '$': '₡',

                'total_mes_crc' => $data['total_with_iva'],
                'total_iva_mes_crc' => $data['total_iva'],
                'total_descuentos_crc' => $data['total_discount'],

                'total_mes_usd' => $data['total_with_iva'],
                'total_iva_mes_usd' => $data['total_iva'],
                'total_descuentos_usd' => $data['total_discount'],

                'graf_facturacion_meses' => $facturacion_meses,
                //'graf_facturacion_rutas' => $ventasByRutas,
                //'graf_facturacion_vendedor' => $ventasByVendedor,
            ]);        
        }
        else
            return $this->render('index');
    }


    /**
     * Logout action.
     *
     * @return string
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    public function actionChange_lang($lang, $url)
    {
        \Yii::$app->language = $lang;
        $cookie = new Cookie([
            'name' => GlobalFunctions::LANGUAGE_COOKIE_KEY,
            'value' => $lang,
            'expire' => time() + 60 * 60 * 24 * 30, // 30 days
        ]);
        \Yii::$app->getResponse()->getCookies()->add($cookie);

        return $this->redirect($url);
    }

    public function actionCkeditorupload()
    {
        $funcNum = $_REQUEST['CKEditorFuncNum'];

        if ($_FILES['upload']) {

            if (($_FILES['upload'] == "none") or (empty($_FILES['upload']['name']))) {
                $message = Yii::t('backend', "Por favor, suba alguna imagen");
            } else if ($_FILES['upload']["size"] == 0 or $_FILES['upload']["size"] > 5 * 1024 * 1024) {
                $message = Yii::t("backend", "El tamaño de la imagen no debe exceder los ") . " 5MB";
            } else if (($_FILES['upload']["type"] != "image/jpg")
                and ($_FILES['upload']["type"] != "image/jpeg")
                and ($_FILES['upload']["type"] != "image/png")
            ) {
                $message = Yii::t("backend", "Ha ocurrido un error subiendo la imagen, por favor intente de nuevo");
            } else if (!is_uploaded_file($_FILES['upload']["tmp_name"])) {

                $message = Yii::t("backend", "Formato de imagen no permitido, debe ser JPG, JPEG o PNG.");
            } else {

                $extension = pathinfo($_FILES['upload']['name'], PATHINFO_EXTENSION);

                //Rename the image here the way you want
                $name = "CKE_" . time() . '.' . $extension;

                // Here is the folder where you will save the images
                $folder = '/uploads/ckeditor_images/';
                $realPath = Yii::$app->getBasePath() . "/web" . $folder;
                if (!file_exists($realPath)) {
                    FileHelper::createDirectory($realPath, 0777);
                }

                $url = Yii::$app->urlManager->getBaseUrl() . $folder . $name;

                move_uploaded_file($_FILES['upload']['tmp_name'], $realPath . $name);
                $message = Yii::t("backend", "Imagen subida satisfactoriamente");
                //Giving permission to read and modify uploaded image
                chmod($realPath . $name, 0777);
            }

            echo '<script type="text/javascript">window.parent.CKEDITOR.tools.callFunction("'
                . $funcNum . '", "' . $url . '", "' . $message . '" );</script>';
        }
    }

    public function actionPhpinfo()
    {
        return $this->render('phpinfo');
    }
}
