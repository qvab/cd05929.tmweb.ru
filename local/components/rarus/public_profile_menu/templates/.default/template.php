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
else  {
    if(count($arResult['MENU_LIST']) > 0){
    ?>
        <div class="tab_form public_profile_filter">
            <div class="tab_form_inner">
                <?if($arParams['TAB'] == ''){?>
                    <div class="item active">
                        <span>Публичный профиль</span>
                    </div>
                <?}else{?>
                    <div class="item">
                        <a href="/profile/?uid=<?=$arParams['U_ID'];?>">Публичный профиль</a>
                    </div>
                <?}?>
                <?$my_c = 1; foreach($arResult['MENU_LIST'] as $cur_code => $cur_name){
                    if($arParams['TAB'] == $cur_code){?>
                        <div class="item active <?if($my_c == count($arResult['MENU_LIST'])){?>last<?}?>">
                            <span><?=$cur_name;?></span>
                        </div>
                    <?}else{?>
                        <div class="item <?if($my_c == count($arResult['MENU_LIST'])){?>last<?}?>">
                            <a href="/profile/<?=$cur_code;?>/?uid=<?=$arParams['U_ID'];?>"><?=$cur_name;?></a>
                        </div>
                    <?}
                    $my_c++;
                }?>
                <div class="clear"></div>
            </div>
        </div>
    <?}?>
<?
}
?>