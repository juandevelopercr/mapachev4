<?php

namespace backend\modules\reportes\controllers;

use Yii;
use backend\models\business\Product;
use backend\modules\reportes\models\TomaInventarioReportForm;
use backend\models\nomenclators\Family;
use backend\models\nomenclators\Category;
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
class TomaInventarioController extends ExportController
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
		$model = new TomaInventarioReportForm();

		if ($model->load(Yii::$app->request->post())) {
			//Familia
			if (!is_null($model->family) && !empty($model->family)) {
				$query    = Family::find();
				$familias = $query->where(['id' => $model->family])
					->asArray()
					->orderBy('name ASC')
					->all();
			} else {
				$subQuery = Product::find()->select('family_id');
				$familias = Family::find()->where(['in', 'id', $subQuery])
					->asArray()
					->all();
			}

			//Categorias
			/*
			if (!is_null($model->category) && !empty($model->category))
			{
				$query    = Category::find();
				$categorias = $query->where(['id', $model->category])
					  			  ->asArray()
					  			  ->orderBy('name ASC')
					  			  ->all();
			}
			else{
				$subQuery = Product::find()->select('category_id');				
				$categorias = Category::find()->where(['in', 'id', $subQuery])
										  ->asArray()
									   	  ->all();											 
			}
			*/

			$content = $this->renderAjax('_header_xls', []);
			foreach ($familias as $f) {
				$content .= $this->renderAjax('_rowHeader', ['familia' => $f['name']]);

				if (!is_null($model->category) && !empty($model->category)) {
					$query    = Category::find();
					$categorias = $query->where(['id' => $model->category])
						->asArray()
						->orderBy('name ASC')
						->all();
				} else {
					$subQuery = Product::find()->select('category_id');
					$categorias = Category::find()->where(['in', 'id', $subQuery])
						->asArray()
						->all();
				}

				$paramCategorias = [];
				foreach ($categorias as $c)
					$paramCategorias[] = $c['id'];

				$query = Product::find()->select('product.description as producto, family.name as familia, category.name as categoria')
					->join('INNER JOIN', 'family', 'product.family_id = family.id')
					->join('INNER JOIN', 'category', 'product.category_id = category.id')
					->where(['product.family_id' => $f['id']]);
				if (!empty($paramCategorias))
					$query->andWhere(['product.category_id' => $paramCategorias]);

				$productos = $query->orderBy(['producto' => SORT_ASC, 'familia' => SORT_ASC, 'categoria' => SORT_ASC])
					->asArray()
					->all();

				foreach ($productos as $product)
					$content .= $this->renderAjax('_rowContent', ['product' => $product]);

				$content .= "<tr><td colspan='10'></td></tr>";
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
