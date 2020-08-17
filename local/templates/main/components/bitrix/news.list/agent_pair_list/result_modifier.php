<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
$userIds = array();
CModule::IncludeModule('iblock');

$arResult['OFFERS_APPROVED'] = array(); // массив данных подтверждения качества товаров
$arResult['SHOW_SERVICES'] = array(// массив с доп. опциями, для вывода в паре (сначала отмеченные, затем нет)
    'IS_ADD_CERT' => true,
    'IS_BILL_OF_HEALTH' => true,
    'IS_VET_CERT' => true,
    'IS_QUALITY_CERT' => true,
    'IS_TRANSFER' => true,
    'IS_SECURE_DEAL' => true,
    'IS_AGENT_SUPPORT' => true,
);

$arResult['ALOWED_TO_CHANGE_OPTIONS'] = array(// массив с доп. опциями, которые может менять (ставить/снимать) покупатель в агентских предложениях
    'IS_ADD_CERT' => true,
    'IS_BILL_OF_HEALTH' => true,
    'IS_VET_CERT' => true,
    'IS_QUALITY_CERT' => true,
    'IS_TRANSFER' => true,
    'IS_SECURE_DEAL' => true,
    'IS_AGENT_SUPPORT' => true,
);
global $USER;
$partnerId = $USER->GetID();

