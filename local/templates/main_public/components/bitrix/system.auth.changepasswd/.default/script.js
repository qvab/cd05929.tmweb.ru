var sms_change_id = 0;
$(document).ready(function () {
    $('.content-form.changepswd-form.public_form form[name="bform"]').on('submit', function(e){

        var formObj = $(this);
        var checked_phone = false;
        var phone_input = formObj.find('input[name="USER_PHONE"]');

        var err_val = '';
        var err_obj;

        if(phone_input.length == 1){
            //проверка не подтвержден ли телефон
            if(!formObj.find('.row.phone_sms').hasClass('check_success')){
                //делаем запрос на отправку смс
                var phoneArea = formObj.find('.field.phone_sms');

                if(!phoneArea.hasClass('check_success')
                    && !phoneArea.hasClass('error')
                ){
                    sendPhoneChangeEvent();

                    if(sms_change_id == 0) {
                        sms_change_id = setInterval(function () {
                            if (typeof formObj.attr('data-sendresult') !== 'undefined') {
                                formObj.removeAttr('data-sendresult');
                                sms_change_id = 0;
                                clearInterval(sms_change_id);
                            }
                        }, 500);
                    }

                    e.preventDefault();
                }else{
                    grecaptcha.execute();
                    e.preventDefault();
                }
            }else{
                checked_phone = true;
            }

            if(checked_phone){
                //проверка email
                var checkObj = formObj.find('input[name="USER_EMAIL"]');
                if(checkObj.length == 1 && checkObj.val() != ''){
                    if(!checkEmailRfc(checkObj.val())){
                        err_val = 'Указан некорректный email';
                        err_obj = checkObj.parents('.row').find('.err_row');
                        if(err_obj.length > 0){
                            err_obj.text(err_val);
                        }else{
                            checkObj.parents('.row').find('.row_sub_head').after('<div class="err_row">' + err_val + '</div>');
                        }
                    }
                }
            }

            if(formObj.find('input[name="USER_PASSWORD"]').val() != formObj.find('input[name="USER_PASSWORD"]').val()){
                err_val = ''
            }

            //проверка установленных галочек на согласии обработки персональных данных и согласии с регламентом системы
            checkObj = $(this).find('input[type="checkbox"][name="AUTH_REG_CONFIM"]');
            if (err_val == '' && checked_phone && checkObj.length == 1 && !checkObj.prop('checked')) {
                err_val = 'Не отмечена галочка согласия хранения персональных данных.';
                alert(err_val);
            }
            //check reglament confirm flag
            checkObj = $(this).find('input[type="checkbox"][name="AUTH_REGLAMENT_CONFIM"]');
            if (err_val == '' && checked_phone && checkObj.length == 1 && !checkObj.prop('checked')) {
                err_val = 'Не отмечена галочка согласия с регламентом системы.';
                alert(err_val);
            }

            if(err_val != '' || !checked_phone){
                //проверка телефона
                e.preventDefault();
                return false;
            }
        }
    });

    $('.content-form.changepswd-form.public_form form[name="bform"] input[name="USER_EMAIL"]').on('keyup', function(){
        var err_obj = $(this).parents('.row').find('.err_row');
        if(err_obj.length > 0){
            err_obj.remove();
        }
    });
});

