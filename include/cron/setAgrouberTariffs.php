<?//приведение тарифов, рассчитанных по ММ к тарифам АХ
//1 день

/*if(empty($_SERVER['SHELL']))
	die();*/

set_time_limit(12000);
//боевой
$_SERVER["DOCUMENT_ROOT"] = "/home/bitrix/www";
//песочница
//$_SERVER["DOCUMENT_ROOT"] = "/www/unorganized_projects/agrohelper/sandboxes/aledem/www";

define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS",true);
flush();
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
ob_end_flush();

CModule::IncludeModule('iblock');
$el = new CIBlockElement;

$ib = rrsIblock::getIBlockId('tariff');

//актуальные тарифы АХ
$arAgrohelperTariffs = model::getAgrohelperTariffs();

//берем все региональные центры
$arCenters = array();
$res = CIBlockElement::GetList(
    array('ID' => 'ASC'),
    array(
        'IBLOCK_ID' => rrsIblock::getIBlockId('regions_centers'),
        'ACTIVE' => 'Y'
    ),
    false,
    false,
    array('ID', 'NAME')
);
while ($ob = $res->Fetch()) {
    $arCenters[$ob['ID']] = $ob;
}

//берем все действующие тарифы, рассчитанные по ММ
$arTariffs = array();
$res = CIBlockElement::GetList(
    array('ID' => 'ASC'),
    array(
        'IBLOCK_ID' => $ib,
    ),
    false,
    false,
    array('ID', 'PROPERTY_CENTER', 'PROPERTY_TARIF_ID')
);
while ($ob = $res->Fetch()) {
    if (!$ob['PROPERTY_CENTER_VALUE'] || !$ob['PROPERTY_TARIF_ID_VALUE']) {
        $el->Delete($ob['ID']);
    }
    else {
        $arTariffs[$ob['PROPERTY_CENTER_VALUE']][$ob['PROPERTY_TARIF_ID_VALUE']] = $ob;
    }
}

//удалим тарифы не существующих РЦ
foreach ($arTariffs as $centerId => $arCenterTariffs) {
    if (!in_array($centerId, array_keys($arCenters))) {
        foreach ($arCenterTariffs as $tariff) {
            $el->Delete($tariff['ID']);
        }
    }
}

//удаляем тарифы РЦ, которых не существует в справочнике тарифов АХ
foreach ($arTariffs as $centerId => $arCenterTariffs) {
    foreach ($arCenterTariffs as $tariffId => $tariff) {
        if (!in_array($tariffId, array_keys($arAgrohelperTariffs))) {
            $el->Delete($tariff['ID']);
            unset($arTariffs[$centerId][$tariffId]);
        }
    }
}

//добавляем/изменяем тарифы РЦ, которые есть в справочнике тарифов АХ
foreach ($arCenters as $centerId => $arCenter) {
    $arCenterTariffs = $arTariffs[$centerId];
    foreach ($arAgrohelperTariffs as $tariffId => $agrohelperTariff) {
        if (in_array($tariffId, array_keys($arCenterTariffs))) {
            //редактируем тариф
            CIBlockElement::SetPropertyValuesEx(
                $arCenterTariffs[$tariffId]['ID'],
                $ib,
                array(
                    'KM_FROM' => $agrohelperTariff['FROM'],
                    'KM_TO' => $agrohelperTariff['TO'],
                    'DAYS' => $agrohelperTariff['DAYS'],
                    'TARIF_AU' => $agrohelperTariff['TARIF'],
                    'TARIF' => $agrohelperTariff['TARIF'],

                )
            );
        }
        else {
            //добавляем тариф
            $arLoadProductArray = Array(
                'IBLOCK_ID' => $ib,
                'NAME' => 'тариф',
                'ACTIVE' => 'Y',
                'PROPERTY_VALUES'=> array(
                    'CENTER' => $centerId,
                    'KM_FROM' => $agrohelperTariff['FROM'],
                    'KM_TO' => $agrohelperTariff['TO'],
                    'DAYS' => $agrohelperTariff['DAYS'],
                    'TARIF_AU' => $agrohelperTariff['TARIF'],
                    'TARIF' => $agrohelperTariff['TARIF'],
                    'TARIF_ID' => $tariffId
                ),
            );

            $el->Add($arLoadProductArray);
        }
    }
}

global $CACHE_MANAGER;
$CACHE_MANAGER->ClearByTag("iblock_id_".$ib);


//удаляем тарифы пользователей
$userTariffIb = rrsIblock::getIBlockId('client_tariffs');
$res = CIBlockElement::GetList(
    array('ID' => 'ASC'),
    array(
        'IBLOCK_ID' => $userTariffIb,
        'PROPERTY_TARIF_ID' => false
    ),
    false,
    false,
    array('ID')
);
while ($ob = $res->Fetch()) {
    $el->Delete($ob['ID']);
}

$CACHE_MANAGER->ClearByTag("iblock_id_".$userTariffIb);
?>