<?php
namespace common\components\ftp;

use setasign\Fpdi\PdfParser\StreamReader;
use setasign\Fpdi\PdfReader;
use setasign\Fpdi\Tcpdf\Fpdi;
use Yii;
use yii\base\Exception;

class FtpImportTask
{
    private $ftpHost;
    private $ftpUser;
    private $ftpPass;

    public function __construct($ftpHost, $ftpUser, $ftpPass)
    {
        $this->ftpHost = $ftpHost;
        $this->ftpUser = $ftpUser;
        $this->ftpPass = $ftpPass;
    }

    public function run($useLocalDirectory = false)
    {        
        $excelReader = new ExcelReader();
        $xmlReader = new XmlReader();
        $dataProcessor = new DataProcessor();
        $downloadedFiles = [];
        $ftpService = null; // Inicializar $ftpService a null
        
        $remoteDirectory = '/'; // Carpeta en el FTP
        
        if ($useLocalDirectory) {          
            $localDirectory = Yii::getAlias('@webroot/uploads/files'); // Carpeta local para leer los archivos            
            $downloadedFiles = $this->getFilesFromLocalDirectory($localDirectory);            
        } else {            
            $ftpService = new FtpService($this->ftpHost, $this->ftpUser, $this->ftpPass);
            $fileService = new FileService($ftpService);
            
            $localDirectory = Yii::getAlias('@webroot/uploads/files'); // Carpeta local donde se descargarán los archivos

            if (!is_dir($localDirectory)) {
                mkdir($localDirectory, 0777, true);
            }

            $downloadedFiles = $fileService->downloadFiles($remoteDirectory, $localDirectory);        
        }

        // Debug message
        Yii::info('Archivos descargados: ' . implode(', ', $downloadedFiles), 'application');

        // Procesar todos los archivos pdf
        foreach ($downloadedFiles as $file) {
            $fileInfo = pathinfo($file);
            if ($fileInfo['extension'] == 'pdf') {
                // Debug message
                Yii::info('Procesando archivo PDF: ' . $file, 'application');                            
                $result = $dataProcessor->processPdfData($file);    
                Yii::info('Resultado del Proceso: '. $result, 'application');            
                $this->deleteFile($file, $useLocalDirectory, $ftpService, $remoteDirectory);                                
            }           
        }
        /*
        if ($ftpService) {
            $ftpService->close();
        }
            */
    }

    private function getFilesFromLocalDirectory($directory)
    {
        $files = [];
        $dirHandle = opendir($directory);

        if ($dirHandle) {
            while (($file = readdir($dirHandle)) !== false) {
                if ($file !== '.' && $file !== '..') {
                    $files[] = $directory . '/' . $file;
                }
            }
            closedir($dirHandle);
        }
        return $files;
    }

    public function deleteFile($file, $useLocalDirectory, $ftpService, $remoteDirectory)
    {
        // Manejo de la eliminación del archivo local
        try {
            Yii::info('Intentando eliminar archivo local: ' . $file, 'application');
            if (file_exists($file)) {
                if (unlink($file)) {
                    Yii::info('Archivo local eliminado: ' . $file, 'application');
                } else {
                    Yii::error('No se pudo eliminar el archivo local: ' . $file, 'application');
                }
            } else {
                Yii::error('Archivo local no encontrado para eliminar: ' . $file, 'application');
            }
        } catch (Exception $e) {
            Yii::error('Excepción al eliminar archivo local: ' . $e->getMessage(), 'application');
        }
    
        // Manejo de la eliminación del archivo FTP
        if (!$useLocalDirectory) {
            try {
                Yii::info('Intentando eliminar archivo FTP', 'application');
                $remoteFile = str_replace(Yii::getAlias('@webroot/uploads/files'), $remoteDirectory, $file);
                if ($ftpService->deleteFile($remoteFile)) {
                    Yii::info('Archivo FTP eliminado: ' . $remoteFile, 'application');
                } else {
                    Yii::error('Error al eliminar el archivo FTP: ' . $remoteFile, 'application');
                }
            } catch (Exception $e) {
                Yii::error('Excepción al eliminar archivo FTP: ' . $e->getMessage(), 'application');
            }
        }
    }

}