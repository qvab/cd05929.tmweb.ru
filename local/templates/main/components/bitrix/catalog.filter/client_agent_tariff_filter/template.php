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
<?if($arResult['SHOW_FORM']):?>
<form class="client_change" name="client_agent_tariff" action="<?=$arParams['LIST_URL']?>" method="get">
    <div class="fblock_fm row" style="margin-bottom: 24px;">
        <div class="row_val">
            <select <? if(sizeof($arResult['CLIENT_LIST']) > 3) { ?>data-search="y"<? } ?> name="client" placeholder="Выберите покупателя">
                <option value="0">Все покупатели</option>
                <?
                foreach ($arResult['CLIENT_LIST'] as $key => $arClient) {
                    $active = false;
                    if ($_GET['client'] == $key)
                        $active = true;

                    if ($arClient['NICK'] != '') {
                    ?>
                        <option data-right="<?=$user_right?>" value="<?=$key;?>" <? if ($active) { ?>selected="selected"<? } ?> ><?=$arClient['NICK'];?></option>
                    <?
                    }
                    elseif ($arClient['NAME'] == '') {
                    ?>
                        <option data-right="<?=$user_right?>" value="<?=$key;?>" <? if ($active) { ?>selected="selected"<? } ?> ><?=$arClient['EMAIL'];?></option>
                    <?
                    }
                    else {
                    ?>
                        <option data-right="<?=$user_right?>" value="<?=$key;?>" <? if ($active) { ?>selected="selected"<? } ?> ><?=$arClient['NAME'];?> (<?=$arClient['EMAIL'];?>)</option>
                    <?
                    }
                }
                ?>
            </select>
        </div>
    </div>
</form>
<?endif;?>
<?
$filterName = $arParams['FILTER_NAME'];
$GLOBALS[$filterName] = array();
$GLOBALS[$filterName]['PROPERTY_USER'] = $_GET['client'];

$GLOBALS['client_tariff_error'] = '';
if ((!isset($_GET['client']) || intval($_GET['client']) < 1) && count($arResult['CLIENT_LIST'])>0) {
    $GLOBALS['client_tariff_error'] = 'Выберите покупателя!';
}elseif(count($arResult['CLIENT_LIST'])==0){
    $GLOBALS['client_tariff_error'] = 'К вам не привязан ни один покупатель';
}
elseif (!in_array($_GET['client'], array_keys($arResult['CLIENT_LIST']))) {
    $GLOBALS['client_tariff_error'] = 'Ошибка! Нет доступа к данным пользователя';
}
?>