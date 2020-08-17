<?
global $USER;
if (isset($_GET['dkey']) && $_GET['dkey'] != '') {
    $arUser = Users::getUserByApiKey($_GET['dkey']);
    if ($arUser['ID'] > 0) {
        $USER->Authorize($arUser['ID']);
    }
    $url = $APPLICATION->GetCurPage();

    unset($_GET['dkey']);
    if(isset($_GET) && is_array($_GET) && count($_GET) > 0){
        $url .= '?' . http_build_query($_GET);
    }
    LocalRedirect($url);
    exit;
}

//массив разрешенных публичных страниц (по умолчанию доступны для всех типов пользователей)
$allowed_public_pages = array(
    'pair_page' => true,
    'send_offer_page' => true,
    'partner_offer_page' => true,
);

//проверка обязательного перенаправления (прямая ссылка)
global $APPLICATION;
if(isset($_GET['spec_href'])
    && count($_GET['spec_href']) == 1
    && $APPLICATION->GetCurDir(false) == '/'
){
    $href_data = getStraightHrefDataByCode($_GET['spec_href']);
    setrawcookie('spec_href', $_SERVER['SCRIPT_URI'] . '?' . $_SERVER['QUERY_STRING'], time() + 3600, '/' );
    //отрабатываем данные приямой ссылки, если они есть
    if(isset($href_data['URL'])){
        workStraightHref($href_data);
    }
}
//проверяем не получил ли пользователь прямую ссылку на завершение авторизации (в этом случае если он авторизован разлогиниваем)
else{
    // http://dmitrd.agrohelper.old.rrsdev.ru/?change_password=yes&lang=ru&USER_CHECKWORD=7eb15529444c61487c3c14f90f772ec0&USER_LOGIN=p77854421848@agrohelper.ru&invite_by_agent=y&backurl=%2Fclient%2Fexclusive_offers%2F%3Fwarehouse_id%3D213606%26culture_id%3D96%26r%3D215856%26o%3D211332
    if(isset($_GET['change_password'])
        && isset($_GET['USER_CHECKWORD'])
        && isset($_GET['backurl'])
        && isset($_GET['invite_by_agent'])
        && $USER->IsAuthorized()
    ){
        $USER->LogOut();
    }
}

$user_groups = CUser::GetUserGroup($USER->GetID());
if (in_array(getGroupIdByRole('p'), $user_groups))
    $GLOBALS['rrs_user_perm_level'] = 'p';
elseif (in_array(getGroupIdByRole('t'), $user_groups))
    $GLOBALS['rrs_user_perm_level'] = 't';
elseif (in_array(getGroupIdByRole('f'), $user_groups))
    $GLOBALS['rrs_user_perm_level'] = 'f';
elseif (in_array(getGroupIdByRole('c'), $user_groups))
    $GLOBALS['rrs_user_perm_level'] = 'c';
/*elseif (in_array(getGroupIdByRole('ag'), $user_groups))
    $GLOBALS['rrs_user_perm_level'] = 'ag'; //агент поставщика
elseif (in_array(getGroupIdByRole('agc'), $user_groups))
    $GLOBALS['rrs_user_perm_level'] = 'agc'; //агент покупателя*/
elseif (in_array(getGroupIdByRole('rm'), $user_groups))
    $GLOBALS['rrs_user_perm_level'] = 'rm'; // региональный манагер
elseif (in_array(getGroupIdByRole('a'), $user_groups))
    $GLOBALS['rrs_user_perm_level'] = 'a';
else {
    $GLOBALS['rrs_user_perm_level'] = 'u';
    if($USER->IsAuthorized()){
        $USER->LogOut();
    }
}

$curDir = next(explode('/', $APPLICATION->GetCurPage()));


//check backurl
$check_back = '';
if(isset($_REQUEST['backurl']) && trim($_REQUEST['backurl']) != '')
{
    $check_back = next(explode('/', $_REQUEST['backurl']));
}

if (!isset($allowed_public_pages[$curDir])) {
    if ($GLOBALS['rrs_user_perm_level'] == 'c' && $curDir != 'client' && $curDir != 'profile') {
        //дописываем параметры для ссылки на встречное предложение от агента
        if ($check_back == 'client'
            && isset($_GET['culture_id'])
            && is_numeric($_GET['culture_id'])
            && isset($_GET['o'])
            && is_numeric($_GET['o'])
            && isset($_GET['r'])
            && is_numeric($_GET['r'])
        ) {
            $_REQUEST['backurl'] .= '&culture_id=' . $_GET['culture_id']
                . '&o=' . $_GET['o']
                . '&r=' . $_GET['r'];
        }
        LocalRedirect($check_back == 'client' ? $_REQUEST['backurl'] : '/client/exclusive_offers/');
    } elseif ($GLOBALS['rrs_user_perm_level'] == 'f' && $curDir != 'farmer' && $curDir != 'profile') {

        //объем дописываем, если требуется
        if (isset($_GET['vol'])
            && is_numeric($_GET['vol'])
            && $check_back == 'farmer'
        ) {
            $_REQUEST['backurl'] .= '&vol=' . $_GET['vol'];
        }
        LocalRedirect($check_back == 'farmer' ? $_REQUEST['backurl'] : '/farmer/');
    } elseif ($GLOBALS['rrs_user_perm_level'] == 't' && $curDir != 'transport' && $curDir != 'profile') {
        LocalRedirect($check_back == 'transport' ? $_REQUEST['backurl'] : '/transport/');
    } elseif ($GLOBALS['rrs_user_perm_level'] == 'p' && $curDir != 'partner' && $curDir != 'profile') {
        LocalRedirect($check_back == 'partner' ? $_REQUEST['backurl'] : '/partner/');
    } /*elseif ($GLOBALS['rrs_user_perm_level'] == 'ag' && $curDir != 'agent' && $curDir != 'profile') {
    LocalRedirect($check_back == 'agent' ? $_REQUEST['backurl'] : '/agent/');
}
elseif ($GLOBALS['rrs_user_perm_level'] == 'agc' && $curDir != 'client_agent' && $curDir != 'profile') {
    LocalRedirect($check_back == 'client_agent' ? $_REQUEST['backurl'] : '/client_agent/');
}*/
    elseif ($GLOBALS['rrs_user_perm_level'] == 'rm' && $curDir != 'regional_managers' && $curDir != 'profile') {
        LocalRedirect($check_back == 'regional_managers' ? $_REQUEST['backurl'] : '/regional_managers/');
    }


    if (defined('PUBLIC_AREA')) {
        if ($GLOBALS['rrs_user_perm_level'] == 'a' && defined('PUBLIC_AREA')) {
            LocalRedirect('/admin/');
            exit;
        }
    } elseif ($GLOBALS['rrs_user_perm_level'] == 'u') {
        //redirect public user to public area
        //LocalRedirect('/?backurl=' . $APPLICATION->GetCurDir() . '#action=auth');
        LocalRedirect('/?backurl=' . $APPLICATION->GetCurPageParam() . '#action=auth');
        exit;
    }
}

if (sizeof(array_intersect($user_groups, array(11, 9))) > 0) {
    $arUser = rrsIblock::getUserInfo($USER->GetID());
    if ($arUser['UF_DEMO'] == 1) {
        $GLOBALS['DEMO'] = 'Y';
    }
}
?>