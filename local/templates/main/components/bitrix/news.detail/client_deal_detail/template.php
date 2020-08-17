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
                    (организатор покупателя <a href="/profile/?uid=<?=$arResult['PROPERTIES']['PARTNER']['VALUE']?>" target="_blank"><?=$arResult['PARTNER']['PARTNER_NAME']?></a>)
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
                        ?>организатор поставщика <a href="/profile/?uid=<?=$arResult['FARMER_PARTNER']['USER']['ID'] ?>" target="_blank"><?= $arResult['FARMER_PARTNER']['PARTNER_NAME']; ?></a><?
                    }
                    ?>)
                </span>
            <?endif;?>
        </div>
    <?endif;?>
</div>

<a class="go_back cross" href="/partner/deals/"></a>

<div class="deal_detail">
    <div class="line_area">
        <div class="line_block">
            <div class="title">Статус</div>
            <div class="text"><?=$arResult['STATUS_MESSAGE']?></div>
        </div>
        <div class="clear"></div>
    </div>
    <div class="clear"></div>

    <div class="line_area">
        <div class="line_block">
            <div class="title">Поставщик</div>
            <div class="text">
                <a href="/profile/?uid=<?=$arResult['PROPERTIES']['FARMER']['VALUE']?>">
                    <?=$arResult['FARMER']['PROPERTY_FULL_COMPANY_NAME_VALUE']?>
                </a>
            </div>
            <div class="text"><b>Адрес отгрузки:</b> <?=$arResult['FARMER_WAREHOUSE']['ADDRESS']?></div>
        </div>
        <div class="line_block">
            <div class="title">Покупатель</div>
            <div class="text">
                <a href="/profile/?uid=<?=$arResult['PROPERTIES']['CLIENT']['VALUE']?>">
                    <?=$arResult['CLIENT']['PROPERTY_FULL_COMPANY_NAME_VALUE']?>
                </a>
            </div>
            <div class="text"><b>Адрес доставки:</b> <?=$arResult['CLIENT_WAREHOUSE']['ADDRESS']?></div>
        </div>
        <div class="clear"></div>
    </div>
    <div class="clear"></div>

    <?
    //стоимость товара по сделке (руб) = т * руб/т
    $cost = $arResult['PROPERTIES']['VOLUME']['VALUE'] * $arResult['PROPERTIES']['BASE_PRICE']['VALUE'];
    //$commission = 0.01 * $arResult['COMMISSION'] * $cost;
    $commission = $arResult['COMMISSION'] * $cost / (100 - $arResult['COMMISSION']);
    ?>
    <div class="line_area">
        <div class="line_block">
            <div class="title">Условия</div>
            <div class="text"><b>Количество:</b> <?=$arResult['PROPERTIES']['VOLUME']['VALUE']?> т</div>
            <div class="text"><b>Базисная цена:</b> <?=number_format($arResult['PROPERTIES']['BASE_PRICE']['VALUE'], 2, '.', ' ')?> руб/т</div>
            <div class="text"><b>Стоимость:</b> <?=number_format($cost, 0, '.', ' ')?> руб</div>
            <div class="text">
                <b>Комиссия организатора<br>
                    <a href="/profile/?uid=<?=$arResult['PROPERTIES']['PARTNER']['VALUE']?>">
                        <?=$arResult['PARTNER']['PROPERTY_FULL_COMPANY_NAME_VALUE']?>
                    </a>:
                </b> <?=$arResult['COMMISSION']?>% (<?=number_format($commission, 2, '.', ' ')?> руб)</div>
        </div>
        <div class="line_block">
            <div class="title">Требования к товару</div>
            <div class="text">
                <b><?=$arResult['DISPLAY_PROPERTIES']['CULTURE']['NAME'];?>:</b>
                <?=current($arResult['DISPLAY_PROPERTIES']['CULTURE']['LINK_ELEMENT_VALUE'])['NAME']?>
            </div>
            <?
            foreach ($arResult['PARAMS_INFO'] as $param) {
                $info = $arResult['REQUEST_PARAMS'][$param['QUALITY_ID']];
                ?>
                <div class="text">
                    <b><?=$param['QUALITY_NAME']?>:</b>
                    <?
                    if ($info['LBASE_NAME']) {
                        echo $info['LBASE_NAME'];
                    }
                    else {
                        echo $info['BASE'] . ' ' . $arResult['UNIT_INFO'][$info['QUALITY_ID']];
                    }
                    ?>
                </div>
            <?
            }
            ?>
        </div>
        <div class="clear"></div>
    </div>
    <div class="clear"></div>
</div>
<?
$templateData = $arResult;
?>
