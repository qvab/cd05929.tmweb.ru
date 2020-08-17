var req_stop_animation = 0;
var isMobileWith  = screen.width <= 425;

$(document).ready(function(){
    //обработка клика по чекбоксу встречного предложения
    $('.list_page_rows.counter_request_client_list .line_area .disregard input[type="checkbox"]').on('click', function(){
        recountClientCounterRequestsListResult();
    });
    initPageURL();

    $('.price_difference').on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        if(0 && !isMobileDevice()) {
            var url_cl = "/client/request/?request_id=" + $(this).data('request');
            var url_ag = "/partner/client_request/?request_id=" + $(this).data('request');
            if (window.location.pathname == '/partner/client_exclusive_offers/') {
                document.location.href = url_ag;
            } else {
                document.location.href = url_cl;
            }
        }
    });
    //раскрыванием/сворачивание данных встречного запроса
    $('.list_page_rows.counter_request_client_list .line_area .line_inner').on('click', function(e){
        var chObj = $(this).find('.disregard input[type="checkbox"]');
        var acceptBut = $(this).find('.accept .submit-btn:not(.inactive)');
        var copyTextObj = $(this).find('.agent_counter_href_value');
        if(!chObj.is(e.target)
            && !acceptBut.is(e.target)
            && (
                copyTextObj.length == 0
                || !copyTextObj.is(e.target)
            )
        ){
            if(req_stop_animation == 0){
                req_stop_animation = 1;

                var wObj = $(this).parents('.line_area');
                if(wObj.hasClass('active')){
                    wObj.removeClass('active');
                    wObj.find('.line_additional').slideUp(300, function(){
                        req_stop_animation = 0;
                    });
                    wObj.find('.arw_list').removeClass('arw_icon_open').addClass('arw_icon_close');
                }else{
                    wObj.addClass('active');
                    wObj.find('.line_additional').slideDown(300, function(){
                        req_stop_animation = 0;
                    });
                    wObj.find('.arw_list').removeClass('arw_icon_close').addClass('arw_icon_open');
                }
            }
        }else if(acceptBut.is(e.target)
            && acceptBut.parents('.inactive_counter_request').length == 0
            && acceptBut.parents('.for_agent').length == 0
        ){
            //нажали кнопку "принять"
            $(this).siblings('form.line_additional').submit();
        }
    });

    resizeInit();

    $( window ).resize(function() {
        resizeInit();
    });

    $(document).on('scroll', scrollStuck);
    $('.list_page_rows_area').on('scroll', function(){
        var $headLine       = $('.head_line', this);

        if($(this).hasClass('stuck')) {
            $headLine.css(
                {
                    'margin-left': '-' + $(this).scrollLeft() + 'px'
                }
            );
        }
    });

    $('.js-agree').on('click', function () {
        $(this).closest('.line_area').find('.line_inner .accept .submit-btn').click();
    });
    
    //скролл до раскрытого ВП
    var opened_cr_data = $('.list_page_rows_area .opened_c_req');
    if(opened_cr_data.length == 1){
        var opened_cr_obj = $('.list_page_rows_area .line_area.active');
        if(opened_cr_obj.length == 1){
            var offset_obj = opened_cr_obj.offset();
            var offset_val = parseInt(offset_obj.top);
            if(!isNaN(offset_val)
                && offset_val > 0
            ){
                setTimeout(function(){
                    $(document).scrollTop(offset_val - 70);
                }, 250);
            }
        }
    }

    //формирование агентом ссылки для покупателя
    $('.accept.for_agent .submit-btn').on('click', function(){
        var wObj = $(this);
        var host_container = $(this).parents('.list_page_rows_area');


        if(typeof wObj.attr('data-profile') != 'undefined'){
            var url_val = '';
            var hrefAreaObj = wObj.parents().siblings('.agent_counter_href_area:first');
            if(hrefAreaObj.length == 0) {
                //пользователь авторизовывался ранее -> берётся ссылка
                wObj.parent().after('<div class="agent_counter_href_area"><div class="agent_counter_href_value">Для создания ссылки на принятие предложения необходимо <a href="/profile/make_full_mode/?uid=' + wObj.attr('data-profile') + '">заполнить профиль</a> покупателя</div></div>');
            }
        }else if(typeof wObj.attr('data-href') != 'undefined'){
            var url_val = '';
            var hrefAreaObj = wObj.parents().siblings('.agent_counter_href_area:first');
            if(hrefAreaObj.length == 0) {
                //если кнопка жмется первый раз
                if (wObj.attr('data-href') == '') {
                    //пользователь ни разу не авторизовывался -> делается запрос на формирование ссылки для завершения регистрации (с учетом переадресации на создание ВП)
                    var lineObj = wObj.parents('.line_area');
                    if (lineObj.length == 1) {
                        var reqObj = lineObj.find('input[type="hidden"][name="request"]');
                        var offerObj = lineObj.find('input[type="hidden"][name="offer"]');

                        //собираем данные цены
                        var dataObj = wObj.parents('.line_area');
                        var checkObj = dataObj.find('.line_additional .area_1[data-type="csm_price"]');
                        var offer_csmprice = '', offer_csm_addittext = '', offer_tarif = '', offer_tarif_distance = '', offer_dump = '', offer_base_price = '', offer_delivery_type = '', offer_nds = 'n', offer_agentprice = '', offer_agentfullprice = '';
                        if(checkObj.length > 0){
                            offer_csmprice = checkObj.find('.decs_separators').text();
                            offer_csm_addittext = checkObj.find('.name_1 .place_type').text();
                            if(typeof checkObj.attr('data-nds') != 'undefined'
                                && checkObj.attr('data-nds') !== 'n'
                            ){
                                offer_nds = checkObj.attr('data-nds');
                            }
                        }
                        checkObj = dataObj.find('.line_additional .area_1[data-type="tarif"]');
                        if(checkObj.length > 0){
                            offer_tarif = checkObj.find('.decs_separators').text();
                            offer_tarif_distance = checkObj.attr('data-distance');
                        }
                        checkObj = dataObj.find('.line_additional .area_1[data-type="dump"]');
                        if(checkObj.length > 0){
                            offer_dump = parseInt(checkObj.find('.decs_separators_s').text().replace(/[ \+]/gi, ''));

                            if(isNaN(offer_dump)){
                                offer_dump = '';
                            }
                        }
                        checkObj = dataObj.find('.line_additional .area_1[data-type="base_price"]');
                        if(checkObj.length > 0){
                            offer_base_price = checkObj.find('.decs_separators').text();
                            offer_delivery_type = checkObj.attr('data-deliverytype');
                        }
                        checkObj = dataObj.find('.line_additional .area_1[data-type="agent_price"]');
                        if(checkObj.length > 0){
                            offer_agentprice = checkObj.find('.decs_separators').text();
                            offer_agentfullprice = checkObj.find('.full_val').text();
                        }

                        if (reqObj.length == 1
                            && offerObj.length == 1
                            && typeof wObj.attr('data-wh') != 'undefined'
                            && typeof wObj.attr('data-uid') != 'undefined'
                            && typeof wObj.attr('data-culture') != 'undefined'
                        ) {
                            $.post('/ajax/getUserInviteHref.php', {
                                uid: wObj.attr('data-uid'),
                                wh_id: wObj.attr('data-wh'),
                                culture_id: wObj.attr('data-culture'),
                                counter_id: lineObj.attr('data-id'),
                                o: offerObj.val(),
                                r: reqObj.val(),
                                offer_csmprice: offer_csmprice,
                                offer_nds: offer_nds,
                                offer_csm_addittext: offer_csm_addittext,
                                offer_tarif: offer_tarif,
                                offer_tarif_distance: offer_tarif_distance,
                                offer_dump: offer_dump,
                                offer_base_price: offer_base_price,
                                offer_delivery_type: offer_delivery_type,
                                offer_agentfullprice: offer_agentfullprice,
                                offer_agentprice: offer_agentprice
                            }, function (mes) {
                                if (mes != 0) {
                                    url_val = mes;
                                    //wObj.parent().after('<div class="agent_counter_href_area"><div class="agent_counter_href_value">' + url_val + '</div></div>');
                                    //copyAgentHref(wObj);
                                    var haveEmail = 0;
                                    if(wObj.attr('data-email').length>0){
                                        haveEmail = 1;
                                    }
                                    if(mes == 1){
                                        //если ошибка
                                        showTextLinkFeedbackFormErr('Ссылка на предложение покупателю');
                                    }else{
                                        //если есть данные
                                        showTextLinkFeedbackForm('Ссылка на предложение покупателю',haveEmail,url_val,0,wObj.attr('data-uid'),'client');
                                    }
                                }
                            });
                        }
                    }
                } else {
                    //пользователь авторизовывался ранее -> берётся ссылка
                    url_val = host_container.attr('data-host') + wObj.attr('data-href');
                    wObj.parent().after('<div class="agent_counter_href_area"><div class="agent_counter_href_value">' + url_val + '</div></div>');
                    copyAgentHref(wObj);
                }
            }else{
                //если кнопка уже нажималась ранее
                var href_obj = hrefAreaObj.find('.agent_counter_href_value');
                if(href_obj.length == 1){
                    if(copyToClipboard(href_obj.text())){
                        if(hrefAreaObj.find('.agent_counter_href_success_text').length == 0){
                            hrefAreaObj.prepend('<div class="agent_counter_href_success_text">Ссылка скопирована в буфер обмена</div>');

                            var del_obj = hrefAreaObj.find('.agent_counter_href_success_text');
                            setTimeout(function(){
                                if(del_obj.length == 1){
                                    del_obj.remove();
                                }
                            }, 10000);
                        }
                    }
                }
            }
        }
    });

    if($('#items_count').length>0){
        if($('#items_count').attr('data-count') == 0){
            $('#client_requests_filter').hide();
        }
    }

    //выделение нужных предложений
    if(typeof checked_offers_list !== 'undefined'){
        var wArea = $('.list_page_rows.counter_request_client_list');
        if(wArea.length == 1) {
            for(var i = 0; i < checked_offers_list.length; i++) {
                var wObj = wArea.find('.line_area[data-id="' + checked_offers_list[i] + '"] .line_inner .disregard input[type="checkbox"]');
                if(wObj.length == 1) {
                    wObj.trigger('click');
                }
            }
        }
    }
});

