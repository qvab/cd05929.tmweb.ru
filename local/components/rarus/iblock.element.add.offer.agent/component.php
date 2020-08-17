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

use Bitrix\Main\Application,
    Bitrix\Main\Web\Uri;

if (!CModule::IncludeModule("iblock")) {
	ShowError(GetMessage("CC_BIEAF_IBLOCK_MODULE_NOT_INSTALLED"));
	return;
}

$arElement = false;
$arParams["ID"] = intval($_REQUEST["id"]);

$arParams["MAX_FILE_SIZE"] = intval($arParams["MAX_FILE_SIZE"]);

$arParams["USER_MESSAGE_ADD"] = trim($arParams["USER_MESSAGE_ADD"]);
if(strlen($arParams["USER_MESSAGE_ADD"]) <= 0)
	$arParams["USER_MESSAGE_ADD"] = GetMessage("IBLOCK_USER_MESSAGE_ADD_DEFAULT");

$arParams["USER_MESSAGE_EDIT"] = trim($arParams["USER_MESSAGE_EDIT"]);
if(strlen($arParams["USER_MESSAGE_EDIT"]) <= 0)
	$arParams["USER_MESSAGE_EDIT"] = GetMessage("IBLOCK_USER_MESSAGE_EDIT_DEFAULT");


if(!is_array($arParams["GROUPS"]))
	$arParams["GROUPS"] = array();

$arGroups = $USER->GetUserGroupArray();
$agentOgj = new agent();

$bAllowAccess = true;
/*//check whether current user can have access to add/edit elements
$bAllowAccess = count(array_intersect($arGroups, $arParams["GROUPS"])) > 0;*/

if (isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER'] != '') {
    $uriString = $_SERVER['HTTP_REFERER'];
    $uri = new Uri($uriString);
    $arResult['BACK_URL'] = $uri->getPathQuery();
}
else {
    $arResult['BACK_URL'] = $arParams['LIST_URL'];
}

$arResult["ERRORS"] = array();
$arResult["SELECTED_FARMER"] = '';
$arResult["SELECTED_WAREHOUSE"] = '';


$_GET['farmer_id'] = intval($_GET['farmer_id']);
if(!empty($_GET['farmer_id'])) {
    $arResult["SELECTED_FARMER"] = $_GET['farmer_id'];
}

if ($bAllowAccess) {
    //user has access to add new offer
    if ($arParams["ID"] > 0) {
        $arFilter = array(
            "IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
            "IBLOCK_ID" => $arParams["IBLOCK_ID"],
            "ID" => $arParams["ID"]
        );
        $rsIBlockElements = CIBlockElement::GetList(array("SORT" => "DESC"), $arFilter, false, array('nTopCount' => 1), array('ID', 'PROPERTY_FARMER', 'PROPERTY_WAREHOUSE'));
        if ($arElement = $rsIBlockElements->Fetch()) {
            //offer exists
            $bAllowAccess = true;
            if(isset($arElement['PROPERTY_FARMER_VALUE'])
                && is_numeric($arElement['PROPERTY_FARMER_VALUE'])
                && $arElement['PROPERTY_FARMER_VALUE'] > 0
            ){
                $arResult["SELECTED_FARMER"] = $arElement['PROPERTY_FARMER_VALUE'];
            }
            if(isset($arElement['PROPERTY_WAREHOUSE_VALUE'])
                && is_numeric($arElement['PROPERTY_WAREHOUSE_VALUE'])
                && $arElement['PROPERTY_WAREHOUSE_VALUE'] > 0
            ){
                $arResult["SELECTED_WAREHOUSE"] = $arElement['PROPERTY_WAREHOUSE_VALUE'];
            }
        }
        else {
            //no offer with request id
            ShowError(GetMessage("IBLOCK_ADD_ELEMENT_NOT_FOUND"));
            $bAllowAccess = false;
        }
    }
}
else {
    //no access
    ShowError(GetMessage("IBLOCK_ADD_ACCESS_DENIED"));
}

