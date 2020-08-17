<?php
/**
 * Created by PhpStorm.
 * User: bragev
 * Date: 07.11.2018
 * Time: 16:06
 */
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
CModule::IncludeModule('iblock');
$elObj = new CIBlockElement;
if(sizeof($_POST)){
    if(isset($_POST['get_report'])){
        if($_POST['get_report'] == 1){
            if((isset($_POST['report_date']))&&(!empty($_POST['report_date']))){

                $date_v = $_POST['report_date'];
                $date_p = date('Y-m-d',strtotime($date_v));
                $date_f = date('d_m_Y',strtotime($date_v));
            }else{
                $date_v = date('d.m.Y');
                $date_p = date('Y-m-d');
                $date_f = date('d_m_Y');
            }
            $rs = CIBlockElement::GetList(
                array(),
                array(
                    "IBLOCK_ID" => rrsIblock::getIBlockId('parity_price_report'),
                    "PROPERTY_R_DATE" => $date_p
                ),
                false,
                false,
                array('ID','PROPERTY_R_DATE','PROPERTY_REPORT')
            );
            $dir_path = $_SERVER['DOCUMENT_ROOT'].'/upload/parity_prices_report/';
            if(!file_exists($dir_path)){
                mkdir($dir_path, 0775);
                chmod($dir_path, 0775);
            }
            $file_path = $dir_path.'ParityPriceReport_'.$date_f.'.xlsx';
            $file_server_path = '/upload/parity_prices_report/ParityPriceReport_'.$date_f.'.xlsx';
            //генерируем отчет
            ParityPriceReport::getReport($file_path,$date_v);

            //проверяем есть ли отчет на текущий день
            if ($rs->SelectedRowsCount() > 0) {

                while($arItem = $rs->GetNext()) {
                    $arFields = array(
                        "DATE_ACTIVE_FROM" => ConvertTimeStamp(time(), "FULL"),
                    );
                    $elObj->Update($arItem['ID'], $arFields);
                }
            }else {
                if(file_exists($file_path)){
                    $arFields = array(
                        'ACTIVE' => 'Y',
                        'IBLOCK_ID' => rrsIblock::getIBlockId('parity_price_report'),
                        "DATE_ACTIVE_FROM" => ConvertTimeStamp(time(), "FULL"),
                        'NAME' => 'Отчет по паритетным ценам '.$date_v,
                        'PROPERTY_VALUES' => array(
                            'R_DATE' => ConvertTimeStamp(strtotime($date_v), 'FULL'),
                            'REPORT' => array("VALUE"=>$file_server_path, "DESCRIPTION"=>"Описание файла")
                        )
                    );
                    $elObj->Add($arFields);
                }
            }
        }
    }
}
LocalRedirect('/admin/parity-price-report.php');