//получаем подтверждение качества (не для поставщиков)
if($arParams['USER_TYPE'] != 'FARMER') {
    $arOffersIds = array();
    foreach ($arResult['ITEMS'] as $arItem) {
        $arOffersIds[$arItem['PROPERTIES']['OFFER']['VALUE']] = true;
    }

    if(count($arOffersIds) > 0){
        $arResult['OFFERS_APPROVED'] = partner::getOffersApproves(array_keys($arOffersIds));
    }
}

    //получение данных клиентов
    $ibCode = 'client_profile';
    $userIds = array();
    foreach ($arResult['ITEMS'] as $i => $arItem) {
        if ($arItem['PROPERTIES']['CLIENT']['VALUE'] > 0) {
            $userIds[$arItem['PROPERTIES']['CLIENT']['VALUE']] = true;
        }
        $arResult['ITEMS'][$i]['PAIR_TYPE'] = 'c';
        if(isset($arItem['PROPERTIES']['COFFER_TYPE']['VALUE'])){
            if($arItem['PROPERTIES']['COFFER_TYPE']['VALUE'] == 'p'){
                $arResult['ITEMS'][$i]['PAIR_TYPE'] = 'p';
            }
        }
        //получение организатора поставщика
        if(!empty($arItem['PROPERTIES']['COFFER_BY_PARTNER']['VALUE'])){
            $partnersIds[$arItem['PROPERTIES']['COFFER_BY_PARTNER']['VALUE']] = true;
            $arResult['ITEMS'][$i]['F_PARTNER_ID'] = $arItem['PROPERTIES']['COFFER_BY_PARTNER']['VALUE'];
        }
        if(empty($arResult['ITEMS'][$i]['F_PARTNER_ID'])){
            //получение последнего организатора для поставщика
            $arTemp = farmer::getLinkedPartnerList($arItem['PROPERTIES']['FARMER']['VALUE'], true);
            if(!empty($arTemp[0])) {
                $partnersIds[$arTemp[0]] = true;
                $arResult['ITEMS'][$i]['F_PARTNER_ID'] = $arTemp[0];
            }
        }

        if($arResult['ITEMS'][$i]['PAIR_TYPE'] == 'p'){
            //получение организатора покупателя
            if(!empty($arItem['PROPERTIES']['DEAL_REFERER']['VALUE'])){
                $partnersIds[$arItem['PROPERTIES']['DEAL_REFERER']['VALUE']] = true;
                $arResult['ITEMS'][$i]['C_PARTNER_ID'] = $arItem['PROPERTIES']['DEAL_REFERER']['VALUE'];
            }else{
                if ($arItem['PROPERTIES']['CLIENT']['VALUE'] > 0) {
                    //получаем последнего орг. по дате привязки
                    $partners = client::getLinkedPartnerList($arItem['PROPERTIES']['CLIENT']['VALUE']);
                    if(sizeof($partners)>0){
                        $partnersIds[$partners[0]] = true;
                        $arResult['ITEMS'][$i]['C_PARTNER_ID'] = $partners[0];
                    }
                }
            }
        }
    }

    if (is_array($partnersIds) && sizeof($partnersIds) > 0) {
        $partnerIbCode = 'partner_profile';
        $user_type_ip = rrsIblock::getPropListKey($partnerIbCode, 'UL_TYPE', 'ip');

        $res = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId($partnerIbCode),
                'ACTIVE' => 'Y',
                'PROPERTY_USER' => array_keys($partnersIds)
            ),
            false,
            false,
            array(
                'ID',
                'NAME',
                'PROPERTY_PHONE',
                'PROPERTY_USER'
            )
        );
        while ($ob = $res->Fetch()) {
            $arResult['USER_LIST'][$ob['PROPERTY_USER_VALUE']] = $ob;
        }
        //получение данных пользователя
        $res = CUser::GetList(
            ($by = 'id'), ($order = 'asc'),
            array(
                'ID' => implode(' | ', array_keys($partnersIds))
            ),
            array('FIELDS' => array('ID', 'NAME', 'LAST_NAME', 'SECOND_NAME'))
        );
        while($ob = $res->Fetch()){
            if(isset($arResult['USER_LIST'][$ob['ID']])){
                $arResult['USER_LIST'][$ob['ID']]['USER_DATA'] = trim($ob['LAST_NAME'].' '.$ob['NAME'].' '.$ob['SECOND_NAME']);
            }
        }
    }

    $user_type_ip = rrsIblock::getPropListKey('client_profile', 'UL_TYPE', 'ip');

    $res = CIBlockElement::GetList(
        array('ID' => 'ASC'),
        array(
            'IBLOCK_ID' => rrsIblock::getIBlockId($ibCode),
            'ACTIVE' => 'Y',
            'PROPERTY_USER' => array_keys($userIds)
        ),
        false,
        false,
        array(
            'ID',
            'NAME',
            'PROPERTY_FULL_COMPANY_NAME',
            'PROPERTY_IP_FIO',
            'PROPERTY_UL_TYPE',
            'PROPERTY_INN',
            'PROPERTY_YUR_ADRESS',
            'PROPERTY_POST_ADRESS',
            'PROPERTY_KPP',
            'PROPERTY_OGRN',
            'PROPERTY_PHONE',
            'PROPERTY_OKPO',
            'PROPERTY_USER'
        )
    );
    while($ob = $res->Fetch()){
        if($user_type_ip == $ob['PROPERTY_UL_TYPE_ENUM_ID']){
            $ob['COMPANY'] = 'ИП ' . $ob['PROPERTY_IP_FIO_VALUE'];
            $ob['IS_IP']   = true;
        }
        else{
            $ob['COMPANY'] = $ob['PROPERTY_FULL_COMPANY_NAME_VALUE'];
            $ob['IS_IP']   = false;
        }
        $arResult['USER_LIST'][$ob['PROPERTY_USER_VALUE']] = $ob;
    }

    //получение данных пользователя
    $res = CUser::GetList(
        ($by = 'id'), ($order = 'asc'),
        array(
            'ID' => implode(' | ', array_keys($userIds))
        ),
        array('FIELDS' => array('ID', 'NAME', 'LAST_NAME', 'SECOND_NAME'))
    );
    while($ob = $res->Fetch()){
        if(isset($arResult['USER_LIST'][$ob['ID']])){
            $arResult['USER_LIST'][$ob['ID']]['USER_DATA'] = trim($ob['LAST_NAME'].' '.$ob['NAME'].' '.$ob['SECOND_NAME']);
        }
    }
    //получение данных поставщиков
    $ibCode = 'farmer_profile';
    $userIds = array();
    foreach ($arResult['ITEMS'] as $arItem) {
        if ($arItem['PROPERTIES']['FARMER']['VALUE'] > 0) {
            $userIds[$arItem['PROPERTIES']['FARMER']['VALUE']] = true;
        }
    }

    $res = CIBlockElement::GetList(
        array('ID' => 'ASC'),
        array(
            'IBLOCK_ID' => rrsIblock::getIBlockId($ibCode),
            'ACTIVE' => 'Y',
            'PROPERTY_USER' => array_keys($userIds)
        ),
        false,
        false,
        array(
            'ID',
            'NAME',
            'PROPERTY_FULL_COMPANY_NAME',
            'PROPERTY_IP_FIO',
            'PROPERTY_UL_TYPE',
            'PROPERTY_INN',
            'PROPERTY_YUR_ADRESS',
            'PROPERTY_POST_ADRESS',
            'PROPERTY_KPP',
            'PROPERTY_OGRN',
            'PROPERTY_PHONE',
            'PROPERTY_OKPO',
            'PROPERTY_USER'
        )
    );
    while ($ob = $res->Fetch()) {
        $ulType = rrsIblock::getPropListId($ibCode, 'UL_TYPE', $ob['PROPERTY_UL_TYPE_ENUM_ID']);
        if ($ulType == 'ip') {
            $ob['COMPANY'] = 'ИП ' . $ob['PROPERTY_IP_FIO_VALUE'];
            $ob['IS_IP']   = true;
        }
        else {
            $ob['COMPANY'] = $ob['PROPERTY_FULL_COMPANY_NAME_VALUE'];
            $ob['IS_IP']   = false;
        }
        $arResult['USER_LIST'][$ob['PROPERTY_USER_VALUE']] = $ob;
    }

    //получение данных пользователя
    $res = CUser::GetList(
        ($by = 'id'), ($order = 'asc'),
        array(
            'ID' => implode(' | ', array_keys($userIds))
        ),
        array('FIELDS' => array('ID', 'NAME', 'LAST_NAME', 'SECOND_NAME'))
    );
    while($ob = $res->Fetch()){
        if(isset($arResult['USER_LIST'][$ob['ID']])){
            $arResult['USER_LIST'][$ob['ID']]['USER_DATA'] = trim($ob['LAST_NAME'].' '.$ob['NAME'].' '.$ob['SECOND_NAME']);
        }
    }


