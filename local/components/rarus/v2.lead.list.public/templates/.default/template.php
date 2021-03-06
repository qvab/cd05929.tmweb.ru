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
<?
$assetObj = \Bitrix\Main\Page\Asset::getInstance();
$assetObj->addJs($this->GetFolder() . '/js/script.js');
?>
<?
if (is_array($arResult["ITEMS"]) && sizeof($arResult["ITEMS"]) > 0) {
?>
<div class="list_page_rows pairs_rows_list farmer_requests_list low_margin">
    <?
    foreach ($arResult["ITEMS"] as $arItem) {
        $arOffer = $arItem['OFFER'];
        $arRequest = $arItem['REQUEST'];
        $diff_date = secondTimesFormat($arRequest['DATE_DIFF'],false);
        $arCost = $arRequest['BEST_PRICE'];
        $price = number_format($arCost['ACC_PRICE_CSM'], 0, ',', ' ');
        $fca_dap = ($arRequest['NEED_DELIVERY'] == 'Y')?'CPT':'FCA';
        ?>
        <div class="line_area<? if ($_GET['o'] == $arOffer['ID'] && $_GET['r'] == $arRequest['ID']) { ?> active<? } ?>">
            <div class="line_inner">
                <div class="name"><?=$arRequest['CULTURE_NAME']?> <span>(<?=$fca_dap?>)</span></div>
                <div class="tons">
                    <?
                    if ($arItem['REQUEST']['NEED_DELIVERY'] == 'Y') {
                        echo '<span class="val decs_separators">' . $arCost['ROUTE']  . '</span> км';
                    }
                    ?>
                </div>
                <div class="price"><span class="val decs_separators"><?=$price?></span> руб/т</div>
                <div class="date" data-active_to-second="<?=strtotime($arRequest['DATE_ACTIVE_TO'])?>">
                    <?=$diff_date;?>
                </div>
                <div class="arw_list arw_icon_close"></div>
                <div class="clear.l"></div>
                <div class="wh_name"><?=$arOffer['WH_NAME']?></div>
                <div class="clear l"></div>
            </div>

            <form action="" method="post" class="line_additional" <? if ($_GET['o'] == $arOffer['ID'] && $_GET['r'] == $arRequest['ID']) { ?> style="display: block;"<? } ?>>
                <input type="hidden" name="offer" value="<?=$arOffer['ID']?>">
                <input type="hidden" name="request" value="<?=$arRequest['ID']?>">
                <input type="hidden" name="warehouse" value="<?=$arCost['WH_ID']?>">

                <div class="prop_area refinement_text">Запрос на товар <a target="_blank" href="/profile/offers/?uid=<?=$arParams['FARMER_ID'];?>&id=<?=$arOffer['ID']?>#<?=$arOffer['ID']?>">#<?=$arOffer['ID']?> от <?=date("d.m.Y", strtotime($arOffer['DATE_CREATE']))?></a></div>

                <div class="prop_area prices_val">
                    <span class="area_1">
                        <div class="name_1">Прогноз цены c места:</div><div class="val_1"><span class="decs_separators"><?=$price;?></span> руб/т</div>
                    </span>

                    <span class="area_2">
                        <div class="name_2">Базисная цена договора:</div><div class="val_2"><span class="decs_separators"><?=number_format($arCost['BASE_PRICE'], 0, ',', ' ')?></span> руб/т</div>
                    </span>
                </div>

                <div class="prop_area adress_val one_line">
                    <div class="adress">Объем:</div>
                    <div class="val_adress"><?=number_format($arRequest['REMAINS'], 0, '.', ' ')?> т.</div>
                    <div class="clear"></div>
                </div>

                <?
                if ($arRequest['NEED_DELIVERY'] == 'N') {
                ?>
                    <div class="prop_area adress_val">
                        <div class="adress">FCA</div>
                        <div class="val_adress" style="padding-left: 0;">Отгрузка с места</div>
                        <div class="clear"></div>
                    </div>
                    <div class="prop_area"><b>Доставка не требуется</b></div>
                    <input type="hidden" value="a" name="delivery" />
                <?
                }
                else {
                    if (sizeof($arResult['REQUEST_WAREHOUSES_LIST'][$arCost['WH_ID']]['TRANSPORT']) > 0) {
                    ?>
                        <div class="prop_area adress_val">
                            <div class="adress">Тип транспорта:</div>
                            <?
                            foreach ($arResult['REQUEST_WAREHOUSES_LIST'][$arCost['WH_ID']]['TRANSPORT'] as $val) {
                            ?>
                                <div class="val_adress"><?=$arResult['TRANSPORT_LIST'][$val]['NAME']?></div>
                            <?
                            }
                            ?>
                            <div class="clear"></div>
                        </div>
                    <?
                    }
                    ?>
                    <div class="prop_area adress_val">
                        <div class="adress">CPT</div>
                        <div class="val_adress" style="padding-left: 0;">С доставкой покупателю</div>
                        <div class="clear"></div>
                    </div>
                    <div class="prop_area adress_val one_line">
                        <div class="adress">Расстояние:</div>
                        <div class="val_adress"><?=number_format($arCost['ROUTE'], 0, '.', ' ')?> км</div>
                        <div class="clear"></div>
                    </div>
                    <?
                    //$tarif = model::getTarif($arCost['CENTER'], $arCost['ROUTE']);
                    $tarif = client::getTarif(
                        0,
                        0,
                        'cpt',
                        $arCost['CENTER'],
                        $arCost['ROUTE'],
                        model::getAgrohelperTariffs()
                    );
                    ?>
                    <div class="prop_area adress_val one_line">
                        <div class="adress">Тариф:</div>
                        <div class="val_adress"><?=number_format($tarif, 0, '.', ' ')?> руб/т</div>
                        <div class="clear"></div>
                    </div>

                    <div class="prop_area delivery_type">
                        <div class="name">Cпособ доставки:</div>
                        <div class="val">
                            <div class="row_area radio_group">
                                <?
                                foreach ($arResult['DELIVERY_LIST'] as $item) {
                                ?>
                                    <div class="radio_area delivery_self">
                                        <input readonly="readonly" data-text="<?=$item['VALUE']?>" type="radio" <? if ($item['XML_ID'] == 'c') { ?>checked="checked"<? } ?> value="<?=$item['XML_ID']?>" name="delivery" />
                                    </div>
                                <?
                                }
                                ?>
                            </div>
                        </div>
                        <div class="clear"></div>
                    </div>
                <?
                }
                ?>

                <div class="prop_area text_definition one_line">
                    <div class="name">Оплата:</div>
                    <div class="data">
                        <?
                        if ($arRequest['PAYMENT'] == 'pre') {
                            echo "Предоплата";
                        }
                        else {
                            echo "Постоплата";
                        }
                        ?>
                    </div>
                    <div class="clear"></div>
                </div>

                <?
                if (sizeof($arRequest['DOCS']) > 0) {
                ?>
                    <div class="prop_area adress_val">
                        <div class="adress">Потребность в документах:</div>
                        <?
                        foreach ($arRequest['DOCS'] as $val) {
                        ?>
                            <div class="val_adress"><?=$arResult['DOCS_LIST'][$val]['NAME']?></div>
                        <?
                        }
                        ?>
                        <div class="clear"></div>
                    </div>
                <?
                }
                ?>

                <?/*<div class="prop_area tonn_val" data-price="<?=$arCost['ACC_PRICE_CSM'];?>" data-remains="<?=$arRequest['REMAINS']?>">
                    <div class="name">Введите количество тонн: </div>
                    <div class="val deal_volume"><input type="text" name="volume" /> т.</div>
                </div>*/?>
                <?/*<div class="prop_area total">
                    <?
                    $total_disabled = false;
                        foreach ($arResult['USER_DEALS_RIGHTS'] as $cur_val) {
                            switch($cur_val) {
                                case 'n0':
                                    echo '<div class="no_deal_rights">Для заключения сделки необходимо <a href="/farmer/profile/">активировать полноценный режим работы</a></div>';
                                    $total_disabled = true;
                                    break;
                                case 'n1':
                                    echo '<div class="no_deal_rights">Для заключения сделки необходимо <a href="/farmer/link_to_partner/">привязаться к организатору</a></div>';
                                    $total_disabled = true;
                                    break;
                                case 'n2':
                                    echo '<div class="no_deal_rights">Для заключения сделки необходимо наличие договора с <a href="/farmer/link_to_partner/">текущим организатором</a></div>';
                                    $total_disabled = true;
                                    break;
                                case 'n3':
                                    echo '<div class="no_deal_rights' . (count($arResult['USER_DEALS_RIGHTS']) > 1 ? ' top_s2' : '') . '">Для заключения сделки необходимо <a href="/farmer/documents/">загрузить документы</a></div>';
                                    $total_disabled = true;
                                    break;
                                case 'no_p':
                                    if ($arRequest['PAYMENT'] == 'pre') {
                                        echo '<div class="no_deal_rights top_s' . count($arResult['USER_DEALS_RIGHTS']) . '">Для заключения сделки необходимо <a href="/farmer/documents/">загрузить документы баланса</a></div>';
                                        $total_disabled = true;
                                    }
                                    break;
                            }
                        }
                        ?>
                        <div class="name">Итого стоимость: </div><div class="val"><span class="decs_separators">0</span> руб<span class="val_type"></span></div>
                        <input type="submit" class="submit-btn inactive<?if($total_disabled){?> hard_disabled<?}?>" name="accept" value="Принять" />
                    </div>*/?>
                <div class="prop_area additional_submits">
                    <?/*<input type="submit" class="reject_but" name="reject" value="Отклонить запрос" />*/?>
                    <div class="hide_but">Свернуть</div>
                </div>
            </form>
        </div>
    <?
    }
    ?>
</div>
<?
}
else {
?>
    Ни одного запроса не найдено
<?
}
?>