<?php
/**
 * Created by PhpStorm.
 * User: bragev
 * Date: 16.11.2018
 * Time: 10:28
 */
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
CModule::IncludeModule('iblock');
if(isset($_GET['key'])){
    if($_GET['key'] == 'read-regional-modifacation'){
        //все необработанные
        if(isset($_GET['RM_ID'])){
            $filter_array = array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('regional_modification'),
                'ID' => $_GET['RM_ID']
            );
            $res = CIBlockElement::GetList(
                array('ID' => 'ASC'),
                $filter_array,
                false,
                false,
                array('ID', 'NAME', 'PROPERTY_INPUT_FILE', 'PROPERTY_SUCCESS')
            );
            while($ar_fields = $res->GetNext()) {
                $file_path = CFile::GetPath($ar_fields['PROPERTY_INPUT_FILE_VALUE']);
                $file_path = $_SERVER['DOCUMENT_ROOT'].$file_path;
                if(file_exists($file_path)){
                    ImportRegionalModification::ImportData($ar_fields['ID'],$file_path);
                }
            }
        }
    }
}
LocalRedirect('/admin/regional-modification.php');

