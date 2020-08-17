<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?

$agentObj = new agent();
$arResult['CLIENT_LIST'] = $agentObj->getClientsForSelect($arParams['AGENT_ID']);

$statusList = rrsIblock::getPropListKey('client_request', 'ACTIVE');
foreach ($statusList as $item) {
    $arResult['Q'][$item['XML_ID']] = 0;
    $status[$item['ID']] = $item['XML_ID'];
}
$arResult['Q']['all'] = 0;

$res = CIBlockElement::GetList(
    array('ID' => 'ASC'),
    array(
        'IBLOCK_ID'         => rrsIblock::getIBlockId('client_request'),
        'ACTIVE'            => 'Y',
        'PROPERTY_CLIENT'   => (count($arResult['CLIENT_LIST']) > 0 ? array_keys($arResult['CLIENT_LIST']) : 0)
    ),
    false,
    false,
    array(
        'ID',
        'PROPERTY_ACTIVE',
        'PROPERTY_CULTURE',
    )
);

$arCultureId = [];
while ($ob = $res->Fetch()) {
    $arResult['Q']['all']++;
    $arResult['Q'][$status[$ob['PROPERTY_ACTIVE_ENUM_ID']]]++;
    $arCultureId[$ob['PROPERTY_CULTURE_VALUE']] = $ob['PROPERTY_CULTURE_VALUE'];
}




// Список культур
$arResult['CULTURE_LIST'] = [];
if(!empty($arCultureId)) {
    
    $re = CIBlockElement::GetList(
        ['NAME' => 'ASC'],
        [
            'IBLOCK_ID' => getIBlockID('directories', 'cultures'),
            'ID'        => array_values($arCultureId),
        ],
        false,
        false,
        array('ID', 'NAME',)
    );

    while($arRow = $re->Fetch()) {
        $arResult['CULTURE_LIST'][$arRow['ID']] = [
            'ID'    => $arRow['ID'],
            'NAME'  => $arRow['NAME'],
        ];
    }
}