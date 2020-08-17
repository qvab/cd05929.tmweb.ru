<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
$this->setFrameMode(true);
//$arRemoveParams = ['status', 'warehouse_id', 'culture_id', 'q', 'best_price', 'PAGEN_1', 'id'];
//$GLOBALS['el_count'] = 100;

//проверка соответствия выбранного фильтра и кук
$checkFilter = client::filterCounterRequestCheck((isset($arParams['AGENT_ID'])));
if($checkFilter['NEED_UPD']){
    //делаем переадресацию, если нужно
    LocalRedirect($checkFilter['URL_UPD']);
    exit;
}
?>
    <div class="tab_form client_counter_requests">

        <?if($arResult['SHOW_FORM']):?>
            <form method="GET" id="client_requests_filter" data-graphmode="month">

                <?
                $selected_region = 0;
                if(!empty($arResult['REGIONS_LIST'])):?>
                    <div class="row">
                        <div class="wrap-select">
                            <select <?if(count($arResult['REGIONS_LIST']) > 3){?>data-search="y"<?}?> name="region_id" placeholder="Выберите регион">
                                <option value="0" data-cnt="<?=$arResult['COUNTER_REGION_DATA_TOT'];?>">Все регионы</option>
                                <?
                                foreach($arResult['REGIONS_LIST'] as $cur_id => $cur_data){
                                    $sSelected = '';
                                    $cur_active = false;
                                    if(isset($_GET['region_id'])
                                        && $_GET['region_id'] == $cur_id
                                    ){
                                        $sSelected = 'selected="selected"';
                                        $selected_region = $cur_id;
                                    } ?>
                                    <option data-cnt="<?=$cur_data['COUNT'];?>" value="<?=$cur_id;?>" <?=$sSelected;?> ><?=$cur_data['NAME'];?></option><?
                                }?>
                            </select>
                        </div>
                    </div>
                <?endif;?>
                <?
                $selected_uid = 0;
                if(!empty($arResult['CLIENT_LIST'])):?>
                    <div class="row">
                        <div class="wrap-select">
                            <select <?if(count($arResult['CLIENT_LIST']) > 3){?>data-search="y"<?}?> name="client_id" placeholder="Выберите покупателя">
                                <option value="0" data-cnt="<?=$arResult['COUNTER_USER_DATA_TOT'];?>">Все покупатели</option>
                                <?
                                foreach($arResult['CLIENT_LIST'] as $cur_id => $arClient){
                                    $sSelected = '';
                                    $cur_active = false;
                                    if(isset($_GET['client_id'])
                                        && $_GET['client_id'] == $cur_id
                                    ){
                                        $sSelected = 'selected="selected"';
                                        $selected_uid = $cur_id;
                                    }

                                    $cur_cnt = 0;
                                    //определение количества ВП
                                    foreach($arResult['COUNTER_WH_DATA'] as $cur_data){
                                        if($cur_id == $cur_data['UID']
                                            &&
                                            ($selected_region == 0 || $selected_region > 0 && $cur_data['REGION'] == $selected_region)
                                        ){
                                            $cur_cnt += $cur_data['CNT'];
                                        }
                                    }
                                    if($arClient['NICK'] != ''){?>
                                        <option data-plimit="<?=$arClient['LIMIT'];?>" data-cnt="<?=$cur_cnt;?>" value="<?=$cur_id;?>" <?=$sSelected;?> ><?=$arClient['NICK'];?></option>
                                    <?}elseif($arClient['NAME'] == ''){?>
                                        <option data-plimit="<?=$arClient['LIMIT'];?>" data-cnt="<?=$cur_cnt;?>" value="<?=$cur_id;?>" <?=$sSelected;?> ><?=$arClient['EMAIL'];?></option>
                                    <?}else{?>
                                        <option data-plimit="<?=$arClient['LIMIT'];?>" data-cnt="<?=$cur_cnt;?>" value="<?=$cur_id;?>" <?=$sSelected;?> ><?=$arClient['NAME'];?> (<?=$arClient['EMAIL'];?>)</option>
                                    <?}
                                }?>
                            </select>
                        </div>
                    </div>
                    <div class="clear"></div>
                <?endif;?>

                <?
                $selected_culture = 0;
                if(!empty($arResult['CULTURE_LIST'])):?>
                    <div class="row<?=(isset($arParams['AGENT_ID']) ? ' second_line' : '')?>">

                        <?$sDataSearch = (count($arResult['CULTURE_LIST']) > 3) ? 'data-search="y"' : null;?>
                        <div class="wrap-select">
                            <select <?=$sDataSearch?> name="culture_id" placeholder="Выберите культуру">
                                <option value="0" data-cnt="<?=$arResult['COUNTER_CULTURE_DATA_TOT'];?>">Все культуры</option>
                                <?foreach ($arResult['CULTURE_LIST'] as $arCulture):
                                    $sSelected = '';
                                    if(isset($_GET['culture_id'])
                                        && $_GET['culture_id'] == $arCulture['ID']
                                    ){
                                        $sSelected = 'selected="selected"';
                                        $selected_culture = $arCulture['ID'];
                                    }
                                    //вывод количества ВП
                                    $cur_cnt = 0;
                                    //определение количества ВП
                                    foreach ($arResult['COUNTER_WH_DATA'] as $cur_data){
                                        if($arCulture['ID'] == $cur_data['CULTURE']
                                            &&
                                            ($selected_uid == 0 || $selected_uid > 0 && $cur_data['UID'] == $selected_uid)
                                            &&
                                            ($selected_region == 0 || $selected_region > 0 && $cur_data['REGION'] == $selected_region)
                                        ){
                                            $cur_cnt += $cur_data['CNT'];
                                        }
                                    }
                                    ?><option data-cnt="<?=$cur_cnt?>" <?=$sSelected?> value="<?=$arCulture['ID']?>"><?=$arCulture['NAME']?></option>
                                <?endforeach;?>
                            </select>
                        </div>
                    </div>
                <?endif;?>

                <?if(!empty($arResult['WAREHOUSE_LIST'])):?>
                    <div class="row<?=(isset($arParams['AGENT_ID']) ? ' second_line' : '')?>">

                        <?$sDataSearch = (count($arResult['WAREHOUSE_LIST']) > 3) ? 'data-search="y"' : null;?>
                        <div class="wrap-select">

                            <select <?=$sDataSearch?> name="warehouse_id" placeholder="Выберите склад">
                                <option value="0" data-cnt="<?=$arResult['COUNTER_WH_DATA_TOT'];?>">Все склады</option>
                                <?foreach ($arResult['WAREHOUSE_LIST'] as $arWarehouse):
                                    $sSelected = '';
                                    if(isset($_GET['warehouse_id'])
                                        && $_GET['warehouse_id'] == $arWarehouse['ID']
                                    ){
                                        $sSelected = 'selected="selected"';
                                    }
                                    $cur_cnt = 0;
                                    //определение количества ВП
                                    foreach ($arResult['COUNTER_WH_DATA'] as $cur_data){
                                        if($arWarehouse['ID'] == $cur_data['WH']
                                            &&
                                            ($selected_uid == 0 || $selected_uid > 0 && $cur_data['UID'] == $selected_uid)
                                            &&
                                            ($selected_culture == 0 || $selected_culture > 0 && $cur_data['CULTURE'] == $selected_culture)
                                            &&
                                            ($selected_region == 0 || $selected_region > 0 && $cur_data['REGION'] == $selected_region)
                                        ){
                                            $cur_cnt += $cur_data['CNT'];
                                        }
                                    }?>
                                    <option data-cnt="<?=$cur_cnt?>" <?=$sSelected?> value="<?=$arWarehouse['ID']?>"><?=$arWarehouse['NAME']?></option>
                                <?endforeach;?>
                            </select>
                        </div>
                    </div>
                <?endif;?>
                <div class="clear"></div>

                <div class="row submit-row">
                    <div class="wrap-btn">
                        <input class="submit-btn" value="Применить" type="submit">
                    </div>

                    <div class="show_hide_counter_graph_container">
                        <div class="show_hide_counter_graph" onclick="showClientGraph(0);" date-textshow="График цен по культуре на текущем складе" date-texthide="Скрыть график">График цен по культуре на текущем складе</div>
                    </div>
                    <div class="clear"></div>
                </div>
                
        </form>
        <div id="draw_object_block_old"><?//удалить _old чтобы работал фикс-скролл?>
            <div id="draw_object">
                <div class="graph_area_tab" data-viewmode="year">Год</div>
                <div class="graph_area_tab active" data-viewmode="month">Месяц</div>
                <div class="clear"></div>
                <div class="graph_area" data-viewmode="year"></div>
                <div class="graph_area active" data-viewmode="month"></div>
            </div>
        </div>
        <?endif;?>

        <?if(isset($arResult['COUNTER_WH_DATA'])
            && count($arResult['COUNTER_WH_DATA']) > 0
        ){
            ?><div class="filter_wh_count_data" style="display: none;"><?
            foreach($arResult['COUNTER_WH_DATA'] as $data_arr){
                ?><div data-region="<?=$data_arr['REGION'];?>" data-uid="<?=$data_arr['UID'];?>" data-wh="<?=$data_arr['WH'];?>" data-culture="<?=$data_arr['CULTURE'];?>" data-cnt="<?=$data_arr['CNT'];?>"></div><?
            }
            ?></div><?
        }
        ?>
    </div>