function scrollStuck() {
    var $wrap           = $('.list_page_rows_area'),
        $rows           = $('.list_page_rows', $wrap),
        $headLine       = $('.head_line', $wrap),
        wrapPositionTop = ($wrap.length > 0 ? $wrap.position().top : 0),
        isStuckTable    = ( $('#draw_object_block').length > 0 );
        // this - тут документ
        $headLine.css({
                'width' : $rows.width(),
        });

        //прикрепление графика только для обычного режима, на мобильном прикрепляется только шапка
        if($(window).width() > 980) {
            if(isStuckTable && ($('#draw_object_block').position().top <= $(this).scrollTop())){
                if($('#draw_object').css('display') == 'block'){
                    $('#draw_object').addClass('draw_object_stuck');
                }
            }else{
                $('#draw_object').removeClass('draw_object_stuck');
            }

            if(isStuckTable && $('#draw_object').css('display') == 'block'){
                if((wrapPositionTop - $('#draw_object').outerHeight()) <= ($(this).scrollTop())){
                    $wrap.addClass('stuck');
                    $headLine.css('top',$('#draw_object').outerHeight(true));
                }
                else {
                    $wrap.removeClass('stuck');
                    $headLine.css('top','0');
                    $headLine.css(
                        {
                            'margin-left' : '0px'
                        }
                    );
                }

            }else{
                if((wrapPositionTop) <= ($(this).scrollTop())){
                    $wrap.addClass('stuck');
                    $headLine.css('top','0');
                }
                else {
                    $wrap.removeClass('stuck');
                    $headLine.css(
                        {
                            'margin-left' : '0px'
                        }
                    );
                }
            }
        }else{
            if((wrapPositionTop) <= ($(this).scrollTop())){
                $wrap.addClass('stuck');
                $headLine.css('top','45px');
            }
            else {
                $wrap.removeClass('stuck');
                $headLine.css(
                    {
                        'margin-left' : '0px'
                    }
                );
            }
        }
}

