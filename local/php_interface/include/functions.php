<?
function p($arg, $is_exit = 0) {
    echo "<pre>";
    if(is_array($arg))
        print_r($arg);
    else
        print($arg);
    echo "</pre>";

    if($is_exit != 0)
        exit;
}

function f_exp($a, $b, $x) {
    return $a * exp ($b * $x);
}

function f_lin($a, $b, $x) {
    return $a + $b * $x;
}

function price ($p) {
    $p = round($p, 0);
    $r = $p%10;
    $p = 10 * floor(0.1 * $p);
    if ($r > 2 && $r < 8)
        $p += 5;
    elseif ($r > 7)
        $p += 10;

    return $p;
}

function hashPass($len = 12) {
    if($len > 27)
        $len = 27;

    $hash = substr(md5(time() . "- text hash additional value " . mt_rand(1000, 255000)), 4, $len);
    return $hash;
}

function getMonthName($month) {
    $arMonth = array(
        1 => "января", 2 => "февраля", 3 => "марта", 4 => "апреля", 5 => "мая", 6 => "июня",
        7 => "июля", 8 => "августа", 9 => "сентября", 10 => "октября", 11 => "ноября", 12 => "декабря"
    );

    return $arMonth[intval($month)];
}

function orderRcPrice($a, $b) {
    if ($a['REQUEST']['BEST_PRICE']['ACC_PRICE_CSM'] == $b['REQUEST']['BEST_PRICE']['ACC_PRICE_CSM'])
        return 0;
    return ($a['REQUEST']['BEST_PRICE']['ACC_PRICE_CSM'] > $b['REQUEST']['BEST_PRICE']['ACC_PRICE_CSM']) ? -1 : 1;
}

//get data through contur-focus api
function getConturData($params_arr, $type = '/api3/req')
{
    $answ_str = '';

    if(!is_array($params_arr) || count($params_arr) == 0)
    {
        $answ_str = 'error';
    }
    else
    {
        $options = array(
            CURLOPT_RETURNTRANSFER => true,     // return web page
            CURLOPT_HEADER         => false,    // don't return headers
            CURLOPT_FOLLOWLOCATION => true,     // follow redirects
            CURLOPT_ENCODING       => "",       // handle all encodings
            CURLOPT_AUTOREFERER    => true,     // set referer on redirect
            CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
            CURLOPT_TIMEOUT        => 120,      // timeout on response
            CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects
            CURLOPT_SSL_VERIFYPEER => false     // Disabled SSL Cert checks
        );

        $ch = curl_init('https://focus-api.kontur.ru' . $type . '?key=' . $GLOBALS['conturKey'] . '&pdf=False&' . http_build_query($params_arr));
        curl_setopt_array($ch, $options);
        $answ_str = curl_exec($ch);
        curl_close($ch);
    }

    return $answ_str;
}

function morph($n, $f1, $f2, $f5) {
    $n = abs(intval($n)) % 100;
    if ($n>10 && $n<20) return $f5;
    $n = $n % 10;
    if ($n>1 && $n<5) return $f2;
    if ($n==1) return $f1;
    return $f5;
}

function flex($k) {
    if ($k%10==1 && $k%100!=11) $fl = 'получил ' . $k . ' поставщик';
    elseif (($k%10==2 && $k%100!=12) || ($k%10==3 && $k%100!=13) || ($k%10==4 && $k%100!=14)) $fl = 'получило ' . $k . ' поставщика';
    else $fl = 'получило ' . $k . ' поставщиков';
    return $fl;
}

//function for deleting (for example 'PROP__') string from array elements value
function delPropStrFromVal(&$elem, $key, $del_string)
{
    $elem = str_replace($del_string, '', $elem);
}

//function for adding (for example 'PROPERTY_') string to array elements value
function addPropStrToVal(&$elem, $key, $add_string)
{
    $elem = $add_string . $elem;
}

//async request through socket (need configured sockets on the server)
function send_async_socket_request($domain, $path = '/', $params = array(), $r_type = 'GET', $is_ssl = false)
{
    $post_params = array();
    foreach ($params as $key => &$val) {
        if (is_array($val))
        {
            foreach($val as $n_key => $n_val)
            {
                $post_params[] = $key . '[' . $n_key . ']=' . $n_val;
            }
        }
        else
        {
            $post_params[] = $key.'='.urlencode($val);
        }
    }

    if($r_type != 'GET' && $r_type != 'POST')
        $r_type = 'GET';

    if($path == '' || $path == false)
        $path = '/';

    $post_string = implode('&', $post_params);

    if($r_type == 'GET' && $post_string != '')
    {
        $path .= '?' . $post_string;
    }

    $fp = fsockopen(($is_ssl ? 'ssl://' : ''). $domain, ($is_ssl ? 443 : 80) , $errno, $errstr, 30);
    if(!$fp)
        return "{$errstr} ({$errno})";

    $out = "{$r_type} {$path} HTTP/1.1\r\n";
    $out.= "Host: {$domain}\r\n";
    $out.= "Content-Type: application/x-www-form-urlencoded\r\n";
    $out.= "Content-Length: ".strlen($post_string)."\r\n";
    $out.= "Connection: Close\r\n\r\n";

    if (isset($post_string))
        $out.= $post_string;

    $res = fwrite($fp, $out);
    fclose($fp);

    return intval($res);
}

/**
 * Получение информации о подписантах
 */
function getSignerList() {
    $result = array();

    $obCache = new CPHPCache;
    $life_time = 86400;
    $cache_id = 'getSignerList';
    if ($obCache->InitCache($life_time, $cache_id, "/")) {
        $vars = $obCache->GetVars();
        $result = $vars[$cache_id];
    }
    else {
        $db_list = CIBlockSection::GetList(array('ID' => 'ASC'), array('IBLOCK_ID' => rrsIblock::getIBlockId('signers'), 'ACTIVE' => 'Y')
        );
        while ($ar_result = $db_list->GetNext()) {
            $sections[$ar_result['ID']] = $ar_result['CODE'];
        }

        $res = CIBlockElement::GetList(
            array('SORT' => 'ASC'),
            array('IBLOCK_ID' => rrsIblock::getIBlockId('signers'), 'ACTIVE' => 'Y'),
            false,
            false,
            array('ID', 'NAME', 'CODE', 'IBLOCK_SECTION_ID')
        );
        while ($ob = $res->Fetch()) {
            $result[$sections[$ob['IBLOCK_SECTION_ID']]][$ob['ID']] = $ob;
        }
    }

    return $result;
}

/**
 * Получение суммы прописью в виде #RUB# (#RUB_STR#) руб. #KOP# коп.
 */
function price2Str($number, $asPrice = true) {
    $words = array(
        'null' => 'ноль',
        0 => '', '_0' => '', 1 => 'один', 2 => 'два', 3 => 'три', 4 => 'четыре', 5 => 'пять', 6 => 'шесть', 7 => 'семь',
        8 => 'восемь', 9 => 'девять', '_1' => 'одна', '_2' => 'две', '_3' => 'три', '_4' => 'четыре',
        '_5' => 'пять', '_6' => 'шесть', '_7' => 'семь', '_8' => 'восемь', '_9' => 'девять',
        11 => 'одиннадцать', 12 => 'двенадцать', 13 => 'тринадцать', 14 => 'четырнадцать', 15 => 'пятнадцать',
        16 => 'шестнадцать', 17 => 'семнадцать', 18 => 'восемнадцать', 19 => 'девятнадцать',
        10 => 'десять', 20 => 'двадцать',30 => 'тридцать', 40 => 'сорок', 50 => 'пятьдесят', 60 => 'шестьдесят',
        70 => 'семьдесят', 80 => 'восемьдесят', 90 => 'девяносто',
        100 => 'сто', 200 => 'двести', 300 => 'триста', 400 => 'четыреста', 500 => 'пятьсот', 600 => 'шестьсот',
        700 => 'семьсот', 800 => 'восемьсот', 900 => 'девятьсот',
        '1_1' => ' тысяча', '1_2' => ' тысячи', '1_5' => ' тысяч',
        '2_1' => ' миллион', '2_2' => ' миллиона', '2_5' => ' миллионов',
        '3_1' => ' миллиард', '3_2' => ' миллиарда', '3_5' => ' миллиардов',
        '0_1' => '', '0_2' => '', '0_5' => '', '4_1' => '', '4_2' => '', '4_5' => '', '5_1' => '', '5_2' => '', '5_5' => '',
        'r1' => ' руб.', 'r2' => ' руб.', 'r5' => ' руб.', 'cp' => 'коп.'
    );
    $number = str_replace(',', '.', '' . floatval($number));
    $number = explode('.', $number);
    $kop = substr((isset($number[1]) ? $number[1].'00' : '00'), 0, 2);
    $number = $number[0];
    $rub = $number;
    if (intval($number) == 0) {
        $result = $words['null'];
    }
    else {
        $parts = str_split($number, 3);
        while (strlen($parts[count($parts) - 1]) < 3) {
            $number = '0' . $number;
            $parts = str_split($number, 3);
        }
        $parts = array_reverse($parts);
        foreach ($parts as $key => $part) {
            $val = intval(substr($part, -2, 2));
            if ($val > 10 && $val < 20) {
                $label = $key . '_5';
                $string = $words[$val];
                $val = intval($part) - $val;
                $string = $words[$val] . ' ' . $string;
            }
            else {
                list($a, $b, $c) = str_split($part);
                $a *= 100;
                $b *= 10;
                $c *= 1;
                $string = trim($words[$a] . ' ' . $words[$b] . ' ' . $words[($key == 1 ? '_' . $c : $c)]);
                $label = $key . (($c == 1) ? '_1' : (($c > 1 && $c < 5) ? '_2' : '_5'));
            }
            $string .= $words[$label];
            $parts[$key] = trim($string);
        }
        $parts = array_reverse($parts);
        $result = number_format($rub, 0, ',', ' ') . ' (' . implode(' ', $parts) . ')';
    }

    if ($asPrice) {
        $c = intval(substr($number, -1, 1));
        $label = (($c == 1) ? 'r1' : (($c > 1 && $c < 5) ? 'r2' : 'r5'));
        $result .= $words[$label] . ' ' . $kop . ' ' . $words['cp'];
    }

    return $result;
}

/**
 * Запись числа прописью
 */
function num2str($inn, $stripkop=false) {
    $nol = 'ноль';
    $str[100]= array('','сто','двести','триста','четыреста','пятьсот','шестьсот', 'семьсот', 'восемьсот','девятьсот');
    $str[11] = array('','десять','одиннадцать','двенадцать','тринадцать', 'четырнадцать','пятнадцать','шестнадцать','семнадцать', 'восемнадцать','девятнадцать','двадцать');
    $str[10] = array('','десять','двадцать','тридцать','сорок','пятьдесят', 'шестьдесят','семьдесят','восемьдесят','девяносто');
    $sex = array(
        array('','один','два','три','четыре','пять','шесть','семь', 'восемь','девять'),// m
        array('','одна','две','три','четыре','пять','шесть','семь', 'восемь','девять') // f
    );
    $forms = array(
        array('копейка', 'копейки', 'копеек', 1), // 10^-2
        array('рубль', 'рубля', 'рублей',  0), // 10^ 0
        array('тысяча', 'тысячи', 'тысяч', 1), // 10^ 3
        array('миллион', 'миллиона', 'миллионов',  0), // 10^ 6
        array('миллиард', 'миллиарда', 'миллиардов',  0), // 10^ 9
        array('триллион', 'триллиона', 'триллионов',  0), // 10^12
    );
    $out = $tmp = array();
    // Поехали!
    $tmp = explode('.', str_replace(',','.', $inn));
    $rub = number_format($tmp[ 0], 0,'','-');
    if ($rub== 0) $out[] = $nol;
    // нормализация копеек
    $kop = isset($tmp[1]) ? substr(str_pad($tmp[1], 2, '0', STR_PAD_RIGHT), 0,2) : '00';
    $segments = explode('-', $rub);
    $offset = sizeof($segments);
    if ((int)$rub== 0) { // если 0 рублей
        $o[] = $nol;
        $o[] = morph( 0, $forms[1][ 0],$forms[1][1],$forms[1][2]);
    }
    else {
        foreach ($segments as $k=>$lev) {
            $sexi= (int) $forms[$offset][3]; // определяем род
            $ri = (int) $lev; // текущий сегмент
            if ($ri== 0 && $offset>1) {// если сегмент==0 & не последний уровень(там Units)
                $offset--;
                continue;
            }
            // нормализация
            $ri = str_pad($ri, 3, '0', STR_PAD_LEFT);
            // получаем циферки для анализа
            $r1 = (int)substr($ri, 0,1); //первая цифра
            $r2 = (int)substr($ri,1,1); //вторая
            $r3 = (int)substr($ri,2,1); //третья
            $r22= (int)$r2.$r3; //вторая и третья
            // разгребаем порядки
            if ($ri>99) $o[] = $str[100][$r1]; // Сотни
            if ($r22>20) {// >20
                $o[] = $str[10][$r2];
                $o[] = $sex[ $sexi ][$r3];
            }
            else { // <=20
                if ($r22>9) $o[] = $str[11][$r22-9]; // 10-20
                elseif($r22> 0) $o[] = $sex[ $sexi ][$r3]; // 1-9
            }
            // Рубли
            $o[] = morph($ri, $forms[$offset][ 0],$forms[$offset][1],$forms[$offset][2]);
            $offset--;
        }
    }
    // Копейки
    if (!$stripkop) {
        $o[] = $kop;
        $o[] = morph($kop,$forms[ 0][ 0],$forms[ 0][1],$forms[ 0][2]);
    }
    return preg_replace("/\s{2,}/",' ',implode(' ',$o));
}

function getUserType($uid) {

    $arResult = [
        'ID'    => 0,
        'TYPE'  => null,
    ];

    $obUser = new CUser;
    $arUserGroups = $obUser->GetUserGroup($uid);

    $arTypeRole = ['p', 't', 'f', 'c', 'ag', 'agc', 'rm',];

    foreach ($arTypeRole as $sCodeRole) {

        if (in_array(getGroupIdByRole($sCodeRole), $arUserGroups)) {

            $arResult['ID']     = getGroupIdByRole($sCodeRole);
            $arResult['TYPE']   = $sCodeRole;

            break;
        }
    }
    return $arResult;
}

/**
 * Объединение массивов с ключами в виде чисел
 * @param $first
 * @param $second
 * @return array
 */
function array_merge_numkeys($first, $second) {
    $result = array();
    foreach($first as $key => $value) {
        $result[$key] = $value;
    }
    foreach($second as $key => $value) {
        $result[$key] = $value;
    }
    return $result;
}

/**
 * Проверка возможности продления запроса
 * @param int $date_difference разница в секундах между датой окончания активности и текущей меткой времени
 * @param boolean $is_active флаг активности запроса (определяется по значению свойства PROPERTY_ACTIVE)
 * @param boolean $was_prolongated флаг того, что свойство IS_PROLONGATED установлено в значение yes
 *
 * @return string строка проверки запроса на возможность продления, варианты результатов проверки
 * "ya" - можно при активном запросе, "yn" - можно при неактивном запросе, "n" - нельзя
*/
function requestCanBePrologated($date_difference, $is_active, $was_prolongated){
    $result = 'n';

    if($is_active
        && $date_difference >= -1
        && $date_difference < 6
    ){
        $result = 'ya';
    }elseif(
        !$is_active
        && !$was_prolongated
        && $date_difference >= -6
    ){
        $result = 'yn';
    }

    return $result;
}

/**
 * Возврат значения приведенного к большему по модулю значению с сохранением знака
 * @param $val1 значение числа 1
 * @param $val2 значение числа 2
 *
 * @return string - разница в процентах (к которому добавлен знак "+" или "-")
 */
