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


$this->addExternalJS(SITE_TEMPLATE_PATH . '/library/jquery.validation/dist/jquery.validate.min.js');

?>
<div class="wrap-freight-tariff-update">

    <div class="title">
        <h1>Обновление тарифов на перевозку</h1>
    </div>

    <!--Сообщение об ошибке-->
    <?if(!empty($arResult['ERROR_MSG'])):?>
        <div class="error-msg">
            <?ShowError('Ошибка! ' . $arResult['ERROR_MSG'])?>
        </div>
    <?endif;?>


    <form method="POST" id="all_tariff">

        <?=bitrix_sessid_post()?>
        <input type="hidden" name="SAVE_ALL_TARIFF" value="Y">

        <div class="tariff-list">

            <div class="tariff-item row title">
                <div class="title">Расстояние от</div>
                <div class="title">Расстояние до</div>
                <div class="title">Кол-во дней в рейсе</div>
                <div class="title">Тарифная ставка</div>
                <div class="clear"></div>
            </div>

            <!--Текущие тарифы-->
            <div class="current-tariffs">
                <?foreach ($arResult['TARIFF_LIST'] as $arTariff):?>
                    <?=CFreightTariffUpdate::getHtmlRowTariff($arTariff['KM_FROM'], $arTariff['KM_TO'], $arTariff['DAYS'], $arTariff['TARIFF'])?>
                <?endforeach;?>
            </div>

            <!--Новые тарифы-->
            <div class="new-tariffs"></div>

        </div>

        <div class="tariff-block-btn">
            <input class="submit-btn left add-row" value="Добавить тариф" type="button">
            <input class="submit-btn right all-save" value="Сохранить все изменения" type="button">
            <div class="clear"></div>
        </div>

    </form>

</div>




<script>
    var iNewRowTariff = 0;
    // Отдает строчку нового тарифа
    var getRowTariff = function(iNewRowTariff) {
        return '<div class="tariff-item row" row="'+iNewRowTariff+'">'+
                    '<div class="left">' +
                        '<input type="text" autocomplete="off" name="NEW_KM_FROM['+iNewRowTariff+']" value="" placeholder="Расстояние от" required="required" title="Поле обязательно к заполнению"  class="km-from">' +
                    '</div>' +
                    '<div class="left">' +
                        '<input type="text" autocomplete="off" name="NEW_KM_TO['+iNewRowTariff+']" value="" placeholder="Расстояние до" required="required" title="Поле обязательно к заполнению"  class="km-to">' +
                    '</div>' +
                    '<div class="left">' +
                        '<input type="text" autocomplete="off" name="NEW_DAYS['+iNewRowTariff+']" value="" placeholder="Кол-во дней в рейсе" required="required" title="Поле обязательно к заполнению"  class="days">' +
                    '</div>' +
                    '<div class="left">' +
                        '<input type="text" autocomplete="off" name="NEW_TARIFF_AU['+iNewRowTariff+']" value="" placeholder="Тарифная ставка" required="required" title="Поле обязательно к заполнению"  class="tariff">' +
                    '</div>' +
                    '<div class="left">' +
                        '<input class="submit-btn add-tariff" value="Добавить тариф" type="button">' +
                    '</div>' +
                    '<div class="left">' +
                        '<input type="button" class="btn-delete-tariff" row="'+iNewRowTariff+'" value="-" title="Удалить тариф">' +
                    '</div>' +
                    '<div class="clear"></div>' +
                '</div>';
    }
</script>