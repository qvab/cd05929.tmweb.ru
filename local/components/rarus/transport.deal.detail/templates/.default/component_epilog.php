<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();?>
<?
$arResult = $templateData;

CModule::IncludeModule('iblock');

//ДТР, подписанный транспортной компанией
if ($_FILES['dtr']['tmp_name'] && $_FILES['dtr']['size'] > 0 && $_FILES['dtr']['error'] == 0) {
    $oElement = new CIBlockElement();
    $arUpdateValues = $arUpdatePropertyValues = array();

    $arUpdateValues['IBLOCK_ID'] = rrsIblock::getIBlockId('deals_docs');
    $arUpdateValues['ACTIVE'] = 'Y';
    $arUpdateValues['NAME'] = 'Договор на транспортировку, подписанный транспортной компанией';
    $arUpdateValues['CODE'] = 'dtr_transport';

    $arUpdatePropertyValues['DEAL'] = $arResult['ID'];

    $copy  = copy($_FILES['dtr']['tmp_name'], $_SERVER["DOCUMENT_ROOT"]."/upload/tmp/".$_FILES['dtr']['name']);
    $file = $_SERVER["DOCUMENT_ROOT"]."/upload/tmp/".$_FILES['dtr']['name'];
    $arUpdatePropertyValues['FILE_PDF'] = CFile::MakeFileArray($file);

    $arUpdateValues['PROPERTY_VALUES'] = $arUpdatePropertyValues;

    if ($docId = $oElement->Add($arUpdateValues)) {
        log::addDealStatusLog($arResult['ID'], 'dtr_transport', 'ДТР подписан транспортной компанией');

        if ($arResult['REQUEST']['PAYMENT'] == 'pre') {
            if (sizeof(array_intersect(array('dkp_client', 'ds_client', 'prepayment_send', 'ds_transport_transport'), array_keys($arResult['LOGS']))) > 3) {
                log::addDealStatusLog($arResult['ID'], 'execution', 'Исполнение заказа');
                deal::setStatus($arResult['ID'], 'execution');
            }
        }
        else {
            if (sizeof(array_intersect(array('dkp_client', 'ds_client', 'ds_transport_transport'), array_keys($arResult['LOGS']))) > 2) {
                log::addDealStatusLog($arResult['ID'], 'execution', 'Исполнение заказа');
                deal::setStatus($arResult['ID'], 'execution');
            }
        }

        $noticeList = notice::getNoticeList();

        //уведомление для АП
        $url = '/farmer/deals/' . $arResult['ID'] . '/';
        if (in_array($noticeList['e_d']['ID'], $arResult['FARMER']['PROPERTY_NOTICE_VALUE'])) {
            $arEventFields = array(
                'CULTURE' => strip_tags($arResult['DISPLAY_PROPERTIES']['CULTURE']['DISPLAY_VALUE']),
                'ID' => $arResult['ID'],
                'URL' => $GLOBALS['host'].$url,
                'EMAIL' => $arResult['FARMER']['USER']['EMAIL'],
            );
            CEvent::Send('FARMER_DTR_INFO', 's1', $arEventFields);
        }
        if (in_array($noticeList['c_d']['ID'], $arResult['FARMER']['PROPERTY_NOTICE_VALUE'])) {
            notice::addNotice($arResult['FARMER']['USER']['ID'], 'd', 'Договор на перевозку подписан', $url, '#' . $arResult['ID']);
        }
        if (in_array($noticeList['s_d']['ID'], $arResult['FARMER']['PROPERTY_NOTICE_VALUE']) && $arResult['FARMER']['PROPERTY_PHONE_VALUE']) {
            $phone = str_replace(array('+', ' ', '(', ')', '-'), "", $arResult['FARMER']['PROPERTY_PHONE_VALUE']);
            notice::sendNoticeSMS($phone, 'Договор на перевозку подписан: '.$GLOBALS['host'].$url);
        }

        //уведомление для агента АП
        $agentObj = new agent();
        /*$farmerAgent = $agentObj->getProfileByFarmerID($arResult['FARMER']['USER']['ID']);
        $url = '/agent/deals/' . $arResult['ID'] . '/';
        if(isset($farmerAgent['DEALS_RIGHTS']) && $farmerAgent['DEALS_RIGHTS']){
            if (in_array($noticeList['e_d']['ID'], $farmerAgent['PROPERTY_NOTICE_VALUE'])) {
                $arEventFields = array(
                    'CULTURE' => strip_tags($arResult['DISPLAY_PROPERTIES']['CULTURE']['DISPLAY_VALUE']),
                    'ID' => $arResult['ID'],
                    'URL' => $GLOBALS['host'].$url,
                    'EMAIL' => $farmerAgent['USER']['EMAIL'],
                );
                CEvent::Send('FARMER_DTR_INFO', 's1', $arEventFields);
            }
            if (in_array($noticeList['c_d']['ID'], $farmerAgent['PROPERTY_NOTICE_VALUE'])) {
                notice::addNotice($farmerAgent['USER']['ID'], 'd', 'Договор на перевозку подписан', $url, '#' . $arResult['ID']);
            }
        }*/

        $url = '/partner/deals/' . $arResult['ID'] . '/';
        if (in_array($noticeList['e_d']['ID'], $arResult['PARTNER']['PROPERTY_NOTICE_VALUE'])) {
            $arEventFields = array(
                'CULTURE' => strip_tags($arResult['DISPLAY_PROPERTIES']['CULTURE']['DISPLAY_VALUE']),
                'ID' => $arResult['ID'],
                'URL' => $GLOBALS['host'].$url,
                'EMAIL' => $arResult['PARTNER']['USER']['EMAIL'],
            );
            CEvent::Send('PARTNER_DTR_INFO', 's1', $arEventFields);
        }
        if (in_array($noticeList['c_d']['ID'], $arResult['PARTNER']['PROPERTY_NOTICE_VALUE'])) {
            notice::addNotice($arResult['PARTNER']['USER']['ID'], 'd', 'Договор на перевозку подписан', $url, '#' . $arResult['ID']);
        }
        if (in_array($noticeList['s_d']['ID'], $arResult['PARTNER']['PROPERTY_NOTICE_VALUE']) && $arResult['PARTNER']['PROPERTY_PHONE_VALUE']) {
            $phone = str_replace(array('+', ' ', '(', ')', '-'), "", $arResult['PARTNER']['PROPERTY_PHONE_VALUE']);
            notice::sendNoticeSMS($phone, 'Договор на перевозку подписан: '.$GLOBALS['host'].$url);
        }

        LocalRedirect($arParams['SELF_URL']);
    }
}

