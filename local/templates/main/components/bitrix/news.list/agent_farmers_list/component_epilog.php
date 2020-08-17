<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

//проверка нужно ли формировать строку для приглашения пользователю
if(isset($_GET['get_invite']) && is_numeric($_GET['get_invite']) && $_GET['get_invite'] > 0)
{
    $agent_obj = new agent();
    $invite_href = $agent_obj->getFarmerInviteHref($_GET['get_invite'], $USER->GetID());

    if($invite_href != '')
    {?>
        <script type="text/javascript">
            $(document).ready(function(){
                var inv_uid = '<?=$_GET['get_invite'];?>';
                $('.list_page_rows .line_area[data-uid="' + inv_uid + '"]').addClass('active').find('.line_additional').show().find('.additional_submits a[data-val="make_invite"]').after('<div class="invite_href"><?=$invite_href?></div>');
            });
        </script>
    <?}
}

//проверка нужно ли удалить/деактивировать пользователя
if(isset($_GET['deactivate']) && is_numeric($_GET['deactivate']) && $_GET['deactivate'] > 0){
    $_GET['delete'] = $_GET['deactivate'];
}
if(isset($_GET['delete']) && is_numeric($_GET['delete']) && $_GET['delete'] > 0)
{
    CModule::IncludeModule('iblock');
    //Особо не переписываем для быстрого ролл бека
    //farmer::deleteFarmer($_GET['delete'], true);
    farmer::deactivateFarmer($_GET['delete']);

    global $APPLICATION;
    $redirect_link = $APPLICATION->GetCurDir();
    if(isset($_GET['deactivate'])){
        $redirect_link .= '?success=deactivated';
    }else{
        $redirect_link .= '?success=deleted';
    }
    LocalRedirect($redirect_link);
    exit;
}