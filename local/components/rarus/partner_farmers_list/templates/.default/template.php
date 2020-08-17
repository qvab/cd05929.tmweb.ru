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
        $page_link = $APPLICATION->GetCurPageParam('', array('deactivate', 'get_invite'));
        foreach($arResult["ITEMS"] as $cur_id => $arItem){
            $linked = (isset($arResult['LINKED_ITEMS'][$cur_id]) ? true : false);
            ?>
            <div class="line_area <?=($linked ? 'is_linked' : 'not_linked');?>" data-uid="<?=$cur_id;?>">
                <div class="line_inner">
                    <div class="name"><?
                        if(trim($arItem['NAME']) != ''){
                            echo $arItem['NAME'] . (!empty($arItem['EMAIL']) ? ' (' . $arItem['EMAIL'] . ')' : '');
                        }elseif(!empty($arItem['EMAIL'])){
                            echo $arItem['EMAIL'];
                        }else{
                            echo $arItem['ID'];
                        }
                    ?></div>
                    <div class="arw_list arw_icon_close"></div>
                    <div class="clear"></div>
                </div>
                <form action="" method="post" class="line_additional">
                    <div class="prop_area additional_submits">
                        <a href="/profile/?uid=<?=$cur_id;?>" target="_blank">Перейти в профиль</a>
                    </div>

                    <?if($arItem['UF_FIRST_LOGIN']){
                        if(isset($arResult['FARMERS_PROFILE_DONE'][$cur_id])
                            && $arResult['FARMERS_PROFILE_DONE'][$cur_id]){
                            ?>
                            <div class="prop_area additional_submits for_linked">
                                <a href="javascript:void(0)" data-val="make_invite" onclick="inviteByPartner(<?=$cur_id;?>);">Сформировать ссылку для авторизации</a>
                            </div>
                            <?
                        }else{
                            ?>
                            <div class="prop_area additional_submits for_linked">
                                Для формирования ссылки авторизации необходимо <a href="/profile/make_full_mode/?uid=<?=$cur_id;?>">заполнить профиль</a> пользователя
                            </div>
                            <?
                        }
                    }?>

                    <?/*<div class="prop_area additional_submits for_linked">
                        <a data-val="delete" href="<?=$APPLICATION->GetCurPageParam('deactivate=' . $cur_id, array('deactivate'));?>">Деактивировать пользователя</a>
                    </div>

                    <div class="prop_area additional_submits for_linked">
                        <a href="/profile/make_full_mode/?uid=<?=$cur_id;?>">Изменить профиль</a>
                    </div>*/?>

                    <?if(isset($arResult['FARMERS_PROFILE_DONE'][$cur_id])
                        && $arResult['FARMERS_PROFILE_DONE'][$cur_id]){?>
                        <div class="prop_area additional_submits for_linked">
                            <a href="/profile/documents/?uid=<?=$cur_id;?>">Работа с документами поставщика</a>
                        </div>
                    <?}?>

                    <div class="prop_area additional_submits for_linked">
                        <a href="javascript:void(0)" onclick="unlinkPartner(<?=$cur_id;?>)">Отвязать пользователя</a>
                    </div>

                    <div class="prop_area additional_submits for_unlinked">
                        <a href="javascript:void(0)" onclick="linkPartner(<?=$cur_id;?>)">Привязать пользователя</a>
                    </div>

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

    //пагинация
    echo $arResult['NAV_DATA'];
}
else {
    ?>
    <div class="list_page_rows requests no-item">
        Ни одного поставщика не найдено
    </div>
    <?
}