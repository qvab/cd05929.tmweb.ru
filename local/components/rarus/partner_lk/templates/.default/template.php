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

if($arResult['ERROR'] == 'Y')
{
    echo '<div class="err_text row">' . $arResult['ERROR_MESSAGE'] . '</div>';
}
else
{
    if($arResult['MESS_STR'] != '')
    {
        echo '<div class="err_text row">' . $arResult['MESS_STR'] . '</div>';
    }
    ?>

    <div class="connected_users_list">
        <?if(count($arResult['USERS_LIST']) > 0)
        {
            ?><div class="row_head">Приглашенные пользователи:</div><?
            $cur_dir = $APPLICATION->GetCurDir(false);
            foreach($arResult['USERS_LIST'] as $cur_id => $cur_data)
            {
                if(isset($cur_data['EMAIL']))
                {
                    if($cur_data['ACTIVE'] == 'Y')
                    {
                        ?><div class="item"><div class="val"><?=$cur_data['EMAIL']?> (статус: аккаунт активирован)<?if(isset($cur_data['LINK_DOC']) && $cur_data['LINK_DOC'] == 'n'){?> Внимание! Для завершения прикрепления к поставщику требуется прикрепить договор.
                                <form class="" action="" method="POST" enctype="multipart/form-data">
                                    <input name="add_doc" value="y" type="hidden" />
                                    <input name="uid" value="<?=$cur_id;?>" type="hidden" />
                                    <div class="row">
                                        <div class="holder row_sub_head">Файл договора:</div>
                                        <div class="holder row_val">
                                            <input type="file" name="doc_val" />
                                            <div class="sub_row">
                                                <input type="text" name="doc_num" />
                                            </div>
                                            <div class="sub_row">
                                                <input type="text" name="doc_date" />
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            <?}?><div data-id="<?=$cur_id;?>" class="unlink<?if(isset($check_unclosed_deals_ids[$cur_id])){?> disabled<?}?>" title="Отвязаться от поставщика"></div></div></div><?
                    }
                    else
                    {
                        ?><div class="item"><?=$cur_data['EMAIL']?> (статус: аккаунт  неактивирован) <a href="<?=$cur_dir;?>?resend=<?=$cur_id;?>">Отправить приглашение повторно</a></div><?
                    }
                }
            }
        }?>
    </div>
<?}