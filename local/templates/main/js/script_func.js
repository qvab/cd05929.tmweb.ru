var stop_menu_animation = 0; // need for 'showHideMenu' function
var stop_backshad_animation = 0; //need for loader aniamtion
var backshad_animation_interval_id = 0;

//make forms elemetns customization (works only with elements that are not customized yet)
function makeCustomForms()
{
    //set rus language
    $.fn.select2.defaults.set('language', 'ru');

    //selects customization
    $('.content form select:not(.select2-hidden-accessible)').each(function(ind, cObj){
        var searchMin = Infinity; //no search field by default
        if($(cObj).attr('data-search') == 'y') { searchMin = 2;}
        $(cObj).select2(
            {
                minimumResultsForSearch: searchMin,
                templateResult: formatAgroState
            }
        ).siblings('.select2').addClass('agro_select2_container');
    });

    //start form radios & checkboxes customization
    $('.content form .radio_group input[type="radio"]:not(.customized)').each(function(ind, cObj){
        if(typeof $(cObj).attr('data-text') !== 'undefined' && $(cObj).attr('data-text').length != $(cObj).attr('data-text').replace('checkbox_href', '').length)
        {
            $(cObj).after('<div class="custom_data_text">' + $(cObj).attr('data-text') + '</div><div class="custom_input' + ($(cObj).prop('checked') === true ? ' checked' : '') + '" data-name="' + $(cObj).attr('name') + '"><div class="ico"></div></div>');
        }
        else
        {
            $(cObj).after('<div class="custom_input' + ($(cObj).prop('checked') === true ? ' checked' : '') + '" data-name="' + $(cObj).attr('name') + '"><div class="ico"></div>' + (typeof $(cObj).attr('data-text') !== 'undefined' ? $(cObj).attr('data-text') : '') + '</div>');
        }
    });
    $('.content form .radio_group input[type="checkbox"]:not(.customized)').each(function(ind, cObj){
        if(typeof $(cObj).attr('data-text') !== 'undefined' && $(cObj).attr('data-text').length != $(cObj).attr('data-text').replace('checkbox_href', '').length)
        {
            $(cObj).after('<div class="custom_data_text">' + $(cObj).attr('data-text') + '</div><div class="custom_input checkbox' + ($(cObj).prop('checked') === true ? ' checked' : '') + '" data-name="' + $(cObj).attr('name') + '"><div class="ico"></div></div>');
        }
        else
        {
            $(cObj).after('<div class="custom_input checkbox' + ($(cObj).prop('checked') === true ? ' checked' : '') + '" data-name="' + $(cObj).attr('name') + '"><div class="ico"></div>' + (typeof $(cObj).attr('data-text') !== 'undefined' ? $(cObj).attr('data-text') : '') + '</div>');
        }
    });
    $('.content form .radio_group input:not(.customized)').on('click', function(e){
        if(!$(this).is('[readonly]'))
        {
            if(!$(this).siblings('.custom_input').hasClass('checked'))
            {//check checked
                //remove other checked radio siblings
                $(this).parents('.radio_group').find('.custom_input[data-name="' + $(this).attr('name') +  '"]:not(.checkbox)').removeClass('checked');
                $(this).siblings('.custom_input').addClass('checked');
            }
            else if($(this).siblings('.custom_input').hasClass('checkbox'))
            {//unset checked to custom checkbox
                $(this).siblings('.custom_input').removeClass('checked');
            }
        }
    });
    $('.content form .radio_group input[type="radio"]:not(.customized), .content form .radio_group input[type="checkbox"]:not(.customized)').addClass('customized');
}

