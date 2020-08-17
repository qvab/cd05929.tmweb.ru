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
?>
<div class="add_limit_line ended"><div class="result_message"></div>Исчерпан лимит создания запросов (лимит запросов: <span class="val"></span>), <a href="javascript: void(0);">подайте заявку</a> на пополнение</div>
<div class="add_limit_line available"><div class="result_message"></div>Текущий лимит запросов: <span class="val"></span> (доступно запросов: <span class="remains"></span>).<br/>Вы можете <a href="javascript: void(0);">подать заявку</a> на пополнение</div>
<?
if (sizeof($arResult["ITEMS"]) > 0) {
    /*if (is_numeric($arResult['NAV_RESULT']->NavRecordCount)
        && $arResult['NAV_RESULT']->NavRecordCount > 10
        || count($arResult["ITEMS"]) > 10
    ) {*/
    ?>
            <div class="additional_href_data">
                <a href="/client/request/new/">+ Создать запрос</a>
            </div>
    <?
    //}
    ?>

    <div class="list_page_rows requests">
        <?
        $check_prolongated = rrsIblock::getPropListKey('client_request', 'IS_PROLONGATED', 'yes');
        foreach ($arResult["ITEMS"] as $arItem) {
            $bNDS = ($arItem['PROPERTIES']['USER_NDS']['VALUE_XML_ID'] == 'yes');
            $active_to_tmstmp = strtotime($arItem['DATE_ACTIVE_TO']);
        ?>
            <a name="<?=$arItem['ID']?>"></a>
            <div class="line_area <?=$arItem["PROPERTIES"]['ACTIVE']['VALUE_XML_ID']?><? if ($_GET['id'] == $arItem['ID']) {?> active<? } ?>" data-elid="<?=$arItem['ID'];?>">
                <div class="line_inner">
                    <div class="name"><?=$arResult['CULTURE_LIST'][$arItem['PROPERTIES']['CULTURE']['VALUE']]['NAME']?>
                        (<?=($arResult['DELIVERY_LIST'][$arItem['PROPERTIES']['DELIVERY']['VALUE']]['CODE'] == 'Y' ? 'CPT' : 'FCA');?>)
                    </div>
                    <div class="tons"><?=number_format($arItem["PROPERTIES"]['REMAINS']['VALUE'], 0, ',', ' ')?> т.</div>
                    <div class="arw_list arw_icon_close"></div>
					<?foreach($arResult['REQUEST_COST'][$arItem['ID']] as $cur_w_id => $cur_data){
						if(isset($arResult['WAREHOUSES_LIST'][$cur_w_id])){?>
							<div class="clear l no_border"></div>
							<div class="warhouse_name"><?=$arResult['WAREHOUSES_LIST'][$cur_w_id]['NAME'];?></div>
							<div class="price_val">
								 <?=number_format($cur_data['DDP_PRICE_CLIENT'], 0, ',', ' ');?> руб/т
							</div>
						<?}
					}?>
                    <div class="clear l"></div>
                </div>
                <form action="" method="post" class="line_additional" <? if ($_GET['id'] == $arItem['ID']) {?>style="display: block;"<? } ?>>
                    <input type="hidden" name="request" value="<?=$arItem['ID']?>">
                    <?
                    $f_calc = false;
                    if(isset($arItem['PROPERTIES']['F_CALC']['VALUE'])) {
                        if (!empty($arItem['PROPERTIES']['F_CALC']['VALUE'])) {
                            $f_calc = true;
                        } else {
//                            if(($arItem['PROPERTIES']['F_NUM']['VALUE'] == '')&&($arItem['PROPERTIES']['F_CALC']['VALUE'] == '')){
//                                $f_calc = true;
//                            }
                        }
                    }
                    if (
                        intval($arItem['PROPERTIES']['F_NUM']['VALUE']) > 0
                        || $f_calc === true
                    ) {
                    ?>
                        <div class="prop_area adress_val">
                            <div class="adress">Количество найденных поставщиков:</div>
                            <div class="val_adress">
                                <?
                                $sSuitableFarmer = intval($arItem['PROPERTIES']['F_NUM']['VALUE']) . ' ' . morph($arItem['PROPERTIES']['F_NUM']['VALUE'], 'поставщик', 'поставщика', 'поставщиков');
                                $sSuitableFarmer .= ', для ' . intval($arItem['PROPERTIES']['FARMER_BEST_PRICE_CNT']['VALUE']) . ' - лучшая цена';
                                ?>
                                <?=$sSuitableFarmer?>
                            </div>
                        </div>
                    <?
                    }
                    ?>

                    <div class="prop_area adress_val">
                        <div class="adress">Таблица сбросов/прибавок:</div>
                        <?
                        $params = $arResult['REQUEST_PARAMS'][$arItem['ID']];
                        foreach ($params as $param) {
                            if(!isset($arResult['PARAMS_INFO'][$arItem['PROPERTIES']['CULTURE']['VALUE']][$param['QUALITY_ID']]['QUALITY_NAME']))
                                continue;
                        ?>
                            <div class="val_adress">
                                <?=$arResult['PARAMS_INFO'][$arItem['PROPERTIES']['CULTURE']['VALUE']][$param['QUALITY_ID']]['QUALITY_NAME']?>: <span class="cur_val">
                                <? if ($param['LBASE_ID'] > 0) { ?>
                                    <b><?=$arResult['LBASE_INFO'][$arItem['PROPERTIES']['CULTURE']['VALUE']][$param['QUALITY_ID']][$param['LBASE_ID']]?></b>
                                <? } else { ?>
                                    <b><?=$param['BASE']?><?=($arResult['UNIT_INFO'][$param['QUALITY_ID']] != '')?' '.$arResult['UNIT_INFO'][$param['QUALITY_ID']]:''?></b>,
                                    MIN: <b><?=$param['MIN']?></b>, MAX: <b><?=$param['MAX']?></b>
                                    <?
                                    if (sizeof($param['DUMPING']) > 0) {
                                    ?>
                                        <div class="req-info-dump-table">
                                            <div class="req-info-dump-title">Таблица сбросов:</div>
                                            <div class="req-info-dump-val">
                                                <?
                                                foreach ($param['DUMPING'] as $dump) {
                                                ?>
                                                    <div>
                                                        <span><?=$dump['MN']?></span>
                                                        <span>-</span>
                                                        <span><?=$dump['MX']?></span>
                                                        <span>:</span>
                                                        <span><?=$dump['DUMP']?></span>
                                                    </div>
                                                <?
                                                }
                                                ?>
                                            </div>
                                        </div>
                                        <div class="clear"></div>
                                    <?
                                    }
                                    ?>
                                <? } ?>
                                </span>
                            </div>
                        <?
                        }
                        ?>
                        <div class="clear"></div>
                    </div>

                    <div class="prop_area adress_val">
                        <div class="adress">Количество:</div>
                        <div class="val_adress">
                            <?=$arItem['PROPERTIES']['VOLUME']['VALUE']?> т
                        </div>
                    </div>

                    <div class="prop_area adress_val">
                        <div class="adress">Способ доставки:</div>
                        <div class="val_adress">
                            <?=$arResult['DELIVERY_LIST'][$arItem['PROPERTIES']['DELIVERY']['VALUE']]['NAME']?>
                        </div>
                        <?
                        if ($arItem['PROPERTIES']['REMOTENESS']['VALUE'] > 0) {
                        ?>
                            <div class="val_adress">
                                Удаленность: <?=$arItem['PROPERTIES']['REMOTENESS']['VALUE']?> км
                            </div>
                        <?
                        }
                        ?>
                    </div>

                    <?
                    /*if (is_array($arItem['PROPERTIES']['DOCS']['VALUE']) && sizeof($arItem['PROPERTIES']['DOCS']['VALUE']) > 0) {
                    ?>
                        <div class="prop_area adress_val">
                            <div class="adress">Потребность в документах:</div>
                            <?
                            foreach ($arItem['DISPLAY_PROPERTIES']['DOCS']['LINK_ELEMENT_VALUE'] as $item) {
                            ?>
                                <div class="val_adress">
                                    <?=$item['NAME']?>
                                </div>
                            <?
                            }
                            ?>
                        </div>
                    <?
                    }*/
                    ?>
                    <?/*
                    <div class="prop_area adress_val">
                        <div class="adress">Тип оплаты:</div>
                        <?
                        if ($arItem['PROPERTIES']['PAYMENT']['VALUE_XML_ID'] == 'pre') {
                            if ($arItem['PROPERTIES']['PERCENT']['VALUE'] > 0) {
                            ?>
                                <div class="val_adress">
                                    Предоплата
                                </div>
                            <?
                            }
                            else {
                            ?>
                                <div class="val_adress">
                                    <?=$arItem['PROPERTIES']['PAYMENT']['VALUE']?>
                                </div>
                            <?
                            }
                        }
                        elseif ($arItem['PROPERTIES']['PAYMENT']['VALUE_XML_ID'] == 'post') {
                        ?>
                            <div class="val_adress">
                                <?=$arItem['PROPERTIES']['PAYMENT']['VALUE']?>
                            </div>
                            <?
                        }
                        ?>
                    </div>
                    */?>
                    <?/*<div class="prop_area adress_val">
                        <div class="adress">Тип закупки:</div>
                        <div class="val_adress">
                            <?=$arResult['MARGIN_LIST'][$arItem['PROPERTIES']['URGENCY']['VALUE']]['NAME']?>
                        </div>
                    </div>*/?>

                    <div class="prop_area adress_val prices_val">
                        <div class="adress">Базисная цена (<?= $bNDS ? 'с' : 'без'?> НДС):</div>
                        <?
                        foreach ($arResult['REQUEST_COST'][$arItem['ID']] as $cost) {
                        ?>
                            <div class="val_adress val_1 basis_price_table">
                                <span class="decs_separators"><?=$cost['DDP_PRICE_CLIENT']?></span> руб/т
                                <span class="line"><?=$arResult['WAREHOUSES_LIST'][$cost['WH_ID']]['NAME']?></span>
                                <span class="line sec"><?=$arResult['WAREHOUSES_LIST'][$cost['WH_ID']]['ADDRESS']?></span>
                            </div>
                        <?
                        }
                        ?>
                    </div>
                    <div class="prop_area">
                        <div class="val"></div>
                        <?
                        /*if ((!$arParams['VERIFIED_PARTNER'])&&($arParams['LINKED_PARTNER'])) {
                        ?>
                            <div class="no_deal_rights">Для создания запроса необходимо чтобы организатор подтвердил вашу привязку</div>
                        <?
                        }
                        elseif(!$arParams['LINKED_PARTNER']) {
                        ?>
                            <div class="no_deal_rights">Для создания запроса необходимо <a href="/client/link_to_partner/">привязаться к организатору</a></div>
                        <?
                        }*/
                        ?>
                        <?/*<a href="<?=$arItem['DETAIL_PAGE_URL']?>" class="submit-btn<? if (!$arParams['LINKED_PARTNER']) { ?> inactive<? } ?>">Копировать запрос</a>*/?>
                        <?
                        //если меньше 6 часов до конца действия запроса
                        //или меньше 6 часов после окончания действия запроса
                        /*$tmstmp_diff = floor(($active_to_tmstmp - time())/3600);
                        if(requestCanBePrologated($tmstmp_diff, $arItem['PROPERTIES']['ACTIVE']['VALUE_XML_ID'] == 'yes', $arItem['PROPERTIES']['IS_PROLONGATED']['VALUE_XML_ID'] == 'yes') != 'n'){?>
                            <div class="prolongate_area">
                                <a href="javascript: void(0);" class="submit-btn req_prolongation">Продлить запрос</a>
                                <div class="clear with_line"></div>
                            </div>
                        <?}*/?>

                        <a href="<?=$arItem['DETAIL_PAGE_URL']?>" class="submit-btn">Изменить запрос</a>
                        <div class="clear"></div>
                    </div>

                    <!--<div class="prop_area">
                        <div class="val"></div>

                        <a href="<?=$arItem['DETAIL_PAGE_URL']?>&mode=change" class="submit-btn">Изменить запрос</a>
                        <div class="clear"></div>
                    </div>-->

                    <div class="prop_area additional_submits">
                        <?
                        if ($arItem['PROPERTIES']['ACTIVE']['VALUE_XML_ID'] == 'yes') {
                        ?>
                            <input type="submit" class="reject_but" name="deactivate" value="Деактивировать запрос" />
                        <?
                        }
                        ?>
                        <div class="hide_but">Свернуть</div>
                    </div>

                    <?
                    if (sizeof($arResult['DEALS'][$arItem['ID']]) > 0) {
                    ?>
                        <div class="prop_area adress_val prices_val">
                            <div class="adress">Пары по данному запросу:</div>
                            <?
                            foreach ($arResult['DEALS'][$arItem['ID']] as $deal) {
                            ?>
                                <div class="val_adress val_1 basis_price_table">
                                    <div class="deal_link">
                                        <a href="/client/deals/<?=$deal['ID']?>/">#<?=$deal['ID']?> от <?=date('d.m.Y', strtotime($deal['DATE_CREATE']))?></a>
                                    </div>
                                    <div class="deal_volume"><?=$deal['VOLUME']?> т.</div>
                                    <div class="deal_status"><?=$deal['STATUS']?></div>
                                </div>
                            <?
                            }
                            ?>
                        </div>
                    <?
                    }
                    ?>
                </form>
            </div>
        <?
        }
        ?>
    </div>
    <?if($arParams["DISPLAY_BOTTOM_PAGER"]):?>
        <?=$arResult["NAV_STRING"]?>
    <?endif;?>
<?
}
else {
?>
    <div class="list_page_rows requests no-item">
        Ни одного запроса не найдено
    </div>
<?
}

?>
<a href="/client/request/new/" class="add_blue_button">Создать запрос<div class="but_addit"><div class="ico">+</div></div></a>

<?
/*if ($arParams['DEMO'] == 'Y') {
?>
    <a href="/client/request/new/" class="add_blue_button">Создать запрос<div class="but_addit"><div class="ico">+</div></div></a>
<?
}
else {
    if ((!$arParams['VERIFIED_PARTNER'])&&($arParams['LINKED_PARTNER'])) {
    ?>
        <div class="no_deal_rights">Для создания запроса необходимо чтобы организатор подтвердил вашу привязку</div>
    <?
    }
    elseif (!$arParams['LINKED_PARTNER']) {
    ?>
        <div class="no_deal_rights">Для создания запроса необходимо <a href="/client/link_to_partner/">привязаться к организатору</a></div>
    <?
    }
    ?>
    <a href="/client/request/new/" class="add_blue_button<? if (!$arParams['VERIFIED_PARTNER']) { ?> inactive<? } ?>">Создать запрос<div class="but_addit"><div class="ico">+</div></div></a>
<?
}*/
?>
