$(document).ready(function(){
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
            ' по прямому контракту покупателю с рейтингом ' + elemObj.attr('data-rating') +
            ' баллов по цене договора ' + elemObj.attr('data-bcprice') +
            ' р/тн на воротах покупателя, находящихся в ' + elemObj.attr('data-km') +
            ' км от Вас. Также предлагаю перевозку, которая ориентировочно обойдется ' + elemObj.attr('data-tarif') +
            ' р/тн. Цена с места за Ваше качество при этом прогнозируется ' + elemObj.attr('data-cmprice') +
            ' р/тн. и будет действительна еще ' + left_time + '.';
    }else{
        result = 'Предлагаю продать ' + elemObj.attr('data-name') +
            ' по прямому контракту покупателю с рейтингом '+ elemObj.attr('data-rating') +
            ' баллов по цене договора ' + elemObj.attr('data-bcprice') +
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