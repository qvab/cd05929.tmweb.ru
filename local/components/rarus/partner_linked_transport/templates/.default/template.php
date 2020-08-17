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
        if (count($arResult['USERS_LIST']) > 0) {
        ?>
            <div class="row_head"><?=$arParams['SUB_HEAD'];?>:</div>
            <div class="list_page_rows ">
                <?
                $cur_dir = $APPLICATION->GetCurDir(false);
                foreach ($arResult['USERS_LIST'] as $cur_id => $cur_data) {
                    if (isset($cur_data['EMAIL'])) {
                        if ($cur_data['ACTIVE'] == 'Y') {
                        ?>
                            <div class="line_area">
                                <div class="line_inner">
                                    <div class="inner_text">
                                        <span class="email_val"><?=$cur_data['COMPANY_NAME']?></span> (статус: аккаунт активирован)
                                    </div>
                                    <div title="Отвязаться от транспортной компании" data-uid="<?=$cur_id;?>" class="unlink_but<?if(isset($arResult['UNCOMPLETE_DEALS_IDS'][$cur_id])){?> disabled<?}?>"></div>
                                    <div class="clear"></div>
                                </div>
                                <form action="" method="post" class="line_additional" enctype="multipart/form-data">
                                    <div class="prop_area">
                                        <div class="radio_group">
                                            <?php
                                            if($cur_data['VERIFIED'] == 'no'){
                                                ?>
                                                <div class="radio_area">
                                                    <input type="checkbox" data-text="Подтверждено" name="ch_verified" value="yes" style="vertical-align: " />
                                                </div>
                                                <div class="btn_block">
                                                    <input type="button" data-id="<?=$cur_data['LINK_ID']?>" name="verified_btn" class="submit-btn" value="Сохранить">
                                                </div>
                                                <?
                                            }
                                            ?>
                                        </div>
                                        <a href="/profile/?uid=<?=$cur_id;?>">Перейти в профиль компании</a>
                                    </div>
                                </form>
                            </div>
                            <?
                        }
                        else {
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
                }
                ?>
            </div>
        <?
        }
        else {
        ?>
            <div>Нет привязанных транспортных компаний</div>
        <?
        }
        ?>
    </div>
<?
}
?>