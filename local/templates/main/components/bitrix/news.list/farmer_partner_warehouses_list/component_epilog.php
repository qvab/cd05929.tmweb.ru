<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();?>
<?
if ($_REQUEST['warehouse'] > 0) {
    $get = null;
    CModule::IncludeModule('iblock');
    if ($_REQUEST['deactivate']) {
        $prop = array(
            'ACTIVE' => rrsIblock::getPropListKey('farmer_warehouse', 'ACTIVE', 'no')
        );
    }
    elseif ($_REQUEST['activate']) {
        $prop = array(
            'ACTIVE' => rrsIblock::getPropListKey('farmer_warehouse', 'ACTIVE', 'yes')
        );
    }
    else {
        $prop = array();
        $n = 0;
        foreach ($_REQUEST['transport'] as $key => $val) {
            $prop['TRANSPORT']["n".$n] = array("VALUE" => $key);
            $n++;
        }
    }

    CIBlockElement::SetPropertyValuesEx(
        $_REQUEST['warehouse'],
        rrsIblock::getIBlockId('farmer_warehouse'),
        $prop
    );
    $el = new CIBlockElement;
    $res = $el->Update($_REQUEST['warehouse'], array('TIMESTAMP_X' => date('d.m.Y H:i:s')));

    //деактивация всех товаров на складе
    $IB_WH_ID = rrsIblock::getIBlockId('farmer_warehouse');
    if($IB_WH_ID){
        //проверяем активность склада, если деактивация прошла успешно, то деактивируем товары
        $res = $el->GetList(array('ID' => 'DESC'), array(
                'IBLOCK_TYPE' => 'farmer',
                'IBLOCK_ID' => $IB_WH_ID,
                'ID'=>$_REQUEST['warehouse'],
                'PROPERTY_ACTIVE' => rrsIblock::getPropListKey('farmer_warehouse', 'ACTIVE', 'no')
            ),
            false,
            false,
            array('ID','PROPERTY_ACTIVE_VALUE'));
        if ($res->SelectedRowsCount() > 0) {
            //если склад неактивный то деактивируем товары
            $d_offer_count = farmer::setWHOfferDeactivation($_REQUEST['warehouse']);
            if($d_offer_count>0){
                $get = '?q='.$d_offer_count;
            }
        }
    }

    LocalRedirect($APPLICATION->GetCurPageParam($get, ['q',]));
}