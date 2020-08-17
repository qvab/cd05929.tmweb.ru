<?
//Получение текста для попапа уточнения цены для организатора
if(isset($_POST['REQUEST_ID'])){
    require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

    CModule::IncludeModule('iblock');

    $sText = partner::getClarifyCounterRequestPrice($_POST['REQUEST_ID']);

    echo ($sText ?: 1);
}