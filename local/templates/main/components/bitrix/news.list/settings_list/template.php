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
<div class="content">
    <?

    $arrTemp = reset($arResult['ITEMS']);
    if ($_GET['up'] == 'ok') {
    ?>
        <div class="success">Настройки успешно сохранены</div>
    <?
    }
    ?>
    <form action="" method="post" class="notice_form<?if(isset($arParams['THREE_LINE']) && $arParams['THREE_LINE'] == 'Y'){?> three_line<?}?>">
        <div class="notice_form_inner">
            <div class="notice_form_inner_scroll">
                <input type="hidden" name="id" value="<?=$arrTemp['ID']?>">
                <div class="radio_group">
                    <div class="notice-block">&nbsp;</div>
                    <?
                    foreach ($arResult['NOTICE_LIST_SOURCE'] as $source) {
                        if (in_array($source['XML_ID'], array_keys($arResult['VIEW']))) {
                        ?>
                            <div class="notice-block wide_label">
                                <?=$source['VALUE']?>
                            </div>
                        <?
                        }
                    }
                    ?>
                    <div class="clear"></div>
                    <?
                    foreach ($arResult['NOTICE_LIST_TYPE'] as $type) {
                        if (in_array($type['XML_ID'], array_keys($arResult['VIEW']))) {
                        ?>
                            <div class="notice-block" style="text-align: left;"><?=$type['VALUE']?></div>
                            <?
                            foreach ($arResult['NOTICE_LIST_SOURCE'] as $source) {
                                ?><div class="notice_wrap"><?
                                if (in_array($source['XML_ID'], array_keys($arResult['VIEW']))) {
                                    $val = $arResult['USER_NOTICE_LIST'][$source['XML_ID']][$type['XML_ID']]['ID'];
                                    $canChange = $arResult['USER_NOTICE_LIST'][$source['XML_ID']][$type['XML_ID']]['CHANGE'];
                                    ?>
                                    <div class="notice-block thin_label"><?=$source['VALUE'];?></div>
                                    <div class="radio_area notice-block<? if (!$canChange) {?> disabled<? } ?>">
                                        <input type="checkbox" name="notice[]" value="<?=$val?>" <? if (in_array($val, $arResult['INFO'])) {?>checked="checked"<? } if (!$canChange) {?>disabled="disabled"<? } ?>>
                                    </div>
                                <?
                                }
                                ?></div><?
                            }
                            ?>
                            <div class="clear"></div>
                        <?
                        }
                    }
                    ?>
                </div>

                <div class="notice_settings_area">
                    <a href="#" class="ch_all_tr">Выбрать все</a>
                    <a href="#" class="no_tr">Снять все</a>
                </div>
            </div>
        </div>
        <input type="submit" name="save" value="Сохранить" class="submit-btn">
    </form>
</div>