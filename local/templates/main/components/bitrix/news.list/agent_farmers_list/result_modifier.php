<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arResult['FARMERS_DATA'] = array();
$arResult['RIGHTS_LIST'] = rrsIblock::getPropListId('farmer_agent_link', 'AGENT_RIGHTS');

//Получение данных связанных поставщиков
$non_logged_ids = array();
$user_ids = array();
$user_obj = new CUser;

foreach($arResult['ITEMS'] as $cur_item){
    $user_ids[$cur_item['PROPERTIES']['USER_ID']['VALUE']] = $cur_item['PROPERTIES']['FARMER_NICKNAME']['VALUE'];
}

if(count($user_ids) > 0){
    global $USER;
    $agent_obj = new agent();

    $res = $user_obj->GetList(
        ($by    = 'ID'),
        ($order = 'ASC'),
        array(
            'ID' => implode(' | ', array_keys($user_ids))
        ),
        array('FIELDS' => array('ID', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'EMAIL'), 'SELECT' => array('UF_FIRST_PHONE', 'UF_FIRST_LOGIN'))
    );
    while($data = $res->Fetch()){
        $arResult['FARMERS_DATA'][$data['ID']] = array(
            'NAME'          => trim($data['LAST_NAME'].' '.$data['NAME'].' '.$data['SECOND_NAME']),
            'EMAIL'         => $data['EMAIL'],
            'NICK'          => '',
            'UF_FIRST_LOGIN' => (intval($data['UF_FIRST_LOGIN']) == 1 ? true : false),
            'UF_FIRST_PHONE' => (intval($data['UF_FIRST_PHONE']) == 1 ? true : false)
        );
        if(isset($user_ids[$data['ID']])){
            $arResult['FARMERS_DATA'][$data['ID']]['NICK'] = $user_ids[$data['ID']];
        }
    }

    //получение данных заполненности обязательных полей профиля
    $agentObj = new agent();
    $arResult['FARMERS_PROFILE_DONE'] = $agentObj->getFarmersRegistrationRights(array_keys($user_ids));
}