//загрузка ДС транспортной компанией
if ($_FILES['ds_transport']['tmp_name'] && $_FILES['ds_transport']['size'] > 0 && $_FILES['ds_transport']['error'] == 0) {
    $oElement = new CIBlockElement();
    $arUpdateValues = $arUpdatePropertyValues = array();

    $arUpdateValues['IBLOCK_ID'] = rrsIblock::getIBlockId('deals_docs');
    $arUpdateValues['ACTIVE'] = 'Y';
    $arUpdateValues['NAME'] = 'Доп. соглашение к договору на транспортировку, подписанное транспортной компанией';
    $arUpdateValues['CODE'] = 'ds_transport_transport';

    $arUpdatePropertyValues['DEAL'] = $arResult['ID'];

    $copy  = copy($_FILES['ds_transport']['tmp_name'], $_SERVER["DOCUMENT_ROOT"]."/upload/tmp/".$_FILES['ds_transport']['name']);
    $file = $_SERVER["DOCUMENT_ROOT"]."/upload/tmp/".$_FILES['ds_transport']['name'];
    $arUpdatePropertyValues['FILE_PDF'] = CFile::MakeFileArray($file);

    $arUpdateValues['PROPERTY_VALUES'] = $arUpdatePropertyValues;

    if ($docId = $oElement->Add($arUpdateValues)) {
        log::addDealStatusLog($arResult['ID'], 'ds_transport_transport', 'ДC к договору на транспортировку подписано транспортной компанией');
        if ($arResult['REQUEST']['PAYMENT'] == 'pre') {
            if (sizeof(array_intersect(array('dkp_client', 'ds_client', 'prepayment_send', 'dtr_transport'), array_keys($arResult['LOGS']))) > 3) {
                log::addDealStatusLog($arResult['ID'], 'execution', 'Исполнение заказа');
                deal::setStatus($arResult['ID'], 'execution');
            }
        }
        else {
            if (sizeof(array_intersect(array('dkp_client', 'ds_client', 'dtr_transport'), array_keys($arResult['LOGS']))) > 2) {
                log::addDealStatusLog($arResult['ID'], 'execution', 'Исполнение заказа');
                deal::setStatus($arResult['ID'], 'execution');
            }
        }

        LocalRedirect($arParams['SELF_URL']);
    }
}

