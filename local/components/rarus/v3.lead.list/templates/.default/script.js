var stop_slide_anim = 0;

// Из миллисекунд в объект с кол-вом дней, часов, минут, секунд. Например: 22 д. 10 ч. 60 с.
millisecToTimeStruct = function (ms) {
    var d, h, m, s;
    if (isNaN(ms)) {
        return {};
    }
    d = ms / (1000 * 60 * 60 * 24);
    h = (d - ~~d) * 24;
    m = (h - ~~h) * 60;
    s = (m - ~~m) * 60;
    return {d: ~~d, h: ~~h, m: ~~m, s: ~~s};
},
    // форматирует вывод
    toFormattedStr = function(tStruct){
        var res = '';
        if (typeof tStruct === 'object'){
            if(tStruct.d>0)
                res += tStruct.d + ' д. '
            if(tStruct.h>0)
                res += tStruct.h + ' час. '
            if(tStruct.m>0)
                res += tStruct.m + ' мин. '
        }
        return res;
    };

/**
 *Форматирование секунд в строку формата (X д. X час. X мин. X сек.)
 */
secondTimesFormat =  function(ms){
    var timeStruct = 0;
    timeStruct = millisecToTimeStruct(ms);
    formattedString = toFormattedStr(timeStruct);
    return formattedString;
}

