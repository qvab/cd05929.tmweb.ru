<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();?>
<?
if ($_REQUEST['request'] > 0) {
    //деактивация запроса
    client::deactivateRequestByID($_REQUEST['request']);

    LocalRedirect($APPLICATION->GetCurPageParam(null, ['q', 'best_price',]));
}

//редирект на неактивные
if(isset($_GET['request_id'])
    && $_GET['request_id'] > 0
    && is_numeric($_GET['request_id'])
    && (!in_array($_GET['request_id'], $arResult['ELEMENTS'])
        || (!isset($_REQUEST['client_id']))
        || (!isset($_REQUEST['culture']))
    )
) {
    //проверка, если пришли со страницы графика
    if(isset($_GET['from_graph'])
        && $_GET['from_graph'] == 'y'
    ){
        //получаем культуру и покупателя запроса
        $reqParams = client::getRequestById($_GET['request_id']);
        $GLOBALS['arrFilter']['PROPERTY_CULTURE'] = $reqParams['CULTURE_ID'];
        $GLOBALS['arrFilter']['PROPERTY_CLIENT'] = $reqParams['CLIENT_ID'];
    }

    //получение страницы переадресации (проверка)
    $new_url = client::getRequestListRedirectById($_GET['request_id'], $arParams['NEWS_COUNT'], (isset($_GET['PAGEN_1']) ? $_GET['PAGEN_1'] : 1));

    if($new_url != '') {
        LocalRedirect($new_url);
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

//проверяем данные ограничений пользователей
$agentObj = new agent();
$uids = $agentObj->getClientsForSelect($arParams['UID']);
if(count($uids) > 0){
    $agent_obj = new agent();
    $agent_req_limits = $agent_obj->checkAvailableRequestLimit(array_keys($uids));

    if($agent_req_limits['REMAINS'] > 0){
        //активируем кнопки добавления
        ?>
        <script type="text/javascript">
            $(document).ready(function(){
                $('.additional_href_data, .add_blue_button').addClass('active');
            });
        </script>
        <?
    }else{
        //показываем сообщение, что нельзя добавлять запросы
        ?>
        <script type="text/javascript">
            $(document).ready(function(){
                $('.add_limit_end').addClass('active');
            });
        </script>
        <?
    }
}