//form field validation & masks
// email (not perfect RFC822 standart example; from https://gist.github.com/badsyntax/719800 )
function checkEmailRfc(arg)
{
    return /^([^\x00-\x20\x22\x28\x29\x2c\x2e\x3a-\x3c\x3e\x40\x5b-\x5d\x7f-\xff]+|\x22([^\x0d\x22\x5c\x80-\xff]|\x5c[\x00-\x7f])*\x22)(\x2e([^\x00-\x20\x22\x28\x29\x2c\x2e\x3a-\x3c\x3e\x40\x5b-\x5d\x7f-\xff]+|\x22([^\x0d\x22\x5c\x80-\xff]|\x5c[\x00-\x7f])*\x22))*\x40([^\x00-\x20\x22\x28\x29\x2c\x2e\x3a-\x3c\x3e\x40\x5b-\x5d\x7f-\xff]+|\x5b([^\x0d\x5b-\x5d\x80-\xff]|\x5c[\x00-\x7f])*\x5d)(\x2e([^\x00-\x20\x22\x28\x29\x2c\x2e\x3a-\x3c\x3e\x40\x5b-\x5d\x7f-\xff]+|\x5b([^\x0d\x5b-\x5d\x80-\xff]|\x5c[\x00-\x7f])*\x5d))*$/.test( arg );
}

function checkEmail(arg)
{
    var pattern = /^([a-z0-9_\.-])+@[a-z0-9-]+\.([a-z]{2,4}\.)?[a-z]{2,4}$/i;
    if (pattern.test(arg)) {
        return true;
    }
    else {
        return false;
    }
}

//field masks
function checkMask(argObj, maskType)
{
    var temp_val = argObj.val();
    if(temp_val == '')
    {
        return;
    }
    var new_val = '0';
    switch(maskType)
    {
        case 'pos_int': //positive integer value
            new_val = parseInt(temp_val.toString().replace(/[^0-9]/g, ''));
            if(!checkCorrectInt(new_val))
            {
                new_val = '0';
            }
            else if(new_val < 0)
            {
                new_val = -1 * new_val;
            }
            break;

        case 'pos_int_empty': //positive integer value (empty as default)
            new_val = temp_val.toString().replace(/[^0-9]/g, '');
            if(!checkCorrectInt(new_val))
            {
                new_val = '';
            }
            else if(new_val < 0)
            {
                new_val = -1 * new_val;
            }
            break;

        case 'phone':// +7 (495) 123-45-67 or 8-916-1234567
            var reg_test = /^\+?[0-9]{0,2}(\-?[0-9]{0,4}\-?| ?\(?[0-9]{0,4}\)? ?)[0-9]{0,3}\-?[0-9]{0,2}\-?[0-9]{0,2}$/;
            new_val = temp_val.toString().replace(/ /g, '');
            if(!reg_test.test(new_val))
            {//if not correct value -> check if set last correct value
                var stab_val = argObj.attr('data-stabval');
                if(typeof stab_val != 'undefined' && stab_val != '')
                {
                    new_val = stab_val;
                }
                else
                {
                    new_val = '';
                }
            }
            argObj.attr('data-stabval', new_val);
            break;

        case 'email':
            //email RFC 5322 (изменен - добавлено тире внутрь почты)
            var reg_test = /^[0-9a-z\-\.\_]*\@?[a-z0-9]*[\-]?[a-z0-9]*\.?([a-z]{0,3}|co?\.?j?p?)$/i;
            //var reg_test = /^(?:[a-z0-9!#$%&'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+/=?^_`{|}~-]+)*|"(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21\x23-\x5b\x5d-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])*")@(?:(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?|\[(?:(?:(2(5[0-5]|[0-4][0-9])|1[0-9][0-9]|[1-9]?[0-9]))\.){3}(?:(2(5[0-5]|[0-4][0-9])|1[0-9][0-9]|[1-9]?[0-9])|[a-z0-9-]*[a-z0-9]:(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21-\x5a\x53-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])+)\])$/i;
            new_val = temp_val.toString().replace(' ', '');
            //if(!reg_test.test(new_val))
            if(!checkEmailMask(new_val))
            {//if not correct value -> check if set last correct value
                var stab_val = argObj.attr('data-stabval');
                if(typeof stab_val != 'undefined' && stab_val != '')
                {
                    new_val = stab_val;
                }
                else
                {
                    new_val = '';
                }
            }
            argObj.attr('data-stabval', new_val);
            break;

        case 'car_number':
            var reg_exp = /^[авекмнорстух](\d{0,1}|\d\d|\d\d\d|\d\d\d[авекмнорстух]|\d\d\d[авекмнорстух][авекмнорстух]|\d\d\d[авекмнорстух][авекмнорстух]\d|\d\d\d[авекмнорстух][авекмнорстух]\d\d{1,2})$/i;
            new_val = argObj.val().toString().replace(/ /g, '');
            if(new_val != '' && !reg_exp.test(new_val))
            {
                var cur_check_val = argObj.attr('data-check');
                if(typeof cur_check_val != 'undefined' && cur_check_val != '')
                {
                    new_val = cur_check_val;
                }
                else
                {
                    new_val = '';
                }
            }
            else
            {
                new_val = new_val.toUpperCase();
                argObj.attr('data-check', new_val);
            }
            break;

        case 'login':

            var reg_test = /[^a-zA-Z0-9\_\-]+/;
            var reg_test2 = /[\_\-]/;
            new_val = argObj.val().toString().replace(/[а-яА-ЯёЁ ]/g, '');
            if(reg_test2.test(new_val.substr(0, 1).toString()) || new_val != new_val.replace(reg_test, '') || new_val.length > 50)
            {//if not correct value -> check if set last correct value
                var stab_val = argObj.attr('data-stabval');
                if(typeof stab_val != 'undefined' && stab_val != '')
                {
                    new_val = stab_val;
                }
                else
                {
                    new_val = '';
                }
            }
            argObj.attr('data-stabval', new_val);
            break;

        case 'weight': // Вес аналогично цене
        case 'price': //positive integer value (empty as default)
            temp_val = temp_val.toString().replace(',', '.');
            new_val = temp_val.toString().replace(/[^0-9\.]/g, '');
            if(!checkCorrectFloat(new_val)){
                if(new_val.toString().length - new_val.toString().replace(/\./g, '').length > 1) {
                    //удаляем лишние точки, если только они портят число
                    if(checkCorrectInt(new_val.toString().replace(/\./g, ''))){
                        var temp_pos = new_val.toString().indexOf('.');
                        new_val = new_val.toString().replace(/\./g, '');
                        new_val = new_val.substr(0, temp_pos) + '.' + new_val.substr(temp_pos, new_val.length - temp_pos);
                    }else{
                        new_val = '';
                    }
                }
            }
            else if(new_val < 0)
            {
                new_val = -1 * new_val;
            }

            break;

        case 'price_not_empty_f': //цена (до двух символов после запятой) не пустая (по умолчанию 0), форматированная (с пробелами)
            temp_val = temp_val.toString().replace(',', '.');
            new_val = temp_val.toString().replace(/[^0-9\.]/g, '');
            if(!checkCorrectFloat(new_val)){
                if(new_val.toString().length - new_val.toString().replace(/\./g, '').length > 1) {
                    //удаляем лишние точки, если только они портят число
                    if(checkCorrectInt(new_val.toString().replace(/\./g, ''))){
                        var temp_pos = new_val.toString().indexOf('.');
                        new_val = new_val.toString().replace(/\./g, '');
                        new_val = new_val.substr(0, temp_pos) + '.' + new_val.substr(temp_pos, new_val.length - temp_pos);
                    }else{
                        new_val = '';
                    }
                }
            }
            else if(new_val < 0)
            {
                new_val = -1 * new_val;
            }

            new_val = number_format(new_val, 0, '.', ' ');

            break;
    }

    argObj.val(new_val);
}

