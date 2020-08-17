<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

CModule::IncludeModule('iblock');
CModule::IncludeModule('highload');

$rec = deal::getRecommendedPrice(389581);
p($rec);

/* $arrCentersToRegions = array();
$obRes = CIBlockElement::GetList(
    array('ID' => 'ASC'),
    array(
        'IBLOCK_ID' => rrsIblock::getIBlockId('regions_centers'),
        'ACTIVE' => 'Y',
    ),
    false,
    false,
    array('ID', 'PROPERTY_REGION')
);
while ($arrData = $obRes->Fetch()) {
    $arrCentersToRegions[$arrData['ID']] = $arrData['PROPERTY_REGION_VALUE'];
}

if(count($arrCentersToRegions) > 0){
    $my_c = 0;
    $obRes = CIBlockElement::GetList(
        array('ID' => 'ASC'),
        array(
            'IBLOCK_ID' => rrsIblock::getIBlockId('parity_price'),
            'ACTIVE' => 'Y',
        ),
        false,
        false,
        array('ID', 'IBLOCK_ID', 'PROPERTY_REGION', 'PROPERTY_CENTER')
    );
    while ($arrData = $obRes->Fetch()) {
        if(
			0 &&
            !empty($arrData['PROPERTY_CENTER_VALUE'])
            && isset($arrCentersToRegions[$arrData['PROPERTY_CENTER_VALUE']])
            && empty($arrData['PROPERTY_REGION_VALUE'])
        ){
			CIBlockElement::SetPropertyValuesEx($arrData['ID'], $arrData['IBLOCK_ID'], array('REGION' => $arrCentersToRegions[$arrData['PROPERTY_CENTER_VALUE']]));
            $my_c++;
        }
    }

    echo $my_c;
} */

//обновляем устаревшие записи
//farmer::updateFarmersWHRoutes();

//генерируем новые записи
//farmer::generateNewFarmersWHRoutes();