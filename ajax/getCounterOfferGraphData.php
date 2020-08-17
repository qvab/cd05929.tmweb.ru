<?php

//Получаем дополнительные данные для графика предложений покупател (для требуемого НДС)

header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header ("Cache-Control: no-cache, must-revalidate");
header ("Pragma: no-cache");
header("content-type: application/x-javascript; charset=UTF-8");

if(isset($_POST['culture'])
    && is_numeric($_POST['culture'])
    && isset($_POST['wh'])
    && is_numeric($_POST['wh'])
    && isset($_POST['nds'])
    && trim($_POST['nds']) != ''
) {
    require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

    //подготавливаем данные
    $arGraphFilter = array(
        'UF_CULTURE' => $_POST['culture'],
        'UF_WAREHOUSE' => $_POST['wh'],
        'UF_USER_ID' => client::getClientByWH($_POST['wh']),
    );

    if(isset($_POST['center'])
        && is_numeric($_POST['center'])
    ){
        $arGraphFilter['UF_CENTER'] = $_POST['center'];
    }else{
        $temp_val = client::getCentersByWH($arGraphFilter['UF_CULTURE'], $arGraphFilter['UF_WAREHOUSE']);
        if (count($temp_val) == 1) {
            $arGraphFilter['UF_CENTER'] = reset($temp_val);
        }
    }

    if (!is_numeric($arGraphFilter['UF_CENTER'])) {
        $arGraphFilter['UF_CENTER'] = 0;
    }
    if (!is_numeric($arGraphFilter['UF_CENTER'])) {
        $arGraphFilter['UF_CENTER'] = 0;
    }

    $user_nds_type = ($_POST['nds'] == 'y');

    //получаем и отдаем данные для графика
    $arResult['COUNTER_GRAPHS_DATA'] = client::getCounterRequestsGraphsDataAll($arGraphFilter, $user_nds_type);
    $nds_val = rrsIblock::getConst('nds');
    client::showCounterRequestsGraphsDataAll($arResult['COUNTER_GRAPHS_DATA'], $nds_val, $user_nds_type, $arGraphFilter['UF_USER_ID'], true);
}