function sendPhoneChangeEvent(){
    var formObj = $('.content-form.changepswd-form.public_form form[name="bform"]');
    var objVal = formObj.find('.row.phone_sms');
    var phoneVal = '';

    var error_val = '';

    if(!objVal.hasClass('check_success')){
        //если телефон еще не был подтвержден, то запускаем алгоритм проверки
        var curLastTime = getCookie('lastSendMessage');

        if(getCookie('lastSendMessage') != null){
            $('#popup_phone_num .error_text').remove();
            if($.now() - curLastTime > 60000){
                var phoneObj = objVal.find('input[type="text"]');

                //убираем вывод ошибки в блоке
                formObj.find('.row.phone_sms.error').removeClass('error big_err');
                formObj.find('.row.phone_sms .row_err').each(function(){ $(this).text(''); });

                var val = phoneObj.val().replace(/[\+\-\(\) ]/g, '');
                if(val.length == 10){
                    val = '7' + val;
                }else if(val.length == 11){
                    val = '7' + val.substr(1, 10);
                }
                var n = val.match( /\d/g );

                if(val == ''){
                    error_val = 'Пожалуйста заполните это обязательное поле';
                }else if(n.length != 11 && n.length != 0) {//проверяем телефон
                    error_val = 'Телефон должен быть в формате +7 (XXX) XXX-XX-XX';
                }

                if(error_val != ''){
                    if(phoneObj.siblings('.row_err').length == 0){
                        phoneObj.parent().append('<div class="row_err">' + error_val + '</div>');
                    }else{
                        phoneObj.siblings('.row_err').text(error_val);
                    }
                    phoneObj.parents('.row.phone_sms').addClass('error');
                }else{
                    //отправляем данные
                    $.post('/ajax/sendRegisterSms.php', {
                        phone: phoneObj.val(),
                        restore: 'y'
                        //token: token
                    }).done(function (mes) {
                        if(mes == 1){//успешное выполнение
                            //показываем поле для ввода кода
                            if(!objVal.hasClass('popup_repeat_send')){
                                phoneVal = '+' + val.substr(0, 1) + '(' + val.substr(1, 3) + ') ' + val.substr(4, 3) +
                                    '-' +  val.substr(7, 2) + '-' +  val.substr(9, 2);
                                showPhoneChangePopup(phoneVal);
                            }else{
                                formObj.find('form').attr('data-sendresult', 'y');
                            }
                        }else{
                            //выводим ошибку
                            if(phoneObj.siblings('.row_err').length == 0){
                                phoneObj.parent().append('<div class="row_err">' + mes.replace('\'', '') + '</div>');
                            }else{
                                phoneObj.siblings('.row_err').text(mes);
                            }
                            phoneObj.parents('.row.phone_sms').addClass('error');
                            if(!objVal.hasClass('popup_repeat_send')) {
                                formObj.find('form').attr('data-sendresult', 'n');
                            }else{
                                $('#popup_phone_num input[name="sms_code"]').before('<div class="error_text">' + mes.replace('\'', '') + '</div>');
                            }
                        }
                    });

                    setCookie('lastSendMessage', $.now());
                }
            }else{
                //если еще не прошла минута с момента предыдущей отправки
                if(!objVal.hasClass('popup_repeat_send')) {
                    //если отправляется не из всплывающего окна, то показываем всплывающее окно
                    var phoneObj = objVal.find('input[type="text"]');

                    var val = phoneObj.val().replace(/[\+\-\(\) ]/g, '');
                    if(val.length == 10){
                        val = '7' + val;
                    }else if(val.length == 11){
                        val = '7' + val.substr(1, 10);
                    }
                    var n = val.match( /\d/g );

                    if(val != '' && n.length == 11) {
                        phoneVal = '+' + val.substr(0, 1) + '(' + val.substr(1, 3) + ') ' + val.substr(4, 3) +
                            '-' +  val.substr(7, 2) + '-' +  val.substr(9, 2);

                        showPhoneChangePopup(phoneVal);
                    }
                }
                $('#popup_phone_num input[name="sms_code"]').before('<div class="error_text">Повторный запрос можно будет направить не раньше чем через минуту с момента прошлой попытки</div>');
            }
        }
    }
}

