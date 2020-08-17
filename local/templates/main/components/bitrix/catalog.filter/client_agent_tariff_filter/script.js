$(document).ready(function(){
    $('.client_change select').on('change', function(e){
        var new_href = $(this).parents('form').attr('action');
        var additional_href = '';
        var client_select_obj = $(this);
        if (client_select_obj.val() > 0) {
            additional_href += 'client=' + client_select_obj.val();
        }
        if (additional_href != '') {
            new_href += '?' + additional_href;
        }

        document.location.href = new_href;

        e.preventDefault();
    });
});