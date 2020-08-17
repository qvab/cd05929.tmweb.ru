<?php
/**
 * Created by PhpStorm.
 * User: bragev
 * Date: 15.11.2018
 * Time: 17:48
 */

require_once 'lib/PhpSpreadsheet/vendor/autoload.php';

use \PhpOffice\PhpSpreadsheet\IOFactory;
use \PhpOffice\PhpSpreadsheet\Collection\CellsFactory;


class ImportRegionalModification {
    /**
     *
     * Импорт данных в по региональным поправкам
     * @param $elID - ID элемента содержащего файл
     * @param $file_path - путь к файлу
     * @return array (
     *       result - true|false
     *       error - текст ошибки если она есть
     * )
     */
    static function ImportData($elID,$file_path){
        $error = '';
        $result = false;
        if(file_exists($file_path)){
            $file_array = self::readXLSFile($file_path);
            if((sizeof($file_array))&&(is_array($file_array))){
                $report_txt = self::InportDataInIB($file_array);
                //прикрепляем файл к отчету и ставим флаг об обработке
                $dir_path = $_SERVER['DOCUMENT_ROOT'].'/upload/regional_mod_reports/';
                if(!file_exists($dir_path)){
                    mkdir($dir_path, 0775);
                    chmod($dir_path, 0775);
                }
                $report_path =  $dir_path.$elID.'.txt';
                file_put_contents($report_path,$report_txt);
                if(file_exists($report_path)){
                    CIBlockElement::SetPropertyValuesEx($elID, false, array(
                        'REPORT_FILE' => CFile::MakeFileArray($report_path),
                        'SUCCESS' => rrsIblock::getPropListKey('regional_modification', 'SUCCESS', 'Y')
                    ));
                    $el = new CIBlockElement;
                    $res = $el->Update($elID, array('TIMESTAMP_X' => date('d.m.Y H:i:s')));
                    unlink($report_path);
                }
                $result = true;
            }else{
                $error = 'Проблемы с чтением файла или его форматом';
            }
        }else{
            $error = 'Файл ['.$file_path.'] не найден';
        }
        return array(
            'result' => $result,
            'error' => $error
        );
    }


    /**
     *
     * @param $file_array - массив с данным для импорта
     */
    private static function InportDataInIB($file_array){
        $result = "";
        //массив культур для отчета
        $cultures_names = self::getListNames(10);
        //массив рег. центров для отчета
        $regions_c_names = self::getListNames(37);
        //массив регионов для отчета
        $regions_names = self::getListNames(23);
        $reg_centers = self::getRegCenters();


        $elObj = new CIBlockElement;
        if((sizeof($file_array))&&(is_array($file_array))){
            foreach($file_array as $regID=>$arItem){
                if((sizeof($file_array))&&(is_array($file_array))){
                    foreach ($arItem as $cultID=>$val){
                        if(isset($reg_centers[$regID][$cultID])){
                            if(!empty($val)){
                                CIBlockElement::SetPropertyValuesEx($reg_centers[$regID][$cultID], false, array('CORRECTION' => $val));
                                $result.="true:  ".$regions_names[$regID]."[".$regID."]  ".$regions_c_names[$reg_centers[$regID][$cultID]]."[".$reg_centers[$regID][$cultID]."]  ".$cultures_names[$cultID]."[".$cultID."] = [".$val."]\n";
                            }else{
                                $result.="false: ".$regions_names[$regID]."[".$regID."]  ".$regions_c_names[$reg_centers[$regID][$cultID]]."[".$reg_centers[$regID][$cultID]."]  ".$cultures_names[$cultID]."[".$cultID."] = [".$val."]\n";
                            }
                        }else{
                            $result.="false: ".$regions_names[$regID]."[".$regID."]  ".$regions_c_names[$reg_centers[$regID][$cultID]]."[".$reg_centers[$regID][$cultID]."]  ".$cultures_names[$cultID]."[".$cultID."] = [".$val."]\n";
                        }
                    }
                }
            }
        }
        return $result;
    }

    /**
     * Чтение файла и предобразования данных в массив для последующего импорта его в ИБ
     * @param $file_path - путь до файла
     */
    static function readXLSFile($file_path)
    {
        mb_internal_encoding("8bit");
        $inputFileType = IOFactory::identify($file_path);
        $reader = IOFactory::createReader($inputFileType);
        $spreadsheet = $reader->load($file_path);
        $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
        $readData = array();
        $readDataResult = array();
        if((sizeof($sheetData))&&(is_array($sheetData))){
            $column = 0;
            //формируем ключи из ID регионов
            foreach($sheetData[2] as $k=>$v){
                if($column>=2){
                    $readData[$v] = $k;
                }
                $column++;
            }
            //читаем данные с поправками по культурам и разбираем их по регионам
            if((sizeof($readData))&&(is_array($readData))){
                foreach($readData as $regID=>$colName){
                    for($i=3,$c=sizeof($sheetData);$i<=$c;$i++){
                        $cult_id = 0;
                        foreach($sheetData[$i] as $k=>$v){
                            if($k=='A'){
                                $cult_id = $v;
                            }else{
                                $val = trim(str_replace('%','',$sheetData[$i][$colName]));
                                $val = str_replace(',','.',$val);
                                $readDataResult[$regID][$cult_id] = $val;
                            }
                            $column++;
                        }
                    }
                }
            }
        }
        return $readDataResult;
    }


    /**
     * Получение типа компании лидера в региональном центре
     * @param  int $center_id идентификатор регионального центра
     *         int $culture_id идентификатор культуры
     * @return int идентификатор типа компании
     */
    public static function getRegCenters() {
        $result = array();
        $res = CIBlockElement::GetList(
            array('ID' => 'DESC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('reg_center_leader'),
                'ACTIVE' => 'Y',
            ),
            false,
            false,
            array(
                'ID',
                'NAME',
                'PROPERTY_CENTER',
                'PROPERTY_REGION',
                'PROPERTY_CULTURE'
            )
        );
        while($ar_fields = $res->GetNext()) {
            $result[$ar_fields['PROPERTY_REGION_VALUE']][$ar_fields['PROPERTY_CULTURE_VALUE']] = $ar_fields['PROPERTY_CENTER_VALUE'];
        }
        return $result;
    }


    /**
     * Получение имен элементов инфоблока и имен прикрепленных к нему элементов в свойствах
     *
     * @param $IB_ID - идентификатор инфоблока
     * @param array $prop_name - код свойства
     * @return array - массив данных
     */
    private static function getListNames($IB_ID,$prop_name = array()){
        $res = array();
        CModule::IncludeModule('iblock');
        $params = array('ID','NAME');
        if((sizeof($prop_name))&&(is_array($prop_name))){
            foreach ($prop_name as $k=>$v){
                $params[] = 'PROPERTY_'.$v;
            }
        }

        $rs = CIBlockElement::GetList(
            array(),
            array(
                "IBLOCK_ID" => $IB_ID,
            ),
            false,
            false,
            $params
        );
        while($ar = $rs->GetNext()) {
            if((sizeof($prop_name))&&(is_array($prop_name))){
                $tmp = array(
                    'NAME'=>$ar['NAME']
                );
                foreach ($prop_name as $k=>$v){
                    $r_name = CIBlockElement::GetByID($ar['PROPERTY_'.$v.'_VALUE']);
                    if($r_val = $r_name->GetNext()){
                        $tmp[$v] = $r_val['NAME'];
                    }
                }
                $res[$ar['ID']] = $tmp;
            }else{
                $res[$ar['ID']] = $ar['NAME'];
            }
        }
        return $res;
    }




}