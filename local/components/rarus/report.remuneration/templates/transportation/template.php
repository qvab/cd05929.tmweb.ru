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
<div class="wrap-report">

    <div class="title">
        <h1><?$APPLICATION->ShowTitle()?></h1>
    </div>

    <!--Сообщение об ошибке-->
    <?if(!empty($arResult['ERROR_MSG'])):?>
        <div class="error-msg">
            <?ShowError('Ошибка! ' . $arResult['ERROR_MSG'])?>
        </div>
    <?endif;?>


    <div class="wrap-filter">

        <form method="POST" id="filter-report">

            <input type="hidden" name="GET_REPORT_TRANSPORTATION" value="Y">
            <?=bitrix_sessid_post()?>

            <div class="row">

                <div class="wrap-input">
                    <input
                            type="text"
                            value="<?=$arParams['DATE_FROM']?>"
                            name="DATE_FROM"
                            placeholder="Сделка от"
                            onclick="BX.calendar({node:this, field:this, form: '', bTime: false, bHideTime: false});"
                    />
                </div>

                <div class="wrap-input">
                    <input
                            type="text"
                            value="<?=$arParams['DATE_TO']?>"
                            name="DATE_TO"
                            placeholder="Сделка по"
                            onclick="BX.calendar({node:this, field:this, form: '', bTime: false, bHideTime: false});"
                    />
                </div>

                <div class="clear"></div>
            </div>

            <?if($arParams['IS_SHOW_FILTER_ORGANIZER']):?>
                <div class="row">
                    <div class="wrap-select">
                        <select data-search="y" name="ORGANIZER" placeholder="Выберите организатора">
                            <option value="0">Все организаторы</option>
                            <?foreach ($arResult['ORGANIZER_LIST'] as $id => $sTitle):?>
                                <?$sSelected = ($arParams['ORGANIZER'] == $id) ? 'selected': null;?>
                                <option <?=$sSelected?> value="<?=$id?>"><?=$sTitle?></option>
                            <?endforeach;?>
                        </select>
                    </div>

                    <div class="clear"></div>
                </div>
            <?endif;?>

            <div class="row">

                <div class="wrap-btn">
                    <input type="reset" class="submit-btn reset" value="Сбросить"/>
                </div>

                <div class="wrap-btn">
                    <input type="submit" class="submit-btn" value="Сформировать отчет"/>
                </div>
                <div class="clear"></div>
            </div>

        </form>
    </div>


    <div class="wrap-data">

        <?if($arResult['PROCESSING_REPORT'] && empty($arResult['DATA_REPORT'])):?>
            <div class="no-report">По заданным условиям не удалось сформировать отчет</div>
        <?endif;?>

        <?$sNoVal = '<div class="no-val">---</div>';?>

        <?if(!empty($arResult['DATA_REPORT'])):?>
            <table cellspacing="0">
                <tbody>
                    <tr>
                        <th class="doc-name">№ и дата ДТР</th>
                        <th class="company-name">Исполнитель</th>
                        <th class="company-name">Заказчик</th>

                        <th class="sum">Сумма сделки, руб</th>
                        <th class="company-name">Организатор АП</th>
                        <th class="sum">Вознаграждение Организатору АП, руб</th>

                        <th class="company-name">Агент</th>
                        <th class="percent-payment" >%</th>
                        <th class="sum">Выплаты Агенту, руб.</th>

                        <th class="sum">Выплаты Оператору (АХ), руб</th>
                    </tr>

                <?foreach ($arResult['DATA_REPORT'] as $arItem):?>
                    <tr>
                        <td class="doc-name"><?=$arItem['DTR']?:$sNoVal?></td>
                        <td><?=$arItem['TRANSPORT_NAME']?></td>
                        <td><?=$arItem['FARMER_NAME']?></td>

                        <td class="sum"><?=$arItem['COST_DEALS']?:$sNoVal?></td>
                        <td><?=$arItem['PARTNER_NAME']?:$sNoVal?></td>
                        <td class="sum"><?=$arItem['REMUNERATION_ORGANIZER_AP']?:$sNoVal?></td>

                        <td><?=$arItem['AGENT_NAME']?:$sNoVal?></td>
                        <td class="percent-payment"><?=$arItem['AGENT_REWARD_PERCENT']?:$sNoVal?></td>
                        <td class="sum"><?=$arItem['AGENT_PAYMENTS']?:$sNoVal?></td>

                        <td class="sum"><?=$arItem['PAYMENTS_OPERATOR_AH']?:$sNoVal?></td>
                    </tr>
                <?endforeach;?>
                </tbody>
            </table>
        <?endif;?>
    </div>
</div>