function percentDiffSign($val1, $val2, $with_color = true){
    $result = 0;

    if($val1 != 0 && $val1 != $val2){
        if($val1 > $val2){
            $result = '-' . ceil(100 * ($val1 - $val2) / $val1) . ' %';
            if($with_color != false
                && $with_color != 'reverse'
            ){
                $result = '<span class="green_value">' . $result . '</span>';
            }elseif($with_color == 'reverse'){
                $result = '<span class="yellow_value">' . $result . '</span>';
            }
        }else{
            $result = '+' . ceil(100 * ($val2 - $val1) / $val1) . ' %';
            if($with_color != false
                && $with_color != 'reverse'
            ){
                $result = '<span class="yellow_value">' . $result . '</span>';
            }elseif($with_color == 'reverse'){
                $result = '<span class="green_value">' . $result . '</span>';
            }
        }
    }elseif($val1 == 0){
        $result = '&ndash;';
        if($with_color != false){
            $result = '<span class="gray_value">' . $result . '</span>';
        }
    }

    return $result;
}

/*
 * функция для создания ссылки на детальный список dashboard (с сохранением backurl)
 * @param $list_url адрес раздела dashboard
 * @param $get_params текущие параметры на dashboard'е
 * @param $new_params параметры, для страницы детального списка dashboard
 *
 * @return string - разница в процентах (к которому добавлен знак "+" или "-")
 * */
function dashboardUriMake($detail_url, $get_params, $new_params){
    $result = '';

    $result = $detail_url . '?' . implode('&', array_merge($get_params, $new_params)); //всегда есть хотя бы один $new_params
    if(count($get_params) > 0){
        $result .= '&backurl=' . urlencode('?' . implode('&', $get_params));
    }

    return $result;
}

/*
 * Функция для проверки корректности телефона
 * @param $arg - телефон для проверки (ожидаемые форматы "+7 (123) 456-78-90" или "71234567890")
 *
 * @return boolean - корректен ли телефон
 * */
function checkPhoneCorrect($arg){
    $result = false;

    if($arg != ''){
        if(preg_replace('/\+\d\ ?\(\d\d\d\) ?\d\d\d\-\d\d\-\d\d/s', '', $arg) == ''){
            $result = true;
        }
        if(!$result
            && preg_replace('/[0-9]{10,11}/s', '', $arg) == ''){
            $result = true;
        }
    }

    return $result;
}

/*
 * возвращает ссылку для восстановления пароля к учетной записи
 * @param $uid - ID пользователя
 * @param $ulogin - логин пользователя
 * @param $auth_page - адрес страницы восстановления пароля (например https://yandex.ru/auth/)
 *
 * @return string - ссылка для восстановления пароля
*/
function makeRestoreLinkForUser($uid, $ulogin, $auth_page){
    $result = '';

    if(is_numeric($uid)) {
        $salt = randString(8); //случайное число
        $checkword = md5(time() . 'my_uniq string'); //получаем новый checkword
        global $DB;

        $query = "UPDATE b_user SET ".
            "    CHECKWORD = '".$salt.md5($salt.$checkword)."', ".
            "    CHECKWORD_TIME = ".$DB->CurrentTimeFunction().", ".
            "    LID = '".$DB->ForSql(SITE_ID, 2)."', ".
            "   TIMESTAMP_X = TIMESTAMP_X ".
            "WHERE ID = '".$uid."' LIMIT 1;";

        $DB->Query($query, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

        $result = "{$auth_page}?change_password=yes&lang=ru&USER_CHECKWORD={$checkword}&USER_LOGIN={$ulogin}";
    }

    return $result;
}

/*
 * генерирует почту агрохелпера из телефона
 * @param string $phone_val - телефон (любой формат - "+7 (123) 456-78-90" или "123 456 78 90" )
 *
 * @return string - почта agrohelper (например "p71234567890@agrohelper.ru")
 * */
function makeEmailFromPhone($arg){
    $result = '';

    $phone_val = str_replace(array('+', '(', ')', '-', ' '), '', $arg);
    if($phone_val != ''){
        if(strlen($phone_val) == 10){
            $phone_val = '7' . $phone_val;
        }

        $result = 'p' . $phone_val . '@agrohelper.ru';
    }

    return $result;
}

/*
 * проверяет - не создан ли email из телефона (т.е. вид "p71234567890@agrohelper.ru")
 * @param string $email_val - email
 *
 * @return boolean - флаг того создан ли email из телефона
 * */
function checkEmailFromPhone($arg){
    $result = false;

    if(mb_strlen($arg) > 11)
    {
        $temp_val = mb_substr($arg, 1, 11);
        if($arg == makeEmailFromPhone($temp_val)){
            $result = true;
        }
    }

    return $result;
}

/*
 * приводит телефон к стандартному виду сайта
 * @param string $arg - телефон в произвольном формате ("+7 (123) 456-78-90" или "123 456 78 90")
 * @param string $from_email - признак преобразования из почты вида "p71234567890@agrohelper.ru"
 *
 * @return string - телефон вида "+7(123)456-78-90"
 * */
function makeCorrectPhone($arg, $from_email = false){
    $result = '';

    $phone_digits = str_replace(array('+', '(', ')', '-', ' '), '', $arg);
    if($from_email
        && strlen($arg) > 11
    ){
        //преобразование из почты вида "p71234567890@agrohelper.ru"
        $phone_digits = substr($arg, 1, 11);
    }

    if($phone_digits != ''){
        if(strlen($phone_digits) == 10){
            $phone_digits = '7' . $phone_digits;
        }else{
            $phone_digits = '7' . substr($phone_digits, 1, strlen($phone_digits) - 1);
        }

        if(strlen($phone_digits) == 11) {
            $result = '+' . substr($phone_digits, 0, 1) . ' (' . substr($phone_digits, 1, 3) . ') '
                . substr($phone_digits, 4, 3) . '-' . substr($phone_digits, 7, 2) . '-'
                . substr($phone_digits, 9, 2);
        }
    }

    return $result;
}

/*
 * Получает цифры телефона из строки
 * @param string $arg - телефон в произвольном формате ("+7 (123) 456-78-90" или "123 456 78 90")
 *
 * @return string - телефон (11 цифр, первая 7)
 * */
function getPhoneDigits($arg){
    $result = '';

    $phone_digits = str_replace(array('+', '(', ')', '-', ' '), '', trim($arg));
    if(strlen($phone_digits) > 11){
        //преобразование из почты вида "p71234567890@agrohelper.ru"
        $phone_digits = substr($arg, 1, 11);
    }

    if($phone_digits != '') {
        if (strlen($phone_digits) == 10) {
            $result = '7' . $phone_digits;
        } else {
            $result = '7' . substr($phone_digits, 1, strlen($phone_digits) - 1);
        }
    }

    return $result;
}

//ищет дубль телефона среди профилей пользователей
function profilePhoneDoubles($arg){
    $result = false;

    if(strlen($arg) > 0) {
        CModule::IncludeModule('iblock');
        $el_obj = new CIBlockElement;

        $phove_val = makeCorrectPhone($arg);
        //ищем среди поставщиков
        $res = $el_obj->GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('farmer_profile'),
                'PROPERTY_PHONE' => $phove_val,
                'ACTIVE' => 'Y'
            ),
            false,
            false,
            array('PROPERTY_USER')
        );
        if ($data = $res->Fetch()) {
            //дубликат
            return $data['PROPERTY_USER_VALUE'];
        }

        //ищем среди покупателей
        $res = $el_obj->GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('client_profile'),
                'PROPERTY_PHONE' => $phove_val,
                'ACTIVE' => 'Y'
            ),
            false,
            false,
            array('PROPERTY_USER')
        );
        if ($data = $res->Fetch()) {
            //дубликат
            return $data['PROPERTY_USER_VALUE'];
        }

        /*//ищем среди агентов поставщиков
        $res = $el_obj->GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('agent_profile'),
                'PROPERTY_PHONE' => $phove_val,
                'ACTIVE' => 'Y'
            ),
            false,
            false,
            array('PROPERTY_USER')
        );
        if ($data = $res->Fetch()) {
            //дубликат
            return $data['PROPERTY_USER_VALUE'];
        }

        //ищем среди агентов покупателей
        $res = $el_obj->GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('client_agent_profile'),
                'PROPERTY_PHONE' => $phove_val,
                'ACTIVE' => 'Y'
            ),
            false,
            false,
            array('PROPERTY_USER')
        );
        if ($data = $res->Fetch()) {
            //дубликат
            return $data['PROPERTY_USER_VALUE'];
        }*/

        //ищем среди агентов покупателей
        $res = $el_obj->GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('partner_profile'),
                'PROPERTY_PHONE' => $phove_val,
                'ACTIVE' => 'Y'
            ),
            false,
            false,
            array('PROPERTY_USER')
        );
        if ($data = $res->Fetch()) {
            //дубликат
            return $data['PROPERTY_USER_VALUE'];
        }
    }

    return $result;
}

/*
 * генерирует код для авторизации пользователя по телефону (используются только цифры)
 * @param int $user_id - id пользователя
 * @param string $password - строка пароля
 *
 * @return string - код ключа
 * */
function userGenPhoneApiKey($user_id, $password){
    $result = '';

    $arGroups = CUser::GetUserGroup($user_id);
    if(count($arGroups) > 0){
        $arGroups = array_flip($arGroups);
        $ib_id = 0;
        CModule::IncludeModule('iblock');
        $el_obj = new CIBlockElement;
        if(isset($arGroups[9])){
            $ib_id = rrsIblock::getIBlockId('client_profile');
        }elseif(isset($arGroups[10])){
            $ib_id = rrsIblock::getIBlockId('partner_profile');
        }elseif(isset($arGroups[11])){
            $ib_id = rrsIblock::getIBlockId('farmer_profile');
        }elseif(isset($arGroups[12])){
            $ib_id = rrsIblock::getIBlockId('transport_profile');
        }elseif(isset($arGroups[13])){
            $ib_id = rrsIblock::getIBlockId('agent_profile');
        }elseif(isset($arGroups[14])){
            $ib_id = rrsIblock::getIBlockId('client_agent_profile');
        }

        if($ib_id > 0) {
            $res = $el_obj->GetList(
                array('ID' => 'ASC'),
                array('IBLOCK_ID' => $ib_id, 'PROPERTY_USER' => $user_id),
                false,
                array('nTopCount' => 1),
                array('PROPERTY_PHONE')
            );
            if($data = $res->Fetch()){
                $phone_val = getPhoneDigits($data['PROPERTY_PHONE_VALUE']);
                if($phone_val != ''){
                    $result = Agrohelper::hashApiKey($phone_val, sha1($password));
                }
            }
        }
    }

    return $result;
}

/*
 * Получение списка вопросов анкетирования для фильтра (для выбранного типа пользователя)
 * @param string $user_type - код типа пользователя ('f' - поставщик, 'c' - покупатель)
 *
 * @return array - массив данных для фильтров
 * */
function getReasonsListForFilter($user_type){
    $result = array();

    CModule::IncludeModule('iblock');
    $el_obj = new CIBlockElement;

    $arFilter = array(
        'IBLOCK_ID' => rrsIblock::getIBlockId('black_list_questionary'),
        'ACTIVE' => 'Y',
        'PROPERTY_USER_TYPE' => rrsIblock::getPropListKey('black_list_questionary', 'USER_TYPE', $user_type)
    );
    $res = $el_obj->GetList(
        array('ID' => 'ASC'),
        $arFilter,
        false,
        false,
        array('ID', 'NAME')
    );
    while($data = $res->Fetch()){
        $result[$data['ID']] = $data['NAME'];
    }

    return $result;
}

/*
 * Добавление пользователя в черный список
 * @param int $user_id - id добавляющего пользователя
 * @param string $user_type - код типа добавляющего пользователя
 * @param int $opp_id - id пользователя-оппонента
 * @param int $deal_id - id пары
 *
 * @return string - код ключа
 * */
function addBlackListElement($user_id, $user_type, $opp_id, $deal_id, $anket_data = array(), $other_text = ''){
    $result = 3; //по умолчанию - неизвестная ошибка

    CModule::IncludeModule('iblock');
    $el_obj = new CIBlockElement;
    $ib_id = ($user_type == 'f' ? rrsIblock::getIBlockId('farmer_black_list') : rrsIblock::getIBlockId('client_black_list'));

    $arFields = array(
        'IBLOCK_ID' => $ib_id,
        'ACTIVE' => 'Y',
        'NAME' => 'Пользователем ' . $user_id . ' внесен в ЧС пользователь ' . $opp_id,
        'PROPERTY_VALUES' => array(
            'USER' => $user_id,
            'OPPONENT' => $opp_id,
            'DEAL' => $deal_id
        )
    );
    if(count($anket_data) > 0){
        $arFields['PROPERTY_VALUES']['ANSWERS'] = $anket_data;
    }
    if($other_text != ''){
        $arFields['PROPERTY_VALUES']['TEXT'] = array(
            'TEXT' => $other_text,
            'TYPE' => 'html'
        );
    }

    $new_id = $el_obj->Add($arFields);
    if(intval($new_id) > 0){
        //все ок
        $result = 1;

        //удаляем соответствия между оппонентами (соответственно удаляются и встречные предложения)
        $arFilter = array();
        if($user_type == 'f'){
            $arFilter['UF_FARMER_ID'] = $user_id;
            $arFilter['UF_CLIENT_ID'] = $opp_id;
        }else{
            $arFilter['UF_CLIENT_ID'] = $user_id;
            $arFilter['UF_FARMER_ID'] = $opp_id;
        }
        $leadlist = lead::getLeadList($arFilter);
        lead::deleteLeads($leadlist);

        $type = deal::getDealType($deal_id); //тип предложения для пары (платное или агентское)
        if($type == 'c'){
            //для покупателя возвращаем одно принятие
            if($_POST['user_type'] == 'c'){
                client::counterReqLimitQuantityChange('return', 1, $user_id);
            }
        }

        //получаем данные организаторов покупателя
        $client_partners = client::getLinkedPartnerList($arFilter['UF_CLIENT_ID']);
        $farmers_partners = farmer::getLinkedPartnerList($arFilter['UF_FARMER_ID']);
        $partners = array();
        $partners_email = array();
        if(sizeof($client_partners)>0){
            foreach ($client_partners as $id){
                $partners[$id] = 1;
            }
        }
        if(sizeof($farmers_partners)>0){
            foreach ($farmers_partners as $id){
                $partners[$id] = 1;
            }
        }

        $res = CUser::GetList(($by = 'id'), ($order = 'asc'), array('ID' => implode(' | ', array_keys($partners))), array('FIELDS' => array('ID', 'EMAIL')));
        while($data = $res->Fetch()){
            $partners_email[$data['ID']] = $data['EMAIL'];
        }

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
        while($data = $res->Fetch()){
            $email_arr[$data['EMAIL']] = true;
        }
        if((count($email_arr) > 0)||(count($partners_email) > 0)){
            $user_data = '';
            $black_user_data = '';
            $questions_data = '';
            $admin_href = $GLOBALS['host'] . '/bitrix/admin/iblock_element_edit.php?IBLOCK_ID=' . $ib_id . '&type=farmer&ID=' . $new_id . '&lang=ru&find_section_section=0&WF=Y';

            //получаем данные пользователей
            if($user_type == 'f'){
                $user_data = farmer::getUserData($user_id);
                $black_user_data = client::getUserData($opp_id);
            }else{
                $user_data = client::getUserData($user_id);
                $black_user_data = farmer::getUserData($opp_id);
            }

            //получаем данные анкеты
            if(isset($arFields['PROPERTY_VALUES']['ANSWERS'])){
                $res = $el_obj->GetList(
                    array('SORT' => 'ASC', 'ID' => 'ASC'),
                    array(
                        'IBLOCK_ID' => rrsIblock::getIBlockId('black_list_questionary'),
                        'ID' => $arFields['PROPERTY_VALUES']['ANSWERS']
                    ),
                    false,
                    false,
                    array('NAME', 'PROPERTY_TEXT')
                );
                while($data = $res->Fetch()){
                    if($questions_data != '')
                    {
                        $questions_data .= '<br/>';
                    }
                    if($data['NAME'] != 'Другое'
                        && $data['NAME'] != 'другое'
                    ){
                        $questions_data .= '- ' . $data['NAME'];
                    }elseif(isset($arFields['PROPERTY_VALUES']['TEXT']['TEXT'])){
                        $questions_data .= '- ' . $data['NAME'] . ':<br/>' . $arFields['PROPERTY_VALUES']['TEXT']['TEXT'];
                    }
                }
            }
            if(count($email_arr) > 0){
                foreach($email_arr as $email_val => $cur_flag) {
                    $emailFields = array(
                        'TYPE_USER_NAME' => ($_POST['user_type'] == 'f' ? 'Поставщик' : 'Покупатель'),
                        'USER_INFO' => $user_data,
                        'BLACK_USER_INFO' => $black_user_data,
                        'QUESTIONS_DATA' => $questions_data,
                        'ADMIN_HREF' => 'Перейти к записи добавления в административной части сайта можно по <a href="'.$admin_href.'">ссылке</a>',
                        'EMAIL' => $email_val
                    );
                    CEvent::Send('BLACKLISTADD', 's1', $emailFields);
                }
            }
            if(count($partners_email) > 0){
                foreach($partners_email as $id => $email_val) {
                    $emailFields = array(
                        'TYPE_USER_NAME' => ($_POST['user_type'] == 'f' ? 'Поставщик' : 'Покупатель'),
                        'USER_INFO' => $user_data,
                        'BLACK_USER_INFO' => $black_user_data,
                        'QUESTIONS_DATA' => $questions_data,
                        'ADMIN_HREF' => '',
                        'EMAIL' => $email_val
                    );
                    CEvent::Send('BLACKLISTADD', 's1', $emailFields);
                }
            }

        }
    }else{
        //неизветсная ошибка
        //echo 3;
        //echo $el_obj->LAST_ERROR;
    }

    return $result;
}

