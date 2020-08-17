<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
use Bitrix\Main\Web\Uri;

$uri = new Uri(str_replace('&amp;', '&', $arResult['sUrlPathParams']));
$uri->deleteParams(array('id', 'success'));
$arResult['NavQueryString'] = $uri->getQuery();
?>