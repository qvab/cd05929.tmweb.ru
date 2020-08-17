<?
//файл с механизмом проверки кода подтверждения телефона

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

//проверяем код подтверждения на корректность
if(!isset($_POST['code'])
    || !filter_var($_POST['code'], FILTER_VALIDATE_INT)
    || strlen($_POST['code']) != 4
){
    echo 'Укажите корректный код подтверждения';
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


//проверяем код подтверждения в БД
$hl_id = rrsIblock::HLgetIBlockId('SMSREGISTER');
$logObj = new log();
$entityDataClass = $logObj->getEntityDataClass($hl_id);
$el = new $entityDataClass;
$res = $el->getList(array(
    'select' => array('ID', 'UF_CODE', 'UF_ATTEMPTS'),
    'filter' => array(
        'UF_PHONE' => $_POST['phone']
    )
));
if($data = $res->fetch()){
    if($data['UF_CODE'] != $_POST['code']){
        //если код подтверждения неверный, то рассчитываем оставшееся количетсво попыток
        if($data['UF_ATTEMPTS'] > 3){
            echo 'Вы исчерпали попытки на подтверждение. Запросите новый код';
            exit;
        }else{
            if($data['UF_ATTEMPTS'] == 3){
                echo 'Неверный код подтверждения (осталось 1 попытка)';
            }else{
                echo 'Неверный код подтверждения (осталось ' . (4 - $data['UF_ATTEMPTS']) . ' попытки)';
            }
            $el->update($data['ID'], array('UF_ATTEMPTS' => $data['UF_ATTEMPTS'] + 1)); //увеличиваем счетчик попыток на 1
            exit;
        }
    }
}else{
    echo 'Не найдены данные телефона. Запросите новый код подтверждения.';
    exit;
}

if(isset($_POST['check_for_doubles'])
    && $_POST['check_for_doubles'] == 'y'
){
    $is_double = profilePhoneDoubles($_POST['phone']);
    if(intval($is_double) > 0){
        //дополнительно проверяем, что телефон уже был активирован
        $res = CUser::GetList(
            ($by = 'id'), ($order = 'asc'),
            array('ID' => $is_double, 'UF_FIRST_PHONE' => 1),
            array('FIELDS' => array('ID'))
        );
        if($res->SelectedRowsCount() == 0){
            echo 'Данный телефон уже зарегистрирован в системе (Вы можете восстановить аккаунт по данному телефону).';
            exit;
        }
    }
}

$_SESSION['success_sms_' . $_POST['phone']] = 'y';
echo 1; //успех операции

//если требуется также дать ссылку для восстановления пароля, то дополняем вывод
if(isset($_POST['restore']) && $_POST['restore'] == 'y'){
    //ищем пользователя
    $user_login = '';
    $user_id = 0;

    //ищем среди регистрировавшихся по телефону
    $res = CUser::GetList(
        ($by = 'id'), ($order = 'asc'),
        array('EMAIL' => 'p' . $_POST['phone'] . '@agrohelper.ru', 'ACTIVE' => 'Y', '!UF_FIRST_PHONE' => 1),
        array('FIELDS' => array('ID', 'LOGIN'))
    );
    if($data = $res->Fetch()){
        $user_id = $data['ID'];
        $user_login = $data['LOGIN'];
    }

    //ищем среди поставщиков
    if($user_id == 0){
        CModule::IncludeModule('iblock');
        //подготовка телефона (приведение к виду "+7 (123) 456-78-90")
        $_POST['phone'] = makeCorrectPhone($_POST['phone']);
        $res = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('farmer_profile'),
                'PROPERTY_PHONE' => $_POST['phone'],
                'ACTIVE' => 'Y'
            ),
            false,
            false,
            array('PROPERTY_USER')
        );
        if($data = $res->Fetch()){
            //пользователь найден
            if(is_numeric($data['PROPERTY_USER_VALUE'])) {
                $user_id = $data['PROPERTY_USER_VALUE'];
            }
        }
    }

    //ищем среди покупателей
    if($user_id == 0){
        $res = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('client_profile'),
                'PROPERTY_PHONE' => $_POST['phone'],
                'ACTIVE' => 'Y'
            ),
            false,
            false,
            array('PROPERTY_USER')
        );
        if($data = $res->Fetch()){
            //пользователь найден
            if(is_numeric($data['PROPERTY_USER_VALUE'])) {
                $user_id = $data['PROPERTY_USER_VALUE'];
            }
        }
    }

    //ищем среди агентов поставщиков
    if($user_id == 0){
        $res = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('agent_profile'),
                'PROPERTY_PHONE' => $_POST['phone'],
                'ACTIVE' => 'Y'
            ),
            false,
            false,
            array('PROPERTY_USER')
        );
        if($data = $res->Fetch()){
            //пользователь найден
            if(is_numeric($data['PROPERTY_USER_VALUE'])) {
                $user_id = $data['PROPERTY_USER_VALUE'];
            }
        }
    }

    //ищем среди агентов покупателей
    if($user_id == 0){
        $res = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('client_agent_profile'),
                'PROPERTY_PHONE' => $_POST['phone'],
                'ACTIVE' => 'Y'
            ),
            false,
            false,
            array('PROPERTY_USER')
        );
        if($data = $res->Fetch()){
            //пользователь найден
            if(is_numeric($data['PROPERTY_USER_VALUE'])) {
                $user_id = $data['PROPERTY_USER_VALUE'];
            }
        }
    }

    if($user_id > 0 && $user_login == ''){
        //получаем логин пользователя
        $res = CUser::GetList(
            ($by = 'id'), ($order = 'asc'),
            array('ID' => $user_id, 'ACTIVE' => 'Y'),
            array('FIELDS' => array('LOGIN'), 'SELECT' => array('UF_FIRST_PHONE', 'UF_AGENT_ADDED'))
        );
        if($data = $res->Fetch()){
            //если телефон был подтвержден пользователем,
            // либо пользователь был добавлен агентом,
            // то доверяем указанному в профиле телефону
            if($data['UF_FIRST_PHONE'] != 1
                || $data['UF_AGENT_ADDED'] == 1
            ) {
                $user_login = $data['LOGIN'];
            }
        }
    }

    echo ';';
    if($user_id > 0 && $user_login != ''){
        //добавляем в вывод ссылку на смену пароля (пустая строка если не удалось сгенерировать ссылку)
        $restore_href = makeRestoreLinkForUser($user_id, $user_login, $GLOBALS['host'] . '/');
        if($restore_href != ''){
            $restore_href .= '&type=phone';
        }
        echo $restore_href;
    }

    //ищем среди транспортных компаний (не нужно ?)
    //ищем среди организаторов (не нужно ?)
}

exit;