<?
switch($GLOBALS['rrs_user_perm_level'])
{
    case 'p':
        require_once($_SERVER['DOCUMENT_ROOT'] . '/partner/.top.menu.php');
        break;
    case 't':
        require_once($_SERVER['DOCUMENT_ROOT'] . '/transport/.top.menu.php');
        break;
    case 'f':
        require_once($_SERVER['DOCUMENT_ROOT'] . '/farmer/.top.menu.php');
        break;
    case 'c':
        require_once($_SERVER['DOCUMENT_ROOT'] . '/client/.top.menu.php');
        break;
    case 'ag':
        require_once($_SERVER['DOCUMENT_ROOT'] . '/agent/.top.menu.php');
        break;
    case 'agc':
        require_once($_SERVER['DOCUMENT_ROOT'] . '/client_agent/.top.menu.php');
        break;
}
?>