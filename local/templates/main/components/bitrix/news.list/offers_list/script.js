var stop_slide_anim = 0;
var charts_arr = [];
var isMobileWith  = screen.width <= 425;
var graphShowMode = 0; //признак того как открывается график (при разворачивании раскрывашки или при переключении вкладки)

$(document).ready(function() {
    $('.line_area').on('click', '.show-more-requests', function () {
        var $block = $(this).closest('.prop_area');
        $(this).hide();
        $block
            .find('.hidden')
            .show();
        $block.find('.hide-more-request').css('display','inline-block');
    });
    $('.line_area').on('click', '.hide-more-request', function () {
        var $block = $(this).closest('.prop_area');
        $(this).hide();
        $block
            .find('.hidden')
            .hide();

        $block.find('.show-more-requests').show();
    });

    $('.line_area').on('click', '.toggle-header', function () {
        var $block = $(this).closest('.prop_area');
        $block
            .find('.hidden')
            .toggle();

    });
    //раскрытие/скрытие "выпадашки"
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
                graphShowMode = 0; //режим раскрывашки

                //отображаем/строим график, если есть данные
                showOfferGraph(wObj);

                wObj.find('form.line_additional').slideDown(300, function(){
                    stop_slide_anim = 0;
                });
                wObj.find('.arw_list').removeClass('arw_icon_close').addClass('arw_icon_open');
            }
        }
    });

    //смена режима графика (год/месяц/неделя)
    $('.prop_area.with_graph .graph_area_tab').on('click', function(){
        if(!$(this).hasClass('active')){
            graphShowMode = 1; //режим перкключения вкладок
            var wObj = $(this).parents('.line_area');
            if(wObj.length != 0){
                wObj.attr('data-graphmode', $(this).attr('data-viewmode'));
                showOfferGraph(wObj);
            }
        }
    });

    //добавление в выпадашки данных о созданиие встречных предложений, если требуется
    var create_counter_data = $('.send_counter_req_data').each(function(cInd, cObj){

    var cur_offer_input = $('.list_page_rows input[name="offer"][type="hidden"][value="' + $(cObj).attr('data-offer') + '"]');
        if(cur_offer_input.length == 1){
            if($(cObj).attr('data-reqs')!=''){
                var lineObj = cur_offer_input.parents('.line_area').find('.prop_area.with_graph').after('<div class="prop_area adress_val counter_data">'
                    + '<div class="adress" id="create' + $(cObj).attr('data-offer') + '">Отправка/создание предложения:</div>'
                    + '<div class="val_adress">'
                    + '<div class="counter_request_additional_data">'
                    + '<div class="row first_row">'
                    + '<div class="row_val">'
                    + '<input type="text" data-checkval="y" data-checktype="pos_int" name="volume" placeholder="Указать количество тонн" value=""><span class="ton_pos">т.</span>'
                    + '</div></div>'
                    + '<div class="row">'
                    + '<div>'
                    + $(cObj).attr('data-rec')
                    + '</div>'
                    + '<div class="flex-row">'
                    + '<div class="row_head">Моя цена "с места":</div>'
                    + '<div class="row_val min_max_val">'
                    + '<div class="min_price">' + number_format($(cObj).attr('data-minval'), 0, '.', ' ') + '<span>min</span></div>'
                    + '<span class="minus minus_bg" data-step="50" onclick="farmerClickCounterMinPrice(this);" data-min="' + $(cObj).attr('data-minval') + '"></span>'
                    + '<input type="text" name="price" placeholder="" value="' + number_format($(cObj).attr('data-setval'), 0, '.', ' ') + '">'
                    + '<span class="plus plus_bg" data-step="50" onclick="farmerClickCounterMaxPrice(this);" data-max="' + $(cObj).attr('data-maxval') + '"></span>'
                    + '<div class="max_price">' + number_format($(cObj).attr('data-maxval'), 0, '.', ' ') + '<span>max</span></div>'
                    + '</div>'
                    //+ '<div class="clear"></div>'
                    + '</div></div>'

                    //+ '<div class="row two_lines_checkbox">'
                    //+ '<div class="row_val">'
                    //+ '<div class="radio_group fst">'
                    //+ '<div class="radio_area"><input type="checkbox" name="can_deliver" value="1" data-text="МОГУ ОТВЕЗТИ <br/>за прибавку в цене" class="customized"><div class="custom_input checkbox" data-name="can_deliver"><div class="ico"></div>МОГУ ОТВЕЗТИ <br>за прибавку в цене</div></div>'
                    //+ '</div>'
                    //+ '<div class="radio_group">'
                    //+ '<div class="radio_area"><input type="checkbox" name="lab_trust" value="1" data-text="ДОВЕРЮСЬ <br/>лаборатории покупателя" class="customized"><div class="custom_input checkbox" data-name="lab_trust"><div class="ico"></div>ДОВЕРЮСЬ <br>лаборатории покупателя</div></div>'
                    //+ '</div>'
                    //+ '<div class="clear"></div>'
                    //+ '</div></div>'

                    + '<input type="button" name="save" value="Отправить предложение" class="submit-btn counter_request_submit">'
                    + '</div></div>'

                    + '<div class="refinement_text"><br>'
                    //+ 'Сделайте предложение, чтобы покупатель увидел ваши намерения и связался с вами в случае заинтересованности.'
                    + ' Срок действия предложения - 7 дней.'
                    + '</div>'

                    + '</div>'
                );
                //работа чекбоксов
                $('.customized').click(function () {
                    var bChecked = $(this).prop('checked');
                    if(bChecked) {
                        $(this).siblings('.custom_input').addClass('checked');
                    } else {
                        $(this).siblings('.custom_input').removeClass('checked');
                    }
                });
            }else if(
                $(cObj).html() === ''
                && !$(cObj).hasClass('send_descr')
            ){
                var lineObj = cur_offer_input.parents('.line_area').find('.prop_area.total').before('<div class="prop_area adress_val">У товара нет запросов</div>');
            }else if($(cObj).html() != ''){
                $('.line_inner[data-offer="' + $(cObj).attr('data-offer') + '"]').addClass('answered');
                if($(cObj).hasClass('send_descr')){
                    var lineObj = cur_offer_input.parents('.line_area').find('.prop_area.counter_data').before('<div class="prop_area adress_val">' + $(cObj).html() + '</div>');
                }else {
                    var lineObj = cur_offer_input.parents('.line_area').find('.prop_area.total').before('<div class="prop_area adress_val">' + $(cObj).html() + '</div>');
                }
            }
        }
    });

    $('.counter_request_additional_data').find('input[name="price"]').on('focus', function () {
        if(isMobileMode() === true){
            $(this).val('');
        }
    });

    //ввод значений не меньше/больше установленных рамок
    $('.counter_request_additional_data input[name="price"]').on('change', function () {
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
                }else if(new_price < min_price){
                    new_price = min_price;
                }
            }
        }
        if(isMobileMode() === false)
            $(this).val(number_format(new_price, 0, '.', ' '));
    });

    //запрещаем отправку формы при нажатии на enter
    $('.counter_request_additional_data input').on('keydown', function(e){
        if(e.keyCode == 13) {
            e.preventDefault();
            return false;
        }
    });

    $('.counter_request_additional_data input[type="button"][name="save"]').on('click', function(){
        var counterObjArea = $(this).parents('.counter_request_additional_data');
        var wObjArea = counterObjArea.parents('.line_area');

        var volume_val = 0, offer_id = 0, price_val = 0, can_deliver = 0, lab_trust = 0;

        volume_val = parseInt(counterObjArea.find('input[name="volume"]').val());
        if(isNaN(volume_val) || volume_val == 0){
            //ошибка - не указан объём
            var err_ob = counterObjArea.find('input[name="volume"]').siblings('.error_msg');
            if(err_ob.length == 0){
                counterObjArea.find('input[name="volume"]').before('<div class="error_msg"></div>');
                err_ob = counterObjArea.find('input[name="volume"]').siblings('.error_msg');
            }
            err_ob.text('Не указан объем.');
            err_ob.addClass('active');
            return false;
        }

        var offer_obj = wObjArea.find('input[name="offer"]:first');
        if(offer_obj.length == 1){
            offer_id = parseInt(offer_obj.val());
        }

        can_deliver = (counterObjArea.find('input[name="can_deliver"]').prop('checked') === true ? 1 : 0);

        lab_trust = (counterObjArea.find('input[name="lab_trust"]').prop('checked') === true ? 1 : 0);

        price_val = parseInt(counterObjArea.find('input[name="price"]').val().replace(' ', ''));

        //отправляем встречные запросы
        if(volume_val > 0
            && offer_id > 0
        ){
            $.post('/farmer/offer/', {
                send_counter_offer_ajax: 'y',
                offer_id: offer_id,
                volume: volume_val,
                price: price_val,
                can_deliver: can_deliver,
                lab_trust: lab_trust
            }, function(mes){
                if(mes == 1){
                    //успех
                    counterObjArea.html('<div class="success_message">Предложения отправлены</div>');
                }else{
                    //ошибка
                    var err_ob = counterObjArea.find('input[name="volume"]').siblings('.error_msg');
                    if(err_ob.length == 0){
                        counterObjArea.find('input[name="volume"]').before('<div class="error_msg"></div>');
                        err_ob = counterObjArea.find('input[name="volume"]').siblings('.error_msg');
                    }
                    err_ob.text('Ошибка при отправке встречного предложения. Обратитесь к администрации за помощью.');
                    err_ob.addClass('active');
                }
            });
        }
    });
});

