<?php

namespace backend\modules\reportes\controllers;

use Yii;
use backend\models\business\Product;
use backend\models\business\ProductHasBranchOffice;
use backend\modules\reportes\models\InventarioReportForm;
use backend\models\nomenclators\Family;
use backend\models\nomenclators\Category;
use backend\models\nomenclators\Country;
use backend\models\nomenclators\UtilsConstants;
use backend\models\settings\Issuer;
use kartik\form\ActiveForm;
use yii\helpers\Url;
use yii\web\UploadedFile;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\FileHelper;

/**
 * InventarioController implements the CRUD actions for Documents model.
 */
class InventarioController extends ExportController
{
	/**
	 * {@inheritdoc}
	 */
	public function behaviors()
	{
		return [
			'verbs' => [
				'class' => VerbFilter::className(),
				'actions' => [
					'delete' => ['POST'],
				],
			],
		];
	}

	public function actionIndex()
	{
		$model = new InventarioReportForm();

		if ($model->load(Yii::$app->request->post())) {
			$query = Product::find()->select('product.family_id, product.category_id, family.name as familia, category.name as categoria')
				->join('INNER JOIN', 'family', 'product.family_id = family.id')
				->join('INNER JOIN', 'category', 'product.category_id = category.id');
			//Familia
			if (!is_null($model->family) && !empty($model->family)) {
				$query->andWhere(['product.family_id' => $model->family]);
			}

			//Categoria
			if (!is_null($model->category) && !empty($model->category)) {
				$query->andWhere(['product.category_id' => $model->category]);
			}

			//Paises
			if (!is_null($model->country) && !empty($model->country)) {
				$query->andWhere(['product.country_id' => $model->country]);
			}

			//Tipo
			if (!is_null($model->tipo) && !empty($model->tipo)) {
				$query->andWhere(['product.inventory_type_id' => $model->tipo]);
			}

			$query->groupBy('product.family_id, family.name, product.category_id, category.name');
			$query->orderBy('product.family_id, category.name ASC');
			$datos = $query->asArray()->all();

			$content = $this->renderAjax('_header_xls', []);
			$content .= $this->renderAjax('_rowHeader', []);

			foreach ($datos as $d) {
				$productos = Product::find()->where(['family_id' => $d['family_id'], 'category_id' => $d['category_id']])->all();

				foreach ($productos as $product) {
					/*
					$locations = PhysicalLocation::find()
						->innerJoin('sector_location', 'physical_location.sector_location_id = sector_location.id')
						->innerJoin('sector', 'sector_location.sector_id = sector.id')
						->where(['physical_location.product_id' => $product->id])
						//->andWhere(['sector.branch_office_id' => $origin_branch_office_id])
						//->andWhere(['>', 'physical_location.quantity', 0])
						->orderBy('physical_location.id DESC')
						->all();
					*/						
					$content .= $this->renderAjax('_rowContent', ['model' => $model, 'productos' => $productos]);
				}

				/*
				$query = Product::find()->select('product.description as producto, family.name as familia, category.name as categoria, country.name as pais')
										->join('INNER JOIN', 'family', 'product.family_id = family.id')
										->join('INNER JOIN', 'category', 'product.category_id = category.id')
										->join('INNER JOIN', 'country', 'product.country_id = country.id')
										->where(['product.family_id'=>$f['id']]);
				if (!empty($paramCategorias))
					$query->andWhere(['product.category_id'=>$paramCategorias]);						
					
				$productos = $query->orderBy(['producto'=> SORT_ASC, 'familia'=> SORT_ASC, 'categoria'=> SORT_ASC, 'pais'=> SORT_ASC])
								->asArray()
								->all();	
								
				foreach ($productos as $product)										   
					$content .= $this->renderAjax('_rowContent', ['product'=>$product]);	

				$content .= "<tr><td colspan='10'></td></tr>";	
				*/
			}

			$content .= $this->renderAjax('_footer_xls', []);
			echo $this->download($content);
			exit;
		}

		return $this->render('_reportForm', [
			'model' => $model,
		]);
	}
}
