<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if(isset($arResult['ITEMS'])){
    ?><div id="items_count" style="display: none;" data-count="<?=count($arResult['ITEMS'])?>"></div><?
}

//Если был переход по ссылке организатора и предложение было удалено, то выводим все предложения
//получаем данные для построения графиков (фильтрация по пользователю, культуре, региональному центру)
if(isset($_GET['warehouse_id'])
    && is_numeric($_GET['warehouse_id'])
    && $_GET['warehouse_id'] > 0
    && isset($_GET['culture_id'])
    && is_numeric($_GET['culture_id'])
    && $_GET['culture_id'] > 0
) {
    $arGraphFilter = array('UF_USER_ID' => 0);
    if (isset($arParams['CLIENT_ID'])) {
        $arGraphFilter = array(
            'UF_USER_ID' => $arParams['CLIENT_ID']
        );
    }elseif(isset($arParams['AGENT_ID'])){
        //для агента фильтруем по покупателям
        $agent_obj = new agent();
        $users_arr = $agent_obj->getClients($arParams['AGENT_ID']);
        if(count($users_arr) > 0) {
            $users_arr = array_flip($users_arr);
            if(isset($_GET['client_id']) && !empty($_GET['client_id'])){
                if(isset($users_arr[$_GET['client_id']])) {
                    $arGraphFilter['UF_USER_ID'] = $_GET['client_id'];
                }
            }else {
                //получение покупателя по складу (т.к. можно строить график только для конкретного покупателя - иначе будут мешанина на графике "Мои цены")
                $uid = client::getClientByWH($_GET['warehouse_id']);
                if($uid > 0){
                    $arGraphFilter['UF_USER_ID'] = $uid;
                }
            }
        }
    }

    //определяем тип налогообложения текущего пользователя
    $user_nds_type = false;
    $nds_val = rrsIblock::getConst('nds');
    if(isset($arGraphFilter['UF_USER_ID'])
        && is_numeric($arGraphFilter['UF_USER_ID'])
    ){
        CModule::IncludeModule('iblock');
        $nds_list = rrsIblock::getElementList(rrsIblock::getIBlockId('nds_list'));
        $check_nds_id = 0;
        foreach($nds_list as $cur_data){
            if($cur_data['CODE'] == 'Y'){
                $check_nds_id = $cur_data['ID'];
                break;
            }
        }
        if($check_nds_id > 0) {
            $res = CIBlockElement::GetList(
                array('ID' => 'ASC'),
                array(
                    'IBLOCK_ID' => rrsIblock::getIBlockId('client_profile'),
                    'PROPERTY_USER' => $arGraphFilter['UF_USER_ID'],
                    'PROPERTY_NDS' => $check_nds_id,
                ),
                false,
                array('nTopCount' => 1),
                array('ID', 'IBLOCK_ID', 'PROPERTY_USER', 'PROPERTY_NDS')
            );
            if($res->SelectedRowsCount() > 0){
                $user_nds_type = true;
            }
        }
    }

    if (isset($_GET['culture_id'])
        && is_numeric($_GET['culture_id'])
    ) {
        $arGraphFilter['UF_CULTURE'] = $_GET['culture_id'];
    }
    if (isset($arResult['UF_CENTER_ID'])
        && is_numeric($arResult['UF_CENTER_ID'])
    ) {
        $arGraphFilter['UF_CENTER'] = $arResult['UF_CENTER_ID'];
    } elseif (isset($arGraphFilter['UF_CULTURE'])
        && isset($_GET['warehouse_id'])
        && is_numeric($_GET['warehouse_id'])
    ) {
        $temp_val = client::getCentersByWH($arGraphFilter['UF_CULTURE'], $_GET['warehouse_id']);
        if (count($temp_val) == 1) {
            $arGraphFilter['UF_CENTER'] = reset($temp_val);
        }
    }
    if(isset($_GET['warehouse_id'])
        && is_numeric($_GET['warehouse_id'])){
        $arGraphFilter['UF_WAREHOUSE'] = $_GET['warehouse_id'];
    }

    if (!is_numeric($arGraphFilter['UF_CENTER'])) {
        $arGraphFilter['UF_CENTER'] = 0;
    }

    //получаем и выводим данные для графика
    $arResult['COUNTER_GRAPHS_DATA'] = client::getCounterRequestsGraphsDataAll($arGraphFilter, $user_nds_type);
    client::showCounterRequestsGraphsDataAll($arResult['COUNTER_GRAPHS_DATA'], $nds_val, $user_nds_type, $arGraphFilter['UF_USER_ID']);

}else{
    ?><div id="no_graph_without_filter"></div><?
}

//выделение предложений на странице и отображение графика
if(!isset($arParams['AGENT_ID'])
    && isset($_GET['show_graph'])
    && $_GET['show_graph'] == 'y'
){
    ?>
    <script type="text/javascript">
        var show_graph_flag = true;
        <?if(isset($_GET['checked_offers'])
            && trim($_GET['checked_offers']) != ''
        ){?>
            var checked_offers_list = [<?=htmlspecialcharsbx($_GET['checked_offers'])?>];
        <?}?>
        <?if(isset($_GET['graph_type'])
            && trim($_GET['graph_type']) != ''
        ){?>
            var show_graph_type = '<?=htmlspecialcharsbx($_GET['graph_type']);?>';
        <?}?>
        <?if(isset($_GET['nds_type'])
            && trim($_GET['nds_type']) != ''
        ){?>
            var show_graph_nds = '<?=htmlspecialcharsbx($_GET['nds_type']);?>';
        <?}?>
    </script>
    <?
}