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
});