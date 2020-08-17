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

    $('body').on('click', '.loader_click', function(){
        $('#'+$(this).data('for')).click();
    });

    $('.document').on('change', function (e) {
        $(this).prev().html(e.target.files[0].name).addClass('load-file');
    });

    $('body').on('click', '.more-btn .submit-btn', function(){
        var line = $('.add-line').html();
        $(this).parents('.prop_area').find('.reestr-table').append(line);
    });

    $('body').on('click', '.reload', function(e){
        e.preventDefault();
        var id  = $(this).attr('href');

        if (document.location.href.indexOf("#") < 0) {
            window.location = window.location+id;
        }
        window.location.reload();
    });

    if (document.location.href.indexOf("#") > 0) {
        var href = document.location.href.split("#");
        var top = $('#'+href[1]).offset().top;
        $('body,html').animate({scrollTop: top}, 500);
    }

    $('.delete-vi-item').on('click', function(){
        var id = $(this).data('vi');
        var deal = $(this).parents('.reestr-table').data('deal');

        $(this).parents('.vi-item').remove();

        $.ajax({
            type: "POST",
            url: "/ajax/deleteViItem.php",
            data: "id="+id+"&deal="+deal,
            success: function(msg){
                var data = JSON.parse(msg);
                if (data.weight != '0') {
                    $('.summary').find('.weight-cell').html(data.weight);
                    $('.summary').find('.rc-cell').html(data.rc);
                    $('.summary').find('.cost-cell').html(data.cost);
                    $('.summary').find('.transport-cell').html(data.transport_cost);
                }
                else {
                    $('.summary, .edit-btn, .add-vi-title').remove();
                }
            }
        });

    });

    $('.edit-btn').on('click', function(){
        $('.summary').remove();
        $(this).remove();
        $('.vi-item.val').each(function(ind, cObj){
            var id = $(cObj).data('vi');
            $(cObj).find('.delete-vi-item').remove();

            var car = $(cObj).find('.car-cell').html();
            $(cObj).find('.car-cell').html('<input type="text" name="car['+id+']" class="car_number" value="'+car+'">');

            var weight = $(cObj).find('.weight-cell').html().replace(/\s/g, '');
            $(cObj).find('.weight-cell').html('<input type="text" name="weight['+id+']" value="'+weight+'">');

            var cost = $(cObj).find('.cost-cell').html().replace(/\s/g, '');
            $(cObj).find('.cost-cell').html('<input type="text" name="cost['+id+']" value="'+cost+'">');

            /*var dump = $(cObj).find('.dump-cell').html();
            $(cObj).find('.dump-cell').html('<input type="text" name="dump['+id+']" value="'+dump+'">');*/

            $(cObj).find('.rc-cell').html('');
            $(cObj).find('.dump-cell').html('');
            $(cObj).find('.transport-cell').html('');

            $(cObj).append('<input type="hidden" name="update[]" value="'+id+'">');
        });
    });

});

function in_array(value, array) {
    for(var i=0; i<array.length; i++){
        if(value == array[i]) return true;
    }
    return false;
}