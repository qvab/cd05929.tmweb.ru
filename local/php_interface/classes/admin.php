<?
//Класс для работы с изменениями админки
class admin
{
    /**
     * Организация работы кнопки выгрузки данных из HL инфоблока COUNTEROFFERS
     * @param int $id идентификатор запроса
     * @return [] массив с информацией о запросе
     */
    public static function showDownloadCounterOffersButton()
    {
        CJSCore::Init(array("jquery"));
        ?>
        <link href="/local/templates/main/css/admin_style.css" type="text/css" data-template-style="true" rel="stylesheet" />
        <script type="text/javascript">
            $('document').ready(function(){
                //устанавливаем кнопку (также обрабатываем обновление области аяксом)
                setInterval(function(){
                    if($('.downloadCounterOffers').length < 1) {
                        $('.adm-workarea-wrap .adm-list-table-wrap .adm-list-table-top .adm-small-button:first').before('<a href="/upload/364295_creation_data.txt" target="_blank" download="highloadblock_rows_list.csv" class="downloadCounterOffers admin_download_button adm-small-button" title="Выгрузка таблицы в excel"><div class="ico"></div> Выгрузка таблицы</a>');
                    }
                }, 200);

                $('.adm-workarea-wrap').on('click', '.downloadCounterOffers', function(e){
                    e.preventDefault();

                    //составляем коды отображаемых колонок
                    var sFieldsList = [];
                    var sSortBy = '';
                    var sOrder = '';
                    $('.adm-workarea-wrap .adm-list-table-wrap .adm-list-table-top').siblings('form:first').find('.adm-list-table tr.adm-list-table-header:first td').each(function(cInd, cObj){
                        if(
                            !$(cObj).hasClass('adm-list-table-checkbox')
                            && !$(cObj).hasClass('adm-list-table-popup-block')
                        ){
                            var wObj = $(cObj).find('.adm-list-table-cell-inner');
                            if(
                                wObj.length == 1
                                && wObj.text() != ''
                            ){
                                sFieldsList[sFieldsList.length] = wObj.text();
                                if($(cObj).hasClass('adm-list-table-cell-sort-down')){
                                    sSortBy = wObj.text();
                                    sOrder = 'DESC';
                                }else if($(cObj).hasClass('adm-list-table-cell-sort-up')){
                                    sSortBy = wObj.text();
                                    sOrder = 'ASC';
                                }
                            }
                        }
                    });

                    //отправялем ajax запрос на генерацию файла
                    $.post('/ajax/generateCounterOffersDownload.php', {
                        fields: sFieldsList.join(';'),
                        sort_by: sSortBy,
                        order: sOrder
                    }, function( mes ) {
                        if(mes == 1){
                            //успех, отдаем файл
                            var link = document.createElement('a');
                            link.setAttribute('href', '/upload/highloadblock_rows_list.xlsx');
                            link.setAttribute('download', 'highloadblock_rows_list.xlsx');
                            link.click();
                        }
                    });
                });
            });
        </script><?
    }

    /*
     * Получение данных встречных предложений, дополненных данными:
     * - культуры (названия)
     *
     * @param array $arrFields - массив полей для получения
     * @param string $sSortBy - поле для сортировки
     * @param string $sOrder - направление сортировки
     * @return array - массив данных по встречным предложениям
     * */
    public static function getCounterRequestsData($arrFields = array(), $sSortBy = '', $sOrder = ''){
        $arrResult = array();
        $arrOffersIds = array();
        $arrOffersCulturesNames = array();

        if(
            !is_array($arrFields)
            || count($arrFields) == 0
        ){
            $arrFields = array('*');
        }else{
            //всегда получаем предложение, для получения культуры
            if(!in_array('UF_OFFER_ID', $arrFields)){
                $arrFields[] = 'UF_OFFER_ID';
            }
        }

        $arrOrder = array('ID' => 'ASC');
        if(
            !empty($sSortBy)
            && !empty($sOrder)
        ){
            $arrOrder = array($sSortBy => $sOrder);
        }

        $iHlIb = rrsIblock::HLgetIBlockId('COUNTEROFFERS');
        $log_obj = new log();
        $entity_class = $log_obj->getEntityDataClass($iHlIb);
        $hl_el = new $entity_class;
        $res = $hl_el->getList(array(
            'select' => $arrFields,
            'order' => $arrOrder
        ));
        while ($data = $res->fetch()) {
            if(isset($data['UF_DATE'])) {
                $data['UF_DATE'] = $data['UF_DATE']->toString();
            }
            if(isset($data['UF_PARTNER_Q_APRVD_D'])) {
                $data['UF_PARTNER_Q_APRVD_D'] = $data['UF_PARTNER_Q_APRVD_D']->toString();
            }

            $arrResult[] = $data;

            $arrOffersIds[$data['UF_OFFER_ID']] = true;
        }

        if(count($arrOffersIds) > 0){
            $arrOffersCulturesNames = farmer::getCultureNamesByOffers(array_keys($arrOffersIds));
            $iTempLength = count($arrResult);
            for($i = 0; $i < $iTempLength; $i++){
                if(isset($arrOffersCulturesNames[$arrResult[$i]['UF_OFFER_ID']])){
                    $arrResult[$i]['CULTURE_NAME'] = $arrOffersCulturesNames[$arrResult[$i]['UF_OFFER_ID']];
//                    $arrResult[$i]['CULTURE_NAME'] = mb_strtolower($arrOffersCulturesNames[$arrResult[$i]['UF_OFFER_ID']]);
//                    if(mb_substr($arrResult[$i]['CULTURE_NAME'],0, 1) == 'я')
//                    {
//                        $arrResult[$i]['CULTURE_NAME'] = 'Я' . mb_substr($arrResult[$i]['CULTURE_NAME'], 1);
//                    }
                }
            }
        }

        return $arrResult;
    }
}