//custom select2 dropdown elements (hide select default values after first change)
function formatAgroState(state)
{
    //remove default value
    var temp_val = $(state.element).attr('value');
    if(typeof temp_val == 'undefined' || temp_val == '' || temp_val.toString().length != temp_val.replace(/[^0-9]/gi, '').length)
    {//return empty value
        return $('<span class="none"></span>');
    }
    var addit_val = '';

    //флаг наличия принятий
    var addit_tmp = $(state.element).attr('data-plimit');
    if(typeof addit_tmp != 'undefined') {
        if (addit_tmp > 0) {
            addit_val = 'П';
        }
    }

    //если есть агентский договор
    addit_tmp = $(state.element).attr('data-pcontract');
    if(typeof addit_tmp != 'undefined') {
        if (addit_tmp === '1') {
            addit_val = (addit_val != '' ? addit_val + ', А' : 'А');
        }
    }

    if(addit_val !== ''){
        addit_val = '<span class="lmt"> - ' + addit_val + '</span>';
    }

    //если обрабатывается select со страницы ВП (option склада и культуры содержит data-cnt)
    var cnt_val = $(state.element).attr('data-cnt');
    if(typeof cnt_val != 'undefined'){
        if(cnt_val != '0'){
            return $('<span class="val bold">' + state.text + ' (' + cnt_val + ')'+addit_val+'</span>');
        }else if(cnt_val == 0){
            return '';
        }
    }



    if (!state.id) {
        return state.text;
    }

    //return normal value
    return $('<span class="val">' + state.text + addit_val+'</span>');
}

