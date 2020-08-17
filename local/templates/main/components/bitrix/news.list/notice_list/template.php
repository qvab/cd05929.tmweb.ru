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
if (sizeof($arResult["ITEMS"]) > 0) {
?>
    <div class="list_page_rows requests notifications">
        <?
        foreach($arResult["ITEMS"] as $arItem) {
        ?>
            <div class="line_area<? if ($arItem['PROPERTIES']['READ']['VALUE'] == 'Y') {?> cancel<? } ?>" id="<?=$this->GetEditAreaId($arItem['ID']);?>">
                <a href="<?=$arItem['PROPERTIES']['LINK_HREF']['VALUE']?>" class="line_inner">
                    <div class="name"><?=str_replace('&amp;quot;', '"', $arItem['NAME']);?></div>
                    <div class="tons"><?=$arItem['PROPERTIES']['LINK_NAME']['VALUE']?></div>
                    <div class="price" style="width: 150px;"><?=$arItem['DATE_CREATE']?></div>
                    <?php
                    if((isset($arItem['PROPERTIES']['SEND_USER']['VALUE'])&&$arItem['PROPERTIES']['SEND_USER']['VALUE'])&&
                        (isset($arItem['PROPERTIES']['PAIR_ID']['VALUE'])&&$arItem['PROPERTIES']['PAIR_ID']['VALUE'])){
                        if(farmer::isFarmerPartner($arItem['PROPERTIES']['SEND_USER']['VALUE'],$USER->GetID())){
                            $farmerEmail = rrsIblock::getEmail($arItem['PROPERTIES']['SEND_USER']['VALUE']);
                            ?><div class="accept"><button class="submit-btn" value="Отправить поставщику" data-pair-id="<?=$arItem['PROPERTIES']['PAIR_ID']['VALUE'];?>" data-email="<?=$farmerEmail?>" data-uid="<?=$arItem['PROPERTIES']['SEND_USER']['VALUE']?>">Отправить<br/>поставщику</button></div><?
                        }
                    }
                    ?>
                    <div class="arw_icon"></div>
                    <div class="clear"></div>
                </a>

            </div>
        <?
        }
        ?>

        <?if($arParams["DISPLAY_BOTTOM_PAGER"]):?>
            <br /><?=$arResult["NAV_STRING"]?>
        <?endif;?>
    </div>
<?
}
else {
?>
    <div class="list_page_rows requests no-item">
        На данный момент оповещений нет.
    </div>
<?
}

$templateData = $arResult;
?>