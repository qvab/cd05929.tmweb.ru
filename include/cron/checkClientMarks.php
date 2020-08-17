<?//проверка оценок покупателей, если через 24 часа оценка не проставлена, то она проставляется автоматически
//от имени организатора: 0,0,0; от имени поставщика: 10,10,10
//1 час

/*if(empty($_SERVER['SHELL']))
	die();*/

set_time_limit(12000);
//боевой
$_SERVER["DOCUMENT_ROOT"] = "/home/bitrix/www";
//песочница
//$_SERVER["DOCUMENT_ROOT"] = "/www/unorganized_projects/agrohelper/sandboxes/aledem/www";

define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS",true);
flush();
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
ob_end_flush();

CModule::IncludeModule('iblock');
$ib = rrsIblock::getIBlockId('client_marks');

$res = CIBlockElement::GetList(
    array('ID' => 'DESC'),
    array(
        'IBLOCK_ID' => $ib,
        'ACTIVE' => 'Y',
        '<DATE_CREATE' => date('d.m.Y H:i:s', strtotime('-1 day')),
        array(
            'LOGIC' => 'OR',
            array(
                'PROPERTY_CHECK_PARTNER' => false
            ),
            array(
                'PROPERTY_CHECK_FARMER' => false
            )
        )
    ),
    false,
    false,
    array('ID', 'PROPERTY_CHECK_PARTNER', 'PROPERTY_CHECK_FARMER')
);
while ($ob = $res->Fetch()) {
    $prop = array();
    if (!$ob['PROPERTY_CHECK_PARTNER_ENUM_ID']) {
        $prop['REC_PARTNER'] = 0    ;
        $prop['LAB_PARTNER'] = 0;
        $prop['PAY_PARTNER'] = 0;
        $prop['CHECK_PARTNER'] = rrsIblock::getPropListKey('client_marks', 'CHECK_PARTNER', 'yes');
    }
    if (!$ob['PROPERTY_CHECK_FARMER_ENUM_ID']) {
        $prop['REC_FARMER'] = 10;
        $prop['LAB_FARMER'] = 10;
        $prop['PAY_FARMER'] = 10;
        $prop['CHECK_FARMER'] = rrsIblock::getPropListKey('client_marks', 'CHECK_FARMER', 'yes');
    }
    if (sizeof($prop) > 0) {
        CIBlockElement::SetPropertyValuesEx(
            $ob['ID'],
            $ib,
            $prop
        );
    }
}
?>