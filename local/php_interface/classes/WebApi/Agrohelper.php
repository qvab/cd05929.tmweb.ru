<?
use \Bitrix\Highloadblock\HighloadBlockTable;

class Agrohelper {
    public static $hlIblock = 5;

    function getEntityDataClass($iblock) {
        CModule::IncludeModule('highloadblock');

        if (empty($iblock) || $iblock < 1)
            return false;

        $hlblock = HighloadBlockTable::getById($iblock)->fetch();
        $entity = HighloadBlockTable::compileEntity($hlblock);
        $entityDataClass = $entity->getDataClass();
        return $entityDataClass;
    }

    public static function addWebApiLog($model, $source, $url, $data, $input, $status, $output) {
        $entityDataClass = self::getEntityDataClass(self::$hlIblock);
        global $DB;

        $data = array(
            'UF_MODEL' => $model,
            'UF_SOURCE' => $source,
            'UF_URL' => $url,
            'UF_REQUEST_DATA' => print_r($data, true),
            'UF_INPUT' => $input,
            'UF_STATUS' => $status,
            'UF_OUTPUT' => $output,
            'UF_DATE' => $DB->FormatDate(date("d.m.Y H:i:s"), "DD.MM.YYYY HH:MI:SS", FORMAT_DATETIME)
        );

        $el = new $entityDataClass;
        $result = $el->add($data);
        if ($result->isSuccess())
            return $result->getId();
        else
            return false;
    }

    public static function getClass($url) {
        $arUrl = explode("/", $url);

        if (in_array('auth', $arUrl)) {
            $class = 'Auth';
            foreach ($arUrl as $val) {
                if ($val != '' && $val != 'auth') {
                    $action = $val;
                }
            }
            if (!$action)
                $action = 'login';
            return array('class' => $class, 'action' => $action);
        }

        $class = '';
        foreach ($arUrl as $val) {
            if ($val != '') {
                $class .= ucfirst($val);
            }
        }

        return array('class' => $class);
    }

    /**
     * генерация API KEY для пользователя
     *
     * @access  public
     * @param   string $login логин пользователя
     *          string $password пароль пользователя
     * @return  string hash-строка
     */
    public static function hashApiKey($login, $sha_password) {
        $hash = md5($login . $sha_password);
        return $hash;
    }

    public static function getResponseByCode($code) {
        $arResponses = array(
            200 => 'OK',
            201 => 'Created',
            404 => 'Not Found',
        );
        return $code . " " . $arResponses[$code];
    }

    public static function remove_utf8_bom($text) {
        $bom = pack('H*','EFBBBF');
        $text = preg_replace("/^$bom/", '', $text);
        return $text;
    }