/*
 * Получение списка id связанных регионов для выбранного региона/регионов (включая этот регион)
 * @param mixed $region_id - id региона/регионов
 *
 * @return array - массив id связанных регионов
 * */
function getLinkedRegions($region_id){
    $result = array();

    CModule::IncludeModule('iblock');
    $el_obj = new CIBlockElement;

    $arFilter = array(
        'IBLOCK_ID' => rrsIblock::getIBlockId('linked_regions'),
        'ACTIVE' => 'Y',
        'PROPERTY_REGION' => $region_id
    );
    $res = $el_obj->GetList(
        array('ID' => 'ASC'),
        $arFilter,
        false,
        false,
        array('PROPERTY_LINKED')
    );
    while($data = $res->Fetch()){
        if(is_array($data['PROPERTY_LINKED_VALUE'])){
            foreach($data['PROPERTY_LINKED_VALUE'] as $cur_region){
                $result[$cur_region] = true;
            }
        }
    }
    //учитываем также и запрашиваемый регион
    if(is_array($region_id)){
        foreach($region_id as $cur_id){
            $result[$cur_id] = true;
        }
    }else{
        $result[$region_id] = true;
    }
    $result = array_keys($result);

    return $result;
}

/*
 * Получение id предложения по ID товара и запроса
 * @param int $offer_id - id товара
 * @param int $request_id - id запроса
 *
 * @return array - id предложения
 * */
function getCounterRequestIDByOfferAndRequest($offer_id, $request_id){
    $result = array();

    CModule::IncludeModule('highloadblock');

    $hlblock = \Bitrix\Highloadblock\HighloadBlockTable::getById(rrsIblock::HLgetIBlockId('COUNTEROFFERS'))->fetch();
    $entity = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlblock);
    $entityDataClass = $entity->getDataClass();
    $el_obj = new $entityDataClass;

    $arFilter = array(
        'select' => array('ID'),
        'filter' => array(
            'UF_OFFER_ID' => $offer_id,
            'UF_REQUEST_ID' => $request_id
        ),
        'limit' => 1,
        'order' => array('ID' => 'DESC')
    );
    $res = $el_obj->getList($arFilter);
    if($data = $res->fetch()){
        $result = $data['ID'];
    }

    return $result;
}

/*
 * Получение списка id складов для выбранных нужного типа пользователя
 * @param array $regionIds - массив id регионов
 * @param string $user_type - тип пользователя, для сущностей которого ищутся склады('f' - поставщик, 'c' - покупатель)
 *
 * @return array - массив id связанных регионов
 * */
function getWHListForRegions($regionIds, $user_type){
    $result = array();

    if(is_array($regionIds)
        && count($regionIds) > 0
    ){
        CModule::IncludeModule('iblock');
        $el_obj = new CIBlockElement;

        $ib_id = rrsIblock::getIBlockId($user_type == 'f' ? 'farmer_warehouse' : 'client_warehouse');
        $arFilter = array(
            'IBLOCK_ID' => $ib_id,
            'ACTIVE' => 'Y',
            'PROPERTY_REGION' => $regionIds
        );
        $res = $el_obj->GetList(
            array('ID' => 'ASC'),
            array(
                $arFilter
            ),
            false,
            false,
            array(
                'ID'
            )
        );
        while($data = $res->Fetch()){
            $result[] = $data['ID'];
        }
    }

    return $result;
}

/*
 * Отсечение тех сущностей, для которых нет соответствий по связанным регионам
 * @param array &$request_data - массив данных запросов
 * @param array &$offers_data - данные данных товаров
 * */
function checkRequestAndOffersByRegion(&$request_data, &$offers_data){

    //составляем списки привязки запросов к регионам, привязки предложений к регионам
    //а также список регионов, для получения связей
    $req_to_reg_arr = array();
    $off_to_reg_arr = array();
    $reg_links = array();
    $unset_req_id = array();
    $unset_off_id = array();
    foreach($request_data as $cur_data){
        foreach($cur_data['COST'] as $cur_cost_data){
            $req_to_reg_arr[$cur_data['ID']][] = $cur_cost_data['WH_REGION'];
            $reg_links[$cur_cost_data['WH_REGION']] = array();
        }
    }
    foreach($offers_data as $cur_data){
        $off_to_reg_arr[$cur_data['ID']][] = $cur_data['WH_REGION'];
        $reg_links[$cur_data['WH_REGION']] = array();
    }

    //получаем связанные регионы и убираем заведомо не связываемые соответствия запрос-товар
    if(count($reg_links) > 0) {
        CModule::IncludeModule('iblock');
        $res = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('linked_regions'),
                'PROPERTY_REGION' => array_keys($reg_links),
            ),
            false,
            false,
            array('PROPERTY_REGION', 'PROPERTY_LINKED')
        );
        while($data = $res->Fetch()){
            $reg_links[$data['PROPERTY_REGION_VALUE']] = $data['PROPERTY_LINKED_VALUE'];
            $reg_links[$data['PROPERTY_REGION_VALUE']][] = $data['PROPERTY_REGION_VALUE'];
        }

        //убираем заведомо не связываемые соответствия запрос-товар (поиск по запросам)
        foreach($req_to_reg_arr as $cur_request => $cur_data){
            $founded_linked = false;
            $search_regions = array();
            foreach($cur_data as $cur_reg){
                if(isset($reg_links[$cur_reg])){
                    foreach($reg_links[$cur_reg] as $cur_search_region){
                        $search_regions[$cur_search_region] = true;
                    }
                }
            }

            //если среди регионов товаров нет связанных регионов из запроса, то убираем этот запрос из списка
            if(count($search_regions) > 0){
                foreach($off_to_reg_arr as $cur_offer => $cur_of_data){
                    foreach($cur_of_data as $cur_region) {
                        if (isset($search_regions[$cur_region])) {
                            $founded_linked = true;
                            break(2);
                        }
                    }
                }
            }

            if(!$founded_linked){
                $unset_req_id[$cur_request] = true;
            }
        }
        //убираем запрос из списка
        foreach($unset_req_id as $cur_request => $cur_flag){
            unset($request_data[$cur_request]);
        }

        //убираем заведомо не связываемые соответствия запрос-товар (поиск по товарам)
        foreach($off_to_reg_arr as $cur_offer => $cur_data){
            $founded_linked = false;
            $search_regions = array();
            foreach($cur_data as $cur_reg){
                if(isset($reg_links[$cur_reg])){
                    foreach($reg_links[$cur_reg] as $cur_search_region){
                        $search_regions[$cur_search_region] = true;
                    }
                }
            }

            //если среди регионов запросов нет связанных регионов из товара, то убираем этот товар из списка
            if(count($search_regions) > 0){
                foreach($req_to_reg_arr as $cur_request => $cur_of_data){
                    foreach($cur_of_data as $cur_region) {
                        if (isset($search_regions[$cur_region])) {
                            $founded_linked = true;
                            break;
                        }
                    }
                }
            }

            if(!$founded_linked){
                $unset_off_id[$cur_offer] = true;
            }
        }
        //убираем товар из списка
        foreach($unset_off_id as $cur_offer => $cur_flag){
            unset($offers_data[$cur_offer]);
        }
    }
}

/**
 * Получение комменатриев к записям истории операций с принятиями и ограничениями для пользователя
 * @param array $elems_ids - id записей инфоблока counter_request_limits_changes
 * @param string $comment_mode - режим получения комментариев (по умолчанию - только пользовательские)
 * @param array $data_types - массив кодов сущностей для получения (принятия и/или ограничения)
 *
 * @return array - данные комментариев
 */
function getHistoryLimitsComments($elems_ids, $comment_mode = 'user', $data_types = array()) {
    $result = array();

    if(count($elems_ids) > 0
        && count($data_types) > 0
    ){
        CModule::IncludeModule('iblock');
        $el_obj = new CIBlockElement;

        $work_mode = 'user';
        if($comment_mode){
            $work_mode = $comment_mode;
        }

        $arSelect = array('ID');
        if($work_mode == 'user')
            $arSelect['U'] = 'PROPERTY_COMMENT_USER';
        elseif($work_mode == 'admin'){
            $arSelect['U'] = 'PROPERTY_COMMENT_USER';
            $arSelect['A'] = 'PROPERTY_COMMENT_ADMIN';
        }

        //т.к. исходные данные могут принадлежать нескольким сущностям, то комментарии нужно брать из нескольких инфоблоков
        $arFilter = array(
            'ID' => $elems_ids
        );
        foreach($data_types as $cur_ib_code) {
            $arFilter['IBLOCK_ID'] = rrsIblock::getIBlockId($cur_ib_code);
            if(is_numeric($arFilter['IBLOCK_ID'])){
                $res = $el_obj->GetList(
                    array('ID' => 'ASC'),
                    $arFilter,
                    false,
                    false,
                    $arSelect
                );
                while ($data = $res->Fetch()) {
                    if (isset($arSelect['U'])) {
                        $result[$data['ID']]['U_COMMENT'] = $data['PROPERTY_COMMENT_USER_VALUE']['TEXT'];
                    }
                    if (isset($arSelect['A'])) {
                        $result[$data['ID']]['A_COMMENT'] = $data['PROPERTY_COMMENT_ADMIN_VALUE']['TEXT'];
                    }
                }
            }
        }
    }

    return $result;
}

/*
 * Генерация прямой ссылки на страницу (для конкретного пользователя, с авторизацией, переавторизацией или завершением регистрации для ранее неавторизовавшихся)
 * @param int $author_id - id того, кто создает запись
 * @param int $target_user_id - id пользователя, для которого создается ссылка
 * @param string $target_user_type - тип пользователя, для которого создается ссылка для фильтрации (f|c)
 * @param int $element_id - id элемента, к которой относится ссылка для фильтрации в админке (необязательный параметр)
 * @param int $iblock_id - id инфоблока, к которому относится элемент для фильтрации в админке (необязательный параметр)
 * @param string $iblock_is_hl - тип инфоблока (highload или обычный), к которому относится элемент для фильтрации в админке ('hl', 'ib') (необязательный параметр)
 * @param string $target_url - адрес страницы на которую ведёт прямая ссылка
 * @param string $sPageUrl - адрес страницы на которую ведёт ссылка, генерируемая данной функцией (по умолчанию на корень сайта, т.к. с корня сайта автоматизирована переадресация на $target_url с прямой ссылки)
 *
 * @return string - код для ссылки
 * */
function generateStraightHref($author_id, $target_user_id, $target_user_type, $element_id = 0, $iblock_id = 0, $iblock_is_hl = '', $target_url, $sPageUrl = ''){
    $result = '';

    $uniq_code = '';

    $target_url = prepareRelativeHref($target_url);

    //получение уникального кода для генерации ссылки
    if(is_numeric($author_id)
        && is_numeric($target_user_id)
        && $target_user_type != ''
        && $target_url != ''
    ){
        $uniq_code = 'a' . $author_id . 'u' . $target_user_id . 't' . $target_user_type . 'u' . $target_url;
        if(is_numeric($element_id)
            && is_numeric($iblock_id)
            && $iblock_is_hl != ''
        ){
            $uniq_code .= 'e' . $element_id . 'i' . $iblock_id . 'h' . $iblock_is_hl;
        }

        //генерация кода md5 для ссылки
        $result_code = md5($uniq_code);
        $ib_id = rrsIblock::getIBlockId('straight_href');

        //проверка кода на дубль
        CModule::IncludeModule('iblock');
        $el_obj = new CIBlockElement;

        $res = $el_obj->GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => $ib_id,
                'PROPERTY_CHECK_CODE' => $result_code
            ),
            false,
            array('nTopCount' => 1),
            array('ID')
        );
        //если найден дубль, возвращаем результат
        if($res->SelectedRowsCount() > 0){
            $result = $result_code;
        }
        //иначе сохраняем данные в БД и возвращаем результат
        else{
            $arFields = array(
                'IBLOCK_ID' => $ib_id,
                'ACTIVE' => 'Y',
                'NAME' => 'Новая ссылка для пользователя с ID ' . $target_user_id,
                'PROPERTY_VALUES' => array(
                    'AUTHOR_USER' => $author_id,
                    'TARGET_USER' => $target_user_id,
                    'TARGET_USER_TYPE' => $target_user_type,
                    'TARGET_URL' => $target_url,
                    'ELEMENT_ID' => (is_numeric($element_id) ? $element_id : ''),
                    'IBLOCK_ID' => (is_numeric($iblock_id) ? $iblock_id : ''),
                    'IBLOCK_TYPE' => $iblock_is_hl,
                    'CHECK_CODE' => $result_code,
                )
            );

            //если добавили запись, то возвращаем код
            $new_id = $el_obj->Add($arFields);
            if(intval($new_id) > 0){
                $result = $result_code;
            }else{
                echo $ib_id;
                echo $el_obj->LAST_ERROR;
            }
        }
    }

    if($result != ''){
        $result = $GLOBALS['host'] . $sPageUrl . '?spec_href=' . $result;
    }
    return $result;
}

/*
 * Попытка получения прямой ссылки по коду
 * @param string $check_code - проверочный код
 *
 * @return array - данные для направления пользователя
 * */
function getStraightHrefDataByCode($check_code){
    $result = array();

    if($check_code != ''){
        $check_code = trim($check_code);
        CModule::IncludeModule('iblock');
        $res = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('straight_href'),
                'PROPERTY_CHECK_CODE' => $check_code
            ),
            false,
            array('nTopCount' => 1),
            array('PROPERTY_TARGET_USER', 'PROPERTY_TARGET_URL', 'PROPERTY_AUTHOR_USER', 'PROPERTY_TARGET_USER_TYPE')
        );
        if($data = $res->Fetch()){
            $result['AUTHOR_UID'] = $data['PROPERTY_AUTHOR_USER_VALUE'];
            $result['TARGET_USER_TYPE'] = $data['PROPERTY_TARGET_USER_TYPE_VALUE'];
            $result['UID'] = $data['PROPERTY_TARGET_USER_VALUE'];
            $result['URL'] = $data['PROPERTY_TARGET_URL_VALUE'];
        }
    }

    return $result;
}

