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
            $arResult["VALUES"]["PARAM"][$key]["MIN"] = $param["MIN"];
            $arResult["VALUES"]["PARAM"][$key]["MAX"] = $param["MAX"];
            $arResult["VALUES"]["PARAM"][$key]["DIRECT_DUMP"] = ($param["PROPERTY_DIRECT_DUMP_VALUE"] == 'Y' ? 'Y' : '');

            if (sizeof($param["DUMP"]) > 0) {
                foreach ($param["DUMP"]["DISCOUNT"] as $i => $item) {
                    $arResult["VALUES"]["PARAM"][$key]["DUMP"][] = array(
                        "MIN" => $param["DUMP"]["MIN"][$i],
                        "MAX" => $param["DUMP"]["MAX"][$i],
                        "DISCOUNT" => $item
                    );
                }
            }
        }

        $arResult["VALUES"]["VOLUME"] = $arResult["POST"]["volume"];
        $arResult["VALUES"]["DELIVERY"] = $arResult["POST"]["delivery"];
        $arResult["VALUES"]["REMOTENESS"] = $arResult["POST"]["remoteness"];
        foreach ($arResult["POST"]["docs"] as $key => $val) {
            $arResult["VALUES"]["DOCS"][] = $key;
        }
        $arResult["VALUES"]["PAYMENT"] = $arResult["POST"]["payment"];
        $arResult["VALUES"]["PERCENT"] = $arResult["POST"]["percent"];
        $arResult["VALUES"]["DELAY"] = $arResult["POST"]["delay"];
        foreach ($arResult["POST"]["nds"] as $key => $val) {
            $arResult["VALUES"]["NDS"][] = $key;
        }
        //$arResult["VALUES"]["URGENCY"] = $arResult["POST"]["urgency"];
    }
    elseif ($arResult["ELEMENT"]["ID"] > 0) {
        $arResult["VALUES"]["CGROUP"] = $arResult["ELEMENT_PROPERTIES"]["GROUP"]["VALUE"];
        $arResult["VALUES"]["CSORT"] = $arResult["ELEMENT_PROPERTIES"]["CULTURE"]["VALUE"];

        foreach ($arResult["ELEMENT_PARAMS"] as $key => $param) {
            $arResult["VALUES"]["PARAM"][$key]["LBASE"] = $param["LBASE_ID"];
            $arResult["VALUES"]["PARAM"][$key]["BASE"] = $param["BASE"];
            $arResult["VALUES"]["PARAM"][$key]["MIN"] = $param["MIN"];
            $arResult["VALUES"]["PARAM"][$key]["MAX"] = $param["MAX"];
            $arResult["VALUES"]["PARAM"][$key]["DIRECT_DUMP"] = ($param["DIRECT_DUMP"] == 'Y' ? 'Y' : '');

            if (sizeof($param["DUMPING"]) > 0) {
                foreach ($param["DUMPING"] as $item) {
                    $arResult["VALUES"]["PARAM"][$key]["DUMP"][] = array(
                        "MIN" => $item['MN'],
                        "MAX" => $item['MX'],
                        "DISCOUNT" => $item['DUMP']
                    );
                }
            }
        }

        $arResult["VALUES"]["VOLUME"] = $arResult["ELEMENT_PROPERTIES"]["VOLUME"]["VALUE"];
        $arResult["VALUES"]["DELIVERY"] = $arResult["ELEMENT_PROPERTIES"]["DELIVERY"]["VALUE"];
        $arResult["VALUES"]["REMOTENESS"] = $arResult["ELEMENT_PROPERTIES"]["REMOTENESS"]["VALUE"];
        foreach ($arResult["ELEMENT_PROPERTIES"]["DOCS"] as $item) {
            $arResult["VALUES"]["DOCS"][] = $item['VALUE'];
        }
        $arResult["VALUES"]["PAYMENT"] = $arResult["ELEMENT_PROPERTIES"]["PAYMENT"]["VALUE"];
        $arResult["VALUES"]["PERCENT"] = $arResult["ELEMENT_PROPERTIES"]["PERCENT"]["VALUE"];
        $arResult["VALUES"]["DELAY"] = $arResult["ELEMENT_PROPERTIES"]["DELAY"]["VALUE"];
        foreach ($arResult["ELEMENT_PROPERTIES"]["NDS"] as $item) {
            $arResult["VALUES"]["NDS"][] = $item['VALUE'];
        }
        //$arResult["VALUES"]["URGENCY"] = $arResult["ELEMENT_PROPERTIES"]["URGENCY"]["VALUE"];
    }

    $arResult['CULTURE_LIST'] = culture::getListByGroupId($arResult["VALUES"]["CGROUP"]);
    $arResult['PARAMS_LIST'] = culture::getParamsListByCultureId($arResult["VALUES"]["CSORT"]);
}
?>