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
    if(isset($_POST['add_client']) && $_POST['add_client'] == 'y')
    {
        if(isset($_POST['nds_value']) && isset($arResult['NDS_LIST'][$_POST['nds_value']]))
        {
            if(isset($_POST['region']) && $_POST['region']){
                $region = trim($_POST['region']);
                if(isset($_POST['PROP__INN']) && $_POST['PROP__INN'] != ''){
                    $inn = trim($_POST['PROP__INN']);

                    $new_email = '';
                    $new_login = '';
                    $nick = '';
                    $phone = '';
                    $double = '';

                    //проверка корректности email
                    if(isset($_POST['email'])
                        && trim($_POST['email']) == ''
                        && !check_email($_POST['email'])
                    ){
                        $arResult['success_text']['error'] = 'Указан некорректный email';
                    }else{
                        //проверка на дублирование пользователя
                        $new_email = trim($_POST['email']);
                        $new_login = $new_email;
                    }

                    //проверка корректности телефона
                    if(isset($_POST['phone'])
                        && trim($_POST['phone']) != ''
                    ) {
                        $phone = trim($_POST['phone']);

                        if($phone == ''
                            || profilePhoneDoubles($phone)
                        )
                        {
                            $double = 'p';
                            $arResult['success_text']['error'] = 'Пользователь с таким телефоном уже есть в системе';
                        }elseif($new_email == ''){
                            $new_email = makeEmailFromPhone($_POST['phone']);
                        }
                    }

                    //регистрация пользователя
                    if($new_email != ''){

                        if(isset($_POST['nick']) && trim($_POST['nick']) != ''){
                            $nick = trim($_POST['nick']);
                        }

                        //проверка нет ли пользователей с такой почтой
                        $user_obj = new CUser;
                        $res = $user_obj->GetList(
                            ($by    = 'id'), ($order = 'asc'),
                            array(
                                'EMAIL' => $new_email
                            ),
                            array('FIELDS' => array('ID', 'EMAIL'))
                        );
                        if($double == '' && $res->SelectedRowsCount() == 0)
                        {
                            //проверка нет ли уже такого ИНН в системе
                            if(!partner::isDoubleProfileInn($inn)) {
                                //заполнение новых данных (включая полученные от контр-фокуса)
                                $additional_data = array(
                                    'FIELDS' => array(
                                        'NAME' => (isset($_POST['name']) ? $_POST['name'] : ''),
                                        'LAST_NAME' => (isset($_POST['last_name']) ? $_POST['last_name'] : ''),
                                        'SECOND_NAME' => (isset($_POST['second_name']) ? $_POST['second_name'] : ''),
                                    ),
                                    'PROPS' => array(
                                        'INN' => $inn,
                                        'REGION' => $region,
                                        'REG_DATE' => (isset($_POST['PROP__REG_DATE']) ? $_POST['PROP__REG_DATE'] : ''),
                                        'IP_FIO' => (isset($_POST['PROP__IP_FIO']) ? $_POST['PROP__IP_FIO'] : ''),
                                        'OGRN' => (isset($_POST['PROP__OGRN']) ? $_POST['PROP__OGRN'] : ''),
                                        'OKPO' => (isset($_POST['PROP__OKPO']) ? $_POST['PROP__OKPO'] : ''),
                                        'UL_TYPE' => (isset($_POST['PROP__UL_TYPE']) ? $_POST['PROP__UL_TYPE'] : ''),
                                        'FULL_COMPANY_NAME' => (isset($_POST['PROP__FULL_COMPANY_NAME']) ? $_POST['PROP__FULL_COMPANY_NAME'] : ''),
                                        'YUR_ADRESS' => (isset($_POST['PROP__YUR_ADRESS']) ? $_POST['PROP__YUR_ADRESS'] : ''),
                                        'KPP' => (isset($_POST['PROP__KPP']) ? $_POST['PROP__KPP'] : ''),
                                        'FIO_DIR' => (isset($_POST['PROP__FIO_DIR']) ? $_POST['PROP__FIO_DIR'] : ''),
                                    )
                                );
                                //Добавляем нового пользователя
                                global $USER;
                                $agentObj = new agent();
                                if ($agentObj->addClientByAgent($USER->GetID(), $new_login, $new_email, $_POST['nds_value'], $nick, $phone, $additional_data) > 0) {
                                    LocalRedirect('/partner/users/linked_clients/?success=add');
                                    exit;
                                } else {
                                    $arResult['success_text']['error'] = 'Ошибка добавления пользователя. Возможно организатор был удален';
                                }
                            }else{
                                $arResult['success_text']['error'] = 'Данный ИНН уже зарегистрирован в системе';
                            }
                        }
                        else
                        {
                            if($double == 'p') {
                                //уже есть пользователь с email сгененированным из веденного телефона
                                $arResult['success_text']['error'] = 'Пользователь с таким телефоном уже есть в системе';
                            }else{
                                $arResult['success_text']['error'] = 'Пользователь с таким email уже есть в системе';
                            }
                        }
                    }
                }else{
                    $arResult['success_text']['error'] = 'Укажите ИНН';
                }
            }
            else{
                $arResult['success_text']['error'] = 'Выберите регион';
            }
        }
        else
        {
            $arResult['success_text']['error'] = 'Выберите тип налогообложения "с НДС/без НДС"';
        }
    }
}

//получаем данные для выбора региона
$res = $el_obj->GetList(
    array('ID' => 'ASC'),
    array(
        'IBLOCK_ID' => rrsIblock::getIBlockId('regions'),
        'ACTIVE' => 'Y'
    ),
    false,
    false,
    array('ID', 'NAME', 'IBLOCK_ID')
);
while ($data = $res->Fetch()) {
    $arResult['REGIONS'][$data['ID']] = $data['NAME'];
}

$this->IncludeComponentTemplate();

unset($res, $data, $el_obj, $u_obj, $res_val, $arEventFields);