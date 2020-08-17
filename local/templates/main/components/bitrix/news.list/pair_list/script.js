var stop_slide_anim = 0;

$(document).ready(function() {
    $('.list_page_rows .line_area .line_inner, .list_page_rows .line_area .line_additional .hide_but').on('click', function(){
        if(stop_slide_anim == 0)
        {
            stop_slide_anim = 1;
            var wObj = $(this).parents('.line_area');
            wObj.toggleClass('active');
            if(!wObj.hasClass('active'))
            {
                wObj.find('.line_additional').slideUp(300, function(){
                    stop_slide_anim = 0;
                });
                wObj.find('.arw_list').removeClass('arw_icon_open').addClass('arw_icon_close');
            }
            else
            {
                wObj.find('.line_additional').slideDown(300, function(){
                    stop_slide_anim = 0;
                });
                wObj.find('.arw_list').removeClass('arw_icon_close').addClass('arw_icon_open');
            }
        }
    });

    var params = window
        .location
        .search
        .replace('?','')
        .split('&')
        .reduce(
            function(p,e){
                var a = e.split('=');
                p[ decodeURIComponent(a[0])] = decodeURIComponent(a[1]);
                return p;
            },
            {}
        );
    if(params['id'] != 'undefined'){
        if($('.list_page_rows.requests .line_area[data-id="' + params['id'] + '"]').length == 1){
            var wObj = $('.list_page_rows.requests .line_area[data-id="' + params['id'] + '"]');
            wObj.find('.line_inner').trigger('click');
            var wObjOffset = wObj.offset();
            setTimeout(function(){
                $(document).scrollTop(wObjOffset.top);
            }, 300);
        }
    }

    //попап отправки пользователя в чёрный список
    $('.list_page_rows .prop_area.pot_black_list_row input[type="button"]').on('click', function(){
        var pairs_area = $(this).parents('.list_page_rows');
        var pair_obj = $(this).parents('.line_area');
        var user_type = 'c';
        var popup_text = '';
        var uid = 0;
        if(typeof pairs_area.attr('data-usertype') != 'undefined'){
            user_type = pairs_area.attr('data-usertype');
        }

        if(user_type == 'c'){
            uid = pair_obj.attr('data-fid');
        }else{
            uid = pair_obj.attr('data-cid');
        }

        //получение данных анкеты
        var anket_text = '';
        var anket_area = $('.anket_answers_data:first');
        if(anket_area.length == 1){
            anket_area.find('.item').each(function(cInd, cObj){
                if($(cObj).text() == 'другое'){
                    anket_text += '<div class="line radio_area"><input data-text="' + $(cObj).text() + ' (до 2000 символов)" type="checkbox" onclick="showHideOtherText(this);" name="anket_quest" value="' + $(cObj).attr('data-id') + '" /></div>';
                    anket_text += '<div class="line text_line"><textarea name="other_text" onclick="clickAnketCheckbox();"></textarea></div>';
                }else{
                    var additional_class = '';
                    if($(cObj).text().length > 45){
                        additional_class = ' p_right';
                    }
                    anket_text += '<div class="line radio_area' + additional_class + '"><input data-text="' + $(cObj).text().replace(/\"/g, '&quot;') + '" type="checkbox" onclick="clickAnketCheckbox();" name="anket_quest" value="' + $(cObj).attr('data-id') + '" /></div>';
                }
            });
        }
        if(anket_text.length > 0){
            anket_text = '<form action="" method="post"><div class="radio_group">' + anket_text + '</div></form>';
        }

        if(user_type == 'c'){
            popup_text = 'Вы уверены, что хотите отправить поставщика в черный список?<br/><br/>Укажите причину добавления:<div class="anket_area content"><div class="err_val"></div>' + anket_text + '</div><input class="submit-btn half_but" onclick="makeBlackListUser(this, \'' + user_type + '\', \'' + uid + '\', \'' + pair_obj.attr('data-id') + '\');" type="button" value="Отправить" /><input class="empty-btn half_but right" onclick="closeDefPopup();" type="button" value="Отмена" />';
        }else{
            popup_text = 'Вы уверены, что хотите отправить покупателя в черный список?<br/><br/>Укажите причину добавления:<div class="anket_area content"><div class="err_val"></div>' + anket_text + '</div><input class="submit-btn half_but" onclick="makeBlackListUser(this, \'' + user_type + '\', \'' + uid + '\', \'' + pair_obj.attr('data-id') + '\');" type="button" value="Отправить" /><input class="empty-btn half_but right" onclick="closeDefPopup();" type="button" value="Отмена" />';
        }

        defaultPopupShow('Черный список', popup_text);

        makeCustomForms();
    });

    //обработка смены опций услуг
    $('.list_page_rows .line_additional').on('change', '.adress_val input[type="checkbox"]', function(){
        checkClientOptionsChanged(this);
    });

    //изменение стоимостей при изменении опций
    $('.options_form input[name="IS_AGENT_SERVICE"], .options_form input[name="IS_ADD_CERT"], .options_form input[name="IS_AGENT_SUPPORT"]').on('change', function () {
        recountCounterOfferPartnerPrice($(this).parents('.line_area'));
    });

    //сохранение данных опций через ajax запрос
    $('.options_form').on('submit', function(e){
        e.preventDefault();

        var objW = $(this);
        var sSendData = objW.serialize();
        sSendData += '&send_ajax=y';
        objW = $(this).parents('.line_area');
        $.post('/client/pair/', sSendData, function (mes) {
            if(mes == 1){
                //"перетаскиваем" пункты
                moveOptionsAfterUpdate(objW);
            }
        });
    });
});

//отправка пользователя в чёрный список
function makeBlackListUser(argObj, user_type, uid, pair_id){

    //проверка заполненности обязательных полей
    var popupObj = $('#def_popup_window');
    var err_val = '';
    var checked_err = false;
    var checkObj;
    var checked_values = [];
    var other_text = '';
    popupObj.find('.line.radio_area input[type="checkbox"]').each(function(cInd, cObj){
        checkObj = $(cObj);
        if(checkObj.prop('checked') === true){
            if(checkObj.attr('data-text') == 'другое (до 2000 символов)'
                || checkObj.attr('data-text') == 'Другое (до 2000 символов)'
            ){
                checkObj = $('#def_popup_window textarea[name="other_text"]');
                var temp_val = checkObj.val();
                if(temp_val.length > 0){
                    if(temp_val.length < 2001) {
                        checked_err = true;
                        other_text = temp_val;
                        checked_values.push($(cObj).val());
                    }else{
                        checked_err = false;
                        err_val = 'Длина сообщения превышает 2000 символов';
                    }
                }else{
                    checked_err = false;
                    err_val = 'Укажите текст с причиной добавления пользователя в чёрный список';
                }
            }else{
                checked_values.push(checkObj.val());
                checked_err = true;
            }
        }
    });

    if(err_val == ''
        && !checked_err
    ){
        err_val = 'Выберите хотя бы один вариант в анкете';
    }

    if(err_val != ''){
        popupObj.find('.err_val').text(err_val);
    }

    if(checked_err) {
        $.post('/ajax/addToBlackList.php', {
            user_type: user_type,
            user_id: uid,
            deal_id: pair_id,
            anket: checked_values,
            other_text: other_text,
        }, function (mes){
            var message_area = $('.list_page_rows').siblings('.message_area:first');
            if (message_area.length == 0) {
                $('.list_page_rows').before('<div class="message_area"><div class="message"></div></div>');
                message_area = $('.list_page_rows').siblings('.message_area:first');
            }
            var message_obj = message_area.find('.message');

            if (mes == '1') {
                //все ок
                message_area.addClass('success');
                message_obj.text('Пользователь добавлен в чёрный список.');
            } else if (mes == '2') {
                //активная запись уже имеется (возможно подвисла страница)
                message_area.addClass('success');
                message_obj.text('Пользователь добавлен в чёрный список.');
            } else if (mes == '3') {
                //неизвестная ошибка
                message_area.removeClass('success');
                message_obj.text('При добавлении в черный список возникла ошибка. Попробуйте позднее или свяжитесь с администрацией.');
            }

            //убираем кнопку добавления, скрываем попап, прокручиваем страницу до сообщения-результата
            $('.list_page_rows .line_area[data-' + (user_type == 'f' ? 'c' : 'f') + 'id="' + uid + '"]').each(function (cInd, cObj) {
                $(cObj).removeClass('pot_black_list_area').addClass('black_list');
            });
            closeDefPopup();
            //$(document).scrollTop(0);
        });
    }
}

//функция определеяет показывать или скрывать текстовое поле
function showHideOtherText(argObj){
    var wObj = $(argObj);
    $('#def_popup_window .err_val').text('');
    if(wObj.prop('checked') === true){
        $('#def_popup_window').find('.text_line').addClass('active');
    }else{
        $('#def_popup_window').find('.text_line.active').removeClass('active');
    }
}

//функция обработки клика на чекбокс или textarea (убираем сообщение об ошибке)
function clickAnketCheckbox(){
    $('#def_popup_window .err_val').text('');
}

//функция обработки клика на чекбокс опции (активизуруем/деактивируем кнопку)
function checkClientOptionsChanged(objArg){
    var objW = $(objArg);
    var objForm = objW.parents('form');
    var bFoundChanges = false;
    objForm.find('input[type="checkbox"]').each(function(cInd, cObj){
        if(
            typeof $(cObj).attr('data-checked') !== 'undefined'
            && $(cObj).attr('data-checked') === 'y'
        ){
            if($(cObj).prop('checked') !== true) {
                bFoundChanges = true;
                return;
            }
        }else{
            if($(cObj).prop('checked') === true) {
                bFoundChanges = true;
                return;
            }
        }
    });

    //устанавливаем/снимаем активность
    if(bFoundChanges){
        objForm.find('.val_adress .accept').removeClass('empty');
    }else{
        objForm.find('.val_adress .accept').addClass('empty');
    }
}
