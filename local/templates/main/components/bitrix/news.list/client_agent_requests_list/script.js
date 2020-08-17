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

    $('.add_blue_button.inactive').on('click', function(e){
        e.preventDefault();
    });
    $('.submit-btn.inactive').on('click', function(e){
        e.preventDefault();
    });

    //open active request
    var url_params1 = document.location.href.toString().split('request_id=');
    if(typeof url_params1[1] != 'undefined'){
        var url_params2 = url_params1[1].split('#');
        if(typeof url_params2[0] != 'undefined'
            && url_params2[0].replace(/[0-9]+/,'').length == 0
            && $('.list_page_rows.requests .line_area[data-elid="' + url_params2[0] + '"]').length == 1
        ){
            var wObj = $('.list_page_rows.requests .line_area[data-elid="' + url_params2[0] + '"]');
            wObj.find('.line_inner').trigger('click');
            var wObjOffset = wObj.offset();
            setTimeout(function(){ $(document).scrollTop(wObjOffset.top); }, 310);
        }
    }

    $('.req_prolongation').on('click', function(){

        var curButObj = $(this);
        var reqObj = curButObj.parents('.line_additional'). find('input[type="hidden"][name="request"]');
        if(reqObj.length == 1 && parseInt(reqObj.val())){
            $.post('/ajax/prolongate_request.php', {
                r_id: reqObj.val()
            }, function(mes){
                var obj = $.parseJSON(mes);
                var res = obj.result;

                if(res == 1){
                    //продлен неактивный запрос (создан новый запрос)
                    location.href = '/client_agent/request/?q='+obj.num+'&bestPrice='+obj.best;
                }else if(checkCorrectInt(res) && res > 0){
                    //продлен активный запрос
                    curButObj.parents('.prolongate_area').remove();

                    var q = obj.num;
                    var bestPrice = obj.best;

                    var mess = '';
                    if (q < 1) {
                        mess = '. На ваш запрос не найдено ни одно предложение'
                    } else {
                        mess = '. Ваш запрос ' + flex(q) + ', для ' + bestPrice + ' - лучшая цена';
                    }

                    setTimeout(function(){
                        alert('Запрос продлён' + mess);
                    }, 10);

                } else if(res == 0){
                    alert('Не удалось продлить запрос. Склады запроса не активны или удалены.');
                }
            });
        }
    });
});

function flex(k) {
    var fl;
    if (k%10==1 && k%100!=11) fl = 'получил ' + k + ' агропроизводитель';
    else if ((k%10==2 && k%100!=12) || (k%10==3 && k%100!=13) || (k%10==4 && k%100!=14)) fl = 'получило ' + k + ' агропроизводителя';
    else fl = 'получило ' + k + ' агропроизводителей';
    return fl;
}

function secondTimesFormat(ms){
    var timeStruct = 0;
    timeStruct = millisecToTimeStruct(ms);
    formattedString = toFormattedStr(timeStruct);
    return formattedString;
}

// Из миллисекунд в объект с кол-вом дней, часов, минут, секунд. Например: 22 д. 10 ч. 60 с.
function millisecToTimeStruct(ms) {
    var d, h, m, s;
    if (isNaN(ms)) {
        return {};
    }
    d = ms / (1000 * 60 * 60 * 24);
    h = (d - ~~d) * 24;
    m = (h - ~~h) * 60;
    s = (m - ~~m) * 60;
    return {d: ~~d, h: ~~h, m: ~~m, s: ~~s};
}

// форматирует вывод
function toFormattedStr(tStruct){
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