<?php
namespace common\components\ftp;

use Yii;
use yii\base\Exception;

class FtpService
{
    private $ftpHost;
    private $ftpUser;
    private $ftpPass;
    private $connection;

    public function __construct($host, $username, $password)
    {
        $this->connection = ftp_connect($host);

        if (!$this->connection || !ftp_login($this->connection, $username, $password)) {
            throw new Exception('Unable to connect or login to FTP server');
        }

        ftp_pasv($this->connection, true); // Enable passive mode
    }

    /*
    public function connect()
    {
        $this->connection = ftp_connect($this->ftpHost);

        if ($this->connection === false) {
            throw new \Exception('Could not connect to FTP server');
        }

        $login = ftp_login($this->connection, $this->ftpUser, $this->ftpPass);

        if ($login === false) {
            throw new \Exception('Could not log in to FTP server');
        }

        ftp_pasv($this->connection, true); // Enable passive mode
    }
    */

    public function listFiles($directory)
    {
        $fileList = ftp_nlist($this->connection, $directory);

        if ($fileList === false) {
            throw new \Exception('Could not list files in directory: ' . $directory);
        }

        return $fileList;
    }

    public function downloadFile($remoteFile, $localFile)
    {
        Yii::info('Downloading: ' . $remoteFile . ' to ' . $localFile, __METHOD__);
        $download = ftp_get($this->connection, $localFile, $remoteFile, FTP_BINARY);        
        if ($download === false) {
            Yii::info('Could not download file: ' . $remoteFile, __METHOD__);
            throw new \Exception('Could not download file: ' . $remoteFile);
        }

        return $download;
    }

    public function deleteFile($remoteFile)
    {
        try {
            // Verificar si el archivo existe en el servidor FTP
            $files = ftp_nlist($this->connection, dirname($remoteFile));            
            if (in_array($remoteFile, $files)) {                
                // Intentar eliminar el archivo FTP y capturar cualquier error
                if (ftp_delete($this->connection, $remoteFile)) {
                    Yii::info('Archivo FTP eliminado: ' . $remoteFile, 'application');
                    return true;
                } else {
                    // Obtener el último mensaje de error FTP
                    $lastFtpError = error_get_last();
                    $ftpErrorMessage = isset($lastFtpError['message']) ? $lastFtpError['message'] : 'No se pudo obtener el mensaje de error FTP.';
                    Yii::info('Error al eliminar el archivo FTP: ' . $remoteFile . '. Mensaje de error: ' . $ftpErrorMessage, 'application');
                    return false;
                }                
            } else {
                Yii::error('El archivo no existe en el servidor FTP: ' . $remoteFile, 'application');
                return false;
            }
        } catch (Exception $e) {
            Yii::error('Excepción al eliminar archivo FTP: ' . $e->getMessage(), 'application');
            return false;
        }
    }

    public function __destruct()
    {
        if ($this->connection) {
            ftp_close($this->connection);
        }
    }
}
