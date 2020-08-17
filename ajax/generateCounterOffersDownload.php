<?php
header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header ("Cache-Control: no-cache, must-revalidate");
header ("Pragma: no-cache");
header ("content-type: application/x-javascript; charset=UTF-8");
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

if(!empty($_POST['fields'])){
    $arrFields = explode(';', $_POST['fields']);
    CModule::IncludeModule('iblock');
    CModule::IncludeModule('highload');

    //получаем данные
    $arrCountersData = admin::getCounterRequestsData($arrFields, (!empty($_POST['sort_by']) ? $_POST['sort_by'] : ''), (!empty($_POST['order']) ? $_POST['order'] : ''));
    if(isset($arrCountersData[0])){
        $arrFields = array();
        foreach($arrCountersData[0] as $sKey => $sValue){
            $arrFields[] = $sKey;
        }
    }

    //запись данных в файл
    require $_SERVER["DOCUMENT_ROOT"] . '/local/php_interface/classes/lib/PhpSpreadsheet/vendor/autoload.php';

    $spreadsheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    //ячейки, которые будем делать шире остальных
    $arrWidthColumns = array(
        'UF_DATE' => 20,
        'UF_PARTNER_Q_APRVD_D' => 20,
        'CULTURE_NAME' => 18,
    );
    //заполняем заголовок
    foreach($arrFields as $iPos => $sKey){
        $sColumn = getCharForExcel($iPos);
        $sheet->setCellValue($sColumn . '1', $sKey);

        if(isset($arrWidthColumns[$sKey])){
            $spreadsheet->getActiveSheet()->getColumnDimension($sColumn)->setWidth($arrWidthColumns[$sKey]);
        }
    }

    //заполянем данные
    $iCounter1 = 2; //строки
    $iCounter2 = 0; //колонки
    foreach($arrCountersData as $arrData){

        $iCounter2 = 0;
        foreach($arrData as $sKey => $sValue){
            $sheet->setCellValue(getCharForExcel($iCounter2) . $iCounter1, $sValue);
            $iCounter2++;
        }
        $iCounter1++;
    }

// Выбросим исключение в случае, если не удастся сохранить файл
    try {
        $writer = new PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($_SERVER["DOCUMENT_ROOT"] . '/upload/highloadblock_rows_list.xlsx');

    } catch (PhpOffice\PhpSpreadsheet\Writer\Exception $e) {
        echo $e->getMessage();
    }

    echo 1;
}