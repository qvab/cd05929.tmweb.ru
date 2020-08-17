var arCorrectFileFormat = ['png', 'jpeg', 'jpg', 'bmp', 'doc', 'docx', 'xls', 'xlsx', 'pdf'];

$(document).ready(function(){

    //переключение между типами подписантов
    $('.ch_signer').on('change', function(e){
        var val = $(this).data('code');
        var form = $(this).parents('form');
        form.find('.sign_block').addClass('no_active').hide();
        form.find('.signer_'+val).removeClass('no_active').show();
    });

    $('form[name="profile_form"]').on('submit', function(e){
        //check all fields are not empty
        var found_err = false, temp_val = '', err_val = '', check_code = '', err_scroll_top = '';
        var formObj = $(this);
        var checkObj = {};

        $(this).find('input[type="text"], input[type="password"], textarea, select').each(function(ind, cObj){
            checkObj = $(cObj);
            if (checkObj.parents('.sign_block.no_active').length == 0) {
                temp_val = $(cObj).val().toString().replace(/ /g, '');

                check_code = $(cObj).attr('name');
                if (check_code == 'USER_EMAIL') {
                    if (temp_val == '') {
                        err_val = 'Пожалуйста заполните это обязательное поле';
                    }
                    else if (!checkEmailRfc(temp_val)) {
                        err_val = 'Укажите корректный email';
                    }
                }
                else if (check_code == 'PROP__BANK') {
                }
                else if (check_code == 'PROP__BIK') {
                }
                else if (check_code == 'num[ul][sign]' || check_code == 'date[ul][sign]'
                    || check_code == 'fio[ul][sign]' || check_code == 'post[ul][sign]'
                ) {
                }
                else if (check_code == 'PROP__RASCH_SCHET' || check_code == 'PROP__KOR_SCHET') {
                }
                else if(check_code != 'sms_code'
                    && check_code != 'PROP__POST_ADRESS'
                    && check_code != 'PROP__FIO_DIR'
                    && check_code != 'SECOND_NAME'
                    && $(cObj).attr('data-phone') != 'PROP__PHONE'
                ) {
                    if (temp_val == '') {
                        err_val = 'Пожалуйста заполните это обязательное поле';
                    }
                }

                if(err_val != '')
                {
                    found_err = true;
                    if(err_scroll_top == 0)
                    {
                        err_scroll_top = $(cObj).offset().top - 100;
                    }
                    if($(cObj).parents('.row').find('.row_err').length > 0)
                    {
                        $(cObj).parents('.row').addClass('error').find('.row_err').text(err_val);
                    }
                    else
                    {
                        $(cObj).after('<div class="row_err">' + err_val + '</div>');
                        $(cObj).parents('.row').addClass('error');
                    }
                }
                err_val = '';
            }
        });

        //проверка на изменение телефона (требует подтверждения по смс)
        var phoneArea = formObj.find('.row.phone_sms');
        var phoneInput = phoneArea.find('input[data-phone="PROP__PHONE"]');
        //убираем вывод ошибки в блоке
        formObj.find('.phone_sms .row_err').each(function(){ $(this).text(''); });
        formObj.find('.row.phone_sms.error').removeClass('error big_err');

        var n = '';
        if(phoneInput.val() != ''){
            n = phoneInput.val().toString().match( /\d/g );
        }

        if (phoneArea.hasClass('changed')
            && (phoneInput.val() == '' || (n.length != 11)&&(n.length != 0))
        ) {
            err_val = 'Необходимо подтверждение по смс';
            if(phoneInput.val() == ''){
                err_val = 'Телефон должен быть в формате +7 (XXX) XXX-XX-XX';
            }
            if(err_scroll_top == 0)
            {
                err_scroll_top = phoneArea.offset().top - 100;
            }
            found_err = true;
            if(phoneArea.find('.row_err').length > 0)
            {
                phoneInput.parents('.row').addClass('error').find('.row_err:first').text(err_val);
            }
            else
            {
                phoneInput.after('<div class="row_err">' + err_val + '</div>');
                phoneArea.addClass('error');
            }
        }

        $(this).find('input[type="file"]').each(function(ind, cObj){
            err_val = '';
            checkObj = $(cObj);
            var val = checkObj.val();
            if (val != '') {
                var arVal = val.split('.');
                var ext = arVal[arVal.length - 1];
                ext = ext.toLowerCase();
                if (!in_array(ext, arCorrectFileFormat)) {
                    err_val = 'Неверный формат файла';
                }
            }
            else if (checkObj.hasClass('needFile')) {
                err_val = 'Прикрепите файл';
            }

            if(err_val != '') {
                found_err = true;
                if(err_scroll_top == 0)
                {
                    err_scroll_top = checkObj.offset().top - 100;
                }
                if(checkObj.parents('.row').find('.row_err').length > 0)
                {
                    checkObj.parents('.row').addClass('error').find('.row_err').text(err_val);
                }
                else
                {
                    checkObj.after('<div class="row_err">' + err_val + '</div>');
                    checkObj.parents('.row').addClass('error');
                }
            }
        });

        if(found_err)
        {
            e.preventDefault();
            if(err_scroll_top != 0)
            {
                window.scrollTo(0, err_scroll_top);
            }
        }else{
            //проверка подтверждения смс по телефону
            if(phoneArea.hasClass('changed')
                && !phoneArea.hasClass('check_success')
            ){
                sendPhoneButtonEvent(phoneArea, true);
                e.preventDefault();
            }
        }
    });

    $('form[name="profile_form"] input[type="password"], form[name="profile_form"] input[type="text"], form[name="profile_form"] input[type="file"], form[name="profile_form"] select, form[name="profile_form"] textarea').on('change', function(e){
        var error_val = '';
        checkObj = $(this);
        var name = checkObj.attr('name');
        var val = checkObj.val();
        var len = val.toString().length;

        if (name == 'PROP__BIK') {
            if (len != 9) {
                var error_val = 'Поле должно содержать 9 цифр';
            }
        }
        else if (name == 'PROP__RASCH_SCHET' || name == 'PROP__KOR_SCHET') {
            if (len > 0 && len != 20) {
                var error_val = 'Поле должно содержать 20 цифр';
            }
        }

        if (error_val != '') {
            if (checkObj.parents('.row').find('.row_err').length > 0) {
                checkObj.parents('.row').addClass('error').find('.row_err').text(error_val);
            }
            else {
                checkObj.after('<div class="row_err">' + error_val + '</div>');
                checkObj.parents('.row').addClass('error');
            }
        }
        else {
            var err_obj = $(this).parents('.row.error');
            if (err_obj.length > 0) {
                err_obj.removeClass('error');
            }
        }
    });

    //обработка телефона
    $('input[data-phone="PROP__PHONE"]').on('change', function(){
        var val = $(this).val();
        var n = '';
        var error_val = '';

        if(val != ''){
            n = val.match( /\d/g );
        }

        if (val == '' && $(this).attr('data-val') != '' || (n.length != 11)&&(n.length != 0)) {
            error_val = 'Телефон должен быть в формате +7 (XXX) XXX-XX-XX';
        }

        //убираем вывод ошибки в блоке
        var formObj = $(this).parents('form');
        formObj.find('.phone_sms .row_err').each(function(){ $(this).text(''); });
        formObj.find('.row.phone_sms.error').removeClass('error big_err');

        if (error_val != '') {
            if ($(this).parents('.row').find('.row_err').length > 0) {
                $(this).parents('.row').addClass('error').find('.row_err').text(error_val);
            }
            else {
                $(this).after('<div class="row_err">' + error_val + '</div>');
                $(this).parents('.row').addClass('error');
            }
            if(val != $(this).attr('data-val')){
                $(this).parents('.row.phone_sms').addClass('changed');
            }
        }else{
            //проверка изменен ли договор
            if(val != $(this).attr('data-val')){
                $(this).parents('.row.phone_sms').addClass('changed');
                var sendButton = $(this).parents('.row.phone_sms').find('.send_button');
                sendButton.removeClass('repeat_send').text(sendButton.attr('data-first'));
            }else{
                $(this).parents('.row.phone_sms').removeClass('changed');
                //убираем поле кода подтверждения, если поле было отображено
                $(this).parents('.row.phone_sms').find('.sms_confirmation.active').removeClass('active');
            }
        }
    });

    //отправка запроса на доставку смс
    $('.phone_sms .send_button').on('click', function(){
        if($(this).parents('.row.phone_sms.check_success').length == 0){
            //если телефон еще не был подтвержден, то запускаем алгоритм прове рки
            var curLastTime = getCookie('lastSendMessage');
            if(getCookie('lastSendMessage') != null){
                if($.now() - curLastTime > 60000){
                    regRecaptchaPhoneSubmit();
                }else{
                    $('#page_body form[name="profile_form"] .phone_sms .row_err').each(function(){ $(this).text(''); });
                    var phoneObj = $(this).siblings('input[data-phone="PROP__PHONE"]');
                    if(phoneObj.siblings('.row_err').length == 0){
                        phoneObj.parent().append('<div class="row_err">Повторный запрос можно будет направить не раньше чем через минуту с момента прошлой попытки</div>');
                    }else{
                        phoneObj.siblings('.row_err').text('Повторный запрос можно будет направить не раньше чем через минуту с момента прошлой попытки');
                    }
                    phoneObj.parents('.row.phone_sms').addClass('error big_err');
                }
            }
        }
    });

    //отправка проверки подтверждения кода, полученного по смс
    $('.phone_sms .submit_sms_button').on('click', function(){
        var formObj = $('#page_body form[name="profile_form"]');
        var phoneObj = formObj.find('input[data-phone="PROP__PHONE"]');
        var codeObj = formObj.find('input[name="sms_code"]');

        //убираем вывод ошибки в блоке
        formObj.find('.row.phone_sms.error').removeClass('error big_err');
        formObj.find('.row.phone_sms .row_err').each(function(){ $(this).text(''); });

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
            if(codeObj.siblings('.row_err').length == 0){
                codeObj.parent().append('<div class="row_err">Укажите корректный код подтверждения (4 цифры)</div>');
            }else{
                codeObj.siblings('.row_err').text('Укажите корректный код подтверждения (4 цифры)');
            }
            codeObj.parents('.row.phone_sms').addClass('error');
        }else{
            //отправляем данные
            $.post('/ajax/checkRegisterSmsCode.php', {
                phone: phoneObj.val(),
                code: codeObj.val()
                //token: token
            }).done(function (mes) {
                if(mes == 1){//успешное выполнение
                    //ставим галку успеха
                    formObj.find('.row.phone_sms').addClass('check_success');
                    phoneObj.attr('readonly', 'readonly');
                    //устанавливаем input со значением
                    phoneObj.after('<input type="hidden" name="PROP__PHONE" value="' + phoneObj.val() + '" />');
                }else{//текст ошибки
                    if(codeObj.siblings('.row_err').length == 0){
                        codeObj.parent().append('<div class="row_err">' + mes.replace('\'', '') + '</div>');
                    }else{
                        codeObj.siblings('.row_err').text(mes);
                    }
                    codeObj.parents('.row.phone_sms').addClass('error');
                }
            });
        }
    });

    //ставим куки защиты повторной отправки смс в течение минуты
    setCookie('lastSendMessage', $.now() - 60000);
});