<?
//фильтрация и переадресация
$filterName = $arParams['FILTER_NAME'];
if ($_GET['id']) {
    /*$newsCount = $GLOBALS['el_count'];
    $curPage = $_GET['PAGEN_1'];
    if (!$curPage)
        $curPage = 1;*/
    $arFilter = array_merge($GLOBALS[$filterName], array('IBLOCK_ID' => $arParams['IBLOCK_ID'], 'ACTIVE' => 'Y'));
    /*$res = CIBlockElement::GetList(
        Array("PROPERTY_ACTIVE" => "ASC", "ACTIVE_TO" => "DESC"),
        $arFilter,
        false,
        array("nPageSize"=>0, "nElementID"=>$_GET['id']),
        array(
            'ID'
        )
    );
    if ($ob = $res->GetNext()) {
        $page = floor(($ob['RANK'] - 1)/$newsCount) + 1;
        if ($page != $curPage) {
            if ($page == 1) {
                LocalRedirect($APPLICATION->GetCurPageParam("", array('warehouse_id', 'culture_id', 'q', 'best_price', 'PAGEN_1')));
            }
            else {
                LocalRedirect($APPLICATION->GetCurPageParam("PAGEN_1=".$page, array('warehouse_id', 'culture_id', 'q', 'best_price')));
            }
        }
    }
    elseif (!$_GET['status']) {
        LocalRedirect($APPLICATION->GetCurPageParam("status=no", array('warehouse_id', 'culture_id', 'q', 'best_price', 'PAGEN_1')));
    }*/
}
?>