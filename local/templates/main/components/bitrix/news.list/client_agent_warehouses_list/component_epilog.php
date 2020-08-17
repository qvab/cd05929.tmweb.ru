<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();?>
<?
if ($_REQUEST['warehouse'] > 0) {
    CModule::IncludeModule('iblock');

    //проверка прав агента на активацию/деактивацию склада покупателя
    $has_right = false;

    $agentObj = new agent();
    //получение ID покупателя по ID склада
    $client_id = $agentObj->getClientByWarehouse($_REQUEST['warehouse']);
    if($client_id > 0){
        global $USER;
        //проверка прав агента на действие от лица покупателя
        $rights_data = $agentObj->getClientsForSelect($USER->GetID(), $client_id, false, true, true);

        if(isset($rights_data[$client_id]['LINK_DOC'])
            && $rights_data[$client_id]['LINK_DOC'] == 'Y'
        ){
            $has_right = true;
        }
    }

    if($has_right){
        if ($_REQUEST['deactivate']) {
            $prop = array(
                'ACTIVE' => rrsIblock::getPropListKey('client_warehouse', 'ACTIVE', 'no')
            );
        }
        elseif ($_REQUEST['activate']) {
            $prop = array(
                'ACTIVE' => rrsIblock::getPropListKey('client_warehouse', 'ACTIVE', 'yes')
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
            rrsIblock::getIBlockId('client_warehouse'),
            $prop
        );
        $el = new CIBlockElement;
        $res = $el->Update($_REQUEST['warehouse'], array('TIMESTAMP_X' => date('d.m.Y H:i:s')));

        //деактивация всех запросов на складе
        $IB_WH_ID = rrsIblock::getIBlockId('client_warehouse');
        if($IB_WH_ID){
            //проверяем активность склада, если деактивация прошла успешно, то деактивируем запросы
            $res = $el->GetList(array('ID' => 'DESC'), array(
                    'IBLOCK_TYPE' => 'client',
                    'IBLOCK_ID' => $IB_WH_ID,
                    'ID'=>$_REQUEST['warehouse'],
                    'PROPERTY_ACTIVE' => rrsIblock::getPropListKey('client_warehouse', 'ACTIVE', 'no')
                ),
                false,
                false,
                array('ID','PROPERTY_ACTIVE_VALUE'));
            if ($res->SelectedRowsCount() > 0) {
                //если склад неактивный то деактивируем запросы (также удаляем пары и встречные запросы)
                $d_request_count = client::setWHRequestDeactivation($_REQUEST['warehouse'], $client_id);
//                if($d_request_count>0){
//                    $get = '?q='.$d_request_count;
//                }
            }
        }
    }

    $sGetParams = null;
    if(isset($_REQUEST['client_id'][0])
        && is_numeric($_REQUEST['client_id'][0])
        && $_REQUEST['client_id'][0] > 0
    ){
        $sGetParams .= 'client_id[]=' . $_REQUEST['client_id'][0];
    }

    LocalRedirect($APPLICATION->GetCurPageParam($sGetParams));
    exit;
}