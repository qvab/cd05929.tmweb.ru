<?php
/**
 * Created by 1C-Rarus
 *
 * В HL переименовываем все dap в cpt
 *
 * @author Постников Василий <postva@rarus.ru>
 */

// Подключение Битрикса
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

// Проверка на админа
if(!$USER->IsAdmin())
    die('WTF Bro!? Fuck off!');
$sLogFileName = __DIR__ . '/13011-' . date('Y_m_d_H_i_s') . '.log';

$fnLogToFile = function($sFile, $sMessage){
    error_log('['. date('Y.m.d - H:i:s') ."] {$sMessage}\n", 3, $sFile);
};

$hl_myd_id = rrsIblock::HLgetIBlockId('COUNTEROFFERS');

$logObj = new log;
$entityDataClass = $logObj->getEntityDataClass($hl_myd_id);
$el = new $entityDataClass;
$rsData = $el->getList(array(
    'select' => array('ID', 'UF_DELIVERY'),
    'filter' => ['UF_DELIVERY' => 'dap'],
    'order'  => array('ID' => 'ASC')
));
$arResult = [];
while ($arRow = $rsData->Fetch()) {
    $fnLogToFile($sLogFileName, '---------------------------------------------------------------');
    $fnLogToFile($sLogFileName, '['.date('Y.m.d - H:i:s')."] HL[{$hl_myd_id}] ID[{$arRow['ID']}] dap change to cpt");
    $fnLogToFile($sLogFileName, '---------------------------------------------------------------');
   log::_updateEntity($hl_myd_id, $arRow['ID'], ['UF_DELIVERY' => 'cpt']);
}
echo 'done';