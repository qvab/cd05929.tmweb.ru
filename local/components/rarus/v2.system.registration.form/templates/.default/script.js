var arCorrectFileFormat = ['png', 'jpeg', 'jpg', 'bmp', 'doc', 'docx', 'xls', 'xlsx', 'pdf'];
var recaptchaSmsRegWidget = null;

$(document).ready(function(){
    //переключение между ролями
    $('.reg_form_control_tabs .form_control_tab').on('click', function(){
        if (!$(this).parents('.reg_form_control_tabs').hasClass('no_access')) {
            if (!$(this).hasClass('active')) {
                $(this).siblings('.active').removeClass('active');
                var type_val = $(this).attr('data-val');
                $(this).addClass('active')
                    .parents('.reg_form_control_tabs').siblings('.reg_form.active').find('input[type="hidden"][name="TYPE"]').val(type_val);
 
                if(typeof $(this).attr('data-val') !== 'undefined'){
                    switch($(this).attr('data-val')){
                        case 'client':
                            document.location.hash = '#action=register_client';
                            break;

                        case 'farmer':
                            document.location.hash = '#action=register_farmer';
                            break;
                    }
                }
            }
        }
    });

    //активируем рекаптчу
    /*var captcha = $('.reg_form.active .g-recaptcha.g-relazy', '#public_reg_form');
    if(grecaptcha && gReady) {
        captcha.trigger('gRender');
    } else {
        var setIntervalId = setInterval(function(){
            if(!grecaptcha || !gReady)
                return;
            clearInterval(setIntervalId);
            captcha.trigger('gRender');
        }, 250);
    }*/

    //отправка формы
    $('form.auth-form').on('submit', function(e){
        var found_err = false;
        var err_val = '';
        var err_scroll_top = 0;
        var temp_val = '';

        var check_code = '';
        var checkObj = {};
        var linked_error_selector = '';

        //убираем все старые ошибки
        $(this).find('.row.error').removeClass('error');
        $(this).find('.row .row_err').remove();

        //check all required fields
        $(this).find('.row .needItem').each(function(ind, cObj){
            checkObj = $(cObj).siblings('.row_val').find('input[type="text"], input[type="password"], textarea, select');
            temp_val = checkObj.val().toString().replace(/ /g, '');
            err_val = '';
            linked_error_selector = '';

            check_code = checkObj.attr('name');
            if (check_code == 'USER_EMAIL') {
                if (temp_val != '' && !checkEmailRfc(temp_val)) {
                    err_val = 'Укажите корректный email';
                }
            }
            if(check_code == 'PROP__PHONE'){
                if(temp_val == ''){
                    err_val = 'Пожалуйста заполните это обязательное поле';
                }else if($(cObj).parents('.row.phone_sms').hasClass('error')){
                    err_val = 'Телефон должен быть в формате <br/>+7 (XXX) XXX-XX-XX';
                }
            }else if(check_code == 'PROP__INN'){
                if(temp_val == ''){
                    err_val = 'Пожалуйста заполните это обязательное поле';
                }else if(temp_val.length != 10
                    && temp_val.length != 12
                ){
                    err_val = 'ИНН должен состоять из 10 или 12 цифр';
                }else if(!$(cObj).parents('.row').hasClass('check_success')){
                    err_val = 'Требуется проверка ИНН';
                }
            }else if(check_code != 'sms_code') {
                if (temp_val == '') {
                    err_val = 'Пожалуйста заполните это обязательное поле';
                }
            }

            if (err_val != '') {
                found_err = true;
                if (err_scroll_top == 0) {
                    err_scroll_top = checkObj.offset().top - 100;
                }
                if (checkObj.parents('.row').find('.row_err').length > 0) {
                    checkObj.parents('.row').addClass('error').find('.row_err:first').html(err_val);
                }
                else {
                    checkObj.after('<div class="row_err">' + err_val + '</div>');
                    checkObj.parents('.row').addClass('error');
                }

                if(linked_error_selector != ''){
                    //проверяем связанную ошибку (чтобы подсветить поле красным)
                    checkObj = checkObj.parents('form').find(linked_error_selector);
                    if(checkObj.length == 1) {
                        checkObj.parents('.row').addClass('error');
                    }
                }
            }
        });

        //check politics confirm flag
        checkObj = $(this).find('input[type="checkbox"][name="AUTH_REG_CONFIM"]');
        if (found_err == 0 && checkObj.length == 1 && !checkObj.prop('checked')) {
            e.preventDefault();
            found_err = true;
            if (err_scroll_top == 0) {
                err_scroll_top = checkObj.offset().top - 100;
            }
            alert('Не отмечена галочка согласия хранения персональных данных.');
        }

        //check reglament confirm flag
        checkObj = $(this).find('input[type="checkbox"][name="AUTH_REGLAMENT_CONFIM"]');
        if (found_err == 0 && checkObj.length == 1 && !checkObj.prop('checked')) {
            e.preventDefault();
            found_err = true;
            if (err_scroll_top == 0) {
                err_scroll_top = checkObj.offset().top - 100;
            }
            alert('Не отмечена галочка согласия с регламентом системы.');
        }

        if (found_err == true || $(this).hasClass('inactive')) {
            e.preventDefault();
            window.scrollTo(0, err_scroll_top);
            return false;
        }
        else if (!$(this).hasClass('g_passed')) {
            var sendResult = 'y';

            //проверка подтверждения телефона по смс
            var phoneArea = $(this).find('.row.phone_sms');
            var register_email_flag = ($(this).find('input[type="hidden"][name="register_email"]').length > 0);
            var phoneObj = phoneArea.find('input.phone_msk[name="PROP__PHONE"]');
            if(!phoneArea.hasClass('check_success')
                && !phoneArea.hasClass('error')
                && phoneObj.val() != ''
                && !register_email_flag
            ){
                sendPhoneButtonEvent(phoneArea, true);

                var formObj = $(this);
                var sms_int_id = setInterval(function(){
                    if(typeof formObj.attr('data-sendresult') !== 'undefined'){
                        formObj.removeAttr('data-sendresult');
                        clearInterval(sms_int_id);
                    }
                }, 500);

                e.preventDefault();
            }
        }
    });

    if ($('form.auth-form.farmer select[name="PROP__NDS"]').length > 0) {
        $('form.auth-form.farmer select[name="PROP__NDS"]').trigger('change');
    }

    $('form.auth-form input[type="password"], form.auth-form input[type="text"], form.auth-form input[type="file"], form.auth-form select, form.auth-form textarea').on('change', function(e){
        //remove error message after value change
        var error_val = '';
        checkObj = $(this);
        var name = checkObj.attr('name');
        var val = checkObj.val();
        var len = val.toString().length;

        if (name == 'PROP__PHONE') {
            var n = val.match( /\d/g );
            if (val != '' && (n.length != 11)&&(n.length != 0)) {
                error_val = 'Телефон должен быть в формате <br/>+7 (XXX) XXX-XX-XX';
            }

            //убираем вывод ошибки в блоке
            $('#public_reg_form .reg_form.active .row.phone_sms .row_err').each(function(){ $(this).text(''); });
            $('#public_reg_form .row.phone_sms.error.big_err').removeClass('error big_err');

        }
        else if (name == 'USER_EMAIL') {
            if (val != '' && !checkEmail(val)) {
                error_val = 'Укажите корректный email';
            }
        }

        if (error_val != '') {
            if (checkObj.parents('.row').find('.row_err').length == 1) {
                checkObj.parents('.row').addClass('error').find('.row_err').html(error_val);
            }
            else {
                checkObj.after('<div class="row_err">' + error_val + '</div>');
                checkObj.parents('.row').addClass('error');
            }
        }
        else {
            var err_obj = $(this).parents('.row.error');
            if(err_obj.length == 1)
            {
                err_obj.removeClass('error');
            }
        }

    });

    $('form.finalize_reg_form').on('submit', function(e){
        //check finalize register fields
        var found_err = false;
        var err_val = '';
        var err_scroll_top = 0;
        var temp_val = '';

        //check warehouse name
        var checkObj = $(this).find('input[type="password"][name="pass"]');
        if (checkObj.length == 1) {
            temp_val = checkObj.val();
            if (temp_val == '') {
                err_val = 'Пожалуйста заполните это обязательное поле';
            }
            if (err_val != '') {
                found_err = true;
                err_scroll_top = checkObj.offset().top - 100;
                if (checkObj.parents('.row').find('.row_err').length == 1) {
                    checkObj.parents('.row').addClass('error').find('.row_err').text(err_val);
                }
                else {
                    checkObj.parents('.row').find('.row_val').append('<div class="row_err"></div>');
                    checkObj.parents('.row').addClass('error').find('.row_err').text(err_val);
                }
                err_val = '';
            }
        }

        //check email
        checkObj = $(this).find('input[type="password"][name="confirm_pass"]');
        if (checkObj.length == 1) {
            temp_val = checkObj.val();
            if (temp_val == '') {
                err_val = 'Пожалуйста заполните это обязательное поле';
            }

            if (err_val != '') {
                found_err = true;
                if (err_scroll_top == 0) {
                    err_scroll_top = checkObj.offset().top - 100;
                }
                if (checkObj.parents('.row').find('.row_err').length == 1) {
                    checkObj.parents('.row').addClass('error').find('.row_err').text(err_val);
                }
                else {
                    checkObj.parents('.row').find('.row_val').append('<div class="row_err"></div>');
                    checkObj.parents('.row').addClass('error').find('.row_err').text(err_val);
                }
            }
            err_val = '';
        }

        if (found_err == true || $(this).find('.submit-btn').hasClass('inactive')) {
            e.preventDefault();
            window.scrollTo(0, err_scroll_top);
            return false;
        }
    });

    //check final register errors
    $('form.finalize_reg_form input[type="password"]').on('keyup', function(e){
        //remove error message after value change
        var err_obj = $(this).parents('.row.error');
        if (err_obj.length == 1) {
            err_obj.removeClass('error');
        }

        var found_err = false;
        //check if submit button need to activate
        $('form.finalize_reg_form input[type="password"]').each(function(ind, cObj){
            if ($(cObj).val() == '') {
                found_err = true;
            }
        });
        if (!found_err) {
            $('form.finalize_reg_form .submit-btn').removeClass('inactive');
        }
        else {
            $('form.finalize_reg_form .submit-btn').addClass('inactive');
        }
    });

    if($('#public_reg_form').hasClass('active') && $('.public_form.active').length > 0){
        $('.public_form.active').not('#public_reg_form').removeClass('active');
    }

    //отправка запроса на доставку смс
    $('.phone_sms .send_button').on('click', function(){
        sendPhoneButtonEvent(this, false);
    });

    //отправка проверки подтверждения кода, полученного по смс
    $('.phone_sms .submit_sms_button').on('click', function(){
        submitPhoneButtonEvent(this, false);
    });

    //ставим куки защиты повторной отправки смс в течение минуты
    setCookie('lastSendMessage', $.now() - 60000);
});

