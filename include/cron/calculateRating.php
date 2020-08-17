<?//Пересчет рейтига покупателей, берутся оценки покупаталей за последний год
//1 день

/*if(empty($_SERVER['SHELL']))
	die();*/

set_time_limit(12000);
//боевой
$_SERVER["DOCUMENT_ROOT"] = "/home/bitrix/www";
//песочница
//$_SERVER["DOCUMENT_ROOT"] = "/www/unorganized_projects/agrohelper/sandboxes/aledem/www";

define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS",true);
flush();
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
ob_end_flush();

CModule::IncludeModule('iblock');
$el = new CIBlockElement;

$ibMarks = rrsIblock::getIBlockId('client_marks');
$ibRating = rrsIblock::getIBlockId('client_rating');

$dlt = array(
    0 => 10,
    5 => 9,
    10 => 8,
    15 => 7,
    25 => 6,
    35 => 5,
    45 => 4,
    55 => 3,
    65 => 2,
    75 => 1,
    90 => 0
);

//получаем все оценки покупателей на последний год
$arMarks = array();
$res = CIBlockElement::GetList(
    array('ID' => 'DESC'),
    array(
        'IBLOCK_ID' => $ibMarks,
        'ACTIVE' => 'Y',
        '>DATE_CREATE' => date('d.m.Y H:i:s', strtotime('-1 year')),
        '!PROPERTY_CHECK_PARTNER' => false,
        '!PROPERTY_CHECK_FARMER' => false
    ),
    false,
    false,
    array(
        'ID',
        'PROPERTY_CLIENT',
        'PROPERTY_REC_PARTNER',
        'PROPERTY_LAB_PARTNER',
        'PROPERTY_PAY_PARTNER',
        'PROPERTY_REC_FARMER',
        'PROPERTY_LAB_FARMER',
        'PROPERTY_PAY_FARMER'
    )
);
while ($ob = $res->Fetch()) {
    if (intval($ob['PROPERTY_CLIENT_VALUE']) > 0) {
        $arMarks[$ob['PROPERTY_CLIENT_VALUE']][] = array(
            'REC_PARTNER' => $ob['PROPERTY_REC_PARTNER_VALUE'],
            'LAB_PARTNER' => $ob['PROPERTY_LAB_PARTNER_VALUE'],
            'PAY_PARTNER' => $ob['PROPERTY_PAY_PARTNER_VALUE'],
            'REC_FARMER' => $ob['PROPERTY_REC_FARMER_VALUE'],
            'LAB_FARMER' => $ob['PROPERTY_LAB_FARMER_VALUE'],
            'PAY_FARMER' => $ob['PROPERTY_PAY_FARMER_VALUE'],
        );
    }
}

//получаем текущий рейтинг покупателей
$res = CIBlockElement::GetList(
    array('ID' => 'DESC'),
    array(
        'IBLOCK_ID' => $ibRating,
        'ACTIVE' => 'Y'
    ),
    false,
    false,
    array(
        'ID',
        'PROPERTY_USER'
    )
);
while ($ob = $res->Fetch()) {
    $arRatingId[$ob['PROPERTY_USER_VALUE']] = $ob['ID'];
}

//есть оценки за последний год?
if (sizeof($arMarks) > 0) {
    foreach ($arMarks as $userId => $clientMarks) {
        $prop = array();
        $pri = $pli = $ppi = $fri = $fli = $fpi = 0;
        $n = sizeof($clientMarks);

        $prop['USER'] = $userId;
        if (is_array($clientMarks) && $n > 0) {
            foreach ($clientMarks as $mark) {
                $pri += $mark['REC_PARTNER'];
                $pli += $mark['LAB_PARTNER'];
                $ppi += $mark['PAY_PARTNER'];
                $fri += $mark['REC_FARMER'];
                $fli += $mark['LAB_FARMER'];
                $fpi += $mark['PAY_FARMER'];
            }
            $_pri = 100 * $pri / $n;
            $_pli = 100 * $pli / $n;
            $_ppi = 100 * $ppi / $n;
            foreach ($dlt as $k => $val) {
                if ($_pri >= $k) {
                    $pri = $val;
                }
                if ($_pli >= $k) {
                    $pli = $val;
                }
                if ($_ppi >= $k) {
                    $ppi = $val;
                }
            }

            $_fri = $fri / $n;
            $_fli = $fli / $n;
            $_fpi = $fpi / $n;

            $prop['REC'] = round(0.5 * ($pri + $_fri), 2);
            $prop['LAB'] = round(0.5 * ($pli + $_fli), 2);
            $prop['PAY'] = round(0.5 * ($ppi + $_fpi), 2);
            $prop['RATING'] = round(($prop['REC'] + $prop['LAB'] + $prop['PAY']) / 3., 2);
        }
        else {
            $prop['REC'] = $prop['LAB'] = $prop['PAY'] = $prop['RATING'] = 0;
        }

        if (intval($arRatingId[$userId]) > 0) {
            //рейтинг у пользователя уже был, обновляем
            CIBlockElement::SetPropertyValuesEx($arRatingId[$userId], $ibRating, $prop);
            unset($arRatingId[$userId]);
        }
        else {
            //рейтинга нет, добавляем
            $arLoadProductArray = Array(
                'IBLOCK_ID'      => $ibRating,
                'PROPERTY_VALUES'=> $prop,
                'NAME'           => 'рейтинг',
                'ACTIVE'         => 'Y',
            );

            $ID = $el->Add($arLoadProductArray);
        }
    }
}

//у оставшихся пользователей рейтинг нужно обнулить
if (sizeof($arRatingId) > 0) {
    $prop = array('REC' => 0, 'LAB' => 0, 'PAY' => 0, 'RATING' => 0);
    foreach ($arRatingId as $val) {
        CIBlockElement::SetPropertyValuesEx($val, $ibRating, $prop);
    }
}

global $CACHE_MANAGER;
$CACHE_MANAGER->ClearByTag("iblock_id_".$ibRating);
?>