if ($bAllowAccess) {

    $arResult['FARMERS_DATA'] = $agentOgj->getFarmersForSelect($USER->GetID());
    if(count($arResult['FARMERS_DATA']) == 0)
    {
        LocalRedirect('/partner/offer/');
        exit;
    }

    if ($arParams["ID"] > 0) {
        //get offer information
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

        //get offer params
        $arResult["ELEMENT_PARAMS"] = current(farmer::getParamsList(array($arElement['ID'])));
    }

    $farmerId = $USER->GetID();

    if (!empty($_REQUEST["save"])) {

        $farmerId = $_REQUEST['farmer_filter'];

        $arResult["POST"] = $_REQUEST;

        $oElement = new CIBlockElement();
        $arUpdateValues = $arUpdatePropertyValues = array();

        $arUpdateValues["IBLOCK_ID"] = $arParams["IBLOCK_ID"];
        $arUpdateValues["ACTIVE"] = "Y";
        $arUpdateValues["MODIFIED_BY"] = $USER->GetID();
        $arUpdateValues["NAME"] = date("d.m.Y H:i:s");

        $arUpdatePropertyValues['FARMER'] = $farmerId;

        $farmerProfile = farmer::getProfile($farmerId);
        if ($farmerProfile['PROPERTY_NDS_CODE'] == 'Y') {
            $arUpdatePropertyValues['USER_NDS'] = rrsIblock::getPropListKey('farmer_offer', 'USER_NDS', 'yes');;
        }
        else/*if ($farmerProfile['PROPERTY_NDS_CODE'] == 'N')*/ {
            $arUpdatePropertyValues['USER_NDS'] = rrsIblock::getPropListKey('farmer_offer', 'USER_NDS', 'no');;
        }

        $arUpdatePropertyValues['GROUP'] = $_REQUEST["cgroup"];
        $arUpdatePropertyValues['CULTURE'] = $_REQUEST["csort"];
        $arUpdatePropertyValues['WAREHOUSE'] = $_REQUEST["warehouse"];
        $arUpdatePropertyValues['ACTIVE'] = rrsIblock::getPropListKey('farmer_offer', 'ACTIVE', 'yes');
        $arUpdatePropertyValues['STATUS_AVAILABLE'] = rrsIblock::getPropListKey('farmer_offer', 'STATUS_AVAILABLE', 'yes');

        $arUpdateValues["PROPERTY_VALUES"] = $arUpdatePropertyValues;

        if (!$arParams["ID"] = $oElement->Add($arUpdateValues)) {
            $arResult["ERRORS"][] = $oElement->LAST_ERROR;
        }

        if (empty($arResult["ERRORS"])) {
            //save offer params
            foreach ($_REQUEST["param"] as $key => $param) {
                $arParamValues = $arParamPropertyValues = array();

                $arParamValues["NAME"] = date("d.m.Y H:i:s");
                $arParamValues["IBLOCK_ID"] = 22;

                $arParamPropertyValues['OFFER'] = $arParams["ID"];
                $arParamPropertyValues['CULTURE'] = $_REQUEST["csort"];
                $arParamPropertyValues['QUALITY'] = $key;

                if ($param["LBASE"] > 0) {
                    $arParamPropertyValues['LBASE'] = $param["LBASE"];
                }
                else {
                    $arParamPropertyValues['BASE'] = $param["BASE"];
                }

                $arParamValues["PROPERTY_VALUES"] = $arParamPropertyValues;

                $ID = $oElement->Add($arParamValues);
            }

            //поиск подходящих для товар запросов покупателей
            deal::searchSuitableRequests($arParams['ID']);

            if (isset($_REQUEST['back_url']) && $_REQUEST['back_url'] != '')
                $uriString = $_REQUEST['back_url'];
            else
                $uriString = $arParams['LIST_URL'];

            //копирование данных для графика "Спрос"
            if(
                isset($_REQUEST["id"])
                && filter_var($_REQUEST["id"], FILTER_VALIDATE_INT)
            ){
                CModule::IncludeModule("highload");
                farmer::copyGraphSprosDataForOffer($_REQUEST["id"], $arParams["ID"]);
            }

            //переадресация
            $uri = new Uri($uriString);
            $uri->addParams(array("status"=>"yes"));
            $redirect = $uri->getUri();
            LocalRedirect($redirect);
            //LocalRedirect($arParams['LIST_URL']);
            exit();
        }
    }

    $arResult['CULTURE_GROUP_LIST'] = rrsIblock::getElementList(rrsIblock::getIBlockId('cultures_groups'));
    $arResult['WAREHOUSE_LIST'] = $agentOgj->getWarehouseList($USER->GetID());

    $arResult["MESSAGE"] = '';
    if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_REQUEST["strIMessage"]) && is_string($_REQUEST["strIMessage"]))
        $arResult["MESSAGE"] = htmlspecialcharsbx($_REQUEST["strIMessage"]);

    $this->includeComponentTemplate();
}