var temp_hash = '';
var iScrollToMenu = 0;
var isStuck = isScrollDown = isScrollStuck = false;
var device_type = 'c'; //тип устройства (c - настольные компьютеры, m - мобильные сенсорные устройства) проверяется на уровне CSS (через media hover и pointer) и на уровне javascript (наличие события ontouchstart)

function  getPageSize(){
    var xScroll, yScroll;

    if (window.innerHeight && window.scrollMaxY) {
        xScroll = document.body.scrollWidth;
        yScroll = window.innerHeight + window.scrollMaxY;
    } else if (document.body.scrollHeight > document.body.offsetHeight){ // all but Explorer Mac
        xScroll = document.body.scrollWidth;
        yScroll = document.body.scrollHeight;
    } else if (document.documentElement && document.documentElement.scrollHeight > document.documentElement.offsetHeight){ // Explorer 6 strict mode
        xScroll = document.documentElement.scrollWidth;
        yScroll = document.documentElement.scrollHeight;
    } else { // Explorer Mac...would also work in Mozilla and Safari
        xScroll = document.body.offsetWidth;
        yScroll = document.body.offsetHeight;
    }

    var windowWidth, windowHeight;
    if (self.innerHeight) { // all except Explorer
        windowWidth = self.innerWidth;
        windowHeight = self.innerHeight;
    } else if (document.documentElement && document.documentElement.clientHeight) { // Explorer 6 Strict Mode
        windowWidth = document.documentElement.clientWidth;
        windowHeight = document.documentElement.clientHeight;
    } else if (document.body) { // other Explorers
        windowWidth = document.body.clientWidth;
        windowHeight = document.body.clientHeight;
    }

    // for small pages with total height less then height of the viewport
    if(yScroll < windowHeight){
        pageHeight = windowHeight;
    } else {
        pageHeight = yScroll;
    }

    // for small pages with total width less then width of the viewport
    if(xScroll < windowWidth){
        pageWidth = windowWidth;
    } else {
        pageWidth = xScroll;
    }

    return {'page':{'width':pageWidth,'height':pageHeight},'window':{'width':windowWidth,'height':windowHeight}}
}
function menuInit() {

    var p_size = getPageSize();
    if(p_size.window.width>980){
        var coef = p_size.window.height -$('#header_title').outerHeight()-$('div.main_menu').outerHeight()-$('div.bot_menu').outerHeight();
        if(coef<70){
            $('div.bot_menu').addClass('to_top');

            var header_h = $('#header').outerHeight();
            var body_h = $('#page_body').outerHeight();
            var bitrix_panel_h = 0;
            var elements_height = $('#header .main_menu .item_area').length * 65; //левое меню плюс шапка
            if($('#bitrix_panel_wrapper').length>0){
                bitrix_panel_h = $('#bitrix_panel_wrapper').outerHeight();
            }
            if(($('#bitrix_panel_wrapper').length>0)&&(bitrix_panel_h>0)){
                body_h = body_h+bitrix_panel_h+60;
            }
            var all_h = Math.max(header_h,body_h, elements_height) + 150;
            header_h = all_h+60;
            $('#header').css('position','absolute');
            $('#header').css('max-height','100%');
            $('#page_body').css('min-height',all_h+'px');

        }else if(coef>80){
            $('div.bot_menu').removeClass('to_top');
            $('#page_body').css('min-height','none');
            $('#page_body').css('height','auto');
            $('#header').css('position','fixed');
            $('#header').css('max-height','100vh');
        }
    }
}
function fixMenuOnSubmenu(height, isClose , ignore) {
    var p_size = getPageSize();
    var isTop = $('div.bot_menu').hasClass('to_top');
    ignore = ignore || false;
    if(p_size.window.width>980){
        if(!ignore) {
            if (isClose) {

                $('#page_body').css('min-height', ($('#page_body').outerHeight() - height - 120) + 'px');
            } else {
                $('#page_body').css('min-height', ($('#page_body').outerHeight() + height) + 'px');
            }
        }
        if(iScrollToMenu > 0 && !ignore){
            isScrollStuck = true;
        } else {
            isScrollStuck = false;
        }
        var coef = p_size.window.height - $('#header_title').outerHeight(true) - $('div.main_menu').outerHeight(true) - $('div.bot_menu').outerHeight(true);
        if(coef<70){
            $('div.bot_menu').addClass('to_top');

            if($(window).scrollTop() <= 50 ) {
                $('#header').css('position', 'absolute');
            }
            else {
                $('#header').css('position', 'absolute');
                if(isStuck && !isTop) {
                    iScrollToMenu = $(window).scrollTop();
                    $('#header').css('top', iScrollToMenu);
                }
            }

            $('#header').css('position','absolute');
            $('#header').css('max-height','100%');
        }else if(coef>80){
            
            if(!isScrollStuck){
                $('div.bot_menu').removeClass('to_top');
                iScrollToMenu = 0;
                isStuck = false;
                $('#page_body').css('min-height','none');
                $('#page_body').css('height','auto');
                $('#header').css('position','fixed');
                $('#header').css('max-height','100vh');
                $('#header').css('top', iScrollToMenu);
            }
        }else {
            isStuck = false;
        }

    }
}

