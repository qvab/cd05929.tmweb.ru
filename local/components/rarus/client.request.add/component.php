<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
$this->setFrameMode(false);

use Bitrix\Main\Application,
    Bitrix\Main\Web\Uri;

if (!CModule::IncludeModule("iblock")) {
	ShowError(GetMessage("CC_BIEAF_IBLOCK_MODULE_NOT_INSTALLED"));
	return;
}

$arResult = array();

if (!is_array($arParams['GROUPS']))
    $arParams['GROUPS'] = array();

$arGroups = CUser::GetUserGroup($arParams['CLIENT_ID']);

//проверка, может ли пользователь добавлять/копировать запрос
$bAllowAccess = count(array_intersect($arGroups, $arParams['GROUPS'])) > 0;


$arElement = false;
$arParams['ID'] = intval($_REQUEST['id']);

if ($bAllowAccess) {
    //у пользователя есть доступ
    if ($arParams['ID'] > 0) {

        global $APPLICATION;
        $APPLICATION->SetTitle('Изменение запроса');

        //копирование элемента
        $arFilter = array(
            'IBLOCK_ID' => $arParams['IBLOCK_ID'],
            'ID' => $arParams['ID']
        );
        $rsIBlockElements = CIBlockElement::GetList(array('ID' => 'DESC'), $arFilter);
        if ($arElement = $rsIBlockElements->Fetch()) {
            //запрос для копирования существует
            $bAllowAccess = true;
        }
        else {
            //нет запроса для копирования
            ShowError(GetMessage("IBLOCK_ADD_ELEMENT_NOT_FOUND"));
            $bAllowAccess = false;
        }
    }
}
else {
    //нет доступа
    ShowError(GetMessage("IBLOCK_ADD_ACCESS_DENIED"));
}

if ($arParams['USER_TYPE'] == 'AGENT') {
    $agentObj = new agent();
    $arResult['CLIENTS_DATA'] = $agentObj->getClientsForSelect($arParams['CLIENT_ID'], false, true, false, true);

    if (count($arResult['CLIENTS_DATA']) < 1) {
        LocalRedirect($arParams['LIST_URL']);
        exit;
    }
    $arResult['RIGHTS_LIST'] = rrsIblock::getPropListKey('client_agent_link', 'AGENT_RIGHTS', 'control_w_deals');
}

if (isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER'] != '') {
    $uriString = $_SERVER['HTTP_REFERER'];
    $uri = new Uri($uriString);
    $arResult['BACK_URL'] = $uri->getPathQuery();
}
else {
    $arResult['BACK_URL'] = $arParams['LIST_URL'];
}

$arResult['ERRORS'] = array();