function sendPhoneButtonEvent(objVal, isPopup){
    if($(objVal).parents('.row.phone_sms.check_success').length == 0){
        //если телефон еще не был подтвержден, то запускаем алгоритм проверки
        var curLastTime = getCookie('lastSendMessage');

        if(getCookie('lastSendMessage') != null){
            if(isPopup){
                $('#popup_phone_num .error_text').remove();
            }
            if($.now() - curLastTime > 60000){
                var formObj = $('#public_reg_form .reg_form.active');
                var phoneObj = formObj.find('input[name="PROP__PHONE"]');

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
                }else{
                    //отправляем данные
                    $.post('/ajax/sendRegisterSms.php', {
                        phone: phoneObj.val()
                        //token: token
                    }).done(function (mes) {
                        if(mes == 1){//успешное выполнение
                            //показываем поле для ввода кода
                            if(!isPopup) {
                                formObj.find('.sms_confirmation').addClass('active');
                            }else{
                                //показываем всплывающее окно
                                if(!$(objVal).hasClass('popup_repeat_send'))
                                {
                                    showPhonePopup(formObj);
                                }else{
                                    formObj.find('form').attr('data-sendresult', 'y');
                                }
                            }
                        }else{//текст ошибки
                            if(mes.substr(0, 1) == '2'){
                                //не нужно ставить блокировку повторной отправки
                                mes = mes.substr(1, mes.length - 1);
                                setCookie('lastSendMessage', -1);
                                closeSmsPopup('body');
                            }
                            if(phoneObj.siblings('.row_err').length == 0){
                                phoneObj.parent().append('<div class="row_err">' + mes.replace('\'', '') + '</div>');
                            }else{
                                phoneObj.siblings('.row_err').text(mes);
                            }
                            phoneObj.parents('.row.phone_sms').addClass('error');
                            if(isPopup)
                            {
                                if(!$(objVal).hasClass('popup_repeat_send')) {
                                    formObj.find('form').attr('data-sendresult', 'n');
                                }else{
                                    $('#popup_phone_num input[name="sms_code"]').before('<div class="error_text">' + mes.replace('\'', '') + '</div>');
                                }
                            }
                        }
                    });
                }
                if(!isPopup){
                    $(objVal).text($(objVal).attr('data-again')).addClass('repeat_send');
                }
                setCookie('lastSendMessage', $.now());
            }else{
                if(!isPopup){
                    $('#public_reg_form .reg_form.active .row_err').each(function(){ $(objVal).text(''); });
                    var phoneObj = $(objVal).siblings('input[name="PROP__PHONE"]');
                    if(phoneObj.siblings('.row_err').length == 0){
                        phoneObj.parent().append('<div class="row_err">Повторный запрос можно будет направить не раньше чем через минуту с момента прошлой попытки</div>');
                    }else{
                        phoneObj.siblings('.row_err').text('Повторный запрос можно будет направить не раньше чем через минуту с момента прошлой попытки');
                    }
                    phoneObj.parents('.row.phone_sms').addClass('error big_err');
                }else{
                    if(!$(objVal).hasClass('popup_repeat_send')) {
                        showPhonePopup($('#public_reg_form .reg_form.active'));
                    }
                    $('#popup_phone_num input[name="sms_code"]').before('<div class="error_text">Повторный запрос можно будет направить не раньше чем через минуту с момента прошлой попытки</div>');
                }
            }
        }
    }
}

