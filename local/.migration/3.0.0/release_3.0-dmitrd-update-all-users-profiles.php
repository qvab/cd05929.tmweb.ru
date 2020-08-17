<?php

//обновленте данных телефонов всех пользователей (и создание ключей для авторизации по телефону вместо email из приложения)


// Подключение Битрикса
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

// Проверка на админа
if (!$USER->IsAdmin())
    die();

// Подключаем необходимые модули
if (!CModule::IncludeModule("iblock")) {
    die('Module "iblock" not found!');
}

$uid = 656;
$el_obj = new CIBlockElement;
$u_obj = new CUser;

$profiles_ib_arr = array(
    'client_profile'    => rrsIblock::getIBlockId('client_profile'),
    'farmer_profile'    => rrsIblock::getIBlockId('farmer_profile'),
    'partner_profile'   => rrsIblock::getIBlockId('partner_profile'),
    'transport_profile' => rrsIblock::getIBlockId('transport_profile'),
    'agent_profile'     => rrsIblock::getIBlockId('agent_profile'),
    'client_agent_profile' => rrsIblock::getIBlockId('client_agent_profile')
);

$data_val = ConvertTimeStamp(false, 'FULL');

$run_date = date('Y_m_d H_i_s');
file_put_contents($_SERVER["DOCUMENT_ROOT"] . '/local/.migration/3.0.0/release_3.0-dmitrd-update-all-users-profiles ' . $run_date . '.csv', iconv("UTF-8", "cp1251", "User id;Phone from;Phone to;New mobile key\n"));

$start_time = time();
$user_pass_sha = array();
$my_c = 0;
$my_b = 0;

//получаем данные пользователей
$res = $u_obj->GetList(
    ($by = 'id'), ($order = 'asc'),
    array('UF_API_KEY_M' => false, '!UF_SHA1' => false),
    array('FIELDS' => array('ID'), 'SELECT' => array('UF_SHA1'))
);
while($data = $res->Fetch()){
    $user_pass_sha[$data['ID']] = $data['UF_SHA1'];
}

//обновляем данные профилей
foreach($profiles_ib_arr as $cur_ib){
    $res = $el_obj->GetList(
        array('ID' => 'ASC'),
        array('IBLOCK_ID' => $cur_ib, '!PROPERTY_PHONE' => false, '!PROPERTY_USER' => false),
        false,
        false,
        array('ID', 'IBLOCK_ID', 'PROPERTY_PHONE', 'PROPERTY_USER')
    );
    while($data = $res->Fetch()){

        if(!isset($user_pass_sha[$data['PROPERTY_USER_VALUE']]))
            continue;

        $new_phone = makeCorrectPhone($data['PROPERTY_PHONE_VALUE']);

        if($new_phone != '' &&
            $new_phone != $data['PROPERTY_PHONE_VALUE']
        ){
            $gen_key = Agrohelper::hashApiKey(getPhoneDigits($new_phone), $user_pass_sha[$data['PROPERTY_USER_VALUE']]);

            file_put_contents($_SERVER["DOCUMENT_ROOT"] . '/local/.migration/3.0.0/release_3.0-dmitrd-update-all-users-profiles ' . $run_date . '.csv',
                iconv("UTF-8", "cp1251", "{$data['PROPERTY_USER_VALUE']};{$data['PROPERTY_PHONE_VALUE']};{$new_phone};{$gen_key}\n"), FILE_APPEND);

            $el_obj->SetPropertyValuesEx($data['ID'], $data['IBLOCK_ID'], array('PHONE' => makeCorrectPhone($data['PROPERTY_PHONE_VALUE'])));
            $u_obj->Update($data['PROPERTY_USER_VALUE'], array('UF_API_KEY_M' => $gen_key));

            $my_c++;
        }
    }
}

echo 'Обновлено ' . $my_c . ' записи профилей ключей мобильной авторизации пользователей за ' . (time() - $start_time) . ' сек';