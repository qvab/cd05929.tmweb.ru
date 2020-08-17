<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

//определяем тип работы (восстановление по телефону, по email, либо пользователь перешёл по ссылке от агента)
$arResult['WORK_MODE'] = 'change';
if(isset($_GET['type']) && $_GET['type'] == 'phone'){
    $arResult['WORK_MODE'] = 'phone'; //восстановление по номеру телефона
}
if(isset($_GET['invite_by_agent']) && $_GET['invite_by_agent'] == 'y'){
    $arResult['WORK_MODE'] = 'by_agent'; //ссылка регистрации от агента/организатора
    $arResult['PHONE'] = '';
    $arResult['EMAIL'] = '';

    //получение телефона и email пользователя
    if(isset($_GET['USER_LOGIN'])){
        $u_login = trim($_GET['USER_LOGIN']);
        if($u_login != ''){
            $u_obj = new CUser;
            $res = $u_obj->GetList(
                ($by = 'id'), ($order = 'asc'),
                array(
                    'LOGIN' => $u_login,
                    'UF_FIRST_PHONE' => 1,
                    'UF_AGENT_ADDED' => 1
                ),
                array('FIELDS' => array('ID', 'EMAIL'))
            );
            if($data = $res->Fetch()){
                if($data['EMAIL'] != makeEmailFromPhone(preg_replace('/[^0-9]/s', '', $data['EMAIL']))) {
                    //проверяем, что email был сделан не из телефона
                    $arResult['EMAIL'] = $data['EMAIL'];
                }

                //получение телефона пользователя, если он был заполнен
                //определяем группу пользователя
                $ar_groups = $u_obj->GetUserGroup($data['ID']);
                $ar_groups = array_flip($ar_groups);

                if(isset($ar_groups['9'])
                    || isset($ar_groups['11'])
                ){

                    CModule::IncludeModule('iblock');
                    $el_obj = new CIBlockElement;

                    $arFilter = array(
                        'PROPERTY_USER' => $data['ID'],
                        'ACTIVE' => 'Y'
                    );

                    if(isset($ar_groups['9'])){
                        //ищем среди покупателей
                        $arFilter['IBLOCK_ID'] = rrsIblock::getIBlockId('client_profile');
                    }else{
                        //ищем среди поставщиков
                        $arFilter['IBLOCK_ID'] = rrsIblock::getIBlockId('farmer_profile');
                    }

                    $res = $el_obj->GetList(
                        array('ID' => 'ASC'),
                        $arFilter,
                        false,
                        false,
                        array('PROPERTY_PHONE')
                    );
                    if ($data = $res->Fetch()) {
                        $arResult['PHONE'] = $data['PROPERTY_PHONE_VALUE'];
                    }
                }
            }
        }
    }
}

