var graphShowMode = 0, loadindGraphAjax = 0;
var graphsObj = {};
var selectedPointId = 0;

$(document).ready(function(){
    $('#client_requests_filter .wrap-btn .reset').click(function () {
        $('#client_requests_filter select').prop('selectedIndex',0).trigger('change');
        $('#client_requests_filter').submit();
    });

    $( '.list_page_rows.counter_request_client_list .price_difference').tooltip({
        content: function () {
            return $(this).prop('title');
        },
        position: {
            my: "center bottom-20",
            at: "center center",
            using: function( position, feedback ) {
                $( this ).css( position );
                $( "<div>" )
                    .addClass( "arrow" )
                    .addClass( feedback.vertical )
                    .addClass( feedback.horizontal )
                    .appendTo( this );
            }
        },
        hide: {
            delay: 500,
            duration: 500
        },
        close: function(event, ui)
        {
            ui.tooltip.hover(function()
                {
                    $(this).stop(true).fadeTo(400, 1);
                },
                function()
                {
                    $(this).fadeOut('400', function()
                    {
                        $(this).remove();
                    });
                });
        }
    });

    //проверка/установка значений фильтра
    var wFilterObj = '';
    var workCookieName = '';

    //сохраняем значение данных при применении фильтра
    $('#client_requests_filter .submit-btn[type="submit"]').on('click', function(){

        workCookieName = 'count_req_filter_warehouse';
        wFilterObj = $('#client_requests_filter select[name="warehouse_id"]');
        if(wFilterObj.length == 1){
            setCookie(workCookieName, wFilterObj.val(), 0);
        }

        workCookieName = 'count_req_filter_culture';
        wFilterObj = $('#client_requests_filter select[name="culture_id"]');
        if(wFilterObj.length == 1){
            setCookie(workCookieName, wFilterObj.val(), 0);
        }

        if($('#client_requests_filter select[name="client_id"]').length>0){
            workCookieName = 'count_req_filter_client';
            wFilterObj = $('#client_requests_filter select[name="client_id"]');
            if(wFilterObj.length == 1){
                setCookie(workCookieName, wFilterObj.val(), 0);
            }
        }
        if($('#client_requests_filter select[name="region_id"]').length>0){
            workCookieName = 'count_req_filter_region';
            wFilterObj = $('#client_requests_filter select[name="region_id"]');
            if(wFilterObj.length == 1){
                setCookie(workCookieName, wFilterObj.val(), 0);
            }
        }
    });
    //сбрасываем данные при сбросе фильтра
    $('#client_requests_filter .submit-btn.reset').on('click', function(){
        workCookieName = 'count_req_filter_warehouse';
        setCookie(workCookieName, '0', 0);
        workCookieName = 'count_req_filter_culture';
        setCookie(workCookieName, '0', 0);
        workCookieName = 'count_req_filter_client';
        setCookie(workCookieName, '0', 0);
        workCookieName = 'count_req_filter_region';
        setCookie(workCookieName, '0', 0);
    });

    //вывод кнопки для графика
    var nds_obj = $('.user_nds_value'); //определим тип ндс пользователя
    var nds_postfix = '';
    if(nds_obj.length == 1
        && typeof nds_obj.attr('data-nds') !== 'undefined'
    ){
        nds_postfix = (nds_obj.attr('data-nds') === 'y' ? '_nds' : '_no_nds');
    }
    var check_graph_obj = $('#graph_my_market' + nds_postfix), check_graph_obj2 = $('#graph_my_req' + nds_postfix), check_graph_obj3 = $('#graph_my_deals' + nds_postfix);
    if(check_graph_obj.length == 1 && check_graph_obj.attr('data-val') != ''
        || check_graph_obj2.length == 1 && check_graph_obj2.attr('data-val') != ''
        || check_graph_obj3.length == 1 && check_graph_obj3.attr('data-val') != ''
    ){
        $('.show_hide_counter_graph_container').addClass('active');
        //отображение нужного графика, если требуется
        if(typeof show_graph_flag !== 'undefined'
            && typeof show_graph_type !== 'undefined'
            && typeof show_graph_nds !== 'undefined'
        ){
            var draw_object = $('#draw_object');
            $('#client_requests_filter').attr('data-graphmode', show_graph_type);
            var nds_value = (show_graph_nds == 'y');

            //получаем нтип НДС пользователя
            var client_nds_obj = $('.user_nds_value');
            var client_nds_value = false;
            if(nds_obj.length == 1
                && typeof client_nds_obj.attr('data-nds') !== 'undefined'
            ){
                client_nds_value = (client_nds_obj.attr('data-nds') == 'y');
            }

            if(nds_value == client_nds_value){
                //если требуемый НДС совпадает с НДС пользователя - отображаем график обычным образом
                showClientGraph(1);
            }else{
                //если требуемый НДС несовпадает с НДС пользователя - отображаем график через "переключение" типа НДС (отправляется ajax-запрос для получения данных)

                //устанавливаем тип НДС для контейнеров по умолчанию
                draw_object.find('.graph_area').attr('data-nds', (client_nds_value ? 'y' : 'n'));

                //добавляем переключение режима "с НДС / без НДС"
                draw_object.prepend('<div class="nds_change_area radio_area"><input type="checkbox" data-text="с НДС" name="nds_change" onclick="changeGraphNDSMode(this);"' + (!nds_value ? ' checked="checked"' : '') + ' /></div>');
                var inpObj = draw_object.find('.nds_change_area input');
                if (typeof inpObj.attr('data-text') !== 'undefined' && inpObj.attr('data-text').length != inpObj.attr('data-text').replace('checkbox_href', '').length) {
                    inpObj.after('<div class="custom_data_text">' + inpObj.attr('data-text') + '</div><div class="custom_input checkbox' + (inpObj.prop('checked') === true ? ' checked' : '') + '" data-name="' + inpObj.attr('name') + '"><div class="ico"></div></div>');
                } else {
                    inpObj.after('<div class="custom_input checkbox' + (inpObj.prop('checked') === true ? ' checked' : '') + '" data-name="' + inpObj.attr('name') + '"><div class="ico"></div>' + (typeof inpObj.attr('data-text') !== 'undefined' ? inpObj.attr('data-text') : '') + '</div>');
                }
                inpObj.addClass('customized');
                draw_object.attr('data-curnds', show_graph_nds);

                //инициализируем переключение
                inpObj.trigger('click');
            }
        }
        //отображаем график, если ранее он был раскрытым
        else if(getCookie('client_counter_graph') == '1'){
            showClientGraph(1);
        }
    }else if($('#no_graph_without_filter').length == 1){

        if($('.show_hide_counter_graph_container').hasClass('small')) {
            var text_val = 'Выберите склад и культуру <br>для отображения графика';
        } else {
            var text_val = 'Выберите склад и культуру для отображения графика';
        }
        // if($('.tab_form.client_counter_requests select[name="client_id"]').length == 1){
        //     text_val = 'Выберите покупателя, склад и культуру для отображения графика';
        // }
        $('.show_hide_counter_graph_container').html('<div class="text_container">' + text_val + '</div>').addClass('active nofilter');
    }

    /**
     * Получение значение выбранного фильтра
     * @param filter_name - имя фильтра
     */
    function getFilterSelectVal(filter_name){
        var result = 0;
        var filter_select = $('.tab_form.client_counter_requests select[name="'+filter_name+'"]');
        if(filter_select.length == 1 && parseInt(filter_select.val()) != 0){
            result = filter_select.val();
        }
        return result;
    }


    var select_data_names = {
        'region_id':'data-region',
        'client_id':'data-uid',
        'culture_id':'data-culture',
        'warehouse_id':'data-wh',
    }

    function filterInit() {
        var select_arr = ['region_id','client_id','culture_id','warehouse_id'];
        getFilterCnt('',select_arr);
    }

    //обработка значение фильтра (подсчет счетчиков)
    function getFilterCnt(select_filter,other_filters) {
        var additional_selector = '';
        var filter_additional = []; //массив с селекторами для выборки данных по фильтрам
        var select_val = 0;
        var cur_val = 0;
        var tot_val = 0;
        var tmp_select = '';
        //обходим все фильтры и получаем для них значения
        for(var item in other_filters){
            tmp_select = other_filters[item];
            additional_selector = ''; //селектор для фильтра на основе уже выбранных значений
            for(var sub_item in other_filters){
                if(other_filters[sub_item] != tmp_select){
                    select_val = getFilterSelectVal(other_filters[sub_item]);
                    //если это не текущий выбранный фильтр
                    if(select_val>0){
                        additional_selector += '['+select_data_names[other_filters[sub_item]]+'="' + select_val + '"]';
                    }
                }
            }
            filter_additional[tmp_select] = additional_selector;
        }
        for (var filter in filter_additional) {
            //console.log(filter+'  =>  '+filter_additional[filter]);
            tot_val = 0;
            if(filter!=select_filter){
                $('.tab_form.client_counter_requests select[name="'+filter+'"]').find('option').each(function(cultInd, cultObj){
                    if($(cultObj).attr('value') != ''
                        && $(cultObj).attr('value') != '0'
                    ) {
                        cur_val = 0;
                        $('.filter_wh_count_data div' + filter_additional[filter] + '['+select_data_names[filter]+'="' + $(cultObj).attr('value') + '"]').each(function (cInd, cObj) {
                            cur_val += parseInt($(cObj).attr('data-cnt'));
                        });
                        $(cultObj).attr('data-cnt', cur_val);
                        tot_val += cur_val;
                    }
                });
                //установка значения по умолчанию (все элементы)
                var filter_option =  $('.tab_form.client_counter_requests select[name="'+filter+'"]').find('option[value="0"]');
                if(filter_option.length == 1){
                    filter_option.attr('data-cnt', tot_val);
                }
                //сбрасывам фильтр, там где по выбранным значениям cnt = 0
                select_val = getFilterSelectVal(filter);
                if($('.tab_form.client_counter_requests select[name="'+filter+'"]').find('option[value="'+select_val+'"]').length == 1){
                    if(parseInt($('.tab_form.client_counter_requests select[name="'+filter+'"]').find('option[value="'+select_val+'"]').attr('data-cnt'))>0){
                        $('.tab_form.client_counter_requests select[name="'+filter+'"]').val(select_val);
                    }else{
                        $('.tab_form.client_counter_requests select[name="'+filter+'"]').val(0);
                    }
                }else{
                    $('.tab_form.client_counter_requests select[name="'+filter+'"]').val(0);
                }
                //обновление отображения select2
                if(filter!=select_filter){
                    $('.tab_form.client_counter_requests select[name="'+filter+'"]').trigger('change.select2');
                }
            }
        }
    }

    $('.tab_form.client_counter_requests select').on('change',function () {
        var this_name = $(this).attr('name');
        var select_arr = ['region_id','client_id','culture_id','warehouse_id'];
        getFilterCnt(this_name,select_arr);
    })

    filterInit();


    $('#draw_object .graph_area_tab').on('click', function(){
        if(!$(this).hasClass('active') 
            && loadindGraphAjax === 0
        ){
            $('#client_requests_filter').attr('data-graphmode', $(this).attr('data-viewmode'));
            showClientGraph(1);
        }
    });
});

