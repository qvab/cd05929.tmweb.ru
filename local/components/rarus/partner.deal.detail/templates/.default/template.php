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

            <?if(!empty($arResult['PARTNER']['ID'])):
                ?>
                <span class="partner-name">
                    (организатор покупателя <a href="/profile/?uid=<?=$arResult['PARTNER']['USER']['ID']?>" target="_blank"><?=$arResult['PARTNER']['PARTNER_NAME'];?></a>)
                </span>
            <?endif;?>
        </div>
    <?endif;?>

    <?if(!empty($arResult['FARMER']['COMPANY'])):?>
        <div class="farmer">
            <div class="item">Поставщик:</div>
            <a href="/profile/?uid=<?=$arResult['PROPERTIES']['FARMER']['VALUE']?>" target="_blank"><?=$arResult['FARMER']['COMPANY']?></a>

            <?if(!empty($arResult['FARMER_PARTNER']['ID']) || !empty($arResult['FARMER_AGENT']['FULL_NAME'])):
                ?>
                <span class="partner-name">
                    (<?if(!empty($arResult['FARMER_PARTNER']['ID'])) {
                        ?>организатор поставщика <a href="/profile/?uid=<?=$arResult['FARMER_PARTNER']['USER']['ID'] ?>" target="_blank"><?= $arResult['FARMER_PARTNER']['PARTNER_NAME']; ?></a><?
                     }
                     if(!empty($arResult['FARMER_PARTNER']['ID']) && !empty($arResult['FARMER_AGENT']['FULL_NAME'])){?>, <?}
                     if(!empty($arResult['FARMER_AGENT']['FULL_NAME'])) {
                        ?>агент поставщика <?=$arResult['FARMER_AGENT']['FULL_NAME'];
                     }
                    ?>)
                </span>
            <?endif;?>
        </div>
    <?endif;?>
</div>

<?
if ($arResult['PROPERTIES']['STATUS']['VALUE_XML_ID'] == 'open') {
?>
    <form class="reject" action="" method="post">
        <input type="submit" class="empty_but submit-btn" name="reject" value="Аннулировать сделку">
    </form>
<?
}
elseif ($arResult['PROPERTIES']['STATUS']['VALUE_XML_ID'] == 'cancel') {
?>
    <div class="reject_info">Сделка аннулирована</div>
<?
}
?>

