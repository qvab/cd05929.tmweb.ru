var init_filter_set = true;

$(document).ready(function(){

    /*$('form[name="request_filter"]').on('submit', function(e){
        var prevent_send = 0;
        if($(this).find('select[name="farmer_id[]"]').val() == 0){
            $(this).find('select[name="farmer_id[]"]').remove();
            prevent_send++;
        }

        if($(this).find('select[name="culture"]').val() == 0){
            $(this).find('select[name="culture"]').remove();
            prevent_send++;
        }

        if(prevent_send == 2){
            e.preventDefault();
            document.location.href = '/agent/request/';
        }
    });*/

    /*$('.select_item select[name="select_farmer"]').on('change', function(){
        var link_href = document.location.href.toString();*/
//        var new_href = link_href.replace(/\?.*/g, '');
        /*var additional_href = '';

//        if(link_href.length != link_href.replace('status=all', '').length){
//            additional_href = 'status=all';
//        }
//        else if(link_href.length != link_href.replace('status=no', '').length){
//            additional_href = 'status=no';
//        }

        additional_href += (additional_href == '' ? '' : '&') + 'farmer_id[]=' + $(this).val();

        if(additional_href != ''){
            new_href += '?' + additional_href;
        }

        document.location.href = new_href;
    });*/

    agentRequestFilterInit();

    //сброс данных количеств склада при изменении фильтра культуры
    $('select[name="culture"]').on('change', function(){
        var filter_tab_obj = $('.tab_form.filter_area');
        var filter_count_obj = $('#filter_counts_data');
        var region_select = filter_tab_obj.find('select[name="region_id"]');
        var farmer_select = filter_tab_obj.find('select[name="farmer_id[]"]');
        var culture_select = $(this);
        var nds_select = filter_tab_obj.find('select[name="type_nds"]');

        var additional_filter = '', total_val = 0, cur_val = 0;

        //проверяем если установлен регион
        if(region_select.val() > 0){
            additional_filter += '[data-region="' + region_select.val() + '"]';
        }

        //проверяем если установлен пользователь
        if(farmer_select.val() > 0){
            additional_filter += '[data-farmer="' + farmer_select.val() + '"]';
        }

        //проверяем если установлена культура
        if(culture_select.val() > 0){
            additional_filter += '[data-culture="' + culture_select.val() + '"]';
        }

        //переустанавливаем значение для каждого склада
        nds_select.find('option').each(function(ndsInd, ndsObj){
            cur_val = 0;
            if(parseInt($(ndsObj).attr('value')) > 0){
                cur_val = filter_count_obj.find('.item[data-nds="' + $(ndsObj).attr('value') + '"]' + additional_filter).length;
                $(ndsObj).attr('data-cnt', cur_val);

                total_val += cur_val;
            }
        });
        nds_select.find('option[value="0"]').attr('data-cnt', total_val);

        //если начальная установка и задан фильтр склада, то устанавливаем значение (иначе сбрасываем на "все")
        if(init_filter_set
            && typeof filter_tab_obj.attr('data-nds') != 'undefined'
            && filter_tab_obj.attr('data-nds') != '0'
        ){
            nds_select.val(filter_tab_obj.attr('data-nds'));
        }else{
            nds_select.val(0);
        }

        nds_select.trigger('change.select2');
    });

    //сброс данных количеств культуры при изменении фильтра пользователя
    $('select[name="farmer_id[]"]').on('change', function(){
        var filter_tab_obj = $('.tab_form.filter_area');
        var filter_count_obj = $('#filter_counts_data');
        var region_select = filter_tab_obj.find('select[name="region_id"]');
        var farmer_select = $(this);
        var culture_select = filter_tab_obj.find('select[name="culture"]');

        var additional_filter = '', total_val = 0, cur_val = 0;

        //проверяем если установлен регион
        if(region_select.val() > 0){
            additional_filter += '[data-region="' + region_select.val() + '"]';
        }

        //проверяем если установлен пользователь
        if(farmer_select.val() > 0){
            additional_filter += '[data-farmer="' + farmer_select.val() + '"]';
        }

        //переустанавливаем значение для каждой культуры
        culture_select.find('option').each(function(cultInd, cultObj){
            cur_val = 0;
            if(parseInt($(cultObj).attr('value')) > 0){
                cur_val = filter_count_obj.find('.item[data-culture="' + $(cultObj).attr('value') + '"]' + additional_filter).length;
                $(cultObj).attr('data-cnt', cur_val);

                total_val += cur_val;
            }
        });
        culture_select.find('option[value="0"]').attr('data-cnt', total_val);

        //если начальная установка и задан фильтр культуры, то устанавливаем значение (иначе сбрасываем на "все")
        if(init_filter_set
            && typeof filter_tab_obj.attr('data-culture') != 'undefined'
            && filter_tab_obj.attr('data-culture') != '0'
        ){
            culture_select.val(filter_tab_obj.attr('data-culture'));
        }else{
            culture_select.val(0);
        }

        culture_select.trigger('change.select2');
        culture_select.trigger('change'); //отправляем факт изменения к следующему фильтру - фильтру культуры
    });

    //сброс данных количеств пользователей при изменении фильтра региона
    $('select[name="region_id"]').on('change', function(){
        var filter_tab_obj = $('.tab_form.filter_area');
        var filter_count_obj = $('#filter_counts_data');
        var region_select = $(this);
        var farmer_select = filter_tab_obj.find('select[name="farmer_id[]"]');

        var additional_filter = '', total_val = 0, cur_val = 0;

        //проверяем если установлен регион
        if(region_select.val() > 0){
            additional_filter += '[data-region="' + region_select.val() + '"]';
        }

        //переустанавливаем значение для каждого покупателя
        farmer_select.find('option').each(function(fInd, fObj){
            cur_val = 0;
            if(parseInt($(fObj).attr('value')) > 0){
                cur_val = filter_count_obj.find('.item[data-farmer="' + $(fObj).attr('value') + '"]' + additional_filter).length;
                $(fObj).attr('data-cnt', cur_val);

                total_val += cur_val;
            }
        });
        farmer_select.find('option[value="0"]').attr('data-cnt', total_val);

        //если начальная установка и задан фильтр пользователей, то устанавливаем значение (иначе сбрасываем на "все")
        if(init_filter_set
            && typeof filter_tab_obj.attr('data-farmer') != 'undefined'
            && filter_tab_obj.attr('data-farmer') != '0'
        ){
            farmer_select.val(filter_tab_obj.attr('data-farmer'));
        }else{
            farmer_select.val(0);
        }

        farmer_select.trigger('change.select2');
        farmer_select.trigger('change'); //отправляем факт изменения к следующему фильтру - фильтру пользователей
    });

    //начальное выставление данных количеств в фильтре
    var filter_tab_obj = $('.tab_form.filter_area');
    var filter_count_obj = $('#filter_counts_data');
    if(filter_tab_obj.length == 1
        && filter_count_obj.length == 1
    ){
        var additional_filter = '', total_val = 0, cur_val = 0;
        var region_select = filter_tab_obj.find('select[name="region_id"]');

        //если фильтр агента, то устанавливаем регион и и инциализируем событие изменения фильтра региона
        if(region_select.length == 1){
            //устанавливаем количества для фильтра региона
            region_select.find('option').each(function(rInd, rObj){
                cur_val = 0;
                if(parseInt($(rObj).attr('value')) > 0){
                    cur_val = filter_count_obj.find('.item[data-region="' + $(rObj).attr('value') + '"]').length;
                    $(rObj).attr('data-cnt', cur_val);

                    total_val += cur_val;
                }
            });
            region_select.find('option[value="0"]').attr('data-cnt', total_val);

            //если задан фильтр региона, то устанавливаем значение
            if(typeof filter_tab_obj.attr('data-region') != 'undefined'
                && filter_tab_obj.attr('data-region') != '0'
            ){
                region_select.val(filter_tab_obj.attr('data-region'));
            }
            //обновляем данные в фильтре регионов (и далее срабатывает событие обновления в фильтре пользователей и далее в остальных фильтрах)
            region_select.trigger('change.select2');
            region_select.trigger('change');
        }
        //если фильтр пользователя, то устанавливаем культуру и инциализируем событие изменения фильтра культуры
        else{
            var culture_select = filter_tab_obj.find('select[name="culture"]');

            //устанавливаем количества для фильтра культур
            culture_select.find('option').each(function(cultInd, cultObj){
                cur_val = 0;
                if(parseInt($(cultObj).attr('value')) > 0){
                    cur_val = filter_count_obj.find('.item[data-culture="' + $(cultObj).attr('value') + '"]').length;
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
            //обновляем данные в фильтре культур (и далее срабатывает событие обновления в фильтре складов)
            culture_select.trigger('change.select2');
            culture_select.trigger('change');
        }

        init_filter_set = false; //после установки фильтров отмечаем, что начальные условия для фильтров заданы
    }
});


function agentRequestFilterInit() {
    //проверка/установка значений фильтра
    var wFilterObj = '';
    var tabFilterSuf = '';
    var tabFilterObj = $('form[name="request_filter"]');
    var workCookieName = '';
    if(tabFilterObj.length == 1){

        //сохраняем значение данных при применении фильтра
        $('form[name="request_filter"] .submit-btn[type="submit"]').on('click', function(){

            workCookieName = 'agent_request_region_id';
            wFilterObj = $('form[name="request_filter"] select[name="region_id"]');
            if(wFilterObj.length == 1){
                setCookie(workCookieName, wFilterObj.val(), 0);
            }

            workCookieName = 'agent_request_farmer_id';
            wFilterObj = $('form[name="request_filter"] select[name="farmer_id[]"]');
            if(wFilterObj.length == 1){
                setCookie(workCookieName, wFilterObj.val(), 0);
            }

            workCookieName = 'agent_request_culture';
            wFilterObj = $('form[name="request_filter"] select[name="culture"]');
            if(wFilterObj.length == 1){
                setCookie(workCookieName, wFilterObj.val(), 0);
            }

            workCookieName = 'agent_request_type_nds';
            wFilterObj = $('form[name="request_filter"] select[name="type_nds"]');
            if(wFilterObj.length == 1){
                setCookie(workCookieName, wFilterObj.val(), 0);
            }
        });
        //сбрасываем данные при сбросе фильтра
        $('form[name="request_filter"] .cancel_filter').on('click', function(){
            workCookieName = 'agent_request_region_id'; setCookie(workCookieName, '0', 0);
            workCookieName = 'agent_request_farmer_id'; setCookie(workCookieName, '0', 0);
            workCookieName = 'agent_request_culture';   setCookie(workCookieName, '0', 0);
            workCookieName = 'agent_request_type_nds';  setCookie(workCookieName, '0', 0);
        });
    }
}