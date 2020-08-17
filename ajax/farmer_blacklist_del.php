<?

//получение ссылки для формирования встречного предложения (предполагается. что текущий пользователь - агент/организатор)
if(isset($_POST['bl_id'])
    && is_numeric($_POST['bl_id'])
){
    header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
    header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
    header ("Cache-Control: no-cache, must-revalidate");
    header ("Pragma: no-cache");
    header("content-type: application/x-javascript; charset=UTF-8");

    require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

    global $USER;
    if($USER->IsAuthorized()){
        CModule::IncludeModule('iblock');
        $el_obj = new CIBlockElement;

        //находим запись в черных списках
        $res = $el_obj->GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('farmer_black_list'),
                'ID' => $_POST['bl_id']
            ),
            false,
            array('nTopCount' => 1),
            array('PROPERTY_USER', 'PROPERTY_OPPONENT', 'PROPERTY_DEAL')
        );
        if($data = $res->Fetch()) {
            $el_obj->Update($_POST['bl_id'], array('ACTIVE' => 'N'));

            //восстанавливаем соответствия
            lead::restoreLeadsFromBlacklist($data['PROPERTY_USER_VALUE'], $data['PROPERTY_OPPONENT_VALUE']);

            //отправка уведомлений администраторам
            $email_arr = array();
            $res = CUser::GetList(
                ($by = 'id'), ($order = 'asc'),
                array(
                    'ACTIVE' => 'Y',
                    'GROUPS_ID' => 1
                ),
                array('FIELDS' => array('EMAIL'))
            );
            while($data2 = $res->Fetch()){
                $email_arr[$data2['EMAIL']] = true;
            }
            if(count($email_arr) > 0){
                $user_data = '';
                $black_user_data = '';
                $questions_data = '';
                $admin_href = $GLOBALS['host'] . '/bitrix/admin/iblock_element_edit.php?IBLOCK_ID=' . $ib_id . '&type=farmer&ID=' . $new_id . '&lang=ru&find_section_section=0&WF=Y';

                //получаем данные пользователей
                $user_data = farmer::getUserData($data['PROPERTY_USER_VALUE']);
                $black_user_data = client::getUserData($data['PROPERTY_OPPONENT_VALUE']);

                foreach($email_arr as $email_val => $cur_flag) {
                    $emailFields = array(
                        'TYPE_USER_NAME' => 'Поставщик',
                        'USER_INFO' => $user_data,
                        'BLACK_USER_INFO' => $black_user_data,
                        'EMAIL' => $email_val,
                        'ADMIN_HREF' => $GLOBALS['host'] . '/bitrix/admin/iblock_element_edit.php?IBLOCK_ID=' . rrsIblock::getIBlockId('deals_deals') . '&type=directories&ID=' . $data['PROPERTY_DEAL_VALUE'] . '&lang=ru&find_section_section=0&WF=Y'
                    );

                    CEvent::Send('BLACKLISTDELETE', 's1', $emailFields);
                }
            }
            $result = array('result' => 1);
            echo json_encode($result);
            exit;
        }
    }
}
$result = array('result' => 0);
echo json_encode($result);
exit;