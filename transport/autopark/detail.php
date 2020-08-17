<?php
/**
 * Created by PhpStorm.
 * User: bragev
 * Date: 02.04.2018
 * Time: 14:30
 */
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");?>
<link rel="stylesheet" href="https://code.jquery.com/ui/1.9.1/themes/smoothness/jquery-ui.css" />
<script src="https://code.jquery.com/ui/1.9.1/jquery-ui.min.js"></script>
<script src="https://api-maps.yandex.ru/2.0-stable/?load=package.full&lang=ru-RU" type="text/javascript"></script>

<script>
    var map0 = '',map1 = '',ID; //координаты базы
    var regionArray = {};
</script>

<?
//если у нас предположительно есть ID базы, пробуем получить ее данные
if(!CModule::IncludeModule("iblock"))
    return;
$APPLICATION->SetAdditionalCSS("/transport/autopark/styles.css", true);
$APPLICATION->SetTitle('Добавление базы автопарка');
?>
<h1 class="page_header"><?$APPLICATION->ShowTitle()?></h1>
<?

$userRegionName = '';
global $USER;

$IB_ID = rrsIblock::getIBlockId('transport_autopark');

$form_data = array();
$show_active_but = '';
if((isset($_REQUEST['WCODE']))&&(!empty($IB_ID))){
    $regionArray = array();
    $form_data['ID'] = 0;
    $form_data['NAME'] = '';
    $form_data['ADDRESS'] = '';
    $form_data['MAP'] = array('55.753215','37.622504');

    if($_REQUEST['WCODE'] == 'add'){
        $IB_REG_ID = rrsIblock::getIBlockId('regions');
        if(!empty($IB_REG_ID)){
            //создание новой базы
            $arSelect = Array("ID", "NAME");
            $arFilter = Array("IBLOCK_ID"=>$IB_REG_ID, "ACTIVE"=>"Y");
            $res = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
            $toJSArray = array();
            while($ob = $res->GetNextElement())
            {
                $arFields = $ob->GetFields();
                $regionArray[$arFields['ID']] = $arFields['NAME'];
                $toJSArray[$arFields['NAME']] = $arFields['ID'];
            }
            $jsonArray = json_encode($toJSArray);
            ?>
            <script>
                regionArray = <?=$jsonArray?>;
            </script>
            <?
        }
    }elseif(is_int((int)$_REQUEST['WCODE'])){
        //редактирование базы
        $res = CIBlockElement::GetByID($_REQUEST['WCODE']);
        if($ob = $res->GetNextElement()){
            $fields = $ob->GetFields();
            $form_data['ID'] = $fields['ID'];
            $form_data['NAME'] = $fields['NAME'];
            $prop = $ob->GetProperties();

            //check if base belongs to another user
            if($USER->GetID() != $prop['TRANSPORT']['VALUE'])
            {
                LocalRedirect('/transport/autopark/');
                exit;
            }
            if(isset($prop['ADDRESS'])){
                $form_data['ADDRESS'] = $prop['ADDRESS']['VALUE'];
            }
            if(isset($prop['REGION'])){

                $form_data['REGION'] = $prop['REGION']['VALUE'];
                $arSelect = Array("ID", "NAME");
                $arFilter = Array("IBLOCK_ID"=>$prop['REGION']['LINK_IBLOCK_ID'], "ACTIVE"=>"Y");
                $res = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
                $toJSArray = array();
                while($ob = $res->GetNextElement())
                {
                    $arFields = $ob->GetFields();
                    $regionArray[$arFields['ID']] = $arFields['NAME'];
                    $toJSArray[$arFields['NAME']] = $arFields['ID'];
                }
                $jsonArray = json_encode($toJSArray);
                ?>
                <script>
                    regionArray = <?=$jsonArray?>;
                </script>
                <?
            }
            if(isset($prop['MAP'])){
                $value = explode(',',$prop['MAP']['VALUE']);
                if(sizeof($value)>1){
                    $form_data['MAP'][0] = $value[0];
                    $form_data['MAP'][1] = $value[1];
                }
            }
            //check status
            $show_active_but = (isset($prop['ACTIVE']['VALUE_ENUM_ID']) && $prop['ACTIVE']['VALUE_ENUM_ID'] == rrsIblock::getPropListKey('transport_autopark', 'ACTIVE', 'no') ? 'd' : 'a');
        }
    }
    if((sizeof($form_data))&&(is_array($form_data))){
        /**
         * Получаем регион текущего пользователя
         */
        $USER_IB_ID = rrsIblock::getIBlockId('transport_profile');
        $userRegionId = 0;
        if($USER_IB_ID){
            $arSelect = Array("ID", "NAME", "PROPERTY_REGION");
            $arFilter = Array("IBLOCK_ID"=>$USER_IB_ID, "ACTIVE"=>"Y","PROPERTY_USER"=>$USER->GetID());
            $res = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
            $toJSArray = array();

            while($ob = $res->GetNextElement())
            {
                $arFields = $ob->GetFields();
                if(!empty($arFields['PROPERTY_REGION_VALUE'])){
                    $userRegionId = $arFields['PROPERTY_REGION_VALUE'];
                    $form_data['REGION'] = $userRegionId;
                }
            }
            if(!empty($userRegionId)){
                if((sizeof($regionArray))&&(is_array($regionArray))){
                    if(array_key_exists($userRegionId,$regionArray)){
                        $userRegionName = $regionArray[$userRegionId];
                    }
                }
                //get user's region coordinates for start point
                if($_REQUEST['WCODE'] == 'add')
                {
                    $res = CIBlockElement::GetList(array('ID' => 'ASC'), array('IBLOCK_CODE' => 'regions', 'ID' => $userRegionId), false, array('nTopCount' => 1), array('PROPERTY_COORDINATES'));
                    if($data = $res->Fetch())
                    {
                        if(trim($data['PROPERTY_COORDINATES_VALUE']) != '')
                        {
                            $form_data['MAP'] = explode(',', $data['PROPERTY_COORDINATES_VALUE']);
                        }
                    }
                    $form_data['REGION'] = $userRegionId;
                }
            }
        }
        ?>
            <a class="go_back cross" href="/transport/autopark/"></a>
            <form class="new_warehouse" action="/transport/autopark/action.php" method="post">
                <input type="hidden" name="ID" value="<?=$form_data['ID']?>">
                <?/*<input type="hidden" data-specval="<?=$form_data['MAP'][0]?>,<?=$form_data['MAP'][1]?>" id="MAP_0" name="P[MAP][0]" value="<?=$form_data['MAP'][0]?>">
                <input type="hidden" id="MAP_1" name="P[MAP][1]" value="<?=$form_data['MAP'][1]?>">*/?>
                <input type="hidden" id="MAP_0" name="P[MAP][0]" value="">
                <input type="hidden" id="MAP_1" name="P[MAP][1]" value="">
                <input type="hidden" id="PRegion" class="w_400" name="P[REGION]"  value="<?=$form_data['REGION']?>">

                <div class="form_block row">
                    <div class="row_val">
                        <?if($show_active_but == 'd'){?>
                            <div class="not_active_stat">(Не активно)</div>
                        <?}?>
                        <input placeholder="Название базы автопарка" type="text" id="P_NAME" name="NAME" class="w_300" value="<?=$form_data['NAME']?>" />
                    </div>
                </div>

                <div class="form_block row">
                    <div class="row_val address">
                        <input placeholder="Адрес" id="Address" class="w_400" type="text" name="P[ADDRESS]"  value="<?=$form_data['ADDRESS']?>" />
                    </div>
                </div>

                <div class="form_block row map">
                    <div class="row_val">
                        <div id="myMap" style="width: 100%; height: 600px;"></div>
                    </div>
                </div>

                <div class="row">
                    <input name="iblock_submit" class="submit-btn left" value="Сохранить" type="submit" />
                    <?if($show_active_but == 'd'){?>
                        <input name="activate" class="empty_but left" value="Активировать" type="button" />
                    <?}elseif($show_active_but == 'a'){?>
                        <input name="deactivate" class="empty_but left" value="Деактивировать" type="button" />
                    <?}?>
                    <div class="clear"></div>
                </div>
            </form>
        </div>
        <script>
            ID = <?=$form_data['ID']?>;
            map0 = <?=$form_data['MAP'][0]?>;
            map1 = <?=$form_data['MAP'][1]?>;
        </script>
        <?
    }
}

