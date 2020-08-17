<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var string $componentPath */
/** @var string $componentName */
/** @var string $componentTemplate */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */
$this->setFrameMode(false);

if (!CModule::IncludeModule("iblock")) {
	ShowError(GetMessage("CC_BIEAF_IBLOCK_MODULE_NOT_INSTALLED"));
	return;
}

$arElement = false;
$arParams["ID"] = intval($_REQUEST["id"]);

if(!is_array($arParams["GROUPS"]))
	$arParams["GROUPS"] = array();

$agentObj = new agent();
$arResult['CLIENTS_DATA'] = $agentObj->getClientsForSelect($USER->GetID(), false, true, false, true);

$bAllowAccess = true;

if(count($arResult['CLIENTS_DATA']) == 0){
    LocalRedirect('/client_agent/request/');
    exit;
}
$arResult['RIGHTS_LIST'] = rrsIblock::getPropListKey('client_agent_link', 'AGENT_RIGHTS', 'control_w_deals');

$arResult["ERRORS"] = array();

if ($bAllowAccess) {
    //права пользователя на копирование данного запроса
    if ($arParams["ID"] > 0) {
        $arFilter = array(
            "IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
            "IBLOCK_ID" => $arParams["IBLOCK_ID"],
            "ID" => $arParams["ID"]
        );
        $rsIBlockElements = CIBlockElement::GetList(array("ID" => "DESC"), $arFilter);
        if ($rsIBlockElements->SelectedRowsCount() > 0){
            //request exists
            $bAllowAccess = true;
            $arElement = $rsIBlockElements->Fetch();
        }
    }
}
else {
    //no access
    ShowError(GetMessage("IBLOCK_ADD_ACCESS_DENIED"));
}

/*if ($bAllowAccess && $GLOBALS['DEMO'] != 'Y') {
    $linkedPartner = client::getLinkedPartner($USER->GetID());
    if (!$linkedPartner) {
        ShowError(GetMessage("RRS_NO_LINKED_PARTNER"));
        $bAllowAccess = false;
    }
}*/

if ($bAllowAccess) {
    if ($arParams['ID'] > 0) {
        //get request information
        $arResult["ELEMENT"] = $arElement;

        //load element properties
        $rsElementProperties = CIBlockElement::GetProperty($arParams["IBLOCK_ID"], $arElement["ID"], array("sort" => "asc"));
        $arResult["ELEMENT_PROPERTIES"] = array();
        while ($arElementProperty = $rsElementProperties->Fetch()) {
            if(!array_key_exists($arElementProperty["CODE"], $arResult["ELEMENT_PROPERTIES"]))
                $arResult["ELEMENT_PROPERTIES"][$arElementProperty["CODE"]] = array();

            if(is_array($arElementProperty["VALUE"])) {
                $htmlvalue = array();
                foreach($arElementProperty["VALUE"] as $k => $v) {
                    if(is_array($v)) {
                        $htmlvalue[$k] = array();
                        foreach($v as $k1 => $v1)
                            $htmlvalue[$k][$k1] = htmlspecialcharsbx($v1);
                    }
                    else {
                        $htmlvalue[$k] = htmlspecialcharsbx($v);
                    }
                }
            }
            else {
                $htmlvalue = htmlspecialcharsbx($arElementProperty["VALUE"]);
            }

            if ($arElementProperty["PROPERTY_TYPE"] == 'F') {
                $htmlvalue = CFile::GetFileArray($arElementProperty["VALUE"]);
            }

            if ($arElementProperty["MULTIPLE"] == "Y") {
                $arResult["ELEMENT_PROPERTIES"][$arElementProperty["CODE"]][] = array(
                    "ID" => htmlspecialcharsbx($arElementProperty["ID"]),
                    "CODE" => $arElementProperty["CODE"],
                    "VALUE" => $htmlvalue,
                    "~VALUE" => $arElementProperty["VALUE"],
                    "VALUE_ID" => htmlspecialcharsbx($arElementProperty["PROPERTY_VALUE_ID"]),
                    "VALUE_ENUM" => htmlspecialcharsbx($arElementProperty["VALUE_ENUM"]),
                    "DESCRIPTION" => $arElementProperty["DESCRIPTION"],
                );
            }
            else {
                $arResult["ELEMENT_PROPERTIES"][$arElementProperty["CODE"]] = array(
                    "ID" => htmlspecialcharsbx($arElementProperty["ID"]),
                    "CODE" => $arElementProperty["CODE"],
                    "VALUE" => $htmlvalue,
                    "~VALUE" => $arElementProperty["VALUE"],
                    "VALUE_ID" => htmlspecialcharsbx($arElementProperty["PROPERTY_VALUE_ID"]),
                    "VALUE_ENUM" => htmlspecialcharsbx($arElementProperty["VALUE_ENUM"]),
                    "DESCRIPTION" => $arElementProperty["DESCRIPTION"],
                );
            }
        }
    }
}