function submitPhoneButtonEvent(objVal, isPopup){


    var formObj = $('#public_reg_form .reg_form.active');
    var phoneObj = formObj.find('input[name="PROP__PHONE"]');
    var codeObj = formObj.find('input[name="sms_code"]');
    if(isPopup){
        codeObj = $('#popup_phone_num input[name="sms_code"]');
    }

    //убираем вывод ошибки в блоке
    if(isPopup){
        $('#popup_phone_num .error_text').remove();
    }else{
        formObj.find('.row.phone_sms.error').removeClass('error big_err');
        formObj.find('.row.phone_sms .row_err').each(function(){ $(objVal).text(''); });
    }

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
        if(isPopup){
            $('#popup_phone_num input[name="sms_code"]').before('<div class="error_text">Укажите корректный код подтверждения (4 цифры)</div>');
        }else{
            if(codeObj.siblings('.row_err').length == 0){
                codeObj.parent().append('<div class="row_err">Укажите корректный код подтверждения (4 цифры)</div>');
            }else{
                codeObj.siblings('.row_err').text('Укажите корректный код подтверждения (4 цифры)');
            }
            codeObj.parents('.row.phone_sms').addClass('error');
        }
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
                if(isPopup){
                    $('#back_shad').removeClass('active');
                    $('#popup_phone_num').remove();
                    formObj.find('form').append('<input type="hidden" name="success_sms" value="y" />');
                }
                $('#public_reg_form form.auth-form').submit();
            }else{//текст ошибки
                if(isPopup){
                    $('#popup_phone_num input[name="sms_code"]').before('<div class="error_text">' + mes.replace('\'', '') + '</div>');
                }else{
                    if(codeObj.siblings('.row_err').length == 0){
                        codeObj.parent().append('<div class="row_err">' + mes.replace('\'', '') + '</div>');
                    }else{
                        codeObj.siblings('.row_err').text(mes);
                    }
                    codeObj.parents('.row.phone_sms').addClass('error');
                }
            }
        });
    }
}

