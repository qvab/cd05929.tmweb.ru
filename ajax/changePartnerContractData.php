<?php

//обновление записи агентского договора

if(isset($_POST['uid'])
    && filter_var($_POST['uid'], FILTER_VALIDATE_INT)
    //&& isset($_POST['agent_contract_date'])
){
    header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
    header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
    header ("Cache-Control: no-cache, must-revalidate");
    header ("Pragma: no-cache");
    header ("content-type: application/x-javascript; charset=UTF-8");
    require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

    $result = 0;

    $checked = 0;
    if(isset($_POST['agent_contract'])
        && $_POST['agent_contract'] == 1
        //&& trim($_POST['agent_contract_date']) != ''
    ){
        $checked = 1;
    }

    global $USER;
    $partner_id = $USER->GetID();

    //проверка, что организатор привязан к покупателю
    if(count(partner::getClients($partner_id, array($_POST['uid']))) > 0){
        $elem_id = 0;
        $file_id = 0;
        $ib_id = rrsIblock::getIBlockId('client_profile');
        $res = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => $ib_id,
                'PROPERTY_USER' => $_POST['uid']
            ),
            false,
            array('nTopCount' => 1),
            array('ID', 'PROPERTY_PARTNER_CONTRACT_FILE')
        );
        if($data = $res->Fetch()){
            $elem_id = $data['ID'];

            if(!empty($data['PROPERTY_PARTNER_CONTRACT_FILE_VALUE']) > 0){
                $file_id = $data['PROPERTY_PARTNER_CONTRACT_FILE_VALUE'];
            }
        }

        if($elem_id > 0) {
            $updateFields = array(
                //'PARTNER_CONTRACT_DATA' => trim($_POST['agent_contract_date']),
                'PARTNER_CONTRACT_SET' => $checked,
                'PARTNER_CONTRACT_LAST_ID' => $partner_id,
            );

            $result = 1;

            //проверяем передан ли новый файл
            if ($checked == 1
                && isset($_FILES['agent_contract_file']['error'])
                && $_FILES['agent_contract_file']['error'] == 0
            ) {
                $updateFields['PARTNER_CONTRACT_FILE'] = $_FILES['agent_contract_file'];
                $updateFields['PARTNER_CONTRACT_LAST_CHANGE_DATE'] = ConvertTimeStamp(false, 'FULL');

                //удаляем старый файл
                if ($file_id > 0) {
                    CFile::Delete($file_id);
                }
            } elseif ($checked != 1) {
                //убираем значения в ИБ
                //$updateFields['PARTNER_CONTRACT_FILE'] = array('del' => 'Y');
                $updateFields['PARTNER_CONTRACT_DATA'] = '';

                //добавляем возможность загрузки файла даже если не отмечена галочка
                if(isset($_FILES['agent_contract_file']['error'])
                && $_FILES['agent_contract_file']['error'] == 0){
                    $updateFields['PARTNER_CONTRACT_LAST_CHANGE_DATE'] = ConvertTimeStamp(false, 'FULL');
                    $updateFields['PARTNER_CONTRACT_FILE'] = $_FILES['agent_contract_file'];
                }

                //удаляем старый файл
//                if ($file_id > 0){
//                    CFile::Delete($file_id);
//                }
                $result = 2;
            }

            //Обновляем данные
            CIBlockElement::SetPropertyValuesEx($elem_id, $ib_id, $updateFields);

            //получаем новую ссылку на файл, если файл был передан
            //if($checked == 1){
            if(isset($_FILES['agent_contract_file']['error'])
                && $_FILES['agent_contract_file']['error'] == 0) {
                $res = CIBlockElement::GetList(
                    array('ID' => 'ASC'),
                    array(
                        'IBLOCK_ID' => $ib_id,
                        'ID' => $elem_id
                    ),
                    false,
                    array('nTopCount' => 1),
                    array('ID', 'PROPERTY_PARTNER_CONTRACT_FILE', 'PROPERTY_PARTNER_CONTRACT_LAST_CHANGE_DATE')
                );
                if ($data = $res->Fetch()) {
                    if (!empty($data['PROPERTY_PARTNER_CONTRACT_FILE_VALUE'])) {
                        $res = CFile::GetByID($data['PROPERTY_PARTNER_CONTRACT_FILE_VALUE']);
                        if ($cur_file = $res->Fetch()) {
                            $temp_path = CFile::GetPath($data['PROPERTY_PARTNER_CONTRACT_FILE_VALUE']);
                            if ($temp_path) {
                                $cur_file['f_src'] = $temp_path;
                                $result = $cur_file['f_src'] . '|' . addslashes($cur_file['ORIGINAL_NAME']) . '|' . $data['PROPERTY_PARTNER_CONTRACT_LAST_CHANGE_DATE_VALUE'];
                                if ($checked == 1) {
                                    $result .= '|1';
                                }
                            }
                        }
                    }
                }
            }
            //}
        }
    }

    echo $result;
}