//показывает график (строит его, если он не был построен ранее и есть даныне для построения)
function showClientGraph(argMode) {

    graphShowMode = argMode; //режим перключения вкладок

    //получаем активный режим показа (год/месяц/неделя)
    var filterObj = $('#client_requests_filter');
    var grahObjArea = $('#draw_object');
    if(grahObjArea.hasClass('show') && graphShowMode == 0) {
        grahObjArea.removeClass('show');
        if($('.graph_href_with_parameters').length > 0) {
            $('.graph_href_with_parameters').removeClass('active');
        }
        $('#draw_object_block').css('height','auto');
        scrollStuck();
        setCookie('client_counter_graph', '0', 3);
    }else{
        grahObjArea.addClass('show');
        if($('.graph_href_with_parameters').length > 0) {
            $('.graph_href_with_parameters').addClass('active');
        }
        var additional_selector = '[data-viewmode="' + filterObj.attr('data-graphmode') + '"]';

        //проверяем указан ли режим ндс для графика (если не указан, то предполагается, что график строится первый раз, а не переключается)
        if(typeof grahObjArea.attr('data-curnds') !== 'undefined'){
            additional_selector += '[data-nds="' + grahObjArea.attr('data-curnds') + '"]';
        }

        var graphObj = grahObjArea.find('.graph_area' + additional_selector);

        //проверяем не был ли ещё построен требуемый график
        if (graphObj.length == 1
            && graphObj.find('.highcharts-container').length == 0
        ) {
            plotCountReqGraph(graphObj);
        }

        graphObj.siblings('.active').each(function (cInd, cObj) {
            $(cObj).removeClass('active');
        });
        graphObj.addClass('active');
        graphObj.siblings('.graph_area_tab[data-viewmode="' + filterObj.attr('data-graphmode') + '"]').addClass('active');
        $('#draw_object_block').css('height',$('#draw_object').outerHeight()+'px');
        scrollStuck();
        setCookie('client_counter_graph', '1', 3);
    }
}

