<?
/*
 * Класс для работы c запросами покупателя
 */

class ClientRequestsOld {
    public static function Exec($model, $data) {
        $headers['HTTP'] = 200;

        //проверка авторизованности пользователя
        $resultData = Auth::CheckAuthorize($data["x-auth-key"], $data["x-auth-timestamp"], $data["x-auth-token"]);
        if (intval($resultData['USER_ID']) > 0) {
            $data['userAccID'] = $resultData['USER_ID'];

            switch ($model) {
                case 'get':
                    if (intval($data['request_id']) > 0) {
                        if(isset($data['copy'])){
                            if ($data['copy'] == 'true') {
                                //Получение информации о запросе для копирования
                                if(isset($data['hash_val'])){
                                    if(strlen($data['hash_val']) != 32){
                                        $resultData['ERROR'] = Agrohelper::getErrorMessage('CopyRequestInfoHashError');
                                    }
                                    else{
                                        $resultData = self::GetRequestCopyFromTable($data['hash_val'], $data['userAccID']);
                                        if($resultData === false){
                                            $resultData = array(
                                                'ERROR' => Agrohelper::getErrorMessage('CopyRequestInfoHashError')
                                            );
                                        }
                                        $outputData = $resultData;
                                    }
                                }
                                else{
                                    $resultData = self::GetRequestCopy($data);
                                    $outputData = $resultData;
                                }
                            }
                            else{
                                $resultData['ERROR'] = Agrohelper::getErrorMessage('CopyRequestError');
                            }
                        }
                        else {
                            //Получение информации о запросе
                            $resultData = self::GetRequest($data);
                        }
                    }
                    else {
                        //Получение списка запросов
                        $resultData = self::GetRequests($data);
                        $outputData = $resultData;
                    }
                    $outputData = $resultData;
                    break;
                case 'post':
                    if ($data['event'] == 'deactivate') {
                        //Деактивация запроса
                        $resultData = self::DeactivateRequest($data);
                        $outputData = array('success' => 1);
                    }
                    elseif ($data['event'] == 'copy' || isset($data['data']['event']) && $data['data']['event'] == 'copy') {
                        //Создание нового запроса путем копирования (проверка из json данных, переданных в теле запроса!)

                        if(isset($data['data']) && is_array($data['data']) && count($data['data']) > 0)
                        {
                            $data['data']['userAccID'] = $data['userAccID'];
                        }

                        $urgency_list = rrsIblock::getElementList(rrsIblock::getIBlockId('urgency'), 'ID');

                        //проверка наличия обязательных данных
                        //проверка корректного request_id
                        if(isset($data['data']['request_id']) && filter_var($data['data']['request_id'], FILTER_VALIDATE_INT)){
                            //проверка, что request_id принадлежит пользователю
                            if(client::getCompareRequestListWithClient($data['data']['request_id'], $data['data']['userAccID'])){
                                //проверка, что передан корректный urgency
                                //if(isset($data['data']['urgency']) && filter_var($data['data']['urgency'], FILTER_VALIDATE_INT) && isset($urgency_list[$data['data']['urgency']])){
                                    //проверка, что передан корректный volume
                                    if(isset($data['data']['volume']) && filter_var($data['data']['volume'], FILTER_VALIDATE_INT)){
                                        //проверка, что переданы корректные цены складов
                                        if(isset($data['data']['warehouse']) && client::checkCountedWarehouses($data['data']['warehouse'])){
                                            //проверка, что у пользователя есть привязка к организатору
                                            CModule::IncludeModule('iblock');
                                            $linkedPartner = client::getLinkedPartner($data['data']['userAccID']);
                                            if($linkedPartner != 0){
                                                $data['data']['linked_partner'] = $linkedPartner;
                                                //проверка наличия валидного хеш-кода
                                                if(isset($data['data']['hash_val'])){
                                                    if(strlen($data['data']['hash_val']) != 32){
                                                        $resultData['ERROR'] = Agrohelper::getErrorMessage('CopyRequestHashError');
                                                    }
                                                    else{
                                                        $resultData = self::CopyRequestFromTable($data['data']['hash_val']);
                                                        if($resultData === false){
                                                            $resultData = array(
                                                                'ERROR' => Agrohelper::getErrorMessage('CopyRequestHashError')
                                                            );
                                                        }
                                                        $outputData = $resultData;
                                                    }
                                                }
                                                else
                                                {
                                                    $resultData = self::CopyRequest($data['data']);
                                                    $outputData = $resultData;
                                                }
                                            }
                                            else{
                                                $resultData['ERROR'] = Agrohelper::getErrorMessage('CopyRequestNoRights');
                                                $headers['HTTP'] = 404;
                                            }
                                        }
                                        else{
                                            $resultData['ERROR'] = Agrohelper::getErrorMessage('CopyRequestNoWarehouse');
                                            $headers['HTTP'] = 404;
                                        }
                                    }
                                    else{
                                        $resultData['ERROR'] = Agrohelper::getErrorMessage('CopyRequestNoVolume');
                                        $headers['HTTP'] = 404;
                                    }
                                /*}
                                else{
                                    $resultData['ERROR'] = Agrohelper::getErrorMessage('CopyRequestNoUrgency');
                                    $headers['HTTP'] = 404;
                                }*/
                            }
                            else{
                                $resultData['ERROR'] = Agrohelper::getErrorMessage('CopyRequestNoId');
                                $headers['HTTP'] = 404;
                            }
                        }
                        else{
                            $resultData['ERROR'] = Agrohelper::getErrorMessage('CopyRequestNoId');
                            $headers['HTTP'] = 404;
                        }
                    }
                    elseif ($data['event'] == 'prolong'
                        || (isset($data['data']['event'])
                            && $data['data']['event'] == 'prolong'
                        )
                    ){//функционал продления запроса покупателем
                        //если данные переданы в теле запроса, то используем их
                        if(isset($data['data']['event'])){
                            $data = array_merge($data, $data['data']);
                            unset($data['data']);
                        }

                        if(isset($data['request_id']) && filter_var($data['request_id'], FILTER_VALIDATE_INT)){
                            if(isset($data['hash_val'])){
                                //получение данных по хэшу
                                if(strlen($data['hash_val']) != 32){
                                    $resultData['ERROR'] = Agrohelper::getErrorMessage('ProlongateRequestHashError');
                                }
                                else{
                                    $resultData = self::ProlongateRequestFromTable($data);
                                    if($resultData === false){
                                        $resultData = array(
                                            'ERROR' => Agrohelper::getErrorMessage('ProlongateRequestHashError')
                                        );
                                    }
                                    $outputData = $resultData;
                                }
                            }elseif(self::RequestCanBePrologated($data)){
                                //создание данных для хэша
                                $resultData = self::ProlongateRequest($data);
                                $outputData = $resultData;
                            }else{
                                $resultData['ERROR'] = Agrohelper::getErrorMessage('ProlongateRequestNoData');
                                $headers['HTTP'] = 404;
                            }
                        }
                        else{
                            $resultData['ERROR'] = Agrohelper::getErrorMessage('ProlongateRequestNoId');
                            $headers['HTTP'] = 404;
                        }
                    }
                    else {
                        $resultData['ERROR'] = Agrohelper::getErrorMessage('incorrectRequest');
                        $headers['HTTP'] = 404;
                    }
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
     * Получение списка запросов
     *
     * @access  public
     * @param   [] $data массив с полями
     * @return  [] список запросов покупателя
     */
    public static function GetRequests($data) {
        $result = array('requests' => client::getRequestListByUser($data['userAccID']));

        //вывод ошибки, если требуется
        $linkedPartner = client::getLinkedPartner($data['userAccID']);
        if($linkedPartner == 0){
            $result['warnings'] = array(
                0 => array(
                    'url' => $GLOBALS['host'] . '/client/link_to_partner/',
                    'text' => 'Для создания запроса необходимо привязаться к организатору'
                )
            );

            $arClient = CUser::GetByID($data['userAccID'])->Fetch();
            if ($arClient['UF_API_KEY'])
                $result['warnings'][0]['url'] .= '?dkey='.$arClient['UF_API_KEY'];
        }

        return $result;
    }

    /**
     * Получение информации о запросе
     *
     * @access  public
     * @param   [] $data массив с полями
     * @return  [] информация о запросе покупателя
     */
    public static function GetRequest($data) {

        $result = client::getRequestDataApi($data);

        //добавление параметра безшовной авторизации к ссылке, если таковой установлен
        $arClient = CUser::GetByID($data['userAccID'])->Fetch();
        if($arClient['UF_API_KEY'])
            $result['request']['request_url'] .= '&dkey='.$arClient['UF_API_KEY'];

        //вывод ошибки, если требуется
        $linkedPartner = client::getLinkedPartner($data['userAccID']);
        if($linkedPartner == 0){
            $result['request']['warnings'] = array(
                0 => array(
                    'url'   => $GLOBALS['host'] . '/client/link_to_partner/',
                    'text'  => 'Для создания запроса необходимо привязаться к организатору'
                )
            );

            if($arClient['UF_API_KEY'])
                $result['request']['warnings'][0]['url'] .= '?dkey='.$arClient['UF_API_KEY'];
        }

        return $result;
    }

    /**
     * Получение информации о запросе, которая необходима для копирования (создание хеш записи и пуск расчета)
     *
     * @access  public
     * @param   [] $data массив с полями
     * @return  [] информация о запросе покупателя
     */
    public static function GetRequestCopy($data) {
        $result = array();

        $hrh_obj = new HighloadRequestsHash(9);
        $new_hash = $hrh_obj->HashGen();
        if($hrh_obj->AddNewNote($new_hash, 'get_request_copy_info'))
        {
            $file_path = $GLOBALS['domain_href'];
            if(!is_dir($file_path . '/upload/hash_temp')){
                mkdir($file_path . '/upload/hash_temp', 2775);
            }
            exec("php -f {$file_path}/include/calculate_api_request_data.php hash_val={$new_hash} userAccID={$data['userAccID']} request_id={$data['request_id']} >> {$file_path}/upload/hash_temp/{$new_hash}.txt 2>&1 &");

//            send_async_socket_request($GLOBALS['domain_name'], '/include/calculate_api_request_data.php', array(
//                'hash_val'      => $new_hash,
//                'userAccID'     => $data['userAccID'],
//                'request_id'    => $data['request_id']
//            ), 'GET', $ssl);
        }

        $result['success_hash'] = $new_hash;

        return $result;
    }

    /**
     * Получение информации о запросе по хэш-коду
     *
     * @access  public
     * @param   [] $data массив с полями
     * @return  [] информация о запросе покупателя
     */
    public static function GetRequestCopyFromTable($hash_val, $user_id) {
        $result = array();

        $hrh_obj = new HighloadRequestsHash(9);
        $data_val = $hrh_obj->GetNoteData($hash_val);
        if($data_val == -1)
        {
            $result['success_hash'] = $hash_val;
        }
        elseif($data_val == false)
        {
            $result['request']['warnings'] = array(
                'text' => 'Данные для копирования запроса не найдены. Требуется повторный запрос расчета данных'
            );
        }
        else
        {
            $result = json_decode($data_val, true);

            //проверка на возможность копирования запроса
            CModule::IncludeModule('iblock');
            $linkedPartner = client::getLinkedPartner($user_id);
            if($linkedPartner == 0){
                $result['request']['warnings'] = array(
                    0 => array(
                        'url'   => $GLOBALS['host'] . '/client/link_to_partner/',
                        'text'  => 'Для создания запроса необходимо привязаться к организатору'
                    )
                );

                $arClient = CUser::GetByID($user_id)->Fetch();
                if($arClient['UF_API_KEY'])
                    $result['request']['warnings'][0]['url'] .= '?dkey='.$arClient['UF_API_KEY'];
            }
        }

        return $result;
    }

    /**
     * Деактивация запроса
     *
     * @access  public
     * @param   [] $data массив с полями
     * @return  bool true в случае успешной деактивации запроса
     */
    public static function DeactivateRequest($data){
        $result = array();

        if(!isset($data['request_id']) || !filter_var($data['request_id'], FILTER_VALIDATE_INT)){
            $result['ERROR'] = Agrohelper::getErrorMessage('DeactivateRequestNoRequest');
        }
        else{
            $result = client::deactivateRequestApi($data);
            //убираем пары запрос - товар из базы
            if($result === true){
                $filter = array(
                    'UF_REQUEST_ID' => $data['request_id']
                );
                $arLeads = lead::getLeadList($filter);
                if (is_array($arLeads) && sizeof($arLeads) > 0) {
                    lead::deleteLeads($arLeads);
                }
            }
        }

        return $result;
    }

    /**
     * Создание нового запроса путем копирования (данные расчитываются отложенно, кладутся в таблицу и будут доступны по хеш-коду)
     *
     * @access  public
     * @param   [] $data массив с полями
     * @return  [] идентификатор созданного запроса, количество поставщиков, получивших данный запрос
     */
    public static function CopyRequest($data) {

        $result = array();

        $hrh_obj = new HighloadRequestsHash(9);
        $new_hash = $hrh_obj->HashGen();
        if($hrh_obj->AddNewNote($new_hash, 'request_copy'))
        {
            /*
            //производим копирование
            $new_id = client::copyRequestApi($data);

            if($new_id == -1)
                $result['ERROR'] = Agrohelper::getErrorMessage('CopyRequestError2');
            elseif($new_id > 0)
            {
                //кладем данные в таблицу
                $write_res = array(
                    'id'        => $new_id,
                    'num'       => 0,
                    'message'   => 'Запрос успешно создан',
                    'success'   => 1
                );
                $hrh_obj->UpdateNote($new_hash, json_encode($write_res));

                $param_str = "hash_val={$new_hash} userAccID={$data['userAccID']} request_id={$data['request_id']} event={$data['event']} urgency={$data['urgency']} volume={$data['volume']} reqID={$new_id}";
                foreach($data['warehouse'] as $cur_id => $cur_data)
                {
                    $param_str.= " warehouse[{$cur_id}]=" . str_replace(' ', '', $cur_data);
                }

                //запуск рассчета пар
                $file_path = $GLOBALS['domain_href'];
                exec("php -f {$file_path}/include/calculate_api_request_copy.php {$param_str} >> {$file_path}/include/hash_temp/{$new_hash}.txt 2>&1 &");

                $result = array('success_hash' => $new_hash);
            }
            else
            {
                $result['ERROR'] = Agrohelper::getErrorMessage('CopyRequestError');
            }*/

            //$param_str = "hash_val={$new_hash} userAccID={$data['userAccID']} request_id={$data['request_id']} event={$data['event']} urgency={$data['urgency']} volume={$data['volume']}";
            $param_str = "hash_val={$new_hash} userAccID={$data['userAccID']} request_id={$data['request_id']} event={$data['event']} volume={$data['volume']}";
            foreach($data['warehouse'] as $cur_id => $cur_data)
            {
                $param_str.= " warehouse[{$cur_id}]=" . str_replace(' ', '', $cur_data);
            }

            //запуск рассчета пар
            $file_path = $GLOBALS['domain_href'];
            if(!is_dir($file_path . '/upload/hash_temp')){
                mkdir($file_path . '/upload/hash_temp', 2775);
            }
            exec("php -f {$file_path}/include/calculate_api_request_copy.php {$param_str} >> {$file_path}/upload/hash_temp/{$new_hash}.txt 2>&1 &");

            $result = array('success_hash' => $new_hash);
        }

        return $result;
    }

    /**
     * Создание нового запроса путем копирования (получение данных созданной копии запроса)
     *
     * @access  public
     * @param   [] $data массив с полями
     * @return  [] идентификатор созданного запроса, количество поставщиков, получивших данный запрос
     */
    public static function CopyRequestFromTable($hash_val) {

        $result = array();

        $hrh_obj = new HighloadRequestsHash(9);
        $data_val = $hrh_obj->GetNoteData($hash_val);
        if($data_val == -1){
            $result['success_hash'] = $hash_val;
        }
        elseif($data_val == false){
            $result = false;
        }
        else{
            $result = json_decode($data_val, true);
        }

        return $result;
    }

    /**
     * Пролонгация запроса покупателем (создание записи в таблице)
     *
     * @param [] $data массив с полями
     * @return [] хэш записи в таблице
     */
    public static function ProlongateRequest($data) {

        $result = array();

        $hrh_obj = new HighloadRequestsHash(9);
        $new_hash = $hrh_obj->HashGen();
        if($hrh_obj->AddNewNote($new_hash, 'request_prolong')){
            $param_str = "hash_val={$new_hash} userAccID={$data['userAccID']} request_id={$data['request_id']} event={$data['event']}";

            //запуск рассчета пар
            $file_path = $GLOBALS['domain_href'];
            if(!is_dir($file_path . '/upload/hash_temp')){
                mkdir($file_path . '/upload/hash_temp', 2775);
            }

            exec("php -f {$file_path}/include/calculate_api_request_prolongate.php {$param_str} >> {$file_path}/upload/hash_temp/{$new_hash}.txt 2>&1 &");

            $result = array('success_hash' => $new_hash);
        }

        return $result;
    }

    /**
     * Пролонгация запроса покупателем (получение данных по хэшу)
     *
     * @param [] $data массив с полями
     * @return [] данные запроса
     */
    public static function ProlongateRequestFromTable($data) {

        $result = array();

        $hrh_obj = new HighloadRequestsHash(9);
        $data_val = $hrh_obj->GetNoteData($data['hash_val']);
        if($data_val == -1){
            $result['success_hash'] = $data['hash_val'];
        }
        elseif($data_val == false){
            $result = false;
        }
        else{
            $result = json_decode($data_val, true);
        }

        return $result;
    }

    /**
     * проверка того может ли быть продлен запрос
     *
     * @param [] $data массив с полями
     * @return boolean флаг разрешения продления запроса
     */
    public static function RequestCanBePrologated($req_data) {
        $result = false;

        CModule::IncludeModule('iblock');

        $el_obj = new CIBlockElement;

        $res = $el_obj->GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('client_request'),
                'ID'        => $req_data['request_id']
            ),
            false,
            array('nTopCount' => 1),
            array('ID', 'ACTIVE_TO', 'PROPERTY_VOLUME', 'PROPERTY_CLIENT', 'PROPERTY_ACTIVE', 'PROPERTY_IS_PROLONGATED')
        );
        if($data = $res->Fetch()){
            $temp_uid = 0;
            if(isset($data['PROPERTY_CLIENT_VALUE'])
                && is_numeric($data['PROPERTY_CLIENT_VALUE'])
            ){
                $temp_uid = $data['PROPERTY_CLIENT_VALUE'];
            }

            //проверка является ли покупатель владельцем запроса
            if($req_data['userAccID'] == $temp_uid){
                //проверка данных запроса и выполнения условия продления
                $tmstmp_diff = floor((strtotime($data['ACTIVE_TO']) - time())/3600);
                if(requestCanBePrologated($tmstmp_diff,
                    $data['PROPERTY_ACTIVE_ENUM_ID'] == rrsIblock::getPropListKey('client_request', 'ACTIVE', 'yes'),
                    $data['PROPERTY_IS_PROLONGATED_ENUM_ID'] == rrsIblock::getPropListKey('client_request', 'IS_PROLONGATED', 'yes')
                    ) != 'n'
                ){//запрос можно продлить
                    $result = true;
                }else{
                    //запрос не подлежит продлению
                }
            }
        }

        return $result;
    }
}
?>