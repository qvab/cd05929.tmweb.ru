var arCorrectFileFormat = ['png', 'jpeg', 'jpg', 'bmp', 'doc', 'docx', 'xls', 'xlsx', 'pdf'];

$(document).ready(function(){

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

    $('form[name="profile_form"]').on('submit', function(e){
        var found_err = false;
        var err_val = '';
        var err_scroll_top = '';
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
                    else {
                        err_val = '';
                        checkObj.parents('.row').removeClass('error');
                    }
                }
                else if (check_code == 'PROP__BIK') {
                    if (temp_val == '') {
                        err_val = 'Пожалуйста заполните это обязательное поле';
                    }
                    else if (temp_val.length != 9) {
                        err_val = 'Поле должно содержать 9 цифр';
                    }
                    else {
                        err_val = '';
                        checkObj.parents('.row').removeClass('error');
                    }
                }
                else if (check_code == 'PROP__RASCH_SCHET' || check_code == 'PROP__KOR_SCHET') {
                    if (temp_val == '') {
                        err_val = 'Пожалуйста заполните это обязательное поле';
                    }
                    else if (temp_val.length != 20) {
                        err_val = 'Поле должно содержать 20 цифр';
                    }
                    else {
                        err_val = '';
                        checkObj.parents('.row').removeClass('error');
                    }
                }
                else if(check_code == 'PROP__PHONE'
                    && (temp_val == ''
                        || temp_val.toString().replace(/[\+()\- ]/g, '').length != 11
                    )
                ) {
                    err_val = 'Телефон должен быть в формате +7 (XXX) XXX-XX-XX';
                }
                else if(check_code != 'sms_code') {
                    if (temp_val == '') {
                        err_val = 'Пожалуйста заполните это обязательное поле';
                    }
                    else {
                        err_val = '';
                        checkObj.parents('.row').removeClass('error');
                    }
                }

                if (err_val != '') {
                    found_err = true;
                    if (err_scroll_top == 0) {
                        err_scroll_top = checkObj.offset().top - 100;
                    }
                    console.log('num: ' + checkObj.parents('.row').find('.row_err').length);
                    if (checkObj.parents('.row').find('.row_err').length > 0) {
                        checkObj.parents('.row').addClass('error').find('.row_err').text(err_val);
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
                        cObj2.parents('.row').addClass('error').find('.row_err').text(err_val);
                    }
                    else {
                        cObj2.after('<div class="row_err">' + err_val + '</div>');
                        cObj2.parents('.row').addClass('error');
                    }
                }
            }
        });

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
                // err_val = 'Прикрепите файл';
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

        //проверка отметки согласия для организатора
        checkObj = $('input[name="AUTH_REG_CONFIM_BY_AGENT"]');
        if(!found_err
            && checkObj.length == 1
            && checkObj.prop('checked') !== true
        ){
            alert('Вы не отметили подтверждение, что предоставление персональных данных на третьих лиц производится с их согласия');
            found_err = true;
        }

        if(found_err || $(this).hasClass('inactive'))
        {
            e.preventDefault();
            if(err_scroll_top != 0)
            {
                window.scrollTo(0, err_scroll_top);
            }
        }else{
            //проверка подтверждения смс по телефону
            var phoneArea = $(this).find('.row.phone_sms');
            if(phoneArea.hasClass('changed')
                && !phoneArea.hasClass('check_success')
            ){
                sendPhoneButtonEvent(phoneArea, true);
                e.preventDefault();
            }
        }
    });

    $('.demo_form input[type="text"][name="PROP__INN"], .demo_form input[type="text"][name="PROP__BIK"], .demo_form input[type="text"][name="PROP__RASCH_SCHET"], .demo_form input[type="text"][name="PROP__KOR_SCHET"]').on('keyup', function(){
        checkMask($(this), 'pos_int_empty');
    });

    $('form[name="profile_form"] input[type="password"], form[name="profile_form"] input[type="text"], form[name="profile_form"] input[type="file"], form[name="profile_form"] select, form[name="profile_form"] textarea').on('change', function(e){
        var error_val = '';
        checkObj = $(this);
        var name = checkObj.attr('name');
        var val = checkObj.val();
        var len = val.toString().length;

        if (name == 'PROP__INN') {
            var form = $(this).parents('form');
            if (len != 10 && len != 12) {
                var error_val = 'Номер должен состоять из 10 или 12 цифр';
            }
            else {
                var user_type = form.find('input[name="TYPE"]').val();
                var nds = form.find('input[name="PROP__NDS"]').val();

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
                var error_val = 'Поле должно содержать 9 цифр';
            }
        }
        else if (name == 'PROP__RASCH_SCHET' || name == 'PROP__KOR_SCHET') {
            if (len != 20) {
                var error_val = 'Поле должно содержать 20 цифр';
            }
        }
        else if (name == 'PROP__PHONE') {
            var n = val.match( /\d/g );
            if (val == '' || (n.length != 11)&&(n.length != 0)) {
                error_val = 'Телефон должен быть в формате +7 (XXX) XXX-XX-XX';
            }
            else if(val != ''){
                if(val == $(this).attr('data-val')){
                    $('.reg_form.demo_form.active .row.phone_sms').removeClass('changed');
                }else{
                    $('.reg_form.demo_form.active .row.phone_sms').addClass('changed');
                }
            }

            //убираем вывод ошибки в блоке
            $('.reg_form.demo_form.active .row.phone_sms .row_err').each(function(){ $(this).text(''); });
            $('.reg_form.demo_form.active .row.phone_sms.error').removeClass('error big_err');
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
            if (err_obj.length == 1) {
                err_obj.removeClass('error');
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
                    $('.reg_form.demo_form.active .row.phone_sms .row_err').each(function(){ $(this).text(''); });
                    var phoneObj = $(this).siblings('input[name="PROP__PHONE"]');
                    if(phoneObj.siblings('.row_err').length > 0){
                        phoneObj.siblings('.row_err').text('Повторный запрос можно будет направить не раньше чем через минуту с момента прошлой попытки');
                    }else{
                        phoneObj.parent().append('<div class="row_err">Повторный запрос можно будет направить не раньше чем через минуту с момента прошлой попытки</div>');
                    }
                    phoneObj.parents('.row.phone_sms').addClass('error big_err');
                }
            }
        }
    });

    //отправка проверки подтверждения кода, полученного по смс
    $('.phone_sms .submit_sms_button').on('click', function(){
        var formObj = $('.reg_form.demo_form.active');
        var phoneObj = formObj.find('input[name="PROP__PHONE"]');
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
    var formObj = $('.reg_form.demo_form.active');
    var phoneObj = formObj.find('input[name="PROP__PHONE"]');
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
            phone: phoneObj.val()
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
                u_type: $(argObj).parents('form').find('input[type="hidden"][name="TYPE"]').val()
            }, function(mes){
                clearInterval(inter_id);
                if (mes == 1 || mes == 'null') {
                    alert('Проверьте корректность введенного значения ИНН.');
                }
                else if (mes == 2) {
                    alert('Данный ИНН уже зарегистрирован в системе');
                }
                else {
                    $(argObj).attr('value', $(argObj).attr('data-val'));

                    var res_val = $.parseJSON(mes);

                    if (typeof res_val.IP != 'undefined') {
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
                        if(typeof res_val.UL.heads != 'undefined'
                            && typeof res_val.UL.heads[0] != 'undefined'
                            && typeof res_val.UL.heads[0].fio != 'undefined'
                        ){
                            wObj.val(res_val.UL.heads[0].fio);
                        }else if(typeof res_val.UL.history.heads != 'undefined'
                            && typeof res_val.UL.history.heads[0] != 'undefined'
                            && typeof res_val.UL.history.heads[0].fio != 'undefined')
                        {
                            wObj.val(res_val.UL.history.heads[0].fio);
                        }

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

function showAgentPolicy()
{
    $('.content-form.policy_page.public_form').toggleClass('active');
    $('body').toggleClass('disable_scroll');
}