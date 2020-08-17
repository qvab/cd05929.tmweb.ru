<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

//проверка нужно ли удалить/деактивировать пользователя
if(isset($_GET['deactivate']) && is_numeric($_GET['deactivate']) && $_GET['deactivate'] > 0){
    $_GET['delete'] = $_GET['deactivate'];
}
if(isset($_GET['delete']) && is_numeric($_GET['delete']) && $_GET['delete'] > 0)
{
    CModule::IncludeModule('iblock');
    //client::deleteClient($_GET['delete'], true);
    farmer::deactivateFarmer($_GET['delete']);

    global $APPLICATION;
    $redirect_link = '';
    if(isset($_GET['deactivate'])){
        $redirect_link = $APPLICATION->GetCurPageParam('success=deactivated', array('success', 'deactivate', 'delete'));
    }else{
        $redirect_link = $APPLICATION->GetCurPageParam('success=deleted', array('success', 'deactivate', 'delete'));
    }
    LocalRedirect($redirect_link);
    exit;
}