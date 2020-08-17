<?
require_once 'lib/notisend/notisend.class.php';

class notice {
    public static
        $project    = 'Agrouber',
        $key        = '18f8e448c534d',
        $sender     = 'AGROHELPER',
        $hlNoticeLog = 11;

    /**
     * Получение списка уведомлений
     * @return [] массив со списком элементов
     */
    public static function getNoticeList() {
        CModule::IncludeModule('iblock');
        $result = array();

        $res = CIBlockElement::GetList(
            array('SORT' => 'ASC'),
            array('IBLOCK_ID' => rrsIblock::getIBlockId('notice'), 'ACTIVE' => 'Y'),
            false,
            false,
            array('ID', 'NAME', 'CODE', 'SORT')
        );
        while ($ob = $res->Fetch()) {
            $result[$ob['CODE']] = $ob;
        }

        return $result;
    }

    /**
     * Получение списка уведомлений
     * @return [] массив со списком элементов
     */
    public static function getNoticeListByUserType($type) {
        CModule::IncludeModule('iblock');
        $result = array();

        $res = CIBlockElement::GetList(
            array('SORT' => 'ASC'),
            array('IBLOCK_ID' => rrsIblock::getIBlockId('notice'), 'ACTIVE' => 'Y', 'PROPERTY_USER_GROUP'),
            false,
            false,
            array('ID', 'NAME', 'CODE', 'SORT', 'PROPERTY_CAN_CHANGE')
        );
        while ($ob = $res->Fetch()) {
            $result[$ob['CODE']] = $ob;
        }

        return $result;
    }

    /**
     * Добавление сообщения в центр уведомлений
     * @param  int $user_id идентификатор пользователя
     *         string $type тип сообщения
     *         string $message сообщение
     *         string $link_href ссылка для перехода
     *         string $link_name наименование ссылки
     * @return int
     */
    public static function addNotice($user_id, $type, $message, $link_href, $link_name, $params = array()) {
        CModule::IncludeModule('iblock');
        $el = new CIBlockElement;

        $PROP = array();
        $PROP['USER'] = $user_id;
        $PROP['TYPE'] = rrsIblock::getPropListKey('user_notice', 'TYPE', $type);
        $PROP['LINK_HREF'] = $link_href;
        $PROP['LINK_NAME'] = $link_name;
        $PROP['READ'] = 'N';

        if((sizeof($params))&&(is_array($params))){
            if(isset($params['SEND_USER'])){
                $PROP['SEND_USER'] = $params['SEND_USER'];
            }
            if(isset($params['PAIR_ID'])){
                $PROP['PAIR_ID'] = $params['PAIR_ID'];
            }
        }

        $fieldArray = Array(
            'IBLOCK_ID' => rrsIblock::getIBlockId('user_notice'),
            'ACTIVE' => 'Y',
            'NAME' => $message,
            "PROPERTY_VALUES"=> $PROP,
        );

        if ($ID = $el->Add($fieldArray)) {
            return $ID;
        }
        else {
            return false;
        }
    }

    /**
     * Добавление сообщения в центр уведомлений
     * @param  int $user_id идентификатор пользователя
     *         string $type тип сообщения
     *         string $message сообщение
     *         string $link_href ссылка для перехода
     *         string $link_name наименование ссылки
     * @return int
     */
    public static function sendNoticeSMS($recipients, $text) {
        $testMode = $GLOBALS['smsTest'];
        $project = self::$project;
        $key = self::$key;
        $sender = self::$sender;
        $api = new NotiSend($project, $key, false, $testMode);
        $api->sendSMS($recipients, $text, $sender);
        //$response = $api->getResponse();
    }

