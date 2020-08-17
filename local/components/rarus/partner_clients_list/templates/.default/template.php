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

CJSCore::Init(array('date'));
?>
<?/*<script src="http://api-maps.yandex.ru/2.1/?lang=ru_RU" type="text/javascript"></script>*/?>
<?
if(sizeof($arResult["ITEMS"]) > 0){
    ?>
    <div class="list_page_rows">
        <?
        $cdate = date('d.m.Y');
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

                    <div class="agent_contract_sign<?if(!empty($arResult['LINKED_ITEMS'][$cur_id]['CONTRACT'])){?> active<?}?>">Агентский<br/>договор</div>
                </div>
                <form action="" method="post" class="line_additional">
                    <div class="prop_area additional_submits">
                        <a href="/profile/?uid=<?=$cur_id;?>" target="_blank">Перейти в профиль</a>
                    </div>

                    <?if($arItem['UF_FIRST_LOGIN']){
                        if(isset($arResult['CLIENTS_PROFILE_DONE'][$cur_id])
                            && $arResult['CLIENTS_PROFILE_DONE'][$cur_id]){
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

                    <?if(isset($arResult['CLIENTS_PROFILE_DONE'][$cur_id])
                        && $arResult['CLIENTS_PROFILE_DONE'][$cur_id]){?>
                        <div class="prop_area additional_submits for_linked">
                            <a href="/profile/documents/?uid=<?=$cur_id;?>">Работа с документами покупателя</a>
                        </div>
                    <?}?>

                    <div class="prop_area additional_submits for_linked">
                        <a href="javascript:void(0)" onclick="unlinkPartner(<?=$cur_id;?>)">Отвязать пользователя</a>
                    </div>

                    <div class="prop_area additional_submits for_unlinked">
                        <a href="javascript:void(0)" onclick="linkPartner(<?=$cur_id;?>)">Привязать пользователя</a>
                    </div>

                    <?
                    $cur_file = array();
                    if(!empty($arResult['LINKED_ITEMS'][$cur_id]['CONTRACT_FILE'])){
                        $res = CFile::GetByID($arResult['LINKED_ITEMS'][$cur_id]['CONTRACT_FILE']);
                        if ($cur_file = $res->Fetch()) {
                            $temp_path = CFile::GetPath($arResult['LINKED_ITEMS'][$cur_id]['CONTRACT_FILE']);
                            if ($temp_path) {
                                $cur_file['f_src'] = $temp_path;
                            }
                        }
                    }
                    ?>
                    <div class="prop_area additional_submits for_linked radio_area contract_prop">
                        <div class="error_message">При сохранении произошла ошибка</div>
                        <div class="result_message">Данные успешно сохранены</div>
                        <div class="radio_group">
                            <input type="checkbox" name="agent_contract" data-text="Агентский договор" <?if(!empty($arResult['LINKED_ITEMS'][$cur_id]['CONTRACT'])){?> checked="checked"<?}?> value="1" />
                        </div>
                        <?/*<div class="agent_contract_date_label">Дата агентского договора</div>
                        <input type="text" name="agent_contract_date" value="<?=(!empty($arResult['LINKED_ITEMS'][$cur_id]['CONTRACT_DATE']) ? $arResult['LINKED_ITEMS'][$cur_id]['CONTRACT_DATE'] : $cdate);?>" onclick="BX.calendar({node:this, field:this, form: '', bTime: false, bHideTime: false});" />*/?>

                        <?
                        if(isset($cur_file['f_src'])) {
                            if (file_exists($_SERVER['DOCUMENT_ROOT'] . $cur_file['f_src'])) {
                                $class = '';
                                ?>
                                <div class="get_file">
                                    Посмотреть текущий договор <a target="_blank" href="<?=$cur_file['f_src'];?>">по ссылке</a>
                                    <?if($arResult['LINKED_ITEMS'][$cur_id]['CONTRACT_LAST_DATE']){?>
                                        <div class="date_line">Дата последнего изменения: <span class="date_val"><?=$arResult['LINKED_ITEMS'][$cur_id]['CONTRACT_LAST_DATE'];?></span></div>
                                    <?}?>
                                </div>
                                <?
                            }
                            else {
                                ?>
                                <div class="get_file">Ошибка загруженного файла (требуется заменить файл, загрузив новый)</div>
                                <?
                            }
                        }
                        ?>
                        <input type="file" <?if(isset($cur_file['f_src'])){?>data-text="Заменить файл"<?}?> name="agent_contract_file" class="file_btn needFile" />
                        <?/*<input type="button" value="Применить" class="submit-btn" />*/?>
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
        Ни одного покупателя не найдено
    </div>
    <?
}