var stop_list_slideanimation = 0;
var parnter_unlink_decision_status = '';

$(document).ready(function(){

    $('input[name=verified_btn]').on('click',function () {
        var obj = $(this);
        if(obj.closest('form').find('input[name=ch_verified]:checked').val() == 'yes'){
            $.ajax({
                type: "POST",
                url: "/ajax/changePartnerLinkVerified.php",
                data: "link_id="+obj.attr('data-id')+"&val=yes&user=client",
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
        if(stop_list_slideanimation == 0 && !$(e.target).hasClass('unlink_but') && $(e.target).prop("tagName") != 'A' && !$(this).parents('.line_area').hasClass('not_activated'))
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
            alert('У пользователя есть незавершенные сделки или активные запросы. Отвязаться от пользователя можно только при отсутствии открытых сделок и активных запросов.');
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



    //сброс до значения по умолчанию без использования формы
    $('form[name="agent_select"]').on('submit', function(e){
        //check if all inputs are filled at docs form
        var select_obj = $(this).find('select[name="agent_id"]');
        if(select_obj.length == 1 && $(this).find('select[name="agent_id"]').val() == 0)
        {
            e.preventDefault();
            document.location.href = document.location.href.toString().replace(/\?.*/, '');
        }
    });

    //прикрепление/открепление агента и обновление его прав
    $('input.submit-btn[data-action="save_agent"], .agent_unlink_but').on('click', function(){

        if(!$(this).hasClass('disabled')){
            var client_id = $(this).parents('form.line_additional').find('input[type="hidden"][name="uid"]').val();
            var agent_id = $(this).parents('.line_drawn').find('select[name="agent_id"]').val();
            var controls_id = $(this).parents('.line_drawn').find('input[name="control_agent"]:checked').val();

            var only_contols_update = false;

            if($(this).hasClass('agent_unlink_but')){
                agent_id = -1;
            }
            else if(agent_id == 0){
                var check_agent = $(this).parents('.line_drawn').find('.agent_data');
                if(check_agent.hasClass('active')
                    && typeof check_agent.attr('data-uid') != 'undefined'
                    && check_agent.attr('data-uid') != ''
                    ){
                    agent_id = check_agent.attr('data-uid');
                    only_contols_update = true;
                }
            }

            var wObj = $(this);
            var selectContainerObj = $(this).parents('form.line_additional').find('.agent_select');
            var dataContainerObj = $(this).parents('form.line_additional').find('.agent_data');
            //var buttonContainerObj = $(this).parents('form.line_additional').find('.submit-btn[data-action="save_agent"]');

            if(agent_id !== '' && agent_id != 0){
                wObj.addClass('disabled');
                $.post('/ajax/changeClientToAgentLink.php', {
                    'update_agent_settings': 'y',
                    'client_id': client_id,
                    'agent_id': agent_id,
                    'control_id': controls_id
                }, function(mes){
                    wObj.removeClass('disabled');
                    if(mes == 1){
                        selectContainerObj.removeClass('active');
                        dataContainerObj.addClass('active').attr('data-uid', agent_id).find('.agent_name').text(selectContainerObj.find('option[value="' + agent_id + '"]').text());
                        if(only_contols_update)
                        {
                            alert('Настройки сохранены');
                        }
                    }
                    else if(mes == 2){
                        selectContainerObj.addClass('active');
                        dataContainerObj.removeAttr('data-uid').removeClass('active').find('.agent_name').text('');
                    }
                    selectContainerObj.find('select[name="agent_id"]').val('0').trigger('change');
                });
            }
        }
    });
});

function goTriggerChange(cObj)
{
    $(cObj).trigger('change');
}