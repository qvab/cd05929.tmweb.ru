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
$filterName = $arParams['FILTER_NAME'];
$GLOBALS[$filterName] = array();
$GLOBALS[$filterName]['PROPERTY_USER'] = $USER->GetID();
$read = false;
if(isset($_GET['read'])){
    if($_GET['read'] == 1){
        $read = true;
    }
}
if($read===true){
    $GLOBALS[$filterName]['PROPERTY_READ'] = 'Y';
    $GLOBALS['NOTICE_COUNT'] = 10;
}else{
    $GLOBALS[$filterName]['PROPERTY_READ'] = 'N';
    $GLOBALS['NOTICE_COUNT'] = 1000;
}
?>