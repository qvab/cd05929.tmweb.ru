<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
$statusList = rrsIblock::getPropListKey('client_warehouse', 'ACTIVE');
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
    CModule::IncludeModule('iblock');
    $el_obj                     = new CIBlockElement;
    $agentObj                   = new agent();

    $arResult['CLIENTS_LIST']   = $agentObj->getClientsForSelect($arParams['AGENT_ID']);
    $res = $el_obj->GetList(
        array('ID' => 'ASC'),
        array(
            'IBLOCK_ID'         => rrsIblock::getIBlockId('client_warehouse'),
            'ACTIVE'            => 'Y',
            'PROPERTY_CLIENT'   => (count($arResult['CLIENTS_LIST']) > 0 ? array_keys($arResult['CLIENTS_LIST']) : 0)
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
    }
}
?>