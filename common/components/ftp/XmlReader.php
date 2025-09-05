<?php 
namespace common\components\ftp;

class XmlReader
{
    public function read($filePath)
    {
        return simplexml_load_file($filePath);
    }
}
?>