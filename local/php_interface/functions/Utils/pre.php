<?php
/**
 * Created by 1C-Rarus.
 *
 * Function for object(-s) dumping in &lt;pre&gt; tags.
 *
 * @author: Alexei Schubert <shuber@rarus.ru>, Smagin Artem <artems@rarus.ru>, Vitaly Melnik <vitali@rarus.ru>
 */

/**
 * Dumps information about a variable in &lt;pre&gt; tags.
 * If the first arg of method is 'toAll' or 'all' don't check user to access
 *
 * @param mixed $expression The variable you want to export.
 * @param mixed $expressionN [optional]
 */
if(!function_exists('pre')) {
    function pre($expression, $expressionN = null) {

        global $USER;
        $arArgs = func_get_args();

        if(count($arArgs) > 1 && ($arArgs[0] === 'toAll' || $arArgs[0] === 'all'))
            array_shift($arArgs);
        elseif(!$USER->IsAdmin())
            return;

        $trace = debug_backtrace();

        echo '<br><b>Debug:</b> '. $trace[2]['file'] .' ('. $trace[2]['line'] .')';
        foreach($arArgs AS $arg) {
            echo "<pre>\n";
            var_dump($arg);
            echo '</pre>';
        }
        echo '<b>End:</b> @-------------------------@<br>';
    }
}