$requestIds = array();
$offersIds = array();
$WHClientIds = array();
$WHFarmerIds = array();
$offers_cultures = array();
$arResult['C_DATES'] = array(); //данные создания пары для определения разрешения добавления в чёрный список

$arCulturesGroup = culture::getCulturesGroup();
$arResult['AX_TARIFS'] = model::getAgrohelperTariffs();


foreach ($arResult['ITEMS'] as $arItem) {
    // Запросы
    if ($arItem['PROPERTIES']['REQUEST']['VALUE'] > 0) {
        $requestIds[$arItem['PROPERTIES']['REQUEST']['VALUE']] = true;
    }
    // товары
    if ($arItem['PROPERTIES']['OFFER']['VALUE'] > 0) {
        $offersIds[$arItem['PROPERTIES']['OFFER']['VALUE']] = true;

        //соответствие культуры и товара (нужно для составления списка активных характеристик товара)
        $offers_cultures[$arItem['PROPERTIES']['OFFER']['VALUE']] = $arItem['PROPERTIES']['CULTURE']['VALUE'];
    }
    // склад клиента
    if ($arItem['PROPERTIES']['CLIENT_WAREHOUSE']['VALUE'] > 0) {
        $WHClientIds[$arItem['PROPERTIES']['CLIENT_WAREHOUSE']['VALUE']] = true;
    }
    // склад фермера
    if ($arItem['PROPERTIES']['FARMER_WAREHOUSE']['VALUE'] > 0) {
        $WHFarmerIds[$arItem['PROPERTIES']['FARMER_WAREHOUSE']['VALUE']] = true;
    }
}

