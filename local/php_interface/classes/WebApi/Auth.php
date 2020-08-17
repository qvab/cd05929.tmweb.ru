<?
/*
 * Класс для работы с ресурсом Auth
 */

class Auth {
    public static function Exec($model, $data) {
        $headers['HTTP'] = 200;

        switch ($model) {
            case 'post':
                //Открытие сессии работы с сервисом (login)
                $resultData = self::Login($data);
                $headers['location'] = $data['action'] . '/';
                $outputData = $resultData;
                break;
            case 'get':
                if ($data['action'] == 'logout') {
                    //Закрытие сессии работы с сервисом (logout)
                    $resultData = self::Logout($data);
                    $headers['location'] = $data['action'] . '/';
                    $outputData = array('success' => 1);
                }
                elseif ($data['action'] == 'passwordrestore') {
                    //Восстановление пароля
                    $resultData = self::Restore($data);
                    $headers['location'] = $data['action'] . '/';
                    $outputData = array('success' => 1);
                }
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
     * Открытие сессии работы с сервисом
     *
     * @access  public
     * @param   [] $data массив с полями
     * @return  [] массив с данными пользователя
     */

    public static function Login($data) {
        $data['login'] =  trim($data['login']);
        $data['password'] =  trim($data['password']);
        $data['x-auth-key'] =  trim($data['x-auth-key']);
        $data['x-auth-timestamp'] =  trim($data['x-auth-timestamp']);

        //проверка на наличие всех обязательных полей
        if ($data['login'] == '' || $data['password'] == '') {
            return array('ERROR' => Agrohelper::getErrorMessage('LoginNoInfo'));
        }
        if ($data['x-auth-key'] == '') {
            return array('ERROR' => Agrohelper::getErrorMessage('LoginNoKey'));
        }

        //поиск пользователя по ключу
        $apiKey = Agrohelper::hashApiKey($data['login'], $data['password']);
        $arUser = Users::getUserByApiKey($apiKey);

        if (!$arUser['ID']) {
            //проверка авторизации по телефону (телефон в произвольной форме)
            $phone_val = getPhoneDigits($data['login']);
            if($phone_val != ''){
                $apiKeyM = Agrohelper::hashApiKey($phone_val, $data['password']);
                $arUser = Users::getUserByApiKey($apiKeyM, true);
            }
        }
        if (!$arUser['ID']) {
            return array('ERROR' => Agrohelper::getErrorMessage('LoginNotFound'));
        }

        if (!in_array(11, $arUser['GROUPS']) && !in_array(9, $arUser['GROUPS'])) {
            return array('ERROR' => Agrohelper::getErrorMessage('LoginNotApiUser'));
        }

        /*$user = new CUser;
        $arFields = array(
            "UF_SHA1" => $data["password"]
        );
        $user->Update($arUser['ID'], $arFields);*/

        if ($arUser['ACTIVE'] != 'Y') {
            return array('ERROR' => Agrohelper::getErrorMessage('LoginNotActive'));
        }

        //поиск устройства пользователя в UsersDevices
        $filter = array(
            'UF_USER' => $arUser['ID'],
            'UF_DEVICE' => $data['x-auth-key']
        );
        $arUserDevice = UsersDevices::_getEntity($filter);

        if (intval($arUserDevice['ID']) > 0) {
            //устройство найдено
            $arFields = array(
                'UF_LAST_AUTH' => date('d.m.Y H:i:s')
            );
            $UsersDevicesEntity = UsersDevices::_updateEntity($arUserDevice['ID'], $arFields);
        }
        else {
            //удаляем устройства с данным ключом
            $filter = array(
                'UF_DEVICE' => $data['x-auth-key']
            );
            $UserDevicesList = UsersDevices::_getEntitiesList($filter);
            if (is_array($UserDevicesList) && sizeof($UserDevicesList) > 0) {
                foreach ($UserDevicesList as $item) {
                    UsersDevices::_deleteEntity($item['ID']);
                }
            }

            //добавляем новое устройство
            $arFields = array(
                'UF_USER' => $arUser['ID'],
                'UF_DEVICE' => $data['x-auth-key'],
                'UF_LAST_AUTH' => date('d.m.Y H:i:s')
            );
            $UsersDevicesEntity = UsersDevices::_createEntity($arFields);
            if (!$UsersDevicesEntity['ID']) {
                return array('ERROR' => Agrohelper::getErrorMessage('СreateDeviceError'));
            }
        }

        $result = array(
            'id' => $arUser['ID'],
            'name' => $arUser['NAME'],
            'email' => $arUser['EMAIL'],
            'type' => ''
        );

        if(in_array(11, $arUser['GROUPS']))
        {
            $result['type'] = 'farmer';
        }
        else
        {
            $result['type'] = 'client';
        }

        return $result;
    }

    /**
     * Закрытие сессии работы с сервисом
     *
     * @access  public
     * @param   [] $data массив с полями
     * @return  bool
     */
    public static function Logout($data) {
        $data['x-auth-key'] =  trim($data['x-auth-key']);

        //проверка на наличие всех обязательных полей
        if ($data['x-auth-key'] == '') {
            return array('ERROR' => Agrohelper::getErrorMessage('LogoutNoKey'));
        }

        $filter = array(
            'UF_DEVICE' => $data['x-auth-key']
        );
        $UsersDevicesEntity = UsersDevices::_getEntity($filter);
        if (!$UsersDevicesEntity['ID']) {
            return array('ERROR' => Agrohelper::getErrorMessage('LogoutNoDevice'));
        }

        $result = UsersDevices::_deleteEntity($UsersDevicesEntity['ID']);
        return $result;
    }

    /**
     * Восстановление пароля
     *
     * @access  public
     * @param   [] $data массив с полями
     * @return  bool
     */
    public static function Restore($data) {
        $data['email'] =  trim($data['email']);

        //проверка на наличие всех обязательных полей
        if ($data['email'] == '') {
            return array('ERROR' => Agrohelper::getErrorMessage('RestoreNoInfo'));
        }

        //поиск пользователя
        $arUser = Users::getUserByEmail($data['email']);
        if (!$arUser['ID']) {
            return array('ERROR' => Agrohelper::getErrorMessage('RestoreNoUser'));
        }
        if ($arUser['ACTIVE'] != 'Y') {
            return array('ERROR' => Agrohelper::getErrorMessage('RestoreNoUser'));
        }

        //отправка на почту контрольной строки
        CUser::SendUserInfo($arUser['ID'], 's1', GetMessage('INFO_REQ'), true, 'USER_PASS_REQUEST');

        return true;
    }

    /**
     * Проверка авторизованности пользователя
     *
     * @access  public
     * @param   string $key ключ устройства
     *          string $timestamp время запроса, формируется устройством
     *          string $token токен, формируется устройством
     * @return  [] идентификатор пользователя, если он авторизован
     */
    public static function CheckAuthorize($key, $timestamp, $token) {
        //проверка на наличие всех обязательных полей
        if (!$key) {
            return array('ERROR' => Agrohelper::getErrorMessage('CheckAuthorizeNoKey'));
        }
        if (!$timestamp) {
            return array('ERROR' => Agrohelper::getErrorMessage('CheckAuthorizeNoTimestamp'));
        }
        if (!$token) {
            return array('ERROR' => Agrohelper::getErrorMessage('CheckAuthorizeNoToken'));
        }

        //поиск устройства пользователя
        $filter = array(
            'UF_DEVICE' => $key
        );
        $UsersDevicesEntity = UsersDevices::_getEntity($filter);

        if (!$UsersDevicesEntity['ID']) {
            return array('ERROR' => Agrohelper::getErrorMessage('CheckAuthorizeNoDevice'));
        }

        if (!$UsersDevicesEntity['UF_USER']) {
            return array('ERROR' => Agrohelper::getErrorMessage('CheckAuthorizeNoID'));
        }

        //поиск пользователя
        $arUser = CUser::GetByID($UsersDevicesEntity['UF_USER'])->Fetch();
        if (!$arUser['ID']) {
            return array('ERROR' => Agrohelper::getErrorMessage('CheckAuthorizeNoUser'));
        }

        $xAuthToken = sha1($key . $timestamp . $arUser['UF_SHA1']);
        if ($xAuthToken != $token) {
            return array('ERROR' => Agrohelper::getErrorMessage('CheckAuthorizeError'));
        }

        return array('USER_ID' => $arUser['ID']);
    }
}
?>