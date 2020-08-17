<?php
if(isset($_POST['uid'])
    && is_numeric($_POST['uid'])
    && isset($_POST['type'])
    && ($_POST['type'] == 'c' || $_POST['type'] == 'f')
    && isset($_POST['mode'])
    && ($_POST['mode'] == 'link' || $_POST['mode'] == 'unlink')
) {
    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
    header("Cache-Control: no-cache, must-revalidate");
    header("Pragma: no-cache");
    header("content-type: application/x-javascript; charset=UTF-8");
    require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
    global $USER;
    if($USER->IsAuthorized()) {

        //привязывает/отвязывает пользователя от организатора
        if($_POST['mode'] == 'link') {
            echo intval(partner::linkUserToPartner($_POST['uid'], $USER->GetID(), $_POST['type']));
        }else{
            echo intval(partner::unlinkUserFromPartner($_POST['uid'], $USER->GetID(), $_POST['type']));
        }

        exit;
    }

}