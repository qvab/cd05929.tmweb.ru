$(document).ready(function() {
    $('.submit-btn').on('click', function(e){
        var form = $(this).parents('form.offer_add');

        var found_err = false;
        var err_val = '';
        var err_scroll_top = 0;
        var temp_val = '';

        //check form fields
        //check warehouse is set
        var checkObj = $('select[name="warehouse"], input[name="warehouse"]');
        if(checkObj.length == 1){
            temp_val = checkObj.val();
        }

        if(temp_val == ''){
            err_val = 'Не выбран склад';
        }

        if(err_val != ''){
            found_err = true;
            if(checkObj.length == 1){
                err_scroll_top = checkObj.offset().top - 100;
                if(checkObj.parents('.row').find('.row_err').length == 1){
                    checkObj.parents('.row').addClass('error').find('.row_err').text(err_val);
                }
                else{
                    checkObj.parents('.row').find('.row_val').append('<div class="row_err"></div>');
                    checkObj.parents('.row').addClass('error').find('.row_err').text(err_val);
                }
            }
            else if(checkObj.length == 0 && $('.for_warehouse_list_val').length == 1){
                checkObj = $('.for_warehouse_list_val');
                err_scroll_top = checkObj.offset().top - 100;
                if(checkObj.parents('.row').find('.row_err').length == 1){
                    checkObj.parents('.row').addClass('error').find('.row_err').text(err_val);
                }
                else{
                    checkObj.parents('.row').find('.row_val').append('<div class="row_err"></div>');
                    checkObj.parents('.row').addClass('error').find('.row_err').text(err_val);
                }
            }
        }
        err_val = '';

        //check delivery select parameter
        checkObj = $('select[name="farmer_filter"]');
        if(checkObj.length == 1)
        {
            temp_val = checkObj.val();
            if(temp_val == 0)
            {
                err_val = 'Пожалуйста выберите агропроизводителя';
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
                    checkObj.parents('.row').find('.row_val:not(.label)').append('<div class="row_err"></div>');
                    checkObj.parents('.row').addClass('error').find('.row_err').text(err_val);
                }
            }
            err_val = '';
        }

        //check delivery select parameter
        checkObj = $('select[name="delivery"]');
        if(checkObj.length == 1)
        {
            temp_val = checkObj.val();
            if(temp_val == '')
            {
                err_val = 'Пожалуйста выберите одно из значений';
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
            setTimeout(function(){
                form.submit();
            }, 300);

        }
        else if(found_err)
        {
            window.scrollTo(0, err_scroll_top);
        }
    });

    var offerFormObj = $('.offer_add');

    offerFormObj.on('change', 'select[name="farmer_filter"], select[name="warehouse"], select[name="delivery"]', function(){
        //remove error hint
        var row_obj = $(this).parents('.row.error');
        if(row_obj.length == 1) { row_obj.removeClass('error'); }

        if($(this).attr('name') == 'warehouse' || $(this).attr('name') == 'warehouse'){
            offerFormObj.find('#agreement').trigger('change');
        }

        //проверяем ограничение сущностей
        if($(this).attr('name') == 'farmer_filter'){
            var optionObj = $(this).find('option[value="' + $(this).val() + '"]')
            if($(this).val() > 0
                &&
                (
                    optionObj.length == 0
                    || typeof optionObj.attr('data-limit') == 'undefined'
                    || optionObj.attr('data-limit') == '0'
                )
            ){
                $('.form_line_error.limit').addClass('active');
                offerFormObj.find('input[type="button"][name="iblock_submit"]').addClass('inactive');
            }
            else{
                $('.form_line_error.limit').removeClass('active');
            }
        }
    });

    //change culture
    offerFormObj.on('change', 'input[name="csort"]', function(e){
        var csort = $(this).val();

        if (csort) {
            $.ajax({
                type: "POST",
                url: "/ajax/changeCultureOffer.php",
                data: "csort="+csort,
                success: function(msg){
                    $('.request-block3').html(msg);
                    $('.request-blocks').show();
                    if ($('.request-block4 .row_val').data('value') == 'Y'
                        && $('.form_line_error.limit.active').length == 0
                    ) {
                        $('.submit-btn').removeClass('inactive');
                    }
                    makeCustomForms();
                }
            });
        }

        e.preventDefault();
    });

    //change culture group
    offerFormObj.on('change', 'input[name="cgroup"]', function(e){
        var cgroup = $(this).val();

        $('.request-block3').html('');
        $('.request-blocks').hide();
        $('.submit-btn').addClass('inactive');

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

    //submit check
    offerFormObj.on('change', '#agreement', function(){
        if($(this).prop('checked') === true)
        {
            if ($('.request-block4 .for_warehouse_list_val').attr('data-value') == 'Y'
                && $('.form_line_error.limit.active').length == 0
            ) {
                $(this).parents('.offer_add').find('.submit-btn').removeClass('inactive');
            }
        }
        else
        {
            $(this).parents('.offer_add').find('.submit-btn').addClass('inactive');
        }
    });

    //check quality change value is correct (temporary save current value)
    offerFormObj.on('focus', '.quality-param-intro input[type="text"]', function(){
        if(checkCorrectFloat($(this).val()))
        {
            $(this).attr('data-tempval', $(this).val());
        }
    });

    offerFormObj.on('change', '.sub_row.txt input[type="text"]', function(e){
        checkOfferParamsCorrect($(this));
    });

    //выбор склада
    $('select[name="farmer_filter"]').on('change', function(){
        if($(this).val() != 0)
        {
            var warehouse_elements = $('.warehouses_fake_list .element[data-uid="' + $(this).val() + '"]');
            if(warehouse_elements.length > 1)
            {
                $('.for_warehouse_list').text($('.for_warehouse_list').attr('data-u3'));
                var select_html = '<select ' + (warehouse_elements.length > 4 ? 'data-search="y"' : '') +' name="warehouse">';
                select_html += '<option value="">Выберите склад отгрузки</option>';
                warehouse_elements.each(function(ind, cObj){
                    select_html += '<option value="' + $(cObj).attr('data-eid') + '">' + $(cObj).attr('data-name') + '</option>';
                });
                select_html += '</select>';
                $('.for_warehouse_list_val').attr('data-value', 'Y').html(select_html);
                makeCustomForms();
            }
            else if(warehouse_elements.length == 1)
            {
                $('.for_warehouse_list').text($('.for_warehouse_list').attr('data-u1'));
                $('.for_warehouse_list_val').attr('data-value', 'Y').html('<input type="hidden" name="warehouse" value="' + warehouse_elements.attr('data-eid') + '" />' +
                    '<input type="text" class="disabled" readonly="readonly" value="' + warehouse_elements.attr('data-name') +'" />');
            }
            else
            {
                $('.for_warehouse_list').text($('.for_warehouse_list').attr('data-u1'));
                $('.for_warehouse_list_val').attr('data-value', 'N').text('Нет складов для отгрузки');
            }
        }
        else
        {
            $('.for_warehouse_list').text($('.for_warehouse_list').attr('data-u1'));
            $('.for_warehouse_list_val').attr('data-value', 'N').text('Нет складов для отгрузки (выберите агропроизводителя)');
        }
    });
    //обработка данных склада при копировании предложения
    if($('select[name="farmer_filter"]').val() > 0){
        $('select[name="farmer_filter"]').trigger('change');
        if($('select[name="warehouse"]').length == 1
            && typeof $('.warehouses_fake_list').attr('data-selected') != 'undefined'
            && $('.warehouses_fake_list').attr('data-selected') != ''
            && $('.warehouses_fake_list').attr('data-selected').toString().replace(/[0-9]+/, '') == ''
        ){
            $('select[name="warehouse"]').val($('.warehouses_fake_list').attr('data-selected')).trigger('change');
        }
    }
});

function rrsClickMin(e) {
    var input = $(e).siblings('input');
    var val = parseFloat(input.val());
    var step = parseFloat($(e).data('step'));

    if (!step)
        step = 0.1;

    val = val - step;
    input.val((Math.abs(val - Math.round(val)) < 0.01 ? Math.round(val) : val.toFixed(1)));
    $(e).siblings('input[type="text"]').trigger('change');
}

function rrsClickMax(e) {
    var input = $(e).siblings('input');
    var val = parseFloat(input.val());
    var step = parseFloat($(e).data('step'));

    if (!step)
        step = 0.1;

    val = val + step;
    input.val((Math.abs(val - Math.round(val)) < 0.01 ? Math.round(val) : val.toFixed(1)));
    $(e).siblings('input[type="text"]').trigger('change');
}

function checkOfferParamsCorrect(argObj)
{
    var temp_val = argObj.val();

    //if not float or if negative return saved value
    if((!checkCorrectFloat(temp_val) || parseFloat(temp_val) < 0) && checkCorrectFloat(argObj.attr('data-tempval')))
    {
        argObj.val(argObj.attr('data-tempval'));
        return;
    }

    //set correct data as temporary
    argObj.attr('data-tempval', argObj.val());
}