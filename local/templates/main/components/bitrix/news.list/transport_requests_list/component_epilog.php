<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();?>
<?
$arResult = $templateData;
if ($_REQUEST['deal'] > 0) {
    CModule::IncludeModule('iblock');
    foreach ($arResult['ITEMS'] as $arItem) {
        if ($arItem['ID'] == $_REQUEST['deal']) {
            $arDeal = $arItem;
            break;
        }
    }

    if ($arDeal['ID'] > 0) {
        $farmer = farmer::getProfile($arDeal['PROPERTIES']['FARMER']['VALUE'], true);

        CIBlockElement::SetPropertyValuesEx(
            $_REQUEST['deal'],
            rrsIblock::getIBlockId('deals_deals'),
            array(
                'TRANSPORT' => $USER->GetID()
            )
        );
        log::addDealStatusLog($_REQUEST['deal'], 'transport', 'Перевозчик найден');

        /*$transport = '';
        if (sizeof($arResult['CLIENT_WAREHOUSES_LIST'][$arDeal['PROPERTIES']['CLIENT_WAREHOUSE']['VALUE']]['TRANSPORT']) > 0) {
            foreach ($arResult['CLIENT_WAREHOUSES_LIST'][$arDeal['PROPERTIES']['CLIENT_WAREHOUSE']['VALUE']]['TRANSPORT'] as $val) {
                $transport .= $arResult['TRANSPORT_LIST'][$val]['NAME'] . '<br>';
            }
        }*/

        $culture = culture::getName($arDeal['PROPERTIES']['CULTURE']['VALUE']);

        $res = deal::getInfo4Transport($_REQUEST['deal']);
        model::transportCalculation($res['CENTER'], $res['DAYS']);

        $noticeList = notice::getNoticeList();
        $url = '/farmer/deals/' . $arDeal['ID'] . '/';

        //уведомление для АП
        if (in_array($noticeList['e_d']['ID'], $farmer['PROPERTY_NOTICE_VALUE'])) {
            $arEventFields = array(
                'CULTURE' => $culture['NAME'],
                'ID' => $arDeal['ID'],
                'URL' => $GLOBALS['host'].$url,
                'EMAIL' => $farmer['USER']['EMAIL'],
            );
            CEvent::Send('FARMER_TRANSPORT_FOUND', 's1', $arEventFields);
        }
        if (in_array($noticeList['c_d']['ID'], $farmer['PROPERTY_NOTICE_VALUE'])) {
            notice::addNotice($farmer['USER']['ID'], 'd', 'Найден перевозчик', $url, '#' . $arDeal['ID']);
        }
        if (in_array($noticeList['s_d']['ID'], $farmer['PROPERTY_NOTICE_VALUE']) && $farmer['PROPERTY_PHONE_VALUE']) {
            $phone = str_replace(array('+', ' ', '(', ')', '-'), "", $farmer['PROPERTY_PHONE_VALUE']);
            notice::sendNoticeSMS($phone, 'Найден перевозчик СХП по сделке: '.$GLOBALS['host'].$url);
        }

        //уведомление для агента АП
        $agentObj = new agent();
        /*$farmerAgent = $agentObj->getProfileByFarmerID($farmer['USER']['ID']);
        $url = '/agent/deals/' . $arDeal['ID'] . '/';
        if(isset($farmerAgent['DEALS_RIGHTS']) && $farmerAgent['DEALS_RIGHTS']){
            if (in_array($noticeList['e_d']['ID'], $farmerAgent['PROPERTY_NOTICE_VALUE'])) {
                $arEventFields = array(
                    'CULTURE' => $culture['NAME'],
                    'ID' => $arDeal['ID'],
                    'URL' => $GLOBALS['host'].$url,
                    'EMAIL' => $farmerAgent['USER']['EMAIL'],
                );
                CEvent::Send('FARMER_TRANSPORT_FOUND', 's1', $arEventFields);
            }
            if (in_array($noticeList['c_d']['ID'], $farmerAgent['PROPERTY_NOTICE_VALUE'])) {
                notice::addNotice($farmerAgent['USER']['ID'], 'd', 'Найден перевозчик', $url, '#' . $arDeal['ID']);
            }
        }*/

    }

    LocalRedirect('/transport/deals/' . $_REQUEST['deal'] . '/');
}
?>