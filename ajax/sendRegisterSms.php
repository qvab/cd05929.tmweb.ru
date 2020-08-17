<?
//файл с механизмом отправки кода подтверждения телефона

//проверяем обязательные поля
//проверяем телефон
if(isset($_POST['phone'])){
    require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
    $_POST['phone'] = getPhoneDigits($_POST['phone']);
    if($_POST['phone'] == ''
        || preg_replace('/[0-9]/s', '', $_POST['phone']) != ''
    ){
        echo 'Укажите корректный телефон';
        exit;
    }
}else{
    echo 'Укажите корректный телефон';
    exit;
}

//проверяем goggle recaptcha
/*if(isset($_POST['token'])){

    //usual register
    //check recaptcha
    $recaptcha_response = '';
    $recaptcha_key = '6LeDzmAUAAAAACcr90tdCuygA__v1xHsEIs37DFe';
    $captcha_error = true;
    if (isset($_POST['token'])) {
        if (!empty($_POST['token'])) {
            $recaptcha_response = $_POST['token'];
            $data = array(
                'secret' => $recaptcha_key,
                'response' => $recaptcha_response,
                'remoteip' => $_SERVER['REMOTE_ADDR']
            );
            $verify = curl_init();
            curl_setopt($verify, CURLOPT_URL, "https://www.google.com/recaptcha/api/siteverify");
            curl_setopt($verify, CURLOPT_POST, true);
            curl_setopt($verify, CURLOPT_POSTFIELDS, http_build_query($data));
            curl_setopt($verify, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($verify, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($verify);
            if ($response ['success'] != true) {
                $captcha_error = true;
            } else {
                $captcha_error = false;
            }
        }
    }

    if ($captcha_error) {
        echo 'Ошибка google-каптчи. Обновите страницу и попробуйте заполнить форму заново. В случае повторения ошибки напишите администрации через форму обраной связи.';
        exit;
    }
}else{
    echo 'Ошибка google-каптчи. Обновите страницу и попробуйте заполнить форму заново. В случае повторения ошибки напишите администрации через форму обраной связи.';
    exit;
}*/

//проверка дублирования телефона
CModule::IncludeModule('iblock');
$el_obj = new CIBlockElement('iblock');
//подготовка телефона (приведение к виду "+7(123)456-78-90")
if(!isset($_POST['restore'])
    || $_POST['restore'] != 'y'
){
    $check_doubles = profilePhoneDoubles($_POST['phone']);
    if(is_numeric($check_doubles)){
        //проверяем был ли подтвержден телефон (если нет, то можно использовать номер)
        $res = CUser::GetList(
            ($by = 'id'), ($order = 'asc'),
            array(
                'ID' => $check_doubles,
                '!UF_FIRST_PHONE' => 1
            ),
            array('FIELDS' => array('ID'))
        );
        if($res->SelectedRowsCount() > 0) {
            echo 'Данный телефон уже зарегистрирован в системе';
            exit;
        }
    }
}

//отправка данных для смс
$old_elements_arr = array();
$hl_id = rrsIblock::HLgetIBlockId('SMSREGISTER');

//проверка наличия и удаление старых запросов
$logObj = new log();
$entityDataClass = $logObj->getEntityDataClass($hl_id);
$el = new $entityDataClass;
$res = $el->getList(array(
        'select' => array('ID', 'UF_TIMESTAMP'),
        'filter' => array(
            'UF_PHONE' => $_POST['phone']
        )
    ));
while ($data = $res->fetch()) {
    //проверка блокировки запроса по времени (1 минута)
    if($data['UF_TIMESTAMP'] > time() - 60){
        echo 'Повторный запрос можно будет направить не раньше чем через минуту с момента прошлой попытки';
        exit;
    }

    $old_elements_arr[$data['ID']] = true;
}
if (count($old_elements_arr) > 0) {
    foreach($old_elements_arr as $cur_el_id => $flag) {
        $el->delete($cur_el_id);
    }
}

//создание новой записи, отправка сообщения и возврат ответа
$check_num = rand(1001, 9999);
$res = $el->add(array(
    'UF_PHONE' => $_POST['phone'],
    'UF_CODE' => $check_num,
    'UF_DATE' => date('d.m.Y H:i:s'),
    'UF_TIMESTAMP' => time()
));
if (!$res->isSuccess()){
    echo 'Ошибка системы. Обновите страницу и попробуйте заполнить форму заново. В случае повторения ошибки напишите администрации через форму обраной связи.';
    exit;
}

//отправка сообщения на номер
//if(isset($_POST['profile']) && $_POST['profile'] == 'y'){
    notice::sendNoticeSMS($_POST['phone'], 'Код подтверждения телефона: ' . $check_num );
//}else{
//    notice::sendNoticeSMS($_POST['phone'], 'Код подтверждения регистрации: ' . $check_num );
//}

echo 1;
exit;