<a class="go_back cross" href="/partner/deals/"></a>

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
            <div class="name">Поиск покупателя и продавца</div>
            <div class="clip_item"></div>
            <div class="clear l"></div>
        </div>

        <div class="line_additional" <? if ($class == 'current') { ?>style="display: block;"<? } ?>>
            <div class="line_additional_inner">
                <div class="prop_area i0">
                    <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                    Покупатель:
                    <a href="/profile/?uid=<?=$arResult['PROPERTIES']['CLIENT']['VALUE']?>">
                        <?=$arResult['CLIENT']['COMPANY']?>
                    </a>
                </div>
                <div class="prop_area i0">
                    <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                    Продавец:
                    <a href="/profile/?uid=<?=$arResult['PROPERTIES']['FARMER']['VALUE']?>">
                        <?=$arResult['FARMER']['COMPANY']?>
                    </a>
                </div>
                <?
                if (in_array('reject', array_keys($arResult['LOGS']))) {
                ?>
                    <div class="prop_area i0">
                        <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                        Сделка аннулирована
                    </div>
                <?
                }
                elseif (in_array('order_deal', array_keys($arResult['LOGS']))) {
                ?>
                    <div class="prop_area i0">
                        <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                        Согласие с условиями сделки от продавца получено
                    </div>
                <?
                }
                else {
                ?>
                    <div class="prop_area i0">
                        <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                        Ожидайте согласие с условиями сделки от продавца
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
                            Согласие с условиями перевозки от продавца получено
                        </div>

                    <?
                    }
                    else {
                    ?>
                        <div class="prop_area i0">
                            <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                            Ожидайте, идёт процесс поиска перевозчика
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
    if (sizeof(array_intersect(array('dkp_client', 'ds_client'), array_keys($arResult['LOGS']))) > 1)
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
                $douInfo = partner::getClientDouInfo(
                    $arResult['PROPERTIES']['PARTNER']['VALUE'],
                    $arResult['PROPERTIES']['CLIENT']['VALUE']
                );
                if ($douInfo['ID'] > 0) {
                ?>
                    <div class="prop_area i0">
                        <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                        Действующий с покупателем договор оказания услуг №<?=$douInfo['PROPERTY_DOU_NUM_VALUE']?> от <?=$douInfo['PROPERTY_DOU_DATE_VALUE']?> г.
                    </div>
                <?
                }
                else {
                ?>
                    <div class="prop_area i0">
                        <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                        Договор оказания услуг с покупателем: <br><br>
                        <form action="" method="post" enctype="multipart/form-data">
                            №<input type="text" class="signer_found_num" name="dou_num" value="" placeholder="" />
                            от<input type="text" class="signer_found_date" autocomplete="off" name="dou_date" onclick="var tmp_val = this.value; this.value = ''; BX.calendar({value: tmp_val, node: this, field: this, bTime: false, callback_after: goTriggerChange(this)}); changeCalendar();" placeholder="" />
                            <br><br>
                            <input type="submit" class="submit-btn" name="saveDouInfo" value="Сохранить"/>
                        </form>
                    </div>
                <?
                }

                if (in_array('ds_client', array_keys($arResult['LOGS']))) {
                ?>
                    <div class="prop_area i0">
                        <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                        Доп. соглашение к договору купли-продажи подписано покупателем: <a href="<?=$arResult['DOCS']['ds_client']['FILE']['SRC']?>" download="">ДС.pdf</a>
                    </div>
                <?
                }
                elseif (in_array('ds_partner', array_keys($arResult['LOGS']))) {
                ?>
                    <div class="prop_area i0">
                        <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                        Доп. соглашение к договору купли-продажи подписано: <a href="<?=$arResult['DOCS']['ds_partner']['FILE']['SRC']?>" download="">ДС.pdf</a><br>
                        Ожидайте подписание соглашения со стороны покупателя.
                    </div>
                <?
                }
                elseif (in_array('ds_ready', array_keys($arResult['LOGS']))) {
                ?>
                    <div class="prop_area i0">
                        <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                        Доп. соглашение к договору купли-продажи: <a href="<?=$arResult['DOCS']['ds']['FILE']['SRC']?>" download="">ДС.pdf</a><br><br>
                        <form action="" method="post" enctype="multipart/form-data">
                            <a href="javascript:void(0);" class="loader_click" data-for="ds">Загрузить подписанное соглашение</a>
                            <input type="file" name="ds" class="document" id="ds">
                            <input type="submit" class="submit-btn" value="Отправить"/>
                        </form>
                    </div>
                <?
                }
                elseif ($douInfo['ID'] > 0) {
                ?>
                    <div class="prop_area i0">
                        <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                        <a href="/partner/deals/<?=$arResult['ID']?>/?doc=ds">Сформировать доп. соглашение</a>
                    </div>
                <?
                }

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
                        Договор купли-продажи подписан: <a href="<?=$arResult['DOCS']['dkp_partner']['FILE']['SRC']?>" download="">Договор.pdf</a><br>
                        Ожидайте подписание договора со стороны покупателя.
                    </div>
                <?
                }
                elseif (in_array('dkp_ready', array_keys($arResult['LOGS']))) {
                ?>
                    <div class="prop_area i0">
                        <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                        Договор купли-продажи: <a href="<?=$arResult['DOCS']['dkp']['FILE']['SRC']?>" download="">Договор.pdf</a><br><br>
                        <form action="" method="post" enctype="multipart/form-data">
                            <a href="javascript:void(0);" class="loader_click" data-for="dkp">Загрузить подписанный договор</a>
                            <input type="file" name="dkp" class="document" id="dkp">
                            <input type="submit" class="submit-btn" value="Отправить"/>
                        </form>
                    </div>
                <?
                }
                elseif ($douInfo['ID'] > 0) {
                ?>
                    <div class="prop_area i0">
                        <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                        <a href="/partner/deals/<?=$arResult['ID']?>/?doc=dkp">Сформировать договор купли-продажи</a>
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
                    $douInfo = partner::getTransportDouInfo(
                        $arResult['PROPERTIES']['PARTNER']['VALUE'],
                        $arResult['PROPERTIES']['TRANSPORT']['VALUE']
                    );
                    if ($douInfo['ID'] > 0) {
                    ?>
                        <div class="prop_area i0">
                            <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                            Действующий с перевозчиком договор оказания услуг №<?=$douInfo['PROPERTY_DOU_NUM_VALUE']?> от <?=$douInfo['PROPERTY_DOU_DATE_VALUE']?> г.
                        </div>
                    <?
                    }
                    else {
                    ?>
                        <div class="prop_area i0">
                            <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                            Договор оказания услуг с перевозчиком: <br><br>
                            <form action="" method="post" enctype="multipart/form-data">
                                №<input type="text" class="signer_found_num" name="dou_num" value="" placeholder="" />
                                от<input type="text" class="signer_found_date" autocomplete="off" name="dou_date" onclick="var tmp_val = this.value; this.value = ''; BX.calendar({value: tmp_val, node: this, field: this, bTime: false, callback_after: goTriggerChange(this)}); changeCalendar();" placeholder="" />
                                <br><br>
                                <input type="submit" class="submit-btn" name="saveDouTrInfo" value="Сохранить"/>
                            </form>
                        </div>
                    <?
                    }

                    if (in_array('ds_transport_transport', array_keys($arResult['LOGS']))) {
                    ?>
                        <div class="prop_area i0">
                            <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                            Доп. соглашение к договору на транспортировку подписано транспортной компанией: <a href="<?=$arResult['DOCS']['ds_transport_transport']['FILE']['SRC']?>" download="">ДС.pdf</a>
                        </div>
                    <?
                    }
                    elseif (in_array('ds_transport_partner', array_keys($arResult['LOGS']))) {
                    ?>
                        <div class="prop_area i0">
                            <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                            Доп. соглашение к договору на транспортировку подписано: <a href="<?=$arResult['DOCS']['ds_transport_partner']['FILE']['SRC']?>" download="">ДС.pdf</a><br>
                            Ожидайте подписание соглашения со стороны транспортной компании.
                        </div>
                    <?
                    }
                    elseif (in_array('ds_transport_ready', array_keys($arResult['LOGS']))) {
                    ?>
                        <div class="prop_area i0">
                            <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                            Доп. соглашение к договору на транспортировку: <a href="<?=$arResult['DOCS']['ds_transport']['FILE']['SRC']?>" download="">ДС.pdf</a><br><br>
                            <form action="" method="post" enctype="multipart/form-data">
                                <a href="javascript:void(0);" class="loader_click" data-for="ds_transport">Загрузить подписанное соглашение</a>
                                <input type="file" name="ds_transport" class="document" id="ds_transport">
                                <input type="submit" class="submit-btn" value="Отправить"/>
                            </form>
                        </div>
                    <?
                    }
                    elseif ($douInfo['ID'] > 0) {
                    ?>
                        <div class="prop_area i0">
                            <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                            <a href="/partner/deals/<?=$arResult['ID']?>/?doc=ds_transport">Сформировать доп. соглашение</a>
                        </div>
                    <?
                    }

                    if (in_array('dtr_transport', array_keys($arResult['LOGS']))) {
                    ?>
                        <div class="prop_area i0">
                            <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                            Договор на транспортировку подписан транспортной компанией: <a href="<?=$arResult['DOCS']['dtr_transport']['FILE']['SRC']?>" download="">Договор.pdf</a>
                        </div>
                    <?
                    }
                    elseif (in_array('dtr_partner', array_keys($arResult['LOGS']))) {
                    ?>
                        <div class="prop_area i0">
                            <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                            Договор на транспортировку подписан: <a href="<?=$arResult['DOCS']['dtr_partner']['FILE']['SRC']?>" download="">Договор.pdf</a><br>
                            Ожидайте подписание договора со стороны транспортной компании.
                        </div>
                    <?
                    }
                    elseif (in_array('dtr_ready', array_keys($arResult['LOGS']))) {
                    ?>
                        <div class="prop_area i0">
                            <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                            Договор транспортировки: <a href="<?=$arResult['DOCS']['dtr']['FILE']['SRC']?>" download="">Договор.pdf</a><br><br>
                            <form action="" method="post" enctype="multipart/form-data">
                                <a href="javascript:void(0);" class="loader_click" data-for="dtr">Загрузить подписанный договор</a>
                                <input type="file" name="dtr" class="document" id="dtr">
                                <input type="submit" class="submit-btn" value="Отправить"/>
                            </form>
                        </div>
                    <?
                    }
                    elseif ($douInfo['ID'] > 0) {
                    ?>
                        <div class="prop_area i0">
                            <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                            <a href="/partner/deals/<?=$arResult['ID']?>/?doc=dtr">Сформировать договор транспортировки</a>
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
        if (sizeof(array_intersect(array('dkp_client', 'ds_client'), array_keys($arResult['LOGS']))) > 1)
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
                            Счет на оплату по договору купли-продажи: <a href="<?=$arResult['DOCS']['prepayment']['FILE']['SRC']?>" download="">просмотреть</a>
                        </div>
                    <?
                    }
                    elseif ($arResult['DOCS']['prepayment']['FILE']['SRC']) {
                    ?>
                        <div class="prop_area i0">
                            <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                            Счет на оплату по договору купли-продажи: <a href="<?=$arResult['DOCS']['prepayment']['FILE']['SRC']?>" download="">просмотреть</a>
                        </div>
                        <div class="prop_area i0 with_button">
                            <form action="" method="post">
                                <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                                <input type="hidden" name="prepayment" value="Y">
                                <input type="submit" class="submit-btn" value="Отправить счет"/>
                            </form>
                        </div>
                    <?
                    }
                    else {
                    ?>
                        <div class="prop_area i0">
                            <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                            <a href="/partner/deals/<?=$arResult['ID']?>/?doc=prepayment">Сформировать счет</a>
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
                        Реестры приемки СХП.<br>
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
                ?>

                <div class="add-line">
                    <div class="vi-item">
                        <div class="vi-row">
                            <div class="vi-cell"><input type="text" name="car[]" class="car_number"></div>
                            <div class="vi-cell"><input type="text" name="weight[]"></div>
                            <div class="vi-cell"><input type="text" name="cost[]"></div>
                            <div class="vi-cell"></div>
                            <div class="vi-cell"></div>
                            <? if ($arResult['PROPERTIES']['DELIVERY']['VALUE_XML_ID'] == 'c') { ?>
                                <div class="vi-cell"></div>
                                <div class="vi-cell"></div>
                            <? } ?>
                        </div>
                        <div class="clear"></div>
                    </div>
                </div>

                <?
                if ($arResult['PROPERTIES']['DELIVERY']['VALUE_XML_ID'] == 'a') {
                    //FCA
                    $title = 'РЦ FCA (ЦСМ), руб';
                }
                elseif ($arResult['PROPERTIES']['DELIVERY']['VALUE_XML_ID'] == 'c') {
                    //CPT+перевозка
                    $title = 'РЦ CPT, руб';
                }
                elseif ($arResult['PROPERTIES']['DELIVERY']['VALUE_XML_ID'] == 'b') {
                    //CPT
                    $title = 'РЦ CPT (ЦСМ), руб';
                }
                else {
                    $title = 'Расчетная цена';
                }

                if ($arResult['PROPERTIES']['DELIVERY']['VALUE_XML_ID'] == 'c') {
                    $class = 'col7';
                }
                else {
                    $class = 'col5';
                }
                ?>

                <form action="" method="post">
                    <div class="prop_area i0">
                        <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                        Данные реестра покупателя:

                        <div class="vi_table_area">
                            <div class="vi-table reestr-table <?=$class?>" data-deal="<?=$arResult['ID']?>">
                                <div class="vi-item">
                                    <div class="vi-row">
                                        <div class="vi-cell">Номер машины (пример А700АК136)</div>
                                        <div class="vi-cell">Нетто выгруженное, кг</div>
                                        <div class="vi-cell">Сумма, руб</div>
                                        <div class="vi-cell"><?=$title?></div>
                                        <div class="vi-cell">Качество, % (скидка(-) / прибавка(+))</div>
                                        <? if ($arResult['PROPERTIES']['DELIVERY']['VALUE_XML_ID'] == 'c') { ?>
                                            <div class="vi-cell">Стоимость перевозки</div>
                                            <div class="vi-cell">ЦСМ, руб</div>
                                        <? } ?>
                                    </div>
                                    <div class="clear"></div>
                                </div>

                                <?
                                if (sizeof($arResult['VI']) > 0) {
                                    foreach ($arResult['VI'] as $key => $item) {
                                    ?>
                                        <div class="vi-item val with_del" data-vi="<?=$item['ID']?>">
                                            <div class="vi-row">
                                                <div class="vi-cell car-cell"><?=$item['CAR']?></div>
                                                <div class="vi-cell weight-cell"><?=number_format($item['WEIGHT'], 0, '.', ' ')?></div>
                                                <div class="vi-cell cost-cell"><?=number_format($item['COST'], 2, '.', ' ')?></div>
                                                <div class="vi-cell rc-cell"><?=number_format($item['RC'], 2, '.', ' ')?></div>
                                                <div class="vi-cell dump-cell"><?=$item['DUMP']?></div>
                                                <? if ($arResult['PROPERTIES']['DELIVERY']['VALUE_XML_ID'] == 'c') { ?>
                                                    <div class="vi-cell transport-cell"><?=number_format($item['TRANSPORT_COST'], 2, '.', ' ')?></div>
                                                    <div class="vi-cell csm-cell"><?=number_format($item['CSM'], 2, '.', ' ')?></div>
                                                <? } ?>
                                            </div>
                                            <div class="clear"></div>
                                            <?
                                            if (!in_array('complete', array_keys($arResult['LOGS']))) {
                                            ?>
                                                <div class="delete-vi-item" title="удалить запись" data-vi="<?=$item['ID']?>"></div>
                                            <?
                                            }
                                            ?>
                                        </div>
                                    <?
                                    }
                                    ?>
                                    <div class="summary">
                                        <div class="vi-row" style="font-weight: bold;">
                                            <div class="vi-cell">ИТОГО</div>
                                            <div class="vi-cell weight-cell"><?=number_format(1000 * $arResult['VI_SUMMARY']['WEIGHT'], 0, '.', ' ')?></div>
                                            <div class="vi-cell cost-cell"><?=number_format($arResult['VI_SUMMARY']['COST'], 2, '.', ' ')?></div>
                                            <div class="vi-cell rc-cell"><?=number_format($arResult['VI_SUMMARY']['RC'], 2, '.', ' ')?></div>
                                            <div class="vi-cell">&nbsp;</div>
                                            <? if ($arResult['PROPERTIES']['DELIVERY']['VALUE_XML_ID'] == 'c') { ?>
                                                <div class="vi-cell transport-cell"><?=number_format($arResult['VI_SUMMARY']['TRANSPORT_COST'], 2, '.', ' ')?></div>
                                                <div class="vi-cell csm-cell"><?=number_format($arResult['VI_SUMMARY']['CSM'], 2, '.', ' ')?></div>
                                            <? } ?>
                                        </div>
                                        <div class="clear"></div>
                                    </div>
                                    <?
                                    if (!in_array('complete', array_keys($arResult['LOGS']))) {
                                    ?>
                                        <div class="add-vi-title">
                                            Добавить запись
                                        </div>
                                    <?
                                    }
                                }

                                if (!in_array('complete', array_keys($arResult['LOGS']))) {
                                ?>
                                    <div class="vi-item">
                                        <div class="vi-row">
                                            <div class="vi-cell">
                                                <input data-checkval="y" data-checktype="car_number" type="text" name="car[]" class="car_number">
                                            </div>
                                            <div class="vi-cell">
                                                <input data-checkval="y" data-checktype="weight" type="text" name="weight[]">
                                            </div>
                                            <div class="vi-cell">
                                                <input data-checkval="y" data-checktype="price" type="text" name="cost[]">
                                            </div>
                                            <div class="vi-cell"></div>
                                            <div class="vi-cell"></div>
                                            <?if ($arResult['PROPERTIES']['DELIVERY']['VALUE_XML_ID'] == 'c') { ?>
                                                <div class="vi-cell"></div>
                                                <div class="vi-cell"></div>
                                            <? } ?>
                                        </div>
                                        <div class="clear"></div>
                                    </div>
                                <?
                                }
                                ?>
                            </div>
                        </div>
                        <div class="clear"></div>
                        <?
                        if (!in_array('complete', array_keys($arResult['LOGS']))) {
                        ?>
                            <div class="reestr_more more-btn">
                                <input type="button" value="Еще..." class="submit-btn">
                            </div>
                            <div class="clear"></div>

                            <input type="submit" class="submit-btn" name="save_reestr" value="Сохранить"/>
                            <?
                            if (sizeof($arResult['VI']) > 0) {
                            ?>
                                <input type="button" class="submit-btn edit-btn" name="" value="Редактировать"/>
                            <?
                            }
                            ?>
                            <div class="clear"></div>
                        <?
                        }
                        ?>
                    </div>
                </form>

                <?
                if (in_array('reestr', array_keys($arResult['LOGS']))
                    && !in_array('complete', array_keys($arResult['LOGS']))
                    && sizeof($arResult['VI']) > 0
                ) {
                ?>
                    <div class="prop_area i0">
                        <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                        Завершить заполнение ведомости исполнения<br><br>
                        <form action="" method="post">
                            <input type="hidden" name="complete" value="Y">
                            <input type="submit" class="submit-btn" value="Завершить"/>
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
            <? if ($arResult['DOCS']['payment']['FILE']['SRC']) { ?>
                <div class="clip"></div>
            <? } ?>
        </div>

        <div class="line_additional" <? if ($class == 'current') { ?>style="display: block;"<? } ?>>
            <div class="line_additional_inner">
                <?
                if (in_array('payment_send', array_keys($arResult['LOGS']))) {
                ?>
                    <div class="prop_area i0">
                        <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                        Счет по договору купли-продажи отправлен: <a href="<?=$arResult['DOCS']['payment']['FILE']['SRC']?>" download="">просмотреть</a>
                    </div>
                    <div class="prop_area i0">
                        <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                        Счет на вознаграждение по договору купли-продажи отправлен: <a href="<?=$arResult['DOCS']['commission']['FILE']['SRC']?>" download="">скачать</a>
                    </div>
                    <?
                    if ($arResult['PROPERTIES']['DELIVERY']['VALUE_XML_ID'] == 'c') {
                    ?>
                        <div class="prop_area i0">
                            <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                            Счет на комиссию по договору транспортировки отправлен: <a href="<?=$arResult['DOCS']['commission_transport']['FILE']['SRC']?>" download="">скачать</a>
                        </div>
                    <?
                    }
                    ?>

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

                    if (in_array('close', array_keys($arResult['LOGS']))) {
                    ?>
                        <div class="prop_area i0">
                            <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                            Расчеты по сделке завершены
                        </div>
                    <?
                    }
                    elseif (($arResult['PROPERTIES']['DELIVERY']['VALUE_XML_ID'] == 'c'
                        && in_array('payment_transport_send', array_keys($arResult['LOGS'])))
                    || $arResult['PROPERTIES']['DELIVERY']['VALUE_XML_ID'] != 'c'
                    ) {
                    ?>
                        <div class="prop_area i0">
                            <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                            Расчеты по сделке завершены<br><br>
                            <form action="" method="post">
                                <input type="hidden" name="close" value="Y">
                                <input type="submit" class="submit-btn" value="Подтвердить"/>
                            </form>
                        </div>
                    <?
                    }
                }
                else {
                ?>
                    <div class="prop_area i0">
                        <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                        Счет на оплату по договору купли продажи: <a href="<?=$arResult['DOCS']['payment']['FILE']['SRC']?>" download="">просмотреть</a>
                    </div>

                    <div class="prop_area i0">
                        <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                        Счет на вознаграждение по договору купли-продажи: <a href="<?=$arResult['DOCS']['commission']['FILE']['SRC']?>" download="">просмотреть</a>
                    </div>
                    <?
                    if ($arResult['PROPERTIES']['DELIVERY']['VALUE_XML_ID'] == 'c') {
                    ?>
                        <div class="prop_area i0">
                            <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                            Счет на оплату комиссии по договору транспортировки: <a href="<?=$arResult['DOCS']['commission_transport']['FILE']['SRC']?>" download="">просмотреть</a>
                        </div>
                    <?
                    }
                    ?>
                    <div class="prop_area i0 with_button">
                        <form action="" method="post">
                            <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                            <input type="hidden" name="payment" value="Y">
                            <input type="submit" class="submit-btn" value="Отправить"/>
                        </form>
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