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

    //обновление текста
    // $('#page_body .kp_copy_text .empty_but.update_text').on('click', function(){
    //     var text_obj = $(this).siblings('textarea');
    //     text_obj.val(copyKPGen($(this).parents('.make_k_offer_area').find('.make_k_offer'), true));
    // });

    //установка метки времени загрузки страницы
    $('.list_page_rows.pairs_rows_list.farmer_requests_list').attr('date-time', parseInt($.now() / 1000));

    $('.deal_volume input[name="volume"]').on('keyup', function(){
        var v = $(this).val();
        var p = $(this).parents('.prop_area.tonn_val').attr('data-price');
        var cost = 0;
        var temp_val = $(this).parents('.prop_area.tonn_val').attr('data-remains');

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
            $(this).parents('.line_additional').find('.submit-btn').addClass('inactive');
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

    //добавляем маску для поля объём
    $('.counter_request_additional_data input[name="volume"]').on('keyup', function(){
        checkMask($(this), 'pos_int');
    });

    $('.counter_request_additional_data').find('input[name="price"]').on('focus', function () {
        if(isMobileMode() === true){
            $(this).val('');
        }
    });

    $('.counter_request_additional_data input[name="save"]').on('click', function(e) {
        var wObj = $('.counter_request_additional_data');
        var error_text = '';
        var check_val = '';

        $('.error_msg')
            .removeClass('active')
            .text('');

        //проверка обязательных полей
        if(wObj.find('input[name="selected_requests[]"]').length == 0){
            error_text += "Не выбран ни один запрос.<br/>";
        }

        check_val = wObj.find('input[name="volume"]').val();
        if(check_val == ""
            || check_val == '0'
        ){
            error_text += "Не указан объем.<br/>";
        }

        check_val = wObj.find('input[name="price"]').val();
        if(check_val == ""
            || check_val == '0'
        ){
            error_text += "Не указана цена.<br/>";
        }

        if(error_text != "") {
            $('.error_msg')
                .addClass('active')
                .html(error_text);

            e.preventDefault();
            return false;
        }
    });

    //вкл/выкл все чекбоксы у запросов
    $('.check_requests_area a').on('click', function(){
        if($(this).hasClass('ch_all_tr')){
            $('.counter_request_area input[name="choose_request"]').each(function(ind, cObj){
                if($(cObj).prop('checked') === false){
                    $(cObj).trigger('click');
                }
            });
        }else{
            $('.counter_request_area input[name="choose_request"]').each(function(ind, cObj){
                if($(cObj).prop('checked') === true){
                    $(cObj).trigger('click');
                }
            });
        }
    });

    //обработка выбора запроса
    $('.counter_request_area input[name="choose_request"]').on('click', function(){
        if($(this).prop('checked') === true){
            wObj = $('.counter_request_additional_data input[name="selected_requests[]"][value="' + $(this).val() + '"]');
            if(wObj.length == 0){
                $('.counter_request_additional_data').prepend('<input type="hidden" name="selected_requests[]" value="' + $(this).val() + '">');
            }
        }else{
            $('.counter_request_additional_data input[name="selected_requests[]"][value="' + $(this).val() + '"]').remove();
        }
    });

    //обработка ручного ввода цены
    $('.counter_request_additional_data input[name="price"]').on('change', function(){
        var minus_obj = $(this).siblings('.minus');
        var plus_obj = $(this).siblings('.plus');
        var min_price = 0;
        var max_price = 0;
        var cur_price = parseInt($(this).val().replace(' ', ''));
        var new_price = 0;

        if(!isNaN(cur_price)){
            new_price = cur_price;
        }

        if(minus_obj.length == 1 && plus_obj.length == 1){
            min_price = parseInt(minus_obj.attr('data-min'));
            max_price = parseInt(plus_obj.attr('data-max'));
            if(!isNaN(min_price)
                && !isNaN(max_price)
            ){
                if(new_price == 0 || new_price > max_price){
                    new_price = max_price;
                }else if(new_price < min_price) {
                    new_price = min_price;
                }
            }
        }
        if(isMobileMode() === false)
            $(this).val(number_format(new_price, 0, '.', ' '));
    });
});

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

//клик по минусу при установлении цены встречного предложения
function farmerClickCounterMinPrice(argObj){
    var wObj = $(argObj).siblings('input[name="price"]');
    if(wObj.length == 1){

        var min_price = parseInt($(argObj).attr('data-min'));
        var step_val = parseInt($(argObj).attr('data-step'));
        var cur_price = parseInt(wObj.val().replace(' ', ''));
        var new_price = 0;

        if(!isNaN(min_price)
            && !isNaN(step_val)
            && !isNaN(cur_price)
        ){
            new_price = cur_price - step_val;
            if(new_price < min_price){
                new_price = min_price;
            }

            wObj.val(number_format(new_price, 0, '.', ' '));
        }
    }
}

//клик по плюсу при установлении цены встречного предложения
function farmerClickCounterMaxPrice(argObj){
    var wObj = $(argObj).siblings('input[name="price"]');
    if(wObj.length == 1){

        var max_price = parseInt($(argObj).attr('data-max'));
        var step_val = parseInt($(argObj).attr('data-step'));
        var cur_price = parseInt(wObj.val().replace(' ', ''));
        var new_price = 0;

        if(!isNaN(max_price)
            && !isNaN(step_val)
            && !isNaN(cur_price)
        ){
            new_price = cur_price + step_val;
            if(new_price > max_price){
                new_price = max_price;
            }

            wObj.val(number_format(new_price, 0, '.', ' '));
        }
    }
}