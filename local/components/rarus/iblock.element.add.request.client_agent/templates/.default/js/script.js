$(document).ready(function() {
    rrsChangeCultureGroup();

    /*$('.request_add input[name="urgency"]').on('change', function(){
        $('.request-block-r').html('');
        $('.request_add .submit-btn').addClass('inactive');
    });*/

    $('select[name="payment"], select[name="delivery"], select[name="client_id"]').on('change', function(){
        $(this).parents('.row').removeClass('error').find('.row_val').find('.row_err').html('');

        if($(this).attr('name') == 'client_id'){
            $('.request_tariffs').attr('href', '/client_agent/tariffs/?client='+$(this).val());

            if($('.spec_row_client_link').css('display') != 'none'){
                $('.spec_row_client_link').css('display', 'none');
            }

            if($('.request-block-r.row .request-block-intro').length != 0){
                $('.request-block-r.row').text('');
            }

            $('form[name="iblock_add"] input[name="iblock_submit"]').addClass('inactive');

            $('.spec_row_default .no_deal_rights').css('display', 'none');

            var check_status = checkSelectedClientRights();
            if(check_status == 'n'){
                $('.spec_row_default .no_deal_rights.spec_row_client_link').css('display', 'block');
            } else if(check_status == 'nv'){
                $('.spec_row_default .no_deal_rights.spec_row_ok').css('display', 'block');
            } else if(check_status == 'nd'){
                $('.spec_row_default .no_deal_rights.spec_row_undemo').css('display', 'block');
                var temp_val = $('.spec_row_default .no_deal_rights.spec_row_undemo a:first').attr('href').replace(/\?uid=[0-9]*/g, '?uid=' + $(this).val());
                $('.spec_row_default .no_deal_rights.spec_row_undemo a:first').attr('href', temp_val);
            } else if(check_status == 'na'){
                $('.spec_row_default .no_deal_rights.spec_row_noag').css('display', 'block');
            } else if(check_status == 'ndoc'){
                $('.spec_row_default .no_deal_rights.spec_row_nodoc').css('display', 'block');
            }

            if(check_status != 'y'){
                $('.request-block10').hide();
            }
        }
    });

    /*$('#request_tarrifs').on('click',function(){
        var remoteness = $('input[name="remoteness"]').val();
        var csort = $('input[name="csort"]').val();
        window.open('/client_agent/request/tariffs/index.php?'+'remoteness='+remoteness+'&csort='+csort, '_blank');
    });*/

    $('.submit-btn').on('click', function(e)
    {
        var found_err = false;
        var err_val = '';
        var err_scroll_top = 0;
        var temp_val = '';

        //check form fields
        //volume check
        var checkObj = $('input[type="text"][name="volume"]');
        if(checkObj.length == 1)
        {
            temp_val = checkObj.val();
            if(temp_val == '')
            {
                err_val = 'Пожалуйста заполните это обязательное поле';
            }
            else if(!checkCorrectInt(temp_val) || parseInt(temp_val) < 0)
            {
                err_val = 'Пожалуйста укажите целое положительное число';
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

        //payment check
        var checkObj = $('select[name="delivery"]');
        if(checkObj.length == 1)
        {
            temp_val = checkObj.val();
            if(temp_val == 'выбрать')
            {
                err_val = 'Пожалуйста, укажите потребность в доставке';
            }
            if(err_val != '')
            {
                found_err = true;
                if(err_scroll_top == 0)
                {
                    err_scroll_top = checkObj.offset().top - 100;
                }
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

        //check free delivery parameter
        checkObj = $('.additional_row.remoteness.active input[type="text"][name="remoteness"]');
        if(checkObj.length == 1)
        {
            temp_val = checkObj.val();
            if(temp_val == '')
            {
                err_val = 'Пожалуйста заполните это обязательное поле';
            }
            else if(!checkCorrectInt(temp_val) || parseInt(temp_val) < 0)
            {
                err_val = 'Пожалуйста укажите целое положительное число';
            }

            if(err_val != '')
            {
                found_err = true;
                if(err_scroll_top == 0)
                {
                    err_scroll_top = checkObj.offset().top - 100;
                }
                if(checkObj.parents('.row').find('.row_err').length == 1)
                {
                    checkObj.parents('.row').addClass('error').find('.row_err').text(err_val);
                }
                else
                {
                    checkObj.parents('.row').find('.additional_row').append('<div class="row_err"></div>');
                    checkObj.parents('.row').addClass('error').find('.row_err').text(err_val);
                }
            }
            err_val = '';
        }

        //payment check
        var checkObj = $('select[name="payment"]');
        if(checkObj.length == 1)
        {
            temp_val = checkObj.val();
            if(temp_val == 'выбрать')
            {
                err_val = 'Пожалуйста, выберите тип оплаты';
            }
            if(err_val != '')
            {
                found_err = true;
                if(err_scroll_top == 0)
                {
                    err_scroll_top = checkObj.offset().top - 100;
                }
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

        if (found_err == false && !$(this).hasClass('inactive')) {
            startBackLoad();
            var formObj = $(this).parents('form.request_add');
            setTimeout(function(){
                formObj.submit();
            }, 350);
        }
        else if(found_err)
        {
            window.scrollTo(0, err_scroll_top);
        }
    });

    $('.request_add select[name="delivery"]').on('change', function(){
        if ($(this).val() == 385) {
            $('.remoteness').addClass('active');
            $('.tarif_info').addClass('active');
        }
        else {
            $('.remoteness').removeClass('active');
            $('.tarif_info').removeClass('active');
        }
    });

    //add fields masks
    //integer positive
    $('.request_add').on('keyup', 'input[name="volume"], input[name="remoteness"]', function(){
        checkMask($(this), 'pos_int');
    });

    $('.request_add').on('change', 'input[name="csort"]', function(e){
        var csort = $(this).val();

        if(csort){
            $.ajax({
                type: "POST",
                url: "/ajax/changeCulture.php",
                data: "csort="+csort,
                success: function(msg){
                    $('.request-block3').html(msg);
                    $('.request-blocks').show();
                    makeCustomForms();
                    $('.request-block-r.row').html(''); //remove calculated list
                    $('.request_add .submit-btn').addClass('inactive');
                    $('.request-block10.row').hide();
                }
            });
        }

        e.preventDefault();
    });

    $('.request_add').on('click', '.add-dump', function(e){
        var cntn = $(this).parents('.quality-param-intro').siblings('.quality-dump-intro');
        cntn.toggleClass("active");
        if (cntn.hasClass("active")) {
            $(this).addClass('collapse').html("- Свернуть");
        }
        else {
            if (cntn.data('dump') == 'Y') {
                var txt = '+ Добавить ограничения и сбросы';
            }
            else {
                var txt = '+ Добавить ограничения';
            }
            $(this).removeClass('collapse').html(txt);
        }
        e.preventDefault();
    });

    $('.request_add').on('click', '.add-dump-table', function(e){
        var cntn = $(this).parents('.quality-param-intro').siblings('.quality-dump-table-intro');
        cntn.addClass("active");
        $(this).addClass('inactive');

        var obj = $(this).parents('.quality-dump-intro').find('.add-dump-item:first');
        rrsAddDumpItem(obj, $(this).hasClass('straight'));

        if($(this).hasClass('straight'))
        {
            $(this).addClass('inactive');
        }
        $(this).siblings('.add-dump-table, .add_straight_or').addClass('inactive');

        e.preventDefault();
    });

    $('.request_add').on('change', '.sub_row.txt input[type="text"]', function(e){
        checkParamsCorrect($(this), true);
    });

    $('.request_add').on('change', '#agree-cost, #agreement', function(){
        rrsCheckSubmit();
    });

    //check quality change value is correct (temporary save current value)
    $('.request_add').on('focus', '.quality-param-intro input[type="text"]', function(){
        if(checkCorrectFloat($(this).val()))
        {
            $(this).attr('data-tempval', $(this).val());
        }
    });

    //remove text error value when user is correcting
    $('.request_add').on('focus', 'input[type="text"]', function(e){
        $(this).parents('.row.error').removeClass('error');
    });
});

function rrsChangeCultureGroup() {
    $('.request_add input[name="cgroup"]').on('change', function(e){
        var cgroup = $(this).val();

        $('.request-block3').html('');
        $('.request-blocks').hide();

        if (cgroup) {
            $.ajax({
                type: "POST",
                url: "/ajax/changeCultureGroup.php",
                data: "cgroup="+cgroup,
                success: function(msg){
                    $('.request-block2').html(msg);
                    makeCustomForms();
                }
            });
        }

        e.preventDefault();
    });
}

function rrsCalculateRequest(e) {

    //проверка выбран ли покупатель
    var selObj = $('select[name="client_id"]');
    var client_id = 0;
    if(selObj.length != 1){
        return false;
    }

    client_id = selObj.val();
    if(client_id != '' && client_id > 0){
        startBackLoad();
        $('.request_add .submit-btn').addClass('inactive');
        var form = $(e).parents('.request_add');
        $.ajax({
            type: "POST",
            url: "/ajax/calculateRequest.php",
            data: form.serialize(),
            success: function(msg){
                $('.request-block-r').html(msg);
                makeCustomForms();
                setTimeout('stopBackLoad()', 300);

                /* //проверка доступности добавления запроса
                var clSelectedObj = $('form[name="iblock_add"] select[name="client_id"] option[value="' + client_id + '"]');
                if(clSelectedObj.length == 1
                    && clSelectedObj.attr('data-right') == 'y'
                    && clSelectedObj.attr('data-agright') == 'y'
                ){
                }*/
            }
        });
    } else {
        if(selObj.siblings('.row_err').length == 1){
            selObj.siblings('.row_err').text('Выберите покупателя');
        } else {
            selObj.parent().append('<div class="row_err">Выберите покупателя</div>');
        }

        selObj.parents('.row').addClass('error');

        var offsetObj = selObj.offset();
        var err_top = offsetObj.top - 100;
        $(document).scrollTop(err_top);
    }
}

function rrsClickMin(e) {
    var input = $(e).siblings('input');
    var val = parseFloat(input.val());
    var step = parseFloat($(e).data('step'));

    if (!step)
        step = 0.1;

    val = val - step;
    input.val((Math.abs(val - Math.round(val)) < 0.01 ? Math.round(val) : val.toFixed(1)));
    checkParamsCorrect(input, false);

    if ($(e).siblings('.min').length > 0 && $(e).parents('.percent_block').length == 1) {
        if (val > 0)
            $(e).siblings('.min').html('прибавка');
        else if (val < 0)
            $(e).siblings('.min').html('сброс');
        else
            $(e).siblings('.min').html('');
    }
}

function rrsClickMax(e) {
    var input = $(e).siblings('input');
    var val = parseFloat(input.val());
    var step = parseFloat($(e).data('step'));

    if (!step)
        step = 0.1;

    val = val + step;

    input.val((Math.abs(val - Math.round(val)) < 0.01 ? Math.round(val) : val.toFixed(1)));
    checkParamsCorrect(input, false);

    if ($(e).siblings('.min').length > 0 && $(e).parents('.percent_block').length == 1) {
        if (val > 0)
            $(e).siblings('.min').html('прибавка');
        else if (val < 0)
            $(e).siblings('.min').html('сброс');
        else
            $(e).siblings('.min').html('');
    }
}

function rrsAddDumpItem(e, is_traight) {
    var parent = $(e).parents('.quality-dump-table-intro');
    var wParent = $(e).parents('.sub_row.txt');
    var items = parent.find('.quality-dump-table-item').length;
    var init_min = -1000000, init_max;

    //get initial values (default -> min & max, otherwise from higher dump max is initial dump min)
    if(wParent.find('.quality-dump-table-item').length > 0)
    {//set higher dump max is initial dump min
        wParent.find('.quality-dump-table-item input[type="text"][name*=MAX]').each(function(ind, cObj){
            if(parseFloat($(cObj).val()) > init_min)
            {
                init_min = $(cObj).val();
            }
        });
    }
    else
    {//default
        init_min = wParent.find('input[type="text"][name*=MIN]:first').val();
    }
    init_max = wParent.find('input[type="text"][name*=MAX]:first').val();

    if (parseInt(items) < 6) {
        var step = parent.data('step');
        var param = parent.data('param');

        var newItem = '<div class="quality-dump-table-item">';
        newItem += '<div class="quality-param-intro d_fst">';
        newItem += '<span class="minus minus_bg" data-step="'+step+'" onclick="rrsClickMin(this);"></span>';
        newItem += '<input type="text" name="param['+param+'][DUMP][MIN][]" value="' + init_min + '">';
        newItem += '<span class="plus plus_bg" data-step="'+step+'" onclick="rrsClickMax(this);"></span>';
        newItem += '</div>';
        newItem += '<div class="quality-param-intro d_sec">';
        newItem += '<span class="minus minus_bg" data-step="'+step+'" onclick="rrsClickMin(this);"></span>';
        newItem += '<input type="text" name="param['+param+'][DUMP][MAX][]" value="' + init_max + '">';
        newItem += '<span class="plus plus_bg" data-step="'+step+'" onclick="rrsClickMax(this);"></span>';
        newItem += '</div>';
        newItem += '<div class="quality-param-intro percent_block">';
        newItem += '<span class="minus minus_bg" data-step=0.5 onclick="rrsClickMin(this);"></span>';
        newItem += '<input type="text" name="param['+param+'][DUMP][DISCOUNT][]" value="-1">';
        newItem += '<span class="plus plus_bg" data-step=0.5 onclick="rrsClickMax(this);"></span>';
        newItem += '<div class="prc_pic">%</div>';
        newItem += '<div class="min">сброс</div>';
        newItem += '</div>';
        newItem += '';
        newItem += '<div class="delete-dump-item' + (is_traight == true ? ' straight' : '') + '" onclick="rrsDeleteDumpItem(this);">Удалить</div>';
        newItem += '<div class="clear"></div>';
        if(is_traight == true)
        {
            newItem += '<input type="hidden" name="param['+param+'][DUMP][STRAIGHT]" value="Y" />';
        }
        newItem += '</div>';

        parent.append(newItem);
    }

    if (parseInt(items) == 5 || is_traight == true) {
        $(e).addClass('inactive');
    }
}

function rrsDeleteDumpItem(e) {
    var parent = $(e).parents('.quality-dump-table-intro');
    $(e).parents('.quality-dump-table-item').remove();

    var items = parent.find('.quality-dump-table-item').length;

    if (items < 6) {
        //parent.find('.add-dump-table').show();
        parent.find('.add-dump-item').removeClass('inactive');
    }
    if (items == 0) {
        parent.removeClass("active");
        parent.parents('.quality-dump-intro').find('.add-dump-table, .add_straight_or').removeClass('inactive');
    }
}

function rrsCheckSubmit()
{
    var input = $('.request-block-r').find('input:checkbox:checked');
    var checked = input.length;
    var checked_rules_flag = false;

    if(checkSelectedClientRights() == 'y'){//проверка доступности добавления запроса для агента покупателя
        checked_rules_flag = true;
    }

    if(checked > 0)
    {
        if(checked_rules_flag)
        {
            $('.submit-btn').removeClass('inactive');
            $('.request-block10').show();
        }
        else
        {
            $('.submit-btn').addClass('inactive');
        }
    }
    else
    {
        $('.submit-btn').addClass('inactive');
        $('.request-block10').hide();
    }
}

function checkSelectedClientRights(){
    var result = 'n';

    var selObj = $('select[name="client_id"]');
    var client_id = 0;

    if(selObj.length == 1){
        client_id = selObj.val();
        if(client_id != '' && client_id > 0){
            var clSelectedObj = $('form[name="iblock_add"] select[name="client_id"] option[value="' + client_id + '"]');
            if(clSelectedObj.length == 1){
                //проверка доступности добавления запроса для агента покупателя
                if(clSelectedObj.attr('data-right') != 'undefined'){
                    result = clSelectedObj.attr('data-right');
                }
            }
        }
    }

    return result;
}

//check if quality params (min, max, etc) are correct & change to correct values
function checkParamsCorrect(wObj, mode)
{
    var val = wObj.val().replace('+', '');

    var wParent = wObj.parents('.sub_row.txt');
    var check_name = wObj.attr('name').toString();
    var input_val = parseFloat(val).toFixed(1);

    //check if input value is float or if value is negative, otherwise return saved correct value
    if((!checkCorrectFloat(val) || parseFloat(val) < 0 && wObj.parents('.quality-param-intro.percent_block').length == 0) && checkCorrectFloat(wObj.attr('data-tempval')))
    {
        wObj.val(wObj.attr('data-tempval'));
        return;
    }

    //check where were changes
    if(check_name.length != check_name.replace('BASE', '').length)
    {//base parameter was changed
        //remove dump value
        wParent.find('.quality-dump-table-item').remove();
        wParent.find('.quality-dump-table-intro').removeClass('active');
        wParent.find('.add-dump-table, .add_straight_or').removeClass('inactive');

        //change min & max values
        var minObj = wParent.find('input[name*="MIN"]');
        if(minObj.length == 1 && parseFloat(minObj.val()) > input_val)
        {//if base val < min -> change min
            minObj.val(input_val);
        }
        var maxObj = wParent.find('input[name*="MAX"]');
        if(maxObj.length == 1 && parseFloat(maxObj.val()) < input_val)
        {//if base val > max -> change max
            maxObj.val(input_val);
        }
    }
    else
    {
        if(check_name.length != check_name.replace('DUMP', '').length)
        {//dump parameter was changed
            var min_el = wParent.find('input[name*="MIN"]:first');
            var max_el = wParent.find('input[name*="MAX"]:first');

            var kb_check = true;
            if(mode == true && check_name.length != check_name.replace('MIN', '').length)
            {//keyboard input & input is dump min
                //check if input is in first & unused section
                kb_check = false;
                var min_check_val = parseFloat(min_el.val());
                if(min_check_val > input_val)
                {
                    kb_check = false;
                    input_val = min_check_val;
                    wObj.val(input_val);
                }
                else
                {
                    wObj.parents('.quality-dump-table-item').siblings('.quality-dump-table-item').each(function(temp_ind, temp_c_obj){
                        var temp_check = parseFloat($(temp_c_obj).find('input[type="text"][name*="MIN"]').val());
                        if(temp_check < input_val)
                        {//founded section lefter than input value
                            kb_check = true;
                        }
                    });
                }

                if(kb_check == false)
                {//new value is ok -> change this section right margin
                    var right_min_margins_list = wObj.parents('.quality-dump-table-item').siblings('.quality-dump-table-item');
                    if(right_min_margins_list.length > 0)
                    {
                        var temp_lowest_max_val = 1000000;
                        right_min_margins_list.each(function(temp_ind, temp_c_obj){
                            var temp_chk_val = parseFloat($(temp_c_obj).find('input[type="text"][name*="MIN"]').val());
                            if(temp_lowest_max_val > temp_chk_val)
                            {
                                temp_lowest_max_val = temp_chk_val;
                            }
                        });
                        if(temp_lowest_max_val < 1000000)
                        {
                            wObj.parents('.quality-dump-table-item').find('input[name*="MAX"]').val(temp_lowest_max_val);
                        }
                    }
                    else
                    {
                        kb_check = true;
                    }
                }
            }

            if(kb_check)
            {//interface input or keyboard input need check
                if(check_name.length != check_name.replace('MIN', '').length)
                {//dump min parameter was changed
                    var compare_max_val = -1000000; //get other dump max values to prevent intersections (highest of dump maximums that lower than check value)
                    var compare_max_val_higher = 1000000; //get other dump max values to prevent intersections (lowest of dump maximums that higher than check value)
                    var max_dump_el = wObj.parents('.quality-dump-table-item').find('input[name*="MAX"]');
                    var all_min_equal_check = 0;
                    var old_val = 0;
                    var step_val = parseFloat(wObj.parents('.quality-dump-table-intro').attr('data-step'));

                    //looking for larger of dump maximums that lower than check value
                    wObj.parents('.quality-dump-table-item').siblings('.quality-dump-table-item').each(function(temp_ind, temp_c_obj){
                        $(temp_c_obj).find('input[type="text"][name*="MAX"]').each(function(ind, cObj){
                            if(parseFloat($(cObj).val()) < input_val && compare_max_val < parseFloat($(cObj).val()) && Math.round(parseFloat($(cObj).val()) * 100) <= Math.round(parseFloat(max_dump_el.val()) * 100))
                            {
                                compare_max_val = parseFloat($(cObj).val());
                                all_min_equal_check++;
                            }
                            if(parseFloat($(cObj).val()) > input_val && compare_max_val_higher > parseFloat($(cObj).val()) && Math.round(parseFloat($(cObj).val()) * 100) < Math.round(parseFloat(max_dump_el.val()) * 100))
                            {
                                compare_max_val_higher = parseFloat($(cObj).val());
                            }
                        });

                        //check if all dump minimiums are same as dump maximums -> then set user's value (or min if user value is not correct)
                        if(all_min_equal_check == 1)
                        {//all maximums are equal -> check minimums
                            old_val = compare_max_val;
                            $(temp_c_obj).find('input[type="text"][name*="MIN"]').each(function(ind, cObj){
                                if(old_val != $(cObj).val())
                                {
                                    old_val = $(cObj).val();
                                    all_min_equal_check++;
                                    return;
                                }
                            });
                        }
                    });

                    if(all_min_equal_check == 1)
                    {//if all dump minimiums are same as dump maximums -> set user's value (if user value is correct)
                        if(input_val < parseFloat(min_el.val()))
                        {
                            wObj.val(min_el.val());
                        }
                        else if(input_val > parseFloat(max_el.val()))
                        {
                            wObj.val(max_el.val());
                        }
                        else if(parseFloat(max_dump_el.val()) < input_val)
                        {
                            wObj.val(max_dump_el.val());
                        }
                    }
                    else if(compare_max_val >= input_val)
                    {//reset value if intersects with other dumps
                        wObj.val(compare_max_val);
                    }
                    else if(min_el.length == 1 && input_val < parseFloat(min_el.val()))
                    {//compare value with min value
                        wObj.val(min_el.val());
                    }
                    else if(compare_max_val_higher != 1000000
                        && checkCorrectFloat(wObj.attr('data-tempval'))
                        && parseFloat(wObj.attr('data-tempval')) >= compare_max_val_higher
                        && input_val < compare_max_val_higher
                    )
                    {//check intersect with other section (new section contain other sections)
                        wObj.val(compare_max_val_higher);
                    }
                    else if(compare_max_val == -1000000 && compare_max_val_higher < 100000 && compare_max_val_higher > input_val
                        || compare_max_val_higher < 100000 && compare_max_val_higher == (input_val + step_val)
                        )
                    {
                        wObj.val(compare_max_val_higher);
                    }
                    else
                    {
                        if(parseFloat(max_dump_el.val()) < input_val)
                        {//compare with dump max parameter (check if dump min > dump max)
                            wObj.val(max_dump_el.val());
                        }
                        else if(input_val + step_val == max_dump_el.val())
                        {//need check next section (if exists that have same dump maximum and other dump minimum -> prevent intersection)
                            wObj.parents('.quality-dump-table-item').siblings('.quality-dump-table-item').each(function(temp_ind, temp_c_obj){
                                if(max_dump_el.val() == $(temp_c_obj).find('input[type="text"][name*="MAX"]').val()
                                    && max_dump_el.val() != $(temp_c_obj).find('input[type="text"][name*="MIN"]').val()
                                    )
                                {//found intersection
                                    wObj.val(max_dump_el.val());
                                    return;
                                }
                            });
                        }
                    }
                }
                else if(check_name.length != check_name.replace('MAX', '').length)
                {//dump max parameter was changed
                    var compare_min_val = 1000000;//get other dump min values to prevent intersections (lowest of dump minimums that higher than check value)
                    var compare_min_val_lower = -1000000;//get other dump min values to prevent intersections (highest of dump minimums that lower than check value)
                    var min_dump_el = wObj.parents('.quality-dump-table-item').find('input[name*="MIN"]');
                    var all_min_equal_check = 0;
                    var old_val = 0;
                    var step_val = parseFloat(wObj.parents('.quality-dump-table-intro').attr('data-step'));

                    //looking for lower of dump minimums that higher than check value
                    wObj.parents('.quality-dump-table-item').siblings('.quality-dump-table-item').each(function(temp_ind, temp_c_obj){
                        $(temp_c_obj).find('input[type="text"][name*="MIN"]').each(function(ind, cObj){
                            if(compare_min_val > parseFloat($(cObj).val()) && Math.round(parseFloat($(cObj).val()) * 100) > Math.round(parseFloat(min_dump_el.val()) * 100))
                            {
                                compare_min_val = $(cObj).val();
                                all_min_equal_check++;
                            }
                            if(parseFloat($(cObj).val()) < input_val && compare_min_val_lower < parseFloat($(cObj).val()) && Math.round(parseFloat($(cObj).val()) * 100) > Math.round(parseFloat(min_dump_el.val()) * 100))
                            {
                                compare_min_val_lower = parseFloat($(cObj).val());
                            }
                        });

                        //check if all dump minimiums are same as dump maximums -> then set user's value (if user value is correct)
                        if(all_min_equal_check == 1)
                        {//all minimiums are equal -> check maximums
                            old_val = compare_min_val;
                            $(temp_c_obj).find('input[type="text"][name*="MAX"]').each(function(ind, cObj){
                                if(old_val != $(cObj).val())
                                {
                                    old_val = $(cObj).val();
                                    all_min_equal_check++;
                                    return;
                                }
                            });
                        }
                    });

                    if(all_min_equal_check == 1)
                    {//if all dump minimiums are same as dump maximums -> set user's value (if user value is correct)
                        if(input_val < parseFloat(min_el.val()))
                        {
                            wObj.val(min_el.val());
                        }
                        else if(input_val > parseFloat(max_el.val()))
                        {
                            wObj.val(max_el.val());
                        }
                        else if(parseFloat(min_dump_el.val()) > input_val)
                        {
                            wObj.val(min_dump_el.val());
                        }
                    }
                    else if(parseFloat(compare_min_val) < input_val)
                    {//reset value if intersects with other dumps
                        wObj.val(compare_min_val);
                    }
                    else if(max_el.length == 1 && input_val > parseFloat(max_el.val()))
                    {//compare value with max value
                        wObj.val(max_el.val());
                    }
    //                else if(compare_min_val == 1000000 && compare_min_val_lower > -1000000 && compare_min_val_lower < input_val)
    //                {
    //                    console.log('1');
    //                }
    //                else if(compare_min_val_lower > -1000000 && compare_max_val_higher == (input_val - step_val))
    //                {
    //                    console.log('2');
    //                }
                    else
                    {//compare with dump min parameter (check if dump max < dump min)
                        if(parseFloat(min_dump_el.val()) > input_val)
                        {
                            wObj.val(min_dump_el.val());
                        }
                        else if(Math.round((input_val - step_val) * 100) == Math.round(parseFloat(min_dump_el.val()) * 100))
                        {//need check next section (if exists that have same dump maximum and other dump minimum -> prevent intersection)
                            wObj.parents('.quality-dump-table-item').siblings('.quality-dump-table-item').each(function(temp_ind, temp_c_obj){
                                if(min_dump_el.val() == $(temp_c_obj).find('input[type="text"][name*="MIN"]').val()
                                    && min_dump_el.val() != $(temp_c_obj).find('input[type="text"][name*="MAX"]').val()
                                    )
                                {//found intersection
                                    wObj.val(min_dump_el.val());
                                    return;
                                }
                            });
                        }
                    }
                }
                else if(check_name.length != check_name.replace('DISCOUNT', '').length)
                {//if percent value
                    if(input_val < -100)
                    {
                        wObj.val(-100);
                    }
                    else if(input_val > 100)
                    {
                        wObj.val(100);
                    }
                    else
                    {//check if input_val value is not 0.5
                        var temp_val = val.toString();
                        if(temp_val.length != temp_val.replace('.', '').length)
                        {//there is dot in float value -> check last value
                            var precision_val = temp_val.split('.');
                            var check_precision_int = parseInt(precision_val[1].substr(0, 1));
                            var plus_val = (check_precision_int < 8 ? 0 : (input_val < 0 ? -1 : 1));
                            var res_val = plus_val + parseInt(precision_val[0]);
                            if(check_precision_int > 2 && check_precision_int < 8)
                            {
                                res_val = (input_val < 0 && res_val == 0 ? '-' : '') + res_val + '.5';
                            }
                            input_val = res_val;
                            wObj.val(input_val);
                        }
                    }
                }
            }
        }
        else if(check_name.length != check_name.replace('MIN', '').length)
        {//min parameter was changed
            //compare value with base value
            var base_el = wParent.find('input[name*="BASE"]');
            if(base_el.length == 1 && input_val > parseFloat(base_el.val()))
            {//change if min < base
                wObj.val(base_el.val());
            }
            else
            {//change dump min values
                wParent.find('.quality-dump-table-item .quality-param-intro input[name*=MIN]').each(function(ind, cObj){
                    if(parseFloat($(cObj).val()) < input_val)
                    {
                        $(cObj).val(input_val);
                        //check dump max value (change if dump min > dump max)
                        var temp_dump_max = $(cObj).parents('.quality-dump-table-item').find('input[name*=MAX]');
                        if(temp_dump_max.length == 1 && input_val > parseFloat(temp_dump_max.val()))
                        {
                            temp_dump_max.val(input_val);
                        }
                    }
                });
            }
        }
        else if(check_name.length != check_name.replace('MAX', '').length)
        {//max parameter was changed
            //compare value with base value
            var base_el = wParent.find('input[name*="BASE"]');
            if(base_el.length == 1 && input_val < parseFloat(base_el.val()))
            {//change if max > base
                wObj.val(base_el.val());
            }
            else
            {//change dump max values
                wParent.find('.quality-dump-table-item .quality-param-intro input[name*=MAX]').each(function(ind, cObj){
                    if(parseFloat($(cObj).val()) > input_val)
                    {
                        $(cObj).val(input_val);
                        //check dump min value (change if dump max < dump min)
                        var temp_dump_min = $(cObj).parents('.quality-dump-table-item').find('input[name*=MIN]');
                        if(temp_dump_min.length == 1 && input_val < parseFloat(temp_dump_min.val()))
                        {
                            temp_dump_min.val(input_val);
                        }
                    }
                });
            }
        }
    }

    //correct result val if it have > 1 digits after dot
    var res_val = val.toString();
    var temp_val = res_val.split('.');
    if(typeof temp_val[1] != 'undefined' && temp_val[1].toString().length > 1)
    {
        wObj.val(parseFloat(val).toFixed(1));
    }
    else {
        wObj.val(parseFloat(val));
    }
    //set correct data as temporary
    wObj.attr('data-tempval', val);
}

function rrsClickMinPrice(e) {
    var strVal = $(e).siblings('input');
    var input = $(e).parents('.wh_price').find('.cost-item input');
    var val = parseFloat(input.val());
    var step = parseFloat($(e).data('step'));
    var min = parseFloat($(e).data('min'));

    if (!step)
        step = 50;

    val = val - step;

    if (val < min) {
        val = min;
    }

    val = (Math.abs(val - Math.round(val)) < 0.01 ? Math.round(val) : val.toFixed(1));
    input.val(val);
    strVal.val(number_format(val, 0, '.', ' '));
}

function rrsClickMaxPrice(e) {
    var strVal = $(e).siblings('input');
    var input = $(e).parents('.wh_price').find('.cost-item input');
    var val = parseFloat(input.val());
    var step = parseFloat($(e).data('step'));
    var max = parseFloat($(e).data('max'));

    if (!step)
        step = 50;

    val = val + step;

    if (val > max) {
        val = max;
    }

    val = (Math.abs(val - Math.round(val)) < 0.01 ? Math.round(val) : val.toFixed(1));
    input.val(val);
    strVal.val(number_format(val, 0, '.', ' '));
}
