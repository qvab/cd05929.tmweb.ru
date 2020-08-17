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
$this->addExternalJS(SITE_TEMPLATE_PATH . '/library/jquery.validation/dist/jquery.validate.min.js');
?>
<div class="content">
    <a href="/admin/tariff/?renew=y" class="renew">Обновить тарифы региональных центров</a>
    <div class="wrap-freight-tariff-update">
        <form action="" method="post" id="all_tariff">
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
                    <?
                    foreach ($arResult['ITEMS'] as $arItem) {
                    ?>
                        <div class="tariff-item row" tariffId="<?=$arItem['ID']?>">
                            <div class="left">
                                <input
                                    type="text"
                                    autocomplete="off"
                                    name="tariff[<?=$arItem['ID']?>][from]"
                                    value="<?=$arItem['PROPERTIES']['KM_FROM']['VALUE']?>"
                                    placeholder="Расстояние от"
                                    required="required"
                                    title="Поле обязательно к заполнению"
                                    class="km-from"
                                    />
                            </div>

                            <div class="left">
                                <input
                                    type="text"
                                    autocomplete="off"
                                    name="tariff[<?=$arItem['ID']?>][to]"
                                    value="<?=$arItem['PROPERTIES']['KM_TO']['VALUE']?>"
                                    placeholder="Расстояние до"
                                    required="required"
                                    title="Поле обязательно к заполнению"
                                    class="km-to"
                                    />
                            </div>
                            <div class="left">
                                <input
                                    type="text"
                                    autocomplete="off"
                                    name="tariff[<?=$arItem['ID']?>][days]"
                                    value="<?=$arItem['PROPERTIES']['DAYS']['VALUE']?>"
                                    placeholder="Кол-во дней в рейсе"
                                    required="required"
                                    title="Поле обязательно к заполнению"
                                    class="days"
                                    />
                            </div>

                            <div class="left">
                                <input
                                    type="text"
                                    autocomplete="off"
                                    name="tariff[<?=$arItem['ID']?>][val]"
                                    value="<?=$arItem['PROPERTIES']['TARIF_AH']['VALUE']?>"
                                    placeholder="Тарифная ставка"
                                    required="required"
                                    title="Поле обязательно к заполнению"
                                    class="tariff"
                                    />
                            </div>

                            <div class="left">
                                <input type="button" class="btn-delete-tariff" value="-" title="Удалить тариф">
                            </div>

                            <div class="clear"></div>
                        </div>
                    <?
                    }
                    ?>
                </div>

                <!--Новые тарифы-->
                <div class="new-tariffs"></div>
            </div>

            <div class="tariff-block-btn">
                <input type="button" class="submit-btn left add-row" value="Добавить тариф">
                <input type="submit" class="submit-btn right all-save" value="Сохранить все изменения">
                <div class="clear"></div>
            </div>
        </form>
    </div>
</div>

<script>
    var iNewRowTariff = 0;
    // Отдает строчку нового тарифа
    var getRowTariff = function(iNewRowTariff) {
        return '<div class="tariff-item row" row="'+iNewRowTariff+'">'+
            '<div class="left">' +
            '<input type="text" autocomplete="off" name="new_tariff['+iNewRowTariff+'][from]" value="" placeholder="Расстояние от" required="required" title="Поле обязательно к заполнению"  class="km-from">' +
            '</div>' +
            '<div class="left">' +
            '<input type="text" autocomplete="off" name="new_tariff['+iNewRowTariff+'][to]" value="" placeholder="Расстояние до" required="required" title="Поле обязательно к заполнению"  class="km-to">' +
            '</div>' +
            '<div class="left">' +
            '<input type="text" autocomplete="off" name="new_tariff['+iNewRowTariff+'][days]" value="" placeholder="Кол-во дней в рейсе" required="required" title="Поле обязательно к заполнению"  class="days">' +
            '</div>' +
            '<div class="left">' +
            '<input type="text" autocomplete="off" name="new_tariff['+iNewRowTariff+'][val]" value="" placeholder="Тарифная ставка" required="required" title="Поле обязательно к заполнению"  class="tariff">' +
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