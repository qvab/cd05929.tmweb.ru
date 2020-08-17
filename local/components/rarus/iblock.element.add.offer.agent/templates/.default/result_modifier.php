<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();?>
<?
if (is_array($arResult["POST"]) || $arResult["ELEMENT"]["ID"] > 0) {
    $arResult["SAVE"] = "Y";
    $arResult["VALUES"] = array();

    if (is_array($arResult["POST"])) {
        $arResult["VALUES"]["CGROUP"] = $arResult["POST"]["cgroup"];
        $arResult["VALUES"]["CSORT"] = $arResult["POST"]["csort"];

        foreach ($arResult["POST"]["param"] as $key => $param) {
            $arResult["VALUES"]["PARAM"][$key]["LBASE"] = $param["LBASE"];
            $arResult["VALUES"]["PARAM"][$key]["BASE"] = $param["BASE"];
        }

        $arResult["VALUES"]["FIO"] = $arResult["POST"]["fio"];
        $arResult["VALUES"]["POSITION"] = $arResult["POST"]["position"];
        $arResult["VALUES"]["PHONE"] = $arResult["POST"]["phone"];
        $arResult["VALUES"]["EMAIL"] = $arResult["POST"]["email"];
        $arResult["VALUES"]["WAREHOUSE"] = $arResult["POST"]["warehouse"];
    }
    elseif ($arResult["ELEMENT"]["ID"] > 0) {
        $arResult["VALUES"]["CGROUP"] = $arResult["ELEMENT_PROPERTIES"]["GROUP"]["VALUE"];
        $arResult["VALUES"]["CSORT"] = $arResult["ELEMENT_PROPERTIES"]["CULTURE"]["VALUE"];

        foreach ($arResult["ELEMENT_PARAMS"] as $key => $param) {
            $arResult["VALUES"]["PARAM"][$key]["LBASE"] = $param["LBASE_ID"];
            $arResult["VALUES"]["PARAM"][$key]["BASE"] = $param["BASE"];
        }

        $arResult["VALUES"]["FIO"] = $arResult["ELEMENT_PROPERTIES"]["CNAME"]["VALUE"];
        $arResult["VALUES"]["POSITION"] = $arResult["ELEMENT_PROPERTIES"]["CPOSITION"]["VALUE"];
        $arResult["VALUES"]["PHONE"] = $arResult["ELEMENT_PROPERTIES"]["CPHONE"]["VALUE"];
        $arResult["VALUES"]["EMAIL"] = $arResult["ELEMENT_PROPERTIES"]["CEMAIL"]["VALUE"];
        $arResult["VALUES"]["WAREHOUSE"] = $arResult["ELEMENT_PROPERTIES"]["WAREHOUSE"]["VALUE"];
    }

    $arResult['CULTURE_LIST'] = culture::getListByGroupId($arResult["VALUES"]["CGROUP"]);
    $arResult['PARAMS_LIST'] = culture::getParamsListByCultureId($arResult["VALUES"]["CSORT"]);
}

if(isset($arResult['FARMERS_DATA'])
    && count($arResult['FARMERS_DATA']) > 0
){
    $arResult['LIMITS_DATA'] = agent::checkAvailableOfferLimit(array_keys($arResult['FARMERS_DATA']));
}
?>