//check if argument is a correct float value (may be integer)
function checkCorrectFloat(arg)
{
    var temp_val = parseFloat(arg);
    return (!isNaN(temp_val) && arg.toString().length == temp_val.toString().length && (arg.toString().length - arg.toString().replace('.', '').length) < 2); //if not NaN then float or integer, otherwise it have other symbols
}

//check if argument is a correct int value
function checkCorrectInt(arg)
{
    var temp_val = arg.toString().replace(/[^0-9]/g, '');
    return (arg.toString().length == temp_val.length); //if not NaN then integer, otherwise it could be string other symbols
}

/***
 number - число
 decimals - количество знаков после разделителя
 dec_point - символ разделителя
 separator - разделитель тысячных
 ***/
function number_format(number, decimals, dec_point, separator ) {
    number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
    var n = !isFinite(+number) ? 0 : +number,
        prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
        sep = (typeof separator === 'undefined') ? ',' : separator ,
        dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
        s = '',
        toFixedFix = function(n, prec) {
            var k = Math.pow(10, prec);
            return '' + (Math.round(n * k) / k)
                .toFixed(prec);
        };
    // Фиксим баг в IE parseFloat(0.55).toFixed(0) = 0;
    s = (prec ? toFixedFix(n, prec) : '' + Math.round(n))
        .split('.');
    if (s[0].length > 3) {
        s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
    }
    if ((s[1] || '')
        .length < prec) {
        s[1] = s[1] || '';
        s[1] += new Array(prec - s[1].length + 1)
            .join('0');
    }
    return s.join(dec);
}

//mobile menu show/hide
function showHideMenu()
{
    if(stop_menu_animation == 0)
    {
        var wObj = $('#header');
        if(wObj.hasClass('active'))
        {
            wObj.removeClass('active');
            $('body').removeClass('disable_scroll');
        }
        else
        {
            wObj.addClass('active');
            $('body').addClass('disable_scroll');
        }
    }
}

//show loader form
function startBackLoad()
{
    var cur_deg = 0;
    if(stop_backshad_animation == 0)
    {
        stop_backshad_animation = 1;
        if($('#back_shad').length > 0)
        {
            $('#back_shad:first').fadeIn(300);
        }
        else
        {
            $('body').append('<div id="back_shad"></div>');
            $('#back_shad').fadeIn(300);
        }

        if($('#load_img').length > 0)
        {
            //$('#load_img:first').fadeIn(300, function(){ stop_backshad_animation = 0;});
            $('#load_img:first').show(300, 'swing', function(){ stop_backshad_animation = 0;});
        }
        else
        {
            $('body').append('<div id="load_img"></div>');
            //$('#load_img').fadeIn(300, function(){ stop_backshad_animation = 0;});
            $('#load_img').show(300, 'swing', function(){ stop_backshad_animation = 0;});
        }
    }

    /*
    backshad_animation_interval_id = setInterval(function(){
        if(cur_deg == 360)
        {
            cur_deg = 0;
        }
        cur_deg += 15;
        $('#load_img').css('transform', 'rotate(' + cur_deg + 'deg)');
    }, 200);
    */
    return true;
}

