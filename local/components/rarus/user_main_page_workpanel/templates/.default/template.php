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
    if ($arResult['MESS_STR'] != '') {
        echo '<div class="err_text row">' . $arResult['MESS_STR'] . '</div>';
    }
    if (count($arResult['CHECK_DATA']) > 0) {
    ?>
        <div class="block_area important_notices_area">
            <div class="block_head">Важное</div>
            <div class="block_data">
                <div class="control_area">
                    <?
                    foreach ($arResult['CHECK_DATA'] as $cur_code => $cur_data) {
                        if (isset($arResult['HREFS'][$cur_code])) {
                        ?>
                            <a href="<?=$arResult['HREFS'][$cur_code];?>" class="item">
                                <div class="name_area"><?=GetMessage($GLOBALS['rrs_user_perm_level'] . '_' . $cur_code . '_' . $cur_data);?></div>
                                <div class="line_elem <?=strtolower($cur_code)?>"></div>
                            </a>
                        <?
                        }
                    }
                    ?>
                    <div class="clear"></div>
                </div>
            </div>
        </div>
    <?}
}