$(document).ready(function() {
    $('.submit-btn').on('click', function(e){
        var form = $(this).parents('form.offer_add');

        var found_err = false;
        var err_val = '';
        var err_scroll_top = 0;
        var temp_val = '';

        //check form fields
        //check warehouse is set
        var checkObj = $('select[name="warehouse"]');
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

    offerFormObj.on('change', 'select[name="warehouse"], select[name="delivery"]', function(){
        //remove error hint
        var row_obj = $(this).parents('.row.error');
        if(row_obj.length == 1) { row_obj.removeClass('error'); }
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
                        && $('.form_line_error.limits').length == 0
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
            if ($('.request-block4 .row_val').data('value') == 'Y'
                && $('.form_line_error.limits').length == 0
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