//показывает график (строит его, если он не был построен ранее и есть данные для построения)
function showOfferGraph(wObj){
    var graphObj = wObj.find('.prop_area.with_graph .graph_area[data-viewmode="' + wObj.attr('data-graphmode') + '"]');

    if(!graphObj.hasClass('active')){
        //проверяем не был ли ещё построен требуемый график
        if (graphObj.find('.highcharts-container').length == 0) {
            plotOfferGraph(graphObj, wObj.find('input[type="hidden"][name="offer"]:first').val());
        }

        graphObj.siblings('.active').each(function (cInd, cObj) {
            $(cObj).removeClass('active');
        });
        graphObj.addClass('active');
        graphObj.siblings('.graph_area_tab[data-viewmode="' + wObj.attr('data-graphmode') + '"]').addClass('active');
    }
}

//строит график для предложения с id равным elem_id
function plotOfferGraph(graphObj, elem_id){
    var gData1 = [], gData2 = [], gData3 = [], catList = [];
    var wdata, tempData, tempData2 = [], tempData3 = [];
    var start_days = 0, temp_date = 0;

    if(graphObj.find('.empty_graph').length == 0) {
        //получаем данные для графика для текущего режима работы и текущего товара (на вермя получения ставим заглушку)
        graphObj.html('<div class="empty_graph no_padding"><div class="empty_graph loading"><div class="loading_graph"></div></div></div>');
        $.post('/ajax/offerGraphData.php', {
            type_code: graphObj.attr('data-viewmode'),
            offer_id: elem_id
        }, function (mes) {
            if (mes == 1) {
                //произошла ошибка получения данных - отображаем пустую область
                if (graphObj.attr('data-viewmode') == 'month') {
                    graphObj.html('<div class="empty_graph">Нет данных за месяц</div>');
                } else if (graphObj.attr('data-viewmode') == 'year') {
                    graphObj.html('<div class="empty_graph">Нет данных за год</div>');
                } else {
                    graphObj.html('<div class="empty_graph">Нет данных за неделю</div>');
                }
            } else {
                //получаем данные
                wdata = JSON.parse(mes);

                //категории
                if (typeof wdata.cat_data != 'undefined') {
                    tempData = wdata.cat_data.split(';');
                    if (typeof tempData[3] != 'undefined') {
                        catList = tempData[3].split(',');

                        if (typeof catList[0] != 'undefined') {
                            tempData2 = catList[0].split('.');
                            temp_date = new Date(tempData2[2] + '-' + tempData2[1] + '-' + tempData2[0] + 'T05:00:00');
                            start_days = Math.ceil(temp_date.getTime() / 86400000);
                        }
                    }
                }
                if(catList.length == 9){
                    catList.splice(-1,1);
                }

                //данные для графика "Спрос"
                if (typeof wdata.best_data != 'undefined'
                    && wdata.best_data != ''
                ) {
                    tempData = wdata.best_data.split(';');
                    for (var i = 0; i < tempData.length; i++) {
                        tempData2 = tempData[i].split(',');
                        tempData3 = tempData2[0].split('.');

                        temp_date = new Date(tempData3[2] + '-' + tempData3[1] + '-' + tempData3[0] + 'T05:00:00');
                        gData1.push({
                            name: tempData2[0],
                            color: '#ffbf00',
                            x: Math.ceil(temp_date.getTime() / 86400000) - start_days,
                            y: parseInt(tempData2[1]),
                        });
                    }
                }

                //данные для графика "Мои цены"
                if (typeof wdata.my_prices_data != 'undefined'
                    && wdata.my_prices_data != ''
                ) {
                    tempData = wdata.my_prices_data.split(';');
                    for (var i = 0; i < tempData.length; i++) {
                        tempData2 = tempData[i].split(',');
                        tempData3 = tempData2[0].split('.');

                        temp_date = new Date(tempData3[2] + '-' + tempData3[1] + '-' + tempData3[0] + 'T05:00:00');
                        gData2.push({
                            name: tempData2[0],
                            color: '#ff0000',
                            x: Math.ceil(temp_date.getTime() / 86400000) - start_days,
                            y: parseInt(tempData2[1])
                        });
                    }
                }

                //данные для графика "Рынок" (сначала ищем по текущему региону)
                if (0 && typeof wdata.deals_cur != 'undefined'
                    && wdata.deals_cur != ''
                ) {
                    tempData = wdata.deals_cur.split(';');
                    for (var i = 0; i < tempData.length; i++) {
                        tempData2 = tempData[i].split(',');
                        tempData3 = tempData2[0].split('.');

                        temp_date = new Date(tempData3[2] + '-' + tempData3[1] + '-' + tempData3[0] + 'T05:00:00');
                        gData3.push({
                            name: tempData2[0],
                            color: '#7ed321',
                            x: Math.ceil(temp_date.getTime() / 86400000) - start_days,
                            y: parseInt(tempData2[1])
                        });
                    }

                    if(gData3.length > 0){
                        gData3 = sortКmElemsByDate(gData3);
                    }
                }
                //если по текущему региону не найдено - ищем по связанным регионам
                if (gData3.length == 0
                    && typeof wdata.deals_linked != 'undefined'
                    && wdata.deals_linked != ''
                ) {
                    tempData = wdata.deals_linked.split(';');
                    for (var i = 0; i < tempData.length; i++) {
                        tempData2 = tempData[i].split(',');
                        tempData3 = tempData2[0].split('.');

                        temp_date = new Date(tempData3[2] + '-' + tempData3[1] + '-' + tempData3[0] + 'T05:00:00');
                        gData3.push({
                            name: tempData2[0],
                            color: '#7ed321',
                            x: Math.ceil(temp_date.getTime() / 86400000) - start_days,
                            y: parseInt(tempData2[1])
                        });
                    }

                    if(gData3.length > 0){
                        gData3 = sortКmElemsByDate(gData3);
                    }
                }

                if (gData1.length > 0
                    || gData2.length > 0 //для gData2 достаточно наличия хотя бы одной точки
                    || gData3.length > 0
                ) {
                    var SeriesData = [];

                    if (gData1.length > 0) {
                        SeriesData.push({
                            data: gData1,
                            name: 'Спрос',
                            color: '#ffbf00',
                            fillOpacity: 0.5,
                            type: 'line',
                            marker: {
                                symbol: 'circle'
                            }
                        });
                    }

                    if (gData3.length > 0) {
                        SeriesData.push({
                            data: gData3,
                            name: 'Рынок',
                            color: '#7ed321',
                            fillOpacity: 0.5,
                            type: 'spline',
                            marker: {
                                symbol: 'circle'
                            }
                        });
                    }

                    if (gData2.length > 0) {
                        SeriesData.push({
                            data: gData2,
                            name: 'Мои цены',
                            color: '#ff0000',
                            fillOpacity: 0.5,
                            type: 'scatter',
                            marker: {
                                symbol: 'circle'
                            }
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
                    };

                    //убираем заглушку и отображаем график
                    var tempObj = graphObj.find('.empty_graph.loading');
                    tempObj.fadeOut(1000, function () {
                        tempObj.remove();

                        window.chart = new Highcharts.Chart({
                            credits: {
                                enabled: false
                            },
                            zoomType: 'y',
                            chart: {
                                renderTo: graphObj[0],
                                height: 400,
                                ignoreHiddenSeries: false
                            },
                            tooltip: {
                                valueSuffix: ' руб'
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
                                labels: {
                                    style: {
                                        fontSize: '9px'
                                    }
                                },
                                categories: catListTitle,
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
                                minRange: 50,
                                labels: {
                                    formatter: function () {
                                        //переводим цифру в десятки тысяч
                                        // return Highcharts.numberFormat(this.value / 1000, 1);
                                        return Highcharts.numberFormat(this.value, 0);
                                    }
                                }
                            },
                            plotOptions: {
                                scatter: {
                                    tooltip: {
                                        headerFormat: '',
                                        pointFormat: '<span style="font-size:10px">{point.name}</span><br/><span style="color:{point.color}">●</span> {series.name}: <b>{point.y}</b>',
                                    }
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

                        $(window).on('resize', function () {
                            window.chart.setSize(null, null);
                        });
                    });

                    graphObj.parents('.with_graph').addClass('show');
                } else {
                    //если нет данных для построения графика
                    if (graphShowMode == 0) {
                        //была раскрыта раскрывашка - переключаем на режим "год" и перерисовываем область графика
                        setTimeout(function () {
                            if (graphObj.attr('data-viewmode') == 'month') {
                                graphObj.html('<div class="empty_graph">Нет данных за месяц</div>');
                            }
                            graphObj.siblings('.graph_area_tab[data-viewmode="year"]').trigger('click');
                        }, 10);
                    } else {
                        //была переключена вкладка - отображаем пустую область
                        if (graphObj.attr('data-viewmode') == 'month') {
                            graphObj.html('<div class="empty_graph">Нет данных за месяц</div>');
                        } else if (graphObj.attr('data-viewmode') == 'year') {
                            graphObj.html('<div class="empty_graph">Нет данных за год</div>');
                        } else {
                            graphObj.html('<div class="empty_graph">Нет данных за неделю</div>');
                        }
                    }
                }
            }
        });
    }
}

//функция обработки собтия клика по точке графика "Спрос"
function pointClickHandler(obj, ev){
    // console.log(obj);
    // console.log(ev);
}

//сортирует все точки графика "Сделки(R300км)" по дате
function sortКmElemsByDate(qData){

    var temp_elem = [], temp_arr1 = [], temp_arr2 = [], temp_val1 = 0, temp_val2 = 0;
    if(qData.length > 0){

        //метод "пузырька"
        for(var i = 0; i < qData.length; i++){
            for(var j = 0; j < qData.length; j++){
                temp_arr1 = qData[i].name.split('.');
                temp_arr2 = qData[j].name.split('.');
                temp_val1 = parseInt(temp_arr1[2] + temp_arr1[1] + temp_arr1[0]);
                temp_val2 = parseInt(temp_arr2[2] + temp_arr2[1] + temp_arr2[0]);

                if(temp_val1 < temp_val2){
                    //меняем местами элементы
                    temp_elem = {
                        name: qData[i].name,
                        color: qData[i].color,
                        x: qData[i].x,
                        y: qData[i].y
                    };

                    qData[i].name = qData[j].name;
                    qData[i].color = qData[j].color;
                    qData[i].x = qData[j].x;
                    qData[i].y = qData[j].y;

                    qData[j].name = temp_elem.name;
                    qData[j].color = temp_elem.color;
                    qData[j].x = temp_elem.x;
                    qData[j].y = temp_elem.y;
                }
            }
        }
    }

    return qData;
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