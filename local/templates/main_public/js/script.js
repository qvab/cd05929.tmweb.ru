var touch_events = 0;
var temp_hash = '';
var menu_save_scroll = 0;
var gReady = false, grecaptcha;
var device_type = 'c'; //тип устройства (c - настольные компьютеры, m - мобильные сенсорные устройства) проверяется на уровне CSS (через media hover и pointer) и на уровне javascript (наличие события ontouchstart)

$(document).ready(function(){
    if(isMobileDevice()){
        $('body').addClass('mobile_device');
    }

    //start selects, radio & checkbox customization
    makeCustomForms();
    //fix select width when mobile orientation change
    window.addEventListener("orientationchange", function() {
        setTimeout(function(){
            var checkObj = $('.row .row_val input[type="text"]:first');
            var check_val = (checkObj.length == 1 ? checkObj.width() + 30 : 0)
            $('span.agro_select2_container').each(function(ind, cObj){
                if(check_val > 0)
                {
                    $(cObj).css('width', check_val + 'px');
                }
                else
                {
                    $(cObj).css('width', (parseInt($(this).parent().width()) - 20) + 'px');
                }
            });
        }, 50);
    }, false);

    //customize decs with space separators
    $('.decs_separators').each(function(ind, cObj){
        $(cObj).text(number_format($(cObj).text(), 0, '.', ' '));
    });

    //Обработка прикрепления файла
    $('.content input[type="file"]').each(function(ind, cObj){
        if($(cObj).parents('.list_page_rows.deals').length == 0)
        {
            $(cObj).before('<div class="input_before_file"><span class="val">' + (typeof $(cObj).attr('data-text') == 'undefined' ? 'Прикрепить файл' : $(cObj).attr('data-text')) + '</span><div class="ico"></div></div>');
        }
    });
    $('.content').on('change', 'input[type="file"]', function(){
        if($(this).parents('.list_page_rows.deals').length == 0)
        {
            if($(this).val() != '')
            {
                $(this).siblings('.input_before_file').find('.val').text($(this).val().toString().replace(/.*\\/g, ''));
            }
        }
    });

    //mobile menu on click
    $('#header .mobile_menu_ico').on('click', function(){
        var wObj = $(this).parents('body');
        if(wObj.hasClass('menu_active'))
        {
            wObj.removeClass('menu_active');
            $(window).scrollTop(menu_save_scroll);
        }
        else
        {
            menu_save_scroll = $(window).scrollTop();
            wObj.addClass('menu_active');
        }
    });

    //submenu logic
    $('.sub_menu .item').on('click', function(){
        if(!$(this).hasClass('active') && stop_menu_animation == 0)
        {
            stop_menu_animation = 1;
            var new_id = $(this).attr('data-id');
            var new_num = parseInt(new_id.toString().replace('s', ''));
            var old_num = parseInt($('.sub_menu .item.active').attr('data-id').toString().replace('s', ''));
            var cur_action = $(this).attr('data-action');
            if(new_num % 2 == 0)
            {
                $('html, body').addClass('even');
            }
            else
            {
                $('html, body').removeClass('even');
            }

            $('.sub_menu .item.active').removeClass('active');
            $(this).addClass('active');
            $('.public_slide_text.active').removeClass('active');
            $('.public_slide_text[data-id="' + new_id + '"]').addClass('active');

            $('#public_slider_item .item[data-id="' + new_id + '"]').css('right', (new_num > old_num ? '-100%' : '100%')).addClass('prepared').animate({
                right: '0'
            }, 400, function(){
                $('#public_slider_item .item.active').removeClass('active');
                $('#public_slider_item .item[data-id="' + new_id + '"]').addClass('active').removeClass('prepared');
                stop_menu_animation = 0;

                //set action if needs
                if(typeof cur_action != 'undefined' && cur_action != '')
                {
                    document.location.hash = 'action=' + cur_action;
                    temp_hash = 'action=' + cur_action;//save submenu hash
                }
            });
        }
    });

    //if page have action set
    var page_action_str = document.location.hash.toString();
    if(page_action_str.replace(/^\#action=/g,'').length != page_action_str.length)
    {//there is an action
        page_action_str = page_action_str.replace(/^\#action=/g,'');
        if(page_action_str === 'register_client'
            || page_action_str === 'register_farmer'
        ){
            page_action_str = 'register';
        }

        var temp_action_elem = $('[data-action="' + page_action_str + '"]');
        if(temp_action_elem.length >= 1)
        {
            temp_action_elem.first().trigger('click');
        }
        else
        {
            var temp_show_element = $('div.public_form[data-actionobj="' + page_action_str + '"]');
            if(temp_show_element.length == 1)
            {//if exists form to show
                $('.public_form.active, #left_side .href_row .active').removeClass('active');
                temp_show_element.addClass('active');
            }
            else
            {
                //console.log('not found show element');
            }
        }
    }
    else
    {
        //console.log('not found action');
    }

    //add fields masks
    $('#page_wrapper input[type="text"][data-checkval="y"]').on('focus', function(){
        if((
                $(this).attr('data-checktype') == 'phone'
                ||
                $(this).attr('data-checktype') == 'email'
                ||
                $(this).attr('data-checktype') == 'car_number'
            )
            && typeof $(this).attr('data-stabval') == 'undefined'
            && $(this).val() != ''
        )
        {
            $(this).attr('data-stabval', $(this).val());
        }
    });
    $('#page_wrapper input[type="text"][data-checkval="y"]').on('keyup', function(){
        checkMask($(this), $(this).attr('data-checktype'));
    });

    $('.phone_msk').inputmask('+7 (999) 999-99-99');

    // Аккардион
    $('.accordions-list-block').each(function(){
        var wrap = $(this);

        // Можно сюда потом накидать еще сворачивания других блоков при открытии нового

        // Подвешиваем on, что бы была возможность динамического добавления разделов
        wrap.on('click', '.accordion-title', function(){
            $(this).closest('.accordion-block').toggleClass('active');
        });
    });


    // Слайдер
    $('.owl-carousel').each(function(){
        var owl = $(this);

        owl.owlCarousel({
            items: owl.attr('owl-items') || 1
        });

    });
});

//show registration form
function showPublicReg(button)
{
    var button = $(button);

    $('.public_form.active, #left_side .href_row .active').removeClass('active');
    $('#public_reg_form').addClass('active');
    button.addClass('active');

    // Переключаемся на нужную форму, если это нужно
    var type = button.attr('type');

    if(typeof button.attr('data-action') !== 'undefined') {
        if (document.location.hash === '#action=register_client') {
            type = 'client';
        } else if(document.location.hash === '#action=register_farmer') {
            type = 'farmer';
        } else if(button.attr('data-action') === 'register') {
            type = 'farmer';
            document.location.hash = 'action=register_farmer';
        } else {
            document.location.hash = 'action=' + button.attr('data-action');
        }
    }

    if(typeof type !== 'undefined') {
        $('.reg_form_control_tabs .form_control_tab[data-val="' + type + '"]').trigger('click');
    }


    // Активируем первую капчу
    var captcha = $('.reg_form.active .g-recaptcha.g-relazy', '#public_reg_form');

    if(grecaptcha && gReady) {
        captcha.trigger('gRender');
    } else {
        $('.reg_form_control_tabs .form_control_tab[data-val="' + type + '"]').trigger('click');
        var setIntervalId = setInterval(function(){
            if(!grecaptcha || !gReady)
                return;
            clearInterval(setIntervalId);
            captcha.trigger('gRender');

            if(typeof type !== 'undefined') {
                $('.reg_form_control_tabs .form_control_tab[data-val="' + type + '"]').trigger('click');
            }
        }, 250);
    }


    //remove mobile menu activity if need
    if($('body').hasClass('menu_active')) {
        $('body').removeClass('menu_active');
    }

    // Скроллим до верха
    $("html, body").stop().animate({scrollTop: 0}, 300);
}

//show help form
function showHelpForm(arg){
    $('.public_form.active, #left_side .href_row .active').removeClass('active');
    $('#public_response_form').addClass('active');
    $(arg).addClass('active');
    if(typeof $(arg).attr('data-action') != 'undefined' && $(arg).attr('data-action') != '')
    {
        document.location.hash = 'action=' + $(arg).attr('data-action');
    }

    //remove mobile menu activity if need
    if($('body').hasClass('menu_active'))
    {
        $('body').removeClass('menu_active');
    }

    // Скроллим до верха
    $("html, body").stop().animate({scrollTop: 0}, 300);

    // Активируем первую капчу
    var captcha = $('.g-recaptcha.g-relazy', '#public_response_form');
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

//show registration form
function showPublicAuth(arg)
{
    $('.public_form.active, #left_side .href_row .active').removeClass('active');
    $('.login-form.public_form').addClass('active');
    $(arg).addClass('active');
    if(typeof $(arg).attr('data-action') != 'undefined' && $(arg).attr('data-action') != '')
    {
        document.location.hash = 'action=' + $(arg).attr('data-action');
    }

    //remove mobile menu activity if need
    if($('body').hasClass('menu_active'))
    {
        $('body').removeClass('menu_active');
    }
}

//show other public form
function showPublicOtherForm(arg_addr, arg_action)
{
    $('.public_form.active, #left_side .href_row .active').removeClass('active');
    $(arg_addr + '.public_form').addClass('active');
    if(typeof $(arg_action).attr('data-action') != 'undefined' && $(arg_action).attr('data-action') != '')
    {
        document.location.hash = 'action=' + $(arg_action).attr('data-action');
    }

    //remove mobile menu activity if need
    if($('body').hasClass('menu_active'))
    {
        $('body').removeClass('menu_active');
    }

    // Скроллим до верха
    $("html, body").stop().animate({scrollTop: 0}, 300);
}

//close public form
function closePublicForm(cObj)
{
    $('.public_form.active, #left_side .href_row .active').removeClass('active');
    $('#public_content').addClass('active');

    document.location.hash = temp_hash;
}

function onRecSubmit(token) {
    //check action
    var c_action = document.location.hash.toString();
    var f_obj = '';
    if(c_action == '#action=help')
    {
        f_obj = $('.response-form');
    }
    else if(c_action == '#action=register'
        || c_action == '#action=register_client'
        || c_action == '#action=register_farmer'
    ){
        f_obj = $('.reg_form.active form');
    }
    else if(c_action == '#action=become_agrohelper')
    {
        f_obj = $('.become_form');
    }else if(c_action == '#action=transport_connection'){
        f_obj = $('.tk_request_form');
    }

    /**
     * Проверяем что это может быть страница с формой
     * TODO::Возможно переделать более правильно
     */
    if((f_obj.length == 0) && (location.pathname == '/becomeagrohelper/')) {
        f_obj = $('.become_form');
    }

    if(f_obj.length == 1)
    {
        f_obj.find('.g-recaptcha-response').val(token);
        f_obj.addClass('g_passed').submit();
    }
}

// функция обработки загрузки гугл рекапчи
function recaptchaCallback(){
    gReady = true;

    $(function(){
        var mainKey = '6LeDzmAUAAAAABZV4UNfOq9SzwqDqtWJXvtDPb5G';
        $('.g-recaptcha').each(function(){
            var box = $(this),
                fnRender = function(){
                    if(box.data('gRender')) return;
                    box.data('gRender', true);

                    var recaptcha = grecaptcha.render(box[0], {
                        'sitekey' : box.attr('data-sitekey') || mainKey
                    });
                    box.data('recaptcha', recaptcha);
                };

            if(box.hasClass('g-relazy')) {
                box.on('gRender', function(){
                    //console.log('gRender TR');
                    fnRender();
                });
            } else {
                fnRender();
            }
        });
    });
};

//форма проверки телефона по смс
function showPhonePopup(phone_area){
    var phoneForm = $('#popup_phone_num');
    window.timeout = null;
    if(phoneForm.length === 0){
        $('#page_body').append('<div id="popup_phone_num">' +
            '<div class="popup_logo"></div><div class="popup_close" onclick="closeSmsPopup(this);"></div>' +
            '<div class="popup_header">Подтверждение телефона</div>' +
            '<div class="text">На ваш номер отправлено сообщение с кодом подтверждения телефона</div>' +
            '<div class="phone_value"></div>' +
            '<input type="text" name="sms_code" oninput="if(timeout !== null){clearTimeout(timeout);} timeout = setTimeout(submitPhoneButtonEvent, 1000, this, true);" placeholder="Введите код" />' +
            '<div class="send_button popup_repeat_send" data-again="Отправить повторно" data-first="Отправить смс" onclick="sendPhoneButtonEvent(this, true);">Отправить смс повторно</div>' +
           // '<div class="submit_sms_button" onclick="submitPhoneButtonEvent(this, true);">Подтвердить</div>' +
            '<div class="clear"></div>' +
            '</div>');

        phoneForm = $('#popup_phone_num');

        //проверка на наличие корректно заполненной почты (даём пользователю возможность зарегистрироваться по email если заполнены и телефон и email)
        /*var email_input = $('.reg_form.active input[type="text"][name="USER_EMAIL"]');
        if(email_input.length == 1
            && email_input.val() != ''
            && checkEmailRfc(email_input.val())
        ){
            phoneForm.append('<div class="email_register_button" onclick="registerOverEmail();">Зарегистрироваться через email</div><div class="clear"></div>');
        }*/
    }

    var phoneVal = phoneForm.find('.phone_value');
    if(phoneVal.length == 1) {
        phoneVal.text(phone_area.find('input[name=PROP__PHONE]').val());
        phoneForm.addClass('active');
        $('#back_shad').addClass('active');
        phoneForm.addClass('active');
    }
}

//форма проверки телефона по смс для восстановления пароля
function showPhoneForgotPopup(phone_val){
    var phoneForm = $('#popup_phone_num');
    window.timeout = null;
    if(phoneForm.length === 0){
        $('#page_body').append('<div id="popup_phone_num">' +
            '<div class="popup_logo"></div><div class="popup_close" onclick="closeSmsPopup(this);"></div>' +
            '<div class="popup_header">Подтверждение телефона</div>' +
            '<div class="text">На ваш номер отправлено сообщение с кодом подтверждения телефона</div>' +
            '<div class="phone_value">' + phone_val + '</div>' +
            '<input type="text" oninput="if(timeout !== null){clearTimeout(timeout);}  timeout = setTimeout(submitPhoneForgotEvent, 1000, this);" name="sms_code" placeholder="Введите код" />' +
            '<div class="send_button popup_repeat_send" data-again="Отправить повторно" data-first="Отправить смс" onclick="sendPhoneForgotEvent();">Отправить смс повторно</div>' +
            //'<div class="submit_sms_button" onclick="submitPhoneForgotEvent(this);">Подтвердить</div>' +
            '<div class="clear"></div>' +
            '</div>');

        phoneForm = $('#popup_phone_num');
    }

    $('#back_shad').addClass('active');
    phoneForm.addClass('active');
}

//используется для регистрации посредством email при заполненных email и телефоне
function registerOverEmail(){
    var wForm = $('.reg_form.active:first form');
    if(wForm.length == 1){
        var email_input = wForm.find('input[type="text"][name="USER_EMAIL"]');
        if(email_input.length == 1
            && email_input.val() != ''
            && checkEmailRfc(email_input.val())
        ){
            var email_flag = wForm.find('input[type="hidden"][name="register_email"]');
            if(email_flag.length == 0){
                wForm.prepend('<input type="hidden" name="register_email" value="y" />');
            }
            wForm.submit();
        }
    }
}

//проверка является ли значение в поле почтой или телефоном
function checkInputPhoneEmail(stringArg){
    var result = '';

    if(checkEmailRfc(stringArg)){
        //почта
        result = 'email';
    }else{
        //проверка телефон (10 или 11 цифр, остальные символы не проверяем)
        var phone_check_val = stringArg.replace(/[^0-9]/g, '');
        if(phone_check_val.length == 10 ||
            phone_check_val.length == 11
            && (phone_check_val.substr(0, 1) == '8'
                || phone_check_val.substr(0, 1) == '7'
            )
        ){
            result = 'phone';
        }
    }

    return result;
}

/*
* Определяет с мобильного ли устройства пришел пользователь (проверяется на уровне CSS (через media hover и pointer) и на уровне javascript (наличие события ontouchstart))
* проверка идет через объект с id mobile_check, добавленный в конец body в footer.php шаблона сайта
* @return Boolean - возвращает true, если устройство определено как мобильное
* */
function isMobileDevice() {
    return (typeof device_type != 'undefined' && device_type === 'm');
}