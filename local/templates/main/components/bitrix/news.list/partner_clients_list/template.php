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
if(sizeof($arResult["ITEMS"]) > 0){
?>
    <div class="list_page_rows">
        <?
        foreach($arResult["ITEMS"] as $arItem){
        ?>
            <div class="line_area <?=$arItem["PROPERTIES"]['ACTIVE']['VALUE_XML_ID']?>" data-uid="<?=$arItem['PROPERTIES']['USER_ID']['VALUE'];?>">
                <div class="line_inner">
                    <div class="name"><?if(isset($arResult['CLIENTS_DATA'][$arItem['PROPERTIES']['USER_ID']['VALUE']])){
                            $cur_client = $arResult['CLIENTS_DATA'][$arItem['PROPERTIES']['USER_ID']['VALUE']];
                            if($cur_client['NICK'] != ''){
                                ?><?=$cur_client['NICK'];?><?
                            }elseif($cur_client['NAME'] == ''){
                                ?><?=$cur_client['EMAIL'];?><?
                            }else{
                                ?><?=$cur_client['NAME'];?> (<?=$cur_client['EMAIL'];?>)<?
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

                    <?if($arResult['CLIENTS_DATA'][$arItem['PROPERTIES']['USER_ID']['VALUE']]['UF_FIRST_LOGIN']){
                        if(isset($arResult['CLIENTS_PROFILE_DONE'][$arItem['PROPERTIES']['USER_ID']['VALUE']])
                            && $arResult['CLIENTS_PROFILE_DONE'][$arItem['PROPERTIES']['USER_ID']['VALUE']]){
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
                        <a href="/profile/make_full_mode/?uid=<?=$arItem['PROPERTIES']['USER_ID']['VALUE'];?>">Изменить профиль</a>
                    </div>

                    <?if(isset($arResult['CLIENTS_PROFILE_DONE'][$arItem['PROPERTIES']['USER_ID']['VALUE']])
                        && $arResult['CLIENTS_PROFILE_DONE'][$arItem['PROPERTIES']['USER_ID']['VALUE']]){?>
                        <div class="prop_area additional_submits">
                            <a href="/profile/documents/?uid=<?=$arItem['PROPERTIES']['USER_ID']['VALUE'];?>">Работа с документами покупателя</a>
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
        Ни одного покупателя не найдено
    </div>
<?
}