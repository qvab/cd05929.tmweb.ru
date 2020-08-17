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

$assetInst = \Bitrix\Main\Page\Asset::getInstance();
$assetInst->addCss(SITE_TEMPLATE_PATH . '/css/jquery.rating.css');
$assetInst->addJs(SITE_TEMPLATE_PATH . '/js/jquery.rating-2.0.js');
?>
<div class="page_sub_title">
    <span class="bold"><?=current($arResult['DISPLAY_PROPERTIES']['CULTURE']['LINK_ELEMENT_VALUE'])['NAME']?></span>
    <span class="num">/ Сделка #<?=$arResult['ID']?> от <?=date('d.m.Y', strtotime($arResult['ACTIVE_FROM']))?></span>
</div>

<!--Участники сделки-->
<div class="participants">
    <?if(!empty($arResult['CLIENT']['COMPANY'])):?>
        <div class="client">
            <div class="item">Покупатель:</div>
            <a href="/profile/?uid=<?=$arResult['PROPERTIES']['CLIENT']['VALUE']?>" target="_blank"><?=$arResult['CLIENT']['COMPANY']?></a>

            <?if(!empty($arResult['PARTNER']['ID'])):?>
                <span class="partner-name">
                    (организатор покупателя <a href="/profile/?uid=<?=$arResult['PARTNER']['USER']['ID']?>" target="_blank"><?=$arResult['PARTNER']['PARTNER_NAME']?></a>)
                </span>
            <?endif;?>
        </div>
    <?endif;?>

    <?if(!empty($arResult['FARMER']['COMPANY'])):?>
        <div class="farmer">
            <div class="item">Поставщик:</div>
            <a href="/profile/?uid=<?=$arResult['PROPERTIES']['FARMER']['VALUE']?>" target="_blank"><?=$arResult['FARMER']['COMPANY']?></a>

            <?if(!empty($arResult['FARMER_PARTNER']['ID'])):
                ?>
                <span class="partner-name">
                    (<?if(!empty($arResult['FARMER_PARTNER']['ID'])) {
                        ?>организатор поставщика <a href="/profile/?uid=<?= $arResult['FARMER_PARTNER']['USER']['ID'] ?>" target="_blank"><?= $arResult['FARMER_PARTNER']['PARTNER_NAME']; ?></a><?
                    }
                    ?>)
                </span>
            <?endif;?>
        </div>
    <?endif;?>
</div>

<?
if ($arResult['PROPERTIES']['STATUS']['VALUE_XML_ID'] == 'cancel') {
?>
    <div class="reject_info">Сделка аннулирована</div>
<?
}
?>

<a class="go_back cross" href="/farmer/deals/"></a>

