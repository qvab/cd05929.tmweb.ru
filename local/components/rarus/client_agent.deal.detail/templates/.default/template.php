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

<div class="tab_form deals detail">
    <div class="tab_form_inner" id="tab_form_inner">
        <div class="item<? if (!in_array($_REQUEST['page'], array('info', 'docs'))) { ?> active<? } ?>">
            <? if (!in_array($_REQUEST['page'], array('info', 'docs'))) { ?>
                <span>Основное</span>
            <? } else { ?>
                <a href="<?=$arParams['SELF_URL']?>">Основное</a>
            <? } ?>
        </div>
        <div class="item<? if ($_REQUEST['page'] == 'info') { ?> active<? } ?>">
            <? if ($_REQUEST['page'] == 'info') { ?>
                <span>Информация</span>
            <? } else { ?>
                <a href="<?=$arParams['SELF_URL']?>?page=info">Информация</a>
            <? } ?>
        </div>
        <div class="item<? if ($_REQUEST['page'] == 'docs') { ?> active<? } ?> last">
            <? if ($_REQUEST['page'] == 'docs') { ?>
                <span>Документы</span>
            <? } else { ?>
                <a href="<?=$arParams['SELF_URL']?>?page=docs">Документы</a>
            <? } ?>
        </div>
        <div class="clear"></div>
    </div>
</div>

<?
if ($arResult['PROPERTIES']['STATUS']['VALUE_XML_ID'] == 'cancel') {
?>
    <div class="reject_info">Сделка аннулирована</div>
<?
}
?>

