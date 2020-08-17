<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
?>
<?$APPLICATION->SetTitle("Создание запроса");?>
    <h1 class="page_header"><?$APPLICATION->ShowTitle()?></h1>
<?
global $USER;
$APPLICATION->IncludeComponent(
    "rarus:client.request.add",
    "agent",
    Array(
        "IBLOCK_ID" => rrsIblock::getIBlockId('client_request'),
        "GROUPS" => array("1","10"),
        "LIST_URL" => "/partner/client_request/",
        "CLIENT_ID" => $USER->GetID(),
        "USER_TYPE" => "AGENT"
    )
);?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>