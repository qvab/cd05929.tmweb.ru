<?//проверка сделок в статусе "Новая сделка", если в течение 1 часа поставщик не подтвердил сделку, то она аннулируется
//10 минут

/*if(empty($_SERVER['SHELL']))
	die();*/
exit;

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

$requestIBlockId = rrsIblock::getIBlockId('client_request');

$arDeals = $arRequests = $requestIds = $returnVolume =  array();

//список сделок, которые необходимо аннулировать
$res = CIBlockElement::GetList(
    array('ID' => 'DESC'),
    array(
        'IBLOCK_ID' => rrsIblock::getIBlockId('deals_deals'),
        'ACTIVE' => 'Y',
        'PROPERTY_STAGE' => rrsIblock::getPropListKey('deals_deals', 'STAGE', 'new'),
        '<PROPERTY_DATE_STAGE' => date('Y-m-d H:i:s', strtotime('-1 hours'))
    ),
    false,
    false,
    array(
        'ID',
        'PROPERTY_VOLUME',
        'PROPERTY_REQUEST',
        'PROPERTY_CULTURE.NAME',
        'PROPERTY_CLIENT',
        'PROPERTY_FARMER',
        'PROPERTY_PARTNER'
    )
);
while ($ob = $res->GetNext()) {
    $arDeals[$ob['ID']] = $ob;
    if (intval($ob['PROPERTY_REQUEST_VALUE']) > 0) {
        $requestIds[$ob['PROPERTY_REQUEST_VALUE']] = true;
        if (!$returnVolume[$ob['PROPERTY_REQUEST_VALUE']])
            $returnVolume[$ob['PROPERTY_REQUEST_VALUE']] = 0;

        $returnVolume[$ob['PROPERTY_REQUEST_VALUE']] += $ob['PROPERTY_VOLUME_VALUE'];
    }
}

if (sizeof($requestIds) > 0) {
    $res = CIBlockElement::GetList(
        array('ID' => 'DESC'),
        array(
            'IBLOCK_ID' => $requestIBlockId,
            'ACTIVE' => 'Y',
            'ID' => array_keys($requestIds)
        ),
        false,
        false,
        array(
            'ID',
            'DATE_ACTIVE_TO',
            'PROPERTY_VOLUME',
            'PROPERTY_REMAINS'
        )
    );
    while ($ob = $res->GetNext()) {
        $arRequests[$ob['ID']] = $ob;
    }
}

