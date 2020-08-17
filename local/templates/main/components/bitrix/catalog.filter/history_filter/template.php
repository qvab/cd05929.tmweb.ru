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

CJSCore::Init(array("jquery","date"));
?>
<div class="tab_form">
    <form method="GET" id="history_filter">

        <div class="row">
            <div class="wrap-select">
                <input type="text" name="date_from" placeholder="Дата от" value="<?=(isset($_GET['date_from']) ? $_GET['date_from'] : '')?>" onclick="BX.calendar({node: this, field: this, bTime: false});" />
            </div>

            <div class="wrap-select">
                <input type="text" name="date_to" placeholder="Дата до" value="<?=(isset($_GET['date_to']) ? $_GET['date_to'] : '')?>" onclick="BX.calendar({node: this, field: this, bTime: false});" />
            </div>
            <div class="clear"></div>

            <?
            //дополнительная фильтрация "история принятий"/"история ограничений"
            if(!isset($arParams['BY_ADMIN'])
            && isset($arParams['USER_TYPE'])
            && $arParams['USER_TYPE'] == 'client'
            ){?>
            <div class="wrap-select l2">
                <select name="data_type">
                    <option value="0">История принятий и ограничений</option>
                    <option value="1" <?if(isset($_GET['data_type']) && $_GET['data_type'] == 1){ echo 'selected="selected"';}?>>История принятий</option>
                    <option value="2" <?if(isset($_GET['data_type']) && $_GET['data_type'] == 2){ echo 'selected="selected"';}?>>История ограничений</option>
                </select>
            </div>
                <div class="clear"></div>
            <?}?>

        </div>

        <?if(isset($arParams['BY_ADMIN'])
                && $arParams['BY_ADMIN'] == 'Y'
                && isset($arResult['USERS'])
                && count($arResult['USERS']) > 0
            ) {?>
            <div class="row">
                <div class="wrap-select">
                    <select <?if(count($arResult['USERS']) > 4){?>data-search="y" <?}?>name="uid">
                        <option value="0">Все покупатели</option>
                        <?foreach($arResult['USERS'] as $cur_id => $cur_name){?>
                            <option value="<?=$cur_id?>"<?if(isset($_GET['uid']) && $_GET['uid'] == $cur_id){ echo ' selected="selected"'; }?>><?=$cur_name?></option>
                        <?}?>
                    </select>
                </div>
                <div class="clear"></div>
            </div>
        <?}?>

        <div class="row">
            <div class="wrap-btn">
                <input class="submit-btn" value="Применить" type="submit">
            </div>
            <div class="wrap-btn">
                <input class="submit-btn reset" value="Сбросить" type="button">
            </div>
            <div class="clear"></div>
        </div>

    </form>
</div>
