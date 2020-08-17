var sms_forg_id = 0;
$(document).ready(function () {
    $('form.restore_form input[type="text"]').on('keyup', function(){
        //проверяем режим ввода данных (телефон/почта)

        var wObj = $(this);
        var wForm = wObj.parents('form');
        var wMode = checkInputPhoneEmail(wObj.val());
        var phoneInput = wForm.find('input[type="hidden"][name="by_phone"]');

        if(wMode == 'phone'){
            if(phoneInput.length == 0){
                wForm.prepend('<input type="hidden" name="by_phone" value="y" />');
            }
        }else{
            if(phoneInput.length == 1){
                phoneInput.remove();
            }
        }
    });

    $('form.restore_form').on('submit', function(e){

        var mode = ($(this).find('input[type="hidden"][name="by_phone"]').length == 0 ? 'email' : 'phone');
        var checked_phone = false;

        if(mode == 'phone'){
            //проверка не подтвержден ли телефон
            if(!$('form.restore_form .field.row.phone_sms').hasClass('check_success')){
                //делаем запрос на отправку смс
                var formObj = $(this);
                var phoneArea = formObj.find('.row.phone_sms');

                if(!phoneArea.hasClass('check_success')
                    && !phoneArea.hasClass('error')
                ){
                    sendPhoneForgotEvent();

                    if(sms_forg_id == 0) {
                        sms_forg_id = setInterval(function () {
                            if (typeof formObj.attr('data-sendresult') !== 'undefined') {
                                formObj.removeAttr('data-sendresult');
                                sms_forg_id = 0;
                                clearInterval(sms_forg_id);
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
        }

        if(mode == 'phone'
            && !checked_phone
        ){
            //проверка телефона

            e.preventDefault();
            return false;
        }
    });
});

//механизм создания кода подтверждения и отправка его на указанный номер телефона (для формы восстановления пароля)
function sendPhoneForgotEvent() {
    var formObj = $('form.restore_form');
    var objVal = formObj.find('.row.phone_sms');
    var phoneVal = '';

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

                if(val != '' && (n.length != 11)&&(n.length != 0)){//проверяем телефон
                    error_val = 'Телефон должен быть в формате +7 (XXX) XXX-XX-XX';
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
                                showPhoneForgotPopup(phoneVal);
                            }else{
                                formObj.find('form').attr('data-sendresult', 'y');
                            }
                        }else{//текст ошибки
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
                }
                setCookie('lastSendMessage', $.now());
            }else{
                if(!objVal.hasClass('popup_repeat_send')) {
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
                    }

                    showPhoneForgotPopup(phoneVal);
                }
                $('#popup_phone_num input[name="sms_code"]').before('<div class="error_text">Повторный запрос можно будет направить не раньше чем через минуту с момента прошлой попытки</div>');
            }
        }
    }
}

//проверка кода из смс
function submitPhoneForgotEvent(objVal){

    var formObj = $('form.restore_form');
    var phoneObj = formObj.find('input[name="USER_EMAIL"]');
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
            restore: 'y'
            //token: token
        }).done(function (mes) {
            if(mes.substr(0, 1) == 1){//успешное выполнение
                //получаем ссылку
                var restore_href = '';
                if (mes.length > 2) {
                    document.location.href = mes.substr(2, mes.length - 2);
                } else {
                    //ошибка при генерации ссылки
                    $('#popup_phone_num input[name="sms_code"]').before('<div class="error_text">Ошибка при восстановлении. Пользователь с таким телефоном не найден. Если указан верный телефон, то попробуйте восстановить работу через email, либо обратитесь к администрации.</div>');
                }
            }else{//текст ошибки
                $('#popup_phone_num input[name="sms_code"]').before('<div class="error_text">' + mes.replace('\'', '') + '</div>');
            }
        });
    }
}