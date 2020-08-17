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

CModule::IncludeModule('iblock');

$arResult['ERROR'] = '';
$arResult['ERROR_MESSAGE'] = '';
$arResult['FARMERS_LIST'] = array();

if (!isset($arParams['U_ID']) || !is_numeric($arParams['U_ID'])) {
    $arResult['ERROR'] = 'Y';
    $arResult['ERROR_MESSAGE'] = 'Не указан ID пользователя.';
}

if (!isset($arParams['U_GROUP_ID']) || !is_numeric($arParams['U_GROUP_ID'])) {
    $arResult['ERROR'] = 'Y';
    $arResult['ERROR_MESSAGE'] = 'Не указана группа пользователя.';
}

if (!isset($arParams['EDIT_PROPS_LIST']) || !is_array($arParams['EDIT_PROPS_LIST'])) {
    $arResult['ERROR'] = 'Y';
    $arResult['ERROR_MESSAGE'] = 'Не указан список свойств для вывода.';
}
$arDocsList = array();
if ($arResult['ERROR'] != 'Y') {
    $arResult['u_fio'] = '';
    $arResult['u_email'] = '';
    if (isset($arParams['U_ID']) && is_numeric($arParams['U_ID'])) {
        //получение данных пользователя
        $u_obj = new CUser;
        $res = $u_obj->GetList(($order_by = 'id'), ($sort = 'asc'), array('ID' => $arParams['U_ID'], 'ACTIVE' => 'Y'), array('FIELDS' => array('ID', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'EMAIL')));
        if ($data = $res->Fetch()) {
            $arResult['u_name'] = $data['NAME'];
            $arResult['u_last_name'] = $data['LAST_NAME'];
            $arResult['u_second_name'] = $data['SECOND_NAME'];
            $arResult['u_fio'] = trim($data['LAST_NAME'].' '.$data['NAME'].' '.$data['SECOND_NAME']);
            $arResult['u_email'] = $data['EMAIL'];
            //получение типа пользователей и его разрешений
            $ib_code = ''; //user profile IB code
            $ib_partners_code = ''; //linked partner IB code
            $client_profile_ib_id = rrsIblock::getIBlockId('client_profile');

            //(проверка аналогичная файлу permissions)
            if ($arParams['U_GROUP_ID'] == getGroupIdByRole('p'))  {
                $APPLICATION->SetTitle("Профиль организатора");
                $ib_code = 'partner_profile';
                $arDocsList = array_keys(partner::getAllDocuments());
            }
            elseif ($arParams['U_GROUP_ID'] == getGroupIdByRole('t')) {
                $APPLICATION->SetTitle("Профиль транспортной компании");
                $ib_code = 'transport_profile';
                $ib_partners_code = 'transport_partner_link';
                $arDocsList = array_keys(transport::getAllDocuments());
            }
            elseif ($arParams['U_GROUP_ID'] == getGroupIdByRole('f')) {
                $APPLICATION->SetTitle("Профиль поставщика");
                $ib_code = 'farmer_profile';
                $ib_partners_code = 'farmer_agent_link';
                //old partner link logic (add PARTNER_ID to beginning of array)
                //array_unshift($arParams['EDIT_PROPS_LIST'], 'PARTNER_ID');
                $arDocsList = array_keys(farmer::getAllDocuments());
            }
            elseif ($arParams['U_GROUP_ID'] == getGroupIdByRole('c')) {
                $APPLICATION->SetTitle("Профиль клиента");
                $ib_code = 'client_profile';
                $ib_partners_code = 'client_agent_link';
                $arDocsList = array_keys(client::getAllDocuments());

            } elseif ($arParams['U_GROUP_ID'] == getGroupIdByRole('agc')) {
                $APPLICATION->SetTitle("Профиль агента покупателя");
                $ib_code = 'client_agent_profile';

            } elseif ($arParams['U_GROUP_ID'] == getGroupIdByRole('ag')) {
                $APPLICATION->SetTitle("Профиль агента поставщика");
                $ib_code = 'agent_profile';

            }

            if($ib_code == '') {
                LocalRedirect('/');
                exit;
            }

            $profile_ib_id = rrsIblock::getIBlockId($ib_code);

            $arParams['EDIT_PROPS_LIST'] = array_merge($arParams['EDIT_PROPS_LIST'], $arDocsList);
            //get user profile data
            CModule::IncludeModule('iblock');
            $props_get_ib_data_ids = array();
            $props_get_users_data = array();
            $props_get_files_ids = array();
            $el_obj = new CIBlockElement;
            $arResult['LINKED_IB_DATA'] = array();
            $arResult['USERS_DATA'] = array();
            $arResult['LINKED_PARTNERS_DATA'] = array();
            $arResult['FILES_DATA'] = array();
            $arResult['EDIT_PROPS_DATA'] = array();
            $arResult['EDIT_PROPS_LIST'] = array_flip($arParams['EDIT_PROPS_LIST']);

            $res = $el_obj->GetList(
                array('ID' => 'ASC'),
                array(
                    'IBLOCK_ID' => $profile_ib_id,
                    'PROPERTY_USER' => $arParams['U_ID']
                ),
                false,
                array('nTopCount' => 1)
            );
            if ($data = $res->GetNextElement()) {
                $p = $data->GetProperties();
                foreach ($p as $cur_prop) {
                    if (isset($arResult['EDIT_PROPS_LIST'][$cur_prop['CODE']])) {
                        $arResult['EDIT_PROPS_DATA'][$cur_prop['CODE']] = array(
                            'NAME' => $cur_prop['NAME'],
                            'VALUE' => $cur_prop['VALUE']
                        );
                        if ($cur_prop['PROPERTY_TYPE'] == 'F') {
                            $arResult['EDIT_PROPS_DATA'][$cur_prop['CODE']]['PROPERTY_TYPE'] = 'F';
                            if (is_numeric($cur_prop['VALUE'])) {
                                $props_get_files_ids[$cur_prop['VALUE']] = true;
                            }
                        }
                        elseif ($cur_prop['USER_TYPE'] == 'UserID' && is_numeric($cur_prop['VALUE'])) {
                            $arResult['EDIT_PROPS_DATA'][$cur_prop['CODE']]['LINK_USER'] = $cur_prop['VALUE'];
                            $props_get_users_data[$cur_prop['VALUE']] = true;
                        }
                        elseif ($cur_prop['PROPERTY_TYPE'] == 'E' && is_numeric($cur_prop['LINK_IBLOCK_ID'])) {
                            $arResult['EDIT_PROPS_DATA'][$cur_prop['CODE']]['LINK_IBLOCK_ID'] = $cur_prop['LINK_IBLOCK_ID'];
                            $props_get_ib_data[$cur_prop['LINK_IBLOCK_ID']][$cur_prop['VALUE']] = true;
                        }
                    }
                }

                //отдельно проверяем дополнительные св-ва, выводимые специально
                if($client_profile_ib_id == $profile_ib_id){

                    //для организатора
                    if($GLOBALS['rrs_user_perm_level'] == 'p') {


                        //для связанного организатора
                        global $USER;
                        if(count(partner::getClients($USER->GetID(), array($arParams['U_ID']))) > 0){

                            if (!empty($p['PARTNER_CONTRACT_SET']['VALUE'])) {
                                $arResult['IS_PARTNER_CONTRACT'] = 1;
                                if (!empty($p['PARTNER_CONTRACT_FILE']['VALUE'])) {
                                    $cur_file = array();
                                    $res = CFile::GetByID($p['PARTNER_CONTRACT_FILE']['VALUE']);
                                    if ($cur_file = $res->Fetch()) {
                                        $temp_path = CFile::GetPath($p['PARTNER_CONTRACT_FILE']['VALUE']);
                                        if ($temp_path) {
                                            $cur_file['f_src'] = $temp_path;
                                        }
                                    }

                                    if (!empty($cur_file['f_src'])) {
                                        $arResult['PARTNER_CONTRACT_FILE'] = $cur_file;
                                    }
                                }
                            }
                        }
                    }
                }

                global $USER;
                $group = getUserType($USER->getID());
                //если текущий пользователь организатор
                if ($group['ID'] == 10) {
                    //проверяем есть поле шаблон ДОУ
                    if (isset($arResult['EDIT_PROPS_DATA']['DOU_DOC'])) {
                        if (isset($arResult['EDIT_PROPS_DATA']['DOU_DOC']['PROPERTY_TYPE'])) {
                            if (($arResult['EDIT_PROPS_DATA']['DOU_DOC']['PROPERTY_TYPE'] == 'F') && (empty($arResult['EDIT_PROPS_DATA']['DOU_DOC']['VALUE']))) {
                                //получаем документ по умолчанию
                                if ((isset($arParams['DOU_DEFAULT_DOC_IB_ID'])) && (isset($arParams['DOU_DEFAULT_DOC_ELEMENT_CODE']))) {
                                    $elObj = new CIBlockElement;
                                    $res = $elObj->GetList(array('ID' => 'DESC'), array('IBLOCK_ID' => $arParams['DOU_DEFAULT_DOC_IB_ID'],
                                        'CODE' => $arParams['DOU_DEFAULT_DOC_ELEMENT_CODE']),
                                        false, false, array('ID', 'PROPERTY_FILE')
                                    );
                                    if ($res->SelectedRowsCount() > 0) {
                                        while ($ar_fields = $res->Fetch()) {
                                            if (!empty($ar_fields['PROPERTY_FILE_VALUE'])) {
                                                $arResult['EDIT_PROPS_DATA']['DOU_DOC']['VALUE'] = $ar_fields['PROPERTY_FILE_VALUE'];
                                                $props_get_files_ids[$arResult['EDIT_PROPS_DATA']['DOU_DOC']['VALUE']] = true;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }else{
                    unset($arResult['EDIT_PROPS_DATA']['DOU_DOC']);
                }
                //get linked iblock_values
                if (count($props_get_ib_data) > 0) {
                    foreach ($props_get_ib_data as $cur_ib_id => $cur_ids_arr) {
                        $res = $el_obj->GetList(
                            array('ID' => 'ASC'),
                            array(
                                'IBLOCK_ID' => $cur_ib_id,
                                'ID' => array_keys($cur_ids_arr)
                            ),
                            false,
                            false,
                            array('ID', 'NAME')
                        );
                        while ($data = $res->Fetch()) {
                            $arResult['LINKED_IB_DATA'][$cur_ib_id][$data['ID']] = $data['NAME'];
                        }
                    }
                }
            }
            //if need to get linked partners data
            if ($ib_partners_code != '') {
                $res = $el_obj->GetList(
                    array('ID' => 'ASC'),
                    array(
                        'IBLOCK_CODE' => $ib_partners_code,
                        'PROPERTY_USER_ID' => $arParams['U_ID'],
                        'ACTIVE' => 'Y'
                    ),
                    false,
                    false,
                    array('PROPERTY_AGENT_ID')
                );
                while ($data = $res->Fetch()) {
                    if (isset($data['PROPERTY_AGENT_ID_VALUE']) && is_numeric($data['PROPERTY_AGENT_ID_VALUE'])) {
                        $props_get_users_data[$data['PROPERTY_AGENT_ID_VALUE']] = true;
                        $arResult['LINKED_PARTNERS_DATA'][$data['PROPERTY_AGENT_ID_VALUE']] = '';
                    }
                }
            }

            //get linked users data
            if (count($props_get_users_data) > 0) {
                $res = $u_obj->GetList(($order_by = 'id'), ($sort = 'asc'), array('ID' => implode(' | ', array_keys($props_get_users_data)), 'ACTIVE' => 'Y'), array('FIELDS' => array('ID', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'LOGIN', 'EMAIL')));
                while ($data = $res->Fetch()) {
                    $arResult['USERS_DATA'][$data['ID']] = array('NAME' => $data['NAME'], 'EMAIL' => $data['EMAIL'], 'LAST_NAME' => $data['LAST_NAME'], 'SECOND_NAME' => $data['SECOND_NAME'], 'LOGIN' => $data['LOGIN']);
                }
            }

            //get files data
            if (count($props_get_files_ids) > 0) {
                $res = CFile::GetList(array('ID' => 'ASC'), array('MODULE_ID' => 'iblock', '@ID' => implode(',', array_keys($props_get_files_ids))));
                while ($data = $res->Fetch()) {
                    if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/upload/' . $data['SUBDIR'] . '/' . $data['FILE_NAME'])) {
                        $arResult['FILES_DATA'][$data['ID']] = array('SRC' => '/upload/' . $data['SUBDIR'] . '/' . $data['FILE_NAME'], 'NAME' => $data['ORIGINAL_NAME']);
                    }
                }
            }

            //fill parnters data with file users name
            foreach ($arResult['LINKED_PARTNERS_DATA'] as $cur_id => $cur_val) {
                if (isset($arResult['USERS_DATA'][$cur_id])) {
                    $arResult['LINKED_PARTNERS_DATA'][$cur_id] = trim($arResult['USERS_DATA'][$cur_id]['LAST_NAME'] . ' ' . $arResult['USERS_DATA'][$cur_id]['NAME']. ' ' . $arResult['USERS_DATA'][$cur_id]['SECOND_NAME']) . " ({$arResult['USERS_DATA'][$cur_id]['EMAIL']})";
                }
            }
        }
        else  {
            $arResult['ERROR'] = 'Y';
            $arResult['ERROR_MESSAGE'] = 'Данные пользователя не найдены.';
        }

        unset($data, $res, $props_get_files_ids, $props_get_ib_data_ids, $props_get_users_data);
    }
}

$this->IncludeComponentTemplate();