var arCorrectFileFormat = ['png', 'jpeg', 'jpg', 'bmp', 'doc', 'docx', 'xls', 'xlsx', 'pdf'];
//var recaptchaSmsRegWidget = null;

$(document).ready(function(){
    //переключение между ролями
    $('.reg_form_control_tabs .form_control_tab').on('click', function(){
        if (!$(this).parents('.reg_form_control_tabs').hasClass('no_access')) {
            if (!$(this).hasClass('active')) {
                var num_list = $(this).attr('data-val');
                $('.form_control_tab.active, .reg_form.active').removeClass('active');
                $('.form_control_tab[data-val="' + num_list + '"], .reg_form[data-val="' + num_list + '"]').addClass('active');

                // Активируем капчу
                var captcha = $('.reg_form.active .g-recaptcha.g-relazy', '#public_reg_form');
                if(grecaptcha && gReady) {
                    captcha.trigger('gRender');
                } else {
                    var setIntervalId = setInterval(function(){
                        if(!grecaptcha || !gReady)
                            return;
                        clearInterval(setIntervalId);
                        captcha.trigger('gRender');
                    }, 250);
                }
            }
        }
    });

    //почтовый адрес соответствует юридическому
    $('.is_yur_adres').on('change', function(e){
        var form = $(this).parents('form');
        if ($(this).prop('checked')) {
            var adres = form.find('input[name="PROP__YUR_ADRESS"]').val();
            form.find('input[name="PROP__POST_ADRESS"]').val(adres);
            if (adres != '') {
                if (form.find('input[name="PROP__POST_ADRESS"]').parents('.row').hasClass('error')) {
                    form.find('input[name="PROP__POST_ADRESS"]').parents('.row').removeClass('error');
                }
            }
        }
        else {
            form.find('input[name="PROP__POST_ADRESS"]').val('');
        }
    });

    //переключение между типами подписантов
    $('.ch_signer').on('change', function(e){
        var val = $(this).data('code');
        var form = $(this).parents('form');
        form.find('.sign_block').addClass('no_active').hide();
        form.find('.signer_'+val).removeClass('no_active').show();
    });

    //отправка формы
    $('form.auth-form').on('submit', function(e){
        var found_err = false;
        var err_val = '';
        var err_scroll_top = 0;
        var temp_val = '';

        var check_code = '';
        var checkObj = {};

        //check all required fields
        $(this).find('.row .needItem').each(function(ind, cObj){
            checkObj = $(cObj).siblings('.row_val').find('input[type="text"], input[type="password"], textarea, select');
            if (checkObj.length == 1 && checkObj.parents('.sign_block.no_active').length == 0) {
                temp_val = checkObj.val().toString().replace(/ /g, '');
                check_code = checkObj.attr('name');
                if (check_code == 'USER_EMAIL') {
                    if (temp_val == '') {
                        err_val = 'Пожалуйста заполните это обязательное поле';
                    }
                    else if (!checkEmailRfc(temp_val)) {
                        err_val = 'Укажите корректный email';
                    }
                }
                else if (check_code == 'PROP__BIK') {
                    if (temp_val == '') {
                        err_val = 'Пожалуйста заполните это обязательное поле';
                    }
                    else if (temp_val.length != 9) {
                        err_val = 'Поле должно содержать 9 цифр';
                    }
                }
                else if (check_code == 'PROP__RASCH_SCHET' || check_code == 'PROP__KOR_SCHET') {
                    if (temp_val == '') {
                        err_val = 'Пожалуйста заполните это обязательное поле';
                    }
                    else if (temp_val.length != 20) {
                        err_val = 'Поле должно содержать 20 цифр';
                    }
                // }else if(check_code == 'PROP__PHONE'
                //     && checkObj.parents('.row.phone_sms').length == 1
                //     && !checkObj.parents('.row.phone_sms').hasClass('check_success')
                // ) {
                //     err_val = 'Необходимо подтверждение <br/>телефона';
                } else if(check_code != 'sms_code') {
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
                }
                err_val = '';
            }
            else if (checkObj.length > 1 && checkObj.parents('.sign_block.no_active').length == 0) {

                checkObj.each(function(i, cObj2){
                    temp_val = cObj2.value.toString().replace(/ /g, '');
                    if (temp_val == '') {
                        err_val = 'Пожалуйста, заполните все поля';
                        return;
                    }
                });

                if (err_val != '') {
                    var cObj2 = checkObj.first();
                    found_err = true;
                    if (err_scroll_top == 0) {
                        err_scroll_top = cObj2.offset().top - 100;
                    }
                    if (cObj2.parents('.row').find('.row_err').length > 0) {
                        cObj2.parents('.row').addClass('error').find('.row_err:first').html(err_val);
                    }
                    else {
                        cObj2.after('<div class="row_err">' + err_val + '</div>');
                        cObj2.parents('.row').addClass('error');
                    }
                }
            }
        });

        if ($(this).find('input[name="PROP__REG_DATE"]').length == 0) {
            checkObj = $(this).find('input[type="hidden"][name="PROP__FULL_COMPANY_NAME"]');
            if (checkObj.length == 1 && err_val == '') {
                temp_val = checkObj.val();
                if (temp_val == '') {
                    err_val = 'Пожалуйста установите данные по вашему ИНН';
                }

                //change object to inn input
                checkObj = $(this).find('input[type="text"][name="PROP__INN"]');
                if (err_val != '') {
                    found_err = true;
                    if (err_scroll_top == 0) {
                        err_scroll_top = checkObj.offset().top - 100;
                    }
                    if (checkObj.parents('.row').find('.row_err').length == 1) {
                        checkObj.parents('.row').addClass('error').find('.row_err').text(err_val);
                    }
                    else {
                        checkObj.parents('.row').find('input[name="PROP__INN"]').after('<div class="row_err"></div>');
                        checkObj.parents('.row').addClass('error').find('.row_err').text(err_val);
                    }
                }
            }
        }
        else {
            checkObj = $(this).find('input[type="hidden"][name="PROP__REG_DATE"]');
            if (checkObj.length == 1 && err_val == '') {
                temp_val = checkObj.val();
                if (temp_val == '') {
                    err_val = 'Пожалуйста установите данные по вашему ИНН';
                }

                //change object to inn input
                checkObj = $(this).find('input[type="text"][name="PROP__INN"]');
                if (err_val != '') {
                    found_err = true;
                    if (err_scroll_top == 0) {
                        err_scroll_top = checkObj.offset().top - 100;
                    }
                    if (checkObj.parents('.row').find('.row_err').length == 1) {
                        checkObj.parents('.row').addClass('error').find('.row_err').text(err_val);
                    }
                    else {
                        checkObj.parents('.row').find('input[name="PROP__OGRN"]').after('<div class="row_err"></div>');
                        checkObj.parents('.row').addClass('error').find('.row_err').text(err_val);
                    }
                }
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
                if (err_scroll_top == 0) {
                    err_scroll_top = checkObj.offset().top - 100;
                }
                if (checkObj.parents('.row').find('.row_err').length == 1) {
                    checkObj.parents('.row').addClass('error').find('.row_err').text(err_val);
                }
                else {
                    checkObj.after('<div class="row_err">' + err_val + '</div>');
                    checkObj.parents('.row').addClass('error');
                }
            }
        });

        err_val = '';

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
            if(!phoneArea.hasClass('check_success')){
                sendPhoneButtonEvent(phoneArea, true);

                var formObj = $(this);
                var sms_int_id = setInterval(function(){
                    if(typeof formObj.attr('data-sendresult') !== 'undefined'){
                        formObj.removeAttr('data-sendresult');
                        clearInterval(sms_int_id);
                    }
                }, 500);

                e.preventDefault();
            }else{
                grecaptcha.execute();
                e.preventDefault();
            }
        }
    });

    if ($('form.auth-form.farmer select[name="PROP__NDS"]').length > 0) {
        $('form.auth-form.farmer select[name="PROP__NDS"]').trigger('change');
    }

    $('form.auth-form select[name="PROP__NDS"]').on('change', function(){
        var form = $(this).parents('form');

        var user_type = form.find('input[name="TYPE"]').val();
        var nds = $(this).val();
        var inn_obj = form.find('input[name="PROP__INN"]');
        var inn = '';

        if(inn_obj.length == 0){
            return false;
        }

        inn = inn_obj.val();
        var len_inn = inn.length;
        var type = '';

        if (len_inn == 10) {
            type = 'ul';
        }
        else if (len_inn == 12) {
            type = 'ip';
        }

        form.find('.docs_line').remove();
        if (type != '') {
            $.ajax({
                type: "POST",
                url: "/ajax/loadDocuments.php",
                data: "user_type="+user_type+"&nds="+nds+"&type="+type,
                success: function(msg){
                    form.find('.row.policy_row').before(msg);
                }
            });
        }

    });

    $('form.auth-form input[type="password"], form.auth-form input[type="text"], form.auth-form input[type="file"], form.auth-form select, form.auth-form textarea').on('change', function(e){
        //remove error message after value change
        var error_val = '';
        checkObj = $(this);
        var name = checkObj.attr('name');
        var val = checkObj.val();
        var len = val.toString().length;

        if (name == 'PROP__INN') {
            var form = $(this).parents('form');
            if (len != 10 && len != 12) {
                error_val = 'Номер должен состоять из 10 или 12 цифр';
                $('.signer_ul, .signer_ip, .sign_block').hide();
            }
            else {
                var user_type = form.find('input[name="TYPE"]').val();
                var nds = form.find('select[name="PROP__NDS"]').val();

                var type = '';
                if (len == 10) {
                    type = 'ul';
                }
                else if (len == 12) {
                    type = 'ip';
                }

                form.find('.docs_line').remove();
                if (type != '') {
                    $.ajax({
                        type: "POST",
                        url: "/ajax/loadDocuments.php",
                        data: "user_type="+user_type+"&nds="+nds+"&type="+type,
                        success: function(msg){
                            form.find('.row.policy_row').before(msg);
                        }
                    });
                }

                if (type == 'ul') {
                    form.find('.signer_ip, .sign_block').hide();
                    form.find('.signer_ul, .signer_ul_dir').show();
                }
                else if (type == 'ip') {
                    form.find('.signer_ul, .sign_block').hide();
                    form.find('.signer_ip, .signer_ip_ip').show();
                }
            }
        }
        else if (name == 'PROP__BIK') {
            if (len != 9) {
                error_val = 'Поле должно содержать 9 цифр';
            }
        }
        else if (name == 'PROP__RASCH_SCHET' || name == 'PROP__KOR_SCHET') {
            if (len != 20) {
                error_val = 'Поле должно содержать 20 цифр';
            }
        }
        else if (name == 'PROP__PHONE') {
            var n = val.match( /\d/g );
            if (val == '' || (n.length != 11)&&(n.length != 0)) {
                error_val = 'Телефон должен быть в формате +7 (XXX) XXX-XX-XX';
            }

            //убираем вывод ошибки в блоке
            $('#public_reg_form .reg_form.active .row.phone_sms .row_err').each(function(){ $(this).text(''); });
            $('#public_reg_form .row.phone_sms.error.big_err').removeClass('error big_err');

        }
        else if (name == 'USER_EMAIL') {
            if (!checkEmail(val)) {
                error_val = 'Укажите корректный email';
            }
        }

        if (error_val != '') {
            if (checkObj.parents('.row').find('.row_err').length == 1) {
                checkObj.parents('.row').addClass('error').find('.row_err').text(error_val);
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

    $('form.auth-form input[name="PROP__PHONE"]').on('blur', function(e){
        var error_val = '';
        checkObj = $(this);
        var name = checkObj.attr('name');
        var val = checkObj.val();

        /*if (val == '') {
            var error_val = 'Телефон должен быть в формате +7 (XXX) XXX-XX-XX';
        }*/

        if (error_val != '') {
            if (checkObj.parents('.row').find('.row_err').length == 1) {
                checkObj.parents('.row').addClass('error').find('.row_err').text(error_val);
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
            var sendButton = $(this).siblings('.send_button');
            sendButton.removeClass('repeat_send').text(sendButton.attr('data-first'));
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

    //add fields masks
    //integer positive to registration
    $('#public_reg_form input[type="text"][name="PROP__INN"], #public_reg_form input[type="text"][name="PROP__BIK"], #public_reg_form input[type="text"][name="PROP__RASCH_SCHET"], #public_reg_form input[type="text"][name="PROP__KOR_SCHET"]').on('keyup', function(){
        checkMask($(this), 'pos_int_empty');
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
                                if(!objVal.hasClass('popup_repeat_send'))
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
                                if(!objVal.hasClass('popup_repeat_send')) {
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

    /* google recaptcha */
    // if(recaptchaSmsRegWidget == null){
    //     recaptchaSmsRegWidget = grecaptcha.render(document.getElementById('recaptcha_sms'), {
    //         'sitekey': '6LeDzmAUAAAAABZV4UNfOq9SzwqDqtWJXvtDPb5G'
    //     });
    // }else{
    //     grecaptcha.reset(recaptchaSmsRegWidget);
    // }
    //
    // grecaptcha.execute(recaptchaSmsRegWidget);
    /* /google recaptcha */
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

function closeSmsPopup(argObj){
    $('#back_shad').removeClass('active');
    $('#popup_phone_num').remove();
}

function uploadBic(argObj) {
    var data_val = $(argObj).siblings('input[type="text"]').val().toString();
    data_val = data_val.replace(/[^0-9]/g, '');
    if (data_val.length == 9) {
        var dots_val = '...';
        var inter_id = setInterval(function(){
            if (dots_val == '.') {
                dots_val = '..';
            }
            else if (dots_val == '..') {
                dots_val = '...';
            }
            else if (dots_val == '...') {
                dots_val = '.';
            }
            $(argObj).attr('value', $(argObj).attr('data-val') + '(загрузка ' + dots_val + ')');
        }, 1000);

        $.post('/ajax/uploadBic.php',{
            bic_val: data_val
        }, function(mes){
            clearInterval(inter_id);
            $(argObj).attr('value', $(argObj).attr('data-val'));

            var res_val = $.parseJSON(mes);

            if (res_val.error != undefined) {
                alert('Проверьте корректность введенного значения БИК');
            }
            else {
                var wObj = $('.reg_form.active input[name="PROP__BANK"]:first');
                wObj.val(res_val.namemini.replace(/&quot;/g, '"')).parents('.row').removeClass('error');

                wObj = $('.reg_form.active input[name="PROP__KOR_SCHET"]:first');
                wObj.val(res_val.ks).parents('.row').removeClass('error');
            }
        });
    }
    else {
        alert('Ошибка в БИК (номер должен состоять из 9 цифр)');
    }
}

function uploadInn(argObj)
{
    //check if inn already was loaded
    if (0 && $('input[name="PROP__KPP"][type="hidden"]').val() != '') {
        //old logic (restrict double inn check if loaded already)
        alert('Данные уже были загружены из базы.');
    }
    else {
        var data_val = $(argObj).siblings('input[type="text"]').val().toString();
        data_val = data_val.replace(/[^0-9]/g, '');
        if (data_val.length == 10 || data_val.length == 12) {
            //start uploading visual
            var dots_val = '...';
            var inter_id = setInterval(function(){
                if (dots_val == '.') {
                    dots_val = '..';
                }
                else if (dots_val == '..') {
                    dots_val = '...';
                }
                else if (dots_val == '...') {
                    dots_val = '.';
                }
                $(argObj).attr('value', $(argObj).attr('data-val') + '(загрузка ' + dots_val + ')');
            }, 1000);

            $.post('/ajax/uploadContur.php',{
                inn_val: data_val,
                u_type: $(argObj).parents('.auth-form').find('input[type="hidden"][name="TYPE"]').val()
            }, function(mes){
                clearInterval(inter_id);
                $(argObj).attr('value', $(argObj).attr('data-val'));
                if (mes == 1 || mes == 'null') {
                    alert('Проверьте корректность введенного значения ИНН.');
                }
                else if (mes == 2) {
                    alert('Данный ИНН уже зарегистрирован в системе.');
                }
                else {

                    var res_val = $.parseJSON(mes);

                    if (typeof res_val.IP != 'undefined') {
                        //IP
                        /*if ($('form.auth-form .input[name="IP"]').length == 0) {
                            //add ip props
                            $('form.auth-form').prepend('<input type="hidden" name="IP" value="Y" /><input type="hidden" name="PROP__IP_REG_DATE" value="Y" /><input type="hidden" name="PROP__IP_FIO" value="Y" />');
                        }*/

                        //hide not ip props
                        $('.reg_form.active input[name="PROP__FULL_COMPANY_NAME"]:first, ' +
                            '.reg_form.active input[name="PROP__YUR_ADRESS"]:first, ' +
                            '.reg_form.active input[name="PROP__KPP"]:first, ' +
                            '.reg_form.active input[name="PROP__FIO_DIR"]:first, ' +
                            '.reg_form.active .is_yur_adres:first'
                        ).parents('.row').css('display', 'none');

                        //show not ip props
                        $('.reg_form.active input[name="PROP__IP_FIO"]:first').parents('.row').css('display', 'block');

                        //reg date (format 2010-08-18 to 18.08.2010)
                        var wObj = $('.reg_form.active input[name="PROP__REG_DATE"]:first');
                        wObj.val(res_val.IP.registrationDate.toString().replace(/(\d\d\d\d)\-(\d\d)\-(\d\d)/g, '$3.$2.$1'));

                        //ip fio
                        wObj = $('.reg_form.active input[name="PROP__IP_FIO"]:first');
                        wObj.val(res_val.IP.fio);
                        var wObj2 = wObj.siblings('textarea');
                        wObj2.val(wObj2.attr('placeholder') + ': ' + res_val.IP.fio);

                        //ogrn
                        wObj = $('.reg_form.active input[name="PROP__OGRN"]:first');
                        wObj.val(res_val.ogrn);
                        wObj2 = wObj.siblings('input[type="text"]');
                        wObj2.val(wObj2.attr('placeholder') + ': ' + res_val.ogrn);

                        //okpo
                        wObj = $('.reg_form.active input[name="PROP__OKPO"]:first');
                        wObj.val(res_val.IP.okpo);
                        wObj2 = wObj.siblings('input[type="text"]');
                        wObj2.val(wObj2.attr('placeholder') + ': ' + res_val.IP.okpo);

                        wObj = $('.reg_form.active input[name="PROP__UL_TYPE"]:first');
                        wObj.val('ip');
                    }
                    else {
                        //not IP
                        //show not ip props
                        $('.reg_form.active input[name="PROP__FULL_COMPANY_NAME"]:first, ' +
                            '.reg_form.active input[name="PROP__YUR_ADRESS"]:first, ' +
                            '.reg_form.active input[name="PROP__KPP"]:first, ' +
                            '.reg_form.active input[name="PROP__FIO_DIR"]:first, ' +
                            '.reg_form.active .is_yur_adres:first'
                        ).parents('.row').css('display', 'block');

                        //hide not ip props
                        $('.reg_form.active input[name="PROP__IP_FIO"]:first').parents('.row').css('display', 'none');

                        //full name
                        var wObj = $('.reg_form.active input[name="PROP__FULL_COMPANY_NAME"]:first');
                        wObj.val(res_val.UL.legalName.full);
                        var wObj2 = wObj.siblings('textarea');
                        wObj2.val(wObj2.attr('placeholder') + ': ' + res_val.UL.legalName.full);

                        //check if individual
                        //yur adress
                        wObj = $('.reg_form.active input[name="PROP__YUR_ADRESS"]:first');
                        wObj2 = wObj.siblings('textarea');
                        var adres = '';

                        //регион
                        if (typeof res_val.UL.legalAddress.parsedAddressRF.regionName != 'undefined'
                            && typeof res_val.UL.legalAddress.parsedAddressRF.regionName.topoValue != 'undefined'
                        ) {
                            adres += res_val.UL.legalAddress.parsedAddressRF.regionName.topoValue;
                        }
                        if (typeof res_val.UL.legalAddress.parsedAddressRF.regionName != 'undefined'
                            && typeof res_val.UL.legalAddress.parsedAddressRF.regionName.topoShortName != 'undefined'
                            //&& res_val.UL.legalAddress.parsedAddressRF.regionName.topoShortName == 'обл'
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

                        wObj.val(adres);
                        wObj2.val(wObj2.attr('placeholder') + ': ' + adres);

                        //ogrn
                        wObj = $('.reg_form.active input[name="PROP__OGRN"]:first');
                        wObj.val(res_val.ogrn);
                        wObj2 = wObj.siblings('input[type="text"]');
                        wObj2.val(wObj2.attr('placeholder') + ': ' + res_val.ogrn);

                        //okpo
                        wObj = $('.reg_form.active input[name="PROP__OKPO"]:first');
                        wObj.val(res_val.UL.okpo);
                        wObj2 = wObj.siblings('input[type="text"]');
                        wObj2.val(wObj2.attr('placeholder') + ': ' + res_val.UL.okpo);

                        //kpp
                        wObj = $('.reg_form.active input[name="PROP__KPP"]:first');
                        wObj.val(res_val.UL.kpp);
                        wObj2 = wObj.siblings('input[type="text"]');
                        wObj2.val(wObj2.attr('placeholder') + ': ' + res_val.UL.kpp);

                        //reg date

                        //reg date (format 2010-08-18 to 18.08.2010)
                        wObj = $('.reg_form.active input[name="PROP__REG_DATE"]:first');
                        wObj.val(res_val.UL.registrationDate.toString().replace(/(\d\d\d\d)\-(\d\d)\-(\d\d)/g, '$3.$2.$1'));

                        //fio gen_dir
                        wObj = $('.reg_form.active input[name="PROP__FIO_DIR"]:first');
                        wObj.val(res_val.UL.heads[0].fio);

                        wObj = $('.reg_form.active input[name="PROP__UL_TYPE"]:first');
                        wObj.val('ul');

                        //post_address
                        var form = $(this).parents('form');
                        if ($('.reg_form.active .is_yur_adres:first').prop('checked')) {
                            wObj = $('.reg_form.active input[name="PROP__POST_ADRESS"]:first');
                            wObj.val(adres);
                            if (wObj.parents('.row').hasClass('error')) {
                                wObj.parents('.row').removeClass('error');
                            }
                        }
                    }
                }
            });
        }
        else {
            alert('Ошибка в ИНН (номер должен состоять из 10 или 12 цифр)');
        }
    }
}