/*
 * Обработка действия прямой ссылки
 * 1. Проверка авторизовывался ли пользователь на сайте
 * 2. Проверка относится ли ссылка к пользователю (если он авторизован), авторизация пользователя под нужным ID если требуется
 * 3. Переадресация на нужную страницу
 *
 * @param array $href_data - данные обрабатываемой ссылки (ключи UID - id пользователя, URL - относительная ссылка)
 *
 * @return array - относительная ссылка
 * */
function workStraightHref($href_data){
    $result = '';

    //проверка параметров
    if(is_array($href_data)
        && isset($href_data['UID'])
        && is_numeric($href_data['UID'])
        && isset($href_data['URL'])
        && trim($href_data['URL']) != ''
    ){
        $href_data['URL'] = trim($href_data['URL']);
        if($href_data['URL'] == ''){
            return $result;
        }

        //проверяем авторизовывался ли пользователь на сайте ранее
        $res = CUser::GetList(
            ($by = 'id'), ($order = 'asc'),
            array(
                'ID' => $href_data['UID'],
                'ACTIVE' => 'Y'
            ),
            array('FIELDS' => array('ID'), 'SELECT' => array('UF_FIRST_LOGIN'))
        );
        global $USER;
        if($data = $res->Fetch()){
            if(isset($data['UF_FIRST_LOGIN'])
                && intval($data['UF_FIRST_LOGIN']) > 0
            ){

                if($USER->IsAuthorized()){
                    $USER->Logout();
                }
                //данный пользователь ни разу не авторизовывался, отправляем его на завершение регистрации
                $invite_href = makeInviteHref($href_data['UID']) . '&backurl=' . urlencode($href_data['URL']) . '';
                $href_data['URL'] = $invite_href;
            }else{
                //проверяем нужно ли авторизовать/переаторизовать пользователя
                if(!$USER->IsAuthorized()){
                    $USER->Authorize($href_data['UID']);
                }elseif($USER->GetID() != $href_data['UID']){
                    $USER->Logout();
                    $USER->Authorize($href_data['UID']);
                }
            }
        }else{
            LocalRedirect('/');
            exit;
        }
        if((!empty($href_data['AUTHOR_UID']))&&(($href_data['TARGET_USER_TYPE'] == 'c')||($href_data['TARGET_USER_TYPE'] == 'client'))){
            setcookie('counter_request_referer', $href_data['AUTHOR_UID'], time() + 120, '/');
        }

        /* if(
            !isset($_GET['st'])
            || $_GET['st'] != 1
        ){
            echo '/?spec_href=' . $_GET['spec_href'] . '&st=1';exit;
            LocalRedirect('/?spec_href=' . $_GET['spec_href'] . '&st=1');
            exit;
        }*/

//        if(
//            isset($_GET['st'])
//            && $_GET['st'] != 1
//        ){
//            echo $GLOBALS['host'] . $href_data['URL'];exit;
//        }

        header('Location: ' . $GLOBALS['host'] . $href_data['URL']);
        exit;
    }

    return $result;
}

/*
 * Подготовка относительной ссылки для абсолютной ссылки
 * @param string $href_val - обрабатываемая ссылка
 *
 * @return string - относительная ссылка
 * */
function prepareRelativeHref($href_val){
    $result = '';

    if($href_val != '') {
        $href_val = trim($href_val);
        //если ссылка уже относительная, то возвращаем её
        if (mb_substr($href_val, 0, 1) == '/') {
            $result = $href_val;
        } else {
            //проверяем если ссылка является абсолютной для данного домена
            $temp_val = preg_replace('/^https?\:\/\//s', '', $href_val);
            $temp_val2 = explode('/', $temp_val, 2);

            if(isset($temp_val2[1]) && trim($temp_val2[1]) != ''){
                $result = '/' . $temp_val2[1];
            }
        }
    }

    return $result;
}

/*
 * Формирование ссылки для активации аккаунта пользователя
 *
 * @param int $user_id идентификатор пользователя
 * @return string ссылка для активации аккаунта
 * */
function makeInviteHref($user_id){
    $result = '';

    $login = '';
    //формирование приглашения
    global $DB;
    $user_obj = new CUser;
    $res = $user_obj->GetList(
        ($by = 'id'),
        ($order = 'desc'),
        array('ID' => $user_id),
        array('FIELDS' => array(
            'LOGIN'
        ))
    );
    if($data = $res->Fetch())
    {
        $login = $data['LOGIN'];
    }

    $ID = intval($user_id);
    $salt = randString(8);
    $checkword = md5(CMain::GetServerUniqID().uniqid());
    $strSql = "UPDATE b_user SET ".
        "    CHECKWORD = '".$salt.md5($salt.$checkword)."', ".
        "    CHECKWORD_TIME = ".$DB->CurrentTimeFunction().", ".
        "    LID = '".$DB->ForSql(SITE_ID, 2)."', ".
        "   TIMESTAMP_X = TIMESTAMP_X ".
        "WHERE ID = '".$ID."'".
        "    AND (EXTERNAL_AUTH_ID IS NULL OR EXTERNAL_AUTH_ID='') LIMIT 1";

    $DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

    $result = '/?change_password=yes&lang=ru&USER_CHECKWORD=' . $checkword . '&USER_LOGIN=' . $login . '&invite_by_agent=y';

    return $result;
}

/*
 * Удаление данных связанных с пользователем
 * 1. Удаление привязок к организаторам (для покупателей и поставщиков) и привязок удаляемого организатора
 * 2. Перенос данных для пары (удаляемых профилей, товаров и запросов) внутрь самой пары
 * 3. Удаляем Товары (для поставщика) и запросы (для покупателя) вместе с предложениями
 * 4. Профили
 * 5. Удаляем склады (client_warehouse и farmer_warehouse)
 * 6. Удаляем дела организатора (для поставщика и организатора)
 *
 * @param int $user_id идентификатор пользователя
 * */
function deleteUserData($user_id){
    //получение типа пользователя (по наличию профиля)
    CModule::IncludeModule('iblock');
    CModule::IncludeModule('highloadblock');
    $el_obj = new CIBlockElement;
    $ib_id = rrsIblock::getIBlockId('client_profile');
    $user_type = ''; //тип пользователя (покупатель, поставщик или организатор)
    $res = $el_obj->GetList(
        array('ID' => 'ASC'),
        array(
            'IBLOCK_ID' => $ib_id,
            'PROPERTY_USER' => $user_id
        ),
        false,
        array('nTopCount' => 1),
        array('ID')
    );
    if($res->SelectedRowsCount() == 1){
        $user_type = 'c';
    }else{
        $ib_id = rrsIblock::getIBlockId('farmer_profile');
        $res = $el_obj->GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => $ib_id,
                'PROPERTY_USER' => $user_id
            ),
            false,
            array('nTopCount' => 1),
            array('ID')
        );
        if($res->SelectedRowsCount() == 1){
            $user_type = 'f';
        }else{
            $ib_id = rrsIblock::getIBlockId('partner_profile');
            $res = $el_obj->GetList(
                array('ID' => 'ASC'),
                array(
                    'IBLOCK_ID' => $ib_id,
                    'PROPERTY_USER' => $user_id
                ),
                false,
                array('nTopCount' => 1),
                array('ID')
            );
            if($res->SelectedRowsCount() == 1){
                $user_type = 'p';
            }
        }
    }

    if($user_type == ''){
        return false;
    }

    //работаем с покупателем
    if($user_type == 'c') {
        // 1. Удаление привязок к организаторам
        $ib_id = rrsIblock::getIBlockId('client_profile');
        $res = $el_obj->GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => $ib_id,
                'PROPERTY_USER' => $user_id,
                '!PROPERTY_PARTNER_ID' => false
            ),
            false,
            array('nTopCount' => 1),
            array('ID')
        );
        if($data = $res->Fetch()){
            $arData = array('PARTNER_ID' => '', 'PARTNER_ID_TIMESTAMP' => '');
            $el_obj->SetPropertyValuesEx($data['ID'], $ib_id, $arData);
        }
        //удаляем привязку к организатору в отдельных инфоблоках
        $ib_id = rrsIblock::getIBlockId('client_partner_link');
        $res = $el_obj->GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => $ib_id,
                'PROPERTY_USER_ID' => $user_id
            ),
            false,
            false,
            array('ID')
        );
        while($data = $res->Fetch()){
            $el_obj->Delete($data['ID']);
        }
        $ib_id = rrsIblock::getIBlockId('client_agent_link');
        $res = $el_obj->GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => $ib_id,
                'PROPERTY_USER_ID' => $user_id
            ),
            false,
            false,
            array('ID')
        );
        while($data = $res->Fetch()){
            $el_obj->Delete($data['ID']);
        }

        //2. Перенос данных для пары (удаляемых профилей, товаров и запросов) внутрь самой пары
        $ib_id = rrsIblock::getIBlockId('deals_deals');
        $temp_ids = array();
        $res = $el_obj->GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => $ib_id,
                'PROPERTY_CLIENT' => $user_id
            ),
            false,
            false,
            array('ID')
        );
        while($data = $res->Fetch()){
            $temp_ids[] = $data['ID'];
        }
        if(count($temp_ids) > 0) {
            deal::savePairDataByIds($temp_ids);
        }

        // 3. Удаляем запросы вместе с предложениями
        $ib_id = rrsIblock::HLgetIBlockId('COUNTEROFFERS');
        $log_obj = new log();
        $temp_class = $log_obj->getEntityDataClass($ib_id);
        $hl_el_obj = new $temp_class;
        $res = $hl_el_obj->getList(array(
            'select' => array('ID'),
            'filter' => array('UF_CLIENT_ID' => $user_id),
            'order' => array('ID'=>'ASC')
        ));
        while($data = $res->fetch()) {
            $hl_el_obj->delete($data['ID']);
        }
        //удаляем запросы и получаем стоимости
        $temp_ids = array();
        $ib_id = rrsIblock::getIBlockId('client_request');
        $res = $el_obj->GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => $ib_id,
                'PROPERTY_CLIENT' => $user_id
            ),
            false,
            false,
            array('ID')
        );
        while($data = $res->Fetch()){
            $temp_ids[] = $data['ID'];
        }
        foreach($temp_ids as $cur_id){
            $el_obj->Delete($cur_id);
        }
        //также удаляем стоимости
        if(count($temp_ids) > 0){
            $ib_id = rrsIblock::getIBlockId('client_request_cost');
            $res = $el_obj->GetList(
                array('ID' => 'ASC'),
                array(
                    'IBLOCK_ID' => $ib_id,
                    'PROPERTY_REQUEST' => $temp_ids
                ),
                false,
                false,
                array('ID')
            );
            while($data = $res->Fetch()){
                $el_obj->Delete($data['ID']);
            }
        }

        // 4. Профиль
        $ib_id = rrsIblock::getIBlockId('client_profile');
        $res = $el_obj->GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => $ib_id,
                'PROPERTY_USER' => $user_id
            ),
            false,
            false,
            array('ID')
        );
        while($data = $res->Fetch()){
            $el_obj->Delete($data['ID']);
        }

        // 5. Удаляем склады
        $ib_id = rrsIblock::getIBlockId('client_warehouse');
        $res = $el_obj->GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => $ib_id,
                'PROPERTY_CLIENT' => $user_id
            ),
            false,
            false,
            array('ID')
        );
        while($data = $res->Fetch()){
            $el_obj->Delete($data['ID']);
        }
    }elseif($user_type == 'f') {
        // 1. Удаление привязок к организаторам
        $ib_id = rrsIblock::getIBlockId('farmer_profile');
        $res = $el_obj->GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => $ib_id,
                'PROPERTY_USER' => $user_id,
                '!PROPERTY_PARTNER_ID' => false
            ),
            false,
            array('nTopCount' => 1),
            array('ID', 'PROPERTY_PARTNER_LINK_DOC')
        );
        if($data = $res->Fetch()){
            $arData = array('PARTNER_ID' => '', 'PARTNER_ID_TIMESTAMP' => '', 'PARTNER_LINK_DOC_NUM' => '', 'PARTNER_LINK_DOC_DATE' => '');
            if(is_numeric($data['PROPERTY_PARTNER_LINK_DOC_VALUE'])){
                CFile::Delete($data['PROPERTY_PARTNER_LINK_DOC_VALUE']);
                $arData['PARTNER_LINK_DOC'] = array('del' => 'Y', 'tmp_name' => '');
            }
            $el_obj->SetPropertyValuesEx($data['ID'], $ib_id, $arData);
        }
        //удаляем привязку к организатору в отдельных инфоблоках
        $ib_id = rrsIblock::getIBlockId('farmer_agent_link');
        $res = $el_obj->GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => $ib_id,
                'PROPERTY_USER_ID' => $user_id
            ),
            false,
            false,
            array('ID')
        );
        while($data = $res->Fetch()){
            $el_obj->Delete($data['ID']);
        }

        //2. Перенос данных для пары (удаляемых профилей, товаров и запросов) внутрь самой пары
        $ib_id = rrsIblock::getIBlockId('deals_deals');
        $temp_ids = array();
        $res = $el_obj->GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => $ib_id,
                'PROPERTY_FARMER' => $user_id
            ),
            false,
            false,
            array('ID')
        );
        while($data = $res->Fetch()){
            $temp_ids[] = $data['ID'];
        }
        if(count($temp_ids) > 0) {
            deal::savePairDataByIds($temp_ids);
        }

        // 3. Удаляем запросы вместе с предложениями
        $ib_id = rrsIblock::HLgetIBlockId('COUNTEROFFERS');
        $log_obj = new log();
        $temp_class = $log_obj->getEntityDataClass($ib_id);
        $hl_el_obj = new $temp_class;
        $res = $hl_el_obj->getList(array(
            'select' => array('ID'),
            'filter' => array('UF_FARMER_ID' => $user_id),
            'order' => array('ID'=>'ASC')
        ));
        while($data = $res->fetch()) {
            $hl_el_obj->delete($data['ID']);
        }
        //удаляем товары и получаем характеристики товаров
        $temp_ids = array();
        $ib_id = rrsIblock::getIBlockId('farmer_offer');
        $res = $el_obj->GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => $ib_id,
                'PROPERTY_FARMER' => $user_id
            ),
            false,
            false,
            array('ID')
        );
        while($data = $res->Fetch()){
            $temp_ids[] = $data['ID'];
        }
        foreach($temp_ids as $cur_id){
            $el_obj->Delete($cur_id);
        }
        //также удаляем характеристики
        if(count($temp_ids) > 0){
            $ib_id = rrsIblock::getIBlockId('farmer_offer_chars');
            $res = $el_obj->GetList(
                array('ID' => 'ASC'),
                array(
                    'IBLOCK_ID' => $ib_id,
                    'PROPERTY_OFFER' => $temp_ids
                ),
                false,
                false,
                array('ID')
            );

            while($data = $res->Fetch()){
                $el_obj->Delete($data['ID']);
            }
        }

        // 4. Профиль
        $ib_id = rrsIblock::getIBlockId('farmer_profile');
        $res = $el_obj->GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => $ib_id,
                'PROPERTY_USER' => $user_id
            ),
            false,
            false,
            array('ID')
        );
        while($data = $res->Fetch()){
            $el_obj->Delete($data['ID']);
        }

        // 5. Удаляем склады
        $ib_id = rrsIblock::getIBlockId('farmer_warehouse');
        $res = $el_obj->GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => $ib_id,
                'PROPERTY_FARMER' => $user_id
            ),
            false,
            false,
            array('ID')
        );
        while($data = $res->Fetch()){
            $el_obj->Delete($data['ID']);
        }

        // 6. Удаляем дела организатора (для товаров поставщика)
        if(count($temp_ids) > 0){
            $ib_id = rrsIblock::HLgetIBlockId('AFFAIRS');
            $log_obj = new log();
            $temp_class = $log_obj->getEntityDataClass($ib_id);
            $hl_el_obj = new $temp_class;
            $res = $hl_el_obj->getList(array(
                'select' => array('ID'),
                'filter' => array('UF_USER_PARTICIPANT' => $user_id),
                'order' => array('ID'=>'ASC')
            ));
            while($data = $res->fetch()) {
                $hl_el_obj->delete($data['ID']);
            }
        }
    }elseif($user_type == 'p') {
        // 1. Удаление привязок к организаторам
        $ib_id = rrsIblock::getIBlockId('client_profile');
        $res = $el_obj->GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => $ib_id,
                'PROPERTY_PARTNER_ID' => $user_id
            ),
            false,
            array('nTopCount' => 1),
            array('ID')
        );
        if($data = $res->Fetch()){
            $arData = array('PARTNER_ID' => '', 'PARTNER_ID_TIMESTAMP' => '');
            $el_obj->SetPropertyValuesEx($data['ID'], $ib_id, $arData);
        }
        $ib_id = rrsIblock::getIBlockId('farmer_profile');
        $res = $el_obj->GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => $ib_id,
                'PROPERTY_PARTNER_ID' => $user_id
            ),
            false,
            array('nTopCount' => 1),
            array('ID', 'PROPERTY_PARTNER_LINK_DOC')
        );
        if($data = $res->Fetch()){
            $arData = array('PARTNER_ID' => '', 'PARTNER_ID_TIMESTAMP' => '', 'PARTNER_LINK_DOC_NUM' => '', 'PARTNER_LINK_DOC_DATE' => '');
            if(is_numeric($data['PROPERTY_PARTNER_LINK_DOC_VALUE'])){
                CFile::Delete($data['PROPERTY_PARTNER_LINK_DOC_VALUE']);
                $arData['PARTNER_LINK_DOC'] = array('del' => 'Y', 'tmp_name' => '');
            }
            $el_obj->SetPropertyValuesEx($data['ID'], $ib_id, $arData);
        }
        //удаляем привязку к организатору в отдельных инфоблоках
        $ib_id = rrsIblock::getIBlockId('client_partner_link');
        $res = $el_obj->GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => $ib_id,
                'PROPERTY_PARTNER_ID' => $user_id
            ),
            false,
            false,
            array('ID')
        );
        while($data = $res->Fetch()){
            $el_obj->Delete($data['ID']);
        }
        $ib_id = rrsIblock::getIBlockId('client_agent_link');
        $res = $el_obj->GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => $ib_id,
                'PROPERTY_AGENT_ID' => $user_id
            ),
            false,
            false,
            array('ID')
        );
        while($data = $res->Fetch()){
            $el_obj->Delete($data['ID']);
        }
        $ib_id = rrsIblock::getIBlockId('farmer_agent_link');
        $res = $el_obj->GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => $ib_id,
                'PROPERTY_AGENT_ID' => $user_id
            ),
            false,
            false,
            array('ID')
        );
        while($data = $res->Fetch()){
            $el_obj->Delete($data['ID']);
        }

        // 4. Профиль
        $ib_id = rrsIblock::getIBlockId('partner_profile');
        $res = $el_obj->GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => $ib_id,
                'PROPERTY_USER' => $user_id
            ),
            false,
            false,
            array('ID')
        );
        while($data = $res->Fetch()){
            $el_obj->Delete($data['ID']);
        }

        // 6. Удаляем дела организатора (для товаров поставщика)
        $ib_id = rrsIblock::HLgetIBlockId('AFFAIRS');
        $log_obj = new log();
        $temp_class = $log_obj->getEntityDataClass($ib_id);
        $hl_el_obj = new $temp_class;
        $res = $hl_el_obj->getList(array(
            'select' => array('ID'),
            'filter' => array('UF_USER_AGENT' => $user_id),
            'order' => array('ID'=>'ASC')
        ));
        while($data = $res->fetch()) {
            $hl_el_obj->delete($data['ID']);
        }
    }
}

