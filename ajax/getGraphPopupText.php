<?php

if(!empty($_POST['culture'])
    && filter_var($_POST['culture'], FILTER_VALIDATE_INT)
    && !empty($_POST['wh'])
    && filter_var($_POST['wh'], FILTER_VALIDATE_INT)
) {

    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
    header("Cache-Control: no-cache, must-revalidate");
    header("Pragma: no-cache");
    header("content-type: application/x-javascript; charset=UTF-8");

    require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

    global $USER;
    if(!$USER->IsAuthorized()){
        echo 0; exit;
    }

    //получение данных для отображения текста для описания графика
    CModule::IncludeModule('iblock');
    $result_arr = array();
    $result_html = '';

    $el_obj = new CIBlockElement;
    $uid = 0;

    //получение названия склада и id покупателя
    $region_name = '';
    $res = $el_obj->GetList(
        array('ID' => 'ASC'),
        array(
            'IBLOCK_ID' => rrsIblock::getIBlockId('client_warehouse'),
            'ID' => $_POST['wh']
        ),
        false,
        array('nTopCount' => 1),
        array('NAME', 'PROPERTY_CLIENT', 'PROPERTY_REGION.NAME')
    );
    if ($data = $res->Fetch()) {
        $region_name = $data['PROPERTY_REGION_NAME'];
        $uid = $data['PROPERTY_CLIENT_VALUE'];
    }

    //получение названия культуры
    if ($region_name != ''
        && !empty($uid)
    ) {
        $culture_name = culture::getName($_POST['culture']);
        if (!empty($culture_name['NAME'])) {

            $result_html .= $region_name . ', ' . $culture_name['NAME'];

            //получение данных спроса
            $check_region = 0;
            if (!empty($_POST['region'])
                && filter_var($_POST['region'], FILTER_VALIDATE_INT)
            ) {
                $check_region = $_POST['region'];
            }
            if ($check_region == 0) {
                $check_region = client::getRegionByWh($_POST['wh']);
            }

            if ($check_region > 0) {
                //Средняя цена спроса
                $nds_type = (isset($_POST['nds_type']) && $_POST['nds_type'] == 'y' ? true : false);
                $last_prices = client::getReqAveragePrices($_POST['culture'], $check_region, $nds_type);
                $yest_date = ConvertTimeStamp(strtotime('-1 day'), 'SHORT');
                $before_yest_date = ConvertTimeStamp(strtotime('-2 days'), 'SHORT');

                $nds_val = rrsIblock::getConst('nds');

                $sAveragePriceText = '';
                $sAveragePrice = '';
                if (!empty($last_prices[$yest_date])) {
                    $price_diff = 0;
                    if (!empty($last_prices[$before_yest_date])) {
                        $price_diff = $last_prices[$yest_date] - $last_prices[$before_yest_date];
                    }

                    $sAveragePriceText = 'Средняя цена спроса, ' . ($nds_type ? 'с НДС' : 'без НДС') . ': ' . number_format($last_prices[$yest_date], 0, '.', ' ') . ' руб/т';
                    if ($price_diff != 0) {
                        $sAveragePriceText .= ' (' . ($price_diff > 0 ? '+' : '') . number_format($price_diff, 0, '.', ' ') . ' руб/т)';
                        $sAveragePrice = number_format($price_diff, 0, '.', ' ');
                    }
                }
                if($sAveragePriceText != ''){
                    $result_html .= '<br><br>' . $sAveragePriceText;
                }

                //Лучшая цена предложения
                $sBestPriceText = '';
                $sBestPrice = '';
                $best_wh_name = client::getWHNameById($_POST['best_offer_wh']);
                if ($best_wh_name != ''
                    && is_numeric($_POST['best_offer_price'])
                    && $_POST['best_offer_price'] > 0
                ) {
                    //учитываем запрашиваемый тип НДС
//                    if ($nds_type && $_POST['best_offer_nds'] == 'n') {
//                        //добавляем НДС в цену
//                        $_POST['best_offer_price'] = $_POST['best_offer_price'] + $_POST['best_offer_price'] * 0.01 * $nds_val;
//                    } elseif (!$nds_type && $_POST['best_offer_nds'] == 'y') {
//                        //вычитаем НДС из цены
//                        $_POST['best_offer_price'] = $_POST['best_offer_price'] / (1 + 0.01 * $nds_val);
//                    }

                    $sBestPriceText .= 'Лучшее предложение СРТ ' . trim($best_wh_name) . ', ' . ($_POST['best_offer_nds'] == 'y' ? 'с НДС' : 'без НДС') . ': ' . number_format($_POST['best_offer_price'], 0, '.', ' ') . ' руб/т';
                    $sBestPrice = number_format($_POST['best_offer_price'], 0, '.', ' ');
                }
                if($sBestPriceText != ''){
                    $result_html .= '<br><br>' . $sBestPriceText;
                }
                $url_value = $GLOBALS['host'] . '/client/exclusive_offers/?culture_id=' . $_POST['culture'] . '&warehouse_id=' . $_POST['wh'];

                //Рассмотреть и принять
                $result_html .= '<br><br>Выбрать и купить: ';
                if (isset($_POST['page'])
                    && filter_var($_POST['page'], FILTER_VALIDATE_INT)
                    && $_POST['page'] > 1
                ) {
                    $url_value .= '&page=' . $_POST['page'];
                }
                $url_value .= '&show_graph=y';
                if (isset($_POST['graph_type'])
                    && $_POST['graph_type'] != ''
                ) {
                    $url_value .= '&graph_type=' . $_POST['graph_type'];
                }
                $url_value .= '&nds_type=' . ($nds_type ? 'y' : 'n');
                if (isset($_POST['action'])
                    && $_POST['action'] != ''
                ) {
                    $url_value .= '&checked_offers=' . $_POST['action'];
                }

                //изменение ссылки на принятие пары
                $url_value = '/client/exclusive_offers/?warehouse_id='
                    . (!empty($_POST['wh']) ? $_POST['wh'] : '') . '&culture_id=' .
                    (!empty($_POST['culture']) ? $_POST['culture'] : '') . '&r=' .
                    (!empty($_POST['rid']) ? $_POST['rid'] : '') . '&o=' .
                    (!empty($_POST['oid']) ? $_POST['oid'] : '') . '&cid=' .
                    (!empty($_POST['cid']) ? $_POST['cid'] : '')
                ;

                //получаем прямую ссылку
                $straight_htef = generateStraightHref($USER->GetID(), $uid, 'c', 0, 0, '', $url_value, '/pair_page/');

                $result_html .= $straight_htef . '<br><br>Обычно каждое второе предложение переходит в сделку!!!';

                $sTemplateText = popupTemplates::getCounterRequestGraphTemplate();
                //если есть шаблон, то отправляем по нему, иначе отправляем по старому
                if($sTemplateText != ''){
                    $result_html = str_replace(
                        array(
                            '#COFFER_GRAPH_REGION#',
                            '#COFFER_GRAPH_CULTURE#',
                            '#COFFER_GRAPH_AVERAGEPRICE_TEXT#',
                            '#COFFER_GRAPH_BESTPRICE_TEXT#',
                            '#COFFER_GRAPH_HREF#',
                        ),
                        array(
                            $region_name,
                            $culture_name['NAME'],
                            $sAveragePriceText,
                            $sBestPriceText,
                            $straight_htef,
                        ),
                        $sTemplateText
                    );
                }

                $result_arr['title'] = 'Отправить график покупателю';
                $result_arr['text'] = $result_html;
                $result_arr['uid'] = $uid;

                $result_arr['email'] = '0';
                //получаем email пользователя
                $res = CUser::GetList(
                    ($by = 'id'), ($order = 'asc'),
                    array(
                        'ID' => $uid,
                        '!EMAIL' => false
                    ),
                    array('FIELDS' => array('EMAIL'))
                );
                if ($res->SelectedRowsCount() > 0) {
                    $result_arr['email'] = '1'; //по логике js скрипта передается признак наличия почты
                }

                echo json_encode($result_arr, JSON_UNESCAPED_UNICODE);
                exit;
            }
        }
    }
}

echo 0;
exit;