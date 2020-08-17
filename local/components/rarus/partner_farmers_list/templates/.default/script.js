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
                wObj.find('.arw_list').removeClass('arw_icon_open').addClass('arw_icon_close');
            }
            else
            {
                wObj.find('form.line_additional').slideDown(300, function(){
                    stop_slide_anim = 0;
                });
                wObj.find('.arw_list').removeClass('arw_icon_close').addClass('arw_icon_open');
            }
        }
    });
});

/* отвязаться от пользователя */
function unlinkPartner(uid){
    $.post('/ajax/changePartnerLinkToUser.php', {
        uid: uid,
        type: 'f',
        mode: 'unlink'
    }, function (mes) {
        if(mes == 1){
            $('.list_page_rows .line_area[data-uid="' + uid + '"]').removeClass('is_linked').addClass('not_linked');
        }
    });
}

/* отвязаться от пользователя */
function linkPartner(uid){
    $.post('/ajax/changePartnerLinkToUser.php', {
        uid: uid,
        type: 'f',
        mode: 'link'
    }, function (mes) {
        if(mes == 1){
            $('.list_page_rows .line_area[data-uid="' + uid + '"]').removeClass('not_linked').addClass('is_linked');
        }
    });
}

/* отвязаться от пользователя */
function inviteByPartner(uid){
    $.post('/ajax/getUserInviteByPartner.php', {
        uid: uid,
        type: 'f'
    }, function (mes) {
        var wObjArea = $('.list_page_rows .line_area[data-uid="' + uid + '"]');
        if(wObjArea.length == 1){
            var wObj = wObjArea.find('.invite_href');
            if(wObj.length == 0) {
                wObjArea.find('.additional_submits a[data-val="make_invite"]').after('<div class="invite_href">' + mes + '</div>');
            }else{
                wObj.text(mes);
            }
        }
    });
}