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
        if(count($arResult['USERS_LIST']) > 0) {
        ?>
            <div class="row_head"><?=$arParams['SUB_HEAD'];?>:</div>
            <div class="list_page_rows ">
                <?

                $cur_dir = $APPLICATION->GetCurDir(false);
                foreach ($arResult['USERS_LIST'] as $cur_id => $cur_data) {
                    if(isset($cur_data['EMAIL']))
                    {
                        if($cur_data['ACTIVE'] == 'Y')
                        {
                            //get document data if exist
                            if(isset($cur_data['LINK_DOC']) && is_numeric($cur_data['LINK_DOC']))
                            {
                                $temp_res = CFile::GetByID($cur_data['LINK_DOC']);
                                $file_data = array();
                                if($file_data = $temp_res->Fetch())
                                {
                                    $temp_path = CFile::GetPath($cur_data['LINK_DOC']);
                                    if($temp_path)
                                    {
                                        $file_data['f_src'] = $temp_path;
                                    }
                                }
                            }
                            ?>
                            <div class="line_area">
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
                                        if (isset($cur_data['LINK_DOC']) && $cur_data['LINK_DOC'] == 'n') {
                                        ?>
                                            <div class="red_text">Внимание! Для завершения прикрепления к поставщику требуется прикрепить договор.</div>
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
                                    <div class="prop_area">
                                        <a href="/profile/?uid=<?=$cur_id;?>">Перейти в профиль компании</a>
                                        <div class="sub_row"></div>
                                        <?if(isset($cur_data['LINK_DOC']) && $cur_data['LINK_DOC'] == 'n'){?>
                                            <div class="row_sub_head">Файл договора:</div>
                                            <div class="sub_row">
                                                <input type="file" name="doc_val" />
                                            </div>
                                            <div class="sub_row">
                                                <input type="text" autocomplete="off" name="doc_num" placeholder="Номер договора" />
                                            </div>
                                            <div class="sub_row">
                                                <input type="text" autocomplete="off" name="doc_date" onclick="var tmp_val = this.value; this.value = ''; BX.calendar({value: tmp_val, node: this, field: this, bTime: false, callback_after: goTriggerChange(this)});" placeholder="Дата договора" />
                                            </div>
                                            <div class="sub_row">
                                                <input type="submit" class="submit-btn inactive" value="Отправить" />
                                            </div>
                                        <?}else{?>
                                            <div class="row_sub_head">Файл договора:</div>
                                            <div class="sub_row">
                                                <?if(isset($file_data['f_src']) && $file_data['f_src'] != ''){?>
                                                    <a href="<?=$file_data['f_src'];?>" download="<?=$file_data['ORIGINAL_NAME'];?>">Скачать</a>
                                                <?}else{?>
                                                    Возникла ошибка с загруженным документом. Обратитесь к администрации.
                                                <?}?>
                                            </div>
                                            <div class="sub_row">
                                                <input type="text" readonly="readonly" name="doc_num" placeholder="Номер договора" value="<?if(isset($cur_data['LINK_DOC_NUM'])) echo $cur_data['LINK_DOC_NUM'];?>" />
                                            </div>
                                            <div class="sub_row">
                                                <input type="text" readonly="readonly" name="doc_date" placeholder="Дата договора" value="<?if(isset($cur_data['LINK_DOC_DATE'])) echo $cur_data['LINK_DOC_DATE'];?>" />
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