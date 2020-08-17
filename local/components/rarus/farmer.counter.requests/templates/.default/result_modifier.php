<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();?>
<?
$arResult['DOCS_LIST'] = rrsIblock::getElementList(rrsIblock::getIBlockId('need_docs'));
$arResult['TRANSPORT_LIST'] = rrsIblock::getElementList(rrsIblock::getIBlockId('transport_type'));

$arResult['RECOMMEND_MESSAGE'] = deal::getRecommendedPriceText($arResult['OFFER_ID']);


?>