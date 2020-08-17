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
    //обновление оставшегося времени
    setInterval(function(){
        $('div.date').each(function(i,elem) {
            if(typeof $(elem).attr('data-active_to-second') != 'undefined')
            {
                var act_second = $(elem).attr('data-active_to-second');
                act_second = act_second*1000;
                var timeDate = new Date();
                var timeMs = timeDate.getTime();
                if(act_second - timeMs < 0)
                {
                    $(elem).text('');
                }
                else
                {
                    var diff_ms =  act_second - timeMs;
                    var f_date = secondTimesFormat(diff_ms);
                    $(elem).text(f_date);
                }
            }
        });
    }, 30000);

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
});
