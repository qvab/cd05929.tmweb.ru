$(document).ready(function(){
    //$('.select_item select').on('change', function(){
    $('form[name="deals_filter"]').on('submit', function(e){
        var link_href = document.location.href.toString();
        var new_href = link_href.replace(/\?.*/g, '');
        var additional_href = '';
        var client_select_obj = $(this).find('select[name="client_id[]"]');

        if(link_href.length != link_href.replace('status=all', '').length){
            additional_href = 'status=all';
        }
        else if(link_href.length != link_href.replace('status=close', '').length){
            additional_href = 'status=close';
        }
        else if(link_href.length != link_href.replace('status=cancel', '').length){
            additional_href = 'status=cancel';
        }

        if(client_select_obj.val() > 0){
            additional_href += (additional_href == '' ? '' : '&') + 'client_id[]=' + client_select_obj.val();
        }

        if(additional_href != ''){
            new_href += '?' + additional_href;
        }

        document.location.href = new_href;

        e.preventDefault();
    });
});