$arResult['REQUEST_PARAMS'] = array();
$arResult['OFFER_PARAMS'] = array();
$arResult['REQUEST_PAYMENTS'] = array();
$arResult['CLIENT_WAREHOUSES_LIST'] = array();
$arResult['FARMER_WAREHOUSES_LIST'] = array();
$temp_offers_params = array();
if(count($offersIds) > 0) {
    $temp_offers_params = farmer::getParamsList(array_keys($offersIds));
}
if(count($WHFarmerIds) > 0) {
    $arResult['FARMER_WAREHOUSES_LIST'] = farmer::getWarehouseParamsList(array_keys($WHFarmerIds));
}
if(count($WHClientIds) > 0) {
    $arResult['CLIENT_WAREHOUSES_LIST'] = client::getWarehouseParamsList(array_keys($WHClientIds));
}
if(count($requestIds) > 0){
    //$arResult['REQUEST_PARAMS'] = client::getParamsList(array_keys($offersIds));

    $payment_enum_data = rrsIblock::getPropListKey('client_request', 'PAYMENT');
    $res = CIBlockElement::GetList(
        array('ID' => 'DESC'),
        array(
            'IBLOCK_ID' => rrsIblock::getIBlockId('client_request'),
            'ACTIVE' => 'Y',
            'ID' => array_keys($requestIds)
        ),
        false,
        false,
        array('ID', 'PROPERTY_PAYMENT.ENUM_ID', 'PROPERTY_PAYMENT', 'PROPERTY_PERCENT', 'PROPERTY_DELAY')
    );
    while($data = $res->Fetch()){
        $arResult['REQUEST_PAYMENTS'][$data['ID']] = array(
            'PAYMENT_NAME' => $data['PROPERTY_PAYMENT_VALUE'],
            'PAYMENT_XML_CODE' => ($payment_enum_data['pre']['ID'] == $data['PROPERTY_PAYMENT_ENUM_ID'] ? 'pre' : 'post'),
            'PERCENT' => $data['PROPERTY_PERCENT_VALUE'],
            'DELAY' => $data['PROPERTY_DELAY_VALUE']
        );
    }
}

$res = CIBlockElement::GetList(
    array('ID' => 'DESC'),
    array('IBLOCK_ID' => rrsIblock::getIBlockId('characteristics'), 'ACTIVE' => 'Y'),
    false,
    false,
    array('ID', 'NAME', 'PROPERTY_CULTURE', 'PROPERTY_QUALITY', 'PROPERTY_QUALITY.NAME')
);
while ($ob = $res->Fetch()) {
    $arResult['PARAMS_INFO'][$ob['PROPERTY_CULTURE_VALUE']][$ob['PROPERTY_QUALITY_VALUE']] = array(
        'ID' => $ob['ID'],
        'QUALITY_NAME' => $ob['PROPERTY_QUALITY_NAME']
    );
}

//берем из параметров товара только те данные у которых есть активная запись в ИБ характеристик
foreach($temp_offers_params as $cur_offer => $cur_data){
    foreach($cur_data as $cur_id => $param) {
        if(!isset($offers_cultures[$cur_offer])
            ||
            !isset($arResult['PARAMS_INFO'][$offers_cultures[$cur_offer]][$param['QUALITY_ID']]['QUALITY_NAME'])
        ){
            continue;
        }
        $arResult['OFFER_PARAMS'][$cur_offer][$cur_id] = $param;
    }
}

$res = CIBlockElement::GetList(
    array('SORT' => 'ASC', 'ID' => 'ASC'),
    array('IBLOCK_ID' => rrsIblock::getIBlockId('quality'), 'ACTIVE' => 'Y'),
    false,
    false,
    array('ID', 'NAME', 'PROPERTY_UNIT')
);
while ($ob = $res->Fetch()) {
    $arResult['UNIT_INFO'][$ob['ID']] = $ob['PROPERTY_UNIT_VALUE'];
}

//отправляем данные в component_epilog.php
$cp = $this->__component;
$cp->SetResultCacheKeys(array('ALOWED_TO_CHANGE_OPTIONS', 'SHOW_SERVICES'));