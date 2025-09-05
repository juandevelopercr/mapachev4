<?php
namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\web\UploadedFile;
use yii\helpers\FileHelper;

class FileController extends Controller
{
    public $uploadPath = '@backend/web/uploads/files';

    public function actionIndex()
    {
        $files = FileHelper::findFiles(Yii::getAlias($this->uploadPath), ['only' => ['*.pdf']]);
        return $this->render('index', ['files' => $files]);
    }

    public function actionUpload()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    
        $file = UploadedFile::getInstanceByName('pdfFile');
        if ($file && $file->extension === 'pdf') {
            $path = Yii::getAlias('@backend/web/uploads/files/') . $file->name;
    
            if ($file->saveAs($path)) {
                // Devuelve el nombre del archivo y el índice para añadirlo dinámicamente
                return [
                    'success' => true,
                    'fileName' => $file->name,
                    'index' => count(glob(Yii::getAlias('@backend/web/uploads/files/*.pdf')))
                ];
            }
        }
    
        return ['success' => false, 'message' => 'Error al subir el archivo.'];
    }
    

    public function actionDelete()
    {
        $filename = Yii::$app->request->post('filename');
        if (!$filename) {
            return $this->asJson([
                'success' => false,
                'message' => 'No se proporcionó el nombre del archivo.'
            ]);
        }
    
        $path = Yii::getAlias($this->uploadPath) . DIRECTORY_SEPARATOR . $filename;
    
        if (file_exists($path) && unlink($path)) {
            return $this->asJson([
                'success' => true,
                'message' => 'Archivo eliminado exitosamente.'
            ]);
        }
    
        return $this->asJson([
            'success' => false,
            'message' => 'No se pudo eliminar el archivo.'
        ]);
    }
}
