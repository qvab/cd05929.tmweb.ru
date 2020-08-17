<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arResult['CLIENTS_DATA'] = array();

//Получение данных связанных покупателей
$non_logged_ids = array();
$user_ids = array();
$user_obj = new CUser;
$clients_rights = array();

foreach($arResult['ITEMS'] as $cur_item){
    $user_ids[$cur_item['PROPERTIES']['USER_ID']['VALUE']] = $cur_item['PROPERTIES']['CLIENT_NICKNAME']['VALUE'];
}

if(count($user_ids) > 0){
    global $USER;
    $agent_obj = new agent();

    //проверка наличия активных сделок
    $clients_deals = deal::getUsersActiveDeals(false, array_keys($user_ids));

    $res = $user_obj->GetList(
        ($by    = 'ID'),
        ($order = 'ASC'),
        array(
            'ID' => implode(' | ', array_keys($user_ids))
        ),
        array('FIELDS' => array('ID', 'NAME', 'LAST_NAME', 'EMAIL'), 'SELECT' => array('UF_DEMO', 'UF_FIRST_PHONE', 'UF_FIRST_LOGIN'))
    );
    while($data = $res->Fetch()){
        $arResult['CLIENTS_DATA'][$data['ID']] = array(
            'NAME'          => trim($data['NAME'] . ' ' . $data['LAST_NAME']),
            'EMAIL'         => $data['EMAIL'],
            'UF_DEMO'       => $data['UF_DEMO'],
            'NICK'          => '',
            'UF_FIRST_LOGIN' => (intval($data['UF_FIRST_LOGIN']) == 1 ? true : false),
            'UF_FIRST_PHONE' => (intval($data['UF_FIRST_PHONE']) == 1 ? true : false),
        );
        if(isset($user_ids[$data['ID']])){
            $arResult['CLIENTS_DATA'][$data['ID']]['NICK'] = $user_ids[$data['ID']];
        }
    }

    //получение данных заполненности обязательных полей профиля
    $agentObj = new agent();
    $arResult['CLIENTS_PROFILE_DONE'] = $agentObj->getClientsRegistrationRights(array_keys($user_ids));
}