//строит график для встречного предложения
function plotCountReqGraph(wObj){

    //проверяем - не обработана ли еще область графика
    if(wObj.find('.highcharts-container').length == 0) {

        //установка текста на кнопке показа/скрывания графика
        // var butObj = $('.show_hide_counter_graph');
        // if (wObj.hasClass('active')) {
        //     butObj.text(butObj.attr('date-texthide'));
        // } else {
        //     butObj.text(butObj.attr('date-textshow'));
        // }

        var gData1 = [], gData2 = [], gData3 = [], gData4 = [], catList = [];
        var tempData, tempData2 = [], tempData3 = [];
        var start_days = 0, temp_val2 = 0, temp_date = 0;

        //определяем текущий тип ндс
        var nds_filter = '';
        var nds_code = 'n';
        var draw_object = $('#draw_object');
        if(typeof draw_object.attr('data-curnds') !== 'undefined'){
            //если ндс уже установлен
            nds_filter = (draw_object.attr('data-curnds') === 'y' ? '_nds' : '_no_nds');
            nds_code = draw_object.attr('data-curnds');
        }else{
            //если первый запуск
            if(typeof show_graph_flag !== 'undefined'
                && typeof show_graph_type !== 'undefined'
                && typeof show_graph_nds !== 'undefined'
            ){
                nds_filter = (show_graph_nds === 'y' ? '_nds' : '_no_nds');
                nds_code = show_graph_nds;
            }else {
                var nds_obj = $('.user_nds_value');
                if (nds_obj.length == 1
                    && typeof nds_obj.attr('data-nds') !== 'undefined'
                ) {
                    nds_filter = (nds_obj.attr('data-nds') === 'y' ? '_nds' : '_no_nds');
                    nds_code = nds_obj.attr('data-nds');
                }
            }
        }

        //данные для категорий
        var graphDatObj = $('#graph_my_categories' + nds_filter);
        var filter_obj = $('#client_requests_filter');

        //получаем ограничение для режима года/месяца/недели
        var restriction_date = $('#date_restrictions').attr('data-' + filter_obj.attr('data-graphmode'));
        var restriction_date_split = restriction_date.split('.');
        var restriction_date_val = parseInt(restriction_date_split[0] + restriction_date_split[1] + restriction_date_split[2]) - 1;

        if (graphDatObj.length == 1) {
            tempData = graphDatObj.attr('data-val').split(';');
            //переводим даты в последовательный набор чисел (1, 2, 3...)
            for (var i = 0; i < tempData.length; i++) {
                tempData3 = tempData[i].split('.');
                //берем только даты, которые отвечают ограничению режима года/месяца/недели
                if(restriction_date_val < parseInt(tempData3[2] + tempData3[1] + tempData3[0])) {
                    temp_date = new Date(tempData3[2] + '-' + tempData3[1] + '-' + tempData3[0] + 'T05:00:00');
                    if (start_days == 0) {
                        start_days = Math.ceil(temp_date.getTime() / 86400000);
                    }
                    catList.push(tempData[i]);
                }
            }
        }
        if(catList.length == 9){
            catList.splice(-1,1);
        }

        //данные для графика "Мои цены"
        graphDatObj = $('#graph_my_req'+ nds_filter);
        if (graphDatObj.length == 1) {
            tempData = graphDatObj.attr('data-val').split(';');
            for (var i = 0; i < tempData.length; i++) {
                tempData2 = tempData[i].split(',');
                tempData3 = tempData2[0].split('.');
                //берем только данные, которые отвечают ограничению режима года/месяца/недели
                if(restriction_date_val < parseInt(tempData3[2] + tempData3[1] + tempData3[0])) {
                    temp_date = new Date(tempData3[2] + '-' + tempData3[1] + '-' + tempData3[0] + 'T05:00:00');
                    gData1.push({
                        name:  tempData2[0] + '<span class="go_button btn" onclick="goFromGraph(' + tempData2[2] + ');">Изменить запрос</span>' + '<span class="go_label">Клик для изменения запроса</span>',
                        color: '#ff0000',
                        x: Math.ceil(temp_date.getTime() / 86400000) - start_days,
                        y: parseInt(tempData2[1]),
                        additval: tempData2[2],
                        events: {
                            click: function(wObj, e){
                                if(!isMobileDevice()){
                                    //для обычных компьютеров переходим на страницу
//                                    goFromGraph(this.additval);
                                }else{
                                    //для мобильных устройств дополнительно отображаем кнопку перехода
                                }
                            }
                        }
                    });
                }
            }
        }

        //данные для графика "Рынок"
        graphDatObj = $('#graph_my_market' + nds_filter);
        if (graphDatObj.length == 1) {
            tempData = graphDatObj.attr('data-val').split(';');
            for (var i = 0; i < tempData.length; i++) {
                tempData2 = tempData[i].split(',');
                tempData3 = tempData2[0].split('.');
                //берем только данные, которые отвечают ограничению режима года/месяца/недели
                if(restriction_date_val < parseInt(tempData3[2] + tempData3[1] + tempData3[0])) {
                    temp_date = new Date(tempData3[2] + '-' + tempData3[1] + '-' + tempData3[0] + 'T05:00:00');
                    gData2.push({
                        name: tempData2[0],
                        color: '#5CB100',
                        x: Math.ceil(temp_date.getTime() / 86400000) - start_days,
                        y: parseInt(tempData2[1])
                    });
                }
            }
        }

        //данные для графика "Мои сделки" (для цикла используем catList т.к. он содержит все даты, это позволит корректно отображать разрывы в пропущенных датах)
        graphDatObj = $('#graph_my_deals' + nds_filter);
        if (graphDatObj.length == 1) {
            tempData = graphDatObj.attr('data-val').split(';');
            if (tempData.length > 0) {
                //строим график с разрывами (бежим по всем имеющимся датам)
                var found_date = false;
                for (var i = 0; i < catList.length; i++) {
                    found_date = false;
                    for (var j = 0; j < tempData.length; j++) {
                        tempData2 = tempData[j].split(',');
                        tempData3 = tempData2[0].split('.');
                        //берем только данные, которые отвечают ограничению режима года/месяца/недели
                        if(restriction_date_val < parseInt(tempData3[2] + tempData3[1] + tempData3[0])){
                            if (tempData2[0] == catList[i]) {
                                //найдено значение в списке дней -> строим точку
                                temp_date = new Date(tempData3[2] + '-' + tempData3[1] + '-' + tempData3[0] + 'T05:00:00');
                                gData3.push({
                                    name: tempData2[0],
                                    color: '#ff0000',
                                    x: Math.ceil(temp_date.getTime() / 86400000) - start_days,
                                    y: parseInt(tempData2[1])
                                });
                                found_date = true;
                                break;
                            }
                        }
                    }

                    //разрыв в графике
                    if(!found_date){
                        tempData3 = catList[i].split('.');
                        temp_date = new Date(tempData3[2] + '-' + tempData3[1] + '-' + tempData3[0] + 'T05:00:00');
                        gData3.push({
                            name: '',
                            color: '#ff0000',
                            x: Math.ceil(temp_date.getTime() / 86400000) - start_days,
                            y: null
                        });
                    }
                }
            }
        }
        //данные для графика "Спрос"
        graphDatObj = $('#graph_average_price' + nds_filter);
        if (graphDatObj.length == 1) {
            tempData = graphDatObj.attr('data-val').split(';');
            for (var i = 0; i < tempData.length; i++) {
                tempData2 = tempData[i].split(',');
                tempData3 = tempData2[0].split('.');
                //берем только данные, которые отвечают ограничению режима года/месяца/недели
                if(restriction_date_val < parseInt(tempData3[2] + tempData3[1] + tempData3[0])) {
                    temp_date = new Date(tempData3[2] + '-' + tempData3[1] + '-' + tempData3[0] + 'T05:00:00');
                    gData4.push({
                        name: tempData2[0],
                        color: '#693A22',
                        x: Math.ceil(temp_date.getTime() / 86400000) - start_days,
                        y: parseInt(tempData2[1])
                    });
                }
            }
        }

        //вносим данные в график
        if (gData3.length > 0
            || gData2.length > 1
            || gData1.length > 0
        ) {
            var SeriesData = [];
            if (gData2.length > 1) {
                SeriesData.push({
                    data: gData2,
                    name: 'Рынок',
                    color: '#5CB100',
                    fillOpacity: 0.5,
                    type: 'spline',
                    marker: {
                        enabled: false
                    }
                });
            }

            if (gData3.length > 0) {
                SeriesData.push({
                    data: gData3,
                    showInLegend: false,
                    name: 'Мои сделки',
                    color: '#ff0000',
                    fillOpacity: 0.5,
                    type: 'scatter',
                    marker: {
                        symbol: 'url(https://agrohelper.ru/local/templates/main/images/check1.png)'
                    },
                });
            }

            if (gData4.length > 0) {
                SeriesData.push({
                    data: gData4,
                    name: 'Спрос',
                    color: '#693A22',
                    fillOpacity: 0.5,
                    type: 'spline',
                    marker: {
                        enabled: false
                    }
                });
            }

            if (gData1.length > 0) {
                SeriesData.push({
                    data: gData1,
                    name: 'Мои цены',
                    color: '#ff0000',
                    fillOpacity: 0.5,
                    type: 'scatter',
                    marker: {
                        symbol: 'circle',
                        radius: 2.5
                    },
                });
            }


            //обрезаем год до двух цифр для вывода на графике
            var catListTitle = [];
            catList.map(function(item) {
                var itr = item.split('.');
                if(itr.length == 3){
                    itr[2] = itr[2].substr(2);
                    catListTitle.push(itr.join('.'));
                }else{
                    catListTitle.push(item);
                }
            });
            var legendComp = {
                align: 'right',
                verticalAlign: 'top',
                x: 0,
                y: -40,
                floating: true
            };
            var legendMobile = {
                align: 'center',
                verticalAlign: 'bottom',
                width: '100%'
            };
            //создаем график и одновременно сохраняем данные графика в объекте для дальнейшего использования
            var show_mode_val = filter_obj.attr('data-graphmode');
            graphsObj[show_mode_val + '_' + nds_code] = new Highcharts.Chart({
                credits: {
                    enabled: false
                },
                zoomType: 'y',
                chart: {
                    renderTo: wObj[0],
                    height: 400,
                    ignoreHiddenSeries: false
                },
                tooltip: {
                    valueSuffix: ' руб',
                    useHTML: true,
                    outside: true,
                    hideDelay: 1500
                },
                title: {
                    text: ''
                },
                //        legend: {
                //            enabled: false
                //        },

                legend: (isMobileWith ? legendMobile : legendComp),
                xAxis: {
                    /*title: {
                        text: 'Даты',
                        align: 'low',
                        margin: (isMobileWith ? 3 : 12),
                        style: {"fontSize": (isMobileWith ? "14px" : "18px")}
                    },*/
                    gridLineWidth: 1,
                    categories: catListTitle,
                    labels: {
                        style: {
                            fontSize: '9px'
                        }
                    },
                    min: 0,
                    softMax: catList.length - 1
                },
                yAxis: {
                    title: {
                        text: '',//'Цена, тыс. руб. за тонну',
                        align: 'low',
                        x: (isMobileWith ? -12 : 0),
                        margin: (isMobileWith ? 0 : 36),
                        style: {"fontSize": (isMobileWith ? "14px" : "18px")}
                    },
                    labels: {
                        formatter: function () {
                            //переводим цифру в десятки тысяч
                            return Highcharts.numberFormat(this.value , 0);
                        },
                    },
                    minRange: 50
                },
                plotOptions: {
                    scatter: {
                        tooltip: {
                            headerFormat: '',
                            pointFormat: '<span style="font-size:10px">{point.name}</span><br/><span style="color:{point.color}">●</span> {series.name}: <b>{point.y}</b>',
                        },
                    },
                    series: {
                        events: {
                            hide: function (e) {
                                //оставляем хотя бы одну ветку для показа
                                var visible_count = 0;
                                for (var i = 0; i < this.chart.series.length; i++) {
                                    if (this.chart.series[i].visible) {
                                        visible_count++;
                                    }
                                }
                                if (visible_count == 0) {
                                    this.setVisible(true, true);
                                }
                            }
                        },
                        label: {
                            enabled : false
                        },
                        point: {
                            events: {
                                mouseOver: function (e) {
                                    $('.highcharts-tooltip-container').show();

                                },
                                mouseOut: function (e) {
                                   // $('.highcharts-tooltip-container').hide();
                                }
                            }
                        }

                        //                borderWidth: 0,
                        //                dataLabels: {
                        //                    enabled: true,
                        //                    format: '{point.y} руб'
                        //                }
                    }
                },
                series: SeriesData
            });

            //обработка изменения размера экрана
            $(window).on('resize', function () {
                var show_mode_val = $('#client_requests_filter').attr('data-graphmode');
                var nds_code = 'n';
                var draw_object = $('#draw_object');
                if(typeof draw_object.attr('data-curnds') !== 'undefined'){
                    //если ндс уже установлен
                    nds_code = draw_object.attr('data-curnds');
                }else{
                    //если первый запуск
                    var nds_obj = $('.user_nds_value');
                    if(nds_obj.length == 1
                        && typeof nds_obj.attr('data-nds') !== 'undefined'
                    ){
                        nds_code = nds_obj.attr('data-nds');
                    }
                }
                graphsObj[show_mode_val + '_' + nds_code].setSize(null, null);
            });

            //обработка наведения на разницу (для подсветки нужной точки)
            $('.list_page_rows_area .line_area .price_difference').on('mouseover', function(){
                var draw_obj = $('#draw_object');
                if(draw_obj.hasClass('show')) {
                    var input_obj = $(this).parents('.line_area').find('form input[name="request"]:first');
                    var show_mode_val = $('#client_requests_filter').attr('data-graphmode'); //тип текущего режима графика (все графики - год, месяц и неделя, лежат внутри объекта graphsObj)

                    var nds_code = 'n';
                    var draw_object = $('#draw_object');
                    if(typeof draw_object.attr('data-curnds') !== 'undefined'){
                        //если ндс уже установлен
                        nds_code = draw_object.attr('data-curnds');
                    }else{
                        //если первый запуск
                        var nds_obj = $('.user_nds_value');
                        if(nds_obj.length == 1
                            && typeof nds_obj.attr('data-nds') !== 'undefined'
                        ){
                            nds_code = nds_obj.attr('data-nds');
                        }
                    }

                    if (input_obj.length == 1) {
                        var req_id = input_obj.val();

                        //подсвечиваем последнее значение на графике соответствующее текущему запросу
                        var my_price_pos = -1;
                        //находим индекс нужного графика по типу (т.к. какие-то из графиков могут не отображаться, то индекс нужного графика мы заранее не знаем)
                        for(var i = 0; i < graphsObj[show_mode_val + '_' + nds_code].series.length; i++){
                            if(graphsObj[show_mode_val + '_' + nds_code].series[i].type == 'scatter'){
                                my_price_pos = i;
                                break;
                            }
                        }

                        if(my_price_pos > -1) {
                            //находим последнюю точку, соотвутствующую нужному запросу
                            var last_pos = -1;
                            for (var i = 0; i < graphsObj[show_mode_val + '_' + nds_code].series[my_price_pos].data.length; i++) {
                                if (req_id == graphsObj[show_mode_val + '_' + nds_code].series[my_price_pos].data[i].additval) {
                                    last_pos = i;
                                }
                            }

                            //подсвечиваем нужную точку
                            if (last_pos > -1) {
                                graphsObj[show_mode_val + '_' + nds_code].series[my_price_pos].data[last_pos].setState('hover');
                                graphsObj[show_mode_val + '_' + nds_code].tooltip.refresh(graphsObj[show_mode_val + '_' + nds_code].series[my_price_pos].data[last_pos]);
                            }
                        }
                    }
                }
            });

            //обработка убирания мыши с разницы (для подсветки нужной точки)
            $('.list_page_rows_area .line_area .price_difference').on('mouseout', function(){
                var draw_obj = $('#draw_object');
                if(draw_obj.hasClass('show')) {
                    var input_obj = $(this).parents('.line_area').find('form input[name="request"]:first');
                    var show_mode_val = $('#client_requests_filter').attr('data-graphmode'); //тип текущего режима графика (все графики - год, месяц и неделя, лежат внутри объекта graphsObj)

                    var nds_code = 'n';
                    var draw_object = $('#draw_object');
                    if(typeof draw_object.attr('data-curnds') !== 'undefined'){
                        //если ндс уже установлен
                        nds_code = draw_object.attr('data-curnds');
                    }else{
                        //если первый запуск
                        var nds_obj = $('.user_nds_value');
                        if(nds_obj.length == 1
                            && typeof nds_obj.attr('data-nds') !== 'undefined'
                        ){
                            nds_code = nds_obj.attr('data-nds');
                        }
                    }

                    if (input_obj.length == 1) {
                        var req_id = input_obj.val();

                        //подсвечиваем последнее значение на графике соответствующее текущему запросу
                        var my_price_pos = -1;
                        //находим индекс нужного графика по типу (т.к. какие-то из графиков могут не отображаться, то индекс нужного графика мы заранее не знаем)
                        for(var i = 0; i < graphsObj[show_mode_val + '_' + nds_code].series.length; i++){
                            if(graphsObj[show_mode_val + '_' + nds_code].series[i].type == 'scatter'){
                                my_price_pos = i;
                                break;
                            }
                        }

                        if(my_price_pos > -1) {
                            //находим последнюю точку, соотвутствующую нужному запросу
                            var last_pos = -1;
                            for (var i = 0; i < graphsObj[show_mode_val + '_' + nds_code].series[my_price_pos].data.length; i++) {
                                if (req_id == graphsObj[show_mode_val + '_' + nds_code].series[my_price_pos].data[i].additval) {
                                    last_pos = i;
                                }
                            }

                            //убираем подсветку с нужной точки
                            if (last_pos > -1) {
                                graphsObj[show_mode_val + '_' + nds_code].series[my_price_pos].data[last_pos].setState('');
                                graphsObj[show_mode_val + '_' + nds_code].tooltip.hide(graphsObj[show_mode_val + '_' + nds_code].series[my_price_pos].data[last_pos]);
                            }
                        }
                    }
                }
            });
        }else{
            //если нет данных для построения графика
            if(graphShowMode == 0){
                //была раскрыта раскрывашка - переключаем на режим "год" и перерисовываем область графика
                setTimeout(function(){
                    wObj.siblings('.graph_area_tab[data-viewmode="year"]').trigger('click');
                }, 10);
            }else{
                //была переключена вкладка - отображаем пустую область
                if(filter_obj.attr('data-graphmode') == 'month') {
                    wObj.html('<div class="empty_graph">Нет данных за месяц</div>');
                }else{
                    wObj.html('<div class="empty_graph">Нет данных за неделю</div>');
                }
            }
        }

        //добавляем переключение режима "с НДС / без НДС"
        var nds_obj = $('.user_nds_value');
        var nds_value = false;
        if(nds_obj.length == 1
            && typeof nds_obj.attr('data-nds') !== 'undefined'
        ){
            nds_value = (nds_obj.attr('data-nds') == 'y');
        }
        var nds_change_input = $('.nds_change_area');
        if(nds_change_input.length == 0) {
            draw_object.prepend('<div class="nds_change_area radio_area"><input type="checkbox" data-text="с НДС" name="nds_change" onclick="changeGraphNDSMode(this);"' + (nds_value ? ' checked="checked"' : '') + ' /></div>');
            var inpObj = draw_object.find('.nds_change_area input');
            if (typeof inpObj.attr('data-text') !== 'undefined' && inpObj.attr('data-text').length != inpObj.attr('data-text').replace('checkbox_href', '').length) {
                inpObj.after('<div class="custom_data_text">' + inpObj.attr('data-text') + '</div><div class="custom_input checkbox' + (inpObj.prop('checked') === true ? ' checked' : '') + '" data-name="' + inpObj.attr('name') + '"><div class="ico"></div></div>');
            } else {
                inpObj.after('<div class="custom_input checkbox' + (inpObj.prop('checked') === true ? ' checked' : '') + '" data-name="' + inpObj.attr('name') + '"><div class="ico"></div>' + (typeof inpObj.attr('data-text') !== 'undefined' ? inpObj.attr('data-text') : '') + '</div>');
            }
            inpObj.addClass('customized');
            draw_object.attr('data-curnds', (nds_value ? 'y' : 'n')).find('.graph_area').attr('data-nds', (nds_value ? 'y' : 'n'));
        }
    }
}

