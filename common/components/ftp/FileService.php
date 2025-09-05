<?php
namespace common\components\ftp;

use Yii;

class FileService
{
    private $ftpService;

    public function __construct($ftpService)
    {
        $this->ftpService = $ftpService;
    }

    public function downloadFiles($remoteDirectory, $localDirectory)
    {
        $downloadedFiles = [];

        //$this->ftpService->connect();
        $files = $this->ftpService->listFiles($remoteDirectory);        
        foreach ($files as $file) {
            // Extraemos solo el nombre del archivo sin la ruta completa
            $fileName = basename($file);
            if ($fileName == '.' || $fileName == '..') {
                continue;
            }

            $remoteFile = $remoteDirectory . '/' . $fileName;
            $localFile = $localDirectory . '/' . $fileName;

            // Debugging info
            Yii::info('Downloading: ' . $remoteFile . ' to ' . $localFile, __METHOD__);

            if ($this->ftpService->downloadFile($remoteFile, $localFile)) {
                $downloadedFiles[] = $localFile;
                Yii::info('Downloaded: ' . $localFile, __METHOD__);
            } else {
                Yii::error('Error al descargar el archivo: ' . $remoteFile, __METHOD__);
            }
        }

        //$this->ftpService->close();

        return $downloadedFiles;
    }
}
