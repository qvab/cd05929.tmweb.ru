<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
$statusList = rrsIblock::getPropListKey('farmer_warehouse', 'ACTIVE');
$status = array();
foreach ($statusList as $item) {
    $arResult['Q'][$item['XML_ID']] = 0;
    $status[$item['ID']] = $item['XML_ID'];
}
$arResult['Q']['all'] = 0;

if(!isset($arParams['AGENT_ID'])
    || !is_numeric($arParams['AGENT_ID'])
)
{
    $arResult['ERROR'] = '<div class="error_text">Не указан агент</div>';
}
else
{
    //получим склады привязанных поставщиков
    $el_obj                     = new CIBlockElement;
    $agentObj                   = new agent();

    $arResult['FARMERS_LIST']   = $agentObj->getFarmersForSelect($arParams['AGENT_ID']);

    /*$res = $el_obj->GetList(
        array('ID' => 'ASC'),
        array(
            'IBLOCK_ID'         => rrsIblock::getIBlockId('farmer_warehouse'),
            'ACTIVE'            => 'Y',
            'PROPERTY_FARMER'   => (count($arResult['FARMERS_IDS']) > 0 ? array_keys($arResult['FARMERS_IDS']) : 0)
        ),
        false,
        false,
        array(
            'ID',
            'PROPERTY_ACTIVE',
        )
    );
    while ($ob = $res->Fetch()) {
        $arResult['Q']['all']++;
        if(isset($status[$ob['PROPERTY_ACTIVE_ENUM_ID']])){
            $arResult['Q'][$status[$ob['PROPERTY_ACTIVE_ENUM_ID']]]++;
        }
    }*/
    $arResult['REGIONS_LIST'] = agent::getAgentRegionsByLeads($arParams['AGENT_ID']);

    //получение данных для вывода фильтра
    $arResult['CULTURE_LIST'] = rrsIblock::getElementList(rrsIblock::getIBlockId('cultures'), 'NAME');

    //получение пар запрос-товар
    $arFilter = array(
        'UF_FARMER_ID' => array_keys($arResult['FARMERS_LIST'])
    );

    //получаем ID культур с учетом того что культуры берем их предложений
    $cultures_ids = farmer::getFermerOffersCultures(array_keys($arResult['FARMERS_LIST']),true);
    $arLeads = lead::getLeadList($arFilter);

    $whs_ids = array();
    foreach ($arLeads as $arItem) {
        //$cultures_ids[$arItem['UF_CULTURE_ID']] = true;
        $whs_ids[$arItem['UF_FARMER_WH_ID']] = true;
    }

    foreach ($arResult['CULTURE_LIST'] as $key => $arItem) {
        if (!isset($cultures_ids[$key])) {
            unset($arResult['CULTURE_LIST'][$key]);
        }
    }
}
?>