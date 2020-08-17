<?

if (isset($_POST['inn_val']) && is_numeric($_POST['inn_val']) && (strlen($_POST['inn_val']) == 10 || strlen($_POST['inn_val']) == 12)) {
    //get company data by INN
    require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');
    CModule::IncludeModule('iblock');

    $sInnVal = htmlspecialcharsbx($_POST['inn_val']);

    //ищем дубли ИНН
    $bFoundDoubleInn = partner::isDoubleProfileInn($sInnVal);

    if($bFoundDoubleInn){
        echo 2;
    }else {
        $res_str = getConturData(array('inn' => $sInnVal));

        $res = json_decode($res_str, true);
        $res = array('0' => $res[0]);
        $res_str = json_encode($res, JSON_UNESCAPED_UNICODE);

        if (mb_substr($res_str, 0, 1) == '[') {
            //remove '[' & ']' (first & last symbols)
            $res_str = mb_substr($res_str, 1, mb_strlen($res_str) - 2);
        }
        if (trim($res_str) == '') {
            echo 1;
        }
        else {
            echo $res_str;
            if (mb_strlen($res_str) != mb_strlen(str_replace('expired', '', $res_str))) {
                //key expired -> send message to admin
                $def_email_from = COption::GetOptionString("main", "email_from");
                if (check_email($def_email_from)) {
                    mail($def_email_from, 'Ошибка ключа api контура на сайте Агрохелпера.', "Ошибка ключа api контура на сайте Агрохелпера\n\nТекст ошибки: " . $res_str);
                }
            }

            $_SESSION['success_inn_' . $sInnVal] = 'y';
        }

        exit;
    }
}