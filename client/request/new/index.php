<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
?>
<?$APPLICATION->SetTitle("Создание запроса");?>
<h1 class="page_header"><?$APPLICATION->ShowTitle()?></h1>
<?
global $USER;
$APPLICATION->IncludeComponent(
    "rarus:client.request.add",
    "",
    Array(
        "IBLOCK_ID" => rrsIblock::getIBlockId('client_request'),
        "GROUPS" => array("1","9"),
        "LIST_URL" => "/client/request/",
        "CLIENT_ID" => $USER->GetID(),
        "USER_TYPE" => "CLIENT"
    )
);?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>