function regRecaptchaPhoneSubmit(/*token*/) {
    var formObj = $('#page_body form[name="profile_form"]');
    var phoneObj = formObj.find('input[data-phone="PROP__PHONE"]');
    var phoneDigits = phoneObj.val().match( /\d/g );

    //убираем вывод ошибки в блоке
    formObj.find('.row.phone_sms.error').removeClass('error big_err');
    formObj.find('.row.phone_sms .row_err').each(function(){ $(this).text(''); });

    if(phoneObj.val() == ''){//проверяем телефон
        if(phoneObj.siblings('.row_err').length == 0){
            phoneObj.parent().append('<div class="row_err">Пожалуйста заполните это обязательное поле</div>');
        }else{
            phoneObj.siblings('.row_err').text('Пожалуйста заполните это обязательное поле');
        }
        phoneObj.parents('.row.phone_sms').addClass('error');
    }else if(phoneDigits.length != 11 && phoneDigits.length != 0){
        if(phoneObj.siblings('.row_err').length == 0){
            phoneObj.parent().append('<div class="row_err">Телефон должен быть в формате +7 (XXX) XXX-XX-XX</div>');
        }else{
            phoneObj.siblings('.row_err').text('Телефон должен быть в формате +7 (XXX) XXX-XX-XX');
        }
        phoneObj.parents('.row.phone_sms').addClass('error');
    }else{
        //отправляем данные
        $.post('/ajax/sendRegisterSms.php', {
            phone: phoneObj.val(),
            profile: 'y'
            //token: token
        }).done(function (mes) {
            if(mes == 1){//успешное выполнение
                //показываем поле для ввода кода
                formObj.find('.sms_confirmation').addClass('active');
            }else{//текст ошибки
                if(mes.substr(0, 1) == '2'){
                    //не нужно ставить блокировку повторной отправки
                    mes = mes.substr(1, mes.length - 1);
                    setCookie('lastSendMessage', -1);
                    var offset = $('.row.phone_sms').offset();
                    $(window).scrollTop(offset.top - 50);
                    closeSmsPopup('body');
                }
                if(phoneObj.siblings('.row_err').length == 0){
                    phoneObj.parent().append('<div class="row_err">' + mes.replace('\'', '') + '</div>');
                }else{
                    phoneObj.siblings('.row_err').text(mes);
                }
                phoneObj.parents('.row.phone_sms').addClass('error');
            }
        });

        var button_obj = formObj.find('.send_button');
        $(button_obj).text($(button_obj).attr('data-again')).addClass('repeat_send');
        setCookie('lastSendMessage', $.now());
    }
}