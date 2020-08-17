function initWHMap() {
    //старт отображения карты
    map = new google.maps.Map(document.getElementById('myMap'), {
        center: {lat: map0, lng: map1},
        zoom: 8,
        streetViewControl: false,
        rotateControl: false,
        fullscreenControl: false
    });

    gGeocoderObj = new google.maps.Geocoder; //для обратного геокодирования

    //обработка клика по карте
    map.addListener('click', function(e) {
        if(e.latLng){
            //обратное геокодирование
            gGeocoderObj.geocode({'location': e.latLng}, function(results, status) {
                if (status == 'OK') {
                    geocodeRequestResultWork(results, e.latLng);
                } else {
                    if(status != 'ZERO_RESULTS') {
                        console.log('error: ' + status);
                    }else{
                        geocodeRequestResultWork(null, null);
                    }
                }
            });
        }
    });

    //добавляем функционал взаимодейтсвия с картой
    $(document).ready(function(){
        //при измененнии ввода меняем отображаемые данные
        $('#Address').on('keyup', function(){adressKeyUpEvent($(this).val());});
    });

    //проверка длины названия склада
    // var maxLen = 15;
    // $('#P_NAME').keyup( function(){
    //     var $this = $(this);
    //     if($this.val().length > maxLen)
    //         $this.val($this.val().substr(0, maxLen));
    // });

    //проверка формы отправки
    $('form.new_warehouse').on('submit', function(e){

        var found_err = false;
        var err_val = '';
        var err_scroll_top = 0;
        var temp_val = '';

        //проверка названия склада
        var checkObj = $(this).find('input[type="text"][name="NAME"]');
        if(checkObj.length == 1)
        {
            temp_val = checkObj.val();
            if(temp_val == '')
            {
                err_val = 'Пожалуйста, заполните это обязательное поле';
            }
            if(err_val != '')
            {
                found_err = true;
                err_scroll_top = checkObj.offset().top - 100;
                if(checkObj.parents('.row').find('.row_err').length == 1)
                {
                    checkObj.parents('.row').addClass('error').find('.row_err').text(err_val);
                }
                else
                {
                    checkObj.parents('.row').find('.row_val').append('<div class="row_err"></div>');
                    checkObj.parents('.row').addClass('error').find('.row_err').text(err_val);
                }
            }
            err_val = '';
        }

        //проверка адреса
        checkObj = $(this).find('input[type="text"][name="P[ADDRESS]"]');
        if(checkObj.length == 1)
        {
            temp_val = checkObj.val();
            if(temp_val == '')
            {
                err_val = 'Пожалуйста, заполните это обязательное поле: ' + checkObj.val();
            }

            if(err_val != '')
            {
                found_err = true;
                if(err_scroll_top == 0)
                {
                    err_scroll_top = checkObj.offset().top - 100;
                }
                if(checkObj.parents('.row').find('.address .row_err').length == 1)
                {
                    checkObj.parents('.row').addClass('error').find('.address .row_err').text(err_val);
                }
                else
                {
                    checkObj.parents('.row').find('input[name="P[ADDRESS]"]').after('<div class="row_err"></div>');
                    checkObj.parents('.row').addClass('error').find('.row_err').text(err_val);
                }
            }
            err_val = '';
        }

        //проверка выбранного региона
        checkObj = $('#PRegion');
        if(checkObj.length == 1)
        {
            if(
                checkObj.val() === ''
                || checkObj.val() == 0
            )
            {
                err_val = 'Пожалуйста, заполните это обязательное поле';
                checkObj = $('.form_block.double_region:not(.inactive) select');
            }

            if(
                err_val != ''
            )
            {
                found_err = true;

                if(checkObj.length == 0){
                    checkObj = $(this).find('input[type="text"][name="P[ADDRESS]"]');

                    if(checkObj.val().length > 0) {
                        err_val = 'Подтвердите регион на карте (кликните на поле ввода адреса и выберите предложенный вариант)';
                    }else{
                        err_val = 'Пожалуйста, заполните это обязательное поле';
                    }
                }

                if(err_scroll_top == 0)
                {
                    err_scroll_top = checkObj.offset().top - 100;
                }
                if(checkObj.parents('.row').find('.address .row_err').length == 1)
                {
                    checkObj.parents('.row').addClass('error').find('.address .row_err').text(err_val);
                }
                else
                {
                    checkObj.after('<div class="row_err"></div>');
                    checkObj.parents('.row').addClass('error').find('.row_err').text(err_val);
                }
            }
            err_val = '';
        }

        if (found_err == true || $(this).hasClass('inactive')) {
            e.preventDefault();
            window.scrollTo(0, err_scroll_top);
            return false;
        }
    });

    $('form.new_warehouse').on('change', 'input[type="text"]', function(e){
        //remove error message after value change
        var err_obj = $(this).parents('.row.error');
        if(err_obj.length == 1)
        {
            err_obj.removeClass('error');
        }
    });

    $('form.new_warehouse input[type="text"]').keypress(function(e){
        if (e.keyCode == 13) {
            e.preventDefault();
        }
    });
}