/*
 * Добавление записи об удалении пользователя в лог
 * @param int $user_id идентификатор пользователя для удаления
 * @param int $admin_id идентификатор администратора для удаления
 * */
function deleteUserLog($user_id, $admin_id){
    $ib_id = rrsIblock::HLgetIBlockId('DELETEUSERLOG');
    $log_obj = new log();
    $temp_class = $log_obj->getEntityDataClass($ib_id);
    $hl_el_obj = new $temp_class;
    $arFields = array(
        'UF_DEL_UID' => $user_id,
        'UF_ADMIN_ID' => $admin_id,
        'UF_DEL_TIME' => ConvertTimeStamp(false, 'FULL'),
        'UF_USERDATA' => ''
    );

    //получаем email пользователя
    $res = CUser::GetList(
        ($by = 'id'), ($order = 'asc'),
        array(
            'ID' => $user_id,
            '!EMAIL' => false
        ),
        array('FIELDS' => array(
            'ID', 'EMAIL'
        ))
    );
    if($data = $res->Fetch()){
        $arFields['UF_USERDATA'] = $data['EMAIL'];
    }

    $res = $hl_el_obj->add($arFields);

//    if (!$res->isSuccess()){
//        echo 1;
//        p(get_class_methods(get_class($res))); //show class for error
//    }else{
//        echo 2;
//    }
}

/*
 * Получение списка всех регионов
 * @return array - список регионов
 * */
function getAllRegionsList(){
    $result = array();

    CModule::IncludeModule('iblock');
    $res = CIBlockElement::GetList(
        array('SORT' => 'ASC', 'NAME' => 'ASC'),
        array(
            'IBLOCK_ID' => rrsIblock::getIBlockId('regions'),
            'ACTIVE' => 'Y'
        ),
        false,
        false,
        array('ID', 'NAME')
    );
    while($data = $res->Fetch()){
        $result[$data['ID']] = $data['NAME'];
    }

    return $result;
}

/*
 * Получение ID типа доставки FCA
 * @return int - ID записи FCA в ИБ delivery4client
 * */
function getFCAItemID(){
    $result = 0;

    CModule::IncludeModule('iblock');
    $res = CIBlockElement::GetList(
        array('NAME' => 'ASC'),
        array(
            'IBLOCK_ID' => rrsIblock::getIBlockId('delivery4client'),
            'CODE' => 'N'
        ),
        false,
        array('nTopCount' => 1),
        array('ID')
    );
    while($data = $res->Fetch()){
        $result = $data['ID'];
    }

    return $result;
}

/*
 * Переполучение данных пользователей из контура
 * @param int $iDays - количество дней, за которое делается проверка
 * @param array $arUIds - массив ID пользователей
 * */
function getUsersProfilesData($iDays = 0, $arUIds = array()){

    $arFilter = array();
    $arWorkIds = array(
        'CLIENTS' => array(),
        'FARMERS' => array(),
    );
    $iClientProfileIb = rrsIblock::getIBlockId('client_profile');
    $iFarmerProfileIb = rrsIblock::getIBlockId('farmer_profile');
    $arClientUlTypePropList = array();
    $arFarmerUlTypePropList = array();

    if($iDays > 0) {
        $arFilter['DATE_REGISTER_1'] = ConvertTimeStamp(strtotime('-' . $iDays . ' DAYS'), 'FULL');
    }

    if(
        is_array($arUIds)
        && count($arUIds) > 0
    ){
        $arFilter['ID'] = implode(' | ', $arUIds);
    }

    if(count($arFilter) > 0){
        $arFilter['ACTIVE'] = 'Y';
        $arFilter['GROUPS_ID'] = 9;

        //ID проверяемых покупателей
        $obRes = CUser::GetList(
            ($by = 'id'), ($order = 'desc'),
            $arFilter,
            array(
                'FIELDS' => array('ID')
            )
        );
        while ($arData = $obRes->Fetch()){
            $arWorkIds['CLIENTS'][] = $arData['ID'];
        }

        //ID проверяемых поставщиков
        $arFilter['GROUPS_ID'] = 11;
        $obRes = CUser::GetList(
            ($by = 'id'), ($order = 'desc'),
            $arFilter,
            array(
                'FIELDS' => array('ID')
            )
        );
        while ($arData = $obRes->Fetch()){
            $arWorkIds['FARMERS'][] = $arData['ID'];
        }

        //получаем ID тех покупателей, у которых не корректно заполнено св-во "Тип ЮЛ" (или "FULL_COMPANY_NAME" и "IP_FIO")
        if(count($arWorkIds['CLIENTS']) > 0) {
            //сначала получаем значения свойства "Тип ЮЛ"
            $obRes = CIBlockPropertyEnum::GetList(
                array('ID' => 'ASC'),
                array(
                    'IBLOCK_ID' => $iClientProfileIb,
                    'CODE' => 'UL_TYPE'
                )
            );
            while ($arData = $obRes->Fetch()) {
                $arClientUlTypePropList[strtolower($arData['XML_ID'])] = $arData['ID'];
            }

            //получаем ID плохих покупателей
            if (count($arClientUlTypePropList) > 0) {
                $arFilter = array(
                    'IBLOCK_ID' => $iClientProfileIb,
                    'PROPERTY_USER' => $arWorkIds['CLIENTS'],
                    '!PROPERTY_INN' => false,
                    array(
                        'LOGIC' => 'OR',
                        array('!PROPERTY_UL_TYPE' => array_values($arClientUlTypePropList)),
                        array('PROPERTY_FULL_COMPANY_NAME' => false, 'PROPERTY_IP_FIO' => false)
                    )
                );
                $obRes = CIBlockElement::GetList(
                    array('ID' => 'ASC'),
                    $arFilter,
                    false,
                    false,
                    array('ID', 'PROPERTY_INN')
                );
                $arWorkIds['CLIENTS'] = array();
                while ($arData = $obRes->Fetch()) {
                    $arWorkIds['CLIENTS'][$arData['ID']] = $arData['PROPERTY_INN_VALUE'];
                }
            } else {
                $arWorkIds['CLIENTS'] = array();
            }
        }

        //получаем ID тех поставщиков, у которых не корректно заполнено св-во "Тип ЮЛ" (или "FULL_COMPANY_NAME" и "IP_FIO")
        if(count($arWorkIds['FARMERS']) > 0) {
            //сначала получаем значения свойства "Тип ЮЛ"
            $obRes = CIBlockPropertyEnum::GetList(
                array('ID' => 'ASC'),
                array(
                    'IBLOCK_ID' => $iFarmerProfileIb,
                    'CODE' => 'UL_TYPE'
                )
            );
            while ($arData = $obRes->Fetch()) {
                $arFarmerUlTypePropList[strtolower($arData['XML_ID'])] = $arData['ID'];
            }

            //получаем ID плохих поставщиков
            if(count($arFarmerUlTypePropList) > 0){
                $arFilter = array(
                    'IBLOCK_ID' => $iFarmerProfileIb,
                    'PROPERTY_USER' => $arWorkIds['FARMERS'],
                    '!PROPERTY_INN' => false,
                    array(
                        'LOGIC' => 'OR',
                        array('!PROPERTY_UL_TYPE' => array_values($arFarmerUlTypePropList)),
                        array('PROPERTY_FULL_COMPANY_NAME' => false, 'PROPERTY_IP_FIO' => false)
                    )
                );
                $obRes = CIBlockElement::GetList(
                    array('ID' => 'ASC'),
                    $arFilter,
                    false,
                    false,
                    array('ID', 'PROPERTY_INN')
                );
                $arWorkIds['FARMERS'] = array();
                while($arData = $obRes->Fetch()){
                    $arWorkIds['FARMERS'][$arData['ID']] = $arData['PROPERTY_INN_VALUE'];
                }
            }else{
                $arWorkIds['FARMERS'] = array();
            }

            //перезаписываем данные пользователей
            foreach($arWorkIds['CLIENTS'] as $iId => $iInn){
                $sConturData = getConturData(array('inn' => $iInn));
                $arConturData = json_decode($sConturData, true);

                $arConturData = array('0' => $arConturData[0]);
                $sConturData = json_encode($arConturData, JSON_UNESCAPED_UNICODE);

                if (mb_substr($sConturData, 0, 1) == '[') {
                    //убираем лишние '[' & ']' в начале и конце строки
                    $sConturData = mb_substr($sConturData, 1, mb_strlen($sConturData) - 2);
                }
                if (trim($sConturData) == '') {
                    echo 'Ошибка на пользователе ' . $iId . ' с ИНН ' . $iInn;
                    break;
                }
                //разбираем строку контура
                else {
                    $arFieldsData = parseConturData($sConturData);
                    if(
                        !empty($arFieldsData['UL_TYPE'])
                        && isset($arClientUlTypePropList[$arFieldsData['UL_TYPE']])
                    ){
                        $arFieldsData['UL_TYPE'] = $arClientUlTypePropList[$arFieldsData['UL_TYPE']];
                        CIBlockElement::SetPropertyValuesEx($iId, $iClientProfileIb, $arFieldsData);
                    }
                }
            }
            foreach($arWorkIds['FARMERS'] as $iId => $iInn){
                $sConturData = getConturData(array('inn' => $iInn));
                $arConturData = json_decode($sConturData, true);

                $arConturData = array('0' => $arConturData[0]);
                $sConturData = json_encode($arConturData, JSON_UNESCAPED_UNICODE);

                if (mb_substr($sConturData, 0, 1) == '[') {
                    //убираем лишние '[' & ']' в начале и конце строки
                    $sConturData = mb_substr($sConturData, 1, mb_strlen($sConturData) - 2);
                }
                if (trim($sConturData) == '') {
                    echo 'Ошибка на пользователе ' . $iId . ' с ИНН ' . $iInn;
                    break;
                }
                //разбираем строку контура
                else {
                    $arFieldsData = parseConturData($sConturData);
                    if(
                        !empty($arFieldsData['UL_TYPE'])
                        && isset($arFarmerUlTypePropList[$arFieldsData['UL_TYPE']])
                    ){
                        $arFieldsData['UL_TYPE'] = $arFarmerUlTypePropList[$arFieldsData['UL_TYPE']];
                        CIBlockElement::SetPropertyValuesEx($iId, $iFarmerProfileIb, $arFieldsData);
                    }
                }
            }
        }
    }
}

/*
 * Разбирает строку от контура на значимые данные
 * @param string $sData - количество дней, за которое делается проверка
 * @param array - массив со значимыми ключами от контура:
 * UL_TYPE, FULL_COMPANY_NAME, IP_FIO, REG_DATE, YUR_ADRESS, KPP, OGRN, OKPO, FIO_DIR
 * */
function parseConturData($sData){
    $arResult = array();

    $arTemp = json_decode($sData, true);

    if(isset($arTemp['UL'])){
        //юрлицо
        if(
            !empty($arTemp['UL']['legalName']['full'])
            && !empty($arTemp['UL']['legalName']['date'])
            && (
                !empty($arTemp['UL']['legalAddress'])
                || !empty($arTemp['UL']['history']['legalAddress'])
            )
            && !empty($arTemp['UL']['kpp'])
            && !empty($arTemp['ogrn'])
            && !empty($arTemp['UL']['okpo'])
            && (
                !empty($arTemp['UL']['heads'][0]['fio'])
                || !empty($arTemp['UL']['history']['heads'][0]['fio'])
            )
        ){
            $arResult['UL_TYPE'] = 'ul';
            $arResult['FULL_COMPANY_NAME'] = $arTemp['UL']['legalName']['full'];
            $arTempDate = ParseDateTime($arTemp['UL']['legalName']['date'], 'YYYY-MM-DD');
            $arResult['REG_DATE'] = ConvertTimeStamp(MakeTimeStamp($arTempDate['YYYY'] . '.' . $arTempDate['MM'] . '.' . $arTempDate['DD'], 'YYYY.MM.DD'), 'SHORT');
            $arResult['YUR_ADRESS'] = '';
            if(!empty($arTemp['UL']['legalAddress'])){
                $arResult['YUR_ADRESS'] = parseConturAdress($arTemp['UL']['legalAddress']);
            }else{
                $arResult['YUR_ADRESS'] = parseConturAdress($arTemp['UL']['history']['legalAddress']);
            }
            $arResult['KPP'] = $arTemp['UL']['kpp'];
            $arResult['OGRN'] = $arTemp['ogrn'];
            $arResult['OKPO'] = $arTemp['UL']['okpo'];
            $arResult['FIO_DIR'] = '';
            if(!empty($arTemp['UL']['heads'][0]['fio'])){
                $arResult['FIO_DIR'] = $arTemp['UL']['heads'][0]['fio'];
            }else{
                $arResult['FIO_DIR'] = $arTemp['UL']['history']['heads'][0]['fio'];
            }
        }
    }elseif(isset($arTemp['IP'])){
        //физ. лицо
        if(
            !empty($arTemp['IP']['fio'])
            && !empty($arTemp['IP']['registrationDate'])
            && !empty($arTemp['ogrn'])
            && !empty($arTemp['IP']['okpo'])
        ){
            $arResult['UL_TYPE'] = 'ip';
            $arResult['IP_FIO'] = $arTemp['IP']['fio'];
            $arTempDate = ParseDateTime($arTemp['IP']['registrationDate'], 'YYYY-MM-DD');
            $arResult['REG_DATE'] = ConvertTimeStamp(MakeTimeStamp($arTempDate['YYYY'] . '.' . $arTempDate['MM'] . '.' . $arTempDate['DD'], 'YYYY.MM.DD'), 'SHORT');
            $arResult['OGRN'] = $arTemp['ogrn'];
            $arResult['OKPO'] = $arTemp['IP']['okpo'];
        }
    }

    return $arResult;
}

