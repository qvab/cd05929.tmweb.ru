<?php
/**
 * Created by PhpStorm.
 * User: bragev
 * Date: 12.09.2018
 * Time: 9:13
 */

/**
 * Преобразование секунд в секунды/минуты/часы/дни/года
 *
 * @param int $seconds - секунды для преобразования
 *
 * @return array $times:
 *		$times[0] - секунды
 *		$times[1] - минуты
 *		$times[2] - часы
 *		$times[3] - дни
 *		$times[4] - года
 *
 */
function seconds2times($seconds)
{
    $times = array();

    // считать нули в значениях
    $count_zero = false;

    // количество секунд в году не учитывает високосный год
    // поэтому функция считает что в году 365 дней
    // секунд в минуте|часе|сутках|году
    $periods = array(60, 3600, 86400, 31536000);

    for ($i = 3; $i >= 0; $i--)
    {
        $period = floor($seconds/$periods[$i]);
        if (($period > 0) || ($period == 0 && $count_zero))
        {
            $times[$i+1] = $period;
            $seconds -= $period * $periods[$i];
            $count_zero = true;
        }
    }

    $times[0] = $seconds;
    return $times;
}

/**
 * Форматирование секунд в строку формата (X лет X д. X час. X мин. X сек.)
 * @param $seconds
 * @param bool $sec - указывать секунды
 * @return string
 */
function secondTimesFormat($seconds,$sec = true){
    $result = '';
    // значения времени
    $times_values = array('сек.','мин.','час.','д.','лет');
        $times = seconds2times($seconds);
        $times_start = 0;
        if($sec === false){
            $times_start = 1;
        }
        for ($i = count($times)-1; $i >= $times_start; $i--) {
            $result.=$times[$i] . ' ' . $times_values[$i] . ' ';
        }
    return $result;
}

/**
 * Форматирование секунд в часы (или минуты, если осталось меньше одного часа), с возвращением текста в родительном падеже
 * @param $seconds - секунды
 * @param bool $with_text - возвращать ли также и текст в соответствующем падеже
 * @return string
 */
function secondToHoursRod($seconds){
    $result = floor($seconds / 3600);

    if($result == 0){
        $result = ceil($seconds / 60);

        //добавляем наименование
        $temp_val1 = ($result < 10 ? $result : substr($result, -2, 2));
        $temp_val2 = substr($result, -1, 1);
        if($temp_val2 > 0
            && ($temp_val1 < 5 || $temp_val1 > 20 && $temp_val2 < 5)
        ){
            if($temp_val2 == 1){
                $result .= ' минуты';
            }else{
                $result .= ' минут';
            }
        }else{
            $result .= ' минут';
        }
    }else{
        //добавляем наименование
        $temp_val1 = ($result < 10 ? $result : substr($result, -2, 2));
        $temp_val2 = substr($result, -1, 1);
        if($temp_val2 > 0
            && ($temp_val1 < 5 || $temp_val1 > 20 && $temp_val2 < 5)
        ){
            if($temp_val2 == 1){
                $result .= ' часа';
            }else{
                $result .= ' часов';
            }
        }else{
            $result .= ' часов';
        }
    }

    return $result;
}

/**
 * Форматирование секунд в часы (или минуты, если осталось меньше одного часа)
 * @param $seconds - секунды
 * @param bool $with_text - возвращать ли также и текст в соответствующем падеже
 * @return string
 */
function secondToHours($seconds, $with_text = true){
    $result = floor($seconds / 3600);

    if($result == 0){
        $result = ceil($seconds / 60);
        if($with_text){
            //добавляем наименование
            $temp_val1 = ($result < 10 ? $result : substr($result, -2, 2));
            $temp_val2 = substr($result, -1, 1);
            if($temp_val2 > 0
                && ($temp_val1 < 5 || $temp_val1 > 20 && $temp_val2 < 5)
            ){
                if($temp_val2 == 1){
                    $result .= ' минута';
                }else{
                    $result .= ' минуты';
                }
            }else{
                $result .= ' минут';
            }
        }
    }elseif($with_text){
        //добавляем наименование
        $temp_val1 = ($result < 10 ? $result : substr($result, -2, 2));
        $temp_val2 = substr($result, -1, 1);
        if($temp_val2 > 0
            && ($temp_val1 < 5 || $temp_val1 > 20 && $temp_val2 < 5)
        ){
            if($temp_val2 == 1){
                $result .= ' час';
            }else{
                $result .= ' часа';
            }
        }else{
            $result .= ' часов';
        }
    }

    return $result;
}


