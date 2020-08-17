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
if (sizeof($arResult["ITEMS"]) > 0) {
?>
    <div class="connected_users_list">
        <div class="list_page_rows">
        <?
        foreach($arResult["ITEMS"] as $arItem) {
        ?>
            <div class="line_area blacklist">
                <div class="line_inner">
                    <div class="inner_text">
                        <span class="email_val">
                            <?
                            if(!empty($arItem['FARMER_NAME'])){
                                echo $arItem['FARMER_NAME'].", ";
                            }
                            ?><?=$arItem['FARMER_LOGIN']?><br/>
                        </span>
                    </div>
                    <div title="<?=GetMessage("DELETE_FROM_BL")?>" data-uid="<?=$arItem['ELEMENT_ID']?>" class="unlink_but"></div>
                    <div class="clear"></div>
                </div>
            </div>
        <?
        }
        ?>
        </div>
    </div>
        <?if($arParams["DISPLAY_BOTTOM_PAGER"]):?>
            <br /><?=$arResult["NAV_STRING"]?>
        <?endif;?>
<?
}
$templateData = $arResult;
?>