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
if (!isset($_REQUEST['status']) || (!in_array($_REQUEST['status'], array('yes', 'no', 'all')))) {
    $_REQUEST['status'] = 'yes';
}
?>
<div class="tab_form">
    <?
    if (in_array('yes', $arParams['STATUS_LIST'])) {
        if ($_REQUEST['status'] == 'yes') { ?>
            <div class="item active"><span>Активные (<?=$arResult['Q']['yes']?>)</span></div>
        <? } else { ?>
            <div class="item"><a href="<?=$arParams['LIST_URL']?>">Активные (<?=$arResult['Q']['yes']?>)</a></div>
        <? }
    }

    if (in_array('no', $arParams['STATUS_LIST'])) {
        if ($_REQUEST['status'] == 'no') { ?>
            <div class="item active"><span>Неактивные (<?=$arResult['Q']['no']?>)</span></div>
        <? } else { ?>
            <div class="item"><a href="<?=$arParams['LIST_URL']?>?status=no">Неактивные (<?=$arResult['Q']['no']?>)</a></div>
        <? }
    }

    if (in_array('all', $arParams['STATUS_LIST'])) {
        if ($_REQUEST['status'] == 'all') { ?>
            <div class="item active last"><span>Все (<?=$arResult['Q']['all']?>)</span></div>
        <? } else { ?>
            <div class="item last"><a href="<?=$arParams['LIST_URL']?>?status=all">Все (<?=$arResult['Q']['all']?>)</a></div>
        <? }
    }
    ?>
    <div class="clear"></div>
</div>

<?
$filterName = $arParams['FILTER_NAME'];
$GLOBALS[$filterName] = array();
$GLOBALS[$filterName]['PROPERTY_CLIENT'] = $USER->GetID();
if (in_array($_REQUEST['status'], array('yes', 'no'))) {
    $GLOBALS[$filterName]['PROPERTY_ACTIVE'] = rrsIblock::getPropListKey('client_warehouse', 'ACTIVE', $_REQUEST['status']);
}
?>