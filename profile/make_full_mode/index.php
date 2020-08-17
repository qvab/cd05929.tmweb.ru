<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

if (!$USER->IsAuthorized()
    || !isset($_GET['uid'])
    || !is_numeric($_GET['uid'])
) {
    LocalRedirect('/');
    exit;
}

$u_obj = new CUser;

//проверка если пользователь не в демо режиме
/*$res = $u_obj->GetList(($by='id'), ($order='asc'),
    array('ID' => $_GET['uid'], 'UF_DEMO' => 1),
    array('FIELDS' => array('ID'))
);
if ($res->SelectedRowsCount() == 0) {
    LocalRedirect('/profile/?uid=' . $_GET['uid']);
    exit;
}*/

$group = getUserType($_GET['uid']);
?>
<h1><?$APPLICATION->ShowTitle();?></h1>

<?
$linked_user = $APPLICATION->IncludeComponent(
    'rarus:public_profile_menu',
    '',
    Array(
        'U_ID' => $_GET['uid'],
        'TYPE' => $group['TYPE'],
        'TAB' => 'make_full_mode'
    )
);?>
<?
//проверка на привязку пользователя (устанавливается чеерз компоненту меню)
if (!$linked_user) {
    LocalRedirect('/profile/?uid=' . $_GET['uid']);
    exit;
}

switch($group['TYPE']){
    case 'f':
        $APPLICATION->IncludeComponent(
            "rarus:farmer_profile_demo",
            ".default",
            Array(
                'U_ID' => $_GET['uid'],
                'EDIT_PROPS_LIST' => array(
                    'PHONE',
                    'REGION',
                    'UL_TYPE',
                    'INN',
                    'FULL_COMPANY_NAME',
                    'IP_FIO',
                    'REG_DATE',
                    'YUR_ADRESS',
                    'POST_ADRESS',
                    'KPP',
                    'OGRN',
                    'OKPO',
                    'FIO_DIR',
                    'OSNOVANIE_PRAVA_PODPISI_FILE',
                    'NDS',
                    'BANK',
                    'BIK',
                    'RASCH_SCHET',
                    'KOR_SCHET'
                ),
                'BY_AGENT' => 'Y'
            ),
            false
        );

        break;

    case 'c':
        $APPLICATION->IncludeComponent(
            "rarus:client_profile_demo",
            ".default",
            Array(
                'U_ID' => $_GET['uid'],
                'EDIT_PROPS_LIST' => array(
                    'PHONE',
                    'REGION',
                    'UL_TYPE',
                    'INN',
                    'FULL_COMPANY_NAME',
                    'IP_FIO',
                    'REG_DATE',
                    'YUR_ADRESS',
                    'POST_ADRESS',
                    'KPP',
                    'OGRN',
                    'OKPO',
                    'FIO_DIR',
                    'OSNOVANIE_PRAVA_PODPISI_FILE',
                    'NDS',
                    'BANK',
                    'BIK',
                    'RASCH_SCHET',
                    'KOR_SCHET'
                ),
                'BY_AGENT' => 'Y'
            ),
            false
        );

        break;
}
    ?>

    <div class="content-form policy_page public_form">
        <div class="close" onclick="showAgentPolicy();"></div>
        <div class="fields text_page_area">
            <?$APPLICATION->IncludeComponent("bitrix:main.include", "",
                array(
                    "AREA_FILE_SHOW" => "file",
                    "PATH" => "/include/policy.php"),
                false
            );?>
        </div>
    </div>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>