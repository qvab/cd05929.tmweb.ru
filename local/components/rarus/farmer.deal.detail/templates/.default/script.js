var stop_slide_anim = 0;
$(document).ready(function() {
    $('.list_page_rows .line_area.deal_done .line_inner, .list_page_rows .line_area.current .line_inner').on('click', function(){
        if(stop_slide_anim == 0)
        {
            stop_slide_anim = 1;
            var wObj = $(this).parents('.line_area');
            wObj.toggleClass('active');
            if(!wObj.hasClass('active'))
            {
                wObj.find('.line_additional').slideUp(300, function(){
                    stop_slide_anim = 0;
                });
            }
            else
            {
                wObj.find('.line_additional').slideDown(300, function(){
                    stop_slide_anim = 0;
                });
            }
        }
    });

    $('.quality-param-intro').on('keyup', 'input[name="tarif"]', function(e){
        checkTarif($(this), 'pos_int');
    });

    $('body').on('click', '.loader_click', function(){
        $('#'+$(this).data('for')).click();
    });

    $('.document').on('change', function (e) {
        $(this).prev().html(e.target.files[0].name).addClass('load-file');
    });

    //звезды рейтинга
    $rate_obj = $('div.rate_total .rate_val');
    $rate_obj.rating({
        fx: 'float',
        readOnly: true
    });
});

function rrsClickMin(e) {
    var input = $(e).siblings('input');
    var val = parseFloat(input.val());
    var step = parseFloat($(e).data('step'));
    var min = parseFloat($(e).data('min'));

    if (!step)
        step = 50;

    val = val - step;

    if (val < 50)
        val = 50;

    if (val < $(e).data('min')) {
        val = min;
    }
    val = (Math.abs(val - Math.round(val)) < 0.01 ? Math.round(val) : val.toFixed(1));
    //input.val((Math.abs(val - Math.round(val)) < 0.01 ? Math.round(val) : val.toFixed(1)));
    input.val(val);

    var price = $(e).parents('form').find('input[name="acc_price"]').val();
    price = price - val;
    if (price < 0) price = 0;
    $('.csm_price').find('span').html(number_format(price, 2, '.', '&nbsp;')+'&nbsp;руб/т');
}

function rrsClickMax(e) {
    var input = $(e).siblings('input');
    var val = parseFloat(input.val());
    var step = parseFloat($(e).data('step'));

    if (!step)
        step = 50;

    val = val + step;
    val = (Math.abs(val - Math.round(val)) < 0.01 ? Math.round(val) : val.toFixed(1));
    //input.val((Math.abs(val - Math.round(val)) < 0.01 ? Math.round(val) : val.toFixed(1)));
    input.val(val);

    var price = $(e).parents('form').find('input[name="acc_price"]').val();
    price = price - val;
    if (price < 0) price = 0;
    $('.csm_price').find('span').html(number_format(price, 2, '.', '&nbsp;')+'&nbsp;руб/т');
}

function checkTarif(argObj, maskType)
{
    var new_val = argObj.val();
    //var new_val = '50';
    switch(maskType)
    {
        case 'pos_int': //positive integer value
            if(!checkCorrectInt(new_val))
            {
                new_val = '50';
            }
            else if(new_val < 0)
            {
                new_val = -1 * new_val;
            }
            break;
    }

    argObj.val(new_val);
}