<div class="list_page_rows deals deal_detail">
    <?
    $k = $l = 1;
    $class = 'current';
    if (sizeof(array_intersect(array('order_deal', 'reject'), array_keys($arResult['LOGS']))) > 0)
        $class = 'deal_done';
    ?>
    <div class="line_area <?=$class?> with_info<? if ($class == 'current') { ?> active<? } ?>">
        <div class="line_inner">
            <div class="indicator"></div>
            <div class="step_num"><?=$k?></div>
            <div class="name">Поиск покупателя</div>
            <div class="clip_item"></div>
            <div class="clear l"></div>
        </div>

        <div class="line_additional" <? if ($class == 'current') { ?>style="display: block;"<? } ?>>
            <div class="line_additional_inner">
                <div class="prop_area i0">
                    <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                    Покупатель найден:
                    <a href="/profile/?uid=<?=$arResult['PROPERTIES']['CLIENT']['VALUE']?>">
                        <?=$arResult['CLIENT']['COMPANY']?>
                    </a>
                </div>
                <?
                if (isset($arResult['CLIENT_RATING'][$arResult['PROPERTIES']['CLIENT']['VALUE']])) {
                    $rating = $arResult['CLIENT_RATING'][$arResult['PROPERTIES']['CLIENT']['VALUE']];
                    $class = '';

                    if (!is_numeric($rating['RATE'])) $class = 'grey';
                    elseif ($rating['RATE'] > 7) $class = 'green';
                    elseif ($rating['RATE'] > 4) $class = 'yellow';
                    else $class = 'red';
                    ?>
                    <div class="prop_area i0">
                        <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                        <div class="adress_val">
                            <div class="adress rate_total">Рейтинг покупателя: <div class="rate_val <?//=$class?>"><input type="hidden" name="val" value="<?=$rating['RATE'] / 2?>" /></div></div>
                            <div class="client_rating">
                                <?
                                if (!is_numeric($rating['REC'])) $class = 'grey';
                                elseif ($rating['REC'] > 7) $class = 'green';
                                elseif ($rating['REC'] > 4) $class = 'yellow';
                                else $class = 'red';
                                ?>
                                <div class="rate_item i1">
                                    <div class="rate_title">Своевременность приемки продукции</div>
                                    <div class="rate_val <?=$class?>"><?=sprintf('%.1f', $rating['REC']);?></div>
                                </div>

                                <?
                                if (!is_numeric($rating['LAB'])) $class = 'grey';
                                elseif ($rating['LAB'] > 7) $class = 'green';
                                elseif ($rating['LAB'] > 4) $class = 'yellow';
                                else $class = 'red';
                                ?>
                                <div class="rate_item i2">
                                    <div class="rate_title">Оценка качества продукции</div>
                                    <div class="rate_val <?=$class?>"><?=sprintf('%.1f', $rating['LAB']);?></div>
                                </div>

                                <?
                                if (!is_numeric($rating['PAY'])) $class = 'grey';
                                elseif ($rating['PAY'] > 7) $class = 'green';
                                elseif ($rating['PAY'] > 4) $class = 'yellow';
                                else $class = 'red';
                                ?>
                                <div class="rate_item i3">
                                    <div class="rate_title">Своевременность оплаты</div>
                                    <div class="rate_val <?=$class?>"><?=sprintf('%.1f', $rating['PAY']);?></div>
                                </div>
                            </div>
                            <div class="clear"></div>
                        </div>
                    </div>
                <?
                }

                if (in_array('reject', array_keys($arResult['LOGS']))) {
                ?>
                    <div class="prop_area i0">
                        <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                        Сделка аннулирована
                    </div>
                <?
                }
                if (in_array('order_deal', array_keys($arResult['LOGS']))) {
                ?>
                    <div class="prop_area i0">
                        <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                        Согласие с условиями сделки отправлено
                    </div>
                <?
                }
                else {
                ?>
                    <div class="prop_area i0">
                        <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                        <form action="" method="post">
                            <input type="hidden" name="agree" value="Y">
                            <input type="submit" class="submit-btn" value="Согласен"/>
                        </form>
                    </div>
                <?
                }
                ?>
            </div>
        </div>
    </div>

    <?
    $k++; $l = 1;
    unset($class);
    if ($arResult['PROPERTIES']['STAGE']['VALUE_XML_ID'] == 'search')
        $class = 'current';
    if (in_array($arResult['PROPERTIES']['DELIVERY']['VALUE_XML_ID'], array('a', 'b'))
        || sizeof(array_intersect(array('no_transport', 'order_transport'), array_keys($arResult['LOGS']))) > 0)
        $class = 'deal_done';
    if (in_array('reject', array_keys($arResult['LOGS'])) && $class == 'current')
        unset($class);
    ?>
    <div class="line_area <?=$class?> with_info<? if ($class == 'current') { ?> active<? } ?>">
        <div class="line_inner">
            <div class="indicator"></div>
            <div class="step_num"><?=$k?></div>
            <div class="name">Поиск перевозчика</div>
            <div class="clip_item"></div>
            <div class="clear l"></div>
        </div>

        <div class="line_additional" <? if ($class == 'current') { ?>style="display: block;"<? } ?>>
            <div class="line_additional_inner">
                <?
                if ($arResult['PROPERTIES']['DELIVERY']['VALUE_XML_ID'] == 'a') {
                ?>
                    <div class="prop_area i0">
                        <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                        Доставляется транспортом покупателя
                    </div>
                <?
                }
                elseif ($arResult['PROPERTIES']['DELIVERY']['VALUE_XML_ID'] == 'b') {
                ?>
                    <div class="prop_area i0">
                        <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                        Доставляется транспортом продавца
                    </div>
                <?
                }
                elseif ($arResult['PROPERTIES']['DELIVERY']['VALUE_XML_ID'] == 'c') {
                    if (in_array('no_transport', array_keys($arResult['LOGS']))) {
                    ?>
                        <div class="prop_area i0">
                            <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                            Перевозчик не найден
                        </div>
                    <?
                    }
                    elseif (in_array('order_transport', array_keys($arResult['LOGS']))) {
                    ?>
                        <div class="prop_area i0">
                            <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                            Перевозчик найден:
                            <a href="/profile/?uid=<?=$arResult['PROPERTIES']['TRANSPORT']['VALUE']?>">
                                <?=$arResult['TRANSPORT']['COMPANY']?>
                            </a>
                        </div>
                        <div class="prop_area i0">
                            <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                            Согласие с условиями перевозки отправлено
                        </div>
                    <?
                    }
                    elseif (in_array('transport', array_keys($arResult['LOGS']))) {
                    ?>
                        <div class="prop_area i0">
                            <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                            Перевозчик найден:
                            <a href="/profile/?uid=<?=$arResult['PROPERTIES']['TRANSPORT']['VALUE']?>">
                                <?=$arResult['TRANSPORT']['COMPANY']?>
                            </a>
                        </div>
                        <div class="prop_area i0">
                            <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                            <form action="" method="post">
                                <input type="hidden" name="agree_transport" value="Y">
                                <input type="submit" class="submit-btn" value="Согласен"/>
                            </form>
                        </div>
                    <?
                    }
                    else {
                        $val = 'Отправить';
                        if ($arResult['PROPERTIES']['TARIF']['VALUE'] > 0) {
                            $val = 'Изменить';
                        }

                        $currentAccPriceCsm = $arResult['PROPERTIES']['ACC_PRICE']['VALUE'] - $arResult['TARIF'];
                        ?>

                        <div class="prop_area i0">
                            <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                            <form action="" method="post">
                                <input type="hidden" name="acc_price" value="<?=$arResult['PROPERTIES']['ACC_PRICE']['VALUE']?>">
                                Тариф на перевозку
                                <div class="row" style="margin-bottom: 15px;">
                                    <div class="sub_row txt">
                                        <div class="quality-param-intro txt">
                                            <span class="minus minus_bg" data-step="50" onclick="rrsClickMin(this);" data-min="<?=$arResult['TARIFF_MIN']?>"></span>
                                            <input type="text" name="tarif" value="<?=$arResult['TARIF']?>" readonly>
                                            <span class="plus plus_bg" data-step="50" onclick="rrsClickMax(this);"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="csm_price">
                                    Прогноз цены c места:
                                    <span>
                                        <?
                                        if ($arResult['PROPERTIES']['ACC_PRICE_CSM']['VALUE'] > 0) {
                                        ?>
                                            <?=number_format($currentAccPriceCsm, 2, '.', '&nbsp;')?>&nbsp;руб/т
                                        <?
                                        }
                                        else {
                                            echo '-';
                                        }
                                        ?>
                                    </span>
                                </div>
                                <input type="submit" class="submit-btn" value="<?=$val?>"/>
                            </form>
                        </div>
                        <?
                        if ($arResult['PROPERTIES']['TARIF']['VALUE'] > 0) {
                        ?>
                            <div class="prop_area i0">
                                <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                                <b>Для поиска перевозчика установлен тариф: <?=$arResult['PROPERTIES']['TARIF']['VALUE']?> руб.</b>
                            </div>
                        <?
                        }
                        ?>

                        <div class="prop_area i0">
                            <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                            Расстояние до адреса доставки: <?=$arResult['PROPERTIES']['ROUTE']['VALUE']?> км
                        </div>
                        <div class="prop_area i0">
                            <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                            <form action="" method="post">
                                Если перевозчик не найден, вы можете изменить способ доставки:
                                <div class="row">
                                    <div class="row_val">
                                        <div class="radio_group">
                                            <? foreach ($arResult['DELIVERY_LIST'] as $item) { ?>
                                                <div class="radio_area">
                                                    <input type="radio" name="delivery" data-text="<?=$item['VALUE']?>" id="delivery<?=$item['XML_ID']?>" value="<?=$item['XML_ID']?>" <? if ($item["XML_ID"] == 'c') { ?>checked="checked"<? } ?>>
                                                </div>
                                            <? } ?>
                                        </div>
                                    </div>
                                </div>
                                <input type="submit" class="submit-btn" value="Изменить"/>
                            </form>
                        </div>
                    <?
                    }
                }
                ?>
            </div>
        </div>
    </div>

    <?
    $k++; $l = 1;
    unset($class);
    if (in_array($arResult['PROPERTIES']['STAGE']['VALUE_XML_ID'], array('pre_dkp', 'dkp')))
        $class = 'current';
    if (in_array('dkp_client', array_keys($arResult['LOGS'])))
        $class = 'deal_done';
    if (in_array('reject', array_keys($arResult['LOGS'])) && $class == 'current')
        unset($class);
    ?>
    <div class="line_area <?=$class?> with_info<? if ($class == 'current') { ?> active<? } ?>">
        <div class="line_inner">
            <div class="indicator"></div>
            <div class="step_num"><?=$k?></div>
            <div class="name">Оформление договора купли-продажи</div>
            <div class="clip_item"></div>
            <div class="clear l"></div>
            <? if ($arResult['DOCS']['dkp']['FILE']['SRC']
                || $arResult['DOCS']['dkp_partner']['FILE']['SRC']
                || $arResult['DOCS']['dkp_client']['FILE']['SRC']
            ) { ?>
                <div class="clip"></div>
            <? } ?>
        </div>

        <div class="line_additional" <? if ($class == 'current') { ?>style="display: block;"<? } ?>>
            <div class="line_additional_inner">
                <?
                if (in_array('dkp_client', array_keys($arResult['LOGS']))) {
                ?>
                    <div class="prop_area i0">
                        <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                        Договор купли-продажи подписан покупателем: <a href="<?=$arResult['DOCS']['dkp_client']['FILE']['SRC']?>" download="">Договор.pdf</a>
                    </div>
                <?
                }
                elseif (in_array('dkp_partner', array_keys($arResult['LOGS']))) {
                ?>
                    <div class="prop_area i0">
                        <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                        Договор купли-продажи: <a href="<?=$arResult['DOCS']['dkp_partner']['FILE']['SRC']?>" download="">Договор.pdf</a><br>
                        Ожидайте подписание договора со стороны покупателя.
                    </div>
                <?
                }
                elseif (in_array('dkp_ready', array_keys($arResult['LOGS']))) {
                ?>
                    <div class="prop_area i0">
                        <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                        Договор купли-продажи<? if ($arResult['DOCS']['dkp']['FILE']['SRC']) { ?>: <a href="<?=$arResult['DOCS']['dkp']['FILE']['SRC']?>" download="">Договор.pdf</a><? } ?>
                    </div>
                <?
                }
                else {
                ?>
                    <div class="prop_area i0">
                        <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                        Подготовка договора купли-продажи
                    </div>
                <?
                }
                ?>
            </div>
        </div>
    </div>

    <?
    if ($arResult['PROPERTIES']['DELIVERY']['VALUE_XML_ID'] == 'c') {
        $k++; $l = 1;
        unset($class);
        if (in_array($arResult['PROPERTIES']['STAGE']['VALUE_XML_ID'], array('pre_dkp', 'dkp')))
            $class = 'current';
        if (in_array('dtr_transport', array_keys($arResult['LOGS'])))
            $class = 'deal_done';
        if (in_array('reject', array_keys($arResult['LOGS'])) && $class == 'current')
            unset($class);
        ?>
        <div class="line_area <?=$class?> with_info<? if ($class == 'current') { ?> active<? } ?>">
            <div class="line_inner">
                <div class="indicator"></div>
                <div class="step_num"><?=$k?></div>
                <div class="name">Оформление договора транспортировки</div>
                <div class="clip_item"></div>
                <div class="clear l"></div>
                <? if ($arResult['DOCS']['dtr']['FILE']['SRC']
                    || $arResult['DOCS']['dtr_partner']['FILE']['SRC']
                    || $arResult['DOCS']['dtr_transport']['FILE']['SRC']
                ) { ?>
                    <div class="clip"></div>
                <? } ?>
            </div>

            <div class="line_additional" <? if ($class == 'current') { ?>style="display: block;"<? } ?>>
                <div class="line_additional_inner">
                    <?
                    if (in_array('dtr_transport', array_keys($arResult['LOGS']))) {
                    ?>
                        <div class="prop_area i0">
                            <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                            Договор на транспортировку подписан перевозчиком: <a href="<?=$arResult['DOCS']['dtr_transport']['FILE']['SRC']?>" download="">Договор.pdf</a>
                        </div>
                    <?
                    }
                    elseif (in_array('dtr_partner', array_keys($arResult['LOGS']))) {
                    ?>
                        <div class="prop_area i0">
                            <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                            Договор на транспортировку: <a href="<?=$arResult['DOCS']['dtr_partner']['FILE']['SRC']?>" download="">Договор.pdf</a><br>
                            Ожидайте подписание договора со стороны транспортной компании.
                        </div>
                    <?
                    }
                    elseif (in_array('dtr_ready', array_keys($arResult['LOGS']))) {
                    ?>
                        <div class="prop_area i0">
                            <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                            Договор на транспортировку<? if ($arResult['DOCS']['dtr']['FILE']['SRC']) { ?>: <a href="<?=$arResult['DOCS']['dtr']['FILE']['SRC']?>" download="">Договор.pdf</a><? } ?>
                        </div>
                    <?
                    }
                    else {
                    ?>
                        <div class="prop_area i0">
                            <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                            Подготовка договора на транспортировку
                        </div>
                    <?
                    }
                    ?>
                </div>
            </div>
        </div>
    <?
    }

    if ($arResult['REQUEST']['PAYMENT'] == 'pre') {
        $k++; $l = 1;
        unset($class);
        if (in_array('dkp_client', array_keys($arResult['LOGS'])))
            $class = 'current';
        if (in_array('execution', array_keys($arResult['LOGS'])))
            $class = 'deal_done';
        if (in_array('reject', array_keys($arResult['LOGS'])) && $class == 'current')
            unset($class);
        ?>
        <div class="line_area <?=$class?> with_info<? if ($class == 'current') { ?> active<? } ?>">
            <div class="line_inner">
                <div class="indicator"></div>
                <div class="step_num"><?=$k?></div>
                <div class="name">Внесение предоплаты</div>
                <div class="clip_item"></div>
                <div class="clear l"></div>
                <? if ($arResult['DOCS']['prepayment']['FILE']['SRC']) { ?>
                    <div class="clip"></div>
                <? } ?>
            </div>

            <div class="line_additional" <? if ($class == 'current') { ?>style="display: block;"<? } ?>>
                <div class="line_additional_inner">
                    <?
                    if (in_array('prepayment_send', array_keys($arResult['LOGS']))) {
                    ?>
                        <div class="prop_area i0">
                            <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                            Счет на оплату по договору купли-продажи: <a href="<?=$arResult['DOCS']['prepayment']['FILE']['SRC']?>">просмотреть</a>
                        </div>
                    <?
                    }
                    else {
                    ?>
                        <div class="prop_area i0">
                            <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                            Ожидайте внесение предоплаты по договору купли-продажи
                        </div>
                    <?
                    }
                    ?>
                </div>
            </div>
        </div>
    <?
    }

    $k++; $l = 1;
    unset($class);
    if (in_array('execution', array_keys($arResult['LOGS'])))
        $class = 'current';
    if (in_array('complete', array_keys($arResult['LOGS'])))
        $class = 'deal_done';
    if (in_array('reject', array_keys($arResult['LOGS'])) && $class == 'current')
        unset($class);
    ?>
    <div class="line_area <?=$class?> with_info<? if ($class == 'current') { ?> active<? } ?>">
        <div class="line_inner">
            <div class="indicator"></div>
            <div class="step_num"><?=$k?></div>
            <div class="name">Исполнение заказа</div>
            <div class="clip_item"></div>
            <div class="clear l"></div>
            <? if (sizeof($arResult['DOCS']['reestr']) > 0) { ?>
                <div class="clip"></div>
            <? } ?>
        </div>

        <div class="line_additional" <? if ($class == 'current') { ?>style="display: block;"<? } ?>>
            <div class="line_additional_inner">
                <?
                if (sizeof($arResult['DOCS']['reestr']) > 0) {
                ?>
                    <div class="prop_area i0">
                        <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                        Реестры приемки СХП.<br><br>
                        <?
                        foreach ($arResult['DOCS']['reestr'] as $file) {
                        ?>
                            Документ от <?=$file['DATE_CREATE']?>: <a href="<?=$file['FILE']['SRC']?>">скачать</a><br>
                        <?
                        }
                        ?>
                    </div>
                <?
                }
                else {
                ?>
                    <div class="prop_area i0">
                        <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                        Ожидайте загрузку реестров приемки СХП
                    </div>
                <?
                }

                if (sizeof($arResult['VI']) > 0) {
                ?>
                    <div class="prop_area i0">
                        <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                        Ведомость исполнения:<br><br>
                        <div class="vi-table">
                            <div class="vi-row">
                                <div class="vi-cell">Номер машины</div>
                                <div class="vi-cell">Нетто выгруженное, кг</div>
                                <div class="vi-cell">Сумма, руб</div>
                                <div class="vi-cell">Расчетная цена, руб</div>
                                <div class="vi-cell">Качество, %</div>
                                <? if ($arResult['PROPERTIES']['DELIVERY']['VALUE_XML_ID'] == 'c') { ?><div class="vi-cell">Стоимость перевозки, руб</div><? } ?>
                            </div>
                            <div class="clear"></div>
                            <?
                            foreach ($arResult['VI'] as $key => $item) {
                            ?>
                                <div class="vi-row">
                                    <div class="vi-cell"><?=$item['CAR']?></div>
                                    <div class="vi-cell"><?=number_format($item['WEIGHT'], 0, ',', ' ')?></div>
                                    <div class="vi-cell"><?=number_format($item['COST'], 2, ',', ' ')?></div>
                                    <div class="vi-cell"><?=number_format($item['RC'], 2, ',', ' ')?></div>
                                    <div class="vi-cell"><?=$item['DUMP']?></div>
                                    <? if ($arResult['PROPERTIES']['DELIVERY']['VALUE_XML_ID'] == 'c') { ?>
                                        <div class="vi-cell"><?=number_format($item['TRANSPORT_COST'], 2, ',', ' ')?></div>
                                    <? } ?>
                                </div>
                                <div class="clear"></div>
                            <?
                            }
                            ?>
                            <div class="vi-row" style="font-weight: bold;">
                                <div class="vi-cell">ИТОГО</div>
                                <div class="vi-cell"><?=number_format(1000 * $arResult['VI_SUMMARY']['WEIGHT'], 0, ',', ' ')?></div>
                                <div class="vi-cell"><?=number_format($arResult['VI_SUMMARY']['COST'], 2, ',', ' ')?></div>
                                <div class="vi-cell"><?=number_format($arResult['VI_SUMMARY']['RC'], 2, ',', ' ')?></div>
                                <div class="vi-cell">&nbsp;</div>
                                <? if ($arResult['PROPERTIES']['DELIVERY']['VALUE_XML_ID'] == 'c') { ?>
                                    <div class="vi-cell"><?=number_format($arResult['VI_SUMMARY']['TRANSPORT_COST'], 2, ',', ' ')?></div>
                                <? } ?>
                            </div>
                            <div class="clear"></div>
                        </div>
                    </div>
                <?
                }
                else {
                ?>
                    <div class="prop_area i0">
                        <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                        Ожидайте загрузки ведомости исполнения
                    </div>
                <?
                }
                ?>
            </div>
        </div>
    </div>

    <?
    $k++; $l = 1;
    unset($class);
    if ($arResult['PROPERTIES']['STAGE']['VALUE_XML_ID'] == 'complete')
        $class = 'current';
    if (in_array('close', array_keys($arResult['LOGS'])))
        $class = 'deal_done';
    if (in_array('reject', array_keys($arResult['LOGS'])) && $class == 'current')
        unset($class);
    ?>
    <div class="line_area <?=$class?> with_info<? if ($class == 'current') { ?> active<? } ?>">
        <div class="line_inner">
            <div class="indicator"></div>
            <div class="step_num"><?=$k?></div>
            <div class="name">Закрытие сделки</div>
            <div class="clip_item"></div>
            <div class="clear l"></div>
            <? if ($arResult['DOCS']['payment_transport']['FILE']['SRC']) { ?>
                <div class="clip"></div>
            <? } ?> 
        </div>

        <div class="line_additional" <? if ($class == 'current') { ?>style="display: block;"<? } ?>>
            <div class="line_additional_inner">
                <?
                if (in_array('payment_transport_send', array_keys($arResult['LOGS']))) {
                ?>
                    <div class="prop_area i0">
                        <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                        Счет на оплату по договору транспортировки:
                        <a href="<?=$arResult['DOCS']['payment_transport']['FILE']['SRC']?>">скачать</a>
                    </div>

                    <div class="prop_area i0">
                        <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                        Акт сдачи-приёмки услуг к договору оказания услуг: <a href="<?=$arResult['DOCS']['act_deal']['FILE']['SRC']?>" download="">скачать</a>
                    </div>

                    <?
                    if ($arResult['PROPERTIES']['DELIVERY']['VALUE_XML_ID'] == 'c') {
                    ?>
                        <div class="prop_area i0">
                            <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                            Акт сдачи-приемки услуг к договору на транспортировку: <a href="<?=$arResult['DOCS']['act_transport']['FILE']['SRC']?>" download="">скачать</a>
                        </div>
                    <?
                    }
                    ?>

                    <?/*<div class="prop_area i0">
                        <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                        Счет на оплату комиссии по договору транспортировки:
                        <a href="<?=$arResult['DOCS']['commission_transport']['FILE']['SRC']?>">скачать</a>
                    </div>*/?>
                <?
                }
                else {
                ?>
                    <div class="prop_area i0">
                        <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                        Ожидайте счет на оплату по договору транспортировки
                    </div>
                <?
                }
                ?>
            </div>
        </div>
    </div>

</div>
<?
$templateData = $arResult;
?>