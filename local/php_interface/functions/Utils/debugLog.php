<?php

/**
 * Вывод массива/строки в файл
 * @param $mData   - Данные
 * @param $sPath   - Путь к файлу (по умолчанию выше корня сайта)
 *
 * @author: Vitaly Melnik <vitali@rarus.ru>
 */

if(!function_exists('debugLog')) {

    function debugLog($mData, $sPath = false) {

        if(!$sPath) {
            $sPattern       = '/'.array_pop(explode('/', $_SERVER['DOCUMENT_ROOT'])).'$/';
            $sReplacement   = 'defaultDebug.log';
            $sPath          = preg_replace($sPattern, $sReplacement, $_SERVER['DOCUMENT_ROOT']);
        }

        $arTrace = debug_backtrace();
        $sFile   = str_replace($_SERVER['DOCUMENT_ROOT'], '', $arTrace[0]['file']);
        $iLine   = $arTrace[0]['line'];

        $sStr  = PHP_EOL .'=============== START '. date('Y m d H:i:s') ." ==================\n({$sFile} :: {$iLine})\n--". PHP_EOL;
        $sStr .= var_export($mData, true);
        $sStr .= PHP_EOL .'=============== END ========================================'. PHP_EOL;

        error_log($sStr, 3, $sPath);
    }
}