<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if (intval($_POST['deal']) > 0 && isset($_POST['action'])) {
    CModule::IncludeModule('iblock');
    $arAction = $arResult['ACTIONS'][$_POST['action']];

    if (sizeof($arAction['delete_logs']) > 0) {
        $logs = log::getDealStatusLog($_POST['deal']);
    }
    if (sizeof($arAction['delete_docs']) > 0) {
        $res = CIBlockElement::GetList(
            array('DATE_CREATE' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('deals_docs'),
                'ACTIVE' => 'Y',
                'PROPERTY_DEAL' => $_POST['deal']
            ),
            false,
            false,
            array('ID', 'NAME', 'CODE')
        );
        while ($ob = $res->Fetch()) {
            $docs[$ob['CODE']][] = $ob;
        }
    }

    if ($arAction['deleve_vi'] == 1) {
        $res = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('deals_exe_docs'),
                'ACTIVE' => 'Y',
                'PROPERTY_DEAL' => $_POST['deal']
            ),
            false,
            false,
            array('ID')
        );
        while ($ob = $res->Fetch()) {
            $vi[] = $ob;
        }
    }

    //Удаление ВИ
    if (is_array($vi) && sizeof($vi) > 0) {
        foreach ($vi as $item) {
            CIBlockElement::Delete($item['ID']);
        }
    }

    //Удаление документов
    foreach ($docs as $key => $arItems) {
        if (in_array($key, $arAction['delete_docs'])) {
            foreach ($arItems as $item) {
                CIBlockElement::Delete($item['ID']);
            }
        }
    }

    //Удаление логов
    foreach ($logs as $item) {
        if (in_array($item['UF_STATUS_CODE'], $arAction['delete_logs'])) {
            log::_deleteEntity(4, $item['ID']);
        }
    }

    //Удалить ТК если статус search или new
    if (in_array($arAction['set_status'], array('new', 'search'))) {
        CIBlockElement::SetPropertyValuesEx($_POST['deal'], rrsIblock::getIBlockId('deals_deals'), array('TRANSPORT' => ''));
    }

    //Установление статуса
    deal::setStatus($_POST['deal'], $arAction['set_status']);

    LocalRedirect('/admin/deals/');
}
?>