$(document).ready(function() {
    //отображение/textarea формы с данными КП
    $('#page_body .make_k_offer').on('click', function(){
        var text_area = $(this).siblings('.kp_copy_text');
        var text_obj = text_area.find('textarea');

        if(text_area.hasClass('active')){
            text_area.removeClass('active');
            $(this).text($(this).attr('data-showtext'));
        }else{
            //генерируем текст для копирования в буффер обмена
            text_obj.val(copyKPGen($(this), false));

            $(this).siblings('.kp_copy_text').addClass('active');
            $(this).text($(this).attr('data-hidetext'));
        }
    });

    if($('.region_limits').length > 0){
        $('.region_limits').each(function(cInd, cObj){
            if($(this).attr('data-plimits')>0){
                $('select[name="region_id"]').find('option[value="'+$(this).attr('data-regionid')+'"]').attr('data-plimit','1');
            }
        });
    }
    if($('.cultures_limits').length > 0){
        $('.cultures_limits').each(function(cInd, cObj){
            if($(this).attr('data-plimits')>0){
                $('select[name="culture"]').find('option[value="'+$(this).attr('data-cultureid')+'"]').attr('data-plimit','1');
            }
        });
    }
    if($('.farmers_limits').length > 0){
        $('.farmers_limits').each(function(cInd, cObj){
            if($(this).attr('data-plimits')>0){
                $('select[name="farmer_id[]"]').find('option[value="'+$(this).attr('data-farmerid')+'"]').attr('data-plimit','1');
            }
        });
    }
    if($('.nds_limits').length > 0){
        $('.nds_limits').each(function(cInd, cObj){
            if($(this).attr('data-plimits')>0){
                $('select[name="type_nds"]').find('option[value="'+$(this).attr('data-ndsid')+'"]').attr('data-plimit','1');
            }
        });
    }

    if($('.wh_limits').length>0){
        $('.wh_limits').each(function(cInd, cObj){
            if($(this).attr('data-plimits')>0){
                $('select[name="wh"]').find('option[value="'+$(this).attr('data-whid')+'"]').attr('data-plimit','1');
            }
        });
    }

    //копирование данных из textarea в буфер обмена
    $('#page_body .kp_copy_text .copy_clip').on('click', function(){
        if(copyToClipboard($(this).siblings('textarea').val())){
            //данные скопированы в буфер обмена, показываем результат
            showCopySuccess($(this));
        }else{
            //не удалось скопировать данные, показываем результат
            alert('Не удалось скопировать данные в буфер обмена, скопируйте пожалуйста данные вручную.');
        }
    });

    //обновление текста
    // $('#page_body .kp_copy_text .empty_but.update_text').on('click', function(){
    //     var text_obj = $(this).siblings('textarea');
    //     text_obj.val(copyKPGen($(this).parents('.make_k_offer_area').find('.make_k_offer'), true));
    // });

    //установка метки времени загрузки страницы
    $('.list_page_rows.pairs_rows_list.farmer_requests_list').attr('date-time', parseInt($.now() / 1000));

    $('.tonn_val_ input[name="volume"]').on('keyup', function(){
        var v = $(this).val();
        var p = $(this).parents('.prop_area.tonn_val_').attr('data-price');
        var cost = 0;
        var temp_val = $(this).parents('.prop_area.tonn_val_').attr('data-remains');
        var href = $(this).parents('.line_additional').find('.counter_request_add').attr('href');
        href = href.replace(/&vol=\d+/, '');
        if (parseInt(v) > 0 && parseFloat(p) > 0 && parseInt(temp_val) > 0)
        {
            if(parseInt(v) > parseInt(temp_val))
            {
                v = temp_val;
                $(this).val(v);
            }
            cost = parseFloat(p) * parseInt(v);
        }

        if(cost > 0)
        {
            $(this).parents('.line_additional').find('.prop_area.total').addClass('active').find('.val .decs_separators').text(number_format(cost, 0, ',', ' '));
            $(this).parents('.line_additional').find('.submit-btn:not(.hard_disabled)').removeClass('inactive');
        }
        else
        {
            $(this).parents('.line_additional').find('.prop_area.total').removeClass('active').find('.val .decs_separators').text(0);
            $(this).parents('.line_additional').find('.submit-btn:not(.counter_request_add)').addClass('inactive');
        }

        if(parseInt(v) > 0) {
            $(this).parents('.line_additional').find('.counter_request_add').attr('href', href + '&vol=' + v);
        }
        else {
            $(this).parents('.line_additional').find('.counter_request_add').attr('href', href);
        }
    });

    //mask tons input as positive integer value
    $('.list_page_rows form').on('keyup', 'input[name="volume"]', function(){
        checkMask($(this), 'pos_int');
    });

    $('.list_page_rows .line_area .line_inner, .list_page_rows .line_area .line_additional .hide_but').on('click', function(){
        if(stop_slide_anim == 0)
        {
            stop_slide_anim = 1;
            var wObj = $(this).parents('.line_area');
            wObj.toggleClass('active');
            if(!wObj.hasClass('active'))
            {
                wObj.find('form.line_additional').slideUp(300, function(){
                    stop_slide_anim = 0;
                });
                wObj.find('.arw_list').removeClass('arw_icon_open').addClass('arw_icon_close');
            }
            else
            {
                wObj.find('form.line_additional').slideDown(300, function(){
                    stop_slide_anim = 0;
                });
                wObj.find('.arw_list').removeClass('arw_icon_close').addClass('arw_icon_open');
            }
        }
    });

    $('.pairs_rows_list form.line_additional input[name="accept"]').on('click', function(e){
        if($(this).hasClass('inactive')) {
            e.preventDefault();
            return false;
        }

        /*var subm_but = $(this).find('input[type="submit"][name="accept"]');
        if(subm_but.length != 1 || subm_but.hasClass('inactive'))
        {
            e.preventDefault();
            return false;
        }*/
    });

    $('.line_additional .prop_area.total .prolongate_area a.inactive').on('click', function(e){
        e.preventDefault();
        return false;
    });

    //ссылка на создание вп от агента для поставщика
    $('.make_by_agent_counter_href').on('click', function(){
        var wObj = $(this);
        var html_val = '';
        var volume_obj = wObj.siblings('.counter_volume_input');

        var host_container = $(this).parents('.list_page_rows');
        var offer_data_obj = $(this).parents('form').find('input[type="hidden"][name="offer"]');
        if(host_container.length == 1
            && typeof host_container.attr('data-host') != 'undefined'
            && typeof wObj.attr('data-href') != 'undefined'
        ){
            if(offer_data_obj.length == 1
                && typeof wObj.attr('data-uid') != 'undefined'
                && wObj.attr('data-uid') != ''
            ){
                var vol_val = 0;
                if(volume_obj.length == 1){
                    //добавляем объем в запрос, если требуется
                    vol_val = parseInt(volume_obj.val());
                    if(isNaN(vol_val)
                        || vol_val < 1
                    ){
                        vol_val = 0;
                    }
                }

                $.post('/ajax/getUserInviteHref.php', {uid: wObj.attr('data-uid'), offer_id: offer_data_obj.val(), vol: vol_val}, function(mes){
                    if(mes != 0){
                        html_val = mes;
                        wObj.attr('data-url', html_val);
                        if(wObj.siblings('.agent_counter_href_value').length == 0) {
                            wObj.parent().append('<div class="agent_counter_href_value"></div>');
                            wObj.siblings('.agent_counter_href_value').html(html_val);
                        }else{
                            wObj.siblings('.agent_counter_href_value').html(html_val);
                        }
                        copyAgentHref(wObj, vol_val);
                    }
                });
            }
        }
    });

    //прокрутка до активного раздела
    var activeFolder = $('.list_page_rows .line_area.active');
    if(activeFolder.length == 1){
        var offset = activeFolder.offset();
        setTimeout(function(){
            $(document).scrollTop(offset.top - 50);
        }, 150);
    }


    $('.cp_accept a.submit-btn').click(function( event ) {
        event.stopPropagation();
        window.location.href = $(this).attr('data-href');
    });


});

