<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();?>
<?
$arResult = $templateData;

$doDKP = false;

CModule::IncludeModule('iblock');

//отправка согласия организатору
if ($_REQUEST['agree'] == 'Y') {
    log::addDealStatusLog($arResult['ID'], 'order_deal', 'Отправлено согласие с условиями сделки');

    if ($arResult['PROPERTIES']['DELIVERY']['VALUE_XML_ID'] == 'c') {
        deal::setStatus($arResult['ID'], 'search');
        LocalRedirect($arParams['SELF_URL']);
    }
    else {
        $doDKP = true;
    }
}

//изменение тарифа для перевозки
if ($_REQUEST['tarif'] > 0) {

    if($_REQUEST['tarif'] < $arResult['TARIFF_MIN']) {
        $_REQUEST['tarif'] = $arResult['TARIFF_MIN'];
    }

    CIBlockElement::SetPropertyValuesEx(
        $arResult['ID'],
        $arParams["IBLOCK_ID"],
        array(
            'TARIF' => $_REQUEST['tarif'],
        )
    );

    $commission = rrsIblock::getConst('commission_transport');
    $tarif = (1. - 0.01 * $commission) * $_REQUEST['tarif'];

    $partner_id = farmer::getPartnerIdByFarmer($arResult['PROPERTIES']['FARMER']['VALUE']);
    //$transportList = transport::getLinkedTransportList($partner_id);

    // Список подтвержденных ТК
    $transportList = transport::getListConfirmedTransportCompanies();

    if (is_array($transportList) && sizeof($transportList) > 0) {
        $noticeList = notice::getNoticeList();
        $limit = rrsIblock::getConst('limit_transport');
        foreach ($transportList as $transport) {
            $autopark = transport::getAutoparkList($transport);
            if (is_array($autopark) && sizeof($autopark) > 0) {
                $min = 10000;
                foreach ($autopark as $base) {
                    $route = rrsIblock::getRoute($base['MAP'], $arResult['FARMER_WAREHOUSE']['MAP']);
                    if ($route < $min) {
                        $min = $route;
                        $minBase = $base['ID'];
                    }
                }

                if ($min <= $limit) {
                    $transportProfile = transport::getProfile($transport, true);

                    $url = '/transport/request/?id=' . $arResult['ID'];
                    if (in_array($noticeList['e_r']['ID'], $transportProfile['PROPERTY_NOTICE_VALUE'])) {
                        $arEventFields = array(
                            'CULTURE' => strip_tags($arResult['DISPLAY_PROPERTIES']['CULTURE']['DISPLAY_VALUE']),
                            'ID' => $arResult['ID'],
                            'URL' => $GLOBALS['host'].$url,
                            'VOLUME' => number_format($arResult['PROPERTIES']['VOLUME']['VALUE'], 0, '.', ' '),
                            'TARIF' => $tarif . ' руб/т',
                            'ROUTE' => $arResult['PROPERTIES']['ROUTE']['VALUE'],
                            'EMAIL' => $transportProfile['USER']['EMAIL'],
                        );
                        CEvent::Send('TRANSPORT_NEW_REQUEST', 's1', $arEventFields);
                    }
                    if (in_array($noticeList['c_r']['ID'], $transportProfile['PROPERTY_NOTICE_VALUE'])) {
                        notice::addNotice($transportProfile['USER']['ID'], 'r', 'Новый запрос на перевозку', $url, '#' . $arResult['ID']);
                    }
                    if (in_array($noticeList['s_r']['ID'], $transportProfile['PROPERTY_NOTICE_VALUE']) && $transportProfile['PROPERTY_PHONE_VALUE']) {
                        $phone = str_replace(array('+', ' ', '(', ')', '-'), "", $transportProfile['PROPERTY_PHONE_VALUE']);
                        notice::sendNoticeSMS($phone, 'Поступил новый запрос на перевозку: '.$GLOBALS['host'].$url);
                    }
                }
            }
        }
    }

    LocalRedirect($arParams['SELF_URL']);
}

//изменение способа доставки
if ($_REQUEST['delivery'] == 'b') {
    CIBlockElement::SetPropertyValuesEx(
        $arResult['ID'],
        $arParams["IBLOCK_ID"],
        array(
            'TARIF' => '',
            'DELIVERY' => rrsIblock::getPropListKey('deals_deals', 'DELIVERY', 'b')
        )
    );

    $noticeList = notice::getNoticeList();
    $arResult['PROPERTIES']['DELIVERY']['VALUE_XML_ID'] = 'b';
    $doDKP = true;
}

