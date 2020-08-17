<?
/*
 * Класс для работы с экземплярами ресурса Users
 */

class Users {
    public static function Exec($model, $data) {
        $headers['HTTP'] = 200;

        switch ($model) {
            case 'post':
                //Регистрация нового пользователя
                $resultData = self::CreateUser($data);
                $headers['location'] = $resultData['ID'];
                $outputData = array('success' => 1);
                break;
            default:
                $resultData['ERROR'] = Agrohelper::getErrorMessage('incorrectRequest');
                $headers['HTTP'] = 404;
        }

        if (sizeof($resultData['ERROR']) > 0) {
            $headers['location'] = '';
            $outputData = array('error' => $resultData['ERROR']);
        }

        return array('HEADERS' => $headers, 'DATA' => $outputData);
    }

    /**
     * Регистрация нового пользователя
     *
     * @access  public
     * @param   [] $data массив с полями
     * @return  [] идентификатор созданного пользователя
     */
    public static function CreateUser($data) {
        $data['name'] =  trim($data['name']);
        $data['email'] =  trim($data['email']);
        $data['type'] =  trim($data['type']);

        //проверка на наличие всех обязательных полей
        if ($data['name'] == '' || $data['email'] == '' || $data['type'] == '') {
            return array('ERROR' => Agrohelper::getErrorMessage('CreateUserNoInfo'));
        }
        if (!in_array($data['type'], array('client', 'partner', 'farmer', 'transport'))) {
            return array('ERROR' => Agrohelper::getErrorMessage('CreateUserTypeError'));
        }

        //проверка существования пользователя с указанным email
        $arUser = self::getUserByEmail($data['email']);
        if (intval($arUser['ID']) > 0) {
            return array('ERROR' => Agrohelper::getErrorMessage('CreateUserExist'));
        }

        CModule::IncludeModule('main');
        $user = new CUser;

        CModule::IncludeModule('iblock');
        $el = new CIBlockElement;

        $ib_code = '';

        $arGroups = array(2);
        switch($data['type']) {
            case 'client':
                $arGroups[] = 9;
                $ib_code = 'client_profile';
                $uCode = 'c';
                break;
            case 'partner':
                $arGroups[] = 10;
                $ib_code = 'partner_profile';
                $uCode = 'p';
                break;
            case 'farmer':
                $arGroups[] = 11;
                $ib_code = 'farmer_profile';
                $uCode = 'f';
                break;
            case 'transport':
                $arGroups[] = 12;
                $ib_code = 'transport_profile';
                $uCode = 't';
                break;
        }

        $password = hashPass(4).'-'.hashPass(12);
        $arFields = array(
            'NAME'                  => $data['name'],
            'EMAIL'                 => $data['email'],
            'LOGIN'                 => $data['email'],
            'ACTIVE'                => 'N',
            'GROUP_ID'              => $arGroups,
            'PASSWORD'              => $password,
            'CONFIRM_PASSWORD'      => $password,
            'UF_PRIV_POLICY_CONF'   => 'Y',
            'UF_FIRST_LOGIN'        => 1
        );

        if (in_array($data['type'], array('client', 'farmer'))) {
            $arFields['UF_DEMO'] = true;
        }

        $id = $user->Add($arFields);
        if (intval($id) > 0) {
            $hash = hashPass();

            $user_obj = new CUser;
            $fields = array(
                'UF_HASH' => $hash,
            );
            $user_obj->Update($id, $fields);

            $arEventFields = array(
                'EMAIL' => $data['email'],
                'HREF' => $GLOBALS['host'] . '/?reg=mobile&hash=' . $hash . '#action=register',
            );

            if($ib_code != '') {
                $arFields = array(
                    'IBLOCK_ID' => rrsIblock::getIBlockId($ib_code),
                    'NAME' => 'Свойства пользователя ' . $data['email'] . ' с ID [' . $id . ']',
                    'ACTIVE' => 'Y'
                );
                $arProps = array();
                $arProps['USER'] = $id;
                if ($uCode != '') {
                    $noticeList = notice::getNoticeListByUserType($uCode);
                    if (is_array($noticeList) && sizeof($noticeList) > 0) {
                        $n = 0;
                        foreach ($noticeList as $item) {
                            if (in_array($uCode, $item['PROPERTY_CAN_CHANGE_VALUE'])) {
                                $arProps['NOTICE']["n".$n] = array('VALUE' => $item['ID']);
                                $n++;
                            }
                        }
                    }
                }
                $arFields['PROPERTY_VALUES'] = $arProps;

                $id_val = $el->Add($arFields);
            }

            CEvent::SendImmediate('REG_HASH_PASSWORD', "s1", $arEventFields);

            $result = array("ID" => $id);
        }
        else {
            $result = array('ERROR' => Agrohelper::getErrorMessage('CreateUserError'));
        }
        return $result;
    }

    /**
     * Поиск учетной записи пользователя по email
     *
     * @access  public
     * @param   string $email почта пользователя
     * @return  [] идентификатор пользователя
     */
    public static function getUserByEmail($email) {
        $result = array();
        $rsUsers = CUser::GetList(($by="id"), ($order="desc"), array("=EMAIL" => $email)/*, array("SELECT"=>array("UF_*"))*/);
        if ($arUser = $rsUsers->Fetch()) {
            $result['ID'] = $arUser['ID'];
            $result['LOGIN'] = $arUser['LOGIN'];
            $result['ACTIVE'] = $arUser['ACTIVE'];
            $result['CHECKWORD'] = $arUser['CHECKWORD'];
        }
        return $result;
    }

    /**
     * Поиск учетной записи пользователя по API KEY
     *
     * @access  public
     * @param   string $key API KEY пользователя
     * @param   Boolean $is_mobile_app флаг того - какой ключ использовать мобильный или нет (по умолчанию - нет)
     * @return  [] идентификатор пользователя
     */
    public static function getUserByApiKey($key, $is_mobile_app = false) {
        $result = array();

        $field_key = 'UF_API_KEY';
        if($is_mobile_app)
        {
            $field_key .= '_M';
        }

        $rsUsers = CUser::GetList(($by="id"), ($order="desc"), array($field_key => $key, 'ACTIVE' => 'Y'));
        if ($arUser = $rsUsers->Fetch()) {
            $result['ID'] = $arUser['ID'];
            $result['EMAIL'] = $arUser['EMAIL'];
            $result['NAME'] = $arUser['NAME'];
            $result['ACTIVE'] = $arUser['ACTIVE'];
            $result['GROUPS'] = CUser::GetUserGroup($arUser['ID']);
        }
        return $result;
    }
}