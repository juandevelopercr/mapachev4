<?php
namespace common\components\ftp;

use PhpOffice\PhpSpreadsheet\IOFactory;

class ExcelReader
{
    public function read($filePath)
    {
        $spreadsheet = IOFactory::load($filePath);
        $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

        return $sheetData;
    }
}

?>