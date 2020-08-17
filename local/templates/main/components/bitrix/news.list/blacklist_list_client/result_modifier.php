<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
$REG_CENTER_LIST = array();
//получаем ригионы региональных центров
$res = CIBlockElement::GetList(
    array('ID' => 'ASC'),
    array(
        'IBLOCK_ID' => rrsIblock::getIBlockId('regions_centers'),
        'ACTIVE' => 'Y',
        'ID' => $center_id
    ),
    false,
    false,
    array(
        'ID',
        'PROPERTY_REGION',
    )
);
while ($ob = $res->Fetch()) {
    $REG_CENTER_LIST[$ob['ID']] = $ob['PROPERTY_REGION_VALUE'];
}

$filterName = $arParams['FILTER_NAME'];

$CULTURE_LIST = rrsIblock::getElementList(rrsIblock::getIBlockId('cultures'));
$REGION_LIST =  rrsIblock::getElementList(rrsIblock::getIBlockId('regions'));
$deals_ids = array();

$users = array();
$TMPL_ITEMS = array();
if(sizeof($arResult['ITEMS'])>0){
    foreach ($arResult['ITEMS'] as $cur_pos => $arItem) {
        $tmp = array(
            'ID' => $arItem['ID'],
            'DATE_CREATE' => $arItem['DATE_CREATE'],
            'DEAL_REGION_ID' => '',
            'DEAL_REGION_NAME' => '',
            'CULTURE_ID' => '',
            'CULTURE_NAME' => ''
        );
        if(isset($arItem['PROPERTIES']['USER']['VALUE'])){
            $tmp['USER'] = $arItem['PROPERTIES']['USER']['VALUE'];
        }
        if(isset($arItem['PROPERTIES']['OPPONENT']['VALUE'])){
            $tmp['OPPONENT'] = $arItem['PROPERTIES']['OPPONENT']['VALUE'];
            $users[] = $tmp['OPPONENT'];
        }
        $tmp['DEAL_ID'] = $arItem['PROPERTIES']['DEAL']['VALUE'];
        $tmp['DEAL_LINK'] = '/client/pair/?id='.$arItem['PROPERTIES']['DEAL']['VALUE'];
        $deal_data = deal::getInfo4BL($arItem['PROPERTIES']['DEAL']['VALUE']);
        $tmp['DEAL_NAME'] = $deal_data['NAME'];
        if(isset($REG_CENTER_LIST[$deal_data['CENTER']])){
            if($REGION_LIST[$REG_CENTER_LIST[$deal_data['CENTER']]]){
                $tmp['DEAL_REGION_ID'] = $REGION_LIST[$REG_CENTER_LIST[$deal_data['CENTER']]]['ID'];
                $tmp['DEAL_REGION_NAME'] = $REGION_LIST[$REG_CENTER_LIST[$deal_data['CENTER']]]['NAME'];
            }
        }
        if(isset($CULTURE_LIST[$deal_data['CULTURE_ID']])){
            $tmp['CULTURE_ID'] = $deal_data['CULTURE_ID'];
            $tmp['CULTURE_NAME'] = $CULTURE_LIST[$deal_data['CULTURE_ID']]['NAME'];
        }

        $filter = true;

        if(isset($GLOBALS[$filterName]['REGION_ID'])){
            if(!empty($GLOBALS[$filterName]['REGION_ID'])){
                if($GLOBALS[$filterName]['REGION_ID']!=$tmp['DEAL_REGION_ID']){
                    $filter = false;
                }
            }
        }
        if(isset($GLOBALS[$filterName]['CULTURE_ID'])){
            if(!empty($GLOBALS[$filterName]['CULTURE_ID'])){
                if($GLOBALS[$filterName]['CULTURE_ID']!=$tmp['CULTURE_ID']){
                    $filter = false;
                }
            }
        }
        if($filter === true){
            $TMPL_ITEMS[] = $tmp;
            //сохраняем id предложения и его позицию во временном массиве $TMPL_ITEMS
            $deals_ids[$arItem['PROPERTIES']['DEAL']['VALUE']] = count($TMPL_ITEMS) - 1;
        }
    }

    //получаем агентские предложения из используемого черного списка
    if(count($deals_ids) > 0){
        CModule::IncludeModule('iblock');
        $res = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('deals_deals'),
                'ID' => array_keys($deals_ids)
            ),
            false,
            false,
            array('ID', 'PROPERTY_COFFER_TYPE')
        );
        while($data = $res->Fetch()){
            if($data['PROPERTY_COFFER_TYPE_VALUE'] == 'p'
                && isset($TMPL_ITEMS[$deals_ids[$data['ID']]])
            ){
                $TMPL_ITEMS[$deals_ids[$data['ID']]]['NO_LIMIT_CHECK'] = true;
            }
        }
    }

    $USER_LIST = farmer::getUserCompanyNames($users);
    for($i=0,$c=sizeof($TMPL_ITEMS);$i<$c;$i++){
        if(isset($USER_LIST[$TMPL_ITEMS[$i]['OPPONENT']])){
            $TMPL_ITEMS[$i]['OPPONENT_NAME'] = $USER_LIST[$TMPL_ITEMS[$i]['OPPONENT']];
        }
    }

    $arResult['ITEMS'] = $TMPL_ITEMS;

    unset($CULTURE_LIST,$REGION_LIST,$REG_CENTER_LIST,$TMPL_ITEMS,$users,$deals_ids);
}

?>