<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

LocalRedirect('/partner/');
exit;
$req_params = '';
$iCulture = htmlspecialcharsbx($_REQUEST['culture']);
$iWh = htmlspecialcharsbx($_REQUEST['wh']);
if(isset($iCulture))
{
    $req_params = '?culture=' . $iCulture;
}
if(isset($iWh)){
    $req_params .= ($req_params == '' ? '?' : '&') . 'wh=' . $iWh;
}
?>
<?$APPLICATION->SetTitle("Создание предложения");?>
<h1 class="page_header"><?$APPLICATION->ShowTitle()?></h1>
<a class="go_back cross" href="/partner/farmer_request/<?=$req_params;?>"></a>
<?$APPLICATION->IncludeComponent(
    "rarus:farmer.counter.requests",
    "",
    Array(
        "AGENT_ID" => $USER->GetID(),
        "OFFER_LIST_URL" => "/partner/offer/",
        "DEAL_LIST_URL" => "/partner/pair/",
        "REQUEST_LIST_URL" => "/partner/farmer_request/",
        "TYPE" => "agent",
    )
);?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>