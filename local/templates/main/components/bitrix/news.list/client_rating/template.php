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

$assetInst = \Bitrix\Main\Page\Asset::getInstance();
$assetInst->addCss(SITE_TEMPLATE_PATH . '/css/jquery.rating.css');
$assetInst->addJs(SITE_TEMPLATE_PATH . '/js/jquery.rating-2.0.js');

if (!is_numeric($arResult['RATING']['RATE'])) $class = 'grey';
elseif ($arResult['RATING']['RATE'] > 7) $class = 'green';
elseif ($arResult['RATING']['RATE'] > 4) $class = 'yellow';
else $class = 'red';

?>

<div class="content">
    <div class="prop_area adress_val">
        <div class="rate_total">Рейтинг покупателя <div class="rate_val <?//=$class?>"><input type="hidden" name="val" value="<?=$arResult['RATING']['RATE'] / 2?>" /></div></div>
        <div class="client_rating">
            <?
            if (!is_numeric($arResult['RATING']['REC'])) $class = 'grey';
            elseif ($arResult['RATING']['REC'] > 7) $class = 'green';
            elseif ($arResult['RATING']['REC'] > 4) $class = 'yellow';
            else $class = 'red';
            ?>
            <div class="rate_item i1">
                <div class="rate_title">Своевременность приемки продукции</div>
                <div class="rate_val <?=$class?>"><?=sprintf('%.1f', $arResult['RATING']['REC']);?></div>
            </div>

            <?
            if (!is_numeric($arResult['RATING']['LAB'])) $class = 'grey';
            elseif ($arResult['RATING']['LAB'] > 7) $class = 'green';
            elseif ($arResult['RATING']['LAB'] > 4) $class = 'yellow';
            else $class = 'red';
            ?>
            <div class="rate_item i2">
                <div class="rate_title">Оценка качества продукции</div>
                <div class="rate_val <?=$class?>"><?=sprintf('%.1f', $arResult['RATING']['LAB']);?></div>
            </div>

            <?
            if (!is_numeric($arResult['RATING']['PAY'])) $class = 'grey';
            elseif ($arResult['RATING']['PAY'] > 7) $class = 'green';
            elseif ($arResult['RATING']['PAY'] > 4) $class = 'yellow';
            else $class = 'red';
            ?>
            <div class="rate_item i3">
                <div class="rate_title">Своевременность оплаты</div>
                <div class="rate_val <?=$class?>"><?=sprintf('%.1f', $arResult['RATING']['PAY']);?></div>
            </div>
        </div>
        <div class="clear"></div>
    </div>
</div>