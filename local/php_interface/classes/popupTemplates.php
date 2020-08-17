<?
class popupTemplates {
    /*
     * Получение шаблона уведомления по коду
     * @param string $sCode - символьный код записи
     * @return string - html код шаблона
     * */
    public static function getTemplate($sCode){
        $sResult = '';

        if(trim($sCode) != '') {
            $obRes = CIBlockElement::GetList(
                array('ID' => 'ASC'),
                array(
                    'IBLOCK_ID' => rrsIblock::getIBlockId('popup_templates'),
                    'ACTIVE' => 'Y',
                    'CODE' => $sCode,
                ),
                false,
                array('nTopCount' => 1),
                array('DETAIL_TEXT')
            );
            if ($arrData = $obRes->Fetch()) {
                $sResult = self::normalizeTemplate($arrData['DETAIL_TEXT']);
            }
        }

        return $sResult;
    }

    /*
     * Получение шаблона уведомления для попапа встречного предложения организатора для покупателя
     * @return string - html код шаблона
     *
     * параметры для замены в шаблоне
     * #ORG_CREQ_CULTURE# - название культуры предложения
     * #ORG_CREQ_VOL# - объем предложения
     * #ORG_CREQ_NDS# - с ндс/без ндс (текст)
     * #ORG_CREQ_CSMPRICE# - цена с места
     * #ORG_CREQ_QUALITY_APPROVED# - подтверждено ли качество
     * #ORG_CREQ_PARAM_LIST# - список параметров качества
     * #ORG_CREQ_WH_NAME# - название склада
     * #ORG_CREQ_WH_ROUTE# - расстояние до склада
     * #ORG_CREQ_WH_ROUTE_TARIF# - стоимость перевозки
     * #ORG_CREQ_SBROS# - сброс по качеству (текст)
     * #ORG_CREQ_BASEPRICE# - базисная цена
     * #ORG_CREQ_SERVICES# - дополнительные опции (текст)
     * #ORG_CREQ_HREF# - ссылка
     * #ORG_CREQ_AGENTPRICE# - стоимость услуги организатора
     * #ORG_CREQ_AGENTPRICE_PER_TON# - стоимость услуги организатора за тонну
     * */
    public static function getOrgCounterRequestClientTemplate()
    {
        return self::getTemplate('org_creq_client');
    }

    /*
     * Получение шаблона уведомления для попапа встречного предложения организатора для покупателя (для публичной страницы создания пары, когда отображается ссылка на другое предложение)
     * @return string - html код шаблона
     *
     * параметры для замены в шаблоне
     * #ORG_CREQ_CULTURE# - название культуры предложения
     * #ORG_CREQ_VOL# - объем предложения
     * #ORG_CREQ_NDS# - с ндс/без ндс (текст)
     * #ORG_CREQ_CSMPRICE# - цена с места
     * #ORG_CREQ_QUALITY_APPROVED# - подтверждено ли качество
     * #ORG_CREQ_PARAM_LIST# - список параметров качества
     * #ORG_CREQ_WH_NAME# - название склада
     * #ORG_CREQ_WH_ROUTE# - расстояние до склада
     * #ORG_CREQ_WH_ROUTE_TARIF# - стоимость перевозки
     * #ORG_CREQ_SBROS# - сброс по качеству (текст)
     * #ORG_CREQ_BASEPRICE# - базисная цена
     * #ORG_CREQ_SERVICES# - дополнительные опции (текст)
     * #ORG_CREQ_HREF# - ссылка
     * #ORG_CREQ_AGENTPRICE# - стоимость услуги организатора
     * #ORG_CREQ_AGENTPRICE_PER_TON# - стоимость услуги организатора за тонну
     * */
    public static function getOrgOtherCounterRequestClientTemplate()
    {
        return self::getTemplate('org_creq_client_other_offer');
    }

    /*
     * Получение шаблона уведомления для рассылки отправки встречного предложения организатора для поставщика
     * @return string - html код шаблона
     *
     * параметры для замены в шаблоне
     * #ORG_CREQ_WH_NAME# - название склада
     * #ORG_CREQ_CULTURE# - название культуры предложения
     * #ORG_CREQ_CSMPRICE# - цена с места
     * #ORG_CREQ_NDS# - с ндс/без ндс (текст)
     * #ORG_CREQ_MARKETPRICE# - данные рынка (текст)
     * #ORG_CREQ_SPROSPRICE# - данные спроса (текст)
     * #ORG_CREQ_HREF# - ссылка
     * */
    public static function getOrgCounterRequestFarmerTemplate()
    {
        return self::getTemplate('org_creq_farmer');
    }

