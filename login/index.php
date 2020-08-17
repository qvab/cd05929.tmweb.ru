<?
define("NEED_AUTH", true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

if (isset($_REQUEST["backurl"]) && strlen($_REQUEST["backurl"])>0) 
	LocalRedirect($backurl);

$APPLICATION->SetTitle("Вход на сайт");

global $USER;
require_once($_SERVER["DOCUMENT_ROOT"] . '/include/permission.php');
if ($GLOBALS['rrs_user_perm_level'] == 'c' && $curDir != 'client') {
    LocalRedirect('/client/');
}
elseif ($GLOBALS['rrs_user_perm_level'] == 'f' && $curDir != 'farmer') {
    LocalRedirect('/farmer/');
}
elseif ($GLOBALS['rrs_user_perm_level'] == 't' && $curDir != 'transport') {
    LocalRedirect('/transport/');
}
elseif ($GLOBALS['rrs_user_perm_level'] == 'p' && $curDir != 'partner') {
    LocalRedirect('/partner/');
}
elseif ($GLOBALS['rrs_user_perm_level'] == 'rm' && $curDir != 'regional_managers' && $curDir != 'partner') {
    LocalRedirect('/regional_managers/');
}
?>
<p class="notetext">Вы зарегистрированы и успешно авторизовались.</p>

<p><br/><a href="/">Перейти в раздел пользователя</a></p>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>