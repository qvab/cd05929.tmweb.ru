<?
//1 час

/*if(empty($_SERVER['SHELL']))
die();*/

set_time_limit(12000);
//боевой
$_SERVER["DOCUMENT_ROOT"] = "/home/bitrix/www";
//песочница
//$_SERVER["DOCUMENT_ROOT"] = "/home/aledem/sandboxes/agrouber/www";

define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS",true);
flush();
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
ob_end_flush();

CModule::IncludeModule('iblock');
$el = new CIBlockElement;

$requestIblockID = rrsIblock::getIBlockId('client_request');

$noticeList = notice::getNoticeList();

$res = CIBlockElement::GetList(
    array("ID" => "DESC"),
    array(
        "IBLOCK_ID" => $requestIblockID,
        "ACTIVE" => "Y",
        "PROPERTY_ACTIVE" => rrsIblock::getPropListKey('client_request', 'ACTIVE', 'yes'),
        "<DATE_ACTIVE_TO" => date("d.m.Y H:i:s")
    ),
    false,
    false,
    array("ID", "DATE_ACTIVE_TO", "PROPERTY_CLIENT", "PROPERTY_CULTURE", "PROPERTY_ACTIVE")
);

while ($ob = $res->GetNext()) {
    CIBlockElement::SetPropertyValuesEx(
        $ob['ID'],
        $requestIblockID,
        array('ACTIVE' => rrsIblock::getPropListKey('client_request', 'ACTIVE', 'no'))
    );
    $result = $el->Update($ob['ID'], array('TIMESTAMP_X' => date('d.m.Y H:i:s')));


    /**
     * Удаляем встречные запросы
     */
    client::removeCountersByRequestID($ob['ID']);

    $client = client::getProfile($ob['PROPERTY_CLIENT_VALUE'], true);
    $culture = culture::getName($ob['PROPERTY_CULTURE_VALUE']);

    $url = '/client/request/?id='. $ob['ID'];
    if (in_array($noticeList['e_r']['ID'], $client['PROPERTY_NOTICE_VALUE'])) {
        $arEventFields = array(
            'CULTURE' => $culture['NAME'],
            'ID' => $ob['ID'],
            'URL' => $GLOBALS['host'].$url,
            'EMAIL' => $client['USER']['EMAIL'],
        );
        CEvent::Send('CLIENT_END_REQUEST', 's1', $arEventFields);
    }
    if (in_array($noticeList['c_r']['ID'], $client['PROPERTY_NOTICE_VALUE'])) {
        notice::addNotice($client['USER']['ID'], 'd', 'Истек срок активности запроса', $url, '#' . $ob['ID']);
    }
    if (in_array($noticeList['s_r']['ID'], $client['PROPERTY_NOTICE_VALUE']) && $client['PROPERTY_PHONE_VALUE']) {
        $phone = str_replace(array('+', ' ', '(', ')', '-'), "", $client['PROPERTY_PHONE_VALUE']);
        notice::sendNoticeSMS($phone, 'Истек срок активности запроса: '.$GLOBALS['host'].$url);
    }
}
?>