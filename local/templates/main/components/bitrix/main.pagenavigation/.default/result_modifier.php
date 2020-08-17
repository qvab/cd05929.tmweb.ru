<?php
/**
 * Created by PhpStorm.
 * User: dmitrd
 * Date: 13.11.2019
 * Time: 10:27
 */


global $APPLICATION;
//убираем из пагинации значения конкретных элементов (обрабатываются только целочисленные параметры)
$replace_params = array(
    'o',
    'r',
);

//за общий url берем текущую страницу без удаляемых парамтеров и без параметра постраничной навигации
$arResult['URL'] = $APPLICATION->GetCurPageParam(false, array_merge($replace_params, array($arParams['NAV_OBJECT']->getId())));

//удаляем указанные в $replace_params параметры
foreach($replace_params as $cur_param){
    $arResult['URL_TEMPLATE'] = preg_replace('/\?' . $cur_param . '=[0-9]+(\&)/s', '$1', $arResult['URL_TEMPLATE']);
    $arResult['URL_TEMPLATE'] = preg_replace('/\&' . $cur_param . '=[0-9]+(\&)/s', '$1', $arResult['URL_TEMPLATE']);
}