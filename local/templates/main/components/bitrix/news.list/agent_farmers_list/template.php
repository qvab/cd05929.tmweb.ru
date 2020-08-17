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
<?/*<script src="http://api-maps.yandex.ru/2.1/?lang=ru_RU" type="text/javascript"></script>*/?>
<?

if (sizeof($arResult["ITEMS"]) > 0) {
?>
    <div class="list_page_rows">
        <?
        foreach ($arResult["ITEMS"] as $arItem) {
        ?>
            <div class="line_area <?=$arItem["PROPERTIES"]['ACTIVE']['VALUE_XML_ID']?>" data-uid="<?=$arItem['PROPERTIES']['USER_ID']['VALUE'];?>">
                <div class="line_inner">
                    <div class="name"><?if(isset($arResult['FARMERS_DATA'][$arItem['PROPERTIES']['USER_ID']['VALUE']])){
                            $cur_farmer = $arResult['FARMERS_DATA'][$arItem['PROPERTIES']['USER_ID']['VALUE']];
                            if($cur_farmer['NICK'] != ''){
                                ?><?=$cur_farmer['NICK'];?><?
                            }elseif($cur_farmer['NAME'] == ''){
                                ?><?=$cur_farmer['EMAIL'];?><?
                            }else{
                                ?><?=$cur_farmer['NAME'];?> (<?=$cur_farmer['EMAIL'];?>)<?
                            }
                        }
                        ?></div>
                    <div class="arw_list arw_icon_close"></div>
                    <div class="clear"></div>
                </div>
                <form action="" method="post" class="line_additional">
                    <div class="prop_area additional_submits">
                        <a href="/profile/?uid=<?=$arItem['PROPERTIES']['USER_ID']['VALUE'];?>" target="_blank">Перейти в профиль</a>
                    </div>

                    <?if($arResult['FARMERS_DATA'][$arItem['PROPERTIES']['USER_ID']['VALUE']]['UF_FIRST_LOGIN']){
                        if(isset($arResult['FARMERS_PROFILE_DONE'][$arItem['PROPERTIES']['USER_ID']['VALUE']])
                            && $arResult['FARMERS_PROFILE_DONE'][$arItem['PROPERTIES']['USER_ID']['VALUE']]
                        ){
                            ?>
                            <div class="prop_area additional_submits">
                                <a data-val="make_invite" href="<?=$APPLICATION->GetCurDir()?>?get_invite=<?=$arItem['PROPERTIES']['USER_ID']['VALUE'];?>">Сформировать ссылку для авторизации</a>
                            </div>
                            <?
                        }else{
                            ?>
                            <div class="prop_area additional_submits">
                                Для формирования ссылки авторизации необходимо <a href="/profile/make_full_mode/?uid=<?=$arItem['PROPERTIES']['USER_ID']['VALUE'];?>">заполнить профиль</a> пользователя
                            </div>
                            <?
                        }
                    }?>

                    <div class="prop_area additional_submits">
                        <a data-val="delete" href="<?=$APPLICATION->GetCurDir()?>?deactivate=<?=$arItem['PROPERTIES']['USER_ID']['VALUE'];?>">Деактивировать пользователя</a>
                    </div>

                    <div class="prop_area additional_submits">
                        <a target="_blank" href="/profile/make_full_mode/?uid=<?=$arItem['PROPERTIES']['USER_ID']['VALUE'];?>">Изменить профиль</a>
                    </div>

                    <?if(isset($arResult['FARMERS_PROFILE_DONE'][$arItem['PROPERTIES']['USER_ID']['VALUE']])
                        && $arResult['FARMERS_PROFILE_DONE'][$arItem['PROPERTIES']['USER_ID']['VALUE']]
                    ){
                        ?>
                        <div class="prop_area additional_submits">
                            <a target="_blank" href="/profile/documents/?uid=<?=$arItem['PROPERTIES']['USER_ID']['VALUE'];?>">Работа с документами поставщика</a>
                        </div>
                    <?}?>

                    <div class="prop_area additional_submits">
                        <div class="hide_but">Свернуть</div>
                    </div>
                </form>
            </div>
        <?
        }
        ?>
    </div>
<?
}
else {
?>
    <div class="list_page_rows requests no-item">
        Ни одного поставщика не найдено
    </div>
<?
}