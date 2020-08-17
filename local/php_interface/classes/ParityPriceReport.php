<?php
/**
 * Created by PhpStorm.
 * User: bragev
 * Date: 07.11.2018
 * Time: 14:14
 */

require_once 'lib/PhpSpreadsheet/vendor/autoload.php';

use Bitrix\Highloadblock\HighloadBlockTable as HLBT;
use Bitrix\Main\Type\DateTime;

use PhpOffice\PhpSpreadsheet\Helper\Sample;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Style;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ParityPriceReport {

    function GetEntityDataClass($HlBlockId) {
        if (empty($HlBlockId) || $HlBlockId < 1) {
            return false;
        }
        $hlblock = HLBT::getById($HlBlockId)->fetch();
        $entity = HLBT::compileEntity($hlblock);
        $entity_data_class = $entity->getDataClass();
        return $entity_data_class;
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

    /**
     * Получение данных для построения отчета
     */
    private static function getReportData($date){
        //получаем список культур
        $cultures = self::getListNames(10);
        $regions = self::getListNames(37,array('REGION'));

        $region_names_title = array();
        foreach($regions as $id=>$item){
            $region_names_title[$item['REGION']] = $item['NAME'];
        }
        ksort($region_names_title);

        CModule::IncludeModule('highloadblock');
        $entity_data_class = self::GetEntityDataClass(1);
        $filter = array(
            '<UF_DATE'=>$date.' 23:59:59'
        );
        $rsData = $entity_data_class::getList(array(
            'select' => array('*'),
            'filter' => $filter
        ));
        $report_array = array();
        while($el = $rsData->fetch()){
            $dt = $el['UF_DATE'];
            $center_name = '';
            $region_name = '';
            $culture_name = '';
            if(isset($regions[$el['UF_CENTER']])){
                $center_name = $regions[$el['UF_CENTER']]['NAME'];
                $region_name = $regions[$el['UF_CENTER']]['REGION'];
            }
            if(isset($cultures[$el['UF_CULTURE']])){
                $culture_name = $cultures[$el['UF_CULTURE']];
            }

            $tmp = array(
                'CULTURE'=>$el['UF_CULTURE'],
                'CENTER'=>$el['UF_CENTER'],
                'CENTER_NAME'=>$center_name,
                'REGION_NAME'=>$region_name,
                'CULTURE_NAME'=>$culture_name,
                'PRICE'=>$el['UF_STANDART_PRICE'],
                'DATE'=>$dt->toString(),
                'DATA_TIME'=>strtotime($dt->toString())
            );

            if(isset($report_array[$culture_name][$region_name]['DATA_TIME'])){
                if($tmp['DATA_TIME']>$report_array[$culture_name][$region_name]['DATA_TIME']){
                    $report_array[$culture_name][$region_name] = $tmp;
                }
            }else{
                $report_array[$culture_name][$region_name] = $tmp;
            }
        }
        return array('regions'=>$region_names_title,'rows_data'=>$report_array);
    }


    /**
     * Получение отчета за месяц
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public static function getReport($file_path,$date){
        $data = self::getReportData($date);
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValueExplicitByColumnAndRow(1,1, $date,'str');
        $coord = $sheet->getCellByColumnAndRow(1,2)->getCoordinate();
        $sheet->getStyle($coord)->applyFromArray(
            array(
                'font' => array('bold' => true,'color'=> array('argb' => 'ff000000')),
                'fill' => array('type' => Fill::FILL_SOLID,'color' => array('argb' => 'FF79EAB9')),
                'alignment' => array(
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                )
            )
        );
        $sheet->setCellValueExplicitByColumnAndRow(1,2, 'Показатели алгоритма математической модели','str');
        $sheet->getColumnDimension('A')->setWidth(50);
        $coord = $sheet->getCellByColumnAndRow(1,2)->getCoordinate();
        $sheet->getStyle($coord)->applyFromArray(
            array(
                'font' => array('bold' => true,'color'=> array('argb' => 'ff000000')),
                'fill' => array('type' => Fill::FILL_SOLID,'color' => array('argb' => 'FF79EAB9')),
                'alignment' => array(
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                    'vertical' => Alignment::VERTICAL_CENTER,
                )
            )
        );
        if((sizeof($data['regions']))&&(is_array($data['regions']))){
            $col = 2;
            $row = 2;
            foreach ($data['regions'] as $k=>$v){
                $sheet->setCellValueExplicitByColumnAndRow($col,$row, $k,'str');

                $cell = $sheet->getCellByColumnAndRow($col,$row);
                $sheet->getColumnDimension($cell->getColumn())->setWidth(15);


                $coord = $sheet->getCellByColumnAndRow($col,$row)->getCoordinate();
                $sheet->getStyle($coord)->applyFromArray(
                    array(
                        'font' => array('bold' => true,'color'=> array('argb' => 'ff000000')),
                        'fill' => array('type' => Fill::FILL_SOLID,'color' => array('argb' => 'FF79EAB9')),
                        'alignment' => array(
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical' => Alignment::VERTICAL_CENTER,
                            'wrapText' => true
                        )
                    )
                );
                $col++;
            }
            $col = 1;
            $row = 3;
            if((sizeof($data['rows_data']))&&(is_array($data['rows_data']))){
                foreach ($data['rows_data'] as $culture_name=>$item){
                   $col = 1;
                   $sheet->setCellValueExplicitByColumnAndRow($col,$row, $culture_name,'str');
                    $coord = $sheet->getCellByColumnAndRow($col,$row)->getCoordinate();
                    $sheet->getStyle($coord)->applyFromArray(
                        array(
                            'font' => array('bold' => true,'color'=> array('argb' => 'ff000000')),
                            'fill' => array('type' => Fill::FILL_SOLID,'color' => array('argb' => 'FF79EAB9')),
                            'alignment' => array(
                                'horizontal' => Alignment::HORIZONTAL_LEFT,
                                'vertical' => Alignment::VERTICAL_CENTER,
                                'wrapText' => true
                            )
                        )
                    );
                   foreach ($data['regions'] as $k=>$v){
                       $col++;
                        if(isset($item[$k])){
                            $sheet->setCellValueExplicitByColumnAndRow($col,$row, $item[$k]['PRICE'],'str');
                        }else{
                            $sheet->setCellValueExplicitByColumnAndRow($col,$row, '','str');
                        }
                       $coord = $sheet->getCellByColumnAndRow($col,$row)->getCoordinate();
                       $sheet->getStyle($coord)->applyFromArray(
                           array(
                               'font' => array('color'=> array('argb' => 'ff000000')),
                               'alignment' => array(
                                   'horizontal' => Alignment::HORIZONTAL_CENTER,
                                   'vertical' => Alignment::VERTICAL_CENTER,
                               )
                           )
                       );
                    }
                    $row++;
                }
            }
        }
        $writer = new Xlsx($spreadsheet);
        $writer->save($file_path);
    }
}