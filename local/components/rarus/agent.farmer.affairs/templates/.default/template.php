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

<div class="wrap-affairs">

    <?if(!empty($arResult['ERROR_MSG'])):?>
        <div class="error-msg"><?ShowError($arResult['ERROR_MSG'])?></div>
    <?endif;?>

    <?// Фильтр?>
    <div class="wrap-filter">

        <form method="GET" id="filter_affairs">

            <div class="row">

                <?if($arParams['FILTER_FIELDS']['DATE_FROM']):?>

                    <div class="wrap-input">
                        <input
                                type="text"
                                value="<?=$arResult['DATE_FROM']?>"
                                name="DATE_FROM"
                                placeholder="Дата действия от"
                                onclick="BX.calendar({node:this, field:this, form: '', bTime: false, bHideTime: false});"
                        />
                    </div>
                <?endif;?>

                <?if($arParams['FILTER_FIELDS']['DATE_TO']):?>

                    <div class="wrap-input">
                        <input
                                type="text"
                                value="<?=$arResult['DATE_TO']?>"
                                name="DATE_TO"
                                placeholder="Дата действия по"
                                onclick="BX.calendar({node:this, field:this, form: '', bTime: false, bHideTime: false});"
                        />
                    </div>
                <?endif;?>

                <div class="clear"></div>
            </div>

            <div class="row">

                <?if($arParams['FILTER_FIELDS']['TYPE'] && !empty($arResult['DATA_FILTER']['TYPE'])):?>
                    <div class="wrap-select">
                        <select name="TYPE_ID" placeholder="Выберите тип">
                            <option value="0">Все типы дел</option>
                            <?foreach ($arResult['DATA_FILTER']['TYPE'] as $arType):?>
                                <?$sSelected = ($_GET['TYPE_ID'] == $arType['ID']) ? 'selected="selected"' : null?>
                                <option <?=$sSelected?> value="<?=$arType['ID']?>"><?=$arType['TITLE']?></option>
                            <?endforeach;?>
                        </select>
                    </div>
                <?endif;?>

                <?if(!empty($arResult['DATA_FILTER']['FARMER'])):?>

                    <?$sDataSearch = (count($arResult['DATA_FILTER']['FARMER']) > 3) ? 'data-search="y"' : null;?>
                    <div class="wrap-select">
                        <select <?=$sDataSearch?> name="FARMER" placeholder="Выберите культуру">
                            <option value="0">Все поставщики</option>
                            <?foreach ($arResult['DATA_FILTER']['FARMER'] as $arFarmer):?>
                                <?$sSelected = ($_GET['FARMER'] == $arFarmer['USER_ID']) ? 'selected="selected"' : null?>
                                <option <?=$sSelected?> value="<?=$arFarmer['USER_ID']?>"><?=$arFarmer['TITLE']?></option>
                            <?endforeach;?>
                        </select>
                    </div>
                <?endif;?>
                <div class="clear"></div>
            </div>

            <?if(!empty($arParams['HIDDEN_INPUTS']) && is_array($arParams['HIDDEN_INPUTS'])):?>
                <?foreach ($arParams['HIDDEN_INPUTS'] as $sName => $sValue):?>
                    <input type="hidden" name="<?=$sName?>" value="<?=$sValue?>"/>
                <?endforeach;?>
            <?endif;?>

            <div class="row">
                <div class="wrap-btn">
                    <input class="submit-btn" value="Применить" type="submit">
                </div>
                <div class="wrap-btn">
                    <input class="submit-btn reset <?=!$arResult['FILTER_USED'] ? 'hidden' : ''?>" value="Сбросить" type="button">
                </div>
                <div class="clear"></div>
            </div>
        </form>
    </div>
    <?// END Фильтр?>

    <?if(
    isset($arParams['ADD_NEW'])
    && $arParams['ADD_NEW'] == 'Y'
    && isset($arParams['FARMER_ID'])
    && count($arResult['DATA_AFFAIR']['ITEMS']) >= 10
    ):?>
        <div class="additional_href_data">
            <a href="/partner/offer/new/">+ Добавить товар</a>
        </div>
    <?endif;?>
    <div class="items-affairs">
        <div class="wrap-items">

            <?// Список дел?>
            <div class="list_page_rows affairs">

                <?foreach ($arResult['DATA_AFFAIR']['ITEMS'] as $arAffair):?>

                    <?
                    $sName = null;
                    $sDescription = null;
                    $sFarmerName = null;

                    $sCode = $arResult['TYPES_AFFAIR'][$arAffair['UF_TYPE_AFFAIR']]['XML_ID'];

                    if($sCode == 'OFFER') {

                        $sName = 'Товар';
                        $sFarmerName = $arResult['DATA_FILTER']['FARMER'][$arAffair['UF_USER_PARTICIPANT']]['TITLE'];

                        if($arParams['SHOW_DESCRIPTION_FARMER']) {
                            $sDescription .= '<b>Поставщик:</b> <a href="/profile/?uid='.$arAffair['UF_USER_PARTICIPANT'].'">' . $sFarmerName . '</a><br />';
                        }

                        $sDescription .= '<b>Ожидаемая цена (руб/тн):</b> ' . $arAffair['UF_EXPECTED_PRICE'] . '<br />';
                        $sDescription .= '<b>Объем в наличии у поставщика (тонн):</b> ' . $arAffair['UF_FARMER_VOLUME'] . '<br />';

                    } elseif ($sCode == 'REQUEST') {
                        $sName = 'Запрос';
                    }

                    $sDateAffair = $arAffair['UF_DATE_AFFAIR']->format('d.m.Y');
                    $sDateCreate = $arAffair['UF_DATE_CREATE']->format('d.m.Y H:i:s');
                    
                    if(!empty($arAffair['UF_COMMENT'])) {
                        $sDescription .= '<b>Комментарии для следующего звонка:</b><br />' . $arAffair['UF_COMMENT'];
                    }
                    ?>

                    <div class="line_area">
                        <div class="line_inner">
                            <div class="name"><?=$sName?>
                                <span class="entity">
                                    <a target="_blank" href="/partner/offer/?&status=all&id=<?=$arAffair['UF_XML_ID']?>#<?=$arAffair['UF_XML_ID']?>"><?=$arResult['OFFERS'][$arAffair['UF_XML_ID']]['CULTURE_NAME'];?></a>
                                </span>
                            </div>
                            <div class="id_date"><b>Дата действия</b>: <?=$sDateAffair?></div>
                            <div class="id_date"><b>От</b>: <?=$sDateCreate?></div>

                            <div class="description">
                                <?=$sDescription?>
                            </div>
                            <div class="clear"></div>
                        </div>
                    </div>

                <?endforeach;?>
            </div>
            <?// Список дел?>

            <div class="nav_string"><?=$arResult['DATA_AFFAIR']['NAV_STRING']?></div>

            <?if(empty($arResult['DATA_AFFAIR']['ITEMS'])):?>
                <?if($arResult['FILTER_USED']):?>
                    <div>По заданному фильтру нет записей</div>
                <?else:?>
                    <div>Нет дел</div>
                <?endif;?>
            <?endif;?>

            <?if(
                    isset($arParams['ADD_NEW'])
                    && $arParams['ADD_NEW'] == 'Y'
                    && isset($arParams['FARMER_ID'])
            ):?>
                <a href="/profile/agent_affairs/new/?uid=<?=$arParams['FARMER_ID'];?>" class="add_blue_button">Добавить дело<div class="but_addit"><div class="ico">+</div></div></a>

            <?endif;?>
        </div>
    </div>
</div>