//выставление счета на оплату по договору транспортировки
if ($_REQUEST['payment_transport'] == 'Y') {
    log::addDealStatusLog($arResult['ID'], 'payment_transport_send', 'Отправлен счет на оплату по договору транспортировки');

    $noticeList = notice::getNoticeList();

    //уведомление для АП
    $url = '/farmer/deals/' . $arResult['ID'] . '/';
    if (in_array($noticeList['e_d']['ID'], $arResult['FARMER']['PROPERTY_NOTICE_VALUE'])) {
        $arEventFields = array(
            'CULTURE' => strip_tags($arResult['DISPLAY_PROPERTIES']['CULTURE']['DISPLAY_VALUE']),
            'ID' => $arResult['ID'],
            'URL' => $GLOBALS['host'].$url,
            'EMAIL' => $arResult['FARMER']['USER']['EMAIL'],
        );
        CEvent::Send('FARMER_PAYMENT_SEND', 's1', $arEventFields);
    }
    if (in_array($noticeList['c_d']['ID'], $arResult['FARMER']['PROPERTY_NOTICE_VALUE'])) {
        notice::addNotice($arResult['PROPERTIES']['FARMER']['VALUE'], 'd', 'Счета на оплату транспортировки', $url, '#' . $arResult['ID']);
    }
    if (in_array($noticeList['s_d']['ID'], $arResult['FARMER']['PROPERTY_NOTICE_VALUE']) && $arResult['FARMER']['PROPERTY_PHONE_VALUE']) {
        $phone = str_replace(array('+', ' ', '(', ')', '-'), "", $arResult['FARMER']['PROPERTY_PHONE_VALUE']);
        notice::sendNoticeSMS($phone, 'Счета на оплату транспортировки: '.$GLOBALS['host'].$url);
    }

    //уведомление для агента АП
    $agentObj = new agent();
    /*$farmerAgent = $agentObj->getProfileByFarmerID($arResult['FARMER']['USER']['ID']);
    $url = '/agent/deals/' . $arResult['ID'] . '/';
    if(isset($farmerAgent['DEALS_RIGHTS']) && $farmerAgent['DEALS_RIGHTS']){
        if (in_array($noticeList['e_d']['ID'], $farmerAgent['PROPERTY_NOTICE_VALUE'])) {
            $arEventFields = array(
                'CULTURE' => strip_tags($arResult['DISPLAY_PROPERTIES']['CULTURE']['DISPLAY_VALUE']),
                'ID' => $arResult['ID'],
                'URL' => $GLOBALS['host'].$url,
                'EMAIL' => $farmerAgent['USER']['EMAIL'],
            );
            CEvent::Send('FARMER_PAYMENT_SEND', 's1', $arEventFields);
        }
        if (in_array($noticeList['c_d']['ID'], $farmerAgent['PROPERTY_NOTICE_VALUE'])) {
            notice::addNotice($farmerAgent['USER']['ID'], 'd', 'Счета на оплату транспортировки', $url, '#' . $arResult['ID']);
        }
    }*/

    LocalRedirect($arParams['SELF_URL']);
}
?>