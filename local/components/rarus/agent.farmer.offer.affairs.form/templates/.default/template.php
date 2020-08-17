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

<?if(!empty($arResult['OFFERS'])):?>
<form class="add-form">
<div class="wrap-affairs" data-uid = "<?=$arParams['FARMER_ID'];?>">

    <div class="error-msg <?=empty($arResult['ERROR_MSG']) ? 'hidden':'';?>">
        <?ShowError('Ошибка! ' . $arResult['ERROR_MSG'])?>
    </div>


    <div class="form-add-new-affair " >

        <div class="fields">
            <div class="field row date-offer">
                <div class="row_head">Товар</div>
                <div class="row_val">
                <?$sDataSearch = (count($arResult['OFFERS']) > 3) ? 'data-search="y"' : null;?>
                <div class="wrap-select">
                    <select <?=$sDataSearch?> name="FARMER_OFFER" placeholder="Выберите товар">
                        <?foreach ($arResult['OFFERS'] as $iOfferID => $arOffer):?>
                            <?$sSelected = ($_GET['offer'] == $arOffer['ID']) ? 'selected="selected"' : null?>
                            <option <?=$sSelected?> value="<?=$arOffer['ID']?>"><?=$arOffer['CULTURE_NAME']?> - <?=$iOfferID;?></option>
                        <?endforeach;?>
                    </select>
                </div>
                    <div class="row_err">Пожалуйста, выберите необходимы товар</div>
                </div>
            </div>
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
</div>
</form>
<?else:?>
    <div class="add-form color-red">У поставщика нет товаров.</div>
<?endif;?>
