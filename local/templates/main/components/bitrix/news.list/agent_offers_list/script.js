var stop_slide_anim = 0;
var stop_change_quality_approve = 0;
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

    $(document).on('click', '.js-copy', function () {
        if(!$(this).hasClass('html_val')){
            if(copyToClipboard( $(this).parent().find('.copy-text').text())){
               // alert('Текст скопирован в буфер обмена');
            }
        }
        //копирование html
        else{
            var argObj = $(this);
            var sHtmlVal = argObj.siblings('.copy-text').html();

            //очистка html
            $.post('/ajax/normalizeHtmlText.php',{ html: sHtmlVal },function(mes){
                copyLinkHtml(mes);

                //показываем текст об успешном копировании
                argObj.siblings('.copy-text').before('<div class="copy-textallowed">Сообщение скопировано в буфер обмена</div>');
                var copyObj = argObj.siblings('.copy-textallowed:last');
                setTimeout(function(){
                    copyObj.remove();
                }, 3000);
            });
        }
    });

    //ссылка на вп от агента для поставщика
    $(document).on('click', '.make_by_agent_counter_href', function(){

        var wObj = $(this);
        var html_val = '';
        var volume_obj = wObj.siblings('.counter_volume_input');

        var host_container = $(this).parents('.list_page_rows');
        var offer_data_obj = $(this).parents('form').find('input[type="hidden"][name="offer"]');
        var haveEmail = wObj.attr('data-haveEmail');
        if(host_container.length == 1
            && typeof host_container.attr('data-host') != 'undefined'
            && typeof wObj.attr('data-href') != 'undefined'
        ){
            if(offer_data_obj.length == 1
                && typeof wObj.attr('data-uid') != 'undefined'
                && wObj.attr('data-uid') != ''
            ){
                var vol_val = 0;
                if(volume_obj.length == 1){
                    //добавляем объем в запрос, если требуется
                    vol_val = parseInt(volume_obj.val());
                    if(isNaN(vol_val)
                        || vol_val < 1
                    ){
                        vol_val = 0;
                    }
                }

                $.post('/ajax/getUserInviteHref.php', {
                    uid: wObj.attr('data-uid'),
                    offer_id: offer_data_obj.val(),
                    vol: vol_val,
                    no_best: 1
                }, function(mes){
                    if(mes != 0){
                        html_val = mes;
                        wObj.attr('data-url', html_val);
                        if(wObj.siblings('.agent_counter_href_value').length == 0) {
                            //wObj.parent().append('<div class="agent_counter_href_value"></div>');
                            //wObj.siblings('.agent_counter_href_value').html(html_val);
                            showTextLinkFeedbackForm('Ссылка для создания предложения',haveEmail,html_val,vol_val,wObj.attr('data-uid'),'farmer');
                        }else{
                            //wObj.siblings('.agent_counter_href_value').html(html_val);
                            showTextLinkFeedbackForm('Ссылка для создания предложения',haveEmail,html_val,vol_val,wObj.attr('data-uid'),'farmer');
                        }
                        //copyAgentHref(wObj, vol_val);
                    }
                });
            }
        }
    });
    //отображаем график, если развернуто предложение
    var checkGraphObj = $('.list_page_rows.farmer_offer .line_area.active .line_additional .prop_area.with_graph');
    if(checkGraphObj.length == 1){
        //отображаем/строим график, если есть данные
        showOfferGraph(checkGraphObj.parents('.line_area'));
    }


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


    $('.list_page_rows .line_area .line_inner, .list_page_rows .line_area .line_additional .hide_but').on('click', function(e){
        if(
            stop_slide_anim == 0
            && $(e.target).closest('.avail_status').length === 0
        ){
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

    //добавление в выпадашки данных о создании встречных предложений, если требуется
    var create_counter_data = $('.send_counter_req_data').each(function(cInd, cObj){

        var cur_offer_input = $('.list_page_rows input[name="offer"][type="hidden"][value="' + $(cObj).attr('data-offer') + '"]'),
            farmerID = cur_offer_input.closest('.line_additional').find("[name='farmerID']").val(),
            farmerAccess = cur_offer_input.closest('.line_additional').find("[name='farmerAccess']").val(),
            haveEmail = cur_offer_input.closest('.line_additional').find("[name='haveEmail']").val();

        if(cur_offer_input.length == 1){
            if($(cObj).attr('data-reqs')!=''){
                var addForm = '';
                var recom_text = $(cObj).attr('data-rec');

                if(recom_text !== '') {
                    if (parseInt(farmerAccess) == 1) {
                        addForm = '<div class="prop_area adress_val counter_rec_href">\n' +
                            '<div class="adress"Отправка ссылки на создание предложения</div>' +
                            '<div class="submit-btn make_by_agent_counter_href" data-haveEmail="' + haveEmail + '" data-uid="' + farmerID + '" data-href="">Ссылка для создания предложения</div>\n' +
                            '<input class="counter_volume_input" type="text" name="counter_volue" placeholder="Объём" />\n' +
                            '<div class="clear"></div>\n' +
                            '</div>';
                    } else {
                        addForm = '<div class="prop_area adress_val">' +
                            '<div class="adress">Отправка ссылки на создание предложения</div>' +
                            'Для создания ссылки на предложение необходимо <a href="/profile/make_full_mode/?uid=' + farmerID + '">заполнить профиль</a> поставщика' +
                            '</div>';
                    }
                }

                cur_offer_input.parents('.line_area').find('.prop_area.with_graph').after('<div class="prop_area adress_val counter_data">'
                    + '<div class="adress">Отправка/создание предложения:</div>'
                    + '<div class="val_adress">'
                    + '<div class="counter_request_additional_data">'
                    + '<div class="row first_row">'
                    + '<div class="row_val">'
                    + '<input type="text" data-checkval="y" data-checktype="pos_int" name="volume" placeholder="Указать количество тонн" value=""><span class="ton_pos">т.</span>'
                    + '</div></div>'
                    + '<div class="row">'
                    + '<div>'
                    + (recom_text !== '' ? recom_text : '')
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

                    + '<div class="prop_area adress_val additional_options active">'

                    + '<div class="val_adress slide-description">'
                    + '<div class="radio_group">'
                    + '<div class="radio_area">'
                    + '<input type="checkbox" data-text="<div class=\'custom_data_text\'><span class=\'option-name\' onclick=\'showAdditOptions(this);\'>Сопровождение сделки<i></i></span></div>" name="IS_AGENT_SUPPORT" value="Y" class="customized"><div class="custom_input checkbox" data-name="IS_AGENT_SUPPORT"><div class="ico"></div><div class="custom_data_text"><span class="option-name" onclick="showAdditOptions(this);">Сопровождение сделки<i></i></span></div></div>'
                    + '</div><div class="price_value">' + counter_option_support + ' руб/т</div></div>'
                    + '<div class="option-description">'
                    + '<p>Помощь в поиске информации, необходимой для совершения сделки. Ваш консьерж в исполнение сделки.</p>'
                    // + '<p>После заказа вы договариваетесь с партнером об агентских условиях (комиссия ±0,5% от суммы сделки, потребность финансирования и т.д.), заключаете агентский договор, и агент исполняет, сопровождает сделку.</p>'
                    // + '<p><a href="/upload/docs/Договор оказания услуг Агента (на ЭТП).docx" download="Договор оказания услуг Агента (на ЭТП).docx">Договор оказания услуг Агента (на ЭТП)</a></p>'
                    // + '<p><a href="/upload/docs/Договор оказания услуг Агента (без ЭТП).docx" download="Договор оказания услуг Агента (без ЭТП).docx">Договор оказания услуг Агента (без ЭТП)</a></p>'
                    + '</div>'
                    + '</div>'

                    //+ '<span class="minus minus_bg" data-step="50" onclick="partnerClickServiceMinusPrice(this);"></span>'
                    //+ '<span class="plus plus_bg" data-step="50" onclick="partnerClickServicePlusPrice(this);"></span>'
                    + '<div class="partner_price_part"><span class="val"></span> руб/т</div>'
                    + '<div class="adress partner_price">Стоимость услуги, руб: <input type="text" readonly="readonly" name="partner_service_price" value="0" /></div>'

                    //+ '<div class="val_adress slide-description">'
                    //+ '<div class="radio_group">'
                    //+ '<div class="radio_area">'
                    ////оставил на случай возвращения выпадашки с описанием + '<input type="checkbox" checked="checked" readonly="readonly" disabled="disabled" data-text="<div class=\'custom_data_text\'><span class=\'option-name\' onclick=\'showAdditOptions(this);\'>Заключение договора<i></i></span></div>" name="IS_AGENT_SERVICE" value="Y" class="customized"><div class="custom_input checkbox checked" data-name="IS_AGENT_SERVICE"><div class="ico"></div><div class="custom_data_text"><span class="option-name" onclick="showAdditOptions(this);">Заключение договора<i></i></span></div></div>'
                    //+ '<input type="checkbox" checked="checked" data-text="Отбор проб и лабораторная диагностика" name="IS_AGENT_SERVICE" value="Y" class="customized"><div class="custom_input checkbox checked" data-name="IS_AGENT_SERVICE"><div class="ico"></div>Отбор проб и лабораторная диагностика</div>'
                    //+ '</div></div>'
                    ////+ '<div class="option-description">'
                    ////+ '<p>После заказа услуги, мы заключаем договор с агропроизводителем в вашей редакции и от вашего имени.</p>'
                    ////+ '</div>'
                    //+ '</div>'

                    //+ '<div class="val_adress slide-description">'
                    //+ '<div class="radio_group">'
                    //+ '<div class="radio_area">'
                    ////оставил на случай возвращения выпадашки с описанием + '<input type="checkbox" data-text="<div class=\'custom_data_text\'><span class=\'option-name\' >Отбор проб и лабораторная диагностика<i></i></span></div>" name="IS_ADD_CERT" value="Y" class="customized"><div class="custom_input checkbox" data-name="IS_ADD_CERT"><div class="ico"></div><div class="custom_data_text"><span class="option-name" onclick="showAdditOptions(this);">Отбор проб и лабораторная диагностика<i></i></span></div></div>'
                    //+ '<input type="checkbox" data-text="Отбор проб и лабораторная диагностика" name="IS_ADD_CERT" value="Y" class="customized"><div class="custom_input checkbox" data-name="IS_ADD_CERT"><div class="ico"></div>Отбор проб и лабораторная диагностика</div>'
                    //+ '</div>'
                    //+ '</div>'
                    ////+ '<div class="option-description">'
                    ////+ '<p>Данная услуга позволяет определить качество, с помощью независимой лаборатории - партнера АГРОХЕЛПЕР, для предварительной оценки и принятия решения о покупке. </p>'
                    ////+ '<p>После заказа услуги вы заключаете договор с нашим партнером, оплачиваете, и он исполняет услугу.</p>'
                    ////+ '<p><a href="/upload/docs/Договор на получение результатов исследований.doc" download="Договор на получение результатов исследований.doc">Договор на получение результатов исследований</a></p>'
                    ////+ '<p><a href="/upload/docs/Договор на проведение исследований и испытаний образцов продукции.docx" download="Договор на проведение исследований и испытаний образцов продукции.docx">Договор на проведение исследований и испытаний образцов продукции</a></p>'
                    ////+ '<p><a href="/upload/docs/Пример карточки анализа.pdf" download="Пример карточки анализа.pdf">Пример карточки анализа</a></p>'
                    ////+ '</div>'
                    //+ '</div>'

                    //+ '<div class="val_adress slide-description">'
                    ////+ '<div><span class="option-name" onclick="showAdditOptions(this);">Сопроводительные документы, в т.ч:<i></i></span><br><br></div>'
                    ////+ '<div class="option-description">'
                    ////+ '<p>После заказа вы заключаете договор с нашим партнёром, оплачиваете, и он исполняет сделку.</p>'
                    ////+ '</div><br>'
                    //+ '<div class="radio_group">'
                    //+ '<div class="radio_area">'
                    //+ '<input type="checkbox" data-text="Карантинное свидетельство" name="IS_BILL_OF_HEALTH" value="Y" class="customized"><div class="custom_input checkbox" data-name="IS_BILL_OF_HEALTH"><div class="ico"></div>Карантинное свидетельство</div>'
                    //+ '</div>'
                    //+ '<div class="radio_area">'
                    //+ '<input type="checkbox" data-text="Ветеринарные свидетельства" name="IS_VET_CERT" value="Y" class="customized"><div class="custom_input checkbox" data-name="IS_VET_CERT"><div class="ico"></div>Ветеринарные свидетельства</div>'
                    //+ '</div>'
                    //+ '<div class="radio_area">'
                    //+ '<input type="checkbox" data-text="Декларация о соответствии" name="IS_QUALITY_CERT" value="Y" class="customized"><div class="custom_input checkbox" data-name="IS_QUALITY_CERT"><div class="ico"></div>Декларация о соответствии</div>'
                    //+ '</div>'
                    //+ '</div></div>'

                    //+ '<div class="val_adress slide-description">'
                    //+ '<div class="radio_group">'
                    //+ '<div class="radio_area">'
                    //+ '<input type="checkbox" data-text="<div class=\'custom_data_text\'><span class=\'option-name\' onclick=\'showAdditOptions(this);\'>Транспортировка<i></i></span></div>" name="IS_TRANSFER" value="Y" class="customized"><div class="custom_input checkbox" data-name="IS_TRANSFER"><div class="ico"></div><div class="custom_data_text"><span class="option-name" onclick="showAdditOptions(this);">Транспортировка<i></i></span></div></div>'
                    //+ '</div></div>'
                    //+ '<div class="option-description">'
                    //+ '<p>После заказа вы заключаете договор с нашим партнёром, оплачиваете, и он исполняет сделку.</p>'
                    //+ '<p>Тариф по договоренности, но стартовым будет тот, который учтен в расчете базисной цены данного товара.</p>'
                    //+ '<p><a href="/upload/docs/Договор на перевозку грузов автомобильным транспортом.docx" download="Договор на перевозку грузов автомобильным транспортом.docx">Договор на перевозку грузов автомобильным транспортом</a></p>'
                    //+ '</div></div>'

                    //+ '<div class="val_adress slide-description">'
                    //+ '<div class="radio_group">'
                    //+ '<div class="radio_area">'
                    //+ '<input type="checkbox" data-text="<div class=\'custom_data_text\'><span class=\'option-name\' onclick=\'showAdditOptions(this);\'>Безопасная сделка<i></i></span></div>" name="IS_SECURE_DEAL" value="Y" class="customized"><div class="custom_input checkbox" data-name="IS_SECURE_DEAL"><div class="ico"></div><div class="custom_data_text"><span class="option-name" onclick="showAdditOptions(this);">Безопасная сделка<i></i></span></div></div>'
                    //+ '</div></div>'
                    //+ '<div class="option-description">'
                    //+ '<p>Данная услуга позволяет вам сделать безопасную предоплату.</p>'
                    //+ '<p>Предоплата перечисляется на специальный счет, который разблокируется после приемки товара.</p>'
                    //+ '<p><a href="/upload/docs/Договор купли-продажи (расчеты через номинальный счет).docx" download="Договор купли-продажи (расчеты через номинальный счет).docx">Договор купли-продажи (расчеты через номинальный счет)</a></p>'
                    //+ '</div></div>'

                    + '</div>'

                    + '<input type="button" name="save" value="Отправить предложение" class="submit-btn counter_request_submit">'
                    + '</div></div>'
                    + '<div class=" refinement_text"><br>'
                    //+ 'Сделайте предложение, чтобы покупатель увидел ваши намерения и связался с вами в случае заинтересованности. '
                    + 'Срок действия предложения - 7 дней.'
                    + '</div>'
                    + '</div>'
                    + addForm

                );

                //рассчитываем стоимость агенстких услуг
                recountCounterOfferPartnerPrice(cur_offer_input.parents('.line_area'));
            }else if(
                $(cObj).html() === ''
                && !$(cObj).hasClass('send_descr')
            ){
                var lineObj = cur_offer_input.parents('.line_area').find('.prop_area.total').before('<div class="prop_area adress_val">У товара нет запросов</div>');
            }else if($(cObj).html() != ''){
                $('.line_inner[data-offer="' + $(cObj).attr('data-offer') + '"]').addClass('answered');
                if($(cObj).hasClass('send_descr')){
                    var lineObj = cur_offer_input.parents('.line_area').find('.prop_area.counter_data').before('<div class="prop_area counter_offer_area adress_val"><span class="copy-text"></span><div class="js-copy copy-left html_val">Скопировать</div></div>');
                }else {
                    var lineObj = cur_offer_input.parents('.line_area').find('.prop_area.total').before('<div class="prop_area counter_offer_area adress_val"><span class="copy-text"></span><div class="js-copy copy-left html_val">Скопировать</div></div>');
                }
                var objW = cur_offer_input.parents('.line_area');
                if(objW.length > 0){
                    objW.find('.counter_offer_area .copy-text').html($(cObj).html());
                    if(objW.find('.no_cancel_counter_offer_area').length === 0) {
                        objW.find('.js-copy.copy-left').after('<div class="cancel_counter_offer_area"><a class="submit-btn" href="javascript: void(0);">Отменить предложение</a></div>');
                    }
                }
            }
        }
    });

    //пересчет цен услуги партнера при изменении объёма или цены
    $('.list_page_rows.farmer_offer').on('keyup', '.line_additional input[name="volume"]', function(){
        recountCounterOfferPartnerPrice($(this).parents('.line_area'));
        //recountPartnerServicePrice($(this).parents('.line_additional'));

        //убираем ошибку, если была
        var errObj = $(this).siblings('.error_msg');
        if(errObj.length === 1){
            errObj.remove();
        }
    });
    $('.list_page_rows.farmer_offer').on('keyup change', '.line_additional input[name="price"]', function(){
        var wObj = $(this);
        setTimeout(function(){
            recountCounterOfferPartnerPrice(wObj.parents('.line_area'));
            //recountPartnerServicePrice(wObj.parents('.line_additional'));
        }, 50);
    });

    //работа чекбоксов
    $('.list_page_rows.farmer_offer .customized').click(function () {
        var bChecked = $(this).prop('checked');
        if(bChecked) {
            $(this).siblings('.custom_input').addClass('checked');
        } else {
            $(this).siblings('.custom_input').removeClass('checked');
        }
    });
    //обработка внесения стоимости услуги
    $('.list_page_rows.farmer_offer').on('change', 'input[name="partner_service_price"]', function(){
        checkMask($(this), 'price_not_empty_f');

        //убираем ошибку, если была
        var errObj = $(this).parents('.additional_options').find('.partner_service_price_error');
        if(errObj.length === 1){
            errObj.remove();
        }

        var cVal = $(this).val().replace(' ', '');

        if(!isNaN(parseInt(cVal))
                && parseInt(cVal) > 0
        ){
            var workArea = $(this).parents('.line_additional');
            var volumeObj = workArea.find('input[name="volume"]');
            var workObj = workArea.find('.partner_price_part .val');
            if(volumeObj.length === 1
                && !isNaN(parseInt(volumeObj.val()))
                && parseInt(volumeObj.val()) > 0
            ){
                //обновляем значение в цене за тонну
                workObj.text(number_format(Math.round(cVal / volumeObj.val()), 0, '.', ' '));
            }
        }
    });

    //обработка смены сопровождения
    $('.list_page_rows.farmer_offer .line_additional').on('change', '.additional_options, input[name="quality_approved"]', function(){
        recountCounterOfferPartnerPrice($(this).parents('.line_area'));
    });

    // $('.list_page_rows.farmer_offer').on('focus', '.counter_request_additional_data input[name="price"]', function (){
    //     if(isMobileMode() === true){
    //         $(this).val('');
    //     }
    // });

    //ввод значений не меньше/больше установленных рамок
    $('.list_page_rows.farmer_offer').on('change', '.counter_request_additional_data input[name="price"]', function () {
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
    $('.list_page_rows.farmer_offer').on('keydown', '.counter_request_additional_data input', function(e){
        if(e.keyCode == 13) {
            e.preventDefault();
            return false;
        }
    });

    $('.list_page_rows.farmer_offer').on('click', '.counter_request_additional_data input[type="button"][name="save"]', function(){
        var counterObjArea = $(this).parents('.counter_request_additional_data');
        var wObjArea = counterObjArea.parents('.line_area');

        var err_ob, volume_val = 0, offer_id = 0, price_val = 0, can_deliver = 0, lab_trust = 0;

        volume_val = parseInt(counterObjArea.find('input[name="volume"]').val());
        if(isNaN(volume_val) || volume_val == 0){
            //ошибка - не указан объём
            err_ob = counterObjArea.find('input[name="volume"]').siblings('.error_msg');
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

        //дополнительные опции и стоимость услуги
        var coffer_type = 'c', addit_partner_price = 0, addit_IS_ADD_CERT = '0', addit_IS_BILL_OF_HEALTH = '0', addit_IS_VET_CERT = '0', addit_IS_QUALITY_CERT = '0', addit_IS_TRANSFER = '0', addit_IS_SECURE_DEAL = '0', addit_IS_AGENT_SUPPORT = '0', partner_quality_approved = '0', partner_quality_approved_d = '';

        var wObj = wObjArea.find('input[name="is_partner_offer"]');
        // if(wObj.length == 1
        //     && wObj.prop('checked') === true
        // ){
        //     coffer_type = 'p';
        // }
        coffer_type = 'p';//всегда ставим агентские предложения

        if(coffer_type === 'p') {

            //проверка цены
            wObj = wObjArea.find('input[name="partner_service_price"]');
            if(wObj.length == 0
                || isNaN(parseInt(wObj.val()))
                || parseInt(wObj.val()) === 0
            ){
                //ошибка цены
                err_ob = wObjArea.find('.partner_service_price_error');
                if(err_ob.length === 0){
                    wObj.parents('.additional_options').prepend('<div class="error_msg partner_service_price_error"></div>');
                    err_ob = wObjArea.find('.partner_service_price_error');
                }
                err_ob.text('Не указана стоимость услуги');
                err_ob.addClass('active');
                return false;
            }else{
                addit_partner_price = wObj.val();
            }

            wObj = wObjArea.find('input[name="IS_AGENT_SUPPORT"]');
            if (wObj.length === 1
                && wObj.prop('checked') === true
            ) {
                addit_IS_AGENT_SUPPORT = 1;
            }
        }

        wObj = wObjArea.find('input[name="quality_approved"]');
        if (wObj.length == 1) {
            partner_quality_approved = (wObj.prop('checked') ? 1 : 0);

            wObj = wObj.parents('.quality_approved_padding').find('.date_val');
            if (wObj.length == 1) {
                partner_quality_approved_d = wObj.text();
            }
        }

        //отправляем встречные запросы
        if(volume_val > 0
            && offer_id > 0
        ){
            $.post('/partner/offer/', {
                send_counter_offer_ajax: 'y',
                offer_id: offer_id,
                volume: volume_val,
                price: price_val,
                can_deliver: can_deliver,
                lab_trust: lab_trust,
                coffer_type: coffer_type,
                addit_partner_price: addit_partner_price,
                addit_is_agent_support: addit_IS_AGENT_SUPPORT,
                partner_quality_approved: partner_quality_approved,
                partner_quality_approved_d: partner_quality_approved_d
            }, function(mes){
                if(mes != 1){
                    //успех -> для организатора ставим текст с кнопкой отмены
                    var objItemArea = counterObjArea.parents('.line_area');
                    objItemArea.find('.prop_area.counter_rec_href').remove();
                    objItemArea.find('.prop_area.counter_data').replaceWith(mes);
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

    $('.radio_group input[name="quality_approved"]').on('change', function(){
        if(stop_change_quality_approve === 0){
            stop_change_quality_approve = 1;
            var wObj = $(this);
            wObj.attr('disabled', 'disabled');
            wObj.siblings('.custom_input').addClass('disabled');
            var offer_id = wObj.attr('data-offerid');
            var quality_approved = (wObj.prop('checked') ? 1 : 0);
            $.post('/ajax/changeOfferQualityApprove.php', {
                offer_id: offer_id,
                q_approved: quality_approved
            }, function(mes){
                stop_change_quality_approve = 0;
                wObj.removeAttr('disabled');
                wObj.siblings('.custom_input').removeClass('disabled');

                //проверка возврашенной даты
                if(mes.length > 0){
                    var areaObj = wObj.parents('.quality_approved_padding');
                    var dateobj = areaObj.find('.date_val');

                    if(mes.length > 2
                        && mes.substr(1, 1) === ';'
                    ){
                        var dateStr = mes.substr(2, mes.length - 2);
                        if(dateobj.length === 0){
                            if(quality_approved) {
                                areaObj.prepend('<div class="date_area">Дата подтверждения:<span class="date_val"></span></div>');
                                dateobj = areaObj.find('.date_val');
                            }
                        }

                        if(quality_approved && dateobj.length > 0){
                            dateobj.text(dateStr);
                        }
                    }

                    if(dateobj.length > 0
                        && !quality_approved
                    ){
                        dateobj.parent().remove();
                    }
                }
            });
        }
    });

    //клик на ссылку для отображения попапа для графика
    $('.list_page_rows').on('click', '.offer_graph_send', function(){
        var objOfferArea = $(this).parents('.line_area');
        var sResultHtml = '', sWHAdres = '', iLastPrice = 0, iDiffPrice = 0, iTemp = 0, sEmail = '', iUid = 0;

        //собираем данные для попапа
        var objW = objOfferArea.find('.line_inner .warhouse_name.with_name');
        if(objW.length > 0){
            sWHAdres = objW.text();
        }

        objW = objOfferArea.find('.line_additional .prop_area.wh_addr .val_adress');
        if(
            objW.length > 0
            && typeof objW.attr('data-regionname') !== 'undefilned'
        ){
            sWHAdres += ', ' + (objW.attr('data-regionname'));
        }

        objW = objOfferArea.find('.with_graph');
        if(typeof objW.attr('data-spros1') != 'undefined'){
            iTemp = parseInt(objW.attr('data-spros1'));
            if(iTemp){
                iLastPrice = iTemp;
            }

            iTemp = parseInt(objW.attr('data-sprosdif'));
            if(iTemp){
                iDiffPrice = iTemp;
            }
        }

        if(typeof objOfferArea.attr('data-email') != 'undefined'){
            sEmail = objOfferArea.attr('data-email');
        }

        objW = objOfferArea.find('input[name="farmerID"]');
        if(objW.length > 0){
            iUid = objW.val();
        }

        sResultHtml = 'Сегодня самая высокая цена спроса на ' + objOfferArea.find('.line_inner div.name').text() + ' на складе ' + sWHAdres  + ' с вашим качеством  - ' + iLastPrice + ' руб/т.';
        if(iDiffPrice > 0){
            sResultHtml += ' Изменения за последний день рост на ' + iDiffPrice + ' руб/т.';
        }else if(iDiffPrice < 0){
            sResultHtml += ' Изменения за последний день падение на ' + iDiffPrice + ' руб/т.';
        }else{
            sResultHtml += ' За последний день цена не изменилась.';
        }
        //берем текст из шаблона
        objW = $('.graph_template_data:first');
        if(
            objW.length === 1
            && objW.text().length !== 0
        ){
            sResultHtml = objW.html();

            //меняем параметры шаблона на значения
            sResultHtml = sResultHtml.replace('#OFFER_GRAPH_CULTURE#', objOfferArea.find('.line_inner div.name').text());
            sResultHtml = sResultHtml.replace('#OFFER_GRAPH_WH_NAME#', sWHAdres);
            sResultHtml = sResultHtml.replace('#OFFER_GRAPH_LASTPRICE#', iLastPrice);

            if(iDiffPrice > 0){
                sResultHtml = sResultHtml.replace('#OFFER_GRAPH_DIFFTEXT#', ' Изменения за последний день рост на ' + iDiffPrice + ' руб/т.');
            }else if(iDiffPrice < 0){
                sResultHtml = sResultHtml.replace('#OFFER_GRAPH_DIFFTEXT#', ' Изменения за последний день падение на ' + iDiffPrice + ' руб/т.');
            }else{
                sResultHtml = sResultHtml.replace('#OFFER_GRAPH_DIFFTEXT#', ' За последний день цена не изменилась.');
            }
        }
        sResultHtml = '<div class="agent_counter_href_value">' + sResultHtml + '</div>';

        if(sEmail != '') {
            sResultHtml += '<input class="submit-btn offer_pop b_left" type="button" onclick="sendTextLinkToEmail(this,' + iUid + ',\'farmer_offer_graph\');" value="Отправить по email" />';
        }
        sResultHtml += '<input class="submit-btn offer_pop b_right" type="button" onclick="copyLinkText(this,0,\'farmer_offer_graph\');" value="Копировать">';

        defaultPopupShow('Данные спроса товара', sResultHtml);
        $('#def_popup_window').addClass('gr_offer link_text_w');
    });

    $('.list_page_rows.farmer_offer').on('click', '.cancel_counter_offer_area a', function(){
        var objOffer = $(this).parents('.line_additional').find('input[name="offer"]');
        if(
            objOffer.length === 1
        ) {
            stopCounterOffer(objOffer.val());
        }
    });

    //изменение статуса товара "в наличии"/"продан"
    $('input[name="STATUS_AVAILABLE"]').on('change', function(){
        var wObj = $(this);
        var offerAvailableStatus = 0;
        if(
            wObj.prop('checked') === false
            && typeof wObj.attr('data-yesid') != 'undefined'
        ){
            offerAvailableStatus = wObj.attr('data-yesid');
        }else if(
            wObj.prop('checked') === true
            && typeof wObj.attr('data-noid') != 'undefined'
        ){
            offerAvailableStatus = wObj.attr('data-noid');
        }
        var offerId = wObj.parents('.line_area').find('.line_additional input[type="hidden"][name="offer"]').val();

        $.post('/ajax/changeOfferAvailability.php', {
            offer_id: offerId,
            stat_id: offerAvailableStatus
        }, function (mes) {
            //устанавливаем / обновляем дату
            if(
                mes !== ''
                && mes !== 1
            ){
                var dateObj = wObj.parents('.val_adress').find('.avail_date .val');
                if(dateObj.length === 0){
                    wObj.parents('.val_adress').prepend('<div class="avail_date">Дата изменения: <span class="val">19.05.2020 16:16:59</span></div>');
                    dateObj = wObj.parents('.val_adress').find('.avail_date .val');
                }

                //устанавливаем значение в плашке
                if(
                    wObj.prop('checked') === false
                    && typeof wObj.attr('data-yestext') != 'undefined'
                ){
                    wObj.parents('.line_area').find('.avail_status').toggleClass('no').text(wObj.attr('data-yestext'));
                }else if(
                    wObj.prop('checked') === true
                    && typeof wObj.attr('data-notext') != 'undefined'
                ){
                    wObj.parents('.line_area').find('.avail_status').toggleClass('no').text(wObj.attr('data-notext'));
                }

                //устанавливаем дату
                dateObj.text(mes);
            }
        });
    });
});


//показывает график (строит его, если он не был построен ранее и есть даныне для построения)
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

                    //ставим данные за последние два дня спроса и кнопку (для попапа для отправки поставщику)
                    var objOfferGraphArea = graphObj.parents('.with_graph');
                    if(objOfferGraphArea.length > 0){
                        if(
                            typeof objOfferGraphArea.attr('data-spros1') == 'undefined'
                            && catList.length > 1
                        ){
                            var sLastDate = catList[catList.length - 1], sPrevLastDate = catList[catList.length - 2];
                            var sLastDatePriceData = '', sPrevLastDatePriceData = '';

                            for (var i = tempData.length - 1; i >= 0; i--) {
                                if(sLastDatePriceData === ''){
                                    sLastDatePriceData = tempData[i];
                                }else if(sPrevLastDatePriceData === ''){
                                    sPrevLastDatePriceData = tempData[i];
                                }else{
                                    break;
                                }
                            }
                            setAgentOfferGraphPopupData(objOfferGraphArea, sLastDate, sPrevLastDate, sLastDatePriceData, sPrevLastDatePriceData);
                        }
                    }

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

                //данные для графика "Сделки" (сначала ищем по текущему региону)
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
                                        //return Highcharts.numberFormat(this.value / 1000, 1);
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
            wObj.val(number_format(new_price, 0, '.', ' ')).trigger('change');
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
            wObj.val(number_format(new_price, 0, '.', ' ')).trigger('change');
        }
    }
}

//клик по минусу при установлении цены услуг партнера
function partnerClickServiceMinusPrice(argObj){
    var wObj = $(argObj).siblings('.partner_price').find('input[name="partner_service_price"]');
    if(wObj.length == 1){

        var min_price = 0;
        var step_val = parseInt($(argObj).attr('data-step'));
        var cur_price = parseInt(wObj.val().replace(' ', ''));
        var new_price = 0;

        if(isNaN(cur_price)){
            cur_price = 0;
        }

        if(!isNaN(min_price)
            && !isNaN(step_val)
        ){
            new_price = cur_price - step_val;
            if(new_price < min_price){
                new_price = min_price;
            }
            wObj.val(number_format(new_price, 0, '.', ' ')).trigger('change');
        }
    }
}

//клик по плюсу при установлении цены услуг партнера
function partnerClickServicePlusPrice(argObj){
    var wObj = $(argObj).siblings('.partner_price').find('input[name="partner_service_price"]');
    if(wObj.length == 1){
        var step_val = parseInt($(argObj).attr('data-step'));
        var cur_price = parseInt(wObj.val().replace(' ', ''));
        var new_price = 0;

        if(isNaN(cur_price)){
            cur_price = 0;
        }

        if(!isNaN(step_val)){
            new_price = cur_price + step_val;
            wObj.val(number_format(new_price, 0, '.', ' ')).trigger('change');
        }
    }
}


//попытка копирования в буфер обмена текущей ссылки для поставщика
function copyAgentHref(argObj, volumeVal){
    var wObj = argObj.siblings('.agent_counter_href_value');
    if(copyToClipboard(wObj.text())){
        //данные скопированы в буфер обмена, показываем результат
        var del_obj = wObj.siblings('.agent_counter_href_success_text');
        var text_val = 'Ссылка скопирована в буфер обмена';
        if(volumeVal > 0){
            text_val += ', с объемом ' + volumeVal;
        }else{
            text_val += ', без объема';
        }
        if(del_obj.length == 0) {
            wObj.before('<div class="agent_counter_href_success_text">' + text_val + '</div>');
            del_obj = wObj.siblings('.agent_counter_href_success_text');
            setTimeout(function () {
                if (del_obj.length == 1) {
                    del_obj.remove();
                }
            }, 20000);
        }else{
            del_obj.remove();
            wObj.before('<div class="agent_counter_href_success_text">' + text_val + '</div>');
            del_obj = wObj.siblings('.agent_counter_href_success_text');
            setTimeout(function () {
                if (del_obj.length == 1) {
                    del_obj.remove();
                }
            }, 20000);
        }
    }
}

//открыть/свернуть дополнительные опции
function showAdditOptions(argObj){
    $(argObj).closest('.slide-description').toggleClass('active');
}

//переключение между типами предложения (организатор/пользователь)
function toggleAdditional(argObj){
    var thisObj = $(argObj);
    var wObj = thisObj.parents('.additional_label').siblings('.additional_options');
    if(wObj.length == 1){
        if(thisObj.prop('checked') === true){
            wObj.addClass('active');
        }else{
            wObj.removeClass('active');
        }
    }
}

//пересчёт цены услуги организатора
function recountPartnerServicePrice(line_additional_obj){
    if(!isNaN(partner_price_coef)
        && partner_price_coef > 0
    ){
        var partnerPriceWithoutVal = 0;
        var priceObj = line_additional_obj.find('input[name="price"]');
        var volObj = line_additional_obj.find('input[name="volume"]');
        var partnerPriceObj = line_additional_obj.find('input[name="partner_service_price"]');
        if(priceObj.length === 1
            && volObj.length === 1
            && partnerPriceObj.length === 1
        ){
            var priceVal = parseInt(priceObj.val().toString().replace(' ', ''));
            var volVal = parseInt(volObj.val().toString().replace(' ', ''));
            var partnerPriceVal = 0;
            if(!isNaN(priceVal)
                && priceVal > 0
                && !isNaN(volVal)
                && volVal > 0
            ){
                partnerPriceWithoutVal = Math.round(priceVal * partner_price_coef / 10000);
                partnerPriceVal = Math.round(partnerPriceWithoutVal * volVal);
                partnerPriceObj.val(number_format(partnerPriceVal, 0, '.', ' '));
                set_correct_val = true;
            }
        }

        //обновляем цену за тонну
        if(partnerPriceWithoutVal > 0){
            line_additional_obj.find('.partner_price_part').addClass('active').find('.val').text(number_format(partnerPriceWithoutVal, 0, '.', ' '));

            //убираем ошибку, если была
            var errObj = $('.additional_options .partner_service_price_error');
            if(errObj.length === 1){
                errObj.remove();
            }
        }
    }
}

//установление данных для попапа по графику товара
function setAgentOfferGraphPopupData(objOfferGraphArea, sLastDate, sPrevLastDate, sLastDatePriceData, sPrevLastDatePriceData){
    var arTemp = '';
    //проверяем есть ли последняя цена (sLastDate - последняя дата из спика всех дат, sLastDatePriceData - последние данные цены из спика цен)
    if(
        sLastDate != ''
        && sLastDatePriceData != ''
    ){
        var tempData = sLastDatePriceData.split(',');
        var nLastDatePrice = 0, nPrevLastDatePrice = 0;
        if(
            sLastDate === tempData[0]
            && typeof tempData[1] != 'undefined'
        ){
            nLastDatePrice = Math.round(tempData[1]);
            objOfferGraphArea.attr('data-spros1', nLastDatePrice);
            //добавляем кнопку
            objOfferGraphArea.find('.val_adress:first').before('<a href="javascript: void(0);" class="offer_graph_send">Отправить график</a>');

            //проверяем есть ли предпоследняя цена (sPrevLastDate - предпоследняя дата из спика всех дат, sPrevLastDatePriceData - предпоследние данные цены из спика цен)
            if(
                sPrevLastDate != ''
                && sPrevLastDatePriceData != ''
            ) {
                tempData = sPrevLastDatePriceData.split(',');
                if(
                    sPrevLastDate === tempData[0]
                    && typeof tempData[1] != 'undefined'
                ){
                    nPrevLastDatePrice = Math.round(tempData[1]);
                    objOfferGraphArea.attr('data-spros2', nPrevLastDatePrice);

                    var nPriceDifference = nLastDatePrice - nPrevLastDatePrice;
                    if(nPriceDifference != 0){
                        objOfferGraphArea.attr('data-sprosdif', nPriceDifference);
                    }
                }
            }
        }
    }
}

//отправка ajax запроса для отмены предложения
function stopCounterOffer(iOfferId){
    $.post('/ajax/cancelCounterOffer.php', {
        offer_id: iOfferId
    }, function (mes) {
        var objInput = $('.list_page_rows.farmer_offer input[type="hidden"][name="offer"][value="' + iOfferId + '"]');
        if(objInput.length > 0) {
            var objArea = objInput.parents('.line_additional').find('.prop_area.counter_offer_area');
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