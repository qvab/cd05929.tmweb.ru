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
                    (организатор покупателя <a href="/profile/?uid=<?=$arResult['PARTNER']['USER']['ID']?>" target="_blank"><?=$arResult['PARTNER']['PROPERTY_FULL_COMPANY_NAME_VALUE']?></a>)
                </span>
            <?endif;?>
        </div>
    <?endif;?>

    <?if(!empty($arResult['FARMER']['COMPANY'])):?>
        <div class="farmer">
            <div class="item">Поставщик:</div>
            <a href="/profile/?uid=<?=$arResult['PROPERTIES']['FARMER']['VALUE']?>" target="_blank"><?=$arResult['FARMER']['COMPANY']?></a>
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

<a class="go_back cross" href="/transport/deals/"></a>

<div class="list_page_rows deals deal_detail">
    <?
    $k = $l = 1;
    $class = 'current';
    if (in_array('order_transport', array_keys($arResult['LOGS'])))
        $class = 'deal_done';
    ?>
    <div class="line_area <?=$class?> with_info<? if ($class == 'current') { ?> active<? } ?>">
        <div class="line_inner">
            <div class="indicator"></div>
            <div class="step_num"><?=$k?></div>
            <div class="name">Новая перевозка</div>
            <div class="clip_item"></div>
            <div class="clear l"></div>
        </div>

        <div class="line_additional" <? if ($class == 'current') { ?>style="display: block;"<? } ?>>
            <div class="line_additional_inner">
                <?
                if (in_array('order_transport', array_keys($arResult['LOGS']))) {
                ?>
                    <div class="prop_area i0">
                        <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                        Перевозка подтверждена
                    </div>
                <?
                }
                else {
                ?>
                    <div class="prop_area i0">
                        <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                        Ожидайте подтверждения транспортировки
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
    if (in_array($arResult['PROPERTIES']['STAGE']['VALUE_XML_ID'], array('pre_dkp', 'dkp')))
        $class = 'current';
    if (sizeof(array_intersect(array('dtr_transport', 'ds_transport_transport'), array_keys($arResult['LOGS']))) > 1)
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
            <? if ($arResult['DOCS']['dtr_partner']['FILE']['SRC']
                || $arResult['DOCS']['dtr_transport']['FILE']['SRC']
            ) { ?>
                <div class="clip"></div>
            <? } ?>
        </div>

        <div class="line_additional" <? if ($class == 'current') { ?>style="display: block;"<? } ?>>
            <div class="line_additional_inner">
                <?
                if (in_array('ds_transport_transport', array_keys($arResult['LOGS']))) {
                ?>
                    <div class="prop_area i0">
                        <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                        Доп. соглашение к договору на транспортировку подписано: <a href="<?=$arResult['DOCS']['ds_transport_transport']['FILE']['SRC']?>" download="">ДС.pdf</a>
                    </div>
                <?
                }
                elseif (in_array('ds_transport_partner', array_keys($arResult['LOGS']))) {
                ?>
                    <div class="prop_area i0">
                        <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                        Доп. соглашение к договору на транспортировку: <a href="<?=$arResult['DOCS']['ds_transport_partner']['FILE']['SRC']?>" download="">ДС.pdf</a><br><br>
                        <form action="" method="post" enctype="multipart/form-data">
                            <a href="javascript:void(0);" class="loader_click" data-for="ds_transport">Загрузить подписанное соглашение</a>
                            <input type="file" name="ds_transport" class="document" id="ds_transport">
                            <input type="submit" class="submit-btn" value="Отправить"/>
                        </form>
                    </div>
                <?
                }
                else {
                ?>
                    <div class="prop_area i0">
                        <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                        Ожидайте доп. соглашение к договору на транспортировку, подписанное организатором
                    </div>
                <?
                }

                if (in_array('dtr_transport', array_keys($arResult['LOGS']))) {
                ?>
                    <div class="prop_area i0">
                        <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                        Договор на транспортировку подписан: <a href="<?=$arResult['DOCS']['dtr_transport']['FILE']['SRC']?>" download="">Договор.pdf</a>
                    </div>
                <?
                }
                elseif (in_array('dtr_partner', array_keys($arResult['LOGS']))) {
                ?>
                    <div class="prop_area i0">
                        <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                        Договор на транспортировку: <a href="<?=$arResult['DOCS']['dtr_partner']['FILE']['SRC']?>" download="">Договор.pdf</a><br><br>
                        <form action="" method="post" enctype="multipart/form-data">
                            <a href="javascript:void(0);" class="loader_click" data-for="dtr">Загрузить подписанный договор</a>
                            <input type="file" name="dtr" class="document" id="dtr">
                            <input type="submit" class="submit-btn" value="Отправить"/>
                        </form>
                    </div>
                <?
                }
                else {
                ?>
                    <div class="prop_area i0">
                        <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                        Ожидайте договор на транспортировку, подписанный организатором
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
    if (sizeof(array_intersect(array('dtr_transport', 'ds_transport_transport'), array_keys($arResult['LOGS']))) > 1)
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
        </div>

        <div class="line_additional" <? if ($class == 'current') { ?>style="display: block;"<? } ?>>
            <div class="line_additional_inner">
                <?
                if (sizeof($arResult['VI']) > 0) {
                ?>
                    <div class="prop_area i0">
                        <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                        Ведомость исполнения:<br><br>

                        <div class="vi_table_area">
                            <div class="vi-table">
                                <div class="vi-row">
                                    <div class="vi-cell">Номер машины</div>
                                    <div class="vi-cell">Нетто выгруженное, кг</div>
                                    <div class="vi-cell">Стоимость перевозки, руб</div>
                                </div>
                                <div class="clear"></div>
                                <?
                                foreach ($arResult['VI'] as $key => $item) {
                                ?>
                                    <div class="vi-row">
                                        <div class="vi-cell"><?=$item['CAR']?></div>
                                        <div class="vi-cell"><?=number_format($item['WEIGHT'], 0, ',', ' ')?></div>
                                        <div class="vi-cell"><?=number_format($item['TRANSPORT_COST'], 2, ',', ' ')?></div>
                                    </div>
                                    <div class="clear"></div>
                                <?
                                }
                                ?>
                                <div class="vi-row" style="font-weight: bold;">
                                    <div class="vi-cell">ИТОГО</div>
                                    <div class="vi-cell"><?=number_format(1000 * $arResult['VI_SUMMARY']['WEIGHT'], 0, ',', ' ')?></div>
                                    <div class="vi-cell"><?=number_format($arResult['VI_SUMMARY']['TRANSPORT_COST'], 2, ',', ' ')?></div>
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
                        Счет на оплату по договору транспортировки отправлен: <a href="<?=$arResult['DOCS']['payment_transport']['FILE']['SRC']?>" download="">просмотреть</a>
                    </div>
                <?
                }
                else {
                ?>
                    <div class="prop_area i0">
                        <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                        Счет на оплату по договору транспортировки: <a href="<?=$arResult['DOCS']['payment_transport']['FILE']['SRC']?>" download="">просмотреть</a><br>
                        <br>
                        <form action="" method="post">
                            <input type="hidden" name="payment_transport" value="Y">
                            <input type="submit" class="submit-btn" value="Отправить"/>
                        </form>
                    </div>
                <?
                }

                if (in_array('payment_send', array_keys($arResult['LOGS']))) {
                ?>
                    <div class="prop_area i0">
                        <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                        Счет на оплату комиссии по договору транспортировки:
                        <a href="<?=$arResult['DOCS']['commission_transport']['FILE']['SRC']?>">скачать</a>
                    </div>
                <?
                }
                else {
                ?>
                    <div class="prop_area i0">
                        <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                        Ожидайте счет на оплату комиссии по договору транспортировки
                    </div>
                <?
                }

                if (in_array('payment_transport_send', array_keys($arResult['LOGS']))) {
                ?>
                    <div class="prop_area i0">
                        <span class="sub_step_num"><?=$k?>.<?=$l++?></span>
                        Акт сдачи-приемки услуг к договору на транспортировку: <a href="<?=$arResult['DOCS']['act_transport']['FILE']['SRC']?>" download="">скачать</a>
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