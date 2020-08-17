<?php

//получение данных пользователей (для фильтра админа)
if(isset($arParams['BY_ADMIN'])
    && $arParams['BY_ADMIN'] == 'Y'
) {
    $uids = array();
    $arResult['USERS'] = array();
    $res = CUser::GetList(
        ($sort = 'id'), ($order = 'asc'),
        array('GROUPS_ID' => 9),
        array('SELECT' => array('ID', 'EMAIL', 'NAME', 'LAST_NAME', 'SECOND_NAME'))
    );
    while($data = $res->Fetch()) {
        if($data['EMAIL'] != '') {
            $arResult['USERS'][$data['ID']] = trim($data['EMAIL']) . ' [' . $data['ID'] . ']';
        }else{
            $temp_val = trim($data['NAME'] . ' ' . $data['LAST_NAME']);
            if($temp_val != ''){
                $arResult['USERS'][$data['ID']] = $temp_val . ' [' . $data['ID'] . ']';
            }else {
                //дополнительно получаем данные пользователей, у которых нет email и имени
                $uids[$data['ID']] = true;
            }
        }
    }

    //дополнительно получаем данные пользователей, у которых нет email и имени
    $ib_id = rrsIblock::getIBlockId((isset($arParams['USER_TYPE']) && $arParams['USER_TYPE'] == 'farmer' ? 'farmer_profile' : 'client_profile'));
    if(count($uids) > 0){
        CModule::IncludeModule('iblock');
        $el_obj = new CIBlockElement();
        $res = $el_obj->GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => $ib_id,
                'PROPERTY_USER' => array_keys($uids),
            ),
            false,
            false,
            array('PROPERTY_USER', 'PROPERTY_PHONE', 'PROPERTY_FULL_COMPANY_NAME')
        );
        while($data = $res->Fetch()){
            $temp_val = trim($data['PROPERTY_FULL_COMPANY_NAME_VALUE']);
            if($temp_val != ''){
                $arResult['USERS'][$data['PROPERTY_USER_VALUE']] = $temp_val . ' [' . $data['PROPERTY_USER_VALUE'] . ']';
            }else{
                $temp_val = trim($data['PROPERTY_PHONE_VALUE']);
                if($temp_val != ''){
                    $arResult['USERS'][$data['PROPERTY_USER_VALUE']] = $temp_val . ' [' . $data['PROPERTY_USER_VALUE'] . ']';
                }else{
                    $arResult['USERS'][$data['PROPERTY_USER_VALUE']] = $data['PROPERTY_USER_VALUE'];
                }
            }
        }
    }
}