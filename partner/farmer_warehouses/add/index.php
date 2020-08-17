<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

//если у нас предположительно есть ID склада, пробуем получить его данные
$APPLICATION->SetAdditionalCSS("/farmer/warehouses/styles.css", true);
$APPLICATION->SetTitle('Добавление склада');
?>
    <h1 class="page_header"><?$APPLICATION->ShowTitle()?></h1>
<?
$userRegionName = '';
global $USER;

CModule::IncludeModule("iblock");
$IB_ID = rrsIblock::getIBlockId('farmer_warehouse');


//get linked farmers list
$farmer_list = partner::getFarmersForSelect($USER->GetID());

$el_obj = new CIBlockElement;

$form_data = array();
$show_active_but = '';
$error_message = '';
?>

    <script type="text/javascript">

        var map0 = '',map1 = '',ID; //координаты склада
        var regionArray = {};
    </script>

<?
if(!empty($IB_ID)){

    if(isset($_POST['add_warehouse_data']) && $_POST['add_warehouse_data'] == 'y'){

        //check parameters
        if(!isset($_POST['farmer_filter']) || $_POST['farmer_filter'] == 0){
            $error_message .= 'Укажите поставщика<br/>';
        }
        else{
            //check if farmer linked to agent
            if(count(partner::getFarmers($USER->GetID(), array($_POST['farmer_filter']))) == 0){
                $error_message .= 'Передан некорректный id поставщика<br/>';
            }
        }

        if(!isset($_POST['NAME']) || trim($_POST['NAME']) == ''){
            $error_message .= 'Укажите название склада<br/>';
        }

        if(!isset($_POST['P']['REGION']) || !is_numeric($_POST['P']['REGION'])){
            $error_message .= 'Укажите название склада<br/>';
        }

        if(!isset($_POST['P']['ADDRESS'])
            || trim($_POST['P']['ADDRESS']) == ''
            || !isset($_POST['P']['MAP'])
            || !is_array($_POST['P']['MAP'])
            || count($_POST['P']['MAP']) == 0
        ){
            $error_message .= 'Укажите корректный адрес склада на карте<br/>';
        }

        if($error_message == ''){
            $arFields = array(
                'IBLOCK_ID'         => rrsIblock::getIBlockId('farmer_warehouse'),
                'ACTIVE'            => 'Y',
                'NAME'              => trim($_POST['NAME']),
                'PROPERTY_VALUES'   => array(
                    'ACTIVE' => rrsIblock::getPropListKey('farmer_warehouse', 'ACTIVE', 'yes'),
                    'FARMER' => $_POST['farmer_filter'],
                    'ADDRESS' => $_POST['P']['ADDRESS'],
                    'MAP' => implode(',', $_POST['P']['MAP'])
                )
            );
            if (isset($_POST['P']['REGION'])) {
                $arFields['PROPERTY_VALUES']['REGION'] = $_POST['P']['REGION'];
            }

            //try add/edit warehouse
            if(
                isset($_REQUEST['warehouse_id'])
                && is_numeric($_REQUEST['warehouse_id'])
                && isset($_POST['ID'])
                && is_numeric($_POST['ID'])
                && $_POST['ID'] > 0
                && $_REQUEST['warehouse_id'] == $_POST['ID']
            ){
                //edit
                if($el_obj->Update($_POST['ID'], array( 'NAME' => $arFields['NAME'] ))){
                    $el_obj->SetPropertyValuesEx($_POST['ID'], rrsIblock::getIBlockId('farmer_warehouse'), $arFields['PROPERTY_VALUES']);
                }
                else{
                    $error_message = $el_obj->LAST_ERROR;
                }
            }
            else{
                //add
                $NID = $el_obj->Add($arFields);
                if(intval($NID) > 0){

                    $_GET['back_url'] = trim($_GET['back_url']);
                    if(!empty($_GET['back_url'])) {
                        $sBackUrl = $_GET['back_url'];
                    } else {
                        $sBackUrl = '/partner/farmer_warehouses/';
                    }

                    LocalRedirect($sBackUrl);
                    //LocalRedirect($APPLICATION->GetCurDir() . '/?warehouse_id=' . $NID . '&success_add=y');
                    exit;
                }
                else{
                    $error_message = $el_obj->LAST_ERROR;
                    echo $error_message;
                    exit;
                }
            }
        }
    }

    $regionArray = array();
    $form_data['ID'] = 0;
    $form_data['NAME'] = '';
    $form_data['ADDRESS'] = '';
    $form_data['MAP'] = array('55.753215','37.622504');
    $form_data['FARMER_ID'] = '';

    $_GET['farmer_id'] = intval($_GET['farmer_id']);
    if(!empty($_GET['farmer_id'])) {
        $form_data['FARMER_ID'] = $_GET['farmer_id'];
    }

    if(!isset($_REQUEST['warehouse_id']) || !filter_var($_REQUEST['warehouse_id'], FILTER_VALIDATE_INT)){
        $IB_REG_ID = rrsIblock::getIBlockId('regions');
        if(!empty($IB_REG_ID)){
            //создание нового склада
            $arSelect = Array("ID", "NAME");
            $arFilter = Array("IBLOCK_ID" => $IB_REG_ID, "ACTIVE" => "Y");
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
            <script type="text/javascript">
                regionArray = <?=$jsonArray?>;
            </script>
            <?
        }
    }
    if((sizeof($form_data))&&(is_array($form_data))){
        /**
         * Получаем регион текущего пользователя
         */
        $USER_IB_ID = rrsIblock::getIBlockId('farmer_profile');
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
        <a class="go_back cross" href="/partner/farmer_warehouses/"></a>

        <?if(count($farmer_list) > 0){?>

            <?if($error_message != ''){?>
                <div class="error_message"><?=$error_message;?></div>
            <?}elseif(isset($_GET['success_add']) && $_GET['success_add'] == 'y'){?>
                <div class="success_message">Склад добавлен</div>
            <?}?>

            <form class="new_warehouse" action="" method="post">
                <input type="hidden" name="ID" value="<?=$form_data['ID']?>" />
                <?/*<input type="hidden" data-specval="<?=$form_data['MAP'][0]?>,<?=$form_data['MAP'][1]?>" id="MAP_0" name="P[MAP][0]" value="<?=$form_data['MAP'][0]?>">
                    <input type="hidden" id="MAP_1" name="P[MAP][1]" value="<?=$form_data['MAP'][1]?>">*/?>
                <input type="hidden" id="MAP_0" name="P[MAP][0]" value="" />
                <input type="hidden" id="MAP_1" name="P[MAP][1]" value="" />
                <input type="hidden" id="PRegion" class="w_400" name="P[REGION]"  value="<?=$form_data['REGION']?>" />
                <input type="hidden" name="add_warehouse_data" value="y" />

                <div class="farmer_filter">
                    <div class="row">
                        <div class="row_val label">Выберите поставщика:</div>
                        <div class="row_val">
                            <select <?if(count($farmer_list) > 4){?>data-search="y"<?}?> name="farmer_filter">
                                <option value="0">Все</option>
                                <?foreach($farmer_list as $cur_id => $cur_data){
                                    if($cur_data['NICK'] != ''){?>
                                        <option <?if($form_data['FARMER_ID'] == $cur_id){?>selected="selected"<?}?> value="<?=$cur_id;?>"><?=$cur_data['NICK'];?></option>
                                    <?}elseif($cur_data['NAME'] != ''){?>
                                        <option <?if($form_data['FARMER_ID'] == $cur_id){?>selected="selected"<?}?> value="<?=$cur_id;?>"><?=$cur_data['NAME'];?><?if(!checkEmailFromPhone($cur_data['EMAIL'])){?> (<?=$cur_data['EMAIL'];?>)<?}?></option>
                                    <?}else{?>
                                        <option <?if($form_data['FARMER_ID'] == $cur_id){?>selected="selected"<?}?> value="<?=$cur_id;?>"><?=(!checkEmailFromPhone($cur_data['EMAIL']) ? $cur_data['EMAIL'] : $cur_id);?></option>
                                    <?}?>
                                <?}?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form_block row">
                    <div class="row_val">
                        <?if($show_active_but == 'd'){?>
                            <div class="not_active_stat">(Не активно)</div>
                        <?}?>
                        <input placeholder="Название склада" type="text" id="P_NAME" name="NAME" class="w_300" value="<?=$form_data['NAME']?>" />
                    </div>
                </div>

                <div class="form_block row">
                    <div class="row_val address">
                        <input placeholder="Адрес" id="Address" class="w_400" type="text" name="P[ADDRESS]"  value="<?=$form_data['ADDRESS']?>" />
                    </div>
                </div>

                <div class="form_block row double_region inactive">
                    <div class="row_label">Выбрать регион:</div>
                    <div class="row_val">
                        <select <?if(count($regionArray) > 4){?>data-search="y"<?}?> >
                            <option value="0">Не выбрано</option>
                            <?foreach($regionArray as $iRegionId => $sRegionName){?>
                                <option value="<?=$iRegionId;?>"><?=$sRegionName;?></option>
                            <?}?>
                        </select>
                    </div>
                </div>

                <div class="form_block row map">
                    <div class="row_val">
                        <div id="myMap" style="max-width: 600px; height: 600px;"></div>
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
            <script type="text/javascript">
                ID = <?=$form_data['ID']?>;
                map0 = <?=$form_data['MAP'][0]?>;
                map1 = <?=$form_data['MAP'][1]?>;
            </script>
        <?}
        else{
            ?>
            <div class="error_message">Не найдены связанные поставщики</div>
            <?
        }
    }
}
else
{
    echo 'Ошибка не найден ифноблок со складами';
}
?>
    <script type="text/javascript">

        var map, gGeocoderObj, gMarker = null, gAutocomplete = null, gAutocompleteListener = null; //данные для google maps
        var reqDelay = 700, reqDelaysStartTime = 0; //данные для отложенного запроса в google maps
        var adresInput = document.getElementById('Address');
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
        }

        //разбора возвращаемого от google результата и установка значений в нужные поля формы (прямая геолокация)
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

        $(document).ready(function(){

            var maxLen = 15;
            $('#P_NAME').keyup( function(){
                var $this = $(this);
                if($this.val().length > maxLen)
                    $this.val($this.val().substr(0, maxLen));
            });

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

                //check adress
                checkObj = $(this).find('input[type="text"][name="P[ADDRESS]"]');
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
                        && checkObj.length > 0
                    )
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
                            checkObj.after('<div class="row_err"></div>');
                            checkObj.parents('.row').addClass('error').find('.row_err').text(err_val);
                        }
                    }
                    err_val = '';
                }

                //check farmer selected
                checkObj = $(this).find('select[name="farmer_filter"]');
                if(checkObj.length != 1 || checkObj.val() == 0){
                    err_val = 'Укажите поставщика';
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
                            checkObj.parents('.row').find('select[name="farmer_filter"]').after('<div class="row_err"></div>');
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

            $('form.new_warehouse').on('change', 'input[type="text"], select[name="farmer_filter"]', function(e){
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

    </script>

    <script src="https://maps.googleapis.com/maps/api/js?key=<?=$GLOBALS['googleMapKey'];?>&callback=initWHMap&libraries=places&language=ru" async defer></script>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");