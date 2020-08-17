function rrsClickMinPrice(e) {
    var strVal = $(e).siblings('input');
    var input = $(e).parents('.wh_price').find('input.tarif_val');
    var val = parseFloat(input.val());
    var step = parseFloat($(e).data('step'));
    var min = parseFloat($(e).data('min'));

    if (!step)
        step = 10;

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
    var input = $(e).parents('.wh_price').find('input.tarif_val');
    var val = parseFloat(input.val());
    var step = parseFloat($(e).data('step'));
    var max = parseFloat($(e).data('max'));

    if (!step)
        step = 10;

    val = val + step;

    if (val > max) {
        val = max;
    }

    val = (Math.abs(val - Math.round(val)) < 0.01 ? Math.round(val) : val.toFixed(1));
    input.val(val);
    strVal.val(number_format(val, 0, '.', ' '));
}

function changeVal() {
    if ((event.keyCode < 48 || event.keyCode > 57))
    {
        event.returnValue = false;
    }
}

function changeAfter(e) {
    var max = parseFloat($(e).parents('.wh_price').find('.plus').data('max'));
    var min = parseFloat($(e).parents('.wh_price').find('.minus').data('min'));
    var val = parseFloat($(e).val().replace(/\s/g, ''));
    var input = $(e).parents('.wh_price').find('input.tarif_val');

    if (val > max) {
        val = max;
    }

    if (val < min) {
        val = min;
    }

    val = (Math.abs(val - Math.round(val)) < 0.01 ? Math.round(val) : val.toFixed(1));

    input.val(val);
    $(e).val(number_format(val, 0, '.', ' '));
}
$(function () {
    $(document).on('scroll', scrollHead); 
});
function scrollHead() {
    var $wrap    = $('.group_item'),
        $headStucked    = $('.stuck-head'),
        wrapPositionTop = ($wrap.length > 0 ? $wrap.position().top : 0);
    if($(window).width() > 680) {
        $headStucked.css({
            'width' : $wrap.outerWidth(true) * 2,
        });
        if((wrapPositionTop) <= ($(this).scrollTop())){
            $headStucked.show();

        }
        else {
            $headStucked.hide();
;
        }
    }
}