//show loader form with text
function startBackLoadWithText(textArg)
{
    var cur_deg = 0;
    if(stop_backshad_animation == 0)
    {
        stop_backshad_animation = 1;
        if($('#back_shad').length > 0)
        {
            $('#back_shad:first').fadeIn(300);
        }
        else
        {
            $('body').append('<div id="back_shad"></div>');
            $('#back_shad').fadeIn(300);
        }

        if($('#load_img').length > 0)
        {
            //$('#load_img:first').fadeIn(300, function(){ stop_backshad_animation = 0;});
            $('#load_img:first').html('<div class="load_img_text">' + textArg + '</div>');
            $('#load_img:first').show(300, 'swing', function(){ stop_backshad_animation = 0;});
        }
        else
        {
            $('body').append('<div id="load_img"><div class="load_img_text">' + textArg + '</div></div>');
            //$('#load_img').fadeIn(300, function(){ stop_backshad_animation = 0;});
            $('#load_img').show(300, 'swing', function(){ stop_backshad_animation = 0;});
        }
    }

    /*
    backshad_animation_interval_id = setInterval(function(){
        if(cur_deg == 360)
        {
            cur_deg = 0;
        }
        cur_deg += 15;
        $('#load_img').css('transform', 'rotate(' + cur_deg + 'deg)');
    }, 200);
    */
    return true;
}

//hide loader form
function stopBackLoad()
{
    if(stop_backshad_animation == 0)
    {
        stop_backshad_animation = 1;

        if($('#back_shad').length > 0)
        {
            $('#back_shad:first').fadeOut(300);
        }

        if($('#load_img').length > 0)
        {
            $('#load_img:first').fadeOut(300, function(){
                stop_backshad_animation = 0;
                //clearInterval(backshad_animation_interval_id);
                backshad_animation_interval_id = 0;
                $(this).text('');
            });
        }
    }
    return true;
}

//images preload
// Usage: preload([ 'img/imageName.jpg', 'img/anotherOne.jpg', 'img/blahblahblah.jpg' ]);
function preload(arrayOfImages) {
    $(arrayOfImages).each(function(){
        $('<img/>')[0].src = this;
        // Alternatively you could use:
        // (new Image()).src = this;
    });
}

//make special click to checkbox hrefs
function triggerCustomClick(cObj, href_flag)
{
    if(href_flag)
    {
        window.open($(cObj).attr('data-href'));
    }
    else
    {
        $(cObj).parents('.custom_data_text').siblings('input[type="checkbox"]').trigger('click');
    }
}

function in_array(value, array) {
    for(var i=0; i<array.length; i++){
        if(value == array[i]) return true;
    }
    return false;
}

function goTriggerChange(cObj)
{
    $(cObj).trigger('change');
}

function changeCalendar() {
    var el = $('[id ^= "calendar_popup_"]'); //найдем div  с календарем
    var links = el.find(".bx-calendar-cell"); //найдем элементы отображающие дни
    $('.bx-calendar-left-arrow').attr({'onclick': 'changeCalendar();'}); //вешаем функцию изменения  календаря на кнопку смещения календаря на месяц назад
    $('.bx-calendar-right-arrow').attr({'onclick': 'changeCalendar();'}); //вешаем функцию изменения  календаря на кнопку смещения календаря на месяц вперед
    $('.bx-calendar-top-month').attr({'onclick': 'changeMonth();'}); //вешаем функцию изменения  календаря на кнопку выбора месяца
    $('.bx-calendar-top-year').attr({'onclick': 'changeYear();'}); //вешаем функцию изменения  календаря на кнопку выбора года
    var date = new Date();
    for (var i =0; i < links.length; i++)
    {
        var atrDate = links[i].attributes['data-date'].value;
        var d = date.valueOf();
        var g = links[i].innerHTML;
        if (date - atrDate <= 24*60*60*1000) {
            $('[data-date="' + atrDate +'"]').addClass("bx-calendar-date-hidden disabled"); //меняем класс у элемента отображающего день, который меньше по дате чем текущий день
        }
    }
}

function changeMonth() {
    var el = $('[id ^= "calendar_popup_month_"]'); //найдем div  с календарем
    var links = el.find(".bx-calendar-month");
    for (var i =0; i < links.length; i++) {
        var func = links[i].attributes['onclick'].value;
        $('[onclick="' + func +'"]').attr({'onclick': func + '; changeCalendar();'}); //повесим событие на выбор месяца
    }
}