<a class="go_back cross" href="/client/deals/"></a>

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
            <div class="name">Поиск продавца</div>
            <div class="clip_item"></div>
            <div class="clear l"></div>
        </div>

        <div class="line_additional" <? if ($class == 'current') { ?>style="display: block;"<? } ?>>
            <div class="line_additional_inner">
                <div class="prop_area i0">
                    <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                    Продавец найден:
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
                        Сделка подтверждена
                    </div>
                <?
                }
                else {
                ?>
                    <div class="prop_area i0">
                        <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                        Ожидайте подтверждения сделки от продавца
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
    <div class="line_area <?=$class?> with_info">
        <div class="line_inner">
            <div class="indicator"></div>
            <div class="step_num"><?=$k?></div>
            <div class="name">Организация доставки</div>
            <div class="clip_item"></div>
            <div class="clear l"></div>
        </div>

        <div class="line_additional no_content"></div>
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
            <? if ($arResult['DOCS']['dkp_partner']['FILE']['SRC']
                || $arResult['DOCS']['dkp_client']['FILE']['SRC']
            ) { ?>
                <div class="clip"></div>
            <? } ?>
        </div>

        <div class="line_additional" <? if ($class == 'current') { ?>style="display: block;"<? } ?>>
            <div class="line_additional_inner">
                <?
                if (in_array('ds_client', array_keys($arResult['LOGS']))) {
                ?>
                    <div class="prop_area i0">
                        <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                        Доп. соглашение к договору подписано: <a href="<?=$arResult['DOCS']['ds_client']['FILE']['SRC']?>" download="">ДС.pdf</a>
                    </div>
                <?
                }
                elseif (in_array('ds_partner', array_keys($arResult['LOGS']))) {
                ?>
                    <div class="prop_area i0">
                        <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                        Доп. соглашение к договору: <a href="<?=$arResult['DOCS']['ds_partner']['FILE']['SRC']?>" download="">ДС.pdf</a><br><br>
                        <form action="" method="post" enctype="multipart/form-data">
                            <a href="javascript:void(0);" class="loader_click" data-for="ds">Загрузить подписанное соглашение</a>
                            <input type="file" name="ds" class="document" id="ds">
                            <input type="submit" class="submit-btn" value="Отправить"/>
                        </form>
                    </div>
                <?
                }
                else {
                ?>
                    <div class="prop_area i0">
                        <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                        Ожидайте доп. соглашение к договору, подписанное организатором
                    </div>
                <?
                }

                if (in_array('dkp_client', array_keys($arResult['LOGS']))) {
                ?>
                    <div class="prop_area i0">
                        <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                        Договор купли-продажи подписан: <a href="<?=$arResult['DOCS']['dkp_client']['FILE']['SRC']?>" download="">Договор.pdf</a>
                    </div>
                <?
                }
                elseif (in_array('dkp_partner', array_keys($arResult['LOGS']))) {
                ?>
                    <div class="prop_area i0">
                        <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                        Договор купли-продажи: <a href="<?=$arResult['DOCS']['dkp_partner']['FILE']['SRC']?>" download="">Договор.pdf</a><br><br>
                        <form action="" method="post" enctype="multipart/form-data">
                            <a href="javascript:void(0);" class="loader_click" data-for="dkp">Загрузить подписанный договор</a>
                            <input type="file" name="dkp" class="document" id="dkp">
                            <input type="submit" class="submit-btn" value="Отправить"/>
                        </form>
                    </div>
                <?
                }
                else {
                ?>
                    <div class="prop_area i0">
                        <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                        Ожидайте договор купли-продажи, подписанный поставщиком
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
        <div class="line_area <?=$class?> with_info">
            <div class="line_inner">
                <div class="indicator"></div>
                <div class="step_num"><?=$k?></div>
                <div class="name">Оформление документов на транспортировку</div>
                <div class="clip_item"></div>
                <div class="clear l"></div>
            </div>

            <div class="line_additional no_content"></div>
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
        ?>
        <div class="line_area <?=$class?> with_info<? if ($class == 'current') { ?> active<? } ?>">
            <div class="line_inner">
                <div class="indicator"></div>
                <div class="step_num"><?=$k?></div>
                <div class="name">Внесение предоплаты</div>
                <div class="clip_item"></div>
                <div class="clear l"></div>
                <? if (in_array('prepayment_send', array_keys($arResult['LOGS']))
                    && $arResult['DOCS']['prepayment']['FILE']['SRC']
                ) { ?>
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
                            Счет на оплату по договору купли-продажи: <a href="<?=$arResult['DOCS']['prepayment']['FILE']['SRC']?>">скачать</a>
                        </div>
                    <?
                    }
                    else {
                    ?>
                        <div class="prop_area i0">
                            <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                            Ожидайте счет на оплату по договору купли-продажи
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
                /*if (sizeof($arResult['DOCS']['reestr']) > 0) {
                ?>
                    <div class="prop_area i0">
                        <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                        Реестры приемки СХП<br><br>
                        <?
                        foreach ($arResult['DOCS']['reestr'] as $file) {
                        ?>
                            Документ от <?=$file['DATE_CREATE']?>: <a href="<?=$file['FILE']['SRC']?>">скачать</a><br>
                        <?
                        }
                        ?>
                    </div>
                <?
                }*/

                if (!in_array('reestr', array_keys($arResult['LOGS']))) {
                ?>
                    <div class="prop_area i0">
                        <span class="sub_step_num"><?=$k?>.<?=$l++?></span>

                        <form action="" method="post" enctype="multipart/form-data">
                            <input type="hidden" name="loadReestr" value="Y">
                            <div class="reestr_block" style="position: relative;">
                                <div class="reestr_item">
                                    <a href="javascript:void(0);" class="loader_click" data-for="reestr1">Загрузить реестры</a>
                                    <input type="file" name="reestr[]" class="document" id="reestr1">
                                </div>
                                <div class="more-btn make_order">
                                    <input type="button" value="Еще..." class="submit-btn" style="width: 100%; height: 100%; padding: 0;">
                                </div>
                            </div>
                            <input type="submit" class="submit-btn" value="Отправить"/>
                        </form>
                    </div>
                    <?
                    if (sizeof($arResult['DOCS']['reestr']) > 0) {
                    ?>
                        <div class="prop_area i0">
                            <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                            Подтвердить, что все реестры СХП загружены<br><br>
                            <form action="" method="post">
                                <input type="hidden" name="confirm" value="Y">
                                <input type="submit" class="submit-btn" value="Подтвердить"/>
                            </form>
                        </div>
                    <?
                    }
                }

                if (sizeof($arResult['VI']) > 0) {
                ?>
                    <div class="prop_area i0">
                        <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                        <div class="vi_table_area">
                            <div class="vi-table">
                                <div class="vi-row">
                                    <div class="vi-cell">Номер машины</div>
                                    <div class="vi-cell">Нетто выгруженное, кг</div>
                                    <div class="vi-cell">Сумма, руб</div>
                                    <div class="vi-cell">Расчетная цена, руб</div>
                                    <div class="vi-cell">Качество, %</div>
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
                                </div>
                                <div class="clear"></div>
                            </div>
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
                        Счет на оплату по договору купли-продажи:
                        <a href="<?=$arResult['DOCS']['payment']['FILE']['SRC']?>">скачать</a>
                    </div>

                    <div class="prop_area i0">
                        <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                        Счет на вознаграждение по договору купли-продажи:
                        <a href="<?=$arResult['DOCS']['commission']['FILE']['SRC']?>">скачать</a>
                    </div>

                    <div class="prop_area i0">
                        <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                        Акт сдачи-приёмки услуг к договору оказания услуг: <a href="<?=$arResult['DOCS']['act_deal']['FILE']['SRC']?>" download="">скачать</a>
                    </div>
                <?
                }
                else {
                ?>
                    <div class="prop_area i0">
                        <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                        Ожидайте счета на оплату по договору купли-продажи
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