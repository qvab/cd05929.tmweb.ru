var stop_list_slideanimation = 0;
var parnter_unlink_decision_status = '';

$(document).ready(function(){

    $('input[name=verified_btn]').on('click',function () {
        var obj = $(this);
        if(obj.closest('form').find('input[name=ch_verified]:checked').val() == 'yes'){
            $.ajax({
                type: "POST",
                url: "/ajax/changePartnerLinkVerified.php",
                data: "link_id="+obj.attr('data-id')+"&val=yes&user=transport",
                dataType:'JSON',
                success: function(msg){
                    if(msg.success == '1'){
                        obj.closest('.radio_group').remove();
                    }
                }
            });
        }
    });

    $('.connected_users_list .line_inner').on('click', function(e){
        if(stop_list_slideanimation == 0 && !$(e.target).hasClass('unlink_but') && !$(this).parents('.line_area').hasClass('not_activated'))
        {
            stop_list_slideanimation = 1;
            $(this).parents('.line_area').find('.line_additional').slideToggle(300, function(){
                stop_list_slideanimation = 0;
            });
        }
    });

    $('.connected_users_list .list_page_rows form.line_additional').on('submit', function(e){
        //check if all inputs are filled at docs form
        if($(this).find('input[type="submit"]').hasClass('inactive'))
        {
            e.preventDefault();
            return false;
        }
    });

    $('.connected_users_list .list_page_rows form.line_additional input[type="text"], .connected_users_list .list_page_rows form.line_additional input[type="file"]').on('change keyup', function(){
        var err_flag = false;
        var wObj = $(this).parents('form.line_additional');
        var checkList = ['doc_val', 'doc_num', 'doc_date'];
        var checkObj;
        for(var i = 0; i < checkList.length; i++)
        {
            checkObj = wObj.find('input[name="' + checkList[i] + '"]');
            if(checkObj.length == 0 || checkObj.val() == '')
            {
                err_flag = true;
                break;
            }
        }

        if(err_flag)
        {
            wObj.find('input[type="submit"]').addClass('inactive');
        }
        else
        {
            wObj.find('input[type="submit"]').removeClass('inactive');
        }
    });

    //unlink partner
    $('.connected_users_list .unlink_but').on('click', function(e){
        if($(this).hasClass('disabled'))
        {
            alert('У пользователя есть незавершенные сделки. Отвязаться от пользователя можно только при отсутствии открытых сделок.');
        }
        else
        {
            parnter_unlink_decision_status = confirm("Вы уверены, что хотите отвязаться от данного пользователя?");
            if(parnter_unlink_decision_status)
            {//go to unlink page
                document.location.href = document.location.href.toString().replace(/\?.*/g, '') + '?unlink_partner=' + $(this).attr('data-uid');
            }
        }
    });
});

function goTriggerChange(cObj)
{
    $(cObj).trigger('change');
}