//всплывающая форма проверки телефона по смс (для входа по ссылке от агента/организатора)
function showPhoneChangePopup(phone_val){
    var phoneForm = $('#popup_phone_num');
    window.timeout = null;
    if(phoneForm.length === 0){
        $('#page_body').append('<div id="popup_phone_num">' +
            '<div class="popup_logo"></div><div class="popup_close" onclick="closeSmsPopup(this);"></div>' +
            '<div class="popup_header">Подтверждение телефона</div>' +
            '<div class="text">На ваш номер отправлено сообщение с кодом подтверждения телефона</div>' +
            '<div class="phone_value">' + phone_val + '</div>' +
            '<input type="text" oninput="if(timeout !== null){clearTimeout(timeout);}  timeout = setTimeout(submitPhoneChangeEvent, 1000, this);" name="sms_code" placeholder="Введите код" />' +
            '<div class="send_button popup_repeat_send" data-again="Отправить повторно" data-first="Отправить смс" onclick="sendPhoneChangeEvent();">Отправить смс повторно</div>' +
            //'<div class="submit_sms_button" onclick="submitPhoneChangeEvent(this);">Подтвердить</div>' +
            '<div class="clear"></div>' +
            '</div>');

        phoneForm = $('#popup_phone_num');
    }

    $('#back_shad').addClass('active');
    phoneForm.addClass('active');
}

//проверка кода из смс
function submitPhoneChangeEvent(objVal){

    var formObj = $('.content-form.changepswd-form.public_form form[name="bform"]');
    var phoneObj = formObj.find('input[name="USER_PHONE"]');
    var codeObj = $('#popup_phone_num input[name="sms_code"]');

    //убираем вывод ошибки в блоке
    $('#popup_phone_num .error_text').remove();

    if(phoneObj.val() == ''){//проверяем телефон
        if(phoneObj.siblings('.row_err').length == 0){
            phoneObj.parent().append('<div class="row_err">Пожалуйста заполните это обязательное поле</div>');
        }else{
            phoneObj.siblings('.row_err').text('Пожалуйста заполните это обязательное поле');
        }
        phoneObj.parents('.row.phone_sms').addClass('error');
    }else if(codeObj.val() != parseInt(codeObj.val())
        || codeObj.val().toString().length != 4
    ){//проверяем код подтверждения
        $('#popup_phone_num input[name="sms_code"]').before('<div class="error_text">Укажите корректный код подтверждения (4 цифры)</div>');
    }else{
        //отправляем данные
        $.post('/ajax/checkRegisterSmsCode.php', {
            phone: phoneObj.val(),
            code: codeObj.val(),
            check_for_doubles: 'y'
            //token: token
        }).done(function (mes) {
            if(mes.substr(0, 1) == 1){//успешное выполнение
                formObj.find('.row.phone_sms').addClass('check_success');
                $('#back_shad').removeClass('active');
                $('#popup_phone_num').remove();
                var errObj = formObj.find('.phone_sms.row.error .row_err');
                if(errObj.length == 1){
                    errObj.parents('.row.error').removeClass('error');
                    errObj.remove();
                }

                //проверка - если заполнены остальные необходимые поля, то отправляем форму
                var all_entered = true;
                var check_obj = formObj.find('input[name="USER_PASSWORD"]');
                if(check_obj.length == 0
                    || check_obj.val().length == 0
                ){
                    all_entered = false;
                }

                if(all_entered) {
                    check_obj = formObj.find('input[name="USER_CONFIRM_PASSWORD"]');
                    if (check_obj.length == 0
                        || check_obj.val().length == 0
                    ) {
                        all_entered = false;
                    }
                }

                if(all_entered) {
                    check_obj = formObj.find('input[name="AUTH_REG_CONFIM"]');
                    if (check_obj.length == 0
                        || check_obj.prop('checked') === false
                    ) {
                        all_entered = false;
                    }
                }

                if(all_entered) {
                    check_obj = formObj.find('input[name="AUTH_REGLAMENT_CONFIM"]');
                    if (check_obj.length == 0
                        || check_obj.prop('checked') === false
                    ) {
                        all_entered = false;
                    }
                }

                if(all_entered){
                    formObj.submit();
                }
            }else{//текст ошибки
                $('#popup_phone_num input[name="sms_code"]').before('<div class="error_text">' + mes.replace('\'', '') + '</div>');
            }
        });
    }
}