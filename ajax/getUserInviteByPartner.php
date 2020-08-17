<?php
if(isset($_POST['uid'])
    && is_numeric($_POST['uid'])
    && isset($_POST['type'])
    && ($_POST['type'] == 'c' || $_POST['type'] == 'f')
) {
    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
    header("Cache-Control: no-cache, must-revalidate");
    header("Pragma: no-cache");
    header("content-type: application/x-javascript; charset=UTF-8");
    require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
    global $USER;
    if($USER->IsAuthorized()) {
        //генерирует ссылку для завершения регистрации пользователя
        $agent_obj = new agent();
        CModule::IncludeModule('iblock');
        if($_POST['type'] == 'c'){
            echo $agent_obj->getClientInviteHref($_POST['uid'], $USER->GetID());
        }elseif($_POST['type'] == 'f'){
            echo $agent_obj->getFarmerInviteHref($_POST['uid'], $USER->GetID());
        }

        exit;
    }

}