function resizeInit() {
    if($('#page_body').width() > 770) {
        $('#opening_top').hide();
        $('#opening_head').show();
    }else{
        $('#opening_head').hide();
        $('#opening_top').show();
    }
}

//

//пересчитываем итоговое значение в списке встречных предложений
function recountClientCounterRequestsListResult(){
    var wArea = $('.list_page_rows.counter_request_client_list');
    var totalVolume = 0, totalPrice = 0, volumeResult = '', priceResult = '';
    var totalAdditVolume = 0, totalAdditPrice = 0, volumeAdditResult = '', priceAdditResult = '';
    var nds_type = 'n', alter_nds_type = 'y'; //по умолчанию берем основные цены без НДС
    var user_nds_obj = $('.nds_value_div');
    var nds_val = parseFloat(user_nds_obj.attr('data-ndsval'));

    //получаем тип НДС цен, которые берём
    if($('.list_page_rows.counter_request_client_list .price span.val[data-nds="n"]:first').length === 0
        && $('.list_page_rows.counter_request_client_list .price span.val[data-nds="y"]:first').length > 0
    ){
        //если все цены "по умолчанию" с НДС, то по ставим тип НДС
        nds_type = 'y';
        alter_nds_type = 'n';
    }


    if(typeof pageDataObject != 'undefined'){
        pageDataObject.checked = [];
    }
    wArea.find('.line_area .line_inner').each(function(ind, cObj){
        var checkboxObj = $(this).find('.disregard input[type="checkbox"]:checked');
        if(checkboxObj.length == 1)
        {
            if(typeof pageDataObject != 'undefined'){
                pageDataObject.checked.push(checkboxObj.attr('name'));
            }
            var volumeVal = 0;
            var volumeObj = $(this).find('.tons');
            if(volumeObj.length == 1){
                volumeVal = parseInt(volumeObj.text());
                if(!isNaN(volumeVal)
                    && volumeVal != ''
                    && volumeVal > 0
                ){
                    totalVolume += volumeVal;

                    var priceVal = 0;
                    var priceObj = $(this).find('.price span[data-nds="' + nds_type + '"]');
                    if(priceObj.length == 1){
                        priceVal = parseInt(priceObj.text().replace(/(руб\/т| )/g, ''));
                    }else{
                        var priceObj = $(this).find('.price span[data-nds="' + alter_nds_type + '"]');
                        if(priceObj.length == 1){
                            priceVal = parseInt(priceObj.text().replace(/(руб\/т| )/g, ''));
                            if(alter_nds_type == 'n'){
                                //добавляем НДС
                                priceVal = priceVal + priceVal * 0.01 * nds_val;
                            }else{
                                //вычитаем НДС
                                priceVal = priceVal / (1 + 0.01 * nds_val);
                            }
                        }
                    }

                    var priceAdditObj = $(this).find('.price .addit_nds_price');
                    if(priceVal > 0){
                        totalPrice += volumeVal * priceVal;
                    }
                }
            }
            //$(cObj).parent().removeClass('inactive_counter_request');
        }else{
           // $(cObj).parent().addClass('inactive_counter_request');
        }
    });
    if(typeof pageDataObject != 'undefined'){
        if(pageDataObject.checked.length > 0) {
            $('.page-url').show();
        }
        else {
            $('.page-url').hide();
        }
    }
    if(totalVolume > 0){
        if(totalPrice > 0){
            totalPrice = Math.round(totalPrice / totalVolume);
        }

        volumeResult = number_format(totalVolume.toString(), 0, '.', ' ') + ' т';
    }else{
        volumeResult = '-';
    }

    if(totalPrice > 0){
        priceResult = number_format(totalPrice.toString(), 0, '.', ' ') + ' руб/т';
    }else{
        priceResult = '-';
    }

    var totalAreaObj = wArea.find('.total_values .volume_val');
    if(totalAreaObj.length > 0){
        totalAreaObj.text(volumeResult);
    }
    totalAreaObj = wArea.find('.total_values .price_val');
    if(totalAreaObj.length > 0){
        totalAreaObj.text(priceResult);
    }

    if(totalPrice > 0){
        if(nds_type === 'y') {
            //вычитаем ндс
            totalAdditPrice = totalPrice / (1 + 0.01 * nds_val);
        }else {
            //добавляем ндс
            totalAdditPrice = totalPrice + totalPrice * 0.01 * nds_val;
        }
        volumeAdditResult = 'y';
        priceAdditResult = number_format(totalAdditPrice.toString(), 0, '.', ' ') + ' руб/т';
    }

    //работа с дополнительными данными (если НДС поставщика не совпадает с НДС покупателя)
    // if(totalAdditVolume > 0){
    //     if(totalAdditPrice > 0){
    //         totalAdditPrice = Math.round(totalAdditPrice / totalAdditVolume);
    //     }
    //
    //     volumeAdditResult = number_format(totalAdditVolume.toString(), 0, '.', ' ') + ' т';
    // }

    // if(totalAdditPrice > 0){
    //     priceAdditResult = number_format(totalAdditPrice.toString(), 0, '.', ' ') + ' руб/т';
    // }

    var additArea = totalAreaObj = wArea.find('.additional_values');
    if(additArea.length == 1){
        if(volumeAdditResult != ''
            && priceAdditResult != ''
        ){
            // totalAreaObj = additArea.find('.volume_val');
            // if(totalAreaObj.length > 0){
            //     totalAreaObj.text(volumeAdditResult);
            // }
            totalAreaObj = additArea.find('.price_val');
            if(totalAreaObj.length > 0){
                totalAreaObj.text(priceAdditResult);
            }
            additArea.addClass('active');
        }else{
            additArea.removeClass('active');
        }
    }
}