function isMobileMode() {
    var result = false;
    var clWidth = $(window).width();
    if(document.documentElement.clientHeight < document.documentElement.scrollHeight){
        clWidth = $(window).width()+20;
    }
    if(clWidth<980){
        result = true;
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

$(document).ready(function(){
    if(isMobileDevice()){
        $('body').addClass('mobile_device');
    }

    var $window = $(window),
        previousDimensions = {
            width: $window.width(),
            height: $window.height()
        };
    copySpecialLink();

	$('.main_menu').on('click', '.sub-link', function(e){
		e.preventDefault();
		var height = 0,
            isClose = false;
		if($(this).closest('.item_area').hasClass('active')){
            height = $(this).closest('.submenu').outerHeight(true);
            isClose = true;
        }
		$(this).closest('.item_area').toggleClass('active');

        if($(this).closest('.item_area').hasClass('active')){
            height = $(this).closest('.submenu').outerHeight(true);
        }
		//checkMenuHeight();
        isStuck = true;
        fixMenuOnSubmenu(height, isClose);
	});

    $(document).on('scroll', function () {
        if(isStuck) {
            if($(window).scrollTop() < iScrollToMenu){
                iScrollToMenu = $(window).scrollTop();
                $('#header').css('top', 0);
                $('#header').css('position', 'fixed');
                isScrollDown = false;
                if(isScrollStuck) {
                    fixMenuOnSubmenu(0,0, true);
                    isScrollStuck = false;
                }
            }else {
                if(!isScrollDown){
                    $('#header').css('top', iScrollToMenu);
                    $('#header').css('position', 'absolute');
                    isScrollDown = true;
                }

            }
        }
    });
	//checkMenuHeight();
    /*$window.resize(function(e) {
        var newDimensions = {
            width: $window.width(),
            height: $window.height()
        };
        var p_size = getPageSize();
        if(p_size.window.width>980){
            var coef = p_size.window.height -$('#header_title').outerHeight()-$('div.main_menu').outerHeight()-$('div.bot_menu').outerHeight();
            if(coef<70){
                $('div.bot_menu').addClass('to_top');

                var header_h = $('#header').outerHeight();
                console.log('header_h: ' + header_h);
                var body_h = $('#page_body').outerHeight();
                console.log('body_h: ' + body_h);
                var bitrix_panel_h = 0;
                if($('#bitrix_panel_wrapper').length>0){
                    bitrix_panel_h = $('#bitrix_panel_wrapper').outerHeight();
                }
                if(($('#bitrix_panel_wrapper').length>0)&&(bitrix_panel_h>0)){
                    body_h = body_h+bitrix_panel_h+60;
                }
                var all_h = Math.max(header_h,body_h);
                if (newDimensions.height > previousDimensions.height) {
                } else {
                    $('#page_body').css('height','100%');
                    $('#page_body').css('min-height',$('#page_body').outerHeight()+90+'px');
                }
                $('#header').css('position','absolute');
                $('#header').css('max-height','100%');

            }else if(coef>80){
                $('div.bot_menu').removeClass('to_top');
                $('#page_body').css('min-height','none');
                $('#page_body').css('height','auto');
                $('#header').css('position','fixed');
                $('#header').css('max-height','100vh');
            }
        }
        // Store the new dimensions
        previousDimensions = newDimensions;
    });*/

    menuInit();

    //start selects, radio & checkbox customization
    makeCustomForms();
    //fix select width when mobile orientation change
    window.addEventListener("orientationchange", function() {
        setTimeout(function(){
            var checkObj = $('.select2-hidden-accessible:first');
            var check_val = (checkObj.length == 1 ? checkObj.width() : 0);

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

    //if page have action set
    var page_action_str = document.location.hash.toString();
    if(page_action_str.replace(/^\#action=/g,'').length != page_action_str.length)
    {//there is an action
        var selector = page_action_str.replace(/^\#action=/g,'');
        if(selector == 'policy'){
            selector = 'policy_demo';
        }
        if($('.'+selector+'_page').length == 1)
        {
            showPublicOtherForm('.'+selector+'_page', this);
        }
    }
    else
    {
        //console.log('not found action');
    }

    //customize decs with space separators
    $('.decs_separators').each(function(ind, cObj){

        //сохраняем знак '+', если нужно
        var temp_sign = '';
        if($(cObj).text().length > 0){
            temp_sign = $(cObj).text().substr(0, 1);
        }

        var result = number_format($(cObj).text(), 0, '.', ' ');
        if(result.length > 0
            && temp_sign === '+'
        ){
            result = '+' + result;
        }

        $(cObj).text(result);
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

    //add admin clas for page
    if($('#bitrix_panel_wrapper div:first').length > 0){
        $('body').addClass('with_bitrix_panel');
    }

    $('.connected_users_list .item .unlink').on('click', function(){
        if($(this).hasClass('disabled'))
        {
            alert('У агропроизводителя есть незавершенные сделки. Отвязаться от агропроизводителя можно только при отсутствии открытых сделок.');
        }
        else
        {
            parnter_unlink_decision_status = confirm("Вы уверены, что хотите отвязаться от текущего агропроизводителя?");
            if(parnter_unlink_decision_status)
            {//go to unlink page
                document.location.href = '/personal/?unlink_client=' + $(this).attr('data-id');
            }
        }
    });

    if(0 && $('#id_check').length == 1)
    {
        $(window).resize(function(){
            $('#id_check').text($(window).width() + ' : ' + $(window).height());
        });
    }

    //add fields masks
    //integer positive to registration
    $('#page_wrapper input[type="text"][data-checkval="y"]').on('keyup', function(){
        checkMask($(this), $(this).attr('data-checktype'));
    });

    $('.phone_msk').inputmask('+7 (999) 999-99-99');

    //ставим куки защиты повторной отправки смс в течение минуты
    setCookie('lastSendMessage', $.now() - 60000);


    /**
     * кастомим раскрывашку
     */
    $('.options_form').on('click', '.option-name', function (e) {
        e.preventDefault();
        e.stopPropagation();

        var $this = $(this);
        $this.closest('.slide-description').toggleClass('active');
    });
});

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
}

//close public form
function closePublicForm(cObj)
{
    $('.public_form.active, #left_side .href_row .active').removeClass('active');
    $('#public_content').addClass('active');
    $('body').toggleClass('disable_scroll');
    document.location.hash = temp_hash;
}

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
            '<input type="text" oninput="if(timeout !== null){clearTimeout(timeout);}  timeout = setTimeout(submitPhoneButtonEvent, 1000, this, true);" name="sms_code" placeholder="Введите код" />' +
            '<div class="send_button popup_repeat_send" data-again="Отправить повторно" data-first="Отправить смс" onclick="sendPhoneButtonEvent(this, true);">Отправить смс повторно</div>' +
            //'<div class="submit_sms_button" onclick="submitPhoneButtonEvent(this, true);">Подтвердить</div>' +
            '<div class="clear"></div>' +
            '</div>' +
            '</div>');

        phoneForm = $('#popup_phone_num');
    }

    phoneForm.find('.phone_value').text(phone_area.find('input[name=PROP__PHONE]').val());
    phoneForm.addClass('active');
    $('#back_shad').addClass('active');
    phoneForm.addClass('active');
}

//отправка проверочного кода в смс на телефон
function sendPhoneButtonEvent(objVal, isPopup){
    if($(objVal).parents('.row.phone_sms.check_success').length == 0){
        //если телефон еще не был подтвержден, то запускаем алгоритм проверки
        var curLastTime = getCookie('lastSendMessage');

        if(getCookie('lastSendMessage') != null){

            var formObj = $(objVal).parents('form');
            if(formObj.length === 0){
                formObj = $('form[name="profile_form"]');
            }
            var phoneObj = formObj.find('input[name="PROP__PHONE"]');

            if(isPopup){
                $('#popup_phone_num .error_text').remove();
            }
            if($.now() - curLastTime > 60000){

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
                                var offset = $('input[name="PROP__PHONE"]').offset();
                                $(window).scrollTop(offset.top - 50);
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
                    formObj.find('.row_err').each(function(){ $(objVal).text(''); });
                    if(phoneObj.siblings('.row_err').length == 0){
                        phoneObj.parent().append('<div class="row_err">Повторный запрос можно будет направить не раньше чем через минуту с момента прошлой попытки</div>');
                    }else{
                        phoneObj.siblings('.row_err').text('Повторный запрос можно будет направить не раньше чем через минуту с момента прошлой попытки');
                    }
                    phoneObj.parents('.row.phone_sms').addClass('error big_err');
                }else{
                    if(!$(objVal).hasClass('popup_repeat_send')) {
                        showPhonePopup(phoneObj.parents('.row.phone_sms'));
                    }
                    $('#popup_phone_num input[name="sms_code"]').before('<div class="error_text">Повторный запрос можно будет направить не раньше чем через минуту с момента прошлой попытки</div>');
                }
            }
        }
    }
}

//отправка кода на проверку
function submitPhoneButtonEvent(objVal, isPopup){


    var formObj = $('form[name="profile_form"]');
    if(formObj.length == 0){
        formObj = $('form[name="profile_form"]');
    }
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

//форма обратной связи для счётчика принятий
function showCounterRequestFeedbackForm(argEmail, lim_param, price_val){
    var backshadowObj = $('#back_shad').addClass('active');
    var form_html = '<div id="popup_phone_num" class="active lim" data-lim="' +lim_param + '" data-price="' + price_val + '"><div class="popup_logo"></div><div class="popup_close" onclick="closeSmsPopup(this);"></div><div class="popup_header">Заявка на увеличение лимита</div><div class="text feedback_data"><div class="row"><div class="row_label">Email:<div class="restr_val"></div></div><input ' + (argEmail != '' ? 'readonly="readonly"' : '') + ' type="text" name="feedback_email" value="' + argEmail + '" />' + (argEmail != '' ? '<input type="hidden" name="feedback_user_email" value="y" />' : '') + '</div><div class="row"><div class="row_label">Тип плательщика:<div class="restr_val"></div></div><select name="user_type" onchange="changeCounterLimitType(this);" ><option value="0">Не выбран</option><option value="1">Физ. лицо</option><option value="2">Юр. лицо / ИП</option></select>' + (argEmail != '' ? '<input type="hidden" name="feedback_user_email" value="y" />' : '') + '</div><div class="row"><div class="row_label">Количество принятий (min. ' + lim_param + '):</div><input type="text" name="opener_count" value="' + lim_param + '" onchange="checkCounterLimitMinValue(this);" /></div><div class="row"><div class="row_label">Стоимость, руб.:</div><input readonly="readonly" type="text" name="opener_sum" value="' + lim_param * price_val + '" /></div><input class="submit-btn" type="button" onclick="sendCounterRequestFeedbackForm(this);" value="Отправить" /></div><div class="clear"></div></div>';
    $('#page_body').append(form_html);
    $('#popup_phone_num select').select2(
        {
            minimumResultsForSearch: Infinity,
            templateResult: formatAgroState
        }
    ).siblings('.select2').addClass('agro_select2_container');
}

//форма обратной связи для ограничения запросов
function showRequestLimitsFeedbackForm(argEmail, lim_param, price_val){
    var backshadowObj = $('#back_shad').addClass('active');
    var form_html = '<div id="popup_phone_num" class="active req_lim" data-lim="' +lim_param + '" data-price="' + price_val + '"><div class="popup_logo"></div><div class="popup_close" onclick="closeSmsPopup(this);"></div><div class="popup_header">Заявка на увеличение лимита<br/>для запросов</div><div class="text feedback_data"><div class="row"><div class="row_label">Email:<div class="restr_val"></div></div><input ' + (argEmail != '' ? 'readonly="readonly"' : '') + ' type="text" name="feedback_email" value="' + argEmail + '" />' + (argEmail != '' ? '<input type="hidden" name="feedback_user_email" value="y" />' : '') + '</div><div class="row"><div class="row_label">Тип плательщика:<div class="restr_val"></div></div><select name="user_type" onchange="changeCounterLimitType(this);" ><option value="0">Не выбран</option><option value="1">Физ. лицо</option><option value="2">Юр. лицо / ИП</option></select>' + (argEmail != '' ? '<input type="hidden" name="feedback_user_email" value="y" />' : '') + '</div><div class="row"><div class="row_label">Количество запросов (min. ' + lim_param + '):</div><input type="text" name="opener_count" value="' + lim_param + '" onchange="checkCounterLimitMinValue(this);" /></div><div class="row"><div class="row_label">Стоимость, руб.:</div><input readonly="readonly" type="text" name="opener_sum" value="' + lim_param * price_val + '" /></div><input class="submit-btn" type="button" onclick="sendRequestLimitsFeedbackForm(this);" value="Отправить" /></div><div class="clear"></div></div>';
    $('#page_body').append(form_html);
    $('#popup_phone_num select').select2(
        {
            minimumResultsForSearch: Infinity,
            templateResult: formatAgroState
        }
    ).siblings('.select2').addClass('agro_select2_container');
}

//форма обратной связи для ограничения товаров
function showOfferLimitsFeedbackForm(argEmail, lim_param, price_val, month_val){
    var backshadowObj = $('#back_shad').addClass('active');
    var form_html = '<div id="popup_phone_num" class="active off_lim" data-lim="' +lim_param + '" data-price="' + price_val + '" data-month="' + month_val + '"><div class="popup_logo"></div><div class="popup_close" onclick="closeSmsPopup(this);"></div><div class="popup_header">Заявка на увеличение лимита<br/>для товаров</div><div class="text feedback_data"><div class="row"><div class="row_label">Email:<div class="restr_val"></div></div><input ' + (argEmail != '' ? 'readonly="readonly"' : '') + ' type="text" name="feedback_email" value="' + argEmail + '" />' + (argEmail != '' ? '<input type="hidden" name="feedback_user_email" value="y" />' : '') + '</div><div class="row"><div class="row_label">Тип плательщика:<div class="restr_val"></div></div><select name="user_type" onchange="changeCounterLimitType(this);" ><option value="0">Не выбран</option><option value="1">Физ. лицо</option><option value="2">Юр. лицо / ИП</option></select>' + (argEmail != '' ? '<input type="hidden" name="feedback_user_email" value="y" />' : '') + '</div><div class="row"><div class="row_label">Количество товаров (min. ' + lim_param + '):</div><input type="text" name="opener_count" value="' + lim_param + '" onchange="checkOfferLimitMinValue(this);" /></div><div class="row"><div class="row_label">Количество месяцев (min. ' + month_val + '):</div><input type="text" name="month_count" value="' + month_val + '" onchange="checkOfferLimitMinValue(this);" /></div><div class="row"><div class="row_label">Стоимость, руб.:</div><input readonly="readonly" type="text" name="opener_sum" value="' + lim_param * price_val * month_val + '" /></div><input class="submit-btn" type="button" onclick="sendOfferLimitsFeedbackForm(this);" value="Отправить" /></div><div class="clear"></div></div>';
    $('#page_body').append(form_html);
    $('#popup_phone_num select').select2(
        {
            minimumResultsForSearch: Infinity,
            templateResult: formatAgroState
        }
    ).siblings('.select2').addClass('agro_select2_container');
}

/**
 * форма для вставки текста с ссылкой
 * @param title     - заголовок окна
 * @param haveEmail - флаг наличия email у пользователя
 * @param text      - текст сообщения
 * @param vol_val   - объем (для поставщиков)
 * @param uid       - ID пользователя
 * @param mode      - режим работы (farmer|client)
 */
function showTextLinkFeedbackForm(title, haveEmail, text, vol_val, uid, mode){
    var backshadowObj = $('#back_shad').addClass('active');
    var email_btn = '';
    if(haveEmail == '1'){
        email_btn = '<input class="submit-btn b_left" type="button" onclick="sendTextLinkToEmail(this,'+uid+',\''+mode+'\');" value="Отправить по email" />';
    }
    var form_html = '<div id="popup_phone_num" class="link_text_w active">'+
        '<div class="popup_logo"></div>' +
        '<div class="popup_close" onclick="closeSmsPopup(this);"></div>'+
        '<div class="popup_header">'+title+'</div>' +
        '<div class="row">' +
        '<div class="agent_counter_href_value">'+text+'</div>' +
        '</div>' +
        email_btn +
        '<input class="submit-btn b_right" type="button" onclick="copyLinkText(this,'+vol_val+',\''+mode+'\');" value="Копировать" /></div>' +
        '<div class="clear"></div>' +
        '</div>';
    $('#page_body').append(form_html);
}

/**
 * Отображение данных попапа для предложения в случае ошибки
 * @param title     - заголовок окна
 */
function showTextLinkFeedbackFormErr(title){
    var backshadowObj = $('#back_shad').addClass('active');

    var form_html = '<div id="popup_phone_num" class="link_text_w active">'+
        '<div class="popup_logo"></div>' +
        '<div class="popup_close" onclick="closeSmsPopup(this);"></div>'+
        '<div class="popup_header">'+title+'</div>' +
        '<div class="row">' +
        '<div class="agent_counter_href_value">Предложение неактуально, обновите страницу.</div>' +
        '</div>' +
        '<input class="submit-btn b_right" type="button" onclick="location.reload(true);" value="Обновить" />' +
        '<div class="clear"></div>' +
        '</div>';
    $('#page_body').append(form_html);
}


/**
 * Попытка копирования в буфер обмена текущей ссылки для поставщика
 * @param argObj        - ссылка на dom элемент
 * @param volumeVal     - объем
 * @param mode          - режим работы (farmer|client)
 */
function copyLinkText(argObj, volumeVal, mode){
    var wObj = $(argObj).closest('.link_text_w').find('.agent_counter_href_value');
    var text = wObj.html();

    //убираем теги и лишние данные из копирования (чтобы корректно отображалось в мессенджерах)
    //меняем символ &nbsp; на пробелы
    text = text.replace(new RegExp("&nbsp;", 'g'), " ");
    //меняем переносы
    text = text.replace(/\<br\/?\>/gi,"\n");
    //убираем оставшиеся теги открывающие
    text = text.replace(/\<(a|b|u|i)[^>]*\>/gi,"");
    //убираем оставшиеся теги закрывающие
    text = text.replace(/\<\/(a|b|u|i) *\>/gi,"");

    //if(copyToClipboard(text)){
        CopyClipboardText(text);
        //данные скопированы в буфер обмена, показываем результат
        var del_obj = wObj.siblings('.agent_counter_href_success_text');
        var text_val = 'Сообщение скопировано в буфер обмена';
        if(mode == 'farmer'){
            if(volumeVal > 0){
                text_val += ', с объемом ' + volumeVal;
            }else{
                text_val += ', без объема';
            }
        }
        if(del_obj.length == 0) {
            wObj.before('<div class="agent_counter_href_success_text">' + text_val + '</div>');
            del_obj = wObj.siblings('.agent_counter_href_success_text');
            setTimeout(function () {
                if (del_obj.length == 1) {
                    del_obj.remove();
                    closeSmsPopup(wObj);
                }
            }, 3000);
        }else{
            del_obj.remove();
            wObj.before('<div class="agent_counter_href_success_text">' + text_val + '</div>');
            del_obj = wObj.siblings('.agent_counter_href_success_text');
            setTimeout(function () {
                if (del_obj.length == 1) {
                    del_obj.remove();
                    closeSmsPopup(wObj);
                }
            }, 3000);
        }
    //}
}

/**
 * Попытка копирования в буфер обмена текущей ссылки для поставщика
 * @param argObj        - ссылка на dom элемент
 * @param volumeVal     - объем
 * @param mode          - режим работы (farmer|client)
 */
function copyLinkHtml(argObj){
    // var wObj = argObj.siblings('.copy-text');
    // var text = wObj.html();
    var text = argObj;

    //убираем теги и лишние данные из копирования (чтобы корректно отображалось в мессенджерах)
    //меняем символ &nbsp; на пробелы
    text = text.replace(new RegExp("&nbsp;", 'g'), " ");
    //меняем переносы
    text = text.replace(/\<br\/?\>/gi,"\n");
    //убираем оставшиеся теги открывающие
    text = text.replace(/\<(a|b|u|i)[^>]*\>/gi,"");
    //убираем оставшиеся теги закрывающие
    text = text.replace(/\<\/(a|b|u|i) *\>/gi,"");

    //if(copyToClipboard(text)){
        CopyClipboardText(text);
    //}
}

/**
 * Отправка текста с ссылкой на Email
 * @param obj   - ссылка на dom элемент
 * @param uid   - ID пользователя
 * @param mode  - режим работы (farmer|client|свой вариант)
 */
function sendTextLinkToEmail(obj,uid,mode) {
    var wObj = $(obj).closest('.link_text_w').find('.agent_counter_href_value');
    $.ajax({
        type: 'POST',
        url: '/ajax/sendTextLinkToEmail.php',
        data: {
            text: wObj.html(),
            uid: uid,
            mode: mode
        },
        dataType : "json",
        success: function (data) {
            if(data.result == 1){
                $(obj).attr('disabled','disabled');
                $(obj).css('opacity','0.5');
                var message = '';
                if(
                    mode == 'farmer'
                    || mode == 'farmer_offer_graph'
                ){
                    message = 'Сообщение направлено поставщику';
                }else if(mode == 'client' || mode == 'client_graph_href'){
                    message = 'Сообщение направлено покупателю';
                }else if(mode == 'notice_farmer'){
                    message = 'Сообщение направлено поставщику';
                }
                var del_obj = wObj.siblings('.agent_counter_href_success_text');
                if(del_obj.length == 0) {
                    wObj.before('<div class="agent_counter_href_success_text">' + message + '</div>');
                    del_obj = wObj.siblings('.agent_counter_href_success_text');
                    setTimeout(function () {
                        if (del_obj.length == 1) {
                            del_obj.remove();
                            closeSmsPopup(wObj);
                        }
                    }, 3000);
                }
            }else{
                var message = 'Ошибка отправки сообщения';
                var del_obj = wObj.siblings('.agent_counter_href_success_text');
                if(del_obj.length == 0) {
                    wObj.before('<div class="agent_counter_href_success_text">' + message + '</div>');
                    del_obj = wObj.siblings('.agent_counter_href_success_text');
                    setTimeout(function () {
                        if (del_obj.length == 1) {
                            del_obj.remove();
                        }
                    }, 3000);
                }
            }
        }
    });
}

//форма обратной связи для счётчика принятий для черного списка
function showCounterRequestFeedbackFormBL(argEmail, lim_param, price_val){
    var backshadowObj = $('#back_shad').addClass('active');
    var form_html = '<div id="popup_phone_num" class="active lim" data-lim="' +lim_param + '" data-price="' + price_val + '">' +
        '<div class="popup_logo"></div>' +
        '<div class="popup_close" onclick="closeSmsPopup(this);"></div>' +
        '<div class="popup_header">Заявка на увеличение лимита</div>' +
        '<div class="text feedback_data">' +
        '<div class="row"><div class="row_label">Email:<div class="restr_val"></div></div>' +
        '<input ' + (argEmail != '' ? 'readonly="readonly"' : '') + ' type="text" name="feedback_email" value="' + argEmail + '" />' + (argEmail != '' ? '<input type="hidden" name="feedback_user_email" value="y" />' : '') + '</div>' +
        '<div class="row"><div class="row_label">Тип плательщика:<div class="restr_val"></div></div><select name="user_type" onchange="changeCounterLimitType(this);" ><option value="0">Не выбран</option><option value="1">Физ. лицо</option>' +
        '<option value="2">Юр. лицо / ИП</option></select>' + (argEmail != '' ? '<input type="hidden" name="feedback_user_email" value="y" />' : '') + '</div><div class="row"><div class="row_label">Количество принятий (min. ' + lim_param + '):</div>' +
        '<input type="text" name="opener_count" value="' + lim_param + '" onchange="checkCounterLimitMinValue(this);" /></div>' +
        '<div class="row">' +
        '<div class="row_label">Стоимость, руб.:</div>' +
        '<input readonly="readonly" type="text" name="opener_sum" value="' + lim_param * price_val + '" />' +
        '</div>' +
        '<div class="row">' +
        '<div class="row_label">Комментарий:</div>' +
        '<textarea type="text" name="comment_text" value=""/>' +
        '</div>' +
        '<input class="submit-btn" type="button" onclick="sendCounterRequestFeedbackForm(this);" value="Отправить" /></div><div class="clear"></div>' +
        '</div>';
    $('#page_body').append(form_html);
    $('#popup_phone_num select').select2(
        {
            minimumResultsForSearch: Infinity,
            templateResult: formatAgroState
        }
    ).siblings('.select2').addClass('agro_select2_container');
    //ограничение длинный ввода в textarea
    $("#popup_phone_num textarea").keyup(function() {
        if (this.value.length > 2000)
            this.value = this.value.substr(0, 2000);
    });

}

//проверка значения количества заявок
function checkCounterLimitMinValue(argObj)
{
    var wObj = $(argObj);
    var popup_obj = wObj.parents('#popup_phone_num');
    var limit_val = 0, price_val = 0, new_val = 0;
    if(typeof popup_obj.attr('data-lim') != 'undefined'){
        //получение настроек
        limit_val = parseInt(popup_obj.attr('data-lim'));
        if(isNaN(limit_val)){
            limit_val = 0;
        }
        new_val = parseInt(wObj.val());
        if(isNaN(new_val) || new_val < limit_val){
            new_val = limit_val;
            wObj.val(new_val);
        }

        price_val = parseInt(popup_obj.attr('data-price'));
        if(isNaN(price_val)){
            price_val = 0;
        }

        //расчёт нового значения
        if(new_val > 0
            && price_val > 0
        ){
            popup_obj.find('input[name="opener_sum"]').val(number_format(new_val * price_val, 0, '.', ' '));
        }
    }
}

//проверка значения количества товаров и месяцев для заявки
function checkOfferLimitMinValue(argObj)
{
    var wObj;
    var popup_obj = $(argObj).parents('#popup_phone_num');
    var limit_val = 0, price_val = 0, new_val = 0, month_val = 0;
    if(typeof popup_obj.attr('data-lim') != 'undefined'){
        //сначала проверяем количество товаров
        wObj = popup_obj.find('input[name="opener_count"]');

        //получение настроек
        limit_val = parseInt(popup_obj.attr('data-lim'));
        if(isNaN(limit_val)){
            limit_val = 0;
        }
        new_val = parseInt(wObj.val());
        if(isNaN(new_val) || new_val < limit_val){
            new_val = limit_val;
            wObj.val(new_val);
        }

        price_val = parseInt(popup_obj.attr('data-price'));
        if(isNaN(price_val)){
            price_val = 0;
        }

        //проверяем количество месяцев
        wObj = popup_obj.find('input[name="month_count"]');

        limit_val = parseInt(popup_obj.attr('data-month'));
        month_val = wObj.val();
        if(isNaN(month_val) || month_val < limit_val){
            month_val = limit_val;
            wObj.val(month_val);
        }

        //расчёт нового значения
        if(new_val > 0
            && price_val > 0
            && month_val > 0
        ){
            popup_obj.find('input[name="opener_sum"]').val(number_format(new_val * price_val * month_val, 0, '.', ' '));
        }
    }
}

//проверка значения количества заявок
function changeCounterLimitType(argObj)
{
    var wObj = $(argObj);
    if(wObj.val() != 0){
        wObj.parents('.row').find('.row_label .restr_val').text('');
    }
}

//попытка отправить заявку администратору (для принятий)
function sendCounterRequestFeedbackForm(argBut) {
    var areaObj = $(argBut).parents('#popup_phone_num');
    var checkObj = null, foundErr = false, emailVal = '', typeVal = '', valueVal = '';

    //проверка внесённых данных
    checkObj = areaObj.find('input[name="feedback_email"]');
    if(checkObj.val().length == 0
        || !checkEmailRfc(checkObj.val())
    ){
        foundErr = true;
        checkObj.parents('.row').find('.row_label .restr_val').text('Укажите корректный email');
    }else{
        emailVal = checkObj.val();
        checkObj.parents('.row').find('.row_label .restr_val').text('');
    }

    checkObj = areaObj.find('select[name="user_type"]');
    if(checkObj.val().length == 0
        || checkObj.val() == 0
    ){
        foundErr = true;
        checkObj.parents('.row').find('.row_label .restr_val').text('Укажите тип оплаты');
    }else{
        typeVal = checkObj.val();
        checkObj.parents('.row').find('.row_label .restr_val').text('');
    }

    valueVal = areaObj.find('input[name="opener_count"]').val();

    var commentText = '';
    if(areaObj.find('textarea[name="comment_text"]').length>0){
        commentText = areaObj.find('textarea[name="comment_text"]').val();
    }


    if(!foundErr) {
        $.post('/ajax/counterRequestFeedback.php', {
            email: emailVal,
            type: typeVal,
            value: valueVal,
            comment_text: commentText,
        }, function (mes) {
            switch (mes) {
                //корректное выполнение
                case '1':
                    $('.opening_limit_ended .result_message, .opening_limit_available .result_message').text('Заявка отправлена.');
                    $('#popup_phone_num .popup_close').trigger('click');
                    break;

                //ошибка в email
                case '2':
                    areaObj.find('input[name="feedback_email"]').parents('.row').find('.row_label .restr_val').text('Укажите корректный email');
                    break;

                default:
                //неопознанная ошибка
            }
        });
    }
}

//попытка отправить заявку администратору (для запросов)
function sendRequestLimitsFeedbackForm(argBut) {
    var areaObj = $(argBut).parents('#popup_phone_num');
    var checkObj = null, foundErr = false, emailVal = '', typeVal = '', valueVal = '';

    //проверка внесённых данных
    checkObj = areaObj.find('input[name="feedback_email"]');
    if(checkObj.val().length == 0
        || !checkEmailRfc(checkObj.val())
    ){
        foundErr = true;
        checkObj.parents('.row').find('.row_label .restr_val').text('Укажите корректный email');
    }else{
        emailVal = checkObj.val();
        checkObj.parents('.row').find('.row_label .restr_val').text('');
    }

    checkObj = areaObj.find('select[name="user_type"]');
    if(checkObj.val().length == 0
        || checkObj.val() == 0
    ){
        foundErr = true;
        checkObj.parents('.row').find('.row_label .restr_val').text('Укажите тип оплаты');
    }else{
        typeVal = checkObj.val();
        checkObj.parents('.row').find('.row_label .restr_val').text('');
    }

    valueVal = areaObj.find('input[name="opener_count"]').val();

    var commentText = '';
    if(areaObj.find('textarea[name="comment_text"]').length>0){
        commentText = areaObj.find('textarea[name="comment_text"]').val();
    }

    if(!foundErr) {
        $.post('/ajax/limitsRequestFeedback.php', {
            email: emailVal,
            type: typeVal,
            value: valueVal,
            comment_text: commentText,
        }, function (mes) {
            switch (mes) {
                //корректное выполнение
                case '1':
                    $('.add_limit_line .result_message').text('Заявка отправлена.');
                    $('#popup_phone_num .popup_close').trigger('click');
                    break;

                //ошибка в email
                case '2':
                    areaObj.find('input[name="feedback_email"]').parents('.row').find('.row_label .restr_val').text('Укажите корректный email');
                    break;

                default:
                //неопознанная ошибка
            }
        });
    }
}

//попытка отправить заявку администратору (для товаров)
function sendOfferLimitsFeedbackForm(argBut) {
    var areaObj = $(argBut).parents('#popup_phone_num');
    var checkObj = null, foundErr = false, emailVal = '', typeVal = '', valueVal = '', monthVal = '';

    //проверка внесённых данных
    checkObj = areaObj.find('input[name="feedback_email"]');
    if(checkObj.val().length == 0
        || !checkEmailRfc(checkObj.val())
    ){
        foundErr = true;
        checkObj.parents('.row').find('.row_label .restr_val').text('Укажите корректный email');
    }else{
        emailVal = checkObj.val();
        checkObj.parents('.row').find('.row_label .restr_val').text('');
    }

    checkObj = areaObj.find('select[name="user_type"]');
    if(checkObj.val().length == 0
        || checkObj.val() == 0
    ){
        foundErr = true;
        checkObj.parents('.row').find('.row_label .restr_val').text('Укажите тип оплаты');
    }else{
        typeVal = checkObj.val();
        checkObj.parents('.row').find('.row_label .restr_val').text('');
    }

    valueVal = areaObj.find('input[name="opener_count"]').val();
    monthVal = areaObj.find('input[name="month_count"]').val();

    var commentText = '';
    if(areaObj.find('textarea[name="comment_text"]').length>0){
        commentText = areaObj.find('textarea[name="comment_text"]').val();
    }

    if(!foundErr) {
        $.post('/ajax/limitsOfferFeedback.php', {
            email: emailVal,
            type: typeVal,
            value: valueVal,
            month_value: monthVal,
            comment_text: commentText,
        }, function (mes) {
            switch (mes) {
                //корректное выполнение
                case '1':
                    $('.add_limit_line .result_message').text('Заявка отправлена.');
                    $('#popup_phone_num .popup_close').trigger('click');
                    break;

                //ошибка в email
                case '2':
                    areaObj.find('input[name="feedback_email"]').parents('.row').find('.row_label .restr_val').text('Укажите корректный email');
                    break;

                default:
                //неопознанная ошибка
            }
        });
    }
}
//TODO альтернативный код, можно и удалить
function checkMenuHeight() {
	var $header = $('#header'),
		$headerLogo = $('#header_title', $header),
		$topMenu = $('.main_menu', $header),
		$bottomMenu = $('.bot_menu', $header);


    var p_size = getPageSize();
    if(p_size.window.width>980){
		if($(window).height()  <= ($headerLogo.outerHeight(true) + $topMenu.outerHeight(true) + $bottomMenu.outerHeight(true)) ) {
			$bottomMenu.css('position', 'static');
			$header.css({
				'position' : 'absolute',
				'max-height' : '100%'
			});
		}
		else {
			$bottomMenu.css('position', 'absolute');
			$header.css({
				'position' : 'fixed',
				'max-height' : '100vh'
			});
		}
	}

}
function copySpecialLink() {
    if(getCookie('spec_href') != null){
        copyToClipboard(getCookie('spec_href'))
        setCookie('spec_href', '', -1);
    }
}

//функция копирует текст аргумента в буфер обмена (требует разрешения браузера, т.е. реального действия пользователя например клика по элементу)
//можено использовать в синхронном аякс запросе (async: false)
function CopyClipboardText(arg) {
    var copyFrom = document.createElement("textarea");
    document.body.appendChild(copyFrom);
    copyFrom.textContent = arg;
    copyFrom.select();
    document.execCommand("copy");
    copyFrom.remove();
}

//пересчитывает и устанавливает стоимость агентской услуги
//objArg - объект line_area в списке товаров
function recountCounterOfferPartnerPrice(objArg){
    if(
        typeof counter_option_contract !== 'undefined'
        && typeof counter_option_lab !== 'undefined'
        && typeof counter_option_support !== 'undefined'
    ){
        var iPrice = 0, iCsmPrice = 0, iVolume = 0, bLabChecked = false, bSupportChecked = false, objPriceInput, objPricePerTonInput, iTemp = 0, sMode = 'offers';

        //если в списке товаров
        var objWorkArea = objArg.find('.counter_request_additional_data');
        var objWork, objTemp;
        if(objWorkArea.length > 0){
            objWork = objWorkArea.find('input[name="partner_service_price"]');
            if(objWork.length > 0){
                objPriceInput = objWork;
                objTemp = objWorkArea.find('input[name="volume"]');
                iTemp = 0;
                if(
                    objTemp.length === 1
                    && objTemp.val() !== ''
                ){
                    iTemp = parseInt(objTemp.val().replace(' ', ''));
                    if(!isNaN(iTemp)){
                        iVolume = iTemp;
                    }
                }

                objTemp = objWorkArea.find('input[name="price"]');
                iTemp = 0;
                if(
                    objTemp.length === 1
                    && objTemp.val() !== ''
                ){
                    iTemp = parseInt(objTemp.val().replace(' ', ''));
                    if(!isNaN(iTemp)){
                        iCsmPrice = iTemp;
                    }
                }

                objTemp = objArg.find('input[name="quality_approved"]');
                if(
                    objTemp.length === 1
                    && objTemp.prop('checked') === true
                ){
                    bLabChecked = true;
                }

                objTemp = objWorkArea.find('input[name="IS_AGENT_SUPPORT"]');
                if(
                    objTemp.length === 1
                    && objTemp.prop('checked') === true
                ){
                    bSupportChecked = true;
                }

                objPricePerTonInput = objWorkArea.find('.partner_price_part .val');
            }
        }
        //если в списке пар
        else{
            objWorkArea = objArg.find('.options_form');
            if(objWorkArea.length > 0) {
                objWork = objArg.find('.agent_price .name_1 .val');
                if (objWork.length > 0) {
                    sMode = 'pairs';
                    objPriceInput = objWork;
                    iTemp = objArg.attr('data-volume');
                    if (
                        typeof objArg.attr('data-volume') !== 'undefined'
                    ) {
                        iTemp = parseInt(objArg.attr('data-volume'));
                        if (!isNaN(iTemp)) {
                            iVolume = iTemp;
                        }
                    }

                    objTemp = objArg.find('.line_additional .prop_area.prices_val:first .area_1:first .val_1 .decs_separators');
                    iTemp = 0;
                    if (
                        objTemp.length === 1
                        && objTemp.text() !== ''
                    ) {
                        iTemp = parseInt(objTemp.text().replace(' ', ''));
                        if (!isNaN(iTemp)) {
                            iCsmPrice = iTemp;
                        }
                    }

                    objTemp = objWorkArea.find('.val_adress.slide-description:not(.inactive) input[name="IS_ADD_CERT"]');
                    if (
                        objTemp.length === 1
                        && objTemp.prop('checked') === true
                    ) {
                        bLabChecked = true;
                    }

                    objTemp = objWorkArea.find('.val_adress.slide-description:not(.inactive) input[name="IS_AGENT_SUPPORT"]');
                    if (
                        objTemp.length === 1
                        && objTemp.prop('checked') === true
                    ) {
                        bSupportChecked = true;
                    }

                    objPricePerTonInput = objArg.find('.line_additional .prop_area.prices_val:first .agent_price .val_1 .decs_separators');
                }
            }
        }

        //установление данных
        if(objPriceInput) {
            //расчет стоимости агентского договора
            iPrice += parseInt(Math.round((counter_option_contract / 10000.0) * iCsmPrice * iVolume));

            //добавление стоимости услуг лаборатории
            if (bLabChecked) {
                iPrice += counter_option_lab;
            }

            //добавление стоимости сопровождения сделки
            if (bSupportChecked) {
                iPrice += counter_option_support * iVolume;
            }

            //устанавливаем рассчитанное значение
            if(sMode === 'offers') {
                objPriceInput.val(number_format(iPrice, 0, '.', ' '));
                if(objPricePerTonInput.length === 1){
                    if(iVolume > 0){
                        objPricePerTonInput.text(number_format(Math.round(iPrice / iVolume), 0, '.', ' '));
                        objPricePerTonInput.parents('.partner_price_part').addClass('active');
                    }else{
                        objPricePerTonInput.parents('.partner_price_part').removeClass('active');
                    }
                }
            }else{
                objPriceInput.text(number_format(iPrice, 0, '.', ' '));
                if(
                    objPricePerTonInput
                    && iVolume > 0
                ){
                    objPricePerTonInput.text(number_format(Math.round(iPrice / iVolume), 0, '.', ' '));
                }
            }
        }
    }
}

//Функция "перетаскивает" пункты в агентском предложении после обновления данных (задержка в 50мс нужна для того, чтобы не мешать с физическим переключением чекбоксов)
//objArg - контейнер form.options_form
function moveOptionsAfterUpdate(objArg){
    var objTemp, i = 0;
    setTimeout(function(){
        //находим убранные опции среди "нажатых"
        objArg.find('.checked_options .slide-description:not(.inactive) input[type="checkbox"]').each(function(cInd, cObj){
            //если опция убрана, то отображаем данные в другом списке
            if($(cObj).prop('checked') !== true){
                // //работаем в колонке div.checked_options
                $(cObj).prop({disabled: true, checked: true}).parents('.val_adress').addClass('inactive').find('.custom_input').addClass('checked');
                // //работаем в колонке div.no_checked_options
                objArg.find('.no_checked_options input[type="checkbox"][name="' + $(cObj).attr('name') + '"]').prop({checked: false, disabled: false}).parents('.val_adress').removeClass('inactive').find('.custom_input').removeClass('checked');
            }
        });

        //находим поставленные опции среди "не нажатых"
        objArg.find('.no_checked_options .slide-description:not(.inactive) input[type="checkbox"]').each(function(cInd, cObj){
            //если опция поставлена, то отображаем данные в другом списке
            if($(cObj).prop('checked') === true){
                //работаем в колонке div.no_checked_options
                $(cObj).prop({disabled: true, checked: false}).parents('.val_adress').addClass('inactive').find('.custom_input').removeClass('checked');
                //работаем в колонке div.checked_options
                objArg.find('.checked_options input[type="checkbox"][name="' + $(cObj).attr('name') + '"]').prop({checked: true, disabled: false}).parents('.val_adress').removeClass('inactive').find('.custom_input').addClass('checked');
            }
        });

        //скрываем пункт "Возможные к заказу", если нужно
        var objUnselectTitle = objArg.find('.no_checked_options .message-add');
        if(objUnselectTitle.length === 1) {
            if (objArg.find('.no_checked_options .slide-description:not(.inactive)').length > 0) {
                objUnselectTitle.removeClass('inactive');
            } else {
                objUnselectTitle.addClass('inactive');
            }
        }

        //убираем выделение кнопки
        objArg.find('.accept').addClass('empty');
    }, 50);
}