//закрытие попапа с смс
function closeSmsPopup(argObj){
    $('#back_shad').removeClass('active');
    $('#popup_phone_num').remove();
}

//отправка ИНН в CF
function uploadInnReg(argObj){
    //проверка ИНН на корректность
    var wObj = $(argObj).siblings('input[name="PROP__INN"]');
    //убираем ошибку, если была
    var err_obj = wObj.siblings('.row_err:first');
    var err_val = '';
    if(err_obj.length != 0){
        err_obj.parents('.row').removeClass('error');
        err_obj.remove();
    }

    //работаем со значением
    if(wObj.length == 1){
        var temp_val = wObj.val().toString().replace(' ', '');
        if(temp_val.length != 10
            && temp_val.length != 12
        ){
            //ошибка в данных ИНН
            err_val = 'ИНН должен состоять из 10 или 12 цифр';

            err_obj = wObj.siblings('.row_err:first');
            if(err_obj.length == 0){
                wObj.after('<div class="row_err"></div>');
                err_obj = wObj.siblings('.row_err');
            }

            err_obj.html(err_val).parents('.row').addClass('error');
        }
        else{
            //корректное значение ИНН -> проверяем на сервере
            $.post('/ajax/uploadContur.php',{
                inn_val: temp_val,
                u_type: $(argObj).parents('.auth-form').find('input[type="hidden"][name="TYPE"]').val()
            }, function(mes){
                if (mes == 1 || mes == 'null') {
                    err_val = 'Проверьте корректность введенного значения ИНН.';
                }else if (mes == 2) {
                    err_val = 'Данный ИНН уже зарегистрирован в системе.';
                }else{
                    var res_val = $.parseJSON(mes);
                    var show_name = '';
                    if (typeof res_val.IP != 'undefined'
                        || typeof res_val.UL != 'undefined'
                    ){
                        //проверены данные для ИП или иного юр. лица -> ставим флаг успеха
                        wObj.addClass('check_success').attr('readonly', 'readonly').parents('.row').addClass('check_success');

                        //также добавляем остальные связанные с ИНН свойства
                        if (typeof res_val.IP != 'undefined') {
                            //для ИП
                            wObj.after('<input type="hidden" name="PROP__REG_DATE" value="' + res_val.IP.registrationDate.toString().replace(/(\d\d\d\d)\-(\d\d)\-(\d\d)/g, '$3.$2.$1') +'" />');
                            wObj.after('<input type="hidden" name="PROP__IP_FIO" value="' + res_val.IP.fio + '" />');
                            wObj.after('<input type="hidden" name="PROP__OGRN" value="' + res_val.ogrn + '" />');
                            wObj.after('<input type="hidden" name="PROP__OKPO" value="' + res_val.IP.okpo + '" />');
                            wObj.after('<input type="hidden" name="PROP__UL_TYPE" value="ip" />');
                            show_name = res_val.IP.fio;
                        }else{
                            //для других типов юр. лиц
                            wObj.after('<input type="hidden" name="PROP__FULL_COMPANY_NAME" value="' + res_val.UL.legalName.full.toString().replace(/\"/g, '&quot;') + '" />');
                            show_name = res_val.UL.legalName.full.toString().replace(/\"/g, '&quot;');

                            //собираем значение адреса
                            var adres = '';
                            //регион
                            if (typeof res_val.UL.legalAddress.parsedAddressRF.regionName != 'undefined'
                                && typeof res_val.UL.legalAddress.parsedAddressRF.regionName.topoValue != 'undefined'
                            ) {
                                adres += res_val.UL.legalAddress.parsedAddressRF.regionName.topoValue;
                            }
                            if (typeof res_val.UL.legalAddress.parsedAddressRF.regionName != 'undefined'
                                && typeof res_val.UL.legalAddress.parsedAddressRF.regionName.topoShortName != 'undefined'
                            ) {
                                adres += ' '+res_val.UL.legalAddress.parsedAddressRF.regionName.topoShortName+'.';
                            }
                            //район
                            if (typeof res_val.UL.legalAddress.parsedAddressRF.district != 'undefined'
                                && typeof res_val.UL.legalAddress.parsedAddressRF.district.topoShortName != 'undefined'
                            ) {
                                adres += (adres != '' ? ', ' : '') + res_val.UL.legalAddress.parsedAddressRF.district.topoShortName;
                            }
                            if (typeof res_val.UL.legalAddress.parsedAddressRF.district != 'undefined'
                                && typeof res_val.UL.legalAddress.parsedAddressRF.district.topoValue != 'undefined'
                            ) {
                                adres += res_val.UL.legalAddress.parsedAddressRF.district.topoValue;
                            }
                            //город
                            if (typeof res_val.UL.legalAddress.parsedAddressRF.city != 'undefined'
                                && typeof res_val.UL.legalAddress.parsedAddressRF.city.topoShortName != 'undefined'
                            ) {
                                adres += (adres != '' ? ', ' : '') + res_val.UL.legalAddress.parsedAddressRF.city.topoShortName + '. ';
                            }
                            if (typeof res_val.UL.legalAddress.parsedAddressRF.city != 'undefined'
                                && typeof res_val.UL.legalAddress.parsedAddressRF.city.topoValue != 'undefined'
                            ) {
                                adres += res_val.UL.legalAddress.parsedAddressRF.city.topoValue;
                            }
                            //поселение
                            if (typeof res_val.UL.legalAddress.parsedAddressRF.settlement != 'undefined'
                                && typeof res_val.UL.legalAddress.parsedAddressRF.settlement.topoShortName != 'undefined'
                            ) {
                                adres += (adres != '' ? ', ' : '') + res_val.UL.legalAddress.parsedAddressRF.settlement.topoShortName + '. ';
                            }
                            if (typeof res_val.UL.legalAddress.parsedAddressRF.settlement != 'undefined'
                                && typeof res_val.UL.legalAddress.parsedAddressRF.settlement.topoValue != 'undefined'
                            ) {
                                adres += res_val.UL.legalAddress.parsedAddressRF.settlement.topoValue;
                            }
                            //улица
                            if (typeof res_val.UL.legalAddress.parsedAddressRF.street != 'undefined'
                                && typeof res_val.UL.legalAddress.parsedAddressRF.street.topoShortName != 'undefined'
                            ) {
                                adres += (adres != '' ? ', ' : '') + res_val.UL.legalAddress.parsedAddressRF.street.topoShortName + '. ';
                            }
                            if (typeof res_val.UL.legalAddress.parsedAddressRF.street != 'undefined'
                                && typeof res_val.UL.legalAddress.parsedAddressRF.street.topoValue != 'undefined'
                            ) {
                                adres += res_val.UL.legalAddress.parsedAddressRF.street.topoValue;
                            }
                            //дом
                            if (typeof res_val.UL.legalAddress.parsedAddressRF.house != 'undefined'
                                && typeof res_val.UL.legalAddress.parsedAddressRF.house.topoShortName != 'undefined'
                            ) {
                                adres += (adres != '' ? ', ' : '') + res_val.UL.legalAddress.parsedAddressRF.house.topoShortName + ' ';
                            }
                            if (typeof res_val.UL.legalAddress.parsedAddressRF.house != 'undefined'
                                && typeof res_val.UL.legalAddress.parsedAddressRF.house.topoValue != 'undefined'
                            ) {
                                adres += res_val.UL.legalAddress.parsedAddressRF.house.topoValue;
                            }
                            //корпус, этаж
                            if (typeof res_val.UL.legalAddress.parsedAddressRF.bulk != 'undefined'
                                && typeof res_val.UL.legalAddress.parsedAddressRF.bulk.topoValue != 'undefined'
                            ) {
                                adres += (adres != '' ? ', ' : '') + res_val.UL.legalAddress.parsedAddressRF.bulk.topoValue;
                            }
                            //помещение, офис
                            if (typeof res_val.UL.legalAddress.parsedAddressRF.flat != 'undefined'
                                && typeof res_val.UL.legalAddress.parsedAddressRF.flat.topoValue != 'undefined'
                            ) {
                                adres += (adres != '' ? ', ' : '');
                                if (typeof res_val.UL.legalAddress.parsedAddressRF.flat.topoShortName != 'undefined') {
                                    adres += res_val.UL.legalAddress.parsedAddressRF.flat.topoShortName + ' ';
                                }
                                adres += res_val.UL.legalAddress.parsedAddressRF.flat.topoValue;
                            }
                            wObj.after('<input type="hidden" name="PROP__YUR_ADRESS" value="' + adres + '" />');
                            wObj.after('<input type="hidden" name="PROP__OGRN" value="' + res_val.ogrn + '" />');
                            wObj.after('<input type="hidden" name="PROP__OKPO" value="' + res_val.UL.okpo + '" />');
                            wObj.after('<input type="hidden" name="PROP__KPP" value="' + res_val.UL.kpp + '" />');
                            wObj.after('<input type="hidden" name="PROP__REG_DATE" value="' + res_val.UL.registrationDate.toString().replace(/(\d\d\d\d)\-(\d\d)\-(\d\d)/g, '$3.$2.$1') + '" />');

                            if(typeof res_val.UL.heads != 'undefined'
                                && typeof res_val.UL.heads[0] != 'undefined'
                                && typeof res_val.UL.heads[0].fio != 'undefined'
                            ) {
                                wObj.after('<input type="hidden" name="PROP__FIO_DIR" value="' + res_val.UL.heads[0].fio + '" />');
                            }else if(typeof res_val.UL.history.heads != 'undefined'
                                && typeof res_val.UL.history.heads[0] != 'undefined'
                                && typeof res_val.UL.history.heads[0].fio != 'undefined')
                            {
                                wObj.after('<input type="hidden" name="PROP__FIO_DIR" value="' + res_val.UL.history.heads[0].fio + '" />');
                            }

                            wObj.after('<input type="hidden" name="PROP__UL_TYPE" value="ul" />');
                        }

                        //выводим название организации
                        if((show_name != '') && ($('#full-inn-name').length <= 0)){
                            wObj.parents('.inn_row').after('<div class="line row" id="full-inn-name"><div class="row_head">Полное название организации</div><div class="row_val one_line"><textarea title="Заполняется автоматически после валидации ИНН" name="" readonly="readonly">' + show_name + '</textarea></div></div>');
                        }else if((show_name != '') && ($('#full-inn-name').length > 0)) {
                            $('#full-inn-name').html('<div class="row_head">Полное название организации</div><div class="row_val one_line"><textarea title="Заполняется автоматически после валидации ИНН" name="" readonly="readonly">' + show_name + '</textarea></div>');
                        }
                    }else{
                        //ошибка при проверке данных
                        err_val = 'Проверьте корректность введенного значения ИНН.';
                    }
                }

                //если есть ошибка - выводим её
                if(err_val != ''){
                    err_obj = wObj.siblings('.row_err:first');
                    if(err_obj.length == 0){
                        wObj.after('<div class="row_err"></div>');
                        err_obj = wObj.siblings('.row_err');
                    }

                    err_obj.html(err_val).parents('.row').addClass('error');
                }
            });
        }
    }
}