?>
<script type="text/javascript">

    var myMap;
    var search_result = [];
    var region_data;
    var userRegionName = '<?=$userRegionName?>';

    ymaps.ready(function () {
        var myPlacemark,myMap;

        if(ID != 0) {

            myMap = new ymaps.Map("myMap", {
                center: [map0, map1],
                zoom: 12,
                behaviors: ['default', 'scrollZoom']
            });
            myMap.controls.add('zoomControl');

            var coords = [map0, map1];

            myMap.cursors.push('arrow');

            //создаем метку по умолчанию
            myPlacemark = createPlacemark(coords);
            myMap.geoObjects.add(myPlacemark);
            // Слушаем событие окончания перетаскивания на метке.
            myPlacemark.events.add('dragend', function () {
                getAddress(myPlacemark.geometry.getCoordinates());
            });
            getAddress(coords);

            // Слушаем клик на карте.
            myMap.events.add('click', function (e) {
                var coords = e.get('coords');
                // Если метка уже создана – просто передвигаем ее.
                if (myPlacemark) {
                    myPlacemark.geometry.setCoordinates(coords);
                }
                // Если нет – создаем.
                else {

                    myPlacemark = createPlacemark(coords);
                    myMap.geoObjects.add(myPlacemark);
                    // Слушаем событие окончания перетаскивания на метке.
                    myPlacemark.events.add('dragend', function () {
                        getAddress(myPlacemark.geometry.getCoordinates());
                    });
                }
                getAddress(coords);
            });
        }else{
            if(map0 != '' && map1 != '')
            {
                myMap = new ymaps.Map("myMap", {
                    center: [map0, map1],
                    zoom: 12,
                    behaviors: ['default', 'scrollZoom']
                });
                myMap.controls.add('zoomControl');

                var coords = [map0, map1];
                myMap.cursors.push('arrow');

                // Слушаем клик на карте.
                myMap.events.add('click', function (e) {
                    if ($('.row.map.error .row_err').length == 1) {
                        $('.row.map.error .row_err').html('');
                    }
                    var coords = e.get('coords');
                    // Если метка уже создана – просто передвигаем ее.
                    if (myPlacemark) {
                        myPlacemark.geometry.setCoordinates(coords);
                    }
                    // Если нет – создаем.
                    else {

                        myPlacemark = createPlacemark(coords);
                        myMap.geoObjects.add(myPlacemark);
                        // Слушаем событие окончания перетаскивания на метке.
                        myPlacemark.events.add('dragend', function () {
                            getAddress(myPlacemark.geometry.getCoordinates());
                        });
                    }
                    getAddress(coords);
                });
            }
            else if(userRegionName.length>0){
                setRegionPos(userRegionName);
            }else{
                myMap = new ymaps.Map("myMap", {
                    center: [map0, map1],
                    zoom: 12,
                    behaviors: ['default', 'scrollZoom']
                });
                myMap.controls.add('zoomControl');

                var coords = [map0, map1];
                myMap.cursors.push('arrow');

                //создаем метку по умолчанию
                myPlacemark = createPlacemark(coords);
                myMap.geoObjects.add(myPlacemark);
                // Слушаем событие окончания перетаскивания на метке.
                myPlacemark.events.add('dragend', function () {
                    getAddress(myPlacemark.geometry.getCoordinates());
                });
                getAddress(coords);

                // Слушаем клик на карте.
                myMap.events.add('click', function (e) {
                    if ($('.row.map.error .row_err').length == 1) {
                        $('.row.map.error .row_err').html('');
                    }
                    var coords = e.get('coords');
                    // Если метка уже создана – просто передвигаем ее.
                    if (myPlacemark) {
                        myPlacemark.geometry.setCoordinates(coords);
                    }
                    // Если нет – создаем.
                    else {

                        myPlacemark = createPlacemark(coords);
                        myMap.geoObjects.add(myPlacemark);
                        // Слушаем событие окончания перетаскивания на метке.
                        myPlacemark.events.add('dragend', function () {
                            getAddress(myPlacemark.geometry.getCoordinates());
                        });
                    }
                    getAddress(coords);
                });
            }
        }

        // Создание метки.
        function createPlacemark(coords) {
            if ($('.row.map.error .row_err').length == 1) {
                $('.row.map.error .row_err').html('');
            }
            return new ymaps.Placemark(coords, {
                iconCaption: 'поиск...'
            }, {
                preset: 'islands#redDotIconWithCaption',
                draggable: true
            });
        }

        /**
         * Получение ID региона из справочница регионов на основе найденных через API Яндекса данных по региону
         */
        function getRegionID(){
            for(var k in regionArray){
                if(k.indexOf(region_data) + 1){
                    $('#PRegion').val(regionArray[k]);
                }
            }
            return false;
        }

        // Определяем адрес по координатам (обратное геокодирование).
        function getAddress(coords) {
            myPlacemark.properties.set('iconCaption', 'поиск...');
            ymaps.geocode(coords).then(function (res) {
                var firstGeoObject = res.geoObjects.get(0);
                getRegionFromSelectPoint(firstGeoObject);
                myPlacemark.properties
                    .set({
                        iconCaption: firstGeoObject.properties.get('name'),
                        balloonContent: firstGeoObject.properties.get('text'),
                        balloonContentBody: firstGeoObject.properties.get('text'),
                        hintContent: firstGeoObject.properties.get('text'),
                    });
                $('#MAP_0').val(coords[0]);
                $('#MAP_1').val(coords[1]);
                $('#Address').val(myPlacemark.properties.get('balloonContent')).trigger('change');
            });
        }

        /**
         * Получает регион по адресу выбранному через поиск
         */
        function getRegionFromSearchResult(){
            if(search_result[0]){
                if(search_result[0].AddressDetails){
                    if(search_result[0].AddressDetails.AdministrativeArea.AdministrativeAreaName){
                        region_data = search_result[0].AddressDetails.AdministrativeArea.AdministrativeAreaName;
                        if(search_result[0].AddressDetails.AdministrativeArea.SubAdministrativeArea){
                            var tmp = search_result[0].AddressDetails.AdministrativeArea.SubAdministrativeArea.SubAdministrativeAreaName;
                            if(tmp.indexOf('автономный округ') + 1) {
                                region_data = tmp;
                            }
                        }
                    }
                }
            }
            setUnionRegion();
            getRegionID();
            return false;
        }

        /**
         * Получаем регион от выбранной точки на карте
         * @param obj
         */
        function getRegionFromSelectPoint(obj){
            var addressObj = obj.properties.get('metaDataProperty');
            if(addressObj.GeocoderMetaData.AddressDetails){
                region_data = addressObj.GeocoderMetaData.AddressDetails.Country.AdministrativeArea.AdministrativeAreaName;
                if(addressObj.GeocoderMetaData.AddressDetails.Country.AdministrativeArea.SubAdministrativeArea){
                    var tmp = addressObj.GeocoderMetaData.AddressDetails.Country.AdministrativeArea.SubAdministrativeArea.SubAdministrativeAreaName;
                    if(tmp.indexOf('автономный округ') + 1) {
                        region_data = tmp;
                    }
                }
            }
            setUnionRegion();
            getRegionID();
            return false;
        }

        function setUnionRegion(){
            switch (region_data){
                case 'Московская область':
                case 'Москва':
                    region_data = 'Москва и Московская область';
                    break;
                case 'Ленинградская область':
                case 'Санкт-Петербург':
                    region_data = 'Санкт-Петербург и Ленинградская область';
                    break;
                case 'Республика Крым':
                case 'Севастополь':
                    region_data = 'Севастополь и Республика Крым';
                    break;
            }
        }

        function setRegionPos(search_query){
            //массив, в который будем записывать результаты поиска
            search_result = [];
            $.ajax({
                type: 'POST',
                url: '/ajax/geocode-maps.php',
                data: 'search_query='+search_query,
                dataType : "json",
                success: function (data) {
                    myMap = new ymaps.Map("myMap", {
                        center: [map0, map1],
                        zoom: 12,
                        behaviors: ['default', 'scrollZoom']
                    });
                    myMap.controls.add('zoomControl');

                    // Слушаем клик на карте.
                    myMap.events.add('click', function (e) {
                        var coords = e.get('coords');
                        // Если метка уже создана – просто передвигаем ее.
                        if (myPlacemark) {
                            myPlacemark.geometry.setCoordinates(coords);
                        }
                        // Если нет – создаем.
                        else {

                            myPlacemark = createPlacemark(coords);
                            myMap.geoObjects.add(myPlacemark);
                            // Слушаем событие окончания перетаскивания на метке.
                            myPlacemark.events.add('dragend', function () {
                                getAddress(myPlacemark.geometry.getCoordinates());
                            });
                        }
                        getAddress(coords);
                    });

                    var coords = [map0, map1];
                    for(var i = 0; i < data.response.GeoObjectCollection.featureMember.length; i++) {
                        //записываем в массив результаты, которые возвращает нам геокодер
                        search_result.push({
                            label: data.response.GeoObjectCollection.featureMember[i].GeoObject.description+' - '+data.response.GeoObjectCollection.featureMember[i].GeoObject.name,
                            value:data.response.GeoObjectCollection.featureMember[i].GeoObject.description+' - '+data.response.GeoObjectCollection.featureMember[i].GeoObject.name,
                            longlat:data.response.GeoObjectCollection.featureMember[i].GeoObject.Point.pos,
                            AddressData:data.response.GeoObjectCollection.featureMember[i].GeoObject.metaDataProperty.GeocoderMetaData.Address.Components,
                            AddressDetails:data.response.GeoObjectCollection.featureMember[i].GeoObject.metaDataProperty.GeocoderMetaData.AddressDetails.Country});
                    }
                    if(search_result.length>0){
                        var longlat = search_result[0].longlat.split(" ");
                        myMap.setCenter([longlat[1], longlat[0]], 11);
                    }
                    $('#MAP_0').val(longlat[1]);
                    $('#MAP_1').val(longlat[0]);
                }
            });
        }

        function searchGeocoder(search_query){
            //массив, в который будем записывать результаты поиска
            search_result = [];
            $.ajax({
                type: 'POST',
                url: '/ajax/geocode-maps.php',
                data: 'search_query='+search_query,
                dataType : "json",
                success: function (data) {
                    for(var i = 0; i < data.response.GeoObjectCollection.featureMember.length; i++) {
                        //записываем в массив результаты, которые возвращает нам геокодер
                        search_result.push({
                            label: data.response.GeoObjectCollection.featureMember[i].GeoObject.description+' - '+data.response.GeoObjectCollection.featureMember[i].GeoObject.name,
                            value:data.response.GeoObjectCollection.featureMember[i].GeoObject.description+' - '+data.response.GeoObjectCollection.featureMember[i].GeoObject.name,
                            longlat:data.response.GeoObjectCollection.featureMember[i].GeoObject.Point.pos,
                            AddressData:data.response.GeoObjectCollection.featureMember[i].GeoObject.metaDataProperty.GeocoderMetaData.Address.Components,
                            AddressDetails:data.response.GeoObjectCollection.featureMember[i].GeoObject.metaDataProperty.GeocoderMetaData.AddressDetails.Country});
                    }
                    //подключаем к текстовому полю виджет autocomplete
                    $("#Address").autocomplete({
                        //в качестве источника результатов указываем массив search_result
                        source: search_result,
                        select: function(event, ui){
                            //это событие срабатывает при выборе нужного результата
                            var longlat = ui.item.longlat.split(" ");
                            if (myPlacemark) {
                                myPlacemark.geometry.setCoordinates([longlat[1], longlat[0]]);
                            }
                            // Если нет – создаем.
                            else {
                                myPlacemark = createPlacemark([longlat[1], longlat[0]]);
                                myPlacemark.properties
                                    .set({
                                        iconCaption: ui.item.label,
                                        balloonContent: ui.item.label,
                                        balloonContentBody: ui.item.label,
                                        hintContent: ui.item.label,
                                    });
                                myMap.geoObjects.add(myPlacemark);
                                // Слушаем событие окончания перетаскивания на метке.
                                myPlacemark.events.add('dragend', function () {
                                    getAddress(myPlacemark.geometry.getCoordinates());
                                });
                            }
                            myMap.setCenter([longlat[1], longlat[0]], 12);
                            getAddress([longlat[1],longlat[0]]);
                            getRegionFromSearchResult();
                        }
                    });
                }
            });
        }

        $("#Address").keyup(function(){
            //по мере ввода фразы, событие будет срабатывать всякий раз
            var search_query = $(this).val();
            //делаем запрос к геокодеру
            searchGeocoder(search_query);

        });
        $.ui.autocomplete.filter = function (array, term) {
            return $.grep(array, function (value) {
                return value.label || value.value || value;
            });
        };


    });

    $.extend( $.ui.autocomplete, {
        escapeRegex: function( value ) {
            return value.replace(/[\-\[\]{}()*+?.,\\\^$|#\s]/g, "\\$&");
        },
        filter: function(array, term) {
            var matcher = new RegExp( $.ui.autocomplete.escapeRegex(term), "i" );
            return $.grep( array, function(value) {
                return matcher.test( value.label || value.value || value );
            });
        }
    });

    $(document).ready(function(){

        //check form submit
        $('form.new_warehouse').on('submit', function(e){

            var found_err = false;
            var err_val = '';
            var err_scroll_top = 0;
            var temp_val = '';

            //check warehouse name
            var checkObj = $(this).find('input[type="text"][name="NAME"]');
            if(checkObj.length == 1)
            {
                temp_val = checkObj.val();
                if(temp_val == '')
                {
                    err_val = 'Пожалуйста заполните это обязательное поле';
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

            //check adress
            checkObj = $(this).find('input[type="text"][name="P[ADDRESS]"]');
            if(checkObj.length == 1)
            {
                temp_val = checkObj.val();
                if(temp_val == '')
                {
                    err_val = 'Пожалуйста заполните это обязательное поле: ' + checkObj.val();
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

            //check map
            if ($(this).find('#MAP_0').val() == '' || $(this).find('#MAP_1').val() == '') {
                err_val = 'Вы не указали адрес склада на карте';
                if(err_val != '')
                {
                    found_err = true;
                    if($(this).find('.map .row_err').length == 1)
                    {
                        $(this).find('.map .row_err').text(err_val);
                        $(this).find('.map').addClass('error');
                    }
                    else
                    {
                        $(this).find('.map').find('.row_val').after('<div class="row_err"></div>');
                        $(this).find('.map').find('.row_err').text(err_val);
                        $(this).find('.map').addClass('error');
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

        $('form.new_warehouse input[type="text"]').on('change', function(e){
            //remove error message after value change
            var err_obj = $(this).parents('.row.error');
            if(err_obj.length == 1)
            {
                err_obj.removeClass('error');
            }
        });

        $('form.new_warehouse input[type="button"][name="deactivate"]').on('click', function(){
            var check_from = $(this).parents('form.new_warehouse');
            var deactivate_input = check_from.find('input[type="hidden"][name="deactivate"]');
            var activate_input = check_from.find('input[type="hidden"][name="activate"]');
            if(deactivate_input.length == 0)
            {
                check_from.prepend('<input type="hidden" name="deactivate" value="y" />');
            }

            if(activate_input.length > 0)
            {
                activate_input.remove();
            }
            check_from.submit();
        });

        $('form.new_warehouse input[type="button"][name="activate"]').on('click', function(){
            var check_from = $(this).parents('form.new_warehouse');
            var deactivate_input = check_from.find('input[type="hidden"][name="deactivate"]');
            var activate_input = check_from.find('input[type="hidden"][name="activate"]');
            if(activate_input.length == 0)
            {
                check_from.prepend('<input type="hidden" name="activate" value="y" />');
            }

            if(deactivate_input.length > 0)
            {
                activate_input.remove();
            }
            check_from.submit();
        });

        $('form.new_warehouse input.submit-btn').on('click', function(){
            var check_from = $(this).parents('form.new_warehouse');
            var deactivate_input = check_from.find('input[type="hidden"][name="deactivate"]');
            var activate_input = check_from.find('input[type="hidden"][name="activate"]');

            if(deactivate_input.length == 1) { deactivate_input.remove(); }
            if(activate_input.length == 1) { activate_input.remove(); }
        });

        $('form.new_warehouse input[type="text"]').keypress(function(e){
            if (e.keyCode == 13) {
                e.preventDefault();
            }
        });
    });

</script>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
