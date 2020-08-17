$(document).ready(function(){
    $('#history_filter .wrap-btn .reset').click(function () {
        var cur_href = document.location.href.toString().replace(/\?.*/g, '');
        document.location.href = cur_href;
    });
});