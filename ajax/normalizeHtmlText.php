<?php

if(!empty($_POST['html'])) {
    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
    header("Cache-Control: no-cache, must-revalidate");
    header("Pragma: no-cache");
    header("content-type: application/x-javascript; charset=UTF-8");

    require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

    echo popupTemplates::normalizeTemplate($_POST['html']);
    exit;
}