//копирование данных в textarea
function copyKPGen(elemObj, updateFlag){
    var result = '';
    var left_time = elemObj.attr('data-date');

    if(updateFlag){
        left_time = secondToHoursForCopy(elemObj.attr('data-seconds'));
    }

    if(elemObj.attr('data-deliverytype') == 'cpt'){
        result = 'Предлагаю продать ' + elemObj.attr('data-name') +
            ' по прямому контракту покупателю по цене договора ' + elemObj.attr('data-bcprice') +
            ' р/тн на воротах покупателя, находящихся в ' + elemObj.attr('data-km') +
            ' км от Вас. Также предлагаю перевозку, которая ориентировочно обойдется ' + elemObj.attr('data-tarif') +
            ' р/тн. Цена с места за Ваше качество при этом прогнозируется ' + elemObj.attr('data-cmprice') +
            ' р/тн. и будет действительна еще ' + left_time + '.';
    }else{
        result = 'Предлагаю продать ' + elemObj.attr('data-name') +
            ' по прямому контракту покупателю по цене договора ' + elemObj.attr('data-bcprice') +
            ' р/тн с погрузкой на Вашем складе. Цена с места за Ваше качество при этом прогнозируется ' + elemObj.attr('data-cmprice') +
            ' р/тн. и будет действительна еще ' + left_time + '.';




    }

    return result;
}

//отображение успешности копирования
function showCopySuccess(butObj){
    butObj.before('<div class="success_copy"></div>');
    var okObj = butObj.siblings('.success_copy:last');
    setTimeout(function(){
        okObj.addClass('disable');
        setTimeout(function(){
            okObj.remove();
        }, 2000);
    }, 20);
}

//возвращает время, оставшееся до окончания действия запроса (рассчитывается на времени до окончания, имеющемся на момент загрузки страницы и времени прошедшем с момента загрузки страницы)
function secondToHoursForCopy(seconds){
    var result = '';
    var seconds_diff = 0; //разница между текущими секундами и секундами в предложении
    var temp_val1 = '', temp_val2 = '';

    //получаем оставшееся время в секундах (имеющиеся изначально секунды остатка минус время прошедшее с момента загрузки страницы)
    seconds_diff = parseInt(seconds) - (
        parseInt($.now() / 1000) - parseInt($('.list_page_rows.pairs_rows_list.farmer_requests_list').attr('date-time'))
    );

    result = Math.floor(seconds_diff / 3600) . toString();
    if(result == '0'){ //минуты
        result = Math.ceil(seconds_diff / 60).toString();
        temp_val1 = parseInt(parseInt(result) < 10 ? result : result.substr(-2, 2));
        temp_val2 = parseInt(result.substr(-1, 1));
        if(temp_val2 > 0
            && (temp_val1 < 5 || temp_val1 > 20 && temp_val2 < 5)
        ){
            if(temp_val2 == 1){
                result += ' минута';
            }else{
                result += ' минуты';
            }
        }else{
            result += ' минут';
        }
    }else{ //часы
        temp_val1 = parseInt(parseInt(result) < 10 ? result : result.substr(-2, 2));
        temp_val2 = parseInt(result.substr(-1, 1));
        if(temp_val2 > 0
            && (temp_val1 < 5 || temp_val1 > 20 && temp_val2 < 5)
        ){
            if(temp_val2 == 1){
                result += ' час';
            }else{
                result += ' часа';
            }
        }else{
            result += ' часов';
        }
    }

    return result;
}

//попытка копирования в буфер обмена текущей ссылки для поставщика
function copyAgentHref(argObj, volumeVal){
    var wObj = argObj.siblings('.agent_counter_href_value');
    if(copyToClipboard(wObj.text())){
        //данные скопированы в буфер обмена, показываем результат
        var del_obj = wObj.siblings('.agent_counter_href_success_text');
        var text_val = 'Ссылка скопирована в буфер обмена';
        if(volumeVal > 0){
            text_val += ', с объемом ' + volumeVal;
        }else{
            text_val += ', без объема';
        }
        if(del_obj.length == 0) {
            wObj.before('<div class="agent_counter_href_success_text">' + text_val + '</div>');
            del_obj = wObj.siblings('.agent_counter_href_success_text');
            setTimeout(function () {
                if (del_obj.length == 1) {
                    del_obj.remove();
                }
            }, 20000);
        }else{
            del_obj.remove();
            wObj.before('<div class="agent_counter_href_success_text">' + text_val + '</div>');
            del_obj = wObj.siblings('.agent_counter_href_success_text');
            setTimeout(function () {
                if (del_obj.length == 1) {
                    del_obj.remove();
                }
            }, 20000);
        }
    }
}