    /**
     * Добавление задания на рассылку уведомлений
     * @param  string $code код метода, который инициировал отправку уведомлений
     *         number $itemId идентификатор элемента
     *         [] $noticeData массив с данными
     * @return number идентификатор созданной записи
     */
    public static function addNoticeLog($code, $itemId, $noticeData) {
        global $DB;

        $jsonData = json_encode($noticeData, JSON_UNESCAPED_UNICODE);
        $data = array(
            'UF_DATE' => $DB->FormatDate(date("d.m.Y H:i:s"), "DD.MM.YYYY HH:MI:SS", FORMAT_DATETIME),
            'UF_STATUS' => 'N',
            'UF_DESC' => 'ожидается',
            'UF_CODE' => $code,
            'UF_ITEM_ID' => $itemId,
            'UF_JSONDATA' => $jsonData,
        );

        return log::_createEntity(self::$hlNoticeLog, $data);
    }

    /**
     * Получение списка заданий на рассылку
     * @param  number $limit количество заданий, которые будут обрабатыватся за один раз
     * @return [] массив записей
     */
    public static function getNoticeLog($limit = 20) {
        $entityDataClass = log::getEntityDataClass(self::$hlNoticeLog);
        $el = new $entityDataClass;

        $result = array();

        $rsData = $el->getList(array(
            'select' => array('ID', 'UF_CODE', 'UF_JSONDATA'),
            'filter' => array("UF_STATUS" => "N"),
            'limit' => $limit,
            'order' => array("ID" => "ASC")
        ));
        while ($res = $rsData->fetch()) {
            $result[] = $res;
        }

        return $result;
    }

    /**
     * Обновление записи после обработки
     * @param  number $id идентификатор записи
     * @return [] массив записей
     */
    public static function updateNoticeLogItem($id) {
        $entityDataClass = log::getEntityDataClass(self::$hlNoticeLog);
        $el = new $entityDataClass;

        global $DB;
        $data = array(
            'UF_EXE_DATE' => $DB->FormatDate(date("d.m.Y H:i:s"), "DD.MM.YYYY HH:MI:SS", FORMAT_DATETIME),
            'UF_STATUS' => "Y",
            'UF_DESC' => "выполнено",
        );
        $res = $el->update($id, $data);
        return $res;
    }


    /**
     * Агент очистки старых сообщений
     * Удаляет прочитанные сообщения старше $iDaysLive дней
     * @param $iDaysLive
     * @return string
     */
    public static function AgentCleanMsg  ($iDaysLive) {
        CModule::IncludeModule('iblock');
        try {

            global $DB;

            if(empty($iDaysLive)) {
                throw new Exception('Не задано количество дней жизни уведомления');
            }

            // Текущая дата минус время жизнии сообщения
            $obDate = new DateTime('now');
            $obDate->modify('-'.$iDaysLive.' days');

            // Дата в формате сайта
            $sDateFrom = $obDate->format($DB->DateFormatToPHP(CSite::GetDateFormat("FULL")));

            if(empty($sDateFrom)) {
                throw new Exception('Не удалось получить дату отсчета');
            }

            // Выборка уведомлений
            $rs = CIBlockElement::GetList(
                ['ID' => 'ASC'],
                [
                    'IBLOCK_ID'     => rrsIblock::getIBlockId('user_notice'),
                    //'PROPERTY_READ' => 'Y',
                    '<DATE_CREATE'  => $sDateFrom,
                ],
                false,
                false,
                ['ID',]
            );

            $sErrorDelete = null;
            while($arRow = $rs->Fetch()) {
                // Удаляем
                if(!CIBlockElement::Delete($arRow['ID'])) {
                    $sErrorDelete .= 'Не удалось удалить уведомление ID['.$arRow['ID'].']' . CIBlockElement::LAST_ERROR . PHP_EOL;
                }
            }

            if(!empty($sErrorDelete)) {
                throw new Exception($sErrorDelete);
            }

        } catch (Exception $e) {
            $sErrorMsg = 'Ошибка очистки уведомлений! ' . $e->getMessage();
            error_log($sErrorMsg, 0); // Команда для чтения error_log: "tailf /var/log/httpd/error_log | while read -r line; do echo -e "$line"; done;"
        }

        return __METHOD__ . '('.$iDaysLive.');';
    }
}
?>