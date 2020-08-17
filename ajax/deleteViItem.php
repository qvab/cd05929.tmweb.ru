<?
header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header ("Cache-Control: no-cache, must-revalidate");
header ("Pragma: no-cache");
header("content-type: application/x-javascript; charset=UTF-8");
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
?>
<?
CModule::IncludeModule('iblock');

CIBlockElement::Delete($_POST['id']);

$weight = $fullCost = $fullTransportCost = 0;
$res = CIBlockElement::GetList(
    array('ID' => 'ASC'),
    array(
        'IBLOCK_ID' => rrsIblock::getIBlockId('deals_exe_docs'),
        'ACTIVE' => 'Y',
        'PROPERTY_DEAL' => $_POST['deal']
    ),
    false,
    false,
    array('ID', 'DATE_CREATE', 'PROPERTY_CAR', 'PROPERTY_WEIGHT', 'PROPERTY_DUMP', 'PROPERTY_RC', 'PROPERTY_COST', 'PROPERTY_TRANSPORT_COST')
);
while ($ob = $res->GetNext()) {
    $weight += $ob['PROPERTY_WEIGHT_VALUE'];
    $fullCost += $ob['PROPERTY_COST_VALUE'];
    $fullTransportCost += $ob['PROPERTY_TRANSPORT_COST_VALUE'];
}

$rc_ = 0;
if ($weight > 0) {
    $rc_ = $fullCost / $weight;
}

$result['weight'] = number_format($weight, 0, ',', ' ');
$result['rc'] = number_format(1000 * $rc_, 2, ',', ' ');
$result['cost'] = number_format($fullCost, 2, ',', ' ');
$result['transport_cost'] = number_format($fullTransportCost, 2, ',', ' ');

echo json_encode($result);
?>