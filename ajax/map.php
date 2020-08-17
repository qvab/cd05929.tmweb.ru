<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
$from = $_GET['from'];
$to = $_GET['to'];

//p($from);
//p($to);

$from = '55.754660,37.619169';
$to = '55.788147159325,38.469558042328';

?>
<script type="application/javascript">
    $(document).ready(function() {
        //alert(<?=$from?>);
        //alert(<?=$to?>);
        ymaps.route([
                //'Кронштадт, Якорная площадь',
                /*{
                    type: 'viaPoint',
                    point: [59.93328,30.342791]
                },*/
                [<?=$from?>],
                [<?=$to?>]
            ]).then(
            function (route) {
                var routeLength = route.getLength();
                $('.route').html(routeLength);
            },
            function (error) {
                alert("Возникла ошибка: " + error.message);
            }
        );
    });
</script>
<div class="route"></div>