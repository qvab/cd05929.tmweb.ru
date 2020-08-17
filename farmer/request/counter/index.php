<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

LocalRedirect('/farmer/');
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
<a class="go_back cross" href="/farmer/request/<?=$req_params;?>"></a>
<?$APPLICATION->IncludeComponent(
    "rarus:farmer.counter.requests",
    "",
    Array(
        "FARMER_ID" => $USER->GetID(),
        "OFFER_LIST_URL" => "/farmer/offer/",
        "DEAL_LIST_URL" => "/farmer/deals/",
        "REQUEST_LIST_URL" => "/farmer/request/",
        "TYPE" => "farmer",
    )
);?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>