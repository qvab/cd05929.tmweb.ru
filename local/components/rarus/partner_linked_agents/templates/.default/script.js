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
        parnter_unlink_decision_status = confirm("Вы уверены, что хотите удалить выбранного агента?");
        if(parnter_unlink_decision_status)
        {//go to unlink page
            document.location.href = document.location.href.toString().replace(/\?.*/g, '') + '?unlink_agent=' + $(this).attr('data-uid');
        }
    });

    //удаление непринятых приглашений и удаление пользователя
    $('.connected_users_list .unlink_but_del').on('click', function(e){
        parnter_unlink_del_decision_status = confirm("Вы уверены, что хотите удалить приглашение агента?");
        if(parnter_unlink_del_decision_status)
        {//go to unlink page
            document.location.href = document.location.href.toString().replace(/\?.*/g, '') + '?unlink_partner_del=' + $(this).attr('data-uid');
        }
    });

    /**
     * Процент вознаграждения
     */
    $('.list_page_rows .line_area .line_additional.reward-options input[name="REWARD"], .list_page_rows .line_area .line_additional.reward-options input[name="REWARD_TRANSPORTATION"]').keyup(function () {

        // Оставляем только числа и точки
        var percent = $(this).val();

        if(percent.length > 0) {
            percent = percent.replace(/\s/g, '');
            percent = percent.replace(/[^\d{}\.]/g, '');
        }

        $(this).val(percent);

        var parent = $(this).closest('.line_additional.reward-options');
        var agentId = parent.attr('agent-id');
        // Активируем кнопку "Сохранить"
        $('.list_page_rows .line_area .line_additional.reward-options[agent-id="'+agentId+'"] input.submit-btn').removeClass('inactive').addClass('active');

    }).change(function () {

        // Приводим к типу float
        var percent = $(this).val();
        if(percent.length > 0) {
            percent = parseFloat(percent).toFixed(2);
            if(!isNaN(percent)) {
                $(this).val(percent);
            }
        }

        var parent = $(this).closest('.line_additional.reward-options');
        var agentId = parent.attr('agent-id');

        // Активируем кнопку "Сохранить"
        $('.list_page_rows .line_area .line_additional.reward-options[agent-id="'+agentId+'"] input.submit-btn').removeClass('inactive').addClass('active');
    });


    /**
     * Сохраняем значение процента вознаграждения
     */
    $('.list_page_rows .line_area .line_additional.reward-options input.submit-btn').click(function () {

        if(!$(this).hasClass('active')) {
            return;
        }

        // Предохраняемся от дублей
        var btn = $(this);
        if(btn.data('save_options')) {
            return;
        }
        btn.data('save_options', true);

        try {

            // Параметры
            var parent = $(this).closest('.line_additional.reward-options');

            var blockError =  parent.find('.error_msg_save_options');
            blockError.text('').hide();

            var agentId = parent.attr('agent-id');
            if(!agentId) {
                throw new Error('Не удалось получить ИД агента');
            }

            var inputReward = parent.find('input[name="REWARD"]');
            var percent = inputReward.val();

            if(percent.length > 0) {
                percent.trim();
            }

            var inputRewardTransportation = parent.find('input[name="REWARD_TRANSPORTATION"]');
            var percentTransportation = inputRewardTransportation.val();

            if(percentTransportation.length > 0) {
                percentTransportation.trim();
            }

            // Аяксим
            $.ajax({
                url: '',
                data: {
                    AJAX: 'Y',
                    SAVE_OPTIONS: 'Y',
                    AGENT_ID: agentId,
                    PERCENT: percent,
                    PERCENT_TRANSPORTATION: percentTransportation,
                    sessid: BX.bitrix_sessid()
                },
                type: 'GET',
                dataType: "json",
                cache: false,
                success: function(json){
                    if(json) {

                        if(json.result) {
                            blink(inputReward);
                            blink(inputRewardTransportation);
                            btn.removeClass('active').addClass('inactive');
                        } else {

                            // Обработка ошибок
                            if(json.errorMsg) {
                                blockError.text(json.errorMsg).show();
                            }
                        }

                        setTimeout('stopBackLoad()', 300);
                        btn.data('save_options', false);
                    }
                },
                error: function(){
                    btn.data('save_options', false);
                    setTimeout('stopBackLoad()', 300);
                    alert('Ошибка запроса, при повторных ошибках сообщите администратору.');
                },
                beforeSend: function () {
                    startBackLoad();
                }
            });
        } catch (e) {

            var errorMsg = 'Ошибка ' + e.name + ":" + e.message;
            blockError.text(errorMsg).show();
            errorMsg += "\n" + e.stack;
            console.error(errorMsg);

            btn.data('save_options', false);
        }
    });
});

function goTriggerChange(cObj)
{
    $(cObj).trigger('change');
}

/**
 * Моргун элемента
 * @param selector
 */
function blink(selector){
    $(selector).fadeOut('slow', function(){
        $(this).fadeIn('slow');
    });
}