if ($bAllowAccess) {
    if ($arParams['ID'] > 0) {
        //получаем информацию о запросе
        $arResult['ELEMENT'] = $arElement;

        //получаем свойства элемента
        $rsElementProperties = CIBlockElement::GetProperty($arParams['IBLOCK_ID'], $arElement['ID'], array('sort' => 'asc'));
        $arResult['ELEMENT_PROPERTIES'] = array();
        while ($arElementProperty = $rsElementProperties->Fetch()) {
            if(!array_key_exists($arElementProperty['CODE'], $arResult['ELEMENT_PROPERTIES']))
                $arResult['ELEMENT_PROPERTIES'][$arElementProperty['CODE']] = array();

            if(is_array($arElementProperty['VALUE'])) {
                $htmlvalue = array();
                foreach($arElementProperty['VALUE'] as $k => $v) {
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
                $htmlvalue = htmlspecialcharsbx($arElementProperty['VALUE']);
            }

            if ($arElementProperty["PROPERTY_TYPE"] == 'F') {
                $htmlvalue = CFile::GetFileArray($arElementProperty['VALUE']);
            }


            if($arElementProperty['CODE'] == 'USE_REGIONS') {
                $arResult['ELEMENT_PROPERTIES'][$arElementProperty['CODE']][$arElementProperty["VALUE"]] = true;
            }elseif ($arElementProperty['MULTIPLE'] == 'Y') {
                $arResult['ELEMENT_PROPERTIES'][$arElementProperty['CODE']][] = array(
                    'ID' => htmlspecialcharsbx($arElementProperty['ID']),
                    'CODE' => $arElementProperty['CODE'],
                    'VALUE' => $htmlvalue,
                    '~VALUE' => $arElementProperty['VALUE'],
                    'VALUE_ID' => htmlspecialcharsbx($arElementProperty['PROPERTY_VALUE_ID']),
                    'VALUE_ENUM' => htmlspecialcharsbx($arElementProperty['VALUE_ENUM']),
                    'DESCRIPTION' => $arElementProperty['DESCRIPTION'],
                );
            }
            else {
                $arResult['ELEMENT_PROPERTIES'][$arElementProperty['CODE']] = array(
                    'ID' => htmlspecialcharsbx($arElementProperty["ID"]),
                    'CODE' => $arElementProperty['CODE'],
                    'VALUE' => $htmlvalue,
                    '~VALUE' => $arElementProperty['VALUE'],
                    'VALUE_ID' => htmlspecialcharsbx($arElementProperty['PROPERTY_VALUE_ID']),
                    'VALUE_ENUM' => htmlspecialcharsbx($arElementProperty['VALUE_ENUM']),
                    'DESCRIPTION' => $arElementProperty['DESCRIPTION'],
                );
            }
        }

        if ($arParams['USER_TYPE'] == 'AGENT') {
            //проверка, привязан ли покупатель к агенту
            if (!in_array($arResult['ELEMENT_PROPERTIES']['CLIENT']['VALUE'], array_keys($arResult['CLIENTS_DATA']))) {
                ShowError(GetMessage("REQUEST_ACCESS_DENIED"));
                $bAllowAccess = false;
            }
        }
        else {
            //проверка, является ли покупатель автором запроса
            if ($arResult['ELEMENT_PROPERTIES']['CLIENT']['VALUE'] != $arParams['CLIENT_ID']) {
                ShowError(GetMessage("REQUEST_ACCESS_DENIED"));
                $bAllowAccess = false;
            }
        }
    }
}

if ($bAllowAccess) {
    $arResult['NDS_VAL'] = rrsIblock::getConst('nds');
    if ($arParams['ID'] > 0) {

        //получение параметров запроса
        $arResult['ELEMENT_PARAMS'] = current(client::getParamsList(array($arElement['ID'])));

        //получение стоимостей со складам запроса
        $arResult['ELEMENT_COST'] = current(client::getCostList(array($arElement['ID'])));

        //получение списка базисных цен по складам покупателя (значение по умолчнию берется из копируемого запроса, если его нету, то из мат. модели)
        $req_data = client::getCostList($arElement['ID']);
        $arResult['DEFAULT_PRICES'] = array();
        //комментируем пока, т.к. убрали кнопку - изменить
            foreach($req_data as $cur_req => $cur_wh_data){
                foreach($cur_wh_data as $cur_wh_id => $cur_data){
                    $arResult['DEFAULT_PRICES'][$cur_wh_id] = $cur_data['DDP_PRICE_CLIENT'];
                }
            }
        $arPrices = client::basePriceCalculation($arResult['ELEMENT_PROPERTIES']['CLIENT']['VALUE'], $arResult['ELEMENT_PROPERTIES']['CULTURE']['VALUE'], $arResult['ELEMENT_PARAMS']);
        $arResult['CLIENT_WAREHOUSES'] = $arPrices['WAREHOUSES'];
        $arResult['PRICES'] = $arPrices['PRICES'];
    }

    if ($arParams['USER_TYPE'] == 'AGENT') {
        $clientId = 0;
        if (isset($_REQUEST['client_id']) && is_numeric($_REQUEST['client_id']) && $_REQUEST['client_id'] > 0){
            $clientId = $_REQUEST['client_id'];
        }
    }
    else {
        $clientId = $arParams['CLIENT_ID'];
    }

    //сохранение запроса покупателя
    if (!empty($_REQUEST['save'])) {
        $bIsFullSetDocument = true;
        $arResult['POST'] = $_REQUEST;

        $wh_request_cost = false;

        if(!empty($_REQUEST['warehouse'])) {
            $wh_array = explode('|', $_REQUEST['warehouse']);
            if (sizeof($wh_array) == 2) {
                //если выбран склад и значние корректно
                $wh_request_cost = true;
            }
        }
        //если задан склад и стоимости
        if($wh_request_cost === true){
            // Есть все обязательные документы и права на создание запроса
            if ($bIsFullSetDocument ) {
                $oElement = new CIBlockElement();
                $arUpdateValues = $arUpdatePropertyValues = array();

                $arUpdateValues['IBLOCK_ID'] = $arParams['IBLOCK_ID'];
                $arUpdateValues['ACTIVE'] = 'Y';
                $arUpdateValues['MODIFIED_BY'] = $USER->GetID();
                $arUpdateValues['ACTIVE_FROM'] = date("d.m.Y H:i:s");
                $arUpdateValues['ACTIVE_TO'] = date("d.m.Y H:i:s", strtotime('+90 days'));
                $arUpdateValues['NAME'] = date("d.m.Y H:i:s");

                $arUpdatePropertyValues['CLIENT'] = $clientId;

                $clientNds = client::getNds($clientId);
                if ($clientNds == 'Y')
                    $arUpdatePropertyValues['USER_NDS'] = rrsIblock::getPropListKey('client_request', 'USER_NDS', 'yes');
                else
                    $arUpdatePropertyValues['USER_NDS'] = rrsIblock::getPropListKey('client_request', 'USER_NDS', 'no');

                $arUpdatePropertyValues['GROUP'] = $_REQUEST['cgroup'];
                $arUpdatePropertyValues['CULTURE'] = $_REQUEST['csort'];
                $arUpdatePropertyValues['VOLUME'] = $_REQUEST['volume'];
                $arUpdatePropertyValues['REMAINS'] = $_REQUEST['volume'];
                $arUpdatePropertyValues['DELIVERY'] = $_REQUEST['delivery'];

                //если FCA, то используем выбранные регионы
                if (rrsIblock::getElementCodeById(rrsIblock::getIBlockId('delivery4client'), $_REQUEST['delivery']) == 'N') {
                    if(empty($_REQUEST['regions_list'])
                        || !is_array($_REQUEST['regions_list'])
                    ) {
                        $arResult['ERRORS'][] = 'Не выбран ни один регион';
                    }else{
                        $arUpdatePropertyValues['USE_REGIONS'] = $_REQUEST['regions_list'];
                    }

//                    $arUpdatePropertyValues['REMOTENESS'] = $_REQUEST['remoteness'];
//                    $arUpdatePropertyValues['MIN_REMOTENESS'] = $_REQUEST['min_remoteness'];
                }

                if (sizeof($_REQUEST['docs']) > 0) {
                    $n = 0;
                    foreach ($_REQUEST['docs'] as $key => $val) {
                        $arUpdatePropertyValues['DOCS']["n".$n] = array("VALUE" => $key);
                        $n++;
                    }
                }

                $arUpdatePropertyValues['PAYMENT'] = $_REQUEST['payment'];

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

                $arUpdatePropertyValues['ACTIVE'] = rrsIblock::getPropListKey('client_request', 'ACTIVE', 'yes');
                $arUpdateValues['PROPERTY_VALUES'] = $arUpdatePropertyValues;

                //создание запроса
                if (!$arParams['ID'] = $oElement->Add($arUpdateValues)) {
                    $arResult['ERRORS'][] = $oElement->LAST_ERROR;
                }

                if (empty($arResult['ERRORS'])) {

                    //сохранение параметров качества запроса
                    foreach ($_REQUEST['param'] as $key => $param) {
                        $arParamValues = $arParamPropertyValues = array();

                        $arParamValues['NAME'] = date('d.m.Y H:i:s');
                        $arParamValues['IBLOCK_ID'] = rrsIblock::getIBlockId('client_request_chars');

                        $arParamPropertyValues['REQUEST'] = $arParams['ID'];
                        $arParamPropertyValues['CULTURE'] = $_REQUEST['csort'];
                        $arParamPropertyValues['QUALITY'] = $key;
                        if ($param['LBASE'] > 0) {
                            $arParamPropertyValues['LBASE'] = $param['LBASE'];
                        }
                        else {
                            $arParamPropertyValues['BASE'] = $param['BASE'];
                            $arParamPropertyValues['MIN'] = $param['MIN'];
                            $arParamPropertyValues['MAX'] = $param['MAX'];
                        }
                        if (is_array($param['DUMP'])) {
                            $n = 0;
                            foreach ($param['DUMP']['MIN'] as $i => $val) {
                                $arParamPropertyValues['DUMPING']["n".$n] = array("VALUE" => "[".$val.";".$param['DUMP']['MAX'][$i]."]:".$param['DUMP']['DISCOUNT'][$i]);
                                $n++;
                            }
                        }

                        if (isset($param['DUMP']['STRAIGHT']) && $param['DUMP']['STRAIGHT'] == 'Y') {
                            $arParamPropertyValues['DIRECT_DUMP'] = 'Y';
                        }

                        $arParamValues['PROPERTY_VALUES'] = $arParamPropertyValues;

                        $ID = $oElement->Add($arParamValues);
                    }

                    //сохранение стоимостей для одного склада
                    if(!empty($_REQUEST['warehouse'])){
                        $wh_array = explode('|',$_REQUEST['warehouse']);
                        if(sizeof($wh_array) == 2){
                            $key = $wh_array[0];
                            $store = $wh_array[1];

                            $centerList = client::getCentersByWH($_REQUEST['csort'], array($key));

                            if ($clientNds == 'N') {
                                $parityPrice = round($store * (1. + 0.01 * $arResult['NDS_VAL']), 0);
                            }
                            else {
                                $parityPrice = round($store, 0);
                            }

                            $arStoreValues = $arStorePropertyValues = array();

                            $arStoreValues['NAME'] = date('d.m.Y H:i:s');
                            $arStoreValues['IBLOCK_ID'] = rrsIblock::getIBlockId('client_request_cost');

                            $arStorePropertyValues['REQUEST'] = $arParams['ID'];
                            $arStorePropertyValues['CULTURE'] = $_REQUEST['csort'];
                            $arStorePropertyValues['WAREHOUSE'] = $key;
                            $arStorePropertyValues['CENTER'] = $centerList[$key];
                            $arStorePropertyValues['PRICE'] = $store;
                            $arStorePropertyValues['PARITY_PRICE'] = $parityPrice;

                            $arStoreValues['PROPERTY_VALUES'] = $arStorePropertyValues;

                            $ID = $oElement->Add($arStoreValues);
                        }
                    }

                    /*
                    //сохранение стоимостей
                    if (is_array($_REQUEST['warehouse'])) {
                        $centerList = client::getCentersByWH($_REQUEST['csort'], array_keys($_REQUEST['warehouse']));
                        $arResult['NDS_VAL'] = rrsIblock::getConst('nds');

                        foreach ($_REQUEST['warehouse'] as $key => $store) {
                            if ($clientNds == 'N') {
                                $parityPrice = round($store * (1. + 0.01 * $arResult['NDS_VAL']), 0);
                            }
                            else {
                                $parityPrice = round($store, 0);
                            }

                            $arStoreValues = $arStorePropertyValues = array();

                            $arStoreValues['NAME'] = date('d.m.Y H:i:s');
                            $arStoreValues['IBLOCK_ID'] = rrsIblock::getIBlockId('client_request_cost');

                            $arStorePropertyValues['REQUEST'] = $arParams['ID'];
                            $arStorePropertyValues['CULTURE'] = $_REQUEST['csort'];
                            $arStorePropertyValues['WAREHOUSE'] = $key;
                            $arStorePropertyValues['CENTER'] = $centerList[$key];
                            $arStorePropertyValues['PRICE'] = $store;
                            $arStorePropertyValues['PARITY_PRICE'] = $parityPrice;

                            $arStoreValues['PROPERTY_VALUES'] = $arStorePropertyValues;

                            $ID = $oElement->Add($arStoreValues);
                        }
                    }*/
                    if(!empty($arParams['ID'])){
                        client::saveCounterRiquestPrices($arParams['ID']);
                    }

//                    echo 1;exit;


                    //деактивация исходного запроса (если находимся в режиме "изменение")
                    /*if(isset($_GET['mode'])
                        && $_GET['mode'] == 'change'
                    ){*/
                    //деактивация теперь и при копировании
                    if((isset($_REQUEST['id']))&&($_REQUEST['id'])){
                        CIBlockElement::SetPropertyValuesEx(
                            $_REQUEST['id'],
                            rrsIblock::getIBlockId('client_request'),
                            array(
                                'ACTIVE' => rrsIblock::getPropListKey('client_request', 'ACTIVE', 'no')
                            )
                        );
                        logRequestDeactivating($_REQUEST['id']); //пишем лог о деактивации запроса

                        $filter = array(
                            'UF_REQUEST_ID' => $_REQUEST['id']
                        );
                        $arLeads = lead::getLeadList($filter);
                        if (is_array($arLeads) && sizeof($arLeads) > 0) {
                            lead::deleteLeads($arLeads);
                        }

                        $el = new CIBlockElement;
                        $res = $el->Update($_REQUEST['id'], array('NAME' => date('d.m.Y H:i:s')));

                        /**
                         * Удаляем встречные предложения
                         */
                        client::removeCountersByRequestID($_REQUEST['id']);
                    }
                    //}

                    if (isset($_REQUEST['back_url']) && $_REQUEST['back_url'] != '')
                        $uriString = $_REQUEST['back_url'];
                    else
                        $uriString = $arParams['LIST_URL'];

                    $uri = new Uri($uriString);
                    $uri->addParams(array("new_request"=>$arParams['ID'], "key"=>md5($arParams['ID'].$_SERVER['SERVER_NAME'])));
                    $redirect = $uri->getUri();

                    //запуск параллельного поиска подходящих для запроса предложений агропроизводителей
                    $file_path = $_SERVER['DOCUMENT_ROOT'];
                    $key = md5($arParams['ID']);
                    exec("php -f {$file_path}/include/search_suitable_offers.php new_request={$arParams['ID']} key={$key} > /dev/null &");

                    LocalRedirect($redirect);
                    exit();
                }
            }
        }else{
            $arResult['ERRORS'][] = 'Не выбран склад';
        }
    }

    $arResult['CULTURE_GROUP_LIST'] = rrsIblock::getElementList(rrsIblock::getIBlockId('cultures_groups'));
    $arResult['DELIVERY_LIST'] = rrsIblock::getElementList(rrsIblock::getIBlockId('delivery4client'));
    $arResult['DOCS_LIST'] = rrsIblock::getElementList(rrsIblock::getIBlockId('need_docs'));
    $arResult['PAYMENT_LIST'] = rrsIblock::getPropListKey('client_request', 'PAYMENT');
    $arResult['NDS_LIST'] = rrsIblock::getPropListKey('client_request', 'NDS');

    if ($arParams['USER_TYPE'] != 'AGENT') {
        $arResult['PROFILE'] = client::getProfile($clientId);
    }

    $arResult["MESSAGE"] = '';
    if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_REQUEST["strIMessage"]) && is_string($_REQUEST["strIMessage"]))
        $arResult["MESSAGE"] = htmlspecialcharsbx($_REQUEST["strIMessage"]);

    $this->includeComponentTemplate();
}