$(document).ready(function(){
    //вкл/выкл все чекбоксы
    $('.form_block.row.transport a.ch_all_tr').on('click', function(){
        $('.form_block.row.transport .radio_area  input[type="checkbox"]').each(function(ind, cObj){
            if($(cObj).prop('checked') === false){
                $(cObj).trigger('click');
            }
        });
    });
    $('.form_block.row.transport a.no_tr').on('click', function(){
        $('.form_block.row.transport .radio_area  input[type="checkbox"]').each(function(ind, cObj){
            if($(cObj).prop('checked') === true){
                $(cObj).trigger('click');
            }
        });
    });

    //обработка изменения ручного выбора региона
    $('.form_block.double_region select').on('change', function(){
        var mRegionValue = $(this).val();
        if(mRegionValue === 0){
            mRegionValue = '';
        }else{
            //убираем ошибку, если она была выведена
            var obErr = $(this).siblings('.row_err');
            if(obErr.length > 0){
                obErr.remove();
            }
            obErr = $(this).parents('.row.error');
            if(obErr.length > 0){
                obErr.removeClass('error');
            }
        }

        $('#PRegion').val(mRegionValue);
    });
});

//разбор возвращаемого от google результата и установка значений в нужные поля формы (прямая геолокация)
function parseGMapLocation(googlePlaceobject){
    //ставим широту и долготу
    $('#MAP_0').val(googlePlaceobject.geometry.location.lat());
    $('#MAP_1').val(googlePlaceobject.geometry.location.lng());

    //ставим регион
    var found_region = false;
    var temp_region = '';
    var region_id = '';
    if(googlePlaceobject.address_components){
        for(var i = 0; i < googlePlaceobject.address_components.length; i++){
            if(googlePlaceobject.address_components[i].types){
                for(var j = 0; j < googlePlaceobject.address_components[i].types.length; j++){
                    temp_region = googlePlaceobject.address_components[i].types[j];
                    if(temp_region == 'administrative_area_level_1'
                        || temp_region == 'administrative_area_level_2'
                        || temp_region == 'administrative_area_level_3'
                    ){
                        region_id = parseRegionIdByName(googlePlaceobject.address_components[i].long_name);
                        if(region_id > 0){
                            //найден регион, устанавливаем его
                            hideSelectRegion();
                            $('#PRegion').val(region_id);
                            found_region = true;
                            break;
                        }else{
                            showSelectRegion();
                            $('#PRegion').val('');
                        }
                    }
                }
            }
            if(found_region){
                break;
            }
        }
    }
}

//проверяет содержится ли checkString в списке имеющихся регионов
function parseRegionIdByName(checkString){
    result = 0;

    for(var k in regionArray){
        if(k.indexOf(checkString) + 1){
            //регион найден
            result = regionArray[k];
            break;
        }
    }

    return result;
}