//попытка копирования в буфер обмена текущей ссылки для поставщика
function copyAgentHref(argObj){
    var wObj = argObj.parent().siblings('.agent_counter_href_area').find('.agent_counter_href_value');
    if(copyToClipboard(wObj.text())){
        //данные скопированы в буфер обмена, показываем результат
        wObj.before('<div class="agent_counter_href_success_text">Текст со ссылкой скопирован в буфер обмена</div>');
        var del_obj = wObj.siblings('.agent_counter_href_success_text');
        setTimeout(function(){
            if(del_obj.length == 1){
                del_obj.remove();
            }
        }, 10000);
    }
}

//Создание ссылки на график с учетом отмеченных предложений
function makeGraphDataForClient(argObj){
    //получаем данные фильтра и страницу
    var additional_url_data = {
        culture: 0,
        wh: 0,
        region: 0,
        client: 0,
        page: 1,
        nds_type: 'n',
        graph_type: 'month',
        best_offer_price: '',
        best_offer_wh: 0,
        best_offer_nds: 'n',
        action: ''
    };
    var dataObj = $('.user_nds_value');
    if(typeof dataObj.attr('data-culture') != 'undefined'){
        additional_url_data.culture = dataObj.attr('data-culture');
    }
    if(typeof dataObj.attr('data-wh') != 'undefined'){
        additional_url_data.wh = dataObj.attr('data-wh');
    }
    if(typeof dataObj.attr('data-region') != 'undefined'){
        additional_url_data.region = dataObj.attr('data-region');
    }
    if(typeof dataObj.attr('data-client') != 'undefined'){
        additional_url_data.client = dataObj.attr('data-client');
    }
    if(typeof dataObj.attr('data-page') != 'undefined'){
        additional_url_data.page = dataObj.attr('data-page');
    }
    dataObj = $('#draw_object .nds_change_area input[type="checkbox"]:first');
    if(dataObj.length > 0){
        additional_url_data.nds_type = (dataObj.prop('checked') === true ? 'y' : 'n');
    }
    dataObj = $('.best_offer_data_div');
    if(dataObj.length > 0){
        additional_url_data.best_offer_wh = dataObj.attr('data-wh');
        additional_url_data.best_offer_nds = (dataObj.attr('data-nds') === 'y' ? 'y' : 'n');
        additional_url_data.cid = dataObj.attr('data-cofferid');
        additional_url_data.oid = dataObj.attr('data-offerid');
        additional_url_data.rid = dataObj.attr('data-requestid');
    }
    dataObj = $('.list_page_rows.counter_request_client_list .line_area[data-id="' + dataObj.attr('data-cofferid') + '"] .price');
    if(dataObj.length > 0){
        var checkObj = dataObj.find('.addit_nds_price');
        if(checkObj.length > 0){
            additional_url_data.best_offer_price = checkObj.text().replace(/(руб\/т| )/g, '');
        }else{
            additional_url_data.best_offer_price = dataObj.find('.val').text().replace(/(руб\/т| )/g, '');
        }
    }
    additional_url_data.graph_type = dataObj.attr('data-graphmode');

    //получаем выбранные предложения
    $('.list_page_rows.counter_request_client_list .line_area .disregard input[type="checkbox"]').each(function(cInd, cObj){
        var lineAreaObj = $(cObj).parents('.line_area');
        if($(cObj).prop('checked') === true
            && typeof lineAreaObj.attr('data-id') != 'undefined'
        ){
            if(additional_url_data.action != ''){
                additional_url_data.action += ',';
            }
            additional_url_data.action += lineAreaObj.attr('data-id');
        }
    });

    //отображаем данные в попапе
    $.post('/ajax/getGraphPopupText.php', additional_url_data, function(mes){
        if(mes != 0){
            var wObj = JSON.parse(mes);
            showTextLinkFeedbackForm(wObj.title, wObj.email, wObj.text, 0, wObj.uid, 'client_graph_href');
        }
    });
}

