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

if ($arResult['ERROR'] == 'Y') {
    echo '<div class="err_text row">' . $arResult['ERROR_MESSAGE'] . '</div>';
}
else {
    if ($arResult['MESS_STR'] != '') {
        echo '<div class="err_text row">' . $arResult['MESS_STR'] . '</div>';
    }
    ?>
    <div class="connected_users_list">
        <?
        if(count($arResult['USERS_LIST']) > 0 || isset($_GET['agent_id']) && is_numeric($_GET['agent_id']) && $_GET['agent_id'] > 0) {
        ?>
            <div class="row_head"><?=$arParams['SUB_HEAD'];?>:</div>

            <?if(count($arResult['AGENTS_LIST']) > 0){?>
                <form action="" method="get" name="agent_select">
                    <div class="list_page_rows agents_list">
                        <select name="agent_id" <?if(count($arResult['AGENTS_LIST']) > 4){?>data-search="y"<?}?> >
                            <option value="0">Все агенты поставщиков</option>
                            <?foreach($arResult['AGENTS_LIST'] as $cur_id => $cur_data){?>
                                <option <?if(isset($_GET['agent_id']) && $_GET['agent_id'] == $cur_id){?>selected="selected"<?}?> value="<?=$cur_id?>"><?=$cur_data;?></option>
                            <?}?>
                        </select>
                    </div>
                    <div class="clear"></div>
                    <div class="row fbtn_submit">
                        <input class="submit-btn left" value="Применить" type="submit">
                    </div>
                    <div class="row fbtn_cancel">
                        <a href="/partner/users/linked_users/" class="cancel_filter">Сбросить</a>
                    </div>
                    <div class="clear"></div>
                </form>

                <?if(count($arResult['USERS_LIST']) == 0 && isset($_GET['agent_id']) && is_numeric($_GET['agent_id']) && $_GET['agent_id'] > 0){?>
                    <div><br/>У выбранного агента нет привязанных поставщиков</div>
                <?}?>
            <?}?>
            <div class="list_page_rows ">
                <?

                $cur_dir = $APPLICATION->GetCurDir(false);
                foreach ($arResult['USERS_LIST'] as $cur_id => $cur_data) {
                    $file_data = array();
                    if(isset($cur_data['EMAIL']))
                    {
                        if($cur_data['ACTIVE'] == 'Y')
                        {?>
                            <div class="line_area" user-id="<?=$cur_id?>">
                                <div class="line_inner">
                                    <div class="inner_text">
                                        <span class="email_val">
                                            <?
                                            if ($cur_data['COMPANY_NAME']) {
                                                echo $cur_data['COMPANY_NAME'];
                                            }
                                            else {
                                                echo $cur_data['EMAIL'];
                                            }
                                            ?>
                                        </span>
                                        (статус: аккаунт активирован)
                                        <?
                                        if (isset($cur_data['VERIFIED']) && $cur_data['VERIFIED'] == 'n') {
                                        ?>
                                            <div class="red_text">Внимание! Для завершения прикрепления к поставщику необходимо подтверждение.</div>
                                        <?
                                        }
                                        ?>
                                    </div>
                                    <div title="Отвязаться от поставщика" data-uid="<?=$cur_id;?>" class="unlink_but<?if(isset($arResult['UNCOMPLETE_DEALS_IDS'][$cur_id])){?> disabled<?}?>"></div>
                                    <div class="clear"></div>
                                </div>
                                <form action="" method="post" class="line_additional" enctype="multipart/form-data">
                                    <input name="add_doc" value="y" type="hidden" />
                                    <input name="uid" value="<?=$cur_id;?>" type="hidden" />

                                    <?if(!isset($cur_data['OPEN_DEALS'])
                                        || !$cur_data['OPEN_DEALS']
                                    ){?>
                                        <div class="prop_area additional_submits">
                                            <?if($cur_data['UF_DEMO']){?>
                                                <a data-val="delete" href="<?=$APPLICATION->GetCurDir()?>?delete=<?=$cur_id;?>">Удалить пользователя</a>
                                            <?}else{?>
                                                <a data-val="delete" href="<?=$APPLICATION->GetCurDir()?>?deactivate=<?=$cur_id;?>">Деактивировать пользователя</a>
                                            <?}?>
                                        </div>
                                    <?}?>

                                    <div class="prop_area">
                                        <?if (isset($cur_data['VERIFIED']) && $cur_data['VERIFIED'] == 'n') {?>
                                            <div class="radio_group">
                                                <div class="radio_area">
                                                    <input type="checkbox" data-text="Подтверждено" name="ch_verified" value="yes" />
                                                </div>
                                                <div class="btn_block">
                                                    <input type="button" data-id="<?=$cur_data['LINK_ID']?>" name="verified_btn" class="submit-btn" value="Сохранить">
                                                </div>
                                                <div class="line_drawn_line top"></div>
                                                <div class="sub_row"></div>
                                            </div>
                                        <?}?>
                                        <a href="/profile/?uid=<?=$cur_id;?>">Перейти в профиль компании</a>
                                        <div class="sub_row"></div>

                                        <?if(count($arResult['AGENTS_LIST']) > 0){
                                            $cur_agent_set = ($cur_data['AGENT_ID'] != '' && isset($arResult['AGENTS_LIST'][$cur_data['AGENT_ID']]));
                                            ?>
                                            <div class="line_drawn_line top"></div>
                                            <div class="line_drawn">
                                                <div class="row_sub_head bold">Привязка агента к поставщику</div>
                                                <div class="sub_row">
                                                    <div class="agent_data <?if($cur_agent_set){
                                                            ?>active" data-uid="<?=$cur_data['AGENT_ID'];?>"><div class="agent_name"><?=$arResult['AGENTS_LIST'][$cur_data['AGENT_ID']]?></div><?
                                                        }else{
                                                            ?>"><div class="agent_name"></div><?
                                                        }?>
                                                        <div class="agent_unlink_but" title="Удалить агента"></div>
                                                    </div>
                                                    <div class="agent_select<?if(!$cur_agent_set){?> active<?}?>">
                                                        <select <?if(count($arResult['AGENTS_LIST']) > 4){?>data-search="y"<?}?> name="agent_id">
                                                            <option value="0">Не выбран</option>
                                                            <?foreach($arResult['AGENTS_LIST'] as $cur_agent_id => $cur_agent_name){?>
                                                                <option value="<?=$cur_agent_id;?>"><?=$cur_agent_name;?></option>
                                                            <?}?>
                                                        </select>
                                                    </div>
                                                    <div class="agent_control_select">
                                                        <div class="radio_group">
                                                            <?$my_c = 0; foreach($arResult['AGENTS_CONTROL_LIST'] as $cur_control_data){?>
                                                                <div class="radio_area">
                                                                    <input type="radio" <?if((!$cur_agent_set || $cur_data['AGENT_CONTROL'] == '') && $my_c == 0 || $cur_agent_set && $cur_control_data['ID'] == $cur_data['AGENT_CONTROL']){?>checked="checked" <?}?> name="control_agent" data-text="<?=$cur_control_data['VALUE'];?>" value="<?=$cur_control_data['ID'];?>" />
                                                                </div>
                                                            <?$my_c++;}?>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="sub_row">
                                                    <input class="submit-btn" data-action="save_agent" value="Сохранить данные агента" type="button" />
                                                </div>

                                            </div>
                                        <?}?>
                                    </div>
                                </form>
                            </div>
                            <?
                        }
                        else
                        {
                            ?>

                                <div class="line_area not_activated">
                                    <div class="line_inner">
                                        <div class="inner_text"><span class="email_val"><?=$cur_data['EMAIL']?></span> (статус: аккаунт неактивирован) <a href="<?=$cur_dir;?>?resend=<?=$cur_id;?>">Отправить приглашение повторно</a></div>
                                        <div class="clear"></div>
                                    </div>
                                </div>
                        <?
                        }
                    }
                }?>
            </div>
        <?}else{?>
            <div>Нет привязанных поставщиков</div>
        <?}?>
    </div>
<?}