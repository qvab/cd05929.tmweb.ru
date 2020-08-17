<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();?>
<?
$arResult = $templateData;

CModule::IncludeModule('iblock');

//аннулирование сделки
if ($_REQUEST['reject']) {
    log::addDealStatusLog($arResult['ID'], 'reject', 'Сделка аннулирована');
    deal::setStatus($arResult['ID'], 'reject');
    LocalRedirect($arParams['SELF_URL']);
}

//формирование документов
if ($_REQUEST['doc'] == 'dkp' || $_REQUEST['doc'] == 'ds' || $_REQUEST['doc'] == 'dtr' || $_REQUEST['doc'] == 'ds_transport') {
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
        if ($_REQUEST['doc'] == 'dkp') {
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
        }
        elseif ($_REQUEST['doc'] == 'ds') {
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
        }
    }

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
        if ($_REQUEST['doc'] == 'dtr') {
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
        }
        elseif ($_REQUEST['doc'] == 'ds_transport') {
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
        }
    }

    LocalRedirect($arParams['SELF_URL']);
}

//сохранение номера и даты ДОУ с покупателем
if ($_REQUEST['saveDouInfo']) {
    $oElement = new CIBlockElement();
    $arUpdateValues = $arUpdatePropertyValues = array();

    $arUpdateValues['IBLOCK_ID'] = rrsIblock::getIBlockId('partner_client_dou');
    $arUpdateValues['ACTIVE'] = 'Y';
    $arUpdateValues['NAME'] = 'ДОУ';
    $arUpdatePropertyValues['DOU_NUM'] = $_REQUEST['dou_num'];
    $arUpdatePropertyValues['DOU_DATE'] = $_REQUEST['dou_date'];
    $arUpdatePropertyValues['PARTNER'] = $arResult['PROPERTIES']['PARTNER']['VALUE'];
    $arUpdatePropertyValues['CLIENT'] = $arResult['PROPERTIES']['CLIENT']['VALUE'];
    $arUpdateValues['PROPERTY_VALUES'] = $arUpdatePropertyValues;

    if ($elId = $oElement->Add($arUpdateValues)) {

        //информация о ДОУ между покупателем и организатором
        $douInfo = partner::getClientDouInfo(
            $arResult['PROPERTIES']['PARTNER']['VALUE'],
            $arResult['PROPERTIES']['CLIENT']['VALUE']
        );
        if ($douInfo['ID'] > 0) {
            $fca_dap = ($arResult['REQUEST']['NEED_DELIVERY'] == 'Y')?'cpt':'fca';
            $docTemplate = 'dkp2'.$fca_dap;

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

            $docId = deal::createDocument(
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

            if ($docId > 0) {
                log::addDealStatusLog($arResult['ID'], 'dkp_ready', 'Договор купли-продажи сформирован');

                $fca_dap = ($arResult['REQUEST']['NEED_DELIVERY'] == 'Y')?'CPT/доставка товара автотранспортом на склад':'FCA/погружено в автотранспорт на складе';

                //ДКП
                $orderDkp = deal::getDocument($arResult['ID'], 'dkp');

                //вознаграждение организатора
                //процент комиссии
                $commission = rrsIblock::getConst('commission');

                //комиссия в руб
                $commissionRub = $commission * $cost / (100 - $commission);
                //$commissionRub = 0.01 * $commission * $cost;

                $docId = deal::createDocument(
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

                log::addDealStatusLog($arResult['ID'], 'ds_ready', 'Доп. соглашение к ДКП сформировано');
            }
        }
        LocalRedirect($arParams['SELF_URL']);
    }
}

//загрузка ДКП, подписанного поставщиком
if ($_FILES['dkp']['tmp_name'] && $_FILES['dkp']['size'] > 0 && $_FILES['dkp']['error'] == 0) {
    $oElement = new CIBlockElement();
    $arUpdateValues = $arUpdatePropertyValues = array();

    $arUpdateValues['IBLOCK_ID'] = rrsIblock::getIBlockId('deals_docs');
    $arUpdateValues['ACTIVE'] = 'Y';
    $arUpdateValues['NAME'] = 'Договор купли-продажи, подписанный поставщиком';
    $arUpdateValues['CODE'] = 'dkp_partner';

    $arUpdatePropertyValues['DEAL'] = $arResult['ID'];

    $copy  = copy($_FILES['dkp']['tmp_name'], $_SERVER["DOCUMENT_ROOT"]."/upload/tmp/".$_FILES['dkp']['name']);
    $file = $_SERVER["DOCUMENT_ROOT"]."/upload/tmp/".$_FILES['dkp']['name'];
    $arUpdatePropertyValues['FILE_PDF'] = CFile::MakeFileArray($file);

    $arUpdateValues['PROPERTY_VALUES'] = $arUpdatePropertyValues;

    if ($docId = $oElement->Add($arUpdateValues)) {
        log::addDealStatusLog($arResult['ID'], 'dkp_partner', 'ДКП подписан АП');

        $noticeList = notice::getNoticeList();

        $agentObj = new agent();
        $clientAgent = $agentObj->getProfileByClientID($arResult['CLIENT']['USER']['ID']);

        //Отправка данных покупателю
        $url = '/client/deals/' . $arResult['ID'] . '/';
        if (in_array($noticeList['e_d']['ID'], $arResult['CLIENT']['PROPERTY_NOTICE_VALUE'])) {
            $arEventFields = array(
                'CULTURE' => strip_tags($arResult['DISPLAY_PROPERTIES']['CULTURE']['DISPLAY_VALUE']),
                'ID' => $arResult['ID'],
                'URL' => $GLOBALS['host'].$url,
                'EMAIL' => $arResult['CLIENT']['USER']['EMAIL'],
            );
            CEvent::Send('CLIENT_DKP', 's1', $arEventFields);
        }
        if (in_array($noticeList['c_d']['ID'], $arResult['CLIENT']['PROPERTY_NOTICE_VALUE'])) {
            notice::addNotice($arResult['CLIENT']['USER']['ID'], 'd', 'Договор купли-продажи', $url, '#' . $arResult['ID']);
        }
        if (in_array($noticeList['s_d']['ID'], $arResult['CLIENT']['PROPERTY_NOTICE_VALUE']) && $arResult['CLIENT']['PROPERTY_PHONE_VALUE']) {
            $phone = str_replace(array('+', ' ', '(', ')', '-'), "", $arResult['CLIENT']['PROPERTY_PHONE_VALUE']);
            notice::sendNoticeSMS($phone, 'Договор купли-продажи по сделке: '.$GLOBALS['host'].$url);
        }

        //Отправка данных агенту покупателя
        /*$url = '/client_agent/deals/' . $arResult['ID'] . '/';
        if(isset($clientAgent['DEALS_RIGHTS']) && $clientAgent['DEALS_RIGHTS']){
            if (in_array($noticeList['e_d']['ID'], $clientAgent['PROPERTY_NOTICE_VALUE'])) {
                $arEventFields = array(
                    'CULTURE' => strip_tags($arResult['DISPLAY_PROPERTIES']['CULTURE']['DISPLAY_VALUE']),
                    'ID' => $arResult['ID'],
                    'URL' => $GLOBALS['host'] . $url,
                    'EMAIL' => $clientAgent['USER']['EMAIL'],
                );
                CEvent::Send('CLIENT_DKP_FOR_AGENT', 's1', $arEventFields);
            }
            if (in_array($noticeList['c_d']['ID'], $clientAgent['PROPERTY_NOTICE_VALUE'])) {
                notice::addNotice($clientAgent['USER']['ID'], 'd', 'Договор купли-продажи', $url, '#' . $arResult['ID']);
            }
        }*/

        LocalRedirect($arParams['SELF_URL']);
    }
}

//загрузка ДС, подписанного организатором
if ($_FILES['ds']['tmp_name'] && $_FILES['ds']['size'] > 0 && $_FILES['ds']['error'] == 0) {
    $oElement = new CIBlockElement();
    $arUpdateValues = $arUpdatePropertyValues = array();

    $arUpdateValues['IBLOCK_ID'] = rrsIblock::getIBlockId('deals_docs');
    $arUpdateValues['ACTIVE'] = 'Y';
    $arUpdateValues['NAME'] = 'Доп. соглашение к ДКП, подписанное организатором';
    $arUpdateValues['CODE'] = 'ds_partner';

    $arUpdatePropertyValues['DEAL'] = $arResult['ID'];

    $copy  = copy($_FILES['ds']['tmp_name'], $_SERVER["DOCUMENT_ROOT"]."/upload/tmp/".$_FILES['ds']['name']);
    $file = $_SERVER["DOCUMENT_ROOT"]."/upload/tmp/".$_FILES['ds']['name'];
    $arUpdatePropertyValues['FILE_PDF'] = CFile::MakeFileArray($file);

    $arUpdateValues['PROPERTY_VALUES'] = $arUpdatePropertyValues;

    if ($docId = $oElement->Add($arUpdateValues)) {
        log::addDealStatusLog($arResult['ID'], 'ds_partner', 'ДC подписано организатором');

        LocalRedirect($arParams['SELF_URL']);
    }
}

//сохранение номера и даты ДОУ с перевозчиком
if ($_REQUEST['saveDouTrInfo']) {
    $oElement = new CIBlockElement();
    $arUpdateValues = $arUpdatePropertyValues = array();

    $arUpdateValues['IBLOCK_ID'] = rrsIblock::getIBlockId('partner_transport_dou');
    $arUpdateValues['ACTIVE'] = 'Y';
    $arUpdateValues['NAME'] = 'ДОУ';
    $arUpdatePropertyValues['DOU_NUM'] = $_REQUEST['dou_num'];
    $arUpdatePropertyValues['DOU_DATE'] = $_REQUEST['dou_date'];
    $arUpdatePropertyValues['PARTNER'] = $arResult['PROPERTIES']['PARTNER']['VALUE'];
    $arUpdatePropertyValues['TRANSPORT'] = $arResult['PROPERTIES']['TRANSPORT']['VALUE'];
    $arUpdateValues['PROPERTY_VALUES'] = $arUpdatePropertyValues;

    if ($elId = $oElement->Add($arUpdateValues)) {

        //информация о ДОУ между перевозчиком и организатором
        $douInfo = partner::getTransportDouInfo(
            $arResult['PROPERTIES']['PARTNER']['VALUE'],
            $arResult['PROPERTIES']['TRANSPORT']['VALUE']
        );
        if ($douInfo['ID'] > 0) {
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
            $transport = transport::getFullProfile($arResult['PROPERTIES']['TRANSPORT']['VALUE']);

            //общая стоимость груза ___ руб. ___ коп
            $arCost = explode('.', number_format($cost, 2, '.', ' '));
            $costStr = $arCost[0] . ' руб. ' . $arCost[1] . ' коп';

            //тариф на перевозку, руб/т
            $tarif = $arResult['PROPERTIES']['TARIF']['VALUE'];

            //стоимость перевозки, руб = руб/т * т
            $transportCost = $tarif * $volumeTn;

            //плата за перевозку ___ руб. ___ коп
            $arTransportCost = explode('.', number_format($transportCost, 2, '.', ' '));
            $transportCostStr = $arTransportCost[0] . ' руб. ' . $arTransportCost[1] . ' коп';

            //вознаграждение организатора
            //процент комиссии на перевозку
            $transportCommission = rrsIblock::getConst('commission_transport');

            //комиссия в руб
            $transportCommissionRub = 0.01 * $transportCommission * $transportCost;

            $transportList = rrsIblock::getElementList(rrsIblock::getIBlockId('transport_type'));
            $transportType = array();
            if (sizeof($arResult['CLIENT_WAREHOUSE']['TRANSPORT']) > 0) {
                foreach ($arResult['CLIENT_WAREHOUSE']['TRANSPORT'] as $val) {
                    $transportType[] = $transportList[$val]['NAME'];
                }
            }
            $transportType = implode(' ,', $transportType);

            $docId = deal::createDocument(
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
                    "#COST_TRANSPORT#" => $transportCostStr,
                    "#COMMISSION_TR_RUB#" => number_format($transportCommissionRub, 2, '.', ' ').' рублей',
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

            log::addDealStatusLog($arResult['ID'], 'ds_transport_ready', 'Доп. соглашение к договору на транспортировку сформировано');
        }
        LocalRedirect($arParams['SELF_URL']);
    }
}

//загрузка ДТР, подписанного организатором
if ($_FILES['dtr']['tmp_name'] && $_FILES['dtr']['size'] > 0 && $_FILES['dtr']['error'] == 0) {
    $oElement = new CIBlockElement();
    $arUpdateValues = $arUpdatePropertyValues = array();

    $arUpdateValues['IBLOCK_ID'] = rrsIblock::getIBlockId('deals_docs');
    $arUpdateValues['ACTIVE'] = 'Y';
    $arUpdateValues['NAME'] = 'Договор на транспортировку, подписанный организатором';
    $arUpdateValues['CODE'] = 'dtr_partner';

    $arUpdatePropertyValues['DEAL'] = $arResult['ID'];

    $copy  = copy($_FILES['dtr']['tmp_name'], $_SERVER["DOCUMENT_ROOT"]."/upload/tmp/".$_FILES['dtr']['name']);
    $file = $_SERVER["DOCUMENT_ROOT"]."/upload/tmp/".$_FILES['dtr']['name'];
    $arUpdatePropertyValues['FILE_PDF'] = CFile::MakeFileArray($file);

    $arUpdateValues['PROPERTY_VALUES'] = $arUpdatePropertyValues;

    if ($docId = $oElement->Add($arUpdateValues)) {
        log::addDealStatusLog($arResult['ID'], 'dtr_partner', 'ДТР подписан организатором');

        $noticeList = notice::getNoticeList();

        $url = '/transport/deals/' . $arResult['ID'] . '/';
        if (in_array($noticeList['e_d']['ID'], $arResult['TRANSPORT']['PROPERTY_NOTICE_VALUE'])) {
            $arEventFields = array(
                'CULTURE' => strip_tags($arResult['DISPLAY_PROPERTIES']['CULTURE']['DISPLAY_VALUE']),
                'ID' => $arResult['ID'],
                'URL' => $GLOBALS['host'].$url,
                'EMAIL' => $arResult['TRANSPORT']['USER']['EMAIL'],
            );
            CEvent::Send('TRANSPORT_DTR', 's1', $arEventFields);
        }
        if (in_array($noticeList['c_d']['ID'], $arResult['TRANSPORT']['PROPERTY_NOTICE_VALUE'])) {
            notice::addNotice($arResult['TRANSPORT']['USER']['ID'], 'd', 'Договор на транспортировку', $url, '#' . $arResult['ID']);
        }
        if (in_array($noticeList['s_d']['ID'], $arResult['TRANSPORT']['PROPERTY_NOTICE_VALUE']) && $arResult['TRANSPORT']['PROPERTY_PHONE_VALUE']) {
            $phone = str_replace(array('+', ' ', '(', ')', '-'), "", $arResult['TRANSPORT']['PROPERTY_PHONE_VALUE']);
            notice::sendNoticeSMS($phone, 'Договор на транспортировку по сделке: '.$GLOBALS['host'].$url);
        }

        LocalRedirect($arParams['SELF_URL']);
    }
}

//загрузка ДС на транспортировку, подписанного организатором
if ($_FILES['ds_transport']['tmp_name'] && $_FILES['ds_transport']['size'] > 0 && $_FILES['ds_transport']['error'] == 0) {
    $oElement = new CIBlockElement();
    $arUpdateValues = $arUpdatePropertyValues = array();

    $arUpdateValues['IBLOCK_ID'] = rrsIblock::getIBlockId('deals_docs');
    $arUpdateValues['ACTIVE'] = 'Y';
    $arUpdateValues['NAME'] = 'Доп. соглашение к ДТР, подписанное организатором';
    $arUpdateValues['CODE'] = 'ds_transport_partner';

    $arUpdatePropertyValues['DEAL'] = $arResult['ID'];

    $copy  = copy($_FILES['ds_transport']['tmp_name'], $_SERVER["DOCUMENT_ROOT"]."/upload/tmp/".$_FILES['ds_transport']['name']);
    $file = $_SERVER["DOCUMENT_ROOT"]."/upload/tmp/".$_FILES['ds_transport']['name'];
    $arUpdatePropertyValues['FILE_PDF'] = CFile::MakeFileArray($file);

    $arUpdateValues['PROPERTY_VALUES'] = $arUpdatePropertyValues;

    if ($docId = $oElement->Add($arUpdateValues)) {
        log::addDealStatusLog($arResult['ID'], 'ds_transport_partner', 'ДС к ДТР подписано организатором');

        LocalRedirect($arParams['SELF_URL']);
    }
}

//формирование счета на предоплату
if ($_REQUEST['doc'] == 'prepayment') {
    $client = client::getFullProfile($arResult['PROPERTIES']['CLIENT']['VALUE']);
    $farmer = farmer::getFullProfile($arResult['PROPERTIES']['FARMER']['VALUE']);
    $order = deal::getDocument($arResult['ID'], 'dkp');
    $culture = culture::getName($arResult['PROPERTIES']['CULTURE']['VALUE']);

    //объем в тоннах
    $volumeTn = $arResult['PROPERTIES']['VOLUME']['VALUE'];

    //базисная цена в руб/т
    $basePriceTn = $arResult['PROPERTIES']['BASE_PRICE']['VALUE'];

    //стоимость товара в руб
    $cost = $volumeTn * $basePriceTn;

    //НДС
    $nds = rrsIblock::getConst("nds");
    $ndsValue = $cost * 0.01 * $nds / (1. + 0.01 * $nds);

    $docId = deal::createDocument(
        $arResult['ID'],
        'payment',
        'prepayment',
        'Счет на оплату по договору купли-продажи',
        array(
            "#FARMER_BANK#" => $farmer['PROPERTY_BANK_VALUE'],
            "#FARMER_BIK#" => $farmer['PROPERTY_BIK_VALUE'],
            "#FARMER_CS#" => $farmer['PROPERTY_KOR_SCHET_VALUE'],
            "#FARMER_INN#" => 'ИНН '.$farmer['PROPERTY_INN_VALUE'],
            "#FARMER_KPP#" => $farmer['KPP'],
            "#FARMER_RS#" => $farmer['PROPERTY_RASCH_SCHET_VALUE'],
            "#FARMER_NAME#" => $farmer['COMPANY'],
            "#PAYMENT_NUM#" => "#DOC_ID#",
            "#PAYMENT_DATE#" => date('d.m.Y'),
            "#FARMER_ADDRESS#" => $farmer['ADDRESS'],
            "#FARMER_PHONE#" => $farmer['PROPERTY_PHONE_VALUE'],
            "#CLIENT_NAME#" => $client['COMPANY'],
            "#CLIENT_INN#" => 'ИНН '.$client['PROPERTY_INN_VALUE'],
            "#CLIENT_KPP#" => $client['KPP'],
            "#CLIENT_ADDRESS#" => $client['ADDRESS'],
            "#CLIENT_PHONE#" => $client['PROPERTY_PHONE_VALUE'],
            "#DKP_NUM#" => $order['ID'],
            "#DKP_DATE#" => date('d.m.Y', strtotime($order['DATE_CREATE'])),
            "#CULTURE#" => $culture['NAME'],
            "#VOLUME#" => number_format($volumeTn, 0, ',', ' '),
            "#PRICE#" => number_format($basePriceTn, 2, '.', ' '),
            "#COST#" => number_format($cost, 2, '.', ' '),
            "#NDS_DESC#" => ($farmer['PROPERTY_NDS_CODE'] == "N")?"Без налога (НДС)":"В том числе НДС",
            "#NDS#" => ($farmer['PROPERTY_NDS_CODE'] == "N")?"":number_format($ndsValue, 2, '.', ' '),
            "#COSTSTR#" => num2str($cost),
        )
    );

    if ($docId > 0) {
        log::addDealStatusLog($arResult['ID'], 'prepayment_ready', 'Сформирован счет на предоплату по ДКП');
    }

    LocalRedirect($arParams['SELF_URL']);
}

//Отправка счета по договору купли-продажи
if ($_REQUEST['prepayment'] == 'Y') {
    log::addDealStatusLog($arResult['ID'], 'prepayment_send', 'Отправлен счета на оплату по договору купли-продажи (предоплата)');

    if ($arResult['PROPERTIES']['DELIVERY']['VALUE_XML_ID'] == 'c') {
        if (sizeof(array_intersect(array('dtr_transport', 'dkp_client', 'ds_client'), array_keys($arResult['LOGS']))) > 2) {
            log::addDealStatusLog($arResult['ID'], 'execution', 'Исполнение заказа');
            deal::setStatus($arResult['ID'], 'execution');
        }
    }
    else {
        if (sizeof(array_intersect(array('dkp_client', 'ds_client'), array_keys($arResult['LOGS']))) > 1) {
            log::addDealStatusLog($arResult['ID'], 'execution', 'Исполнение заказа');
            deal::setStatus($arResult['ID'], 'execution');
        }
    }

    $noticeList = notice::getNoticeList();

    $agentObj = new agent();
    $clientAgent = $agentObj->getProfileByClientID($arResult['CLIENT']['USER']['ID']);

    //Отправка данных покупателю
    $url = '/client/deals/' . $arResult['ID'] . '/';
    if (in_array($noticeList['e_d']['ID'], $arResult['CLIENT']['PROPERTY_NOTICE_VALUE'])) {
        $arEventFields = array(
            'CULTURE' => strip_tags($arResult['DISPLAY_PROPERTIES']['CULTURE']['DISPLAY_VALUE']),
            'ID' => $arResult['ID'],
            'URL' => $GLOBALS['host'].$url,
            'EMAIL' => $arResult['CLIENT']['USER']['EMAIL'],
        );
        CEvent::Send('CLIENT_PREPAYMENT_SEND', 's1', $arEventFields);
    }
    if (in_array($noticeList['c_d']['ID'], $arResult['CLIENT']['PROPERTY_NOTICE_VALUE'])) {
        notice::addNotice($arResult['CLIENT']['USER']['ID'], 'd', 'Счет для внесения предоплаты', $url, '#' . $arResult['ID']);
    }
    if (in_array($noticeList['s_d']['ID'], $arResult['CLIENT']['PROPERTY_NOTICE_VALUE']) && $arResult['CLIENT']['PROPERTY_PHONE_VALUE']) {
        $phone = str_replace(array('+', ' ', '(', ')', '-'), "", $arResult['CLIENT']['PROPERTY_PHONE_VALUE']);
        notice::sendNoticeSMS($phone, 'Счет на предоплату по сделке: '.$GLOBALS['host'].$url);
    }

    //Отправка данных агенту покупателя
    /*$url = '/client_agent/deals/' . $arResult['ID'] . '/';
    if(isset($clientAgent['DEALS_RIGHTS']) && $clientAgent['DEALS_RIGHTS']){
        if (in_array($noticeList['e_d']['ID'], $clientAgent['PROPERTY_NOTICE_VALUE'])) {
            $arEventFields = array(
                'CULTURE' => strip_tags($arResult['DISPLAY_PROPERTIES']['CULTURE']['DISPLAY_VALUE']),
                'ID' => $arResult['ID'],
                'URL' => $GLOBALS['host'] . $url,
                'EMAIL' => $clientAgent['USER']['EMAIL'],
            );
            CEvent::Send('CLIENT_PREPAYMENT_SEND_FOR_AGENT', 's1', $arEventFields);
        }
        if (in_array($noticeList['c_d']['ID'], $clientAgent['PROPERTY_NOTICE_VALUE'])) {
            notice::addNotice($clientAgent['USER']['ID'], 'd', 'Счет для внесения предоплаты', $url, '#' . $arResult['ID']);
        }
    }*/

    LocalRedirect($arParams['SELF_URL']);
}

//сохранение реестра в ведомость исполнения
if ($_REQUEST['save_reestr']) {
    if (is_array($_REQUEST['car']) && sizeof($_REQUEST['car']) > 0) {
        $data = array();
        foreach ($_REQUEST['car'] as $key => $item) {
            if ($_REQUEST['car'][$key] != '' && $_REQUEST['weight'][$key] != '' && $_REQUEST['cost'][$key] != '' && $_REQUEST['cost'][$key] > 0) {
                $update = false;
                if (in_array($key, $_REQUEST['update'])) {
                    $update = true;
                }
                $data[] = array(
                    'car' => $_REQUEST['car'][$key],
                    'weight' => $_REQUEST['weight'][$key],
                    'cost' => str_replace(' ', '', $_REQUEST['cost'][$key]),
                    'update' => $update,
                    'key' => $key
                );
            }
        }
    }

    if (is_array($data) && sizeof($data) > 0) {
        $oElement = new CIBlockElement();
        $ib = rrsIblock::getIBlockId('deals_exe_docs');

        if ($arResult['PROPERTIES']['DELIVERY']['VALUE_XML_ID'] == 'c'
            && $arResult['PROPERTIES']['TRANSPORT']['VALUE']
            && $arResult['PROPERTIES']['TARIF']['VALUE']
        ) {
            $transport_tarif = $arResult['PROPERTIES']['TARIF']['VALUE'];
        }

        if ($arResult['PROPERTIES']['DELIVERY']['VALUE_XML_ID'] == 'a') {
            //$tarif = model::getTarif($arResult['PROPERTIES']['CENTER']['VALUE'], $arResult['PROPERTIES']['ROUTE']['VALUE'], true);
            $arAgrohelperTariffs = model::getAgrohelperTariffs();
            foreach ($arAgrohelperTariffs as $key => $arTariff) {
                if ($arResult['PROPERTIES']['ROUTE']['VALUE'] >= $arTariff['FROM'])
                    $tariffId = $key;

                if ($arResult['PROPERTIES']['ROUTE']['VALUE'] < $arTariff['TO'])
                    break;
            }
            if (intval($tariffId) > 0)
                $tarif = round($arAgrohelperTariffs[$tariffId]['TARIF'], 0);
            else
                $tarif = 0;
        }

        foreach ($data as $item) {
            $arUpdateValues = $arUpdatePropertyValues = array();

            $arUpdateValues['IBLOCK_ID'] = $ib;
            $arUpdateValues['ACTIVE'] = 'Y';
            $arUpdateValues['NAME'] = date('d.m.Y H:i:s');

            $arUpdatePropertyValues['DEAL'] = $arResult['ID'];

            $price = $arResult['PROPERTIES']['BASE_PRICE']['VALUE'];

            $price_nds = $price;

            $rc = 1000 * $item['cost'] / $item['weight'];

            if ($arResult['PROPERTIES']['DELIVERY']['VALUE_XML_ID'] == 'a') {
                $dump = 100.*(($rc + $tarif - $price_nds)/$price_nds);
            }
            else {
                $dump = 100.*(($rc - $price_nds)/$price_nds);
            }

            //$dumpRub = 0.01 * $price_nds * $item['dump'];
            //$price_acc = $price_nds + $dumpRub;
            //$cost = 0.001 * $item['weight'] * $item['rc'];

            $arUpdatePropertyValues['CAR'] = $item['car'];
            $arUpdatePropertyValues['WEIGHT'] = $item['weight'];
            $arUpdatePropertyValues['COST'] = $item['cost'];
            $arUpdatePropertyValues['RC'] = $rc;
            $arUpdatePropertyValues['DUMP'] = round($dump, 2);

            if ($transport_tarif) {
                $transport_cost = 0.001 * $transport_tarif *  $item['weight'];
                $arUpdatePropertyValues['TRANSPORT_COST'] = $transport_cost;
                $arUpdatePropertyValues['CSM'] = $rc - $transport_tarif;
            }

            $arUpdateValues["PROPERTY_VALUES"] = $arUpdatePropertyValues;

            if ($item['update']) {
                $ID = $oElement->Update($item['key'], $arUpdateValues);
            }
            else {
                $ID = $oElement->Add($arUpdateValues);
            }
        }
    }

    LocalRedirect($arParams['SELF_URL']);
}

//подтверждение загрузки ведомости исполнения
if ($_REQUEST['complete'] == 'Y') {
    log::addDealStatusLog($arResult['ID'], 'complete', 'Ведомость исполнения загружена');
    deal::setStatus($arResult['ID'], 'complete');

    //наименование культуры
    $culture = culture::getName($arResult['PROPERTIES']['CULTURE']['VALUE']);

    //профили участников сделки
    $client = client::getFullProfile($arResult['PROPERTIES']['CLIENT']['VALUE']);
    $farmer = farmer::getFullProfile($arResult['PROPERTIES']['FARMER']['VALUE']);
    $partner = partner::getFullProfile($arResult['PROPERTIES']['PARTNER']['VALUE']);

    //ДКП
    $orderDkp = deal::getDocument($arResult['ID'], 'dkp');

    //объем из ВИ в т
    $volumeTn = $arResult['VI_SUMMARY']['WEIGHT'];

    //объем из ВИ в кг
    $volumeKg = 1000 * $volumeTn;

    //цена товара из ВИ в руб/т
    $priceTn = $arResult['VI_SUMMARY']['RC'];

    //цена товара из ВИ в руб/кг
    $priceKg = 0.001 * round($priceTn, 0);

    //общая стоимость товара из ВИ, руб
    $cost = $arResult['VI_SUMMARY']['COST'];

    //НДС
    $nds = rrsIblock::getConst("nds");
    $ndsRub = round($cost * 0.01 * $nds / (1. + 0.01 * $nds), 2);

    $docId = deal::createDocument(
        $arResult['ID'],
        'payment',
        'payment',
        'Счет на оплату по договору купли-продажи',
        array(
            "#PAYMENT_NUM#" => "#DOC_ID#",
            "#PAYMENT_DATE#" => date('d.m.Y'),
            "#DKP_NUM#" => $orderDkp['ID'],
            "#DKP_DATE#" => date('d.m.Y', strtotime($orderDkp['DATE_CREATE'])),
            "#CULTURE#" => $culture['NAME'],
            "#VOLUME#" => number_format($volumeTn, 2, '.', ' '),
            "#PRICE#" => number_format($priceTn, 2, '.', ' '),
            "#COST#" => number_format($cost, 2, '.', ' '),
            "#NDS_DESC#" => ($farmer['PROPERTY_NDS_CODE'] == "N")?"Без налога (НДС)":"В том числе НДС",
            "#NDS#" => ($farmer['PROPERTY_NDS_CODE'] == "N")?"":number_format($ndsRub, 2, '.', ' '),
            "#COSTSTR#" => num2str(round($cost, 2)),
            "#CLIENT_NAME#" => $client['COMPANY'],
            "#CLIENT_INN#" => 'ИНН '.$client['PROPERTY_INN_VALUE'],
            "#CLIENT_KPP#" => $client['KPP'],
            "#CLIENT_ADDRESS#" => $client['ADDRESS'],
            "#CLIENT_PHONE#" => $client['PROPERTY_PHONE_VALUE'],
            "#FARMER_NAME#" => $farmer['COMPANY'],
            "#FARMER_INN#" => 'ИНН '.$farmer['PROPERTY_INN_VALUE'],
            "#FARMER_KPP#" => $farmer['KPP'],
            "#FARMER_ADDRESS#" => $farmer['ADDRESS'],
            "#FARMER_PHONE#" => $farmer['PROPERTY_PHONE_VALUE'],
            "#FARMER_BANK#" => $farmer['PROPERTY_BANK_VALUE'],
            "#FARMER_BIK#" => $farmer['PROPERTY_BIK_VALUE'],
            "#FARMER_CS#" => $farmer['PROPERTY_KOR_SCHET_VALUE'],
            "#FARMER_RS#" => $farmer['PROPERTY_RASCH_SCHET_VALUE'],
        )
    );

    //комиссия организатору
    $commission = rrsIblock::getConst('commission');
    $commissionRub = $commission * $cost / (100 - $commission);

    //цена комиссии, руб/т
    $commissionPriceTn = $commission * $priceTn / (100 - $commission);

    //НДС
    $commissionNdsRub = round($commissionRub * 0.01 * $nds / (1. + 0.01 * $nds), 2);

    $docId = deal::createDocument(
        $arResult['ID'],
        'commission',
        'commission',
        'Счет на вознаграждение по договору купли-продажи',
        array(
            "#PAYMENT_NUM#" => "#DOC_ID#",
            "#PAYMENT_DATE#" => date('d.m.Y'),
            "#DKP_NUM#" => $orderDkp['ID'],
            "#DKP_DATE#" => date('d.m.Y', strtotime($orderDkp['DATE_CREATE'])),
            "#CULTURE#" => $culture['NAME'],
            "#VOLUME#" => number_format($volumeTn, 2, '.', ' '),
            "#PRICE#" => number_format($commissionPriceTn, 2, '.', ' '),
            "#COST#" => number_format($commissionRub, 2, '.', ' '),
            "#NDS_DESC#" => ($farmer['PROPERTY_NDS_CODE'] == "N")?"Без налога (НДС)":"В том числе НДС",
            "#NDS#" => ($farmer['PROPERTY_NDS_CODE'] == "N")?"":number_format($commissionNdsRub, 2, '.', ' '),
            "#COSTSTR#" => num2str(round($commissionRub, 2)),
            "#CLIENT_NAME#" => $client['COMPANY'],
            "#CLIENT_INN#" => 'ИНН '.$client['PROPERTY_INN_VALUE'],
            "#CLIENT_KPP#" => $client['KPP'],
            "#CLIENT_ADDRESS#" => $client['ADDRESS'],
            "#CLIENT_PHONE#" => $client['PROPERTY_PHONE_VALUE'],
            //"#FARMER_NAME#" => $farmer['COMPANY'],
            //"#FARMER_INN#" => 'ИНН '.$farmer['PROPERTY_INN_VALUE'],
            //"#FARMER_KPP#" => $farmer['KPP'],
            //"#FARMER_ADDRESS#" => $farmer['ADDRESS'],
            //"#FARMER_PHONE#" => $farmer['PROPERTY_PHONE_VALUE'],
            "#PARTNER_NAME#" => $partner['COMPANY'],
            "#PARTNER_INN#" => 'ИНН '.$partner['PROPERTY_INN_VALUE'],
            "#PARTNER_KPP#" => $partner['KPP'],
            "#PARTNER_ADDRESS#" => $partner['ADDRESS'],
            "#PARTNER_PHONE#" => $partner['PROPERTY_PHONE_VALUE'],
            "#PARTNER_BANK#" => $partner['PROPERTY_BANK_VALUE'],
            "#PARTNER_BIK#" => $partner['PROPERTY_BIK_VALUE'],
            "#PARTNER_CS#" => $partner['PROPERTY_KOR_SCHET_VALUE'],
            "#PARTNER_RS#" => $partner['PROPERTY_RASCH_SCHET_VALUE'],
        )
    );

    //информация о ДОУ между покупателем и организатором
    $douInfo = partner::getClientDouInfo(
        $arResult['PROPERTIES']['PARTNER']['VALUE'],
        $arResult['PROPERTIES']['CLIENT']['VALUE']
    );

    //таблицы с параметрами качества
    $table = new deal;
    $arTableHtml = $table->formDumpTable($arResult['PROPERTIES']['CULTURE']['VALUE'], $arResult['PROPERTIES']['REQUEST']['VALUE']);

    $fca_dap = ($arResult['REQUEST']['NEED_DELIVERY'] == 'Y')?'CPT/доставка товара автотранспортом на склад':'FCA/погружено в автотранспорт на складе';

    $docId = deal::createDocument(
        $arResult['ID'],
        'act_deal',
        'act_deal',
        'Акт сдачи-приёмки услуг к договору оказания услуг',
        array(
            "#ACT_NUM#" => "#DOC_ID#",
            "#ACT_DATE#" => date('d.m.Y'),
            "#DOU_NUM#" => $douInfo['PROPERTY_DOU_NUM_VALUE'],
            "#DOU_DATE#" => date('d.m.Y', strtotime($douInfo['PROPERTY_DOU_DATE_VALUE'])),
            "#DEAL_NUM#" => $orderDkp['ID'],
            "#DEAL_DATE#" => date('d.m.Y', strtotime($orderDkp['DATE_CREATE'])),
            "#CULTURE#" => $culture['CHEGO'],
            "#VOLUME#" => number_format($volumeKg, 0, ',', ' '),
            "#VI_PRICE#" => price2Str($priceKg),
            "#VI_COMMISSION_COST#" => number_format($commissionRub, 2, '.', ' ') . ' рублей',
            "#FCA_DAP#" => $fca_dap,
            "#BASE_TABLE#" => $arTableHtml['BASE_TABLE'],
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
            "#CLIENT_OKPO#" => 'ОКПО '.$client['PROPERTY_OKPO_VALUE'],
            "#CLIENT_RS#" => $client['PROPERTY_RASCH_SCHET_VALUE'],
            "#CLIENT_BANK#" => $client['PROPERTY_BANK_VALUE'],
            "#CLIENT_CS#" => 'к/с '.$client['PROPERTY_KOR_SCHET_VALUE'],
            "#CLIENT_BIK#" => 'БИК '.$client['PROPERTY_BIK_VALUE'],
            "#FARMER_COMPANY#" => $farmer['COMPANY'],
            "#FARMER_ADDRESS#" => $farmer['ADDRESS'],
            "#FARMER_INN#" => 'ИНН '.$farmer['PROPERTY_INN_VALUE'],
            "#FARMER_KPP#" => $farmer['KPP'],
            "#FARMER_OGRN#" => $farmer['OGRN'],
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

    if ($arResult['PROPERTIES']['DELIVERY']['VALUE_XML_ID'] == 'c') {
        //ДТР
        $orderDtr = deal::getDocument($arResult['ID'], 'dtr');

        //профиль перевозчика
        $transport = transport::getFullProfile($arResult['PROPERTIES']['TRANSPORT']['VALUE']);

        //склады отгрузки и доставки
        $client_warehouse = current(client::getWarehouseParamsList(array($arResult['PROPERTIES']['CLIENT_WAREHOUSE']['VALUE'])));
        $farmer_warehouse = current(farmer::getWarehouseParamsList(array($arResult['PROPERTIES']['FARMER_WAREHOUSE']['VALUE'])));

        //тариф на перевозку, он же цена по счету, руб/т
        $transportPriceTn = $arResult['PROPERTIES']['TARIF']['VALUE'];

        //стоимость перевозки
        $transportCost = $volumeTn * $transportPriceTn;

        //НДС
        $transportNdsRub = round($transportCost * 0.01 * $nds / (1. + 0.01 * $nds), 2);

        $docId = deal::createDocument(
            $arResult['ID'],
            'payment_transport',
            'payment_transport',
            'Счет на оплату договору транспортировки',
            array(
                "#PAYMENT_NUM#" => "#DOC_ID#",
                "#PAYMENT_DATE#" => date('d.m.Y'),
                "#DTR_NUM#" => $orderDtr['ID'],
                "#DTR_DATE#" => date('d.m.Y', strtotime($orderDtr['DATE_CREATE'])),
                "#CULTURE#" => $culture['CHEGO'],
                "#VOLUME#" => number_format($volumeTn, 2, '.', ' '),
                "#PRICE#" => number_format($transportPriceTn, 2, '.', ' '),
                "#COST#" => number_format($transportCost, 2, '.', ' '),
                "#NDS_DESC#" => ($farmer['PROPERTY_NDS_CODE'] == "N")?"Без налога (НДС)":"В том числе НДС",
                "#NDS#" => ($farmer['PROPERTY_NDS_CODE'] == "N")?"":number_format($transportNdsRub, 2, '.', ' '),
                "#COSTSTR#" => num2str(round($transportCost), 2),
                "#FROM#" => $farmer_warehouse['ADDRESS'],
                "#TO#" => $client_warehouse['ADDRESS'],
                "#FARMER_NAME#" => $farmer['COMPANY'],
                "#FARMER_INN#" => 'ИНН '.$farmer['PROPERTY_INN_VALUE'],
                "#FARMER_KPP#" => $farmer['KPP'],
                "#FARMER_ADDRESS#" => $farmer['ADDRESS'],
                "#FARMER_PHONE#" => $farmer['PROPERTY_PHONE_VALUE'],
                "#TRANSPORT_NAME#" => $transport['COMPANY'],
                "#TRANSPORT_INN#" => 'ИНН '.$transport['PROPERTY_INN_VALUE'],
                "#TRANSPORT_KPP#" => $transport['KPP'],
                "#TRANSPORT_ADDRESS#" => $transport['ADDRESS'],
                "#TRANSPORT_PHONE#" => $transport['PROPERTY_PHONE_VALUE'],
                "#TRANSPORT_BANK#" => $transport['PROPERTY_BANK_VALUE'],
                "#TRANSPORT_BIK#" => $transport['PROPERTY_BIK_VALUE'],
                "#TRANSPORT_CS#" => $transport['PROPERTY_KOR_SCHET_VALUE'],
                "#TRANSPORT_RS#" => $transport['PROPERTY_RASCH_SCHET_VALUE'],
            )
        );

        //комиссия организатору
        $transportCommission = rrsIblock::getConst('commission_transport');
        $transportCommissionRub = 0.01 * $transportCommission * $transportCost;

        //цена комиссии, руб/т
        $transportCommissionPriceTn = 0.01 * $transportCommission * $transportPriceTn;

        //НДС
        $transportCommissionNdsRub = round($transportCommissionRub * 0.01 * $nds / (1. + 0.01 * $nds), 2);

        $docId = deal::createDocument(
            $arResult['ID'],
            'commission_transport',
            'commission_transport',
            'Счет на оплату комиссии по договору транспортировки',
            array(
                "#PAYMENT_NUM#" => "#DOC_ID#",
                "#PAYMENT_DATE#" => date('d.m.Y'),
                "#DTR_NUM#" => $orderDtr['ID'],
                "#DTR_DATE#" => date('d.m.Y', strtotime($orderDtr['DATE_CREATE'])),
                "#CULTURE#" => $culture['CHEGO'],
                "#VOLUME#" => number_format($volumeTn, 2, '.', ' '),
                "#PRICE#" => number_format($transportCommissionPriceTn, 2, '.', ' '),
                "#COST#" => number_format($transportCommissionRub, 2, '.', ' '),
                "#NDS_DESC#" => ($farmer['PROPERTY_NDS_CODE'] == "N")?"Без налога (НДС)":"В том числе НДС",
                "#NDS#" => ($farmer['PROPERTY_NDS_CODE'] == "N")?"":number_format($transportCommissionNdsRub, 2, '.', ' '),
                "#COSTSTR#" => num2str(round($transportCommissionRub, 2)),
                "#FROM#" => $farmer_warehouse['ADDRESS'],
                "#TO#" => $client_warehouse['ADDRESS'],
                //"#FARMER_NAME#" => $farmer['COMPANY'],
                //"#FARMER_INN#" => $farmer['PROPERTY_INN_VALUE'],
                //"#FARMER_KPP#" => $farmer['KPP'],
                //"#FARMER_ADDRESS#" => $farmer['ADDRESS'],
                //"#FARMER_PHONE#" => $farmer['PROPERTY_PHONE_VALUE'],
                "#PARTNER_NAME#" => $partner['COMPANY'],
                "#PARTNER_INN#" => 'ИНН '.$partner['PROPERTY_INN_VALUE'],
                "#PARTNER_KPP#" => $partner['KPP'],
                "#PARTNER_ADDRESS#" => $partner['ADDRESS'],
                "#PARTNER_PHONE#" => $partner['PROPERTY_PHONE_VALUE'],
                "#PARTNER_BANK#" => $partner['PROPERTY_BANK_VALUE'],
                "#PARTNER_BIK#" => $partner['PROPERTY_BIK_VALUE'],
                "#PARTNER_CS#" => $partner['PROPERTY_KOR_SCHET_VALUE'],
                "#PARTNER_RS#" => $partner['PROPERTY_RASCH_SCHET_VALUE'],
                "#TRANSPORT_NAME#" => $transport['COMPANY'],
                "#TRANSPORT_INN#" => 'ИНН '.$transport['PROPERTY_INN_VALUE'],
                "#TRANSPORT_KPP#" => $transport['KPP'],
                "#TRANSPORT_ADDRESS#" => $transport['ADDRESS'],
                "#TRANSPORT_PHONE#" => $transport['PROPERTY_PHONE_VALUE'],
            )
        );

        //информация о ДОУ между перевозчиком и организатором
        $douInfo = partner::getTransportDouInfo(
            $arResult['PROPERTIES']['PARTNER']['VALUE'],
            $arResult['PROPERTIES']['TRANSPORT']['VALUE']
        );

        //общая стоимость груза ___ руб. ___ коп
        $arCost = explode('.', number_format($cost, 2, '.', ' '));
        $costStr = $arCost[0] . ' руб. ' . $arCost[1] . ' коп';

        //плата за перевозку ___ руб. ___ коп
        $arTransportCost = explode('.', number_format($transportCost, 2, '.', ' '));
        $transportCostStr = $arTransportCost[0] . ' руб. ' . $arTransportCost[1] . ' коп';

        $transportList = rrsIblock::getElementList(rrsIblock::getIBlockId('transport_type'));
        $transportType = array();
        if (sizeof($arResult['CLIENT_WAREHOUSE']['TRANSPORT']) > 0) {
            foreach ($arResult['CLIENT_WAREHOUSE']['TRANSPORT'] as $val) {
                $transportType[] = $transportList[$val]['NAME'];
            }
        }
        $transportType = implode(' ,', $transportType);

        $docId = deal::createDocument(
            $arResult['ID'],
            'act_transport',
            'act_transport',
            'Акт сдачи-приемки услуг к договору на транспортировку',
            array(
                "#ACTTR_NUM#" => "#DOC_ID#",
                "#ACTTR_DATE#" => date('d.m.Y'),
                "#DOUTR_NUM#" => $douInfo['PROPERTY_DOU_NUM_VALUE'],
                "#DOUTR_DATE#" => date('d.m.Y', strtotime($douInfo['PROPERTY_DOU_DATE_VALUE'])),
                "#CULTURE#" => $culture['CHEGO'],
                "#CULTURE_NAME#" => $culture['NAME'],
                "#VOLUME#" => number_format($volumeKg, 0, ',', ' '),
                "#VI_COST#" => $costStr,
                "#VI_COST_TRANSPORT#" => $transportCostStr,
                "#VI_COMMISSION_COST_TR#" => number_format($transportCommissionRub, 2, '.', ' ') . ' рублей',
                "#TRANSPORT#" => $transportType,
                "#CLIENT_COMPANY#" => $client['COMPANY'],
                "#CLIENT_WH_ADDRESS#" => $arResult['CLIENT_WAREHOUSE']['ADDRESS'],
                "#FARMER_COMPANY#" => $farmer['COMPANY'],
                "#FARMER_WH_ADDRESS#" => $arResult['FARMER_WAREHOUSE']['ADDRESS'],
                "#FARMER_ADDRESS#" => $farmer['ADDRESS'],
                "#FARMER_INN#" => 'ИНН '.$farmer['PROPERTY_INN_VALUE'],
                "#FARMER_KPP#" => $farmer['KPP'],
                "#FARMER_OGRN#" => $farmer['OGRN'],
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

        $noticeList = notice::getNoticeList();

        $url = '/transport/deals/' . $arResult['ID'] . '/';
        if (in_array($noticeList['e_d']['ID'], $arResult['TRANSPORT']['PROPERTY_NOTICE_VALUE'])) {
            $arEventFields = array(
                'CULTURE' => strip_tags($arResult['DISPLAY_PROPERTIES']['CULTURE']['DISPLAY_VALUE']),
                'ID' => $arResult['ID'],
                'URL' => $GLOBALS['host'].$url,
                'EMAIL' => $arResult['TRANSPORT']['USER']['EMAIL'],
            );
            CEvent::Send('TRANSPORT_PAYMENT_READY', 's1', $arEventFields);
        }
        if (in_array($noticeList['c_d']['ID'], $arResult['TRANSPORT']['PROPERTY_NOTICE_VALUE'])) {
            notice::addNotice($arResult['TRANSPORT']['USER']['ID'], 'd', 'Сформирован счет на оплату транспортировки', $url, '#' . $arResult['ID']);
        }
        if (in_array($noticeList['s_d']['ID'], $arResult['TRANSPORT']['PROPERTY_NOTICE_VALUE']) && $arResult['TRANSPORT']['PROPERTY_PHONE_VALUE']) {
            $phone = str_replace(array('+', ' ', '(', ')', '-'), "", $arResult['TRANSPORT']['PROPERTY_PHONE_VALUE']);
            notice::sendNoticeSMS($phone, 'Сформирован счет на оплату транспортировки: '.$GLOBALS['host'].$url);
        }
    }

    LocalRedirect($arParams['SELF_URL']);
}

//выставление счета на оплату комиссии по договору транспортировки
if ($_REQUEST['payment'] == 'Y') {
    log::addDealStatusLog($arResult['ID'], 'payment_send', 'Отправлены счета для завершения сделки');

    $noticeList = notice::getNoticeList();

    $agentObj = new agent();
    $clientAgent = $agentObj->getProfileByClientID($arResult['CLIENT']['USER']['ID']);

    //Отправка данных покупателю
    $url = '/client/deals/' . $arResult['ID'] . '/';
    if (in_array($noticeList['e_d']['ID'], $arResult['CLIENT']['PROPERTY_NOTICE_VALUE'])) {
        $arEventFields = array(
            'CULTURE' => strip_tags($arResult['DISPLAY_PROPERTIES']['CULTURE']['DISPLAY_VALUE']),
            'ID' => $arResult['ID'],
            'URL' => $GLOBALS['host'].$url,
            'EMAIL' => $arResult['CLIENT']['USER']['EMAIL'],
        );
        CEvent::Send('CLIENT_PAYMENT_SEND', 's1', $arEventFields);
    }
    if (in_array($noticeList['c_d']['ID'], $arResult['CLIENT']['PROPERTY_NOTICE_VALUE'])) {
        notice::addNotice($arResult['CLIENT']['USER']['ID'], 'd', 'Счета на оплату по сделке', $url, '#' . $arResult['ID']);
    }
    if (in_array($noticeList['s_d']['ID'], $arResult['CLIENT']['PROPERTY_NOTICE_VALUE']) && $arResult['CLIENT']['PROPERTY_PHONE_VALUE']) {
        $phone = str_replace(array('+', ' ', '(', ')', '-'), "", $arResult['CLIENT']['PROPERTY_PHONE_VALUE']);
        notice::sendNoticeSMS($phone, 'Счета на оплату по сделке: '.$GLOBALS['host'].$url);
    }

    //Отправка данных агенту покупателя
    /*$url = '/client_agent/deals/' . $arResult['ID'] . '/';
    if(isset($clientAgent['DEALS_RIGHTS']) && $clientAgent['DEALS_RIGHTS']){
        if (in_array($noticeList['e_d']['ID'], $clientAgent['PROPERTY_NOTICE_VALUE'])) {
            $arEventFields = array(
                'CULTURE' => strip_tags($arResult['DISPLAY_PROPERTIES']['CULTURE']['DISPLAY_VALUE']),
                'ID' => $arResult['ID'],
                'URL' => $GLOBALS['host'].$url,
                'EMAIL' => $clientAgent['USER']['EMAIL'],
            );
            CEvent::Send('CLIENT_PAYMENT_SEND_FOR_AGENT', 's1', $arEventFields);
        }
        if (in_array($noticeList['c_d']['ID'], $clientAgent['PROPERTY_NOTICE_VALUE'])) {
            notice::addNotice($clientAgent['USER']['ID'], 'd', 'Счета на оплату по сделке', $url, '#' . $arResult['ID']);
        }
    }*/

    LocalRedirect($arParams['SELF_URL']);
}

//закрытие сделки
if ($_REQUEST['close'] == 'Y') {
    log::addDealStatusLog($arResult['ID'], 'close', 'Сделка завершена');
    deal::setStatus($arResult['ID'], 'close');

    $el = new CIBlockElement();

    $noticeList = notice::getNoticeList();

    $agentObj = new agent();
    $clientAgent = $agentObj->getProfileByClientID($arResult['CLIENT']['USER']['ID']);

    //Отправка данных покупателю
    $url = '/client/deals/' . $arResult['ID'] . '/';
    if (in_array($noticeList['e_d']['ID'], $arResult['CLIENT']['PROPERTY_NOTICE_VALUE'])) {
        $arEventFields = array(
            'CULTURE' => strip_tags($arResult['DISPLAY_PROPERTIES']['CULTURE']['DISPLAY_VALUE']),
            'ID' => $arResult['ID'],
            'URL' => $GLOBALS['host'].$url,
            'EMAIL' => $arResult['CLIENT']['USER']['EMAIL'],
        );
        CEvent::Send('CLOSE_DEAL', 's1', $arEventFields);
    }
    if (in_array($noticeList['c_d']['ID'], $arResult['CLIENT']['PROPERTY_NOTICE_VALUE'])) {
        notice::addNotice($arResult['CLIENT']['USER']['ID'], 'd', 'Сделка завершена', $url, '#' . $arResult['ID']);
    }
    if (in_array($noticeList['s_d']['ID'], $arResult['CLIENT']['PROPERTY_NOTICE_VALUE']) && $arResult['CLIENT']['PROPERTY_PHONE_VALUE']) {
        $phone = str_replace(array('+', ' ', '(', ')', '-'), "", $arResult['CLIENT']['PROPERTY_PHONE_VALUE']);
        notice::sendNoticeSMS($phone, 'Сделка завершена: '.$GLOBALS['host'].$url);
    }

    //Отправка данных агенту покупателя
    /*$url = '/client_agent/deals/' . $arResult['ID'] . '/';
    if(isset($clientAgent['DEALS_RIGHTS']) && $clientAgent['DEALS_RIGHTS']){
        if (in_array($noticeList['e_d']['ID'], $clientAgent['PROPERTY_NOTICE_VALUE'])) {
            $arEventFields = array(
                'CULTURE' => strip_tags($arResult['DISPLAY_PROPERTIES']['CULTURE']['DISPLAY_VALUE']),
                'ID' => $arResult['ID'],
                'URL' => $GLOBALS['host'] . $url,
                'EMAIL' => $clientAgent['USER']['EMAIL'],
            );
            CEvent::Send('CLOSE_DEAL', 's1', $arEventFields);
        }
        if (in_array($noticeList['c_d']['ID'], $clientAgent['PROPERTY_NOTICE_VALUE'])) {
            notice::addNotice($clientAgent['USER']['ID'], 'd', 'Сделка завершена', $url, '#' . $arResult['ID']);
        }
    }*/

    //уведомление для АП
    $url = '/farmer/deals/' . $arResult['ID'] . '/';
    if (in_array($noticeList['e_d']['ID'], $arResult['FARMER']['PROPERTY_NOTICE_VALUE'])) {
        $arEventFields = array(
            'CULTURE' => strip_tags($arResult['DISPLAY_PROPERTIES']['CULTURE']['DISPLAY_VALUE']),
            'ID' => $arResult['ID'],
            'URL' => $GLOBALS['host'].$url,
            'EMAIL' => $arResult['FARMER']['USER']['EMAIL'],
        );
        CEvent::Send('CLOSE_DEAL', 's1', $arEventFields);
    }
    if (in_array($noticeList['c_d']['ID'], $arResult['FARMER']['PROPERTY_NOTICE_VALUE'])) {
        notice::addNotice($arResult['FARMER']['USER']['ID'], 'd', 'Сделка завершена', $url, '#' . $arResult['ID']);
    }
    if (in_array($noticeList['s_d']['ID'], $arResult['FARMER']['PROPERTY_NOTICE_VALUE']) && $arResult['FARMER']['PROPERTY_PHONE_VALUE']) {
        $phone = str_replace(array('+', ' ', '(', ')', '-'), "", $arResult['FARMER']['PROPERTY_PHONE_VALUE']);
        notice::sendNoticeSMS($phone, 'Сделка завершена: '.$GLOBALS['host'].$url);
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
            CEvent::Send('CLOSE_DEAL', 's1', $arEventFields);
        }
        if (in_array($noticeList['c_d']['ID'], $farmerAgent['PROPERTY_NOTICE_VALUE'])) {
            notice::addNotice($farmerAgent['USER']['ID'], 'd', 'Сделка завершена', $url, '#' . $arResult['ID']);
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
        CEvent::Send('CLOSE_DEAL', 's1', $arEventFields);
    }
    if (in_array($noticeList['c_d']['ID'], $arResult['PARTNER']['PROPERTY_NOTICE_VALUE'])) {
        notice::addNotice($arResult['PARTNER']['USER']['ID'], 'd', 'Сделка завершена', $url, '#' . $arResult['ID']);
    }
    if (in_array($noticeList['s_d']['ID'], $arResult['PARTNER']['PROPERTY_NOTICE_VALUE']) && $arResult['PARTNER']['PROPERTY_PHONE_VALUE']) {
        $phone = str_replace(array('+', ' ', '(', ')', '-'), "", $arResult['PARTNER']['PROPERTY_PHONE_VALUE']);
        notice::sendNoticeSMS($phone, 'Сделка завершена: '.$GLOBALS['host'].$url);
    }

    //сохранение данных для оценки
    $arFields = array(
        'IBLOCK_ID' => rrsIblock::getIBlockId('client_marks'),
        'ACTIVE' => 'Y',
        'NAME' => date('d.m.Y H:i:s'),
        'PROPERTY_VALUES' => array(
            'DEAL' => $arResult['ID'],
            'CLIENT' => $arResult['PROPERTIES']['CLIENT']['VALUE'],
            'FARMER' => $arResult['PROPERTIES']['FARMER']['VALUE'],
            'PARTNER' => $arResult['PROPERTIES']['PARTNER']['VALUE'],
        ),
    );

    if ($markId = $el->Add($arFields)) {
        $url = '/partner/deals/' . $arResult['ID'] . '/?page=mark';
        if ($arResult['PARTNER']['USER']['UF_API_KEY'])
            $url .= '&dkey='.$arResult['PARTNER']['USER']['UF_API_KEY'];
        $arEventFields = array(
            'CULTURE' => strip_tags($arResult['DISPLAY_PROPERTIES']['CULTURE']['DISPLAY_VALUE']),
            'ID' => $arResult['ID'],
            'URL' => $GLOBALS['host'].$url,
            'EMAIL' => $arResult['PARTNER']['USER']['EMAIL'],
        );
        CEvent::Send('PARTNER_MARK_DEAL', 's1', $arEventFields);

        $url = '/farmer/deals/' . $arResult['ID'] . '/?page=mark';
        if ($arResult['FARMER']['USER']['UF_API_KEY'])
            $url .= '&dkey='.$arResult['FARMER']['USER']['UF_API_KEY'];
        $arEventFields = array(
            'CULTURE' => strip_tags($arResult['DISPLAY_PROPERTIES']['CULTURE']['DISPLAY_VALUE']),
            'ID' => $arResult['ID'],
            'URL' => $GLOBALS['host'].$url,
            'EMAIL' => $arResult['FARMER']['USER']['EMAIL'],
        );
        CEvent::Send('FARMER_MARK_DEAL', 's1', $arEventFields);

    }

    LocalRedirect($arParams['SELF_URL']);
}

if ($_GET['mark'] == 'ok') {
?>
    <script type="application/javascript">
        $(document).ready(function() {
            alert('Спасибо за вашу оценку');
        });
    </script>
<?
}
?>