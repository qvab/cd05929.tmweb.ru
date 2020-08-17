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
if (sizeof($arResult["ITEMS"]) > 0 &&
    (count($arResult['FARMERS_DATA']) + count($arResult['CLIENTS_DATA']) > 0
        || !isset($arParams['AGENT_USER'])
        || $arParams['AGENT_USER'] != 'Y'
    )
) {
    if ($arParams['USER_TYPE'] == 'FARMER') {
        $prop = 'CLIENT';
    }
    elseif (in_array($arParams['USER_TYPE'], array('CLIENT', 'PARTNER', 'TRANSPORT'))) {
        $prop = 'FARMER';
    }
?>
    <div class="list_page_rows requests deals_list">
        <?
        foreach($arResult["ITEMS"] as $arItem) {
            $detail_allow = true;
            //убираем возможность перехода для агента, если у него нет прав для этого поставщика
            if(isset($arParams['AGENT_USER'])
                && $arParams['AGENT_USER'] == 'Y'
            ){
                $detail_allow = false;
                if(isset($arResult['FARMER_AGENT_RIGHTS'][$arItem['PROPERTIES']['FARMER']['VALUE']])
                    && $arResult['FARMER_AGENT_RIGHTS'][$arItem['PROPERTIES']['FARMER']['VALUE']]
                    || isset($arResult['CLIENT_AGENT_RIGHTS'][$arItem['PROPERTIES']['CLIENT']['VALUE']])
                    && $arResult['CLIENT_AGENT_RIGHTS'][$arItem['PROPERTIES']['CLIENT']['VALUE']] == 'Y'
                ){
                    $detail_allow = true;
                }
            }
        ?>
            <div class="line_area <?=$arItem['PROPERTIES']['STATUS']['VALUE_XML_ID']?>" id="<?=$this->GetEditAreaId($arItem['ID']);?>">
            <?if($detail_allow){?>
                <a href="<?=$arItem['DETAIL_PAGE_URL'];?>" class="line_inner">
            <?}else{?>
                <div class="line_inner">
            <?}?>
                    <div class="name"><?=current($arItem['DISPLAY_PROPERTIES']['CULTURE']['LINK_ELEMENT_VALUE'])['NAME']?></div>
                    <div class="id_date">#<?=$arItem['ID']?> от <?=reset(explode(' ', $arItem['DATE_CREATE']));?></div>
                    <div class="tons"><?=number_format($arItem["PROPERTIES"]['VOLUME']['VALUE'], 0, ',', ' ')?> т</div>
                    <div class="price"><?=number_format($arItem["PROPERTIES"]['ACC_PRICE_CSM']['VALUE'], 0, ',', ' ')?> руб/т</div>
                    <?
                    if ($arResult['USER_LIST'][$arItem['PROPERTIES'][$prop]['VALUE']] != '') {
                    ?>
                        <div class="wh_name">
                            <?=$arResult['USER_LIST'][$arItem['PROPERTIES'][$prop]['VALUE']]?>
                            <?if(isset($arParams['AGENT_USER'])
                                && $arParams['AGENT_USER'] == 'Y'
                            ){
                                if(isset($arResult['FARMERS_DATA'][$arItem['PROPERTIES']['FARMER']['VALUE']])){
                                ?><br/><br/>
                                    <?if($arResult['FARMERS_DATA'][$arItem['PROPERTIES']['FARMER']['VALUE']]['NICK'] != ''){?>
                                        <?=$arResult['FARMERS_DATA'][$arItem['PROPERTIES']['FARMER']['VALUE']]['NICK'];?>
                                    <?}elseif($arResult['FARMERS_DATA'][$arItem['PROPERTIES']['FARMER']['VALUE']]['NAME'] != ''){?>
                                        <?=$arResult['FARMERS_DATA'][$arItem['PROPERTIES']['FARMER']['VALUE']]['NAME'];?> [<?=$arResult['FARMERS_DATA'][$arItem['PROPERTIES']['FARMER']['VALUE']]['EMAIL'];?>]
                                    <?}else{?>
                                        <?=$arResult['FARMERS_DATA'][$arItem['PROPERTIES']['FARMER']['VALUE']]['EMAIL'];?>
                                    <?}
                                }else{?>
                                    <br/><br/>
                                    <?if($arResult['CLIENTS_DATA'][$arItem['PROPERTIES']['CLIENT']['VALUE']]['NICK'] != ''){?>
                                        <?=$arResult['CLIENTS_DATA'][$arItem['PROPERTIES']['CLIENT']['VALUE']]['NICK'];?>
                                    <?}elseif($arResult['CLIENTS_DATA'][$arItem['PROPERTIES']['CLIENT']['VALUE']]['NAME'] != ''){?>
                                        <?=$arResult['CLIENTS_DATA'][$arItem['PROPERTIES']['CLIENT']['VALUE']]['NAME'];?> [<?=$arResult['CLIENTS_DATA'][$arItem['PROPERTIES']['CLIENT']['VALUE']]['EMAIL'];?>]
                                    <?}else{?>
                                        <?=$arResult['CLIENTS_DATA'][$arItem['PROPERTIES']['CLIENT']['VALUE']]['EMAIL'];?>
                                    <?}
                                }?>
                            <?}?>
                        </div>
                    <?
                    }
                    ?>
                    <div class="clear"></div>
            <?if($detail_allow){?>
                </a>
            <?}else{?>
                </div>
            <?}?>
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
elseif(isset($arParams['AGENT_USER'])
    && $arParams['AGENT_USER'] == 'Y'
    && (count($arResult['FARMERS_DATA']) + count($arResult['CLIENTS_DATA'])) == 0
) {
?>
    <div class="list_page_rows requests no-item">
        К вам не привязан ни один поставщик
    </div>
<?
}
else {
?>
    <div class="list_page_rows requests no-item">
        Ни одной сделки не найдено
    </div>
<?
}
?>