//смена режима графика (с НДС/без НДС)
function changeGraphNDSMode(arg){
    var checked = false;
    var nds_postfix = '_no_nds';
    var nds_mode = 'n';

    if($(arg).prop('checked') === true){
        $(arg).siblings('.custom_input').addClass('checked');
        checked = true;
        nds_postfix = '_nds';
        nds_mode = 'y';
    }else{
        $(arg).siblings('.custom_input').removeClass('checked');
    }

    //отобажаем нужный график
    var wObj = $('#draw_object');
    var check_obj = $('.user_nds_value');
    //меняем текущий режим ндс
    wObj.attr('data-curnds', nds_mode);
    if($('#graph_my_categories' + nds_postfix).length == 0){
        if(loadindGraphAjax === 0) {
            loadindGraphAjax = 1;//запрещаем повторную отправку
            //нет данных для нужного графика - запрашиваем их
            $('#draw_object').append('<div class="graph_area" data-viewmode="year" data-nds="' + nds_mode + '" ></div><div class="graph_area" data-viewmode="month" data-nds="' + nds_mode + '"></div><div class="graph_area" data-viewmode="week" data-nds="' + nds_mode + '"></div>');
            $.ajax({
                url: '/ajax/getCounterOfferGraphData.php',
                method: 'POST',
                data: {
                    culture: check_obj.attr('data-culture'),
                    wh: check_obj.attr('data-wh'),
                    nds: (checked ? 'y' : 'n')
                }
            }).done(function (mes) {
                $('.list_page_rows_area:first').append(mes);
                showClientGraph(1);
                loadindGraphAjax = 0;
            });
        }
    }else {
        //отображаем нужный график
        showClientGraph(1);
    }
}

//переход к запросу
function goFromGraph(reqId){
    var url_val = '';
    if(window.location.pathname == '/partner/client_exclusive_offers/'){
        url_val = "/partner/client_request/?request_id=" + reqId + '&from_graph=y';
    }else {
        url_val = "/client/request/?request_id=" + reqId + '&from_graph=y';
    }

    document.location.href = url_val;
}