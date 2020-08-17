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

CModule::IncludeModule('iblock');
$el_obj = new CIBlockElement;
if($arResult['ERROR'] != 'Y')
{
    $arResult['success_text'] = array();
    $arResult['NDS_LIST'] = rrsIblock::getElementList(rrsIblock::getIBlockId('nds_list'), 'ID');
    if(isset($_POST['add_farmer']) && $_POST['add_farmer'] == 'y')
    {
        if(isset($_POST['nds_value']) && isset($arResult['NDS_LIST'][$_POST['nds_value']]))
        {
            if(isset($_POST['login']) && trim($_POST['login']) != ''
                || isset($_POST['by_phone']) && isset($_POST['phone']) && $_POST['phone'] != ''
            )
            {
                $new_login = trim(Cutil::translit($_POST['login'], 'ru'));
                $new_email = $new_login . '@agrohelper.ru';
                $nick = '';
                $phone = '';
                if(isset($_POST['nick']) && trim($_POST['nick']) != ''){
                    $nick = trim($_POST['nick']);
                }
                if(isset($_POST['by_phone']) && isset($_POST['phone']) && $_POST['phone'] != ''){
                    $new_email = makeEmailFromPhone($_POST['phone']);
                    $phone = $_POST['phone'];
                }

                //проверка телефона
                if($phone != ''
                    && profilePhoneDoubles($phone)
                )
                {
                    $arResult['success_text']['error'] = 'Пользователь с таким телефоном уже есть в системе';
                }

                if(!isset($arResult['success_text']['error'])
                    || $arResult['success_text']['error'] == ''
                ){
                    //проверка нет ли пользователей с такой почтой
                    $user_obj = new CUser;
                    $res = $user_obj->GetList(
                        ($by    = 'id'),
                        ($order = 'asc'),
                        array(
                            'EMAIL' => $new_email
                        ),
                        array('FIELDS' => 'ASC')
                    );
                    if($res->SelectedRowsCount() == 0)
                    {
                        //Добавляем нового пользователя
                        global $USER;
                        $agentObj = new agent();
                        if($agentObj->addFarmerByAgent($USER->GetID(), $new_login, $new_email, $_POST['nds_value'], $nick, $phone) > 0)
                        {
                            LocalRedirect('/partner/users/linked_users/?success=add');
                            exit;
                        }
                        else
                        {
                            $arResult['success_text']['error'] = 'Ошибка добавления пользователя. Возможно организатор был удален';
                        }
                    }
                    else
                    {
                        $arResult['success_text']['error'] = 'Пользователь с таким логином уже есть в системе';
                    }
                }
            }
        }
        else
        {
            $arResult['success_text']['error'] = 'Выберите тип налогообложения "с НДС/без НДС"';
        }
    }
}

$this->IncludeComponentTemplate();

unset($res, $data, $el_obj, $u_obj, $res_val, $arEventFields);