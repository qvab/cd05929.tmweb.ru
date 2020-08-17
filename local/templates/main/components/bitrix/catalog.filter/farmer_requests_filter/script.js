$(document).ready(function(){
    $('.submit-btn[type="submit"]').on('click', function(e) {
        var selectObj;
        $(this).parents('form').find('select').each(function(ind, sObj){
            selectObj = $(sObj);
            if (selectObj.val() == 0) {
                selectObj.attr('disabled', 'disabled');
            }
            else {
                selectObj.removeAttr('disabled');
            }
        });
    });

    farmerRequestFilterInit();


    //сброс данных количеств склада при изменении фильтра культуры
    $('select[name="culture"]').on('change', function(){
        var filter_tab_obj = $('.tab_form.filter_area');
        var filter_count_obj = $('#filter_counts_data');
        var culture_select = $(this);
        var wh_select = filter_tab_obj.find('select[name="wh"]');

        var additional_filter = '', total_val = 0, cur_val = 0;

        //проверяем если установлена культура
        if(culture_select.val() > 0){
            additional_filter += '[data-culture="' + culture_select.val() + '"]';
        }

        //переустанавливаем значение для каждого склада
        wh_select.find('option').each(function(whInd, whObj){
            cur_val = 0;
            if(parseInt($(whObj).attr('value')) > 0){
                cur_val = filter_count_obj.find('.item[data-wh="' + $(whObj).attr('value') + '"]' + additional_filter).length;
                $(whObj).attr('data-cnt', cur_val);

                total_val += cur_val;
            }
        });
        wh_select.find('option[value="0"]').attr('data-cnt', total_val);
        wh_select.val(0);

        wh_select.trigger('change.select2');
    });

    //начальное выставление данных количеств в фильтре
    var filter_tab_obj = $('.tab_form.filter_area');
    var filter_count_obj = $('#filter_counts_data');
    if(filter_tab_obj.length == 1
        && filter_count_obj.length == 1
    ){
        var additional_filter = '', total_val = 0, cur_val = 0;

        //если фильтр агента, то устанавливаем регион и и инциализируем событие изменения фильтра региона
        if(filter_tab_obj.find('select[name="region"]').length == 1){
            if(typeof filter_tab_obj.attr('data-farmer') != 'undefined'
                && filter_tab_obj.attr('data-farmer') != '0'
            ){
                additional_filter += '[data-farmer="' + filter_tab_obj.attr('data-farmer') + '"]';
            }
        }
        //если фильтр пользователя, то устанавливаем культуру и инциализируем событие изменения фильтра культуры
        else{
            var culture_select = filter_tab_obj.find('select[name="culture"]');

            //устанавливаем количества для фильтра культур
            culture_select.find('option').each(function(cultInd, cultObj){
                cur_val = 0;
                if(parseInt($(cultObj).attr('value')) > 0){
                    cur_val = filter_count_obj.find('.item[data-culture="' + $(cultObj).attr('value') + '"]' + additional_filter).length;
                    $(cultObj).attr('data-cnt', cur_val);

                    total_val += cur_val;
                }
            });
            culture_select.find('option[value="0"]').attr('data-cnt', total_val);

            //если задан фильтр культуры, то устанавливаем значение
            if(typeof filter_tab_obj.attr('data-culture') != 'undefined'
                && filter_tab_obj.attr('data-culture') != '0'
            ){
                culture_select.val(filter_tab_obj.attr('data-culture'));
            }
            //обновляем данные в фильтре складов
            culture_select.trigger('change.select2');
            culture_select.trigger('change');

            //если задан фильтр склада, то устанавливаем значение
            if(typeof filter_tab_obj.attr('data-wh') != 'undefined'
                && filter_tab_obj.attr('data-wh') != '0'
            ){
                var wh_select = filter_tab_obj.find('select[name="wh"]');
                wh_select.val(filter_tab_obj.attr('data-wh'));
                wh_select.trigger('change.select2');
            }
        }
    }
});

function farmerRequestFilterInit() {
    //проверка/установка значений фильтра
    var wFilterObj = '';
    var FilterObj = $('form[name="farmer_requests_filter"]');
    var workCookieName = '';
    if(FilterObj.length == 1){
        //сохраняем значение данных при применении фильтра
        $('form[name="farmer_requests_filter"] .submit-btn[type="submit"]').on('click', function(){
            workCookieName = 'farmer_requests_culture';
            wFilterObj = $('form[name="farmer_requests_filter"] select[name="culture"]');
            if(wFilterObj.length == 1){
                setCookie(workCookieName, wFilterObj.val(), 3);
            }
            workCookieName = 'farmer_requests_wh';
            wFilterObj = $('form[name="farmer_requests_filter"] select[name="wh"]');
            if(wFilterObj.length == 1){
                setCookie(workCookieName, wFilterObj.val(), 3);
            }
        });
        //сбрасываем данные при сбросе фильтра
        $('form[name="farmer_requests_filter"] .cancel_filter').on('click', function(){
            workCookieName = 'farmer_requests_culture';
            setCookie(workCookieName, '0', 3);
            workCookieName = 'farmer_requests_wh';
            setCookie(workCookieName, '0', 3);
        });
    }
}
