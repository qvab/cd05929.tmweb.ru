<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if (intval($_POST['deal']) > 0) {
    CModule::IncludeModule('iblock');
    $el = new CIBlockElement;

    $logs = log::getDealStatusLog($_POST['deal']);
    if (in_array('complete', array_keys($logs))) {
        $stage = rrsIblock::getPropListKey('deals_deals', 'STAGE', 'complete');
    }
    elseif (in_array('execution', array_keys($logs))) {
        $stage = rrsIblock::getPropListKey('deals_deals', 'STAGE', 'execution');
    }
    elseif (in_array('order_transport', array_keys($logs))) {
        $stage = rrsIblock::getPropListKey('deals_deals', 'STAGE', 'dkp');
    }
    elseif (in_array('order_deal', array_keys($logs))) {
        $res = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('deals_deals'),
                'ID' => $_POST['deal']
            ),
            false,
            false,
            array('ID', 'PROPERTY_DELIVERY')
        );
        if ($ob = $res->Fetch()) {
            $dId = $ob['PROPERTY_DELIVERY_ENUM_ID'];
            $dCode = rrsIblock::getPropListId('deals_deals', 'DELIVERY', $dId);

            if ($dCode == 'c') {
                $stage = rrsIblock::getPropListKey('deals_deals', 'STAGE', 'search');
            }
            else {
                $stage = rrsIblock::getPropListKey('deals_deals', 'STAGE', 'dkp');
            }
        }
    }
    elseif (in_array('new', array_keys($logs))) {
        $stage = rrsIblock::getPropListKey('deals_deals', 'STAGE', 'new');
    }

    if(intval($stage) > 0) {
        //Установление статуса
        $prop = array(
            'STAGE' => $stage,
            'DATE_STAGE' => date('d.m.Y H:i:s'),
            'STATUS' => rrsIblock::getPropListKey('deals_deals', 'STATUS', 'open')
        );

        CIBlockElement::SetPropertyValuesEx(
            $_POST['deal'],
            rrsIblock::getIBlockId('deals_deals'),
            $prop
        );

        $res = $el->Update($_POST['deal'], array('NAME' => date('d.m.Y H:i:s')));

        //Удаление логов
        if (in_array('reject', array_keys($logs))) {
            log::_deleteEntity(4, $logs['reject']['ID']);
        }

        LocalRedirect('/admin/deals/');
    }
}
?>