/*
 * Собирает строку адреса от контура из данных контура
 * @param array $arAdress - массив с данными адреса из контура
 * @param string - строка с адресом
 * */
function parseConturAdress($arAdress){
    $sResult = '';

    //регион
    if(!empty($arAdress['parsedAddressRF']['regionName']['topoValue'])){
        $sResult .= $arAdress['parsedAddressRF']['regionName']['topoValue'];
    }
    if(!empty($arAdress['parsedAddressRF']['regionName']['topoShortName'])){
        $sResult .= ' ' . $arAdress['parsedAddressRF']['regionName']['topoShortName'] . '.';
    }

    //район
    if(!empty($arAdress['parsedAddressRF']['district']['topoShortName'])){
        $sResult .= ($sResult ? ', ' : '') . $arAdress['parsedAddressRF']['district']['topoShortName'] . ' ';
    }
    if(!empty($arAdress['parsedAddressRF']['district']['topoValue'])){
        $sResult .= $arAdress['parsedAddressRF']['district']['topoValue'];
    }

    //город
    if (!empty($arAdress['parsedAddressRF']['city']['topoShortName'])){
        $sResult .= ($sResult ? ', ' : '') . $arAdress['parsedAddressRF']['city']['topoShortName'] . '. ';
    }
    if (!empty($arAdress['parsedAddressRF']['city']['topoValue'])){
        $sResult .= $arAdress['parsedAddressRF']['city']['topoValue'];
    }

    //поселение
    if (!empty($arAdress['parsedAddressRF']['settlement']['topoShortName'])){
        $sResult .= ($sResult ? ', ' : '') . $arAdress['parsedAddressRF']['settlement']['topoShortName'] . '. ';
    }
    if (!empty($arAdress['parsedAddressRF']['settlement']['topoValue'])){
        $sResult .= $arAdress['parsedAddressRF']['settlement']['topoValue'];
    }

    //улица
    if (!empty($arAdress['parsedAddressRF']['street']['topoShortName'])){
        $sResult .= ($sResult ? ', ' : '') . $arAdress['parsedAddressRF']['street']['topoShortName'] . '. ';
    }
    if (!empty($arAdress['parsedAddressRF']['street']['topoValue'])){
        $sResult .= $arAdress['parsedAddressRF']['street']['topoValue'];
    }

    //дом
    if (!empty($arAdress['parsedAddressRF']['house']['topoShortName'])){
        $sResult .= ($sResult ? ', ' : '') . $arAdress['parsedAddressRF']['house']['topoShortName'] . ' ';
    }
    if (!empty($arAdress['parsedAddressRF']['house']['topoValue'])){
        $sResult .= $arAdress['parsedAddressRF']['house']['topoValue'];
    }

    //корпус, этаж
    if (!empty($arAdress['parsedAddressRF']['bulk']['topoShortName'])){
        $sResult .= ($sResult ? ', ' : '') . $arAdress['parsedAddressRF']['bulk']['topoShortName'];
    }

    //помещение, офис
    if (!empty($arAdress['parsedAddressRF']['flat']['topoValue'])){
        $sResult .= ($sResult ? ', ' : '') . $arAdress['flat']['bulk']['topoValue'];
    }
    if (!empty($arAdress['parsedAddressRF']['flat']['topoShortName'])){
        $sResult .= $arAdress['parsedAddressRF']['flat']['topoShortName'];
    }

    return $sResult;
}

/*
 * Добавление пары из встречного предложения
 * @param array $arData - массив с данными (должны быть либо ключи 'o' - ID товара и 'r' - ID запроса, либо 'c' - ID предложения в HL инфоблоке COUNTEROFFERS)
 * @param int $iPartnerId - ID организатора (того, который сгенерировал ссылку)
 * @return string - в случае ошибки текст ошибки, иначе ID(если добавление прошло успешно, либо при имеющейся паре для данного запроса и товара)
 * */
