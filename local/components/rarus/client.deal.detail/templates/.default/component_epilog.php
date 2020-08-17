<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();?>
<?
$arResult = $templateData;

CModule::IncludeModule('iblock');


global $USER;
if($USER->GetID() == 55
    /* && $_SERVER['REMOTE_ADDR'] == '213.247.194.90' */
){
//    ob_start();
//    var_dump($_FILES['dkp']['tmp_name'] && $_FILES['dkp']['size'] > 0 && $_FILES['dkp']['error'] == 0);
//    var_dump($_FILES['dkp']['tmp_name']);
//    var_dump($_FILES['dkp']['size'] > 0);
//    var_dump($_FILES['dkp']['error'] == 0);
//    mail('somefor@yandex.ru', 'message subject test', ob_get_clean());
    /* exit; */
}

//загрузка ДКП, подписанного покупателем
if ($_FILES['dkp']['tmp_name'] && $_FILES['dkp']['size'] > 0 && $_FILES['dkp']['error'] == 0) {
    $oElement = new CIBlockElement();
    $arUpdateValues = $arUpdatePropertyValues = array();

    $arUpdateValues['IBLOCK_ID'] = rrsIblock::getIBlockId('deals_docs');
    $arUpdateValues['ACTIVE'] = 'Y';
    $arUpdateValues['NAME'] = 'Договор купли-продажи, подписанный покупателем';
    $arUpdateValues['CODE'] = 'dkp_client';

    $arUpdatePropertyValues['DEAL'] = $arResult['ID'];

    $copy  = copy($_FILES['dkp']['tmp_name'], $_SERVER["DOCUMENT_ROOT"]."/upload/tmp/".$_FILES['dkp']['name']);
    $file = $_SERVER["DOCUMENT_ROOT"]."/upload/tmp/".$_FILES['dkp']['name'];
    $arUpdatePropertyValues['FILE_PDF'] = CFile::MakeFileArray($file);

    $arUpdateValues['PROPERTY_VALUES'] = $arUpdatePropertyValues;

    if ($docId = $oElement->Add($arUpdateValues)) {

        if($USER->GetID() == 55){
            //mail('somefor@yandex.ru', 'message subject success', 'success');
        }

        log::addDealStatusLog($arResult['ID'], 'dkp_client', 'ДКП подписан покупателем');


        if($USER->GetID() == 55){
            //mail('somefor@yandex.ru', 'message subject success_log', 'success');
        }

        if ($arResult['PROPERTIES']['DELIVERY']['VALUE_XML_ID'] == 'c') {
            if ($arResult['REQUEST']['PAYMENT'] == 'pre') {
                if (sizeof(array_intersect(array('dtr_transport', 'ds_client', 'prepayment_send', 'ds_transport_transport'), array_keys($arResult['LOGS']))) > 3) {
                    log::addDealStatusLog($arResult['ID'], 'execution', 'Исполнение заказа');
                    deal::setStatus($arResult['ID'], 'execution');
                }
            }
            else {
                if (sizeof(array_intersect(array('dtr_transport', 'ds_client', 'ds_transport_transport'), array_keys($arResult['LOGS']))) > 2) {
                    log::addDealStatusLog($arResult['ID'], 'execution', 'Исполнение заказа');
                    deal::setStatus($arResult['ID'], 'execution');
                }
            }
        }
        else {
            if ($arResult['REQUEST']['PAYMENT'] == 'pre') {
                if (sizeof(array_intersect(array('ds_client', 'prepayment_send'), array_keys($arResult['LOGS']))) > 1) {
                    log::addDealStatusLog($arResult['ID'], 'execution', 'Исполнение заказа');
                    deal::setStatus($arResult['ID'], 'execution');
                }
            }
            else {
                if (in_array('ds_client', array_keys($arResult['LOGS']))) {
                    log::addDealStatusLog($arResult['ID'], 'execution', 'Исполнение заказа');
                    deal::setStatus($arResult['ID'], 'execution');
                }
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
            CEvent::Send('FARMER_DKP_INFO', 's1', $arEventFields);
        }
        if (in_array($noticeList['c_d']['ID'], $arResult['FARMER']['PROPERTY_NOTICE_VALUE'])) {
            notice::addNotice($arResult['FARMER']['USER']['ID'], 'd', 'Договор купли-продажи подписан', $url, '#' . $arResult['ID']);
        }
        if (in_array($noticeList['s_d']['ID'], $arResult['FARMER']['PROPERTY_NOTICE_VALUE']) && $arResult['FARMER']['PROPERTY_PHONE_VALUE']) {
            $phone = str_replace(array('+', ' ', '(', ')', '-'), "", $arResult['FARMER']['PROPERTY_PHONE_VALUE']);
            notice::sendNoticeSMS($phone, 'Договор купли-продажи по сделке подписан: '.$GLOBALS['host'].$url);
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
                CEvent::Send('FARMER_DKP_INFO', 's1', $arEventFields);
            }
            if (in_array($noticeList['c_d']['ID'], $farmerAgent['PROPERTY_NOTICE_VALUE'])) {
                notice::addNotice($farmerAgent['USER']['ID'], 'd', 'Договор купли-продажи подписан', $url, '#' . $arResult['ID']);
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
            CEvent::Send('PARTNER_DKP_INFO', 's1', $arEventFields);
        }
        if (in_array($noticeList['c_d']['ID'], $arResult['PARTNER']['PROPERTY_NOTICE_VALUE'])) {
            notice::addNotice($arResult['PARTNER']['USER']['ID'], 'd', 'Договор купли-продажи подписан', $url, '#' . $arResult['ID']);
        }
        if (in_array($noticeList['s_d']['ID'], $arResult['PARTNER']['PROPERTY_NOTICE_VALUE']) && $arResult['PARTNER']['PROPERTY_PHONE_VALUE']) {
            $phone = str_replace(array('+', ' ', '(', ')', '-'), "", $arResult['PARTNER']['PROPERTY_PHONE_VALUE']);
            notice::sendNoticeSMS($phone, 'Договор купли-продажи по сделке подписан: '.$GLOBALS['host'].$url);
        }

        LocalRedirect($arParams['SELF_URL']);
    }else{
        if($USER->GetID() == 55
            /* && $_SERVER['REMOTE_ADDR'] == '213.247.194.90' */
        ){
//            ob_start();
//            echo $oElement->LAST_ERROR;
//            p($arUpdateValues);
//            p($_FILES['dkp']);
//            mail('somefor@yandex.ru', 'message subject', ob_get_clean());
            /* exit; */
        }
    }
}

//загрузка ДС, подписанного покупателем
if ($_FILES['ds']['tmp_name'] && $_FILES['ds']['size'] > 0 && $_FILES['ds']['error'] == 0) {
    $oElement = new CIBlockElement();
    $arUpdateValues = $arUpdatePropertyValues = array();

    $arUpdateValues['IBLOCK_ID'] = rrsIblock::getIBlockId('deals_docs');
    $arUpdateValues['ACTIVE'] = 'Y';
    $arUpdateValues['NAME'] = 'Доп. соглашение, подписанное покупателем';
    $arUpdateValues['CODE'] = 'ds_client';

    $arUpdatePropertyValues['DEAL'] = $arResult['ID'];

    $copy  = copy($_FILES['ds']['tmp_name'], $_SERVER["DOCUMENT_ROOT"]."/upload/tmp/".$_FILES['ds']['name']);
    $file = $_SERVER["DOCUMENT_ROOT"]."/upload/tmp/".$_FILES['ds']['name'];
    $arUpdatePropertyValues['FILE_PDF'] = CFile::MakeFileArray($file);

    $arUpdateValues['PROPERTY_VALUES'] = $arUpdatePropertyValues;

    if ($docId = $oElement->Add($arUpdateValues)) {
        log::addDealStatusLog($arResult['ID'], 'ds_client', 'ДС к договору подписано покупателем');
        if ($arResult['PROPERTIES']['DELIVERY']['VALUE_XML_ID'] == 'c') {
            if ($arResult['REQUEST']['PAYMENT'] == 'pre') {
                if (sizeof(array_intersect(array('dtr_transport', 'dkp_client', 'prepayment_send', 'ds_transport_transport'), array_keys($arResult['LOGS']))) > 3) {
                    log::addDealStatusLog($arResult['ID'], 'execution', 'Исполнение заказа');
                    deal::setStatus($arResult['ID'], 'execution');
                }
            }
            else {
                if (sizeof(array_intersect(array('dtr_transport', 'dkp_client', 'ds_transport_transport'), array_keys($arResult['LOGS']))) > 2) {
                    log::addDealStatusLog($arResult['ID'], 'execution', 'Исполнение заказа');
                    deal::setStatus($arResult['ID'], 'execution');
                }
            }
        }
        else {
            if ($arResult['REQUEST']['PAYMENT'] == 'pre') {
                if (sizeof(array_intersect(array('dkp_client', 'prepayment_send'), array_keys($arResult['LOGS']))) > 1) {
                    log::addDealStatusLog($arResult['ID'], 'execution', 'Исполнение заказа');
                    deal::setStatus($arResult['ID'], 'execution');
                }
            }
            else {
                if (in_array('dkp_client', array_keys($arResult['LOGS']))) {
                    log::addDealStatusLog($arResult['ID'], 'execution', 'Исполнение заказа');
                    deal::setStatus($arResult['ID'], 'execution');
                }
            }
        }

        LocalRedirect($arParams['SELF_URL']);
    }
}

//загрузка реестров приемки СХП
if ($_REQUEST['loadReestr']) {
    if (is_array($_FILES['reestr']['tmp_name']) && sizeof($_FILES['reestr']['tmp_name']) > 0) {
        $files = array();
        foreach ($_FILES['reestr']['tmp_name'] as $key => $file) {
            if ($_FILES['reestr']['tmp_name'][$key] && $_FILES['reestr']['size'][$key] > 0 && $_FILES['reestr']['error'][$key] == 0) {
                $files[] = array(
                    'name' => $_FILES['reestr']['name'][$key],
                    'type' => $_FILES['reestr']['type'][$key],
                    'tmp_name' => $_FILES['reestr']['tmp_name'][$key],
                    'error' => $_FILES['reestr']['error'][$key],
                    'size' => $_FILES['reestr']['size'][$key],
                );
            }
        }

        if (is_array($files) && sizeof($files) > 0) {
            $oElement = new CIBlockElement();
            $ib = rrsIblock::getIBlockId('deals_docs');
            foreach ($files as $f) {
                $arUpdateValues = $arUpdatePropertyValues = array();

                $arUpdateValues['IBLOCK_ID'] = $ib;
                $arUpdateValues['ACTIVE'] = 'Y';
                $arUpdateValues['NAME'] = 'Реестры приемки СХП';
                $arUpdateValues['CODE'] = 'reestr';

                $arUpdatePropertyValues['DEAL'] = $arResult['ID'];

                $copy  = copy($f['tmp_name'], $_SERVER["DOCUMENT_ROOT"]."/upload/tmp/".$f['name']);
                $file = $_SERVER["DOCUMENT_ROOT"]."/upload/tmp/".$f['name'];
                $arUpdatePropertyValues['FILE_PDF'] = CFile::MakeFileArray($file);

                $arUpdateValues['PROPERTY_VALUES'] = $arUpdatePropertyValues;

                $oElement->Add($arUpdateValues);
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
                CEvent::Send('FARMER_REESTR', 's1', $arEventFields);
            }
            if (in_array($noticeList['c_d']['ID'], $arResult['FARMER']['PROPERTY_NOTICE_VALUE'])) {
                notice::addNotice($arResult['FARMER']['USER']['ID'], 'd', 'Реестры приемки СХП', $url, '#' . $arResult['ID']);
            }
            if (in_array($noticeList['s_d']['ID'], $arResult['FARMER']['PROPERTY_NOTICE_VALUE']) && $arResult['FARMER']['PROPERTY_PHONE_VALUE']) {
                $phone = str_replace(array('+', ' ', '(', ')', '-'), "", $arResult['FARMER']['PROPERTY_PHONE_VALUE']);
                notice::sendNoticeSMS($phone, 'Реестры приемки СХП: '.$GLOBALS['host'].$url);
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
                    CEvent::Send('FARMER_REESTR', 's1', $arEventFields);
                }
                if (in_array($noticeList['c_d']['ID'], $farmerAgent['PROPERTY_NOTICE_VALUE'])) {
                    notice::addNotice($farmerAgent['USER']['ID'], 'd', 'Реестры приемки СХП', $url, '#' . $arResult['ID']);
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
                CEvent::Send('PARTNER_REESTR', 's1', $arEventFields);
            }
            if (in_array($noticeList['c_d']['ID'], $arResult['PARTNER']['PROPERTY_NOTICE_VALUE'])) {
                notice::addNotice($arResult['PARTNER']['USER']['ID'], 'd', 'Реестры приемки СХП', $url, '#' . $arResult['ID']);
            }
            if (in_array($noticeList['s_d']['ID'], $arResult['PARTNER']['PROPERTY_NOTICE_VALUE']) && $arResult['PARTNER']['PROPERTY_PHONE_VALUE']) {
                $phone = str_replace(array('+', ' ', '(', ')', '-'), "", $arResult['PARTNER']['PROPERTY_PHONE_VALUE']);
                notice::sendNoticeSMS($phone, 'Реестры приемки СХП: '.$GLOBALS['host'].$url);
            }
        }
    }

    LocalRedirect($arParams['SELF_URL']);
}

//подтверждение загрузки всех реестров
if ($_REQUEST['confirm'] == 'Y') {
    log::addDealStatusLog($arResult['ID'], 'reestr', 'Реестры загружены');
    LocalRedirect($arParams['SELF_URL']);
}
?>