if ($bAllowAccess) {
    if ($arParams['ID'] > 0) {
        //get request params
        $arResult['ELEMENT_PARAMS'] = current(client::getParamsList(array($arElement['ID'])));
        $arResult['ELEMENT_COST'] = current(client::getCostList(array($arElement['ID'])));

        $arPrices = client::basePriceCalculation($arResult['ELEMENT_PROPERTIES']['CLIENT']['VALUE'], $arResult['ELEMENT_PROPERTIES']['CULTURE']['VALUE'], $arResult['ELEMENT_PARAMS']);

        $arResult['CLIENT_WAREHOUSES'] = $arPrices['WAREHOUSES'];
        $arResult['PRICES'] = $arPrices['PRICES'];
    }

    $clientId = 0;
    if(isset($_REQUEST['client_id'])
        && is_numeric($_REQUEST['client_id'])
        && $_REQUEST['client_id'] > 0
    ){
        $clientId = $_REQUEST['client_id'];
    }

    if (!empty($_REQUEST['save'])) {

        //сохранение запроса покупателя
        $arResult['POST'] = $_REQUEST;

        //проверка прав агента покупателя на сохранение запроса
        $user_right = 'n';
        if(isset($arResult['CLIENTS_DATA'][$clientId])){
            if(!isset($arResult['CLIENTS_DATA'][$clientId]['VERIFIED'])
                || $arResult['CLIENTS_DATA'][$clientId]['VERIFIED'] != 'Y'
            ){
                $user_right = 'nv';
            }elseif(!isset($arResult['CLIENTS_DATA'][$clientId]['UF_DEMO'])
                || $arResult['CLIENTS_DATA'][$clientId]['UF_DEMO'] == 1
            ){
                $user_right = 'nd';
            }elseif(!isset($arResult['CLIENTS_DATA'][$clientId]['LINK_DOC'])
                || $arResult['CLIENTS_DATA'][$clientId]['LINK_DOC'] != 'Y'
            ){
                $user_right = 'ndoc';
            }else{
                $user_right = 'y';
            }
        }

        //если у агента покупателя есть права на созданеи запроса для данного покупателя
        if($user_right == 'y'){

            $oElement = new CIBlockElement();
            $arUpdateValues = $arUpdatePropertyValues = array();

            $arUpdateValues['IBLOCK_ID'] = $arParams['IBLOCK_ID'];
            $arUpdateValues['ACTIVE'] = 'Y';
            $arUpdateValues['MODIFIED_BY'] = $clientId;
            $arUpdateValues['ACTIVE_FROM'] = date("d.m.Y H:i:s");
            $arUpdateValues['ACTIVE_TO'] = date("d.m.Y H:i:s",
                mktime(
                    date("H", strtotime($arUpdateValues["ACTIVE_FROM"])),
                    date("i", strtotime($arUpdateValues["ACTIVE_FROM"])),
                    date("s", strtotime($arUpdateValues["ACTIVE_FROM"])),
                    date("m", strtotime($arUpdateValues["ACTIVE_FROM"])),
                    date("d", strtotime($arUpdateValues["ACTIVE_FROM"]))+1,
                    date("Y", strtotime($arUpdateValues["ACTIVE_FROM"]))
                )
            );

            $arUpdateValues['NAME'] = date("d.m.Y H:i:s");

            $arUpdatePropertyValues['CLIENT'] = $clientId;

            $clientProfile = client::getProfile($clientId);
            if ($clientProfile['PROPERTY_NDS_CODE'] == 'Y') {
                $arUpdatePropertyValues['USER_NDS'] = rrsIblock::getPropListKey('client_request', 'USER_NDS', 'yes');;
            }
            else {
                $arUpdatePropertyValues['USER_NDS'] = rrsIblock::getPropListKey('client_request', 'USER_NDS', 'no');;
            }

            $arUpdatePropertyValues['GROUP'] = $_REQUEST["cgroup"];
            $arUpdatePropertyValues['CULTURE'] = $_REQUEST["csort"];
            $arUpdatePropertyValues['VOLUME'] = $_REQUEST["volume"];
            $arUpdatePropertyValues['REMAINS'] = $_REQUEST["volume"];
            $arUpdatePropertyValues['DELIVERY'] = $_REQUEST["delivery"];
            if ($_REQUEST['delivery'] == 385) {
                $arUpdatePropertyValues['REMOTENESS'] = $_REQUEST["remoteness"];
            }

            if (sizeof($_REQUEST['docs']) > 0) {
                $n = 0;
                foreach ($_REQUEST['docs'] as $key => $val) {
                    $arUpdatePropertyValues['DOCS']["n".$n] = array("VALUE" => $key);
                    $n++;
                }
            }

            $arUpdatePropertyValues['PAYMENT'] = $_REQUEST["payment"];

            $paymentCode = rrsIblock::getPropListId('client_request', 'PAYMENT', $_REQUEST['payment']);
            if ($paymentCode == 'pre') {
                $arUpdatePropertyValues['PERCENT'] = $_REQUEST["percent"];
            }
            elseif ($paymentCode == 'post') {
                $arUpdatePropertyValues['DELAY'] = $_REQUEST["delay"];
            }

            if (sizeof($_REQUEST['nds']) > 0) {
                $n = 0;
                foreach ($_REQUEST['nds'] as $key => $val) {
                    $arUpdatePropertyValues['NDS']["n".$n] = array("VALUE" => $key);
                    $n++;
                }
            }

            //$arUpdatePropertyValues['URGENCY'] = $_REQUEST["urgency"];
            $arUpdatePropertyValues['ACTIVE'] = rrsIblock::getPropListKey('client_request', 'ACTIVE', 'yes');

            $arUpdateValues["PROPERTY_VALUES"] = $arUpdatePropertyValues;

            if (!$arParams["ID"] = $oElement->Add($arUpdateValues)) {
                $arResult["ERRORS"][] = $oElement->LAST_ERROR;
            }

            if (empty($arResult["ERRORS"])) {
                //сохранение параметров качества запроса
                foreach ($_REQUEST["param"] as $key => $param) {
                    $arParamValues = $arParamPropertyValues = array();

                    $arParamValues["NAME"] = date("d.m.Y H:i:s");
                    $arParamValues["IBLOCK_ID"] = rrsIblock::getIBlockId('client_request_chars');

                    $arParamPropertyValues['REQUEST'] = $arParams["ID"];
                    $arParamPropertyValues['CULTURE'] = $_REQUEST["csort"];
                    $arParamPropertyValues['QUALITY'] = $key;
                    if ($param["LBASE"] > 0) {
                        $arParamPropertyValues['LBASE'] = $param["LBASE"];
                    }
                    else {
                        $arParamPropertyValues['BASE'] = $param["BASE"];
                        $arParamPropertyValues['MIN'] = $param["MIN"];
                        $arParamPropertyValues['MAX'] = $param["MAX"];
                    }
                    if (is_array($param["DUMP"])) {
                        $n = 0;
                        foreach ($param["DUMP"]["MIN"] as $i => $val) {
                            $arParamPropertyValues['DUMPING']["n".$n] = array("VALUE" => "[".$val.";".$param["DUMP"]["MAX"][$i]."]:".$param["DUMP"]["DISCOUNT"][$i]);
                            $n++;
                        }
                    }

                    if (isset($param["DUMP"]['STRAIGHT']) && $param["DUMP"]['STRAIGHT'] == 'Y') {
                        $arParamPropertyValues['DIRECT_DUMP'] = 'Y';
                    }

                    $arParamValues["PROPERTY_VALUES"] = $arParamPropertyValues;

                    $ID = $oElement->Add($arParamValues);
                }

                //сохранение стоимостей
                if (is_array($_REQUEST["warehouse"])) {
                    $centerList = client::getCentersByWH($_REQUEST['csort'], array_keys($_REQUEST['warehouse']));
                    $nds = rrsIblock::getConst('nds');

                    foreach ($_REQUEST["warehouse"] as $key => $store) {
                        if ($clientProfile['PROPERTY_NDS_CODE'] == 'N') {
                            $parityPrice = round($store * (1. + 0.01 * $nds), 0);
                        }
                        else {
                            $parityPrice = round($store, 0);
                        }

                        $arStoreValues = $arStorePropertyValues = array();

                        $arStoreValues["NAME"] = date("d.m.Y H:i:s");
                        $arStoreValues["IBLOCK_ID"] = rrsIblock::getIBlockId('client_request_cost');

                        $arStorePropertyValues['REQUEST'] = $arParams["ID"];
                        $arStorePropertyValues['CULTURE'] = $_REQUEST['csort'];
                        $arStorePropertyValues['WAREHOUSE'] = $key;
                        $arStorePropertyValues['CENTER'] = $centerList[$key];
                        $arStorePropertyValues['PRICE'] = $store;
                        //$arStorePropertyValues['PARITY_PRICE'] = $arPrices[$centerList[$key]]['PRICE_'.strtoupper($urCode)];
                        $arStorePropertyValues['PARITY_PRICE'] = $parityPrice;

                        $arStoreValues["PROPERTY_VALUES"] = $arStorePropertyValues;

                        $ID = $oElement->Add($arStoreValues);
                    }
                }

                //рассылка уведомлений
                $linkedPartner = client::getLinkedPartner($clientId);
                $noticeList = notice::getNoticeList();
                $partnerProfile = partner::getProfile($linkedPartner, true);
                $culture = culture::getName($_REQUEST['csort']);

                $url = '/profile/?uid='.$clientId;
                if (in_array($noticeList['e_r']['ID'], $partnerProfile['PROPERTY_NOTICE_VALUE'])) {
                    $arEventFields = array(
                        'URL' => $GLOBALS['host'].$url,
                        'CLIENT_ID' => $clientId,
                        'REQUEST_ID' => $arParams['ID'],
                        'CULTURE' => $culture['NAME'],
                        'EMAIL' => $partnerProfile['USER']['EMAIL'],
                    );
                    CEvent::Send('PARTNER_CLIENT_ADD_REQUEST', 's1', $arEventFields);
                }
                if (in_array($noticeList['c_r']['ID'], $partnerProfile['PROPERTY_NOTICE_VALUE'])) {
                    notice::addNotice($partnerProfile['USER']['ID'], 'r', 'Новый запрос покупателя', $url, '#' . $clientId);
                }
                if (in_array($noticeList['s_r']['ID'], $partnerProfile['PROPERTY_NOTICE_VALUE']) && $partnerProfile['PROPERTY_PHONE_VALUE']) {
                    $phone = str_replace(array('+', ' ', '(', ')', '-'), "", $partnerProfile['PROPERTY_PHONE_VALUE']);
                    notice::sendNoticeSMS($phone, 'Новый запрос покупателя: '.$GLOBALS['host'].$url);
                }

                //поиск подходящих для запроса товаров поставщиков
                $q = deal::searchSuitableOffers($arParams['ID']);
                CIBlockElement::SetPropertyValuesEx($arParams['ID'], $arParams['IBLOCK_ID'], array('F_NUM' => $q['FARMER_CNT']));

                LocalRedirect($arParams['LIST_URL'].'?q='.$q['FARMER_CNT'].'&best_price='. $q['FARMER_BEST_PRICE_CNT']);
                exit();
            }
        }
    }

    $arResult['CULTURE_GROUP_LIST'] = rrsIblock::getElementList(rrsIblock::getIBlockId('cultures_groups'));
    $arResult['DELIVERY_LIST'] = rrsIblock::getElementList(rrsIblock::getIBlockId('delivery4client'));
    $arResult['DOCS_LIST'] = rrsIblock::getElementList(rrsIblock::getIBlockId('need_docs'));
    $arResult['PAYMENT_LIST'] = rrsIblock::getPropListKey('client_request', 'PAYMENT');
    $arResult['NDS_LIST'] = rrsIblock::getPropListKey('client_request', 'NDS');
    //$arResult['MARGIN_LIST'] = rrsIblock::getElementList(rrsIblock::getIBlockId('urgency'));

    /*$arResult['PROFILE'] = client::getProfile($clientId);

    $arResult['LINKED_PARTNER'] = client::getLinkedPartner($clientId);
    $arResult['VERIFIED_PARTNER'] = client::getLinkedPartnerVerified($clientId);*/

    $arResult["MESSAGE"] = '';
    if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_REQUEST["strIMessage"]) && is_string($_REQUEST["strIMessage"]))
        $arResult["MESSAGE"] = htmlspecialcharsbx($_REQUEST["strIMessage"]);

    $this->includeComponentTemplate();
}