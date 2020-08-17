<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/**
 * @author: Vitaly Melnik <vitali@rarus.ru>
 */

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
CJSCore::Init(array('date'));
?>

<div class="wrap-affairs" offer-id="<?=$arParams['OFFER_ID']?>" cnt="<?=$arResult['DATA']['CNT']?>" limit="<?=$arParams['LIMIT']?>">

    <div class="error-msg <?=empty($arResult['ERROR_MSG']) ? 'hidden':'';?>">
        <?ShowError('Ошибка! ' . $arResult['ERROR_MSG'])?>
    </div>

    <div class="items-affairs">
        <div class="wrap-items">
            <?if(empty($arResult['DATA']['ITEMS'])):?>
                <div class="no_affairs">Нет ближайших дел</div>
            <?else:?>
                <?foreach ($arResult['DATA']['ITEMS'] as $arItem) {
                    echo getItemHtmlRow($arItem['UF_DATE_AFFAIR'], $arItem['UF_FARMER_VOLUME'], $arItem['UF_EXPECTED_PRICE'], $arItem['UF_COMMENT']);
                }?>
            <?endif;?>
        </div>
        <div class="show-more <?=($arResult['DATA']['CNT'] > 1) ? '': 'hidden';?>">Показать еще</div>
    </div>


    <div class="form-add-new-affair hidden">

        <div class="fields">

            <div class="field row date-affair">
                <div class="row_head">Дата следующего действия</div>
                <div class="row_val">
                    <input
                            type="text"
                            name="DATE_AFFAIR"
                            onclick="BX.calendar({node:this, field:this, form: '', bTime: false, bHideTime: false});"
                    />
                    <div class="row_err">Пожалуйста, выберите дату следующего действия</div>
                </div>
            </div>

            <div class="field row farmer-volume">
                <div class="row_head">Объем в наличии у поставщика (тонн)</div>
                <div class="row_val">
                    <input type="text" name="FARMER_VOLUME">
                    <div class="row_err">Пожалуйста, укажите объем продукции</div>
                </div>
            </div>

            <div class="field row expected-price">
                <div class="row_head">Ожидаемая цена (руб/тн)</div>
                <div class="row_val">
                    <input type="text" name="EXPECTED_PRICE">
                    <div class="row_err">Пожалуйста, укажите ожидаемую цену</div>
                </div>
            </div>

            <div class="field row comment">
                <div class="row_head">Комментарии для следующего звонка</div>
                <div class="row_val">
                    <textarea></textarea>
                </div>
            </div>

            <div class="field row">
                <div class="row_head"></div>
                <div class="row_val">
                    <span class="submit-btn add-new-affair">Сохранить дело</span>
                </div>
            </div>
        </div>
    </div>

    <div class="btn-show-form-add-new">Добавить дело</div>
</div>