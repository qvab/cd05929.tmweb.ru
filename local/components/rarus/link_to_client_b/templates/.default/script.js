$(document).ready(function(){
    var parnter_unlink_decision_status = '', parnter_link_decision_status = '';
    //unlink partner
    $('.current_partner_link_area .unlink_but').on('click', function(){
        if($(this).hasClass('disabled'))
        {
            alert('У вас есть незавершенные сделки. Отвязаться от организатора можно только при отсутствии открытых сделок.');
        }
        else
        {
            parnter_unlink_decision_status = confirm("Вы уверены, что хотите отвязаться от текущего организатора?");
            if(parnter_unlink_decision_status)
            {//go to unlink page
                document.location.href = '/personal/link_to_partner/?unlink_partner=y';
            }
        }
    });
    //link to partner
    $('.partner_list_area .link_but').on('click', function(){
        parnter_link_decision_status = confirm("Вы уверены, что хотите привязаться к текущему организатору?");
        if(parnter_link_decision_status)
        {
            var link_partner_id = $(this).attr('data-id');
            if(checkCorrectInt(link_partner_id))
            {//go to link page
                document.location.href = '/personal/link_to_partner/?link_to_partner=' + link_partner_id;
            }
        }
    });
});