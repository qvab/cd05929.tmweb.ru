<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
$statusList = rrsIblock::getPropListKey('farmer_offer', 'ACTIVE');
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
    $WAREHOUSE_LIST = array();
    //получим склады привязанных поставщиков
    CModule::IncludeModule('iblock');
    $el_obj                     = new CIBlockElement;
    $agentObj                   = new agent();

    $arResult['FARMERS_LIST']   = $agentObj->getFarmersForSelect($arParams['AGENT_ID']);
    $res = $el_obj->GetList(
        array('ID' => 'ASC'),
        array(
            'IBLOCK_ID'         => rrsIblock::getIBlockId('farmer_offer'),
            'ACTIVE'            => 'Y',
            'PROPERTY_FARMER'   => (count($arResult['FARMERS_LIST']) > 0 ? array_keys($arResult['FARMERS_LIST']) : 0)
        ),
        false,
        false,
        array(
            'ID',
            'PROPERTY_ACTIVE',
            'PROPERTY_WAREHOUSE'
        )
    );
    while ($ob = $res->Fetch()) {
        $WAREHOUSE_LIST[$ob['PROPERTY_WAREHOUSE_VALUE']] = 1;
        $arResult['Q']['all']++;
        if(isset($status[$ob['PROPERTY_ACTIVE_ENUM_ID']])){
            $arResult['Q'][$status[$ob['PROPERTY_ACTIVE_ENUM_ID']]]++;
        }
    }
    $REGIONS_WH = array();
    //получаем регионы из складов
    $res = CIBlockElement::GetList(
        array('ID' => 'ASC'),
        array(
            'IBLOCK_ID' => rrsIblock::getIBlockId('farmer_warehouse'),
            'ACTIVE' => 'Y',
            'ID' => array_keys($WAREHOUSE_LIST),
        ),
        false,
        false,
        array('ID','PROPERTY_REGION')
    );
    $regions = array();
    while ($ob = $res->Fetch()) {
        $regions[$ob['PROPERTY_REGION_VALUE']] = 1;
        $REGIONS_WH[$ob['PROPERTY_REGION_VALUE']][] = $ob['ID'];
    }
    $arResult['REGIONS_WH'] = $REGIONS_WH;
    $arResult['REGIONS_LIST'] = array();
    if((sizeof($regions))&&(is_array($regions))){
        //получаем регионы
        $arRegions = array();
        $res = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('regions'),
                'ACTIVE' => 'Y',
                'ID' => array_keys($regions)
            ),
            false,
            false,
            array('ID', 'NAME')
        );
        while ($ob = $res->Fetch()) {
            $arRegions[$ob['ID']] = $ob;
        }
        $arResult['REGIONS_LIST'] = $arRegions;
    }

    //получим данные для фильтра по культуре
    $arResult['CULTURE_LIST'] = array();
    $cultures = array();
    $res = $el_obj->GetList(
        array('NAME' => 'ASC'),
        array(
            'IBLOCK_ID' => rrsIblock::getIBlockId('farmer_offer'),
            'ACTIVE' => 'Y',
            'PROPERTY_FARMER' => array_keys($arResult['FARMERS_LIST'])
        ),
        array('PROPERTY_CULTURE')
    );
    while($data = $res->Fetch()) {
        $cultures[$data['PROPERTY_CULTURE_VALUE']] = true;
    }
    if(count($cultures) > 0)
    {
        $res = $el_obj->GetList(
            array('NAME' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('cultures'),
                'ACTIVE' => 'Y',
                'ID' => array_keys($cultures)
            ),
            false,
            false,
            array('ID', 'NAME')
        );
        while($data = $res->Fetch())
        {
            $arResult['CULTURE_LIST'][] = $data;
        }
    }
    //получаем типы НДС поставщиков
    $res = $el_obj->GetList(
        ['ID' => 'ASC',],
        [
            'ACTIVE'        => 'Y',
            'IBLOCK_ID'     => getIBlockID('farmer', 'farmer_profile'),
            'PROPERTY_USER' => array_keys($arResult['FARMERS_LIST']),
        ],
        false,
        false,
        [
            'PROPERTY_NDS',
        ]
    );
    $arResult['NDS_LIST'] = array();
    $nds_list = rrsIblock::getElementList(rrsIblock::getIBlockId('nds_list'));
    while ($arRow = $res->Fetch()) {
        if(isset($nds_list[$arRow['PROPERTY_NDS_VALUE']])){
            $tmp = $nds_list[$arRow['PROPERTY_NDS_VALUE']];
            if($tmp['CODE']=='N') $tmp['VALUE']=2; else $tmp['VALUE']=1;
            $arResult['NDS_LIST'][$arRow['PROPERTY_NDS_VALUE']] = $tmp;
        }
    }
}

/**
 * Обработка
 */
if (!isset($_REQUEST['status']) || (!in_array($_REQUEST['status'], array('yes', 'no', 'all')))) {
    $_REQUEST['status'] = 'yes';
}

?>