    /*
     * Получение шаблона уведомления для попапа отправки встречного предложения организатора для поставщика
     * @return string - html код шаблона
     *
     * параметры для замены в шаблоне
     * #ORG_CREQ_WH_NAME# - название склада
     * #ORG_CREQ_CULTURE# - название культуры предложения
     * #ORG_CREQ_CSMPRICE# - цена с места
     * #ORG_CREQ_NDS# - с ндс/без ндс (текст)
     * #ORG_CREQ_MARKETPRICE# - данные рынка (текст)
     * #ORG_CREQ_SPROSPRICE# - данные спроса (текст)
     * #ORG_CREQ_HREF# - ссылка
     * */
    public static function getOrgCounterRequestFarmerPopupTemplate()
    {
        return self::getTemplate('org_creq_farmer_popup');
    }

    /*
     * Получение шаблона отправки графика для товара
     * @return string - html код шаблона
     *
     * параметры для замены в шаблоне
     * #OFFER_GRAPH_CULTURE# - название культуры товара
     * #OFFER_GRAPH_WH_NAME# - название склада
     * #OFFER_GRAPH_LASTPRICE# - последняя цена
     * #OFFER_GRAPH_DIFFTEXT# - с ндс/без ндс (текст)
     * */
    public static function getOfferGraphTemplate()
    {
        return self::getTemplate('offer_graph');
    }

    /*
     * Получение шаблона отправки графика для предложений покупателя
     * @return string - html код шаблона
     *
     * параметры для замены в шаблоне
     * #COFFER_GRAPH_REGION# - название региона
     * #COFFER_GRAPH_CULTURE# - название культуры
     * #COFFER_GRAPH_NDS# - текст ндс для средней цены
     * #COFFER_GRAPH_AVERAGEPRICE# - средняя цены
     * #COFFER_GRAPH_BESTWH# - название склада для лучшей цены
     * #COFFER_GRAPH_BESTNDS# - текст ндс для лучшей цены
     * #COFFER_GRAPH_BESTPRICE# - лучшая цена
     * #COFFER_GRAPH_HREF# - ссылка на лендинг лучшего предложения
     * */
    public static function getCounterRequestGraphTemplate()
    {
        return self::getTemplate('client_offer_graph');
    }

    /*
     * Попытка "нормализовать" данные, полученные из визуального редактора и представить их такими, каковы теги html (убрав лишние переносы и отступы, полученные из шаблона)
     * @param $sArg string - необработанный html код шаблона
     * @return string - обработанный html код шаблона
     * */
    public static function normalizeTemplate($sArg)
    {
        $sReturn = $sArg;

        //убираем переносы, табуляции и т.д.
        $sReturn = str_replace(array("\n","\r","\t"), '', $sReturn);

        //убираем двойные пробелы и табуляции
        $sReturn = preg_replace('/\s+/', ' ', $sReturn);
        //убираем отступы перед и после тегов <div> и </div>
        $sReturn = preg_replace('/( \<div[^>]*\>|\<div[^>]*\> )/sui', "<div>", $sReturn);
        $sReturn = preg_replace('/( \<\/div\>|\<\/div\> )/sui', "</div>", $sReturn);
        //убираем все пустые <div></div>
        $sReturn = preg_replace('/\<div[^>]*\> *\<\/div\>/sui', '', $sReturn);
        //меняем "сцепку" /div><div или /div><br><div на <br>
        $sReturn = preg_replace('/(\<\/div\> *\<div[^>]*\>|\<\/div\> *<br *\/?\> *\<div[^>]*\>)/sui', "<br/>", $sReturn);
        //заменяем все <div><br/></div> на <br>
        $sReturn = preg_replace('/(<div[^>]*\> *\<br *\/?\> *\<\/div\>)/sui', "<br>", $sReturn);
        //убираем <br> после или перед <div>
        $sReturn = preg_replace('/(\<br *\/?\> *\<div[^>]*\>|\<div[^>]*\> *\<br *\/?\>)/sui', "<div>", $sReturn);
        //убираем <br> после или перед </div>
        $sReturn = preg_replace('/\<\/div\> *\<br *\/?\>/sui', "</div>", $sReturn);
        //оставляем разрешенные теги
        $sReturn = strip_tags($sReturn,'<br><a><b><u><i>');

        return $sReturn;
    }
}