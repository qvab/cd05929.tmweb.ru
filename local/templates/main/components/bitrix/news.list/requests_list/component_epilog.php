<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();?>

<?
global $USER;

if ($_REQUEST['request'] > 0) {
    //деактивация запроса
    client::deactivateRequestByID($_REQUEST['request']);

    LocalRedirect($APPLICATION->GetCurPageParam(null, ['q', 'best_price',]));
}

//редирект на неактивные
if(isset($_GET['request_id'])
    && $_GET['request_id'] > 0
    && is_numeric($_GET['request_id'])
    && !in_array($_GET['request_id'], $arResult['ELEMENTS'])
    && (
        (!isset($_REQUEST['warehouse_id']))
        || (!isset($_REQUEST['culture_id']))
    )
) {
    //проверка, если пришли со страницы графика
    if(isset($_GET['from_graph'])
        && $_GET['from_graph'] == 'y'
    ){
        //получаем культуру и склад запроса
        $reqParams = client::getRequestById($_GET['request_id']);
        $reqCost = reset($reqParams['COST']);
        $GLOBALS['arrFilter']['PROPERTY_CULTURE'] = $reqParams['CULTURE_ID'];
        $GLOBALS['arrFilter']['PROPERTY_WAREHOUSE'] = $reqCost['WH_ID'];
    }

    //получение страницы переадресации (проверка)
    $new_url = client::getRequestListRedirectById($_GET['request_id'], $arParams['NEWS_COUNT'], (isset($_GET['PAGEN_1']) ? $_GET['PAGEN_1'] : 1));
    if($new_url != '') {
        LocalRedirect($new_url);
        exit;
    }

    //редирект на неактивные
    if(!isset($_GET['status'])
        || $_GET['status'] != 'no'
    ){
        LocalRedirect('/client/request/?status=no&request_id=' . $_GET['request_id']);
        exit;
    }
}
if(!empty($_GET['request_id'])
    && in_array($_GET['request_id'], $arResult['ELEMENTS'])
){
    ?><script type="text/javascript">
        $(document).ready(function(){
            var reqInptObj = $('input[type=hidden][value="<?=$_GET['request_id'];?>"]');
            if(reqInptObj.length == 1){
                var reqObj = reqInptObj.parents('.line_area');
                reqObj.find('.line_inner').trigger('click');
                setTimeout(function(){
                    var offsetObj = reqObj.offset();
                    $(document).scrollTop(offsetObj.top - 30);
                }, 500);
            }
        });
    </script><?
}

//проверка запрещения пролонгирования при неактивных складах/стоимостях
if(isset($arResult['CHECK_PROLONG_ITEMS']) && count($arResult['CHECK_PROLONG_ITEMS']) > 0) {
    $arResult['NO_PROLONG_ITEMS'] = client::checkRequestDeactivatedWH($arResult['CHECK_PROLONG_ITEMS']);
    if(count($arResult['NO_PROLONG_ITEMS']) > 0){
        ?>
        <script type="text/javascript">
            var no_prolong_ids = [<?=implode(',', array_keys($arResult['NO_PROLONG_ITEMS']))?>];
            var wInp, wForm, wButton;
            for(var i = 0; i < no_prolong_ids.length; i++){
                wInp = $('form.line_additional input[type="hidden"][name="request"][value="' + no_prolong_ids[i] + '"]');
                if(wInp.length == 1){
                    wForm = wInp.parents('form.line_additional');
                    if(wForm.length == 1){
                        wButton = wForm.find('.submit-btn.req_prolongation').remove();
                    }
                }
            }
        </script>
        <?
    }
}

//проверка наличия ограничения по созданию запроса
$req_limit = client::checkAvailableRequestLimit($USER->GetID());
//получаем email пользователя
$email_val = $USER->GetEmail();
//если email из телефона, то не отображаем его
if(checkEmailFromPhone($email_val)){
    $email_val = '';
}
if($req_limit['REMAINS'] > 0){
    //активируем кнопки по созданию запроса и также отображаем данные ограничения
    ?>
    <script type="text/javascript">
        $(document).ready(function(){
            $('.additional_href_data, .add_blue_button').addClass('active');
            var limObj = $('.add_limit_line.available');
            limObj.addClass('active').find('.val').text('<?=$req_limit['CNT'];?>');
            limObj.find('.remains').text('<?=$req_limit['REMAINS'];?>');
            limObj.find('a:first').on('click', function(){
                showRequestLimitsFeedbackForm('<?=$email_val;?>', '<?=rrsIblock::getConst('min_request_limit_form');?>', '<?=rrsIblock::getConst('request_limit_price');?>');
            });
        });
    </script>
    <?
}else{
    //показываем сообщение об исчерпании лимита
    ?>
    <script type="text/javascript">
        $(document).ready(function(){
            var limObj = $('.add_limit_line.ended');
            limObj.addClass('active').find('.val').text('<?=$req_limit['CNT'];?>');
            limObj.find('a:first').on('click', function(){
                showRequestLimitsFeedbackForm('<?=$email_val;?>', '<?=rrsIblock::getConst('min_request_limit_form');?>', '<?=rrsIblock::getConst('request_limit_price');?>');
            });
        });
    </script>
    <?
}
?>