function addPairByCounterOffer($arData, $iPartnerId = 0){
    $sResult = '';

    $iRequest = (empty($arData['r']) ? 0 : $arData['r']);
    $iOffer = (empty($arData['o']) ? 0 : $arData['o']);
    $iCounterRequest = (empty($arData['c']) ? 0 : $arData['c']);

    $arCounterRequestData = array();

    //получаем данные предложения по его ID
    if(filter_var($iCounterRequest, FILTER_VALIDATE_INT)
    ){
        $arCounterRequestData = getCounterRequestDataById($iCounterRequest, array(
            'ID',
            'UF_OFFER_ID',
            'UF_REQUEST_ID',
            'UF_CLIENT_ID',
            'UF_NDS_CLIENT',
            'UF_NDS_FARMER',
            'UF_TYPE',
            'UF_DELIVERY',
            'UF_VOLUME',
            'UF_BASE_CONTR_PRICE',
            'UF_FARMER_PRICE',
            'UF_CLIENT_WH_ID',
            'UF_FARMER_WH_ID',
            'UF_VOLUME_OFFER',
            'UF_VOLUME_REMAINS',
            'UF_ADDIT_FIELDS',
            'UF_COFFER_TYPE',
            'UF_PARTNER_PRICE',
            'UF_CREATE_BY_PARTNER',
            'UF_PARTNER_Q_APRVD',
            'UF_PARTNER_Q_APRVD_D'
        ));

        //если не нашли в действующих предложениях, то возможно в парах есть запись, но без наличия ID товара и запроса найти пары мы не сможем
//        if(!isset($arCounterRequestData['ID'])){
//            $sResult = 'Объем, который Вы выбрали, продан. В ближайшее время мы вышлем Вам новое предложение.';
//        }
    }

    //если не задан counter_id, получаем данные предложения по ID товара и ID запроса
    if(!isset($arCounterRequestData['ID'])){
//        $arCounterRequestData = getCounterRequestDataByOfferAndRequest($iOffer, $iRequest, array(
//            'ID',
//            'UF_OFFER_ID',
//            'UF_REQUEST_ID',
//            'UF_CLIENT_ID',
//            'UF_NDS_CLIENT',
//            'UF_NDS_FARMER',
//            'UF_TYPE',
//            'UF_DELIVERY',
//            'UF_VOLUME',
//            'UF_BASE_CONTR_PRICE',
//            'UF_FARMER_PRICE',
//            'UF_CLIENT_WH_ID',
//            'UF_FARMER_WH_ID',
//            'UF_VOLUME_OFFER',
//            'UF_VOLUME_REMAINS',
//            'UF_ADDIT_FIELDS',
//            'UF_COFFER_TYPE',
//            'UF_PARTNER_PRICE',
//            'UF_CREATE_BY_PARTNER',
//            'UF_PARTNER_Q_APRVD',
//            'UF_PARTNER_Q_APRVD_D'
//        ));

        //если не нашли в действующих предложениях, то возможно в парах есть запись, проверяем наличие пары по ID товара и запроса
        if(
            !isset($arCounterRequestData['ID'])
            && filter_var($iOffer, FILTER_VALIDATE_INT)
            && filter_var($iRequest, FILTER_VALIDATE_INT)
        ){
            $iTemp = deal::getIdByRequestAndOffer($iOffer, $iRequest, true);
            if($iTemp > 0){
                $sResult = $iTemp;
            }else{
                //если нет в парах, то проверяем есть ли другое предложение для данного запроса (выбираем лучшее) и если есть, то предлагаем ссылку
                $sOtherCounterOfferText = client::getOtherCounterRequestHrefByRequest($iRequest);
                $iUid = client::getUserIdByRequest($iRequest);
                $sLogoHref = client::getStraightHrefMain($iUid);
                if($sOtherCounterOfferText == '') {
                    $sResult = '<div class="no_volume">Объем, который Вы выбрали, продан. В ближайшее время мы вышлем Вам новое предложение.</div>';
                }else{
                    //установка в лого на ссылки на авторизацию и
                    ob_start();?>
                    <link href="/local/templates/main_public_noauth/components/bitrix/news.list/pair_list/style.css" type="text/css" rel="stylesheet" />
                    <script type="text/javascript">
                        $(document).ready(function(){
                            $('#logo').replaceWith('<a id="logo" href="<?=$sLogoHref;?>" class="color white"></a>');
                        });
                    </script>
                    <div class="other_pair_page_block">
                        <div class="other_pair_page_block_title">Данное предложение уже принято другим покупателем.<br/>Рассмотрите предложение ниже:</div>
                        <div class="list_page_rows requests">
                            <div class="line_area active">
                                <div class="line_additional">
                                    <div class="prop_area adress_val">
                                        <div class="counter_href_area"><?=$sOtherCounterOfferText;?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="clear"></div>
                    <?
                    $sResult = ob_get_clean();
                }
            }
        }
    }

    //если нужно добавить пару, добавляем
    if(
        $sResult == ''
        && !empty($arCounterRequestData['UF_CLIENT_ID'])
    ) {
        $arRights = client::checkRights('counter_request', $arCounterRequestData['UF_CLIENT_ID']);

        $arRequest = client::getRequestById($arCounterRequestData['UF_REQUEST_ID']);
        $arOffer = farmer::getOfferById($arCounterRequestData['UF_OFFER_ID']);
        $arLead = lead::getLead($arOffer['FARMER_ID'], $arCounterRequestData['UF_REQUEST_ID'], $arCounterRequestData['UF_OFFER_ID']);

        //если есть принятия или предложение является агентским
        if ($arRights['USER_RIGHTS']['REQUEST_RIGHT'] != 'LIM'
            || $arCounterRequestData['UF_COFFER_TYPE'] == 'p'
        ) {
            $iVolume = $arCounterRequestData['UF_VOLUME'];
            if ($iVolume == 0) {
                return $sResult;
            }
            $iWarehouseId = $arCounterRequestData['UF_CLIENT_WH_ID'];

            $iRemains0 = $arRequest['REMAINS'];
            if ($iRemains0 < $iVolume) {
                $iVolume = $iRemains0;
                if ($iVolume == 0) {
                    return $sResult;
                }
                //throw new Exception('Данный объем не требуется. Проверьте правильность указанного объема');
            }


            //добавляем запись в БД (если не бот из соц сетей)
            if(
                stripos($_SERVER['HTTP_USER_AGENT'], 'whatsapp') === false
                && stripos($_SERVER['HTTP_USER_AGENT'], 'whatsapp') === false
                && stripos($_SERVER['HTTP_USER_AGENT'], 'twitter') === false
            ) {

            //обновление остатка в запросе покупателя
            $iRemains = $iRemains0 - $iVolume;
            $prop = array('REMAINS' => $iRemains);
            if ($iRemains == 0) {
                $prop['ACTIVE'] = rrsIblock::getPropListKey('client_request', 'ACTIVE', 'no');
                logRequestDeactivating($arRequest['ID']); //пишем лог о деактивации запроса
            }
            CIBlockElement::SetPropertyValuesEx($arRequest['ID'], rrsIblock::getIBlockId('client_request'), $prop);

            //стоимость на выбранном складе
            $arCost = $arRequest['COST'][$iWarehouseId];

            if ($arRequest['NEED_DELIVERY'] == 'N')
                $type = 'fca';
            else
                $type = 'cpt';

            //сброс по параметрам
            $iDumpValue = deal::getDump($arRequest['PARAMS'], $arOffer['PARAMS']);

            $arAgrohelperTariffs = model::getAgrohelperTariffs();
            $arCulturesGroup = culture::getCulturesGroup();

            //расчет цен БЦ, БЦ контракта, РЦ, ЦСМ для покупателя
            $arPrice = client::pairPriceCalculation(
                array(
                    'CLIENT_ID' => $arRequest['CLIENT_ID'],
                    'CLIENT_WH_ID' => $iWarehouseId,
                    'CENTER' => $arCost['CENTER'],
                    'ROUTE' => $arLead['UF_ROUTE'],
                    'RCSM' => $arCounterRequestData['UF_FARMER_PRICE'],
                    'CLIENT_NDS' => $arRequest['USER_NDS'],
                    'FARMER_NDS' => $arOffer['USER_NDS'],
                    'TYPE' => $arCounterRequestData['UF_DELIVERY'],
                    'DUMP' => $iDumpValue,
                    'TARIFF_LIST' => $arAgrohelperTariffs,
                    'CULTURE_GROUP_ID' => $arCulturesGroup[$arRequest['CULTURE_ID']]
                ),
                true,
                true
            );

            //заполнение свойств
            $arUpdateValues = $arUpdatePropertyValues = array();

            $arUpdateValues['IBLOCK_ID'] = rrsIblock::getIBlockId('deals_deals');
            $arUpdateValues['ACTIVE'] = 'Y';
            $arUpdateValues['NAME'] = date("d.m.Y H:i:s");
            $arUpdateValues['ACTIVE_FROM'] = date("d.m.Y H:i:s");

            $arUpdatePropertyValues['CULTURE'] = $arRequest['CULTURE_ID'];
            $arUpdatePropertyValues['CLIENT'] = $arRequest['CLIENT_ID'];
            $arUpdatePropertyValues['REQUEST'] = $arRequest['ID'];
            $arUpdatePropertyValues['VOLUME_0'] = $arRequest['REMAINS'];
            $arUpdatePropertyValues['CENTER'] = $arPrice['CENTER'];
            $arUpdatePropertyValues['CLIENT_WAREHOUSE'] = $arPrice['WH_ID'];
            $arUpdatePropertyValues['PARITY_PRICE'] = $arPrice['PARITY_PRICE'];
            $arUpdatePropertyValues['A_NDS'] = ($arRequest['USER_NDS'] == 'yes') ? 'Y' : 'N';
            $arUpdatePropertyValues['B_NDS'] = ($arOffer['USER_NDS'] == 'yes') ? 'Y' : 'N';
            $arUpdatePropertyValues['BASE_PRICE'] = round($arPrice['BASE_PRICE'], 2);
            $arUpdatePropertyValues['DUMP'] = $iDumpValue;
            $arUpdatePropertyValues['DUMP_RUB'] = $arPrice['DUMP_RUB'];
            if (isset($arPrice['CSM_FOR_CLIENT']['SBROS_RUB']) && $arPrice['CSM_FOR_CLIENT']['SBROS_RUB'] != 0) {
                $arUpdatePropertyValues['DUMP_RUB_CLIENT_NDS'] = $arPrice['CSM_FOR_CLIENT']['SBROS_RUB'];
            }
            $arUpdatePropertyValues['TARIF'] = $arPrice['TARIF'];
            $arUpdatePropertyValues['ACC_PRICE'] = round($arPrice['ACC_PRICE'], 2);
            $arUpdatePropertyValues['ROUTE'] = $arPrice['ROUTE'];
            $arUpdatePropertyValues['BASE_CONTR_PRICE'] = round($arCounterRequestData['UF_BASE_CONTR_PRICE'], 2);
            if (isset($arPrice['CSM_FOR_CLIENT']['UF_CSM_PRICE'])) {
                $arUpdatePropertyValues['ACC_PRICE_CSM_CLIENT_NDS'] = $arPrice['CSM_FOR_CLIENT']['UF_CSM_PRICE'];
            }
            $arUpdatePropertyValues['ACC_PRICE_CSM'] = round($arCounterRequestData['UF_FARMER_PRICE'], 2);
            $arUpdatePropertyValues['FARMER'] = $arOffer['FARMER_ID'];
            $arUpdatePropertyValues['OFFER'] = $arOffer['ID'];
            $arUpdatePropertyValues['VOLUME'] = $iVolume;
            $arUpdatePropertyValues['FARMER_WAREHOUSE'] = $arOffer['WH_ID'];
            $arUpdatePropertyValues['DELIVERY'] = rrsIblock::getPropListKey('deals_deals', 'DELIVERY', $_REQUEST['delivery']);

            $arUpdatePropertyValues['PARTNER'] = 0;
            if(!empty($iPartnerId)){
                $arUpdatePropertyValues['PARTNER'] = $iPartnerId;
            }else{
                $arUpdatePropertyValues['PARTNER'] = reset(farmer::getLinkedPartnerList($arOffer['FARMER_ID'], true));
            }

            $arUpdatePropertyValues['STAGE'] = rrsIblock::getPropListKey('deals_deals', 'STAGE', 'new');
            $arUpdatePropertyValues['DATE_STAGE'] = date('d.m.Y H:i:s');
            $arUpdatePropertyValues['PAIR_STATUS'] = rrsIblock::getPropListKey('deals_deals', 'PAIR_STATUS', 'new');
            $arUpdatePropertyValues['DELIVERY_TYPE'] = $arCounterRequestData['UF_DELIVERY'];

            //устанавливаем того, кто отправил ссылку, если есть данные
            if(filter_var($iPartnerId, FILTER_VALIDATE_INT)) {
                $arUpdatePropertyValues['DEAL_REFERER'] = $iPartnerId;
            }

            //Доп опции
            $arAdditData = array(
                'IS_ADD_CERT' => 'N',
                'IS_BILL_OF_HEALTH' => 'N',
                'IS_VET_CERT' => 'N',
                'IS_QUALITY_CERT' => 'N',
                'IS_TRANSFER' => 'N',
                'IS_SECURE_DEAL' => 'N',
                'IS_AGENT_SUPPORT' => 'N',
            );
            if ($arCounterRequestData['UF_COFFER_TYPE'] == 'p') {
                //если является агентским предложением, то вносим эти данные в пару
                $arUpdatePropertyValues['PARTNER_PRICE'] = $arCounterRequestData['UF_PARTNER_PRICE'];
                $arUpdatePropertyValues['COFFER_BY_PARTNER'] = $arCounterRequestData['UF_CREATE_BY_PARTNER'];
                //проставляем данные, внесенные организатором (обязательные)
                $temp_addit_data = array();
                if (trim($arCounterRequestData['UF_ADDIT_FIELDS']) != '') {
                    $temp_addit_data = json_decode($arCounterRequestData['UF_ADDIT_FIELDS'], true);
                    if (
                        isset($temp_addit_data['IS_ADD_CERT']) && $temp_addit_data['IS_ADD_CERT'] == 1
                        || !empty($arOffer['Q_APPROVED'])
                    ) {
                        $arAdditData['IS_ADD_CERT'] = 'Y';
                    }
                    if (isset($temp_addit_data['IS_BILL_OF_HEALTH']) && $temp_addit_data['IS_BILL_OF_HEALTH'] == 1) {
                        $arAdditData['IS_BILL_OF_HEALTH'] = 'Y';
                    }
                    if (isset($temp_addit_data['IS_VET_CERT']) && $temp_addit_data['IS_VET_CERT'] == 1) {
                        $arAdditData['IS_VET_CERT'] = 'Y';
                    }
                    if (isset($temp_addit_data['IS_QUALITY_CERT']) && $temp_addit_data['IS_QUALITY_CERT'] == 1) {
                        $arAdditData['IS_QUALITY_CERT'] = 'Y';
                    }
                    if (isset($temp_addit_data['IS_TRANSFER']) && $temp_addit_data['IS_TRANSFER'] == 1) {
                        $arAdditData['IS_TRANSFER'] = 'Y';
                    }
                    if (isset($temp_addit_data['IS_SECURE_DEAL']) && $temp_addit_data['IS_SECURE_DEAL'] == 1) {
                        $arAdditData['IS_SECURE_DEAL'] = 'Y';
                    }
                    if (isset($temp_addit_data['IS_AGENT_SUPPORT']) && $temp_addit_data['IS_AGENT_SUPPORT'] == 1) {
                        $arAdditData['IS_AGENT_SUPPORT'] = 'Y';
                    }
                }
            }
            $arUpdatePropertyValues['COFFER_TYPE'] = $arCounterRequestData['UF_COFFER_TYPE'];
            $arUpdatePropertyValues['IS_ADD_CERT'] = $arAdditData['IS_ADD_CERT'] ?: 'N';
            $arUpdatePropertyValues['IS_BILL_OF_HEALTH'] = $arAdditData['IS_BILL_OF_HEALTH'] ?: 'N';
            $arUpdatePropertyValues['IS_VET_CERT'] = $arAdditData['IS_VET_CERT'] ?: 'N';
            $arUpdatePropertyValues['IS_QUALITY_CERT'] = $arAdditData['IS_QUALITY_CERT'] ?: 'N';
            $arUpdatePropertyValues['IS_TRANSFER'] = $arAdditData['IS_TRANSFER'] ?: 'N';
            $arUpdatePropertyValues['IS_SECURE_DEAL'] = $arAdditData['IS_SECURE_DEAL'] ?: 'N';
            $arUpdatePropertyValues['IS_AGENT_SUPPORT'] = $arAdditData['IS_AGENT_SUPPORT'] ?: 'N';

            $arUpdateValues['PROPERTY_VALUES'] = $arUpdatePropertyValues;

                $GLOBALS['ADDED_PAIR'] = true;

                $obElement = new CIBlockElement;
                $iID = $obElement->Add($arUpdateValues);
                if (!$iID) {
                    $sResult = 'Ошибка при добавлении пары: ' . $obElement->LAST_ERROR;
                } else {
                    //В ответе возвращаем ID новой пары
                    $sResult = $iID;
                    $GLOBALS['CLIENT_ID'] = $arRequest['CLIENT_ID'];

                    //убираем того, кто отправил ссылку, если есть данные
                    if (isset($arUpdatePropertyValues['DEAL_REFERER'])) {
                        setcookie('counter_request_referer', "", time() - 10, '/');
                    }

                    //создание записи платежа
                    $arUpdateValues = array(
                        'IBLOCK_ID' => rrsIblock::getIBlockId('deals_payments'),
                        'ACTIVE' => 'Y',
                        'NAME' => 'Платеж по сделке ' . $iID,
                        'PROPERTY_VALUES' => array(
                            'CLIENT' => $arRequest['CLIENT_ID'],
                            'FARMER' => $arOffer['FARMER_ID'],
                            'DEAL' => $iID,
                            'CULTURE' => $arRequest['CULTURE_ID'],
                            'VOLUME' => $iVolume,
                            'DEAL_PRICE' => ''
                        )
                    );
                    $obElement->Add($arUpdateValues);

                    //удаление пар запрос-товар
                    if ($iRemains == 0) {
                        $filter = array(
                            'UF_REQUEST_ID' => $arRequest['ID']
                        );
                        $arLeads = lead::getLeadList($filter);
                        if (is_array($arLeads) && sizeof($arLeads) > 0) {
                            lead::deleteLeads($arLeads);
                        }
                    }

                    //удаление записи встречного Предложения
                    log::_deleteEntity(log::getIdByName('COUNTEROFFERS'), $arCounterRequestData['ID']);

                    //уменьшение объема по связанным встречным предложениям (у которых те же товары ап, но другие запросы)
                    $iRemainsVolume = $arCounterRequestData['UF_VOLUME_REMAINS'] - $iVolume;
                    if ($iRemainsVolume < 0) $iRemainsVolume = 0;
                    client::counterRequestsRecountVolume($arCounterRequestData['UF_OFFER_ID'], $iRemainsVolume, $arRequest['CULTURE_ID'], $arRequest['CULTURE_NAME']);

                    //списывание одного принятия при его использовании (если не агенсткое предложение)
                    if ($arCounterRequestData['UF_COFFER_TYPE'] != 'p') {
                        client::counterReqLimitQuantityChange('use', -1, $arRequest['CLIENT_ID']);
                    }

                    //вычисление новой паритетной цены для рег. центра по культуре
                    $arPrices = model::parityPriceCalculation($arCost['CENTER'], $arRequest['CULTURE_ID']);
                    if (is_array($arPrices) && sizeof($arPrices) > 0) {
                        //сохранение новой паритетной цены
                        $iNewId = model::saveParityPrice($arCost['CENTER'], $arRequest['CULTURE_ID'], $arPrices);
                        if ($iNewId > 0) {
                            //логирование изменения паритетной цены
                            log::addParityPriceLog($arCost['CENTER'], $arRequest['CULTURE_ID'], 'новая сделка', 'deal', $arPrices);
                        }
                    }

                    //Отправка уведомлений
                    $arSendedUsers = array();
                    //Доп опции
                    $sList = 'Услуги Агрохелпера:<ul>';
                    $arFields = array(
                        'IS_ADD_CERT' => 'Отбор проб и лабораторная диагностика',
                        'IS_BILL_OF_HEALTH' => 'Карантинное свидетельство',
                        'IS_VET_CERT' => 'Ветеринарные свидетельства',
                        'IS_QUALITY_CERT' => 'Сертификаты качества',
                        'IS_TRANSFER' => 'Транспортировка',
                        'IS_SECURE_DEAL' => 'Безопасная сделка',
                        'IS_AGENT_SUPPORT' => 'Сопровождение сделки'
                    );
                    $arSList = array();
                    foreach ($arFields as $sName => $sTranslate) {
                        if (isset($_REQUEST[$sName]) && $_REQUEST[$sName] === 'Y') {
                            $arSList[] = "<li>" . $sTranslate . "</li>";
                        }
                    }
                    if (count($arSList) > 0) {
                        $sList .= implode('', $arSList);

                        $sList .= '</ul>';
                    } else {

                        $sList = '';
                    }
                    $sList_admin = $sList;
                    if (!empty($iID)) {
                        $sList .= '<br><a target="_blank" href="' . $GLOBALS['host'] . '/partner/pair/?id=' . $iID . '">Пара #' . $iID . '</a>';
                        $sList_admin .= '<br><a target="_blank" href="' . $GLOBALS['host'] . '/bitrix/admin/iblock_element_edit.php?IBLOCK_ID=38&type=deals&ID=' . $iID . '">Пара #' . $iID . '</a>';
                    }

                    //фио покупателя
                    $sUserName = getUserName($arRequest['CLIENT_ID']);
                    $sUrl = $GLOBALS['host'] . '/profile/?uid=' . $arRequest['CLIENT_ID'];

                    if (!empty($iID)) {
                        //отправляем уведомления организаторам

                        //получаем дополнительные данные - покупателя, поставщика из пары и их организаторов
                        $sClientCompanyName = reset(client::getUserCompanyNames(array($arRequest['CLIENT_ID'])));
                        $sClientPhone = reset(client::getPhoneList(array($arRequest['CLIENT_ID'])));
                        if($sClientPhone != ''){
                            $sClientPhone = 'Телефон: ' . $sClientPhone . '<br/>';
                        }
                        $sClientName = getUserName($arRequest['CLIENT_ID']);
                        $sClientData = "<br/>ФИО: {$sClientName}<br/>{$sClientPhone}<br/>";
                        $sFarmerCompanyName = reset(farmer::getUserCompanyNames($arOffer['FARMER_ID']));
                        $sFarmerPhone = reset(farmer::getPhoneList(array($arOffer['FARMER_ID'])));
                        if($sFarmerPhone != ''){
                            $sFarmerPhone = 'Телефон: ' . $sFarmerPhone . '<br/>';
                        }
                        $sFarmerName = getUserName($arOffer['FARMER_ID']);
                        $sFarmerData = "<br/>ФИО: {$sFarmerName}<br/>{$sFarmerPhone}<br/>";
                        $sClientPartnerData = '';
                        $iClientPartner = 0;
                        if(!empty($arUpdatePropertyValues['DEAL_REFERER'])){
                            $iClientPartner = $arUpdatePropertyValues['DEAL_REFERER'];
                        }else{
                            $arrTemp = client::getLinkedPartnerList($arRequest['CLIENT_ID'], true);
                            if(!empty($arrTemp[0])){
                                $iClientPartner = $arrTemp[0];
                            }
                        }
                        if($iClientPartner > 0) {
                            $arrClientPartnerData = partner::getPartnerInfo($iClientPartner);
                            if (!empty($arrClientPartnerData['NAME'])) {
                                $sPartnerPhone = $arrClientPartnerData['PHONE'];
                                if ($sPartnerPhone != '') {
                                    $sPartnerPhone = 'Телефон: ' . $sPartnerPhone . '<br/>';
                                }
                                $sPartnerName = $arrClientPartnerData['NAME'];
                                $sClientPartnerData = "<br/>Данные организатора покупателя:<br/>ФИО: {$sPartnerName}<br/>{$sPartnerPhone}";
                            }
                        }
                        $sFarmerPartnerData = '';
                        $iFarmerPartner = 0;
                        if(!empty($arUpdatePropertyValues['COFFER_BY_PARTNER'])){
                            $iFarmerPartner = $arUpdatePropertyValues['COFFER_BY_PARTNER'];
                        }else{
                            $arrTemp = farmer::getLinkedPartnerList($arOffer['FARMER_ID'], true);
                            if(!empty($arrTemp[0])){
                                $iFarmerPartner = $arrTemp[0];
                            }
                        }
                        if($iFarmerPartner > 0) {
                            $arrFarmerPartnerData = partner::getPartnerInfo($iFarmerPartner);
                            if (!empty($arrFarmerPartnerData['NAME'])) {
                                $sPartnerPhone = $arrFarmerPartnerData['PHONE'];
                                if ($sPartnerPhone != '') {
                                    $sPartnerPhone = 'Телефон: ' . $sPartnerPhone . '<br/>';
                                }
                                $sPartnerName = $arrFarmerPartnerData['NAME'];
                                $sFarmerPartnerData = "<br/>Данные организатора поставщика:<br/>ФИО: {$sPartnerName}<br/>{$sPartnerPhone}";
                            }
                        }
                        $sFarmerNds = '';
                        $sClientNds = '';
//                        $sFarmerNds = ($arOffer['USER_NDS'] == 'yes' ? ' (с НДС)' : ' (без НДС)');
//                        $sClientNds = ($arRequest['USER_NDS'] == 'yes' ? ' (с НДС)' : ' (без НДС)');

                        /**
                         * отправляем админам
                         */
                        $sUserInfo = "Предложение товара \"{$arOffer['CULTURE_NAME']}\" в объёме {$iVolume} т по цене «с места»{$sFarmerNds} {$arUpdatePropertyValues['ACC_PRICE_CSM']} руб/т, на складе \"{$arOffer['WH_NAME']}\" от \"{$sFarmerCompanyName}\" принято покупателем.<br/>";
                        $arEventFields = array(
                            'FIO' => $sUserName,
                            'ID' => $arRequest['ID'],
                            'URL' => $GLOBALS['host'] . '/profile/?uid=' . $arRequest['CLIENT_ID'],
                            'LIST' => $sList_admin,
                            'USERINFO' => $sUserInfo . $sFarmerData,
                        );
                        $arFilter = array('GROUPS_ID' => 1, 'ACTIVE' => 'Y');
                        $res = CUser::GetList(($by = "id"), ($order = "asc"), $arFilter, array('FIELDS' => array('ID', 'EMAIL', 'ACTIVE', 'NAME', 'LAST_NAME', 'LOGIN')));
                        while ($arUser = $res->Fetch()) {
                            if (!isset($arSendedUsers[$arUser['ID']])) {
                                $arSendedUsers[$arUser['ID']] = true;
                                $arEventFields['EMAIL'] = $arUser['EMAIL'];
                                CEvent::Send('ADD_NEW_PAIR', 's1', $arEventFields);
                            }
                        }

                        $arrClientPartnersList = client::getLinkedPartnerList(!empty($arRequest['CLIENT_ID']) ? $arRequest['CLIENT_ID'] : 0);
                        $arrFarmerPartnersList = farmer::getLinkedPartnerList(!empty($arOffer['FARMER_ID']) ? $arOffer['FARMER_ID'] : 0);
                        $arrEmails = array();
                        if(
                            count($arrClientPartnersList) > 0
                            || count($arrFarmerPartnersList) > 0
                        ) {
                            $obRes = CUser::GetList(
                                ($by = 'ID'), ($order = 'ASC'),
                                array(
                                    'ID' => implode(' | ', array_unique(array_merge($arrClientPartnersList, $arrFarmerPartnersList)))
                                ),
                                array('FIELDS' => array('ID', 'EMAIL'))
                            );
                            while ($arrData = $obRes->Fetch()) {
                                if (
                                    $arrData['EMAIL']
                                    && !checkEmailFromPhone($arrData['EMAIL'])
                                ) {
                                    $arrEmails[$arrData['ID']] = $arrData['EMAIL'];
                                }
                            }
                        }

                        $arEventFields = array(
                            'FIO' => $sUserName,
                            'ID' => $arRequest['ID'],
                            'URL' => $sUrl,
                            'LIST' => $sList,
                            'USERINFO' => '',
                        );
                        $culture = culture::getName($arRequest['CULTURE_ID']);
                        $farmer_wh_name = trim(farmer::getWHNameById($arOffer['WH_ID']));
                        //партнерам поставщика
                        $sUserInfo = "Предложение товара \"{$arOffer['CULTURE_NAME']}\" в объёме {$iVolume} т по цене «с места»{$sFarmerNds} {$arUpdatePropertyValues['ACC_PRICE_CSM']} руб/т, на складе \"{$arOffer['WH_NAME']}\" от \"{$sFarmerCompanyName}\" принято покупателем.<br/>";
                        $message = 'Принято встречное предложение по товару "' . $culture['NAME'] . '" на складе "' . $farmer_wh_name . '"';
                        if (!empty($arOffer['FARMER_ID'])) {
                            if (is_array($arrFarmerPartnersList)) {
                                foreach ($arrFarmerPartnersList as $partner_id) {
                                    if (!isset($arSendedUsers[$partner_id])) {
                                        $arSendedUsers[$partner_id] = true;
                                        $partner_link = $GLOBALS['host'] . '/partner/pair/?id=' . $iID;
                                        notice::addNotice($partner_id, 'd', $message, $partner_link, '#' . $iID,
                                            array('SEND_USER' => $arOffer['FARMER_ID'], 'PAIR_ID' => $iID));

                                        if(isset($arrEmails[$partner_id])){

                                            $arEventFields['USERINFO'] = $sUserInfo;
                                            //добавляем данные организатора покупателя
                                            if($partner_id != $iClientPartner){
                                                $arEventFields['USERINFO'] .= $sClientPartnerData;
                                            }
                                            $arEventFields['USERINFO'] .= $sFarmerData;

                                            $arEventFields['EMAIL'] = $arrEmails[$partner_id];
                                            CEvent::Send('ADD_NEW_PAIR', 's1', $arEventFields);
                                        }
                                    }
                                }
                            }
                        }
                        //партнерам покупателя
                        $arEventFields['USERINFO'] = '';
                        $client_wh_name = '';
                        if(
                            is_array($arRequest['COST'])
                            && count($arRequest['COST']) == 1
                        ){
                            $arrTemp = reset($arRequest['COST']);
                            if(!empty($arrTemp['WH_NAME'])){
                                $client_wh_name = trim($arrTemp['WH_NAME']);
                            }
                        }
                        $sUserInfo = "Предложение товара \"{$arOffer['CULTURE_NAME']}\" в объёме {$iVolume} т по цене «с места»{$sFarmerNds} {$arUpdatePropertyValues['ACC_PRICE_CSM']} руб/т, на складе \"{$arOffer['WH_NAME']}\" от \"{$sFarmerCompanyName}\" принято покупателем \"{$sClientCompanyName}\" на складе \"{$client_wh_name}\" с ценой СРТ{$sClientNds} {$arUpdatePropertyValues['BASE_PRICE']} руб/т.<br/>";
                        if (!empty($arRequest['CLIENT_ID'])) {
                            if (is_array($arrClientPartnersList)) {
                                foreach ($arrClientPartnersList as $partner_id) {
                                    if (!isset($arSendedUsers[$partner_id])) {
                                        $arSendedUsers[$partner_id] = true;
                                        $partner_link = $GLOBALS['host'] . '/partner/pair/?id=' . $iID;
                                        notice::addNotice($partner_id, 'd', $message, $partner_link, '#' . $iID,
                                            array('SEND_USER' => $arRequest['CLIENT_ID'], 'PAIR_ID' => $iID));

                                        if(isset($arrEmails[$partner_id])){

                                            $arEventFields['USERINFO'] = $sUserInfo;
                                            //добавляем данные организатора поставщика
                                            if($partner_id != $iFarmerPartner){
                                                $arEventFields['USERINFO'] .= $sFarmerPartnerData;
                                            }
                                            $arEventFields['USERINFO'] .= $sClientData;

                                            $arEventFields['EMAIL'] = $arrEmails[$partner_id];
                                            CEvent::Send('ADD_NEW_PAIR', 's1', $arEventFields);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }else{
                echo '<div class="no_volume">Возникла ошибка в браузере. Воспользуйтесь другим браузером пожалуйста.</div>';
            }
        }
    }

    return $sResult;
}

/*
 * Получить данные встречного предложения по ID
 * @param int $iCounterRequestId - ID встречного предложения
 * @param array $arSelect - массив для выборки полей из инфоблока
 * @return array - массив с данными встречного предложения
 * */
function getCounterRequestDataById($iCounterRequestId, $arSelect = array()){
    $arResult = array();
    
    if(filter_var($iCounterRequestId, FILTER_VALIDATE_INT)) {
        if (
            !is_array($arSelect)
            || count($arSelect) == 0
        ) {
            $arSelect = '*';
        }

        $iHlCounterOffer = rrsIblock::HLgetIBlockId('COUNTEROFFERS');
        $logObj = new log;
        $sEntityDataClass = $logObj->getEntityDataClass($iHlCounterOffer);
        $obEl = new $sEntityDataClass;
        $obRes = $obEl->getList(array(
            'select' => $arSelect,
            'filter' => array('ID' => $iCounterRequestId),
            'order' => array('ID' => 'DESC'),
        ));
        if ($arData = $obRes->fetch()) {
            $arResult = $arData;
            if(isset($arResult['UF_PARTNER_Q_APRVD_D'])){
                $arResult['UF_PARTNER_Q_APRVD_D'] = $arResult['UF_PARTNER_Q_APRVD_D']->toString();
            }
        }
    }

    return $arResult;
}

/*
 * Получить ID запроса и товара из логов встречных предложений по ID
 * @param int $iCounterRequestId - ID встречного предложения
 * @return array - массив с данными встречного предложения
 * */
function getCounterRequestLogById($iCounterRequestId){
    $arResult = array();

    if(filter_var($iCounterRequestId, FILTER_VALIDATE_INT)) {

        $iHlCounterOffer = rrsIblock::HLgetIBlockId('COUNTEROFFERS');
        $logObj = new log;
        $sEntityDataClass = $logObj->getEntityDataClass($iHlCounterOffer);
        $obEl = new $sEntityDataClass;
        $obRes = $obEl->getList(array(
            'select' => array('UF_REQUEST_ID', 'UF_OFFER_ID'),
            'filter' => array('ID' => $iCounterRequestId),
            'order' => array('ID' => 'DESC'),
        ));
        if ($arData = $obRes->fetch()) {
            $arResult = $arData;
            if(isset($arResult['UF_PARTNER_Q_APRVD_D'])){
                $arResult['UF_PARTNER_Q_APRVD_D'] = $arResult['UF_PARTNER_Q_APRVD_D']->toString();
            }
        }
    }

    return $arResult;
}

/*
 * Получить данные встречного предложения по ID товара и ID запроса
 * @param int $iOfferId - ID товара
 * @param int $iRequestId - ID запроса
 * @param array $arSelect - массив для выборки полей из инфоблока
 * @return array - массив с данными встречного предложения
 * */
function getCounterRequestDataByOfferAndRequest($iOfferId, $iRequestId, $arSelect = array()){
    $arResult = array();

    if(
        filter_var($iOfferId, FILTER_VALIDATE_INT)
        && filter_var($iRequestId, FILTER_VALIDATE_INT)
    ) {
        if (
            !is_array($arSelect)
            || count($arSelect) == 0
        ) {
            $arSelect = '*';
        }

        $iHlCounterOffer = rrsIblock::HLgetIBlockId('COUNTEROFFERS');
        $logObj = new log;
        $sEntityDataClass = $logObj->getEntityDataClass($iHlCounterOffer);
        $obEl = new $sEntityDataClass;
        $obRes = $obEl->getList(array(
            'select' => $arSelect,
            'filter' => array('UF_OFFER_ID' => $iOfferId, 'UF_REQUEST_ID' => $iRequestId),
            'order' => array('ID' => 'DESC'),
        ));
        if ($arData = $obRes->fetch()) {
            $arResult = $arData;
            if(isset($arResult['UF_PARTNER_Q_APRVD_D'])){
                $arResult['UF_PARTNER_Q_APRVD_D'] = $arResult['UF_PARTNER_Q_APRVD_D']->toString();
            }
        }
    }

    return $arResult;
}

/**
 * Получение имени пользователя (без названия компании)
 * @param int | array $iUserId - ID покупателя
 * @return string данные покупателя
 */
function getUserName($mUserId){
    $result = '';
    $bIsArray = false;
    if(is_array($mUserId)){
        $result = array();
        $bIsArray = true;
    }

    $sTemp = '';
    $res = CUser::GetList(
        ($by = 'id'), ($order = 'asc'),
        array('ID' => (is_array($mUserId) ? implode(' | ', $mUserId) : $mUserId)),
        array('FIELDS' => array('ID', 'EMAIL', 'NAME', 'LAST_NAME'))
    );
    while($data = $res->Fetch()) {
        $sTemp = trim($data['NAME'] . ' ' . $data['LAST_NAME']);
        if ($sTemp == '') {
            if (!checkEmailFromPhone($data['EMAIL'])) {
                $sTemp = $data['EMAIL'];
            } else {
                $sTemp = $data['ID'];
            }
        }

        if($sTemp){
            if($bIsArray){
                $result[$data['ID']] = $sTemp;
            }else{
                $result = $sTemp;
            }
        }
    }

    return $result;
}

/**
 * Получение телефона пользователя
 * @param int $iIblock - ID инфоблока профиля
 * @param int $iUserId - ID пользователя
 * @return string телефон
 */
function getUserPhone($iIblock, $iUserId){
    $result = '';

    if($result == ''){
        $obRes = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => $iIblock,
                'PROPERTY_USER' => $iUserId,
            ),
            false,
            array('nTopcCount' => 1),
            array('PROPERTY_PHONE')
        );
        if($arData = $obRes->Fetch()){
            if(!empty($arData['PROPERTY_PHONE_VALUE'])){
                $result = $arData['PROPERTY_PHONE_VALUE'];
            }
        }
    }

    return $result;
}

/**
 * Получение email пользователей (с возможной проверкой на генерацию email из теелфона)
 * @param array $arrIds - ID пользователей
 * @param boolean $checkPhoneEmail - нужна ли проверка на то, что почта сгененирована автоматически из телефона (по умолчанию - да)
 * @return array массив данных пользователей
 */
function getUsersEmail($arrIds, $checkPhoneEmail = true){
    $arrResult = array();

    if(
        is_array($arrIds)
        && count($arrIds) > 0
    ){
        $obRes = CUser::GetList(
            ($by = 'ID'), ($order = 'ASC'),
            array( 'ID' => implode(' | ', $arrIds) ),
            array('FIELDS' => array('ID', 'EMAIL'))
        );
        while($arrData = $obRes->Fetch()){
            if(
                !empty($arrData['EMAIL'])
            ){
                //проверяем почту на генерацию из телефона, если нужно
                if(
                    !$checkPhoneEmail
                    || !checkEmailFromPhone($arrData['EMAIL'])
                ) {
                    $arrResult[$arrData['ID']] = $arrData['EMAIL'];
                }
            }
        }
    }

    return $arrResult;
}

/**
 * логирование деактивации запроса
 * @param int $iRequest - ID запроса, если есть
 * @return string телефон
 */
function logRequestDeactivating($iRequest = 0){
    global $USER;
    CModule::IncludeModule('highloadblock');
    $iIb = rrsIblock::HLgetIBlockId('REQUESTDEACTLOG');
    $objLog = new Log();
    $sEntityDataClass = $objLog->getEntityDataClass($iIb);
    $obEl = new $sEntityDataClass;
    $arrFields = array(
        'UF_UID' => $USER->GetID(),
        'UF_URI' => $_SERVER['REQUEST_URI'],
        'UF_POST' => json_encode($_POST),
        'UF_GET' => json_encode($_GET),
        'UF_REQUEST' => json_encode($_REQUEST),
        'UF_DATE' => ConvertTimeStamp(false, 'FULL'),
        'UF_USER_AGENT' => $_SERVER['HTTP_USER_AGENT'],
        'UF_REQUEST_ID' => $iRequest,
    );
    $obEl->Add($arrFields);
}

/**
 * проверка наличия запросов с 0 остатком, неактивных, но с наличием предложений
 * @return string телефон
 */
function checkBadRequests(){
    global $USER;
    CModule::IncludeModule('highloadblock');
    $iIb = rrsIblock::HLgetIBlockId('COUNTEROFFERS');
    $objLog = new Log();
    $sEntityDataClass = $objLog->getEntityDataClass($iIb);
    $obEl = new $sEntityDataClass;
    $obRes = $obEl->getList(array(
        'order' => array('UF_REQUEST_ID' => 'ASC'),
        'select' => array('UF_REQUEST_ID'),
        'group' => array('UF_REQUEST_ID'),
    ));
    $arrCheckRequests = array();
    while ($arrData = $obRes->fetch()) {
        $arrCheckRequests[$arrData['UF_REQUEST_ID']] = true;
    }

    if(count($arrCheckRequests) > 0){
        CModule::IncludeModule('iblock');
        $obRes = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('client_request'),
                'ID' => array_keys($arrCheckRequests),
                'PROPERTY_ACTIVE' => rrsIblock::getPropListKey('client_request', 'ACTIVE', 'no'),
            ),
            false,
            array('nTopCount' => 1),
            array('ID')
        );
        if($arrData = $obRes->Fetch()){
            mail('somefor@yandex.ru', 'agrohelper found bad requests', 'Request ID example: ' . $arrData['ID']);
        }
    }
}

function getCharForExcel($iNum){
    $sResult = '';

    $num = $iNum % 26;

    $sResult = chr(65 + $num);
    if($iNum > 25){
        if($iNum <= 51) {
            $sResult = 'A' . $sResult;
        }else{
            $sResult = 'B' . $sResult;
        }
    }

    return $sResult;
}