//отрабатывает при вводе очередного символа
//arg - строка адреса
function adressKeyUpEvent(arg){
    //задержка в reqDelay миллисекунд для того, чтобы не отправлять данные сразу и не перегружать запросами сервис
    //обновляем метку сравнения
    reqDelaysStartTime = $.now();
    setTimeout(function(){
        if(arg.length > 2){
            if($.now() - reqDelaysStartTime > reqDelay - 25){
                //отправляем запрос

                if(gAutocomplete == null) {
                    gAutocomplete = new google.maps.places.Autocomplete(adresInput, {componentRestrictions: {country: "ru"}});
                    $(adresInput).on('keypress', function (e) {
                        if (e.keyCode === 10 || e.keyCode === 13) {
                            e.preventDefault();
                        }
                    });
                    gAutocomplete.bindTo('bounds', map);
                    gAutocomplete.setFields(
                        ['address_components', 'geometry', 'icon', 'name']);

                    gAutocompleteListener = gAutocomplete.addListener('place_changed', function () {
                        var place = gAutocomplete.getPlace();
                        if (!place.geometry) {
                            // User entered the name of a Place that was not suggested and
                            // pressed the Enter key, or the Place Details request failed.
                            //window.alert("No details available for input: '" + place.name + "'");
                            return;
                        }

                        // If the place has a geometry, then present it on a map.
                        if (place.geometry.viewport) {
                            map.fitBounds(place.geometry.viewport);
                        } else {
                            map.setCenter(place.geometry.location);
                            map.setZoom(15);
                        }

                        //ставим указатель
                        if (gMarker != null) {
                            gMarker.setMap(null);
                        }
                        gMarker = new google.maps.Marker({
                            position: place.geometry.location,
                            map: map,
                            title: $('#Address').val(),
                            icon: 'http://maps.google.com/mapfiles/kml/paddle/blu-blank.png'
                        });

                        //устанавливаем данные выбранного адреса в форму
                        parseGMapLocation(place);

                        //убираем автозаполнение
                        google.maps.event.clearInstanceListeners(gAutocomplete);
                        google.maps.event.removeListener(gAutocompleteListener);
                        gAutocomplete = null;
                        var temp_val = $(adresInput).val();

                        $(adresInput).replaceWith('<input placeholder="Адрес" id="Address" class="w_400" type="text" name="P[ADDRESS]" autocomplete="off">');
                        adresInput = document.getElementById('Address');
                        $(adresInput).val(temp_val);
                        $(adresInput).on('keyup', function(){adressKeyUpEvent($(this).val());});
                        $(adresInput).trigger('change');
                    });


                    $(adresInput).blur();
                    setTimeout(function(){
                        $(adresInput).focus();
                        google.maps.event.trigger(adresInput, 'focus', {});
                    }, 100);
                }
            }
        }
    }, reqDelay);
}
//работа с результатами запроса gGeocoderObj (для обратного геокодирования)
function geocodeRequestResultWork(results_data, lat_lang){
    if(results_data != null
        && results_data.length > 0
    ){
        //обработка результатов от google (ищем тип - улица, либо общиие данные - locality)
        var adress_str = '';
        var region_id = 0;
        for(var i = 0; i < results_data.length; i++){
            if(results_data[i].types){
                for(var j = 0; j < results_data[i].types.length; j++){
                    if(results_data[i].types[j] == 'street_address'
                        || results_data[i].types[j] == 'premise'
                        || results_data[i].types[j] == 'route'
                        || results_data[i].types[j] == 'locality'
                        || results_data[i].types[j] == 'political'
                        || results_data[i].types[j] == 'sublocality'
                        || results_data[i].types[j] == 'administrative_area_level_1'
                        || results_data[i].types[j] == 'administrative_area_level_2'
                        || results_data[i].types[j] == 'administrative_area_level_3'
                    ){
                        if(adress_str == '' && results_data[i].formatted_address) {
                            adress_str = results_data[i].formatted_address;
                        }

                        //ищем регион
                        if(results_data[i].address_components){
                            for(var k = 0; k < results_data[i].address_components.length; k++){
                                if(results_data[i].address_components[k]){
                                    if(results_data[i].address_components[k].types){
                                        for(var m = 0; m < results_data[i].address_components[k].types.length; m++){
                                            if(results_data[i].address_components[k].types[m] == 'locality'
                                                || results_data[i].address_components[k].types[m] == 'political'
                                                || results_data[i].address_components[k].types[m] == 'administrative_area_level_1'
                                                || results_data[i].address_components[k].types[m] == 'administrative_area_level_2'
                                                || results_data[i].address_components[k].types[m] == 'administrative_area_level_3'
                                            ){
                                                region_id = parseRegionIdByName(results_data[i].address_components[k].long_name);
                                                if(region_id > 0){
                                                    break;
                                                }
                                            }
                                        }
                                    }
                                }
                                if(region_id > 0){
                                    break;
                                }
                            }

                        }
                        if(region_id > 0 && adress_str != ''){
                            break;
                        }
                    }
                }
            }
            if(adress_str != ''){
                //найден адрес - ставим данные
                adress_str = adress_str.replace(/(Unnamed Road\, |\, \d{6})/g, '');

                if(adress_str == 'Unnamed Road'){
                    adress_str = 'Неизвестный адрес';
                }

                //ставим название в поле
                $('#Address').val(adress_str);

                //ставим широту и долготу
                $('#MAP_0').val(lat_lang.lat());
                $('#MAP_1').val(lat_lang.lng());
                //ставим регион
                $('#PRegion').val(region_id);

                //ставим указатель
                if(gMarker != null){
                    gMarker.setMap(null);
                }
                gMarker = new google.maps.Marker({
                    position: lat_lang,
                    map: map,
                    title: adress_str,
                    icon: 'http://maps.google.com/mapfiles/kml/paddle/blu-blank.png'
                });
                break;
            }
        }
    }else{
        //ничего не найдено
    }
}

/* Показываем выбор регионов вручную
* */
function showSelectRegion(){
    var obSelect = $('.form_block.row.double_region select');
    if(obSelect.length === 1){
        obSelect.val(0);
        obSelect.trigger('change');
        obSelect.parents('.row.double_region').removeClass('inactive');
        obSelect.focus();
    }
}

/* Скрываем ручной выбор регионов
* */
function hideSelectRegion(){
    var obElement = $('.form_block.row.double_region');
    if(obElement.length === 1){
        obElement.addClass('inactive');
    }
}