function changeYear() {
    var el = $('[id ^= "calendar_popup_year_"]'); //найдем div  с календарем
    var link = el.find(".bx-calendar-year-input");
    var func2 = link[0].attributes['onkeyup'].value;
    $('[onkeyup="' + func2 +'"]').attr({'onkeyup': func2 + '; changeCalendar();'}); //повесим событие на ввод года
    var links = el.find(".bx-calendar-year-number");
    for (var i =0; i < links.length; i++) {
        var func = links[i].attributes['onclick'].value;
        $('[onclick="' + func +'"]').attr({'onclick': func + '; changeCalendar();'}); //повесим событие на выбор года
    }
}

function simpleTranslit(arg)
{
    var result = '';
    var ru_sym = 'абвгдеёжзийклмнопрстуфхцчшщьыъэюяАБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЬЫЪЭЮЯ';
    var en_sym = ["a", "b", "v", "g", "d", "e", "yo","zh", "z", "i", "j", "k", "l", "m", "n", "o", "p", "r", "s", "t", "u", "f", "h", "c", "ch", "sh", "sh", "", "y", "", "e", "yu", "ya",
                  "A", "B", "V", "G", "D", "E", "YO","ZH", "Z", "I", "J", "K", "L", "M", "N", "O", "P", "R", "S", "T", "U", "F", "H", "C", "CH", "SH", "SH", "", "Y", "", "E", "YU", "YA"];
    var c_ind = 0;
    for(var i = 0; i < arg.length; i++)
    {
        if(ru_sym.indexOf(arg[i]) != -1)
        {
            c_ind = ru_sym.indexOf(arg[i]);
            if(typeof en_sym[parseInt(c_ind)] != 'undefined')
            {
                result += en_sym[c_ind];
            }
        }
        else
        {
            result += arg[i];
        }
    }

    return result;
}

function setCookie(name,value,days) {
    var expires = "";
    if (days) {
        var date = new Date();
        date.setTime(date.getTime() + (days*24*60*60*1000));
        expires = "; expires=" + date.toUTCString();
    }
    document.cookie = name + "=" + (value || "")  + expires + "; path=/";
}

function getCookie(name) {
    var nameEQ = name + "=";
    var ca = document.cookie.split(';');
    for(var i=0;i < ca.length;i++) {
        var c = ca[i];
        while (c.charAt(0)==' ') c = c.substring(1,c.length);
        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
    }
    return null;
}

function deleteCookie(name) {
    setCookie(name, "", -1);
}

function copyToClipboardFF(text) {
    window.prompt ("Copy to clipboard: Ctrl C, Enter", text);
}

function copyToClipboard(textVal) {
    var success   = false,
        range     = document.createRange(),
        selection;
    // For IE.
    if (window.clipboardData) {
        window.clipboardData.setData("Text", textVal);
        success = true;
    } else {
        // Create a temporary element off screen.
        var tmpElem = $('<div>');
        tmpElem.css({
            position: "absolute",
            left:     "-1000px",
            top:      "-1000px",
        });
        // Add the input value to the temp element.
        tmpElem.text(textVal);
        $("body").append(tmpElem);
        // Select temp element.
        range.selectNodeContents(tmpElem.get(0));
        selection = window.getSelection ();
        selection.removeAllRanges ();
        selection.addRange (range);
        // Lets copy.
        try {
            success = document.execCommand ("copy", false, null);
        }
        catch (e) {
            copyToClipboardFF(textVal);
            success = true;
        }

        // remove temp element.
        tmpElem.remove();
    }

    return success;
}

//проверяет является ли строка телефоном (10 или 11 цифр, возможны варианты от "+7 (123) 456-78-90" до "123 456 78 90")
function checkIsPhone(stringPhone){
    var pattern = /^ *(\+\d)? ?(\(\d{3}\)|\d{3}) ?\d{3}\-? ?\d{2}\-? ?\d{2} *$/g;
    return pattern.test(stringPhone);
}

//стандартный попап с наполнением
function defaultPopupShow(argTitle, argText){
    var popup_obj = $('#def_popup_window');
    if(popup_obj.length == 0){
        $('#back_shad').addClass('active');
        $('body').append('<div id="def_popup_window" class="active"><div class="popup_close" onclick="closeDefPopup();"></div>' + (argTitle != '' ? '<div class="popup_header">' + argTitle + '</div>' : '') + '<div class="text"></div><div class="clear"></div></div>');
        var textObj = $('#def_popup_window .text:first');
        textObj.html(argText);
    }
}