$noticeList = notice::getNoticeList();
foreach ($arDeals as $deal) {
    //меняем статус
    deal::setStatus($deal['ID'], 'reject');

    //пишем данные в лог
    log::addDealStatusLog($deal['ID'], 'reject', 'Сделка аннулируется');

    /*$client = client::getProfile($deal['PROPERTY_CLIENT_VALUE'], true);
    $farmer = farmer::getProfile($deal['PROPERTY_FARMER_VALUE'], true);
    $partner = partner::getProfile($deal['PROPERTY_PARTNER_VALUE'], true);

    $url = '/client/deals/' . $deal['ID'] . '/';
    if (in_array($noticeList['e_d']['ID'], $client['PROPERTY_NOTICE_VALUE'])) {
        $arEventFields = array(
            'CULTURE' => $deal['PROPERTY_CULTURE_NAME'],
            'ID' => $deal['ID'],
            'URL' => $GLOBALS['host'].$url,
            'EMAIL' => $client['USER']['EMAIL'],
        );
        CEvent::Send('REJECT_DEAL', 's1', $arEventFields);
    }
    if (in_array($noticeList['c_d']['ID'], $client['PROPERTY_NOTICE_VALUE'])) {
        notice::addNotice($client['USER']['ID'], 'd', 'Сделка аннулирована', $url, '#' . $deal['ID']);
    }
    if (in_array($noticeList['s_d']['ID'], $client['PROPERTY_NOTICE_VALUE']) && $client['PROPERTY_PHONE_VALUE']) {
        $phone = str_replace(array('+', ' ', '(', ')', '-'), "", $client['PROPERTY_PHONE_VALUE']);
        notice::sendNoticeSMS($phone, 'Сделка аннулирована: '.$GLOBALS['host'].$url);
    }

    $url = '/farmer/deals/' . $deal['ID'] . '/';
    if (in_array($noticeList['e_d']['ID'], $farmer['PROPERTY_NOTICE_VALUE'])) {
        $arEventFields = array(
            'CULTURE' => $deal['PROPERTY_CULTURE_NAME'],
            'ID' => $deal['ID'],
            'URL' => $GLOBALS['host'].$url,
            'EMAIL' => $farmer['USER']['EMAIL'],
        );
        CEvent::Send('REJECT_DEAL', 's1', $arEventFields);
    }
    if (in_array($noticeList['c_d']['ID'], $farmer['PROPERTY_NOTICE_VALUE'])) {
        notice::addNotice($farmer['USER']['ID'], 'd', 'Сделка аннулирована', $url, '#' . $deal['ID']);
    }
    if (in_array($noticeList['s_d']['ID'], $farmer['PROPERTY_NOTICE_VALUE']) && $farmer['PROPERTY_PHONE_VALUE']) {
        $phone = str_replace(array('+', ' ', '(', ')', '-'), "", $farmer['PROPERTY_PHONE_VALUE']);
        notice::sendNoticeSMS($phone, 'Сделка аннулирована: '.$GLOBALS['host'].$url);
    }

    $url = '/partner/deals/' . $deal['ID'] . '/';
    if (in_array($noticeList['e_d']['ID'], $partner['PROPERTY_NOTICE_VALUE'])) {
        $arEventFields = array(
            'CULTURE' => $deal['PROPERTY_CULTURE_NAME'],
            'ID' => $deal['ID'],
            'URL' => $GLOBALS['host'].$url,
            'EMAIL' => $partner['USER']['EMAIL'],
        );
        CEvent::Send('REJECT_DEAL', 's1', $arEventFields);
    }
    if (in_array($noticeList['c_d']['ID'], $partner['PROPERTY_NOTICE_VALUE'])) {
        notice::addNotice($partner['USER']['ID'], 'd', 'Сделка аннулирована', $url, '#' . $deal['ID']);
    }
    if (in_array($noticeList['s_d']['ID'], $partner['PROPERTY_NOTICE_VALUE']) && $partner['PROPERTY_PHONE_VALUE']) {
        $phone = str_replace(array('+', ' ', '(', ')', '-'), "", $partner['PROPERTY_PHONE_VALUE']);
        notice::sendNoticeSMS($phone, 'Сделка аннулирована: '.$GLOBALS['host'].$url);
    }*/
}

if (sizeof($arRequests) > 0) {
    foreach ($arRequests as $request) {
        if ($request['ID'] > 0) {
            //новый остаток с учетом объема возвращенного из сделок
            $newRemains = $request['PROPERTY_REMAINS_VALUE'] + $returnVolume[$request['ID']];

            //если новый остаток оказался больше общего кол-ва, то приравниваем его к кол-ву
            if ($newRemains > $request['PROPERTY_VOLUME_VALUE']) {
                $newRemains = $request['PROPERTY_VOLUME_VALUE'];
            }

            $prop = array();
            $prop['REMAINS'] = array('VALUE' => $newRemains);

            //проверяем даты окончания активности
            //если дата окончания активности еще не прошла, то устанавливаем свойство Активность запроса: Y
            if (time() < strtotime($request['DATE_ACTIVE_TO'])){
                $prop['ACTIVE'] = rrsIblock::getPropListKey('client_request', 'ACTIVE', 'yes');
            }

            //меняем параметра запроса
            CIBlockElement::SetPropertyValuesEx(
                $request['ID'],
                $requestIBlockId,
                $prop
            );
        }
    }

    global $CACHE_MANAGER;
    $CACHE_MANAGER->ClearByTag("iblock_id_".$requestIBlockId);
}
?>