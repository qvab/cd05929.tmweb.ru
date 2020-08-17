$(document).ready(function() {

    $('div.rating').rating({
        fx: 'half',
        click: function(res){
            $(this).parents('.prop_area').find('input[type="hidden"]').val(2*res);
        }

    });

});