function closeDefPopup()
{
    var popup_obj = $('#def_popup_window');
    if(popup_obj.length == 1){
        $('#back_shad').removeClass('active');
        popup_obj.remove();
    }
}

/**
 * Возвращает массив Get параметров
 * @returns {{}}
 */
function getParams() {
    var params = window
        .location
        .search
        .replace('?','')
        .split('&')
        .reduce(
            function(p,e){
                var a = e.split('=');
                p[ decodeURIComponent(a[0])] = decodeURIComponent(a[1]);
                return p;
            },
            {}
        );
    return params;

}

/**
 * Проверка строки/подстроки почты
 * Своя функция
 * исходит из требований:
 * 1) первый символ буквенный или цифра (для имени почты и домена/поддомена)
 * 2) не может быть два тире подряд
 * 3) в домене не может быть двух точек подряд
 * 4) как имя почты так и домен (и поддомен) не может оканчиваться на тире
 * 5) доменная зона может состоять только из буквенных символов
 *
 * @param sArg - полная или частичная почта
 * @returns boolean
 */
function checkEmailMask(sArg) {
    var bResult = false, sReg = /[a-z0-1]/i, sTemp = '', arTemp = [];

    try {
        // 1) первый символ буквенный (для имени почты)
        sTemp = sArg.substr(0, 1);
        if(sReg.test(sTemp)){
            // console.log(1);
            sTemp = sArg;
            // 2) не может быть два тире подряд
            if(sTemp.length === sTemp.replace('--', '').length){
                // console.log(2);
                // 3) в домене не может быть двух точек подряд
                if(sTemp.length === sTemp.replace('..', '').length){
                    // console.log(3);
                    //не может быть больше одной собаки
                    arTemp = sTemp.split('@');
                    if(arTemp.length === 2) {
                        //если уже есть часть почты до собаки
                        sReg = /^[0-9a-z\-\.\_]*\@/i;
                        if (sReg.test(sTemp)) {
                            // 4) имя почты не может оканчиваться на тире
                            bResult = false;
                            if (sTemp.length === sTemp.replace('-@', '').length) {
                                //если уже есть вся почта (или поддомены)
                                sReg = /^[0-9a-z\-\.\_]+\@[0-9a-z\-\.]+$/i;
                                if (sReg.test(sTemp)){
                                    //после собаки может стоять либо цифра либо буква
                                    sTemp = arTemp[1].substr(0, 1);
                                    sReg = /[a-z0-9]/i;
                                    if(sReg.test(sTemp)) {
                                        bResult = true;
                                        arTemp = arTemp[1].split('.');
                                        //каждая часть разделенная точкой должна начинаться цифрой или буквой и не должна заканчиваться тире
                                        for (var i = 0; i < arTemp.length; i++) {
                                            if(arTemp[i].length > 0){

                                                // 1б) первый символ для домена может быть буквенный или цифрой
                                                sTemp = arTemp[i].substr(0, 1);
                                                if(!sReg.test(sTemp)){
                                                    bResult = false;
                                                    break;
                                                }

                                                // 4б) поддомен не может оканчиваться на тире
                                                if(i != arTemp.length - 1) {
                                                    sTemp = arTemp[i].substr(-1, 1);
                                                    if (sTemp.length !== sTemp.replace('-', '').length) {
                                                        bResult = false;
                                                        break;
                                                    }
                                                }
                                            }
                                        }

                                        // if(bResult){
                                        //     bResult = false;
                                        // }
                                    }
                                } else {
                                    //проверяем на отсутствие подчеркивания после собаки
                                    sReg = /^[0-9a-z\-\.\_]+\@[0-9a-z\-\.\_]+$/i;
                                    if (!sReg.test(sTemp)) {
                                        //если еще не вся почта есть
                                        bResult = true;
                                    }
                                }
                            }
                        } else {
                            //если еще не внесена почта до собаки
                            bResult = true;
                        }
                    }else if(arTemp.length == 1){
                        //если еще не внесена почта до собаки
                        bResult = true;
                    }
                }
            }
        }
    }catch(obException){
        //console.log(100);
    }

    return bResult;
}