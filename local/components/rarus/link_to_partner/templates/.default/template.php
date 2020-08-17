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
if ($arResult['ERROR'] == 'Y') {
    echo '<div class="err_text row">' . $arResult['ERROR_MESSAGE'] . '</div>';
}
else {
    if (is_array($arResult['CURRENT_PARTNER_DATA']) && $arResult['CURRENT_PARTNER_DATA']['ID'] > 0) {
    ?>
        <div class="current_partner_link_area">
            <div class="title">Вы привязаны к организатору:</div>
            <div class="list_page_rows requests">
                <div class="line_area">
                    <div class="line_inner item">
                        <div class="partner_val"><?=implode(' ', array($arResult['CURRENT_PARTNER_DATA']['LAST_NAME'], $arResult['CURRENT_PARTNER_DATA']['NAME'], $arResult['CURRENT_PARTNER_DATA']['SECOND_NAME'], '[' . $arResult['CURRENT_PARTNER_DATA']['LOGIN'] . ']'));?></div>
                        <div title="Отвязаться от организатора" class="unlink_but<?if(isset($arResult['UNCOMPLETE_DEALS']) && $arResult['UNCOMPLETE_DEALS'] == 'y'){?> disabled<?}?>"></div>
                        <div class="clear"></div>
                    </div>
                    <div class="line_additional">
                        <div class="prop_area">
                            <a href="/profile/?uid=<?=$arResult['CURRENT_PARTNER_DATA']['ID'];?>">Перейти в профиль пользователя</a>
                            <div class="sub_row"></div>
                            <?
                            /*if(!isset($arResult['CUR_PARTNER_DATA']['VERIFIED'])
                                || $arResult['CUR_PARTNER_DATA']['VERIFIED'] != rrsIblock::getPropListKey('farmer_profile', 'VERIFIED', 'yes')
                            ) {
                            ?>
                                <div class="row_sub_head">Нет подтверждения прикрепления к организатору</div>
                            <?
                            }else{?>

                            <?}*/?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?
    }
    ?>
    <div class="partner_list_area">
        <?
        if (!is_numeric($arParams['REGION_ID'])) {
            echo '<div class="row mess_txt">Выберите регион для отображения организаторов.</div>';
        }
        elseif (count($arResult['PARTNERS_LIST']) > 0) {
        ?>
            <div class="partner_list_area">
                <div class="title">Список других организаторов системы в регионе:</div>
                <div class="list_page_rows requests partner_other_list">
                    <?
                    foreach ($arResult['PARTNERS_LIST'] as $cur_val) {
                    ?>
                        <div class="line_area">
                            <div title="Чтобы прикрепиться к организатору вам нужно открепиться от вашего текущего организатора" class="line_inner item">
                                <div class="name"><?=implode(' ', array($cur_val['LAST_NAME'], $cur_val['NAME'], $cur_val['SECOND_NAME'], '[' . $cur_val['LOGIN'] . ']'));?></div>
                                <?
                                if (!isset($arResult['CURRENT_PARTNER_DATA'])) {
                                ?>
                                    <div data-id="<?=$cur_val['ID'];?>" class="link_but"></div>
                                <?
                                }
                                ?>
                                <div class="clear"></div>
                            </div>
                        </div>
                    <?
                    }
                ?>
            </div>
        <?
        }
        else {
        ?>
            <div class="row mess_txt">Нет организаторов для отображения в выбранном регионе.</div>
        <?
        }
        ?>
    </div>
<?
}
?>