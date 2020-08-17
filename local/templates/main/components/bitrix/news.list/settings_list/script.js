var stop_slide_anim = 0;

$(document).ready(function() {
    $('.list_page_rows .line_area .line_inner, .list_page_rows .line_area .line_additional .hide_but').on('click', function(){
        if(stop_slide_anim == 0)
        {
            stop_slide_anim = 1;
            var wObj = $(this).parents('.line_area');
            wObj.toggleClass('active');
            if(!wObj.hasClass('active'))
            {
                wObj.find('form.line_additional').slideUp(300, function(){
                    stop_slide_anim = 0;
                });
            }
            else
            {
                wObj.find('form.line_additional').slideDown(300, function(){
                    stop_slide_anim = 0;
                });
            }
        }
    });
});
