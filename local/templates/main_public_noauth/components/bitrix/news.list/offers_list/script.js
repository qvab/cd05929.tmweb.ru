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

    //отображаем график
    showOfferGraph($('.list_page_rows.farmer_offer .line_area:first'));

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

    //устанавливаем цену при нажатии на рекомендуемую цену
    $('.list_page_rows .line_additional').on('click', '.pr_val_rec', function(){
        var nValue = parseInt($(this).find('.val_span').text().replace(' ', ''));
        if(!isNaN(nValue)) {
            $(this).parents('.line_additional').find('input[type="text"][name="price"]').val(number_format(nValue, 0, '.', ' '));
            recountCounterOfferPartnerPriceOffer($(this).parents('.line_area'));
        }
    });

    $('.list_page_rows').on('focus', '.counter_request_additional_data input[name="price"]',  function () {
        if(isMobileMode() === true){
            $(this).val('');
        }
    });

    //ввод значений не меньше/больше установленных рамок
    $('.list_page_rows').on('change', '.counter_request_additional_data input[name="price"]', function () {
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

        $(this).val(number_format(new_price, 0, '.', ' '));
    });

    //запрещаем отправку формы при нажатии на enter
    $('.list_page_rows').on('keydown', '.counter_request_additional_data input', function(e){
        if(e.keyCode == 13) {
            e.preventDefault();
            return false;
        }
    });

    $('.list_page_rows').on('click', '.counter_request_additional_data input[type="button"][name="save"]', function(){
        var counterObjArea = $(this).parents('.counter_request_additional_data');
        var wObjArea = counterObjArea.parents('.line_area');

        var volume_val = 0, offer_id = 0, price_val = 0, can_deliver = 0, lab_trust = 0;

        volume_val = parseInt(counterObjArea.find('input[name="volume"]').val());
        if(isNaN(volume_val) || volume_val == 0){
            //ошибка - не указан объём
            var err_ob = $('.counter_request_additional_data').siblings('.error_msg');
            if(err_ob.length == 0){
                $('.counter_request_additional_data').before('<div class="error_msg"></div>');
                err_ob = $('.counter_request_additional_data').siblings('.error_msg');
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
        if(isNaN(price_val) || price_val == 0){
            //ошибка - не указан объём
            var err_ob = $('.counter_request_additional_data').siblings('.error_msg');
            if(err_ob.length == 0){
                $('.counter_request_additional_data').before('<div class="error_msg"></div>');
                err_ob = $('.counter_request_additional_data').siblings('.error_msg');
            }
            err_ob.text('Не указана цена.');
            err_ob.addClass('active');
            return false;
        }

        //отправляем встречные запросы
        if(volume_val > 0
            && offer_id > 0
            && price_val > 0
        ){
            $.post(document.location.href, {
                send_counter_offer_ajax: 'y',
                offer_id: offer_id,
                volume: volume_val,
                price: price_val,
                can_deliver: can_deliver,
                lab_trust: lab_trust
            }, function(mes){
                if(mes == 1){
                    //успех
                    var culture_name = $('#public_content h3.centered .culture_name').text();
                    var warehouse_name = $('#public_content h3.centered .warehouse_name').text();
                    //отображаем разные тексты для организатора и поставщика
                    var wObjAreaParent = wObjArea.parents('.list_page_rows');

                    if(typeof wObjAreaParent.attr('data-pid') !== 'undefined'){
                        var partnerData = '';
                        if(typeof wObjAreaParent.attr('data-pext') !== 'undefined'){
                            partnerData = '<br/>' + wObjAreaParent.attr('data-pext');
                        }
                        counterObjArea.parents('.adress_val').html('<div class="val_adress no_pad"><span class="copy-text">' + getCounterOfferDate() + ' <br/>Вы сделали предложение ' + volume_val + ' т товара ' + culture_name + ', со склада ' + warehouse_name + ' по цене "с места": ' + price_val + ' руб/т.' + partnerData + '<div class="cancel_counter_offer_area"><a class="submit-btn" href="javascript: void(0);">Отменить предложение</a></div></span><div class="js-copy copy-left html_val">Скопировать</div>');
                    }else{
                        counterObjArea.parents('.adress_val').html('<div class="val_adress no_pad">' + getCounterOfferDate() + ' для культуры "' + culture_name + '" со склада ' + warehouse_name + ' направлено предложение с заявленным объёмом ' + volume_val + ' т. и ценой ' + price_val + ' руб/т. Тип доставки установлен как exw.<div class="cancel_counter_offer_area"><a class="submit-btn" href="javascript: void(0);">Отменить предложение</a></div></div>');
                    }
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

    //отмена предложения
    $('.list_page_rows').on('click', '.cancel_counter_offer_area a', function(){
        var objOffer = $(this).parents('.line_additional').find('input[name="offer"]');
        if(
            objOffer.length === 1
        ) {
            stopCounterOffer(objOffer.val());
        }
    });

    //пересчёт цен услуги партнера при изменении объёма или цены
    $('.list_page_rows.farmer_offer').on('keyup', '.line_additional input[name="volume"]', function(){
        recountCounterOfferPartnerPriceOffer($(this).parents('.line_area'));
    });
    $('.list_page_rows.farmer_offer').on('keyup change', '.line_additional input[name="price"]', function(){
        var wObj = $(this);
        setTimeout(function(){
            recountCounterOfferPartnerPriceOffer(wObj.parents('.line_area'));
        }, 50);
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
                        align: 'left',
                        verticalAlign: 'bottom',
                        x: 0,
                        y: 30,
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

function isMobileMode() {
    var result = false;
    var clWidth = $(window).width();
    if(document.documentElement.clientHeight < document.documentElement.scrollHeight){
        clWidth = $(window).width()+20;
    }
    if(clWidth<980){
        result = true;
    }
    return result;
}

//отправка ajax запроса для отмены предложения
function stopCounterOffer(iOfferId){
    var volume_val = 0, from_farmer = 1, partner_id = 0;
    var objW = $('.list_page_rows .line_area:first');
    if(typeof objW.attr('data-vol') !== 'undefined'){
        volume_val = parseInt(objW.attr('data-vol'));
        if(isNaN(volume_val)){
            volume_val = 0;
        }
    }

    if(typeof $('.list_page_rows').attr('data-pid') !== 'undefined'){
        from_farmer = 0;
        partner_id = $('.list_page_rows').attr('data-pid');
    }
    $.post('/ajax/cancelCounterOffer.php', {
        offer_id: iOfferId,
        partner_id: partner_id,
        from_farmer: from_farmer,
        volume: volume_val
    }, function (mes) {
        var objInput = $('.list_page_rows.farmer_offer input[type="hidden"][name="offer"][value="' + iOfferId + '"]');
        if(objInput.length > 0) {
            var objArea = objInput.parents('.line_additional').find('.prop_area.counter_data');
            if(objArea.length > 0) {
                if (mes == 1) {
                    var objRes = objArea.find('.answer_area');
                    if(objRes.length === 0){
                        objArea.prepend('<div class="answer_area"></div>');
                        objRes = objArea.find('.answer_area');
                    }
                    objRes.html('Не найдены запросы к данному товару (обновите страницу).');
                } else if (mes == 2) {
                    var objRes = objArea.find('.answer_area');
                    if(objRes.length === 0){
                        objArea.prepend('<div class="answer_area"></div>');
                        objRes = objArea.find('.answer_area');
                    }
                    objRes.html('Предложения уже были отменены (обновите страницу).');
                } else {
                    var objParentArea = objInput.parents('.line_additional');
                    objArea.replaceWith(mes);
                    objParentArea.parents('.line_area').find('.line_inner').removeClass('answered');
                    //добавляем обработку клика на чекбоксе
                    objParentArea.find('.counter_request_additional_data input[type="checkbox"]').on('click', function(){
                        var objW = $(this).siblings('.checkbox');
                        if(objW.length > 0){
                            if($(this).prop('checked')){
                                objW.addClass('checked');
                            }else{
                                objW.removeClass('checked');
                            }
                        }
                    });
                }
            }
        }
    });
}

//пересчитывает и устанавливает стоимость агентской услуги
//objArg - объект line_area в списке товаров
function recountCounterOfferPartnerPriceOffer(objArg){
    var wObj = objArg.find('input[name="serv_price"]');
    if(
        typeof counter_option_contract !== 'undefined'
        && typeof counter_option_lab !== 'undefined'
        && typeof counter_option_support !== 'undefined'
        && wObj.length === 1
    ){
        var iPrice = 0, iCsmPrice = 0, iVolume = 0, bLabChecked = false, bSupportChecked = true, objPriceInput, objPricePerTonInput, iTemp = 0;

        //если в списке товаров
        var objTemp;
        var objWork = objArg.find('input[name="serv_price"]');
        if (objWork.length > 0) {
            objPriceInput = objWork;
            iTemp = parseInt(objArg.find('.counter_data input[name="volume"]').val().replace(' ', ''));
            if (!isNaN(iTemp)) {
                iVolume = iTemp;
            }

            iTemp = parseInt(objArg.find('.counter_data input[name="price"]').val().replace(' ', ''));
            if (!isNaN(iTemp)) {
                iCsmPrice = iTemp;
            }

            if (
                typeof objArg.attr('data-approved') !== 'undefined'
            ) {
                bLabChecked = true;
            }

            objPricePerTonInput = objArg.find('.partner_price_part .val');
        }

        //установление данных
        if(objPriceInput) {
            //расчет стоимости агентского договора
            iPrice += parseInt(Math.round((counter_option_contract / 10000.0) * iCsmPrice * iVolume));

            //добавление стоимости услуг лаборатории
            if (bLabChecked) {
                iPrice += counter_option_lab;
            }

            //добавление стоимости сопровождения сделки
            if (bSupportChecked) {
                iPrice += counter_option_support * iVolume;
            }

            //устанавливаем рассчитанное значение
            objPriceInput.val(number_format(iPrice, 0, '.', ' '));
            if(objPricePerTonInput){
                if(iVolume > 0) {
                    objPricePerTonInput.text(number_format(Math.round(iPrice / iVolume), 0, '.', ' '));
                    objPricePerTonInput.parents('.partner_price_part').addClass('active');
                }else{
                    objPricePerTonInput.parents('.partner_price_part').removeClass('active');
                }
            }
        }
    }
}

//получает текущую дату и время (без секунд)
function getCounterOfferDate(){
    var now = new Date(), sDay = '', sMonth = '';

    var iTemp = parseInt(now.getDate());
    sDay = iTemp;
    if(iTemp < 10){
        sDay = '0' + sDay;
    }
    iTemp = parseInt(now.getMonth());
    sMonth = (iTemp + 1);
    if(iTemp < 9){
        sMonth = '0' + sMonth;
    }

    return sDay + '.' + sMonth + '.' + now.getFullYear() + ' ' + now.getHours()+ ':' + now.getMinutes();
}