//попап с данными по странице для покупателя
function initPageURL() {
    $(document).on('click', '.page-url a' , function (e) {
        e.preventDefault();

        var $this = $(this),
            $totalVal   = $('.total_values .volume_val'),
            $totalPrice = $('.total_values .price_val');

        if ( typeof pageDataObject.WH_ID != 'undefined'
            && typeof pageDataObject.USER_ID != 'undefined'
            && typeof pageDataObject.CULTURE_ID != 'undefined'
        ) {
            $.post('/ajax/getUserInviteByPage.php', {
                uid        : pageDataObject.USER_ID,
                wh_id      : pageDataObject.WH_ID,
                culture_id : pageDataObject.CULTURE_ID,
                page       : pageDataObject.PAGE,
                checked    : pageDataObject.checked
            }, function (mes) {
                if (mes != 0) {
                    var nds_type = 'n'; //по умолчанию берем основные цены без НДС
                    var additPrice = 0, additText = '';
                    if($('.list_page_rows.counter_request_client_list .price span.val[data-nds="n"]:first').length === 0
                        && $('.list_page_rows.counter_request_client_list .price span.val[data-nds="y"]:first').length > 0
                    ){
                        //если все цены "по умолчанию" с НДС, то по ставим тип НДС
                        nds_type = 'y';
                    }

                    var user_nds_obj = $('.nds_value_div');
                    var nds_val = parseFloat(user_nds_obj.attr('data-ndsval'));
                    additPrice = parseInt($totalPrice.text().replace(/(руб\/т| )/g, ''));
                    if(nds_type == 'n'){
                        //добавляем ндс
                        additPrice = additPrice + additPrice * 0.01 * nds_val;
                        additText = ' / ' + number_format(additPrice.toString(), 0, '.', ' ') + ' руб/т (c НДС)';
                    }else{
                        //вычитаем ндс
                        additPrice = additPrice / (1 + 0.01 * nds_val);
                        additText = ' / ' + number_format(additPrice.toString(), 0, '.', ' ') + ' руб/т (без НДС)';
                    }

                    var url_val = mes,
                        haveEmail = 0;

                    if(pageDataObject.USER_EMAIL.length > 0){
                        haveEmail = 1;
                    }

                    var textPopup = pageDataObject.CULTURE_NAME + ', ' + pageDataObject.WH_NAME + "<br><br>"
                                    + 'Возможность купить: ' + $totalVal.text() + '<br><br>'
                                    + 'Средневзвешенная цена объёма: ' + $totalPrice.text() + additText + '<br><br>'
                                    + 'Выбрать и купить: ' + url_val;

                    showTextLinkFeedbackForm('Ссылка на страницу предложений покупателю', haveEmail, textPopup, 0, pageDataObject.USER_ID, 'page_client');
                }
            });
        }
    })
}

//Попап для организатора - уточнение цены
function getPricePopup(iRequestId){
    $.post('/ajax/clarifyCounterRequestPrice.php', {
        REQUEST_ID: iRequestId
    }, function (mes) {
        if(mes != 1){
            defaultPopupShow('Уточнение цены у покупателя', mes);
            $('#def_popup_window').addClass('link_text_w clarify_price');
        }
    });
}