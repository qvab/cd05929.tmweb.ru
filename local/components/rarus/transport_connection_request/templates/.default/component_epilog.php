<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if(isset($_COOKIE['tk_connection_request_success'])
    && $_COOKIE['tk_connection_request_success'] == 'y'
){
    unset($_COOKIE['tk_connection_request_success']);
    setcookie('tk_connection_request_success', '', -1);
    $arResult['success'] = 'ok';
    ?>
        <script type="text/javascript">
            $('form.tk_request_form').prepend('<div class="success">Заявка успешно отправлена</div>');
        </script>
    <?
}