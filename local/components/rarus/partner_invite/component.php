    <?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var string $componentPath */
/** @var string $componentName */
/** @var string $componentTemplate */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

/** @global CIntranetToolbar $INTRANET_TOOLBAR */
global $INTRANET_TOOLBAR;

use Bitrix\Main\Context,
    Bitrix\Main\Type\DateTime,
    Bitrix\Main\Loader,
    Bitrix\Iblock;

if(!isset($arParams['U_ID']) || !is_numeric($arParams['U_ID']))
{
    $arResult['ERROR'] = 'Y';
    $arResult['ERROR_MESSAGE'] = 'Не указан ID пользователя.';
}

$el_obj = new CIBlockElement;
if($arResult['ERROR'] != 'Y')
{
    $arResult['error_text'] = '';
        $arResult['success_text'] = array();
    if(isset($_POST['send_invite']) && $_POST['send_invite'] == 'y')
    {
        $u_obj = new CUser;
        if(!isset($_POST['user_type']) || (!in_array($_POST['user_type'], array(9, 11, 12, 13, 14))))
        {
            $arResult['error_text'] = 'Не выбран тип пользователя';
        }

        if(!isset($_POST['AUTH_REG_CONFIM_BY_PARTNER']) || $_POST['AUTH_REG_CONFIM_BY_PARTNER'] != 'Y'){
            $arResult['error_text'] = 'Вы не отметили подтверждение, что предоставление персональных данных на третьих лиц производится с их согласия';
        }        

        if($arResult['error_text'] == ''
            && (!isset($_POST['email']) || !check_email($_POST['email']))
        )
        {
            $arResult['error_text'] = 'Ошибка в указанном email';
        }

        if ($arResult['error_text'] == '' && !$_POST['send_sms'] && !$_POST['send_email']) {
            $arResult['error_text'] = 'Не указан способ оповещения пользователя';
        }

        if ($arResult['error_text'] == '' && $_POST['send_sms'] == 'Y' && !$_POST['phone']) {
            $arResult['error_text'] = 'Не указан телефон пользователя';
        }

        if($arResult['error_text'] == '')
        {//check email double
            $res = $u_obj->GetList(($by="personal_country"), ($order="desc"), array('EMAIL' => $_POST['email']), array('SELECT' => array('UF_HASH_INVITE')));
            if($res->SelectedRowsCount() > 0)
            {
                if($data = $res->Fetch())
                {
                    if($data['UF_HASH_INVITE'] && trim($data['UF_HASH_INVITE']) != '')
                    {
                        if ($_POST['user_type'] == 9)
                            $url = '/partner/users/linked_clients/';
                        elseif ($_POST['user_type'] == 11)
                            $url = '/partner/users/linked_users/';
                        elseif ($_POST['user_type'] == 12)
                            $url = '/partner/users/linked_transport/';
                        elseif ($_POST['user_type'] == 13)
                            $url = '/partner/users/linked_agents/';
                        elseif ($_POST['user_type'] == 14)
                            $url = '/partner/users/linked_client_agents/';
                        $arResult['error_text'] = 'Ошибка! На данный email уже отправлено приглашение (повторное приглашение можно отправить <a href="'.$url.'">со страницы со списком привязанных пользователей</a>).';
                    }
                    else
                    {
                        $arResult['error_text'] = 'Ошибка! Данный email уже добавлен в базу';
                    }
                }
            }
        }

        //add user to invite list
        if($arResult['error_text'] == '')
        {
            $password = hashPass(4).'-'.hashPass(12);

            $arFields = array(
                'EMAIL' => $_POST['email'],
                'LOGIN' => $_POST['email'],
                'LID' => 'ru',
                'ACTIVE' => 'N',
                'GROUP_ID' => array(2, $_POST['user_type']),
                'UF_THIRD_PARTY_CONS' => 'Y',
                'PASSWORD'          => $password,
                'CONFIRM_PASSWORD'  => $password
            );

            if (in_array($_POST['user_type'], array(11, 9))) {
                $arFields['UF_DEMO']        = true;
                $arFields['UF_FIRST_PHONE'] = true;
                $arFields['UF_FIRST_LOGIN'] = true;
            }elseif(in_array($_POST['user_type'], array(13, 14))){
                $arFields['UF_FIRST_PHONE'] = true;
                $arFields['UF_FIRST_LOGIN'] = true;
            }

            $ID = $u_obj->Add($arFields);
            if(intval($ID) > 0)
            {
                //set invite hash
                $hash = hashPass(8);
                $fields = array(
                    "UF_HASH_INVITE" => $hash
                );
                $u_obj->Update($ID, $fields);

                $ib_ib = 0;
                switch($_POST['user_type'])
                {
                    case '9':
                        $ib_ib = rrsIblock::getIBlockId('client_profile');
                        $to = 'покупателя';
                        break;

                    case '11':
                        $ib_ib = rrsIblock::getIBlockId('farmer_profile');
                        $to = 'поставщика';
                        break;

                    case '12':
                        $ib_ib = rrsIblock::getIBlockId('transport_profile');
                        $to = 'перевозчика';
                        break;

                    case '13':
                        $ib_ib = rrsIblock::getIBlockId('agent_profile');
                        $to = 'агента поставщика';
                        break;

                    case '14':
                        $ib_ib = rrsIblock::getIBlockId('client_agent_profile');
                        $to = 'агента покупателя';
                        break;
                }

                $url = $GLOBALS['host'] . '/?reg_hash=' . $hash . (strlen($ib_ib) == 2 ? $ib_ib : '0' . $ib_ib) . '#action=register';
                if ($_POST['send_email'] == 'Y') {
                    //send email (add link = hash + iblock id)
                    $arEventFields = array(
                        'EMAIL' => $_POST['email'],
                        'HREF' => $url,
                        'TO' => $to
                    );
                    $res_val = CEvent::Send("AGRO_INVITE_USER", "s1", $arEventFields);

                    $arResult['success_text'][] = 'Приглашение на почту ' . $_POST['email'] . ' отправлено.';
                }

                if ($_POST['send_sms'] == 'Y') {
                    $phone = str_replace(array('+', ' ', '(', ')', '-'), "", $_POST['phone']);
                    notice::sendNoticeSMS($phone, 'Вас пригласили зарегистрироваться в системе Агрохелпер. Пожалуйста, перейдите по ссылке: ' . $url);

                    $arResult['success_text'][] = 'Приглашение на номер ' . $_POST['phone'] . ' отправлено.';
                }

                //set initial values
                if($ib_ib > 0)
                {
                    CModule::IncludeModule('iblock');
                    $arFields = array(
                        'IBLOCK_ID' => $ib_ib,
                        'ACTIVE' => 'Y',
                        'NAME' => 'Свойства пользователя ' . $_POST['email'] . ' с ID [' . $ID . ']',
                        'PROPERTY_VALUES' => array(
                            'USER' => $ID,
                            //'INN' => (isset($_POST['inn']) && is_numeric($_POST['inn']) && (strlen($_POST['inn']) == 10 || strlen($_POST['inn']) == 12) ? $_POST['inn'] : ''),
                            'PHONE' => (isset($_POST['phone']) && $_POST['phone'] != '' ? makeCorrectPhone($_POST['phone']) : ''),
                            'PARTNER_ID' => $arParams['U_ID']
                        )
                    );
                    $el_obj->Add($arFields);
                }
            }
        }
    }
}

//set documents page title
if($arParams['SET_TITLE'] == 'Y') {
    $arProfile = $el_obj->GetList(
        array('ID' => 'ASC'),
        array('IBLOCK_CODE' => 'partner_profile', 'ACTIVE' => 'Y', 'PROPERTY_USER' => $arParams['U_ID']),
        false,
        array('nTopCount' => 1),
        array('PROPERTY_FULL_COMPANY_NAME')
    )->Fetch();
    if($arProfile && isset($arProfile['PROPERTY_FULL_COMPANY_NAME_VALUE']) && trim($arProfile['PROPERTY_FULL_COMPANY_NAME_VALUE']) != '') {
        $APPLICATION->SetTitle($arProfile['PROPERTY_FULL_COMPANY_NAME_VALUE']);
    }
}

$this->IncludeComponentTemplate();

unset($res, $data, $el_obj, $u_obj, $res_val, $arEventFields);