    public static function getErrorMessage($code) {
        $arMessages = array(
            "OldAppMessage"                 => array("code" => 90,  "description" => "Нет возможности принять запрос. Необходимо обновить приложение"),

            "incorrectRequest"              => array("code" => 100, "description" => "Некорректный запрос"),
            "createEntityError"             => array("code" => 101, "description" => "Не удалось создать запись в Highload инфоблоке"),

            "CreateUserNoInfo"              => array("code" => 111, "description" => "Недостаточно информации для создания нового пользователя"),
            "CreateUserTypeError"           => array("code" => 112, "description" => "Неверно указан тип пользователя"),
            "CreateUserExist"               => array("code" => 113, "description" => "Пользователь с указанной почтой уже существует"),
            "CreateUserError"               => array("code" => 114, "description" => "Не удалось создать нового пользователя"),

            "СreateDeviceError"             => array("code" => 201, "description" => "Не удалось добавить устройство пользователя"),

            "CheckAuthorizeNoKey"           => array("code" => 801, "description" => "Отсутствует идентификационный ключ устройства X-Auth-Key"),
            "CheckAuthorizeNoTimestamp"     => array("code" => 802, "description" => "Отсутствует X-Auth-Timestamp"),
            "CheckAuthorizeNoToken"         => array("code" => 803, "description" => "Отсутствует токен X-Auth-Token"),
            "CheckAuthorizeNoDevice"        => array("code" => 804, "description" => "Устройство пользователя не найдено"),
            "CheckAuthorizeNoID"            => array("code" => 805, "description" => "Неверный идентификатор пользователя"),
            "CheckAuthorizeNoUser"          => array("code" => 806, "description" => "Пользователь не найден"),
            "CheckAuthorizeError"           => array("code" => 807, "description" => "Неверный X-Auth-Token"),
            "LoginNoInfo"                   => array("code" => 811, "description" => "Информациии для аутентификации пользователя недостаточно"),
            "LoginNoKey"                    => array("code" => 812, "description" => "Отсутствует идентификационный ключ устройства X-Auth-Key"),
            "LoginNotFound"                 => array("code" => 813, "description" => "Неправильное имя пользователя или пароль"),
            "LoginNotFarmer"                => array("code" => 814, "description" => "Пользователь не является поставщиком"),
            "LoginNotActive"                => array("code" => 815, "description" => "Учетная запись пользователя не активна"),
            "LoginNotApiUser"               => array("code" => 816, "description" => "Пользователь не имеет прав на использование мобильной системы"),
            "LogoutNoKey"                   => array("code" => 821, "description" => "Отсутствует идентификационный ключ устройства X-Auth-Key"),
            "LogoutNoDevice"                => array("code" => 822, "description" => "Устройство пользователя не найдено"),
            "RestoreNoInfo"                 => array("code" => 831, "description" => "Отсутствует электронная почта"),
            "RestoreNoUser"                 => array("code" => 832, "description" => "Учетная запись пользователя не найдена"),
            "RestoreError"                  => array("code" => 834, "description" => "email пользователя не найден"),
            "CopyRequestNoId"               => array("code" => 841, "description" => "Передан некорректный id запроса"),
            "CopyRequestNoVolume"           => array("code" => 842, "description" => "Передан некорректный параметр volume (ожидается целое число)"),
            "CopyRequestNoWarehouse"        => array("code" => 843, "description" => "Передан некорректный параметр warehouse (ожидается массив цен со значимыми ключами)"),
            "CopyRequestNoRights"           => array("code" => 844, "description" => "Пользователь не привязан к организатору"),
            "CopyRequestError"              => array("code" => 845, "description" => "Ошибка при копировании запроса"),
            "CopyRequestError2"             => array("code" => 845, "description" => "Ошибка при копировании запроса"),
            "CopyRequestError3"             => array("code" => 846, "description" => "Ошибка при копировании запроса"),

            "GetRequestsNoOffers"           => array("code" => 301, "description" => "Ни одного товара не найдено"),
            "GetRequestsNoCultures"         => array("code" => 302, "description" => "Ни одной культуры не найдено"),
            "GetRequestsNoRequests"         => array("code" => 303, "description" => "Ни одного запроса не найдено"),
            "GetRequestNoInfo"              => array("code" => 311, "description" => "Недостаточно данных для получения информации о запросе"),
            "GetRequestNoOffer"             => array("code" => 312, "description" => "Товар поставщика не найден"),
            "GetRequestPermissions"         => array("code" => 313, "description" => "Нет прав на просмотр данного запроса"),
            "GetRequestNoRequest"           => array("code" => 314, "description" => "Запрос покупателя на покупку СХП не найден"),
            "GetRequestError"               => array("code" => 315, "description" => "Запрос не найден"),
            "GetRequestDelete"              => array("code" => 316, "description" => "Запрос был ранее отклонен"),
            "DeleteRequestNoInfo"           => array("code" => 321, "description" => "Недостаточно данных для отклонения запроса"),
            "DeleteRequestNoOffer"          => array("code" => 322, "description" => "Товар поставщика не найден"),
            "DeleteRequestPermissions"      => array("code" => 323, "description" => "Нет прав для отклонения данного запроса"),
            "DeleteRequestNoRequest"        => array("code" => 324, "description" => "Запрос покупателя на покупку СХП не найден"),
            "DeactivateRequestNotFind"      => array("code" => 331, "description" => "Запрос не найден"),
            "DeactivateRequestDeactivated"  => array("code" => 332, "description" => "Запрос уже деактивирован"),
            "DeactivateRequestActPChanged"  => array("code" => 333, "description" => "Изменен код свойства активности, требуется обновление значения в api"),
            "DeactivateRequestNoRequest"    => array("code" => 334, "description" => "Передан некорректный id запроса"),
            "CopyRequestParamError"         => array("code" => 341, "description" => "Некорректное значение параметра copy"),
            "CopyRequestInfoHashError"      => array("code" => 342, "description" => "Некорректное значение параметра hash_val"),
            "CopyRequestHashError"          => array("code" => 343, "description" => "Некорректное значение параметра hash_val"),
            "CopyRequestNoUrgency"          => array("code" => 344, "description" => "Некорректное значение параметра urgency"),

            "CreateDealNoInfo"              => array("code" => 401, "description" => "Недостаточно данных для создания сделки"),
            "CreateDealNoVolume"            => array("code" => 402, "description" => "Объем не указан или указан неверно"),
            "CreateDealNoRequest"           => array("code" => 403, "description" => "Запрос от покупателя не найден"),
            "CreateDealNoDocs"              => array("code" => 404, "description" => "Не соблюдены условия для оформления сделки"),
            "CreateDealNoOffer"             => array("code" => 405, "description" => "Товар поставщика не найден"),
            "CreateDealPermissions"         => array("code" => 406, "description" => "Нет прав для оформления сделки"),
            "CreateDealNoDelivery"          => array("code" => 407, "description" => "Не указан способ доставки"),
            "CreateDealVolumeError"         => array("code" => 408, "description" => "Данный объем не требуется. Проверьте правильность указанного объема"),
            "CreateDealError"               => array("code" => 408, "description" => "Не удалось создать новую сделку"),

            "ProlongateRequestNoId"         => array("code" => 501, "description" => "Передан некорректный id запроса"),
            "ProlongateRequestHashError"    => array("code" => 502, "description" => "Некорректное значение параметра hash_val"),
            "ProlongateRequestNoData"       => array("code" => 503, "description" => "Запрос не может быть продлён"),

            "SavePushTokenNoInfo"           => array("code" => 901, "description" => "Недостаточно данных для сохранения токена"),
        );

        return $arMessages[$code];
    }
}
?>