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

<div class="prop_area adress_val dashboard_data">

    <h1 class="page_header">Активность пользователей</h1>

    <?if(count($arResult['PARTNERS']) > 0){?>
        <form action="" method="get" name="agent_select">
            <div class="list_page_rows agents_list">

                <?/*<div class="wrap-select">
                    <select name="agent_id" <?if(count($arResult['AGENTS']) > 4){?>data-search="y"<?}?> >
                        <option value="0">Все</option>
                        <option value="1" <?if(isset($arParams['AGENT_ID']) && $arParams['AGENT_ID'] == 1){?>selected="selected"<?}?> >Все агенты</option>
                        <option value="2" <?if(isset($arParams['AGENT_ID']) && $arParams['AGENT_ID'] == 2){?>selected="selected"<?}?>>Без агента</option>
                        <?foreach($arResult['AGENTS'] as $cur_id => $cur_data){?>
                            <option <?if(isset($arParams['AGENT_ID']) && $arParams['AGENT_ID'] == $cur_id){?>selected="selected"<?}?> value="<?=$cur_id?>"><?=$cur_data;?></option>
                        <?}?>
                    </select>
                </div>

                <div class="wrap-select">*/?>
                    <select name="partner_id" <?if(count($arResult['PARTNERS']) > 4){?>data-search="y"<?}?> >
                        <option value="0">Все организаторы</option>
                        <?foreach($arResult['PARTNERS'] as $cur_id => $cur_data){?>
                            <option <?if(isset($arParams['PARTNER_ID']) && $arParams['PARTNER_ID'] == $cur_id){?>selected="selected"<?}?> value="<?=$cur_id?>"><?=$cur_data;?></option>
                        <?}?>
                    </select>
                <?/*</div>*/?>
            </div>
            <div class="clear"></div>
            <div class="row fbtn_submit">
                <input class="submit-btn left" value="Применить" type="submit" />
            </div>
            <div class="row fbtn_cancel">
                <a href="/regional_managers/dashboard/" class="cancel_filter">Сбросить</a>
            </div>
            <div class="clear"></div>
        </form>
    <?}?>

    <table>
        <tr class="legend">
            <th class="label"></th>
            <th>Сегодня</th>
            <th>+/- вчера</th>
            <th>+/- неделя</th>
        </tr>

        <tr><td colspan="4"><div class="dash_line"></div></td></tr>

        <tr class="label_line">
            <td class="label">Количество поставщиков</td>
            <td><a href="<?=dashboardUriMake($arParams['DETAIL_URL'], $arResult['URI_PARAMS'], array('user_type=farmer'));?>"><?=$arResult['DATA']['FARMERS_DATA_MAIN']['TOTAL_TODAY'];?></a></td>
            <td><?=$arResult['PERCENTS']['FARMERS_DATA_MAIN']['TOTAL_YESTERDAY'];?></td>
            <td><?=$arResult['PERCENTS']['FARMERS_DATA_MAIN']['TOTAL_WEEK_AGO'];?></td>
        </tr>

        <tr>
            <td class="label_inner">Полноценный режим</td>
            <td><a href="<?=dashboardUriMake($arParams['DETAIL_URL'], $arResult['URI_PARAMS'], array('user_type=farmer', 'show_type=not_demo'));?>"><?=$arResult['DATA']['FARMERS_DATA_MAIN']['NOT_DEMO_TODAY'];?></a></td>
            <td><?=$arResult['PERCENTS']['FARMERS_DATA_MAIN']['NOT_DEMO_YESTERDAY'];?></td>
            <td><?=$arResult['PERCENTS']['FARMERS_DATA_MAIN']['NOT_DEMO_WEEK_AGO'];?></td>
        </tr>

        <tr>
            <td class="label_inner">Демо-режим</td>
            <td><a href="<?=dashboardUriMake($arParams['DETAIL_URL'], $arResult['URI_PARAMS'], array('user_type=farmer', 'show_type=demo'));?>"><?=$arResult['DATA']['FARMERS_DATA_MAIN']['DEMO_TODAY'];?></a></td>
            <td><?=$arResult['PERCENTS']['FARMERS_DATA_MAIN']['DEMO_YESTERDAY'];?></td>
            <td><?=$arResult['PERCENTS']['FARMERS_DATA_MAIN']['DEMO_WEEK_AGO'];?></td>
        </tr>

        <tr><td colspan="4"><div class="dash_line"></div></td></tr>

        <tr class="label_line">
            <td class="label">Количество поставщиков без активных товаров</td>
            <td><a href="<?=dashboardUriMake($arParams['DETAIL_URL'], $arResult['URI_PARAMS'], array('user_type=farmer', 'show_type=no_data'));?>"><?=$arResult['DATA']['FARMERS_DATA_NO_OFFERS']['TODAY'];?></a></td>
            <td><?=$arResult['PERCENTS']['FARMERS_DATA_NO_OFFERS']['YESTERDAY'];?></td>
            <td><?=$arResult['PERCENTS']['FARMERS_DATA_NO_OFFERS']['WEEK_AGO'];?></td>
        </tr>

        <tr><td colspan="4"><div class="dash_line"></div></td></tr>

        <tr class="label_line">
            <td class="label">Количество покупателей</td>
            <td><a href="<?=dashboardUriMake($arParams['DETAIL_URL'], $arResult['URI_PARAMS'], array('user_type=client'));?>"><?=$arResult['DATA']['CLIENTS_DATA_MAIN']['TOTAL_TODAY'];?></a></td>
            <td><?=$arResult['PERCENTS']['CLIENTS_DATA_MAIN']['TOTAL_YESTERDAY'];?></td>
            <td><?=$arResult['PERCENTS']['CLIENTS_DATA_MAIN']['TOTAL_WEEK_AGO'];?></td>
        </tr>

        <tr>
            <td class="label_inner">Полноценный режим</td>
            <td><a href="<?=dashboardUriMake($arParams['DETAIL_URL'], $arResult['URI_PARAMS'], array('user_type=client', 'show_type=not_demo'));?>"><?=$arResult['DATA']['CLIENTS_DATA_MAIN']['NOT_DEMO_TODAY'];?></a></td>
            <td><?=$arResult['PERCENTS']['CLIENTS_DATA_MAIN']['NOT_DEMO_YESTERDAY'];?></td>
            <td><?=$arResult['PERCENTS']['CLIENTS_DATA_MAIN']['NOT_DEMO_WEEK_AGO'];?></td>
        </tr>

        <tr>
            <td class="label_inner">Демо-режим</td>
            <td><a href="<?=dashboardUriMake($arParams['DETAIL_URL'], $arResult['URI_PARAMS'], array('user_type=client', 'show_type=demo'));?>"><?=$arResult['DATA']['CLIENTS_DATA_MAIN']['DEMO_TODAY'];?></a></td>
            <td><?=$arResult['PERCENTS']['CLIENTS_DATA_MAIN']['DEMO_YESTERDAY'];?></td>
            <td><?=$arResult['PERCENTS']['CLIENTS_DATA_MAIN']['DEMO_WEEK_AGO'];?></td>
        </tr>

        <tr><td colspan="4"><div class="dash_line"></div></td></tr>

        <tr class="label_line">
            <td class="label">Количество покупателей без активных запросов</td>
            <td><a href="<?=dashboardUriMake($arParams['DETAIL_URL'], $arResult['URI_PARAMS'], array('user_type=client', 'show_type=no_data'));?>"><?=$arResult['DATA']['CLIENTS_DATA_NO_REQUESTS']['TODAY'];?></a></td>
            <td><?=$arResult['PERCENTS']['CLIENTS_DATA_NO_REQUESTS']['YESTERDAY'];?></td>
            <td><?=$arResult['PERCENTS']['CLIENTS_DATA_NO_REQUESTS']['WEEK_AGO'];?></td>
        </tr>

        <tr><td colspan="4"><div class="dash_line"></div></td></tr>

        <tr class="label_line">
            <td class="label">Транспортные компании</td>
            <td><a href="<?=dashboardUriMake($arParams['DETAIL_URL'], $arResult['URI_PARAMS'], array('user_type=transport'));?>"><?=$arResult['DATA']['TRANSPORT_DATA']['TODAY'];?></a></td>
            <td><?=$arResult['PERCENTS']['TRANSPORT_DATA']['YESTERDAY'];?></td>
            <td><?=$arResult['PERCENTS']['TRANSPORT_DATA']['WEEK_AGO'];?></td>
        </tr>

        <tr><td colspan="4"><div class="dash_line"></div></td></tr>

    </table>
</div>