if ($_REQUEST['agree_transport'] == 'Y') {
    log::addDealStatusLog($arResult['ID'], 'order_transport', 'Отправлено согласие с условиями перевозки');

    $doDKP = true;
}

if ($doDKP) {
    //наименование культуры
    $culture = culture::getName($arResult['PROPERTIES']['CULTURE']['VALUE']);

    //объем в тоннах
    $volumeTn = $arResult['PROPERTIES']['VOLUME']['VALUE'];

    //объем в кг
    $volumeKg = 1000 * $volumeTn;

    //базисная цена в руб/т
    $basePriceTn = $arResult['PROPERTIES']['BASE_PRICE']['VALUE'];

    //базисная цена в руб/кг
    $basePriceKg = 0.001 * round($basePriceTn, 0);

    //стоимость товара в руб
    $cost = $volumeTn * $basePriceTn;

    //таблицы с параметрами качества
    $table = new deal;
    $arTableHtml = $table->formDumpTable($arResult['PROPERTIES']['CULTURE']['VALUE'], $arResult['PROPERTIES']['REQUEST']['VALUE']);

    //профили участников сделки
    $client = client::getFullProfile($arResult['PROPERTIES']['CLIENT']['VALUE']);
    $farmer = farmer::getFullProfile($arResult['PROPERTIES']['FARMER']['VALUE']);
    $partner = partner::getFullProfile($arResult['PROPERTIES']['PARTNER']['VALUE']);

    //информация о ДОУ между покупателем и организатором
    $douInfo = partner::getClientDouInfo(
        $arResult['PROPERTIES']['PARTNER']['VALUE'],
        $arResult['PROPERTIES']['CLIENT']['VALUE']
    );

    if ($douInfo['ID'] > 0) {

        $fca_dap = ($arResult['REQUEST']['NEED_DELIVERY'] == 'Y')?'cpt':'fca';
        $docTemplate = 'dkp2'.$fca_dap;

        $docIdDkp = deal::createDocument(
            $arResult['ID'],
            $docTemplate,
            'dkp',
            'Договор купли-продажи',
            array(
                "#DEAL_NUM#" => "#DOC_ID#",
                "#DEAL_DATE#" => date('d.m.Y'),
                "#DOU_NUM#" => $douInfo['PROPERTY_DOU_NUM_VALUE'],
                "#DOU_DATE#" => date('d.m.Y', strtotime($douInfo['PROPERTY_DOU_DATE_VALUE'])),
                "#CULTURE#" => $culture['CHEGO'],
                "#VOLUME#" => number_format($volumeKg, 0, ',', ' '),
                "#PRICE#" => price2Str($basePriceKg),
                "#BASE_TABLE#" => $arTableHtml['BASE_TABLE'],
                "#DUMP_TABLE#" => $arTableHtml['DUMP_TABLE'],
                "#CLIENT_COMPANY#" => $client['COMPANY'],
                "#CLIENT_POST#" => $client['PROPERTY_POST_VALUE'],
                "#CLIENT_NAME#" => $client['PROPERTY_FIO_SIGN_VALUE'],
                "#CLIENT_FOUND#" => $client['PROPERTY_FOUNDATION_VALUE'],
                "#CLIENT_WH_ADDRESS#" => $arResult['CLIENT_WAREHOUSE']['ADDRESS'],
                "#CLIENT_POST_ADDRESS#" => $client['PROPERTY_POST_ADRESS_VALUE'],
                "#CLIENT_EMAIL#" => $client['USER']['EMAIL'],
                "#CLIENT_ADDRESS#" => $client['ADDRESS'],
                "#CLIENT_INN#" => 'ИНН '.$client['PROPERTY_INN_VALUE'],
                "#CLIENT_KPP#" => $client['KPP'],
                "#CLIENT_OGRN#" => $client['OGRN'],
                "#CLIENT_OKPO#" => 'ОКПО '.$client['PROPERTY_OKPO_VALUE'],
                "#CLIENT_RS#" => $client['PROPERTY_RASCH_SCHET_VALUE'],
                "#CLIENT_BANK#" => $client['PROPERTY_BANK_VALUE'],
                "#CLIENT_CS#" => 'к/с '.$client['PROPERTY_KOR_SCHET_VALUE'],
                "#CLIENT_BIK#" => 'БИК '.$client['PROPERTY_BIK_VALUE'],
                "#FARMER_COMPANY#" => $farmer['COMPANY'],
                "#FARMER_POST#" => $farmer['PROPERTY_POST_VALUE'],
                "#FARMER_NAME#" => $farmer['PROPERTY_FIO_SIGN_VALUE'],
                "#FARMER_FOUND#" => $farmer['PROPERTY_FOUNDATION_VALUE'],
                "#FARMER_WH_ADDRESS#" => $arResult['FARMER_WAREHOUSE']['ADDRESS'],
                "#FARMER_POST_ADDRESS#" => $farmer['PROPERTY_POST_ADRESS_VALUE'],
                "#FARMER_EMAIL#" => $farmer['USER']['EMAIL'],
                "#FARMER_ADDRESS#" => $farmer['ADDRESS'],
                "#FARMER_INN#" => 'ИНН '.$farmer['PROPERTY_INN_VALUE'],
                "#FARMER_KPP#" => $farmer['KPP'],
                "#FARMER_OGRN#" => $farmer['OGRN'],
                "#FARMER_OKPO#" => 'ОКПО '.$farmer['PROPERTY_OKPO_VALUE'],
                "#FARMER_RS#" => $farmer['PROPERTY_RASCH_SCHET_VALUE'],
                "#FARMER_BANK#" => $farmer['PROPERTY_BANK_VALUE'],
                "#FARMER_CS#" => 'к/с '.$farmer['PROPERTY_KOR_SCHET_VALUE'],
                "#FARMER_BIK#" => 'БИК '.$farmer['PROPERTY_BIK_VALUE'],
                "#PARTNER_COMPANY#" => $partner['COMPANY'],
                "#PARTNER_POST#" => $partner['PROPERTY_POST_VALUE'],
                "#PARTNER_NAME#" => $partner['PROPERTY_FIO_SIGN_VALUE'],
                "#PARTNER_FOUND#" => $partner['PROPERTY_FOUNDATION_VALUE'],
                "#PARTNER_POST_ADDRESS#" => $partner['PROPERTY_POST_ADRESS_VALUE'],
                "#PARTNER_EMAIL#" => $partner['USER']['EMAIL'],
                "#PARTNER_ADDRESS#" => $partner['ADDRESS'],
                "#PARTNER_INN#" => 'ИНН '.$partner['PROPERTY_INN_VALUE'],
                "#PARTNER_KPP#" => $partner['KPP'],
                "#PARTNER_OGRN#" => $partner['OGRN'],
                "#PARTNER_RS#" => $partner['PROPERTY_RASCH_SCHET_VALUE'],
                "#PARTNER_BANK#" => $partner['PROPERTY_BANK_VALUE'],
                "#PARTNER_CS#" => 'к/с '.$partner['PROPERTY_KOR_SCHET_VALUE'],
                "#PARTNER_BIK#" => 'БИК '.$partner['PROPERTY_BIK_VALUE'],
            ),
            'client_profile'
        );

        if ($docIdDkp > 0) {
            log::addDealStatusLog($arResult['ID'], 'dkp_ready', 'Договор купли-продажи сформирован');
        }

        $fca_dap = ($arResult['REQUEST']['NEED_DELIVERY'] == 'Y')?'CPT/доставка товара автотранспортом на склад':'FCA/погружено в автотранспорт на складе';

        //ДКП
        $orderDkp = deal::getDocument($arResult['ID'], 'dkp');

        //вознаграждение организатора
        //процент комиссии
        $commission = rrsIblock::getConst('commission');

        //комиссия в руб
        $commissionRub = $commission * $cost / (100 - $commission);
        //$commissionRub = 0.01 * $commission * $cost;

        $docIdDs = deal::createDocument(
            $arResult['ID'],
            'ds',
            'ds',
            'Доп. соглашение к договору купли-продажи',
            array(
                "#DS_NUM#" => "#DOC_ID#",
                "#DS_DATE#" => date('d.m.Y'),
                "#DOU_NUM#" => $douInfo['PROPERTY_DOU_NUM_VALUE'],
                "#DOU_DATE#" => date('d.m.Y', strtotime($douInfo['PROPERTY_DOU_DATE_VALUE'])),
                "#DEAL_NUM#" => $orderDkp['ID'],
                "#DEAL_DATE#" => date('d.m.Y', strtotime($orderDkp['DATE_CREATE'])),
                "#CULTURE#" => $culture['CHEGO'],
                "#VOLUME#" => number_format($volumeKg, 0, ',', ' '),
                "#PRICE#" => price2Str($basePriceKg),
                "#COMMISSION_RUB#" => number_format($commissionRub, 2, '.', ' ') . ' рублей',
                "#FCA_DAP#" => $fca_dap,
                "#RANG_TABLE#" => $arTableHtml['RANG_TABLE'],
                "#CLIENT_COMPANY#" => $client['COMPANY'],
                "#CLIENT_POST#" => $client['PROPERTY_POST_VALUE'],
                "#CLIENT_NAME#" => $client['PROPERTY_FIO_SIGN_VALUE'],
                "#CLIENT_FOUND#" => $client['PROPERTY_FOUNDATION_VALUE'],
                "#CLIENT_POST_ADDRESS#" => $client['PROPERTY_POST_ADRESS_VALUE'],
                "#CLIENT_EMAIL#" => $client['USER']['EMAIL'],
                "#CLIENT_ADDRESS#" => $client['ADDRESS'],
                "#CLIENT_INN#" => 'ИНН '.$client['PROPERTY_INN_VALUE'],
                "#CLIENT_KPP#" => $client['KPP'],
                "#CLIENT_OGRN#" => $client['OGRN'],
                "#CLIENT_RS#" => $client['PROPERTY_RASCH_SCHET_VALUE'],
                "#CLIENT_BANK#" => $client['PROPERTY_BANK_VALUE'],
                "#CLIENT_CS#" => 'к/с '.$client['PROPERTY_KOR_SCHET_VALUE'],
                "#CLIENT_BIK#" => 'БИК '.$client['PROPERTY_BIK_VALUE'],
                "#FARMER_COMPANY#" => $farmer['COMPANY'],
                "#FARMER_POST#" => $farmer['PROPERTY_POST_VALUE'],
                "#FARMER_NAME#" => $farmer['PROPERTY_FIO_SIGN_VALUE'],
                "#FARMER_FOUND#" => $farmer['PROPERTY_FOUNDATION_VALUE'],
                "#FARMER_POST_ADDRESS#" => $farmer['PROPERTY_POST_ADRESS_VALUE'],
                "#FARMER_EMAIL#" => $farmer['USER']['EMAIL'],
                "#FARMER_ADDRESS#" => $farmer['ADDRESS'],
                "#FARMER_INN#" => 'ИНН '.$farmer['PROPERTY_INN_VALUE'],
                "#FARMER_KPP#" => $farmer['KPP'],
                "#FARMER_OGRN#" => $farmer['OGRN'],
                "#FARMER_OKPO#" => 'ОКПО '.$farmer['PROPERTY_OKPO_VALUE'],
                "#FARMER_RS#" => $farmer['PROPERTY_RASCH_SCHET_VALUE'],
                "#FARMER_BANK#" => $farmer['PROPERTY_BANK_VALUE'],
                "#FARMER_CS#" => 'к/с '.$farmer['PROPERTY_KOR_SCHET_VALUE'],
                "#FARMER_BIK#" => 'БИК '.$farmer['PROPERTY_BIK_VALUE'],
                "#PARTNER_COMPANY#" => $partner['COMPANY'],
                "#PARTNER_POST#" => $partner['PROPERTY_POST_VALUE'],
                "#PARTNER_NAME#" => $partner['PROPERTY_FIO_SIGN_VALUE'],
                "#PARTNER_FOUND#" => $partner['PROPERTY_FOUNDATION_VALUE'],
                "#PARTNER_POST_ADDRESS#" => $partner['PROPERTY_POST_ADRESS_VALUE'],
                "#PARTNER_EMAIL#" => $partner['USER']['EMAIL'],
                "#PARTNER_ADDRESS#" => $partner['ADDRESS'],
                "#PARTNER_INN#" => 'ИНН '.$partner['PROPERTY_INN_VALUE'],
                "#PARTNER_KPP#" => $partner['KPP'],
                "#PARTNER_OGRN#" => $partner['OGRN'],
                "#PARTNER_RS#" => $partner['PROPERTY_RASCH_SCHET_VALUE'],
                "#PARTNER_BANK#" => $partner['PROPERTY_BANK_VALUE'],
                "#PARTNER_CS#" => 'к/с '.$partner['PROPERTY_KOR_SCHET_VALUE'],
                "#PARTNER_BIK#" => 'БИК '.$partner['PROPERTY_BIK_VALUE'],
            ),
            'client_profile'
        );

        if ($docIdDs > 0) {
            log::addDealStatusLog($arResult['ID'], 'ds_ready', 'Доп. соглашение к договору купли-продажи сформировано');
        }

        if ($docIdDkp > 0) {
            $url = '/partner/deals/' . $arResult['ID'] . '/';
            if (in_array($noticeList['e_d']['ID'], $arResult['PARTNER']['PROPERTY_NOTICE_VALUE'])) {
                $arEventFields = array(
                    'CULTURE' => strip_tags($arResult['DISPLAY_PROPERTIES']['CULTURE']['DISPLAY_VALUE']),
                    'ID' => $arResult['ID'],
                    'URL' => $GLOBALS['host'].$url,
                    'EMAIL' => $arResult['PARTNER']['USER']['EMAIL'],
                );
                CEvent::Send('PARTNER_DKP', 's1', $arEventFields);
            }
            if (in_array($noticeList['c_d']['ID'], $arResult['PARTNER']['PROPERTY_NOTICE_VALUE'])) {
                notice::addNotice($arResult['PARTNER']['USER']['ID'], 'd', 'Договор купли-продажи', $url, '#' . $arResult['ID']);
            }
            if (in_array($noticeList['s_d']['ID'], $arResult['PARTNER']['PROPERTY_NOTICE_VALUE']) && $arResult['PARTNER']['PROPERTY_PHONE_VALUE']) {
                $phone = str_replace(array('+', ' ', '(', ')', '-'), "", $arResult['PARTNER']['PROPERTY_PHONE_VALUE']);
                notice::sendNoticeSMS($phone, 'Договор купли-продажи по сделке: '.$GLOBALS['host'].$url);
            }
        }
    }

    if ($arResult['PROPERTIES']['DELIVERY']['VALUE_XML_ID'] == 'c') {
        //профиль перевозчика
        $transport = transport::getFullProfile($arResult['PROPERTIES']['TRANSPORT']['VALUE']);

        //общая стоимость груза ___ руб. ___ коп
        $arCost = explode('.', number_format($cost, 2, '.', ' '));
        $costStr = $arCost[0] . ' руб. ' . $arCost[1] . ' коп';

        //тариф на перевозку, руб/т
        $tarif = $arResult['PROPERTIES']['TARIF']['VALUE'];

        //стоимость перевозки, руб = руб/т * т
        $costTrantsport = $arResult['PROPERTIES']['TARIF']['VALUE'] * $arResult['PROPERTIES']['VOLUME']['VALUE'];

        //плата за перевозку ___ руб. ___ коп
        $arCostTransport = explode('.', number_format($costTrantsport, 2, '.', ' '));
        $costTrantsportStr = $arCostTransport[0] . ' руб. ' . $arCostTransport[1] . ' коп';

        $transportList = rrsIblock::getElementList(rrsIblock::getIBlockId('transport_type'));
        $transportType = array();
        if (sizeof($arResult['CLIENT_WAREHOUSE']['TRANSPORT']) > 0) {
            foreach ($arResult['CLIENT_WAREHOUSE']['TRANSPORT'] as $val) {
                $transportType[] = $transportList[$val]['NAME'];
            }
        }
        $transportType = implode(' ,', $transportType);

        //информация о ДОУ между перевозчиком и организатором
        $douInfo = partner::getTransportDouInfo(
            $arResult['PROPERTIES']['PARTNER']['VALUE'],
            $arResult['PROPERTIES']['TRANSPORT']['VALUE']
        );

        if ($douInfo['ID'] > 0) {

            $docIdDtr = deal::createDocument(
                $arResult['ID'],
                'dtr',
                'dtr',
                'Договор на транспортировку',
                array(
                    "#DTR_NUM#" => "#DOC_ID#",
                    "#DTR_DATE#" => date('d.m.Y'),
                    "#CULTURE_NAME#" => $culture['NAME'],
                    "#VOLUME#" => number_format($volumeKg, 0, ',', ' '),
                    "#COST#" => $costStr,
                    "#COST_TRANSPORT#" => $costTrantsportStr,
                    "#TRANSPORT#" => $transportType,
                    "#CLIENT_COMPANY#" => $client['COMPANY'],
                    "#CLIENT_WH_ADDRESS#" => $arResult['CLIENT_WAREHOUSE']['ADDRESS'],
                    "#FARMER_COMPANY#" => $farmer['COMPANY'],
                    "#FARMER_POST#" => $farmer['PROPERTY_POST_VALUE'],
                    "#FARMER_NAME#" => $farmer['PROPERTY_FIO_SIGN_VALUE'],
                    "#FARMER_ADDRESS#" => $farmer['ADDRESS'],
                    "#FARMER_WH_ADDRESS#" => $arResult['FARMER_WAREHOUSE']['ADDRESS'],
                    "#FARMER_INN#" => 'ИНН '.$farmer['PROPERTY_INN_VALUE'],
                    "#FARMER_KPP#" => $farmer['KPP'],
                    "#FARMER_OGRN#" => $farmer['OGRN'],
                    "#FARMER_OKPO#" => 'ОКПО '.$farmer['PROPERTY_OKPO_VALUE'],
                    "#FARMER_RS#" => $farmer['PROPERTY_RASCH_SCHET_VALUE'],
                    "#FARMER_BANK#" => $farmer['PROPERTY_BANK_VALUE'],
                    "#FARMER_CS#" => 'к/с '.$farmer['PROPERTY_KOR_SCHET_VALUE'],
                    "#FARMER_BIK#" => 'БИК '.$farmer['PROPERTY_BIK_VALUE'],
                    "#TRANSPORT_COMPANY#" => $transport['COMPANY'],
                    "#TRANSPORT_POST#" => $transport['PROPERTY_POST_VALUE'],
                    "#TRANSPORT_NAME#" => $transport['PROPERTY_FIO_SIGN_VALUE'],
                    "#TRANSPORT_ADDRESS#" => $transport['ADDRESS'],
                    "#TRANSPORT_INN#" => 'ИНН ' . $transport['PROPERTY_INN_VALUE'],
                    "#TRANSPORT_KPP#" => $transport['KPP'],
                    "#TRANSPORT_OGRN#" => $transport['OGRN'],
                    "#TRANSPORT_OKPO#" => 'ОКПО ' . $transport['PROPERTY_OKPO_VALUE'],
                    "#TRANSPORT_RS#" => $transport['PROPERTY_RASCH_SCHET_VALUE'],
                    "#TRANSPORT_BANK#" => $transport['PROPERTY_BANK_VALUE'],
                    "#TRANSPORT_CS#" => 'к/с '.$transport['PROPERTY_KOR_SCHET_VALUE'],
                    "#TRANSPORT_BIK#" => 'БИК '.$transport['PROPERTY_BIK_VALUE'],
                )
            );

            if ($docIdDtr > 0) {
                log::addDealStatusLog($arResult['ID'], 'dtr_ready', 'Договор на транспортировку сформирован');
            }

            //вознаграждение организатора
            //процент комиссии на перевозку
            $commissionTransport = rrsIblock::getConst('commission_transport');

            //комиссия в руб
            $commissionTransportRub = 0.01 * $commissionTransport * $costTrantsport;

            $docIdDstr = deal::createDocument(
                $arResult['ID'],
                'ds_transport',
                'ds_transport',
                'Доп. соглашение к договору на транспортировку',
                array(
                    "#DSTR_NUM#" => "#DOC_ID#",
                    "#DSTR_DATE#" => date('d.m.Y'),
                    "#DOUTR_NUM#" => $douInfo['PROPERTY_DOU_NUM_VALUE'],
                    "#DOUTR_DATE#" => date('d.m.Y', strtotime($douInfo['PROPERTY_DOU_DATE_VALUE'])),
                    "#CULTURE#" => $culture['CHEGO'],
                    "#CULTURE_NAME#" => $culture['NAME'],
                    "#VOLUME#" => number_format($volumeKg, 0, ',', ' '),
                    "#COST#" => $costStr,
                    "#COST_TRANSPORT#" => $costTrantsportStr,
                    "#COMMISSION_TR_RUB#" => number_format($commissionTransportRub, 2, '.', ' ').' рублей',
                    "#TRANSPORT#" => $transportType,
                    "#CLIENT_COMPANY#" => $client['COMPANY'],
                    "#CLIENT_WH_ADDRESS#" => $arResult['CLIENT_WAREHOUSE']['ADDRESS'],
                    "#FARMER_COMPANY#" => $farmer['COMPANY'],
                    "#FARMER_WH_ADDRESS#" => $arResult['FARMER_WAREHOUSE']['ADDRESS'],
                    "#PARTNER_COMPANY#" => $partner['COMPANY'],
                    "#PARTNER_POST#" => $partner['PROPERTY_POST_VALUE'],
                    "#PARTNER_NAME#" => $partner['PROPERTY_FIO_SIGN_VALUE'],
                    "#PARTNER_FOUND#" => $partner['PROPERTY_FOUNDATION_VALUE'],
                    "#PARTNER_ADDRESS#" => $partner['ADDRESS'],
                    "#PARTNER_EMAIL#" => $partner['USER']['EMAIL'],
                    "#PARTNER_INN#" => 'ИНН '.$partner['PROPERTY_INN_VALUE'],
                    "#PARTNER_KPP#" => $partner['KPP'],
                    "#PARTNER_OGRN#" => $partner['OGRN'],
                    "#PARTNER_RS#" => $partner['PROPERTY_RASCH_SCHET_VALUE'],
                    "#PARTNER_BANK#" => $partner['PROPERTY_BANK_VALUE'],
                    "#PARTNER_CS#" => 'к/с '.$partner['PROPERTY_KOR_SCHET_VALUE'],
                    "#PARTNER_BIK#" => 'БИК '.$partner['PROPERTY_BIK_VALUE'],
                    "#TRANSPORT_COMPANY#" => $transport['COMPANY'],
                    "#TRANSPORT_POST#" => $transport['PROPERTY_POST_VALUE'],
                    "#TRANSPORT_NAME#" => $transport['PROPERTY_FIO_SIGN_VALUE'],
                    "#TRANSPORT_FOUND#" => $transport['PROPERTY_FOUNDATION_VALUE'],
                    "#TRANSPORT_ADDRESS#" => $transport['ADDRESS'],
                    "#TRANSPORT_EMAIL#" => $transport['USER']['EMAIL'],
                    "#TRANSPORT_INN#" => 'ИНН '.$transport['PROPERTY_INN_VALUE'],
                    "#TRANSPORT_KPP#" => $transport['KPP'],
                    "#TRANSPORT_OGRN#" => $transport['OGRN'],
                    "#TRANSPORT_RS#" => $transport['PROPERTY_RASCH_SCHET_VALUE'],
                    "#TRANSPORT_BANK#" => $transport['PROPERTY_BANK_VALUE'],
                    "#TRANSPORT_CS#" => 'к/с '.$transport['PROPERTY_KOR_SCHET_VALUE'],
                    "#TRANSPORT_BIK#" => 'БИК '.$transport['PROPERTY_BIK_VALUE'],
                )
            );

            if ($docIdDstr > 0) {
                log::addDealStatusLog($arResult['ID'], 'ds_transport_ready', 'Доп. соглашение к договору на транспортировку сформировано');
            }

            if ($docIdDtr > 0) {
                $url = '/partner/deals/' . $arResult['ID'] . '/';
                if (in_array($noticeList['e_d']['ID'], $arResult['PARTNER']['PROPERTY_NOTICE_VALUE'])) {
                    $arEventFields = array(
                        'CULTURE' => strip_tags($arResult['DISPLAY_PROPERTIES']['CULTURE']['DISPLAY_VALUE']),
                        'ID' => $arResult['ID'],
                        'URL' => $GLOBALS['host'].$url,
                        'EMAIL' => $arResult['PARTNER']['USER']['EMAIL'],
                    );
                    CEvent::Send('PARTNER_DTR', 's1', $arEventFields);
                }
                if (in_array($noticeList['c_d']['ID'], $arResult['PARTNER']['PROPERTY_NOTICE_VALUE'])) {
                    notice::addNotice($arResult['PARTNER']['USER']['ID'], 'd', 'Договор на транспортировку', $url, '#' . $arResult['ID']);
                }
                if (in_array($noticeList['s_d']['ID'], $arResult['PARTNER']['PROPERTY_NOTICE_VALUE']) && $arResult['PARTNER']['PROPERTY_PHONE_VALUE']) {
                    $phone = str_replace(array('+', ' ', '(', ')', '-'), "", $arResult['PARTNER']['PROPERTY_PHONE_VALUE']);
                    notice::sendNoticeSMS($phone, 'Договор на транспортировку по сделке: '.$GLOBALS['host'].$url);
                }
            }
        }
    }

    deal::setStatus($arResult['ID'], 'dkp');
    LocalRedirect($arParams['SELF_URL']);
}
?>