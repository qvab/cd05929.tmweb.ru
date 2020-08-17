<?
/*
 * Класс для работы с Push-уведомлениями
 */

require_once('Resource.php');

class Push extends Resource {
    protected static $hlIblock = 6;
    protected static $serverKey = 'AAAAwbzKvZk:APA91bEJkcMOxr8w-Dzdd6qj7I0EMfyTI0Onhjmctrl6wmBx2YHS5QVXiqLIDtGyQ5FnwmK95XvEefvUInp18LtYhYMSiiHIXqUEQIM6b15ez2ry2_iatXP74IpQFcQLKfl6Uj4BFUEiRCW4ZbGRm9gTYVw6Jkn1Xg';

    public static function Exec($model, $data) {
        $headers['HTTP'] = 200;

        //проверка авторизованности пользователя
        $resultData = Auth::CheckAuthorize($data["x-auth-key"], $data["x-auth-timestamp"], $data["x-auth-token"]);
        if (intval($resultData["USER_ID"]) > 0) {
            $data["userAccID"] = $resultData["USER_ID"];

            switch ($model) {
                case 'post':
                    //Передача Push-токена
                    $resultData = self::SavePushToken($data);
                    $headers['location'] = $resultData['ID'];
                    $outputData = array('success' => 1);
                    break;
                default:
                    $resultData['ERROR'] = Agrohelper::getErrorMessage('incorrectRequest');
                    $headers['HTTP'] = 404;
            }
        }

        if (sizeof($resultData['ERROR']) > 0) {
            $headers['location'] = '';
            $outputData = array('error' => $resultData['ERROR']);
        }

        return array('HEADERS' => $headers, 'DATA' => $outputData);
    }

    /**
     * Передача Push-токена
     *
     * @access  public
     * @param   [] $data массив с полями
     * @return  bool true в случае успешного сохранения токена
     */
    public static function SavePushToken($data) {
        $data['os'] =  trim($data['os']);
        $data['token'] =  trim($data['token']);

        //проверка на наличие всех обязательных полей
        if ($data['os'] == '' || $data['token'] == '') {
            return array('ERROR' => Agrohelper::getErrorMessage('SavePushTokenNoInfo'));
        }

        //поиск устройства пользователя
        $filter = array(
            'UF_DEVICE' => $data["x-auth-key"]
        );
        $UsersDevicesEntity = UsersDevices::_getEntity($filter);

        //Сохранение push-токена
        $fields = array(
            'UF_OS' => $data['os'],
            'UF_TOKEN' => $data['token'],
        );
        $PushEntity = self::_updateEntity($UsersDevicesEntity['ID'], $fields);

        return $PushEntity;
    }

    public static function SendPush ($token, $text, $data, $push_title = 'Новый запрос') {
        $server_key = self::$serverKey;
        $url = 'https://fcm.googleapis.com/fcm/send';
        $headers = array('Authorization: key=' . $server_key,
            'Content-Type: application/json');

        /*if (is_array($token))
            $fields['registration_ids'] = $token;
        else
            $fields['registration_ids'] = array($token);

        $fields['priority'] = 'high';
        $fields['notification'] = array('body' => $text, 'title' => $title);
        $fields['data'] = array('message' => $text, 'title' => $title);*/

        $fields['data'] = $data;
        $fields['notification'] = array(
            'sound' => 'default',
            'badge' => '1',
            'body' => $text,
            'title' => $push_title
        );
        $fields['to'] = $token;

        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_POSTFIELDS => json_encode($fields)
        ));
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
}
?>