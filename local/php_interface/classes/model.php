<?
class model {
    /**
     * Расчет математической модели Агрохелпер
     * @param  int $center_id идентификатор регионального центра
     *         int $culture_id идентификатор культуры
     * @return [] массив с результатами расчета
     */
    public static function MathCalculation($center_id, $culture_id, $debug = false) {
        CModule::IncludeModule('iblock');

        //получение типа компании лидера в региональном центре
        $type_id = self::getType($center_id, $culture_id);

        //получение показателей: Биржевая стоимость, Стоимость перевалки в порту, ЖД доставка
        $params = self::getParams($center_id, $culture_id, $type_id);

        //получение показателей: Пошлина, Поправка на качество, Потеря в весе, Маржа трейдера, Объем выработки конечного продукта
        $corrections = self::getCorrections($culture_id, $type_id);

        //получение стоимости погрузки в ЖД вагон
        $loading_cost = self::getLoadingCost($culture_id);

        //получение маржинальности переработчика и общепроизводственных расходов
        $marginality = self::getMarginality($culture_id, $type_id);

        //получение поправки регионального центра
        $center_correction = self::getRegionCenterCorrection($center_id);

        //получение курса доллара
        $d = rrsIblock::getCurrencyRate();

        $DDP['PRICE'] = 0;

        if ($debug) {
            $cultureInfo = rrsIblock::getElementsInfo(10, array($culture_id));
            $typeInfo = rrsIblock::getElementsInfo(24, $type_id);
            $modelInfo = rrsIblock::getElementsInfo(25, array_keys($params));

            $DDP['CULTURE'] = array('TITLE' => 'Культура', 'VALUE' => $cultureInfo[$culture_id]['NAME']);
            $DDP['TYPE'] = array('TITLE' => 'Тип компании А клиента', 'VALUE' => $typeInfo[$type_id]['NAME']);
            $DDP['MODEL'] = array('TITLE' => 'Модель реализации', 'VALUE' => array());
            $DDP['GAIN'] = array('TITLE' => ' Выручка', 'VALUE' => 0);

            foreach ($params as $key => $model) {
                $DDP['MODEL']['VALUE']['ID'][$model['MODEL']] = $model['ID'];
                $DDP['MODEL']['VALUE']['NAME'][$model['MODEL']] = $modelInfo[$key]['NAME'];

                //Биржевая стоимость/FOB, $
                $cost = $model['MARKET_COST'];
                $DDP['MODEL']['VALUE']['COST']['TITLE'] = 'Биржевая стоимость/FOB, $';
                $DDP['MODEL']['VALUE']['COST']['VALUE'][$model['MODEL']] = $cost;

                //Биржевая стоимость без пошлины/FOB, $
                $cost = $cost * (1 - 0.01 * $corrections[$key]['TAX']);
                $DDP['MODEL']['VALUE']['TAX']['TITLE'] = 'Пошлина, %';
                $DDP['MODEL']['VALUE']['TAX']['VALUE'][$model['MODEL']] = $corrections[$key]['TAX'] . '%';
                $DDP['MODEL']['VALUE']['COST_TAX']['TITLE'] = 'Биржевая стоимость без пошлины/FOB, $';
                $DDP['MODEL']['VALUE']['COST_TAX']['VALUE'][$model['MODEL']] = $cost;

                //Стоимость в порту БЦ(FOB), р/тн
                $cost = $cost * $d;
                $DDP['MODEL']['VALUE']['DOLLAR']['TITLE'] = 'Курс $';
                $DDP['MODEL']['VALUE']['DOLLAR']['VALUE'][$model['MODEL']] = $d;
                $DDP['MODEL']['VALUE']['COST_RUB']['TITLE'] = 'Стоимость в порту БЦ(FOB), р/тн';
                $DDP['MODEL']['VALUE']['COST_RUB']['VALUE'][$model['MODEL']] = $cost;

                //Стоимость в порту БЦ(FOB) с поправкой на качество, р/тн
                $cost = $cost * (1 - 0.01 * $corrections[$key]['QUALITY']);
                $DDP['MODEL']['VALUE']['QUALITY']['TITLE'] = 'Поправка на качество(коэф)';
                $DDP['MODEL']['VALUE']['QUALITY']['VALUE'][$model['MODEL']] = $corrections[$key]['QUALITY'] . '%';
                $DDP['MODEL']['VALUE']['COST_QUALITY']['TITLE'] = 'Стоимость в порту БЦ(FOB) с поправкой на качество, р/тн';
                $DDP['MODEL']['VALUE']['COST_QUALITY']['VALUE'][$model['MODEL']] = $cost;

                //Стоимость в порту БЦ(FOB) с поправкой на качество и потерю в весе р/тн
                $cost = $cost * (1 - 0.01 * $corrections[$key]['WEIGHT_LOSS']);
                $DDP['MODEL']['VALUE']['WEIGHT']['TITLE'] = 'Потеря в весе, %';
                $DDP['MODEL']['VALUE']['WEIGHT']['VALUE'][$model['MODEL']] = $corrections[$key]['WEIGHT_LOSS'] . '%';
                $DDP['MODEL']['VALUE']['COST_WEIGHT']['TITLE'] = 'Стоимость в порту БЦ(FOB) с поправкой на качество и потерю в весе р/тн';
                $DDP['MODEL']['VALUE']['COST_WEIGHT']['VALUE'][$model['MODEL']] = $cost;

                //Стоимость в порту БЦ(FOB) с вычетом маржи трейдера, р/тн
                $cost = $cost * (1 - 0.01 * $corrections[$key]['MARGIN_TRADER']);
                $DDP['MODEL']['VALUE']['MARGIN']['TITLE'] = 'Маржа трейдера';
                $DDP['MODEL']['VALUE']['MARGIN']['VALUE'][$model['MODEL']] = $corrections[$key]['MARGIN_TRADER'] . '%';
                $DDP['MODEL']['VALUE']['COST_MARGIN']['TITLE'] = 'Стоимость в порту БЦ(FOB) с вычетом маржи трейдера, р/тн';
                $DDP['MODEL']['VALUE']['COST_MARGIN']['VALUE'][$model['MODEL']] = $cost;

                //Логистика склад А - FOB(порт)
                $cost = $cost - $model['TRANSFER_COST'] - $model['RAILWAY_COST'] - $loading_cost;
                $DDP['MODEL']['VALUE']['TRANSFER']['TITLE'] = 'Стоимость перевалки в порту, р/тн';
                $DDP['MODEL']['VALUE']['TRANSFER']['VALUE'][$model['MODEL']] = $model['TRANSFER_COST'];
                $DDP['MODEL']['VALUE']['RAILWAY']['TITLE'] = 'Доставка ЖД области клиента A - ЖД ст порт, р/тн';
                $DDP['MODEL']['VALUE']['RAILWAY']['VALUE'][$model['MODEL']] = $model['RAILWAY_COST'];
                $DDP['MODEL']['VALUE']['LOADING']['TITLE'] = 'Погрузка в ЖД вагон';
                $DDP['MODEL']['VALUE']['LOADING']['VALUE'][$model['MODEL']] = $loading_cost;
                $DDP['MODEL']['VALUE']['COST_TRANSPORT']['TITLE'] = 'Логистика склад А - FOB(порт)';
                $DDP['MODEL']['VALUE']['COST_TRANSPORT']['VALUE'][$model['MODEL']] = $cost;

                //Выручка
                $cost = 0.01 * $cost * $corrections[$key]['VOLUME'];
                $DDP['MODEL']['VALUE']['VOLUME']['TITLE'] = 'Объем выработки конечного продукта, %';
                $DDP['MODEL']['VALUE']['VOLUME']['VALUE'][$model['MODEL']] = $corrections[$key]['VOLUME'] . '%';
                $DDP['MODEL']['VALUE']['COST_VOLUME']['TITLE'] = 'Выручка';
                $DDP['MODEL']['VALUE']['COST_VOLUME']['VALUE'][$model['MODEL']] = $cost;
                $DDP['GAIN']['VALUE'] += $cost;
                $DDP['PRICE'] += $cost;
            }

            //Прибыль
            $profit = 0.01 * $DDP['PRICE'] * $marginality['MARGINALITY'];

            //Общепроизводственные расходы, руб
            $bill_rub = 0.01 * $DDP['PRICE'] * $marginality['BILL'];

            //Паритетная цена БЦ(DDP) , р/тн
            $DDP['PRICE'] = $DDP['PRICE'] - $profit - $bill_rub;

            $DDP['MARGINALITY'] = array('TITLE' => 'Маржинальность переработчика', 'VALUE' => $marginality['MARGINALITY'] . '%');
            $DDP['PROFIT'] = array('TITLE' => 'Прибыль', 'VALUE' => $profit);
            $DDP['BILL'] = array('TITLE' => 'Общепроизводственные расходы, %', 'VALUE' => $marginality['BILL'] . '%');
            $DDP['BILL_RUB'] = array('TITLE' => 'Общепроизводственные расходы, руб', 'VALUE' => $bill_rub);
            $DDP['BP_DDP'] = array('TITLE' => 'Паритетная цена БЦ(DPP) , р/тн', 'VALUE' => $DDP['PRICE']);

            $DDP['PRICE'] = $DDP['PRICE'] * (1 - 0.01 * $center_correction);

            $DDP['REGION_CORRECTION'] = array('TITLE' => 'Региональная поправка СОВЭКОН', 'VALUE' => $center_correction . '%');
            $DDP['BP_DDP_REGION'] = array('TITLE' => 'БЦ(DPP), р/тн без учета НДС = Цена, если клиент А работает с НДС', 'VALUE' => $DDP['PRICE']);
        }
        else {
            foreach ($params as $key => $model) {
                $cost = 0.01
                    * ($d
                        * $model['MARKET_COST']
                        * (1 - 0.01 * $corrections[$key]['TAX'])
                        * (1 - 0.01 * $corrections[$key]['QUALITY'])
                        * (1 - 0.01 * $corrections[$key]['WEIGHT_LOSS'])
                        * (1 - 0.01 * $corrections[$key]['MARGIN_TRADER'])
                        - $model['TRANSFER_COST']
                        - $model['RAILWAY_COST']
                        - $loading_cost)
                    * $corrections[$key]['VOLUME'];
                $DDP['PRICE'] += $cost;
            }

            //Паритетная цена БЦ(DDP), р/тн
            $DDP['PRICE'] = $DDP['PRICE']
                * (1 - 0.01 * $marginality['MARGINALITY'] - 0.01 * $marginality['BILL'])
                * (1 - 0.01 * $center_correction);
        }

        $DDP['PRICE'] = price($DDP['PRICE']);

        $urgencyList = self::getUrgencyList();
        foreach ($urgencyList as $key => $item) {
            $DDP['PRICE_'.mb_strtoupper($key)] = price($DDP['PRICE'] * (1 + 0.01 * $item['MARGIN']));
        }

        return $DDP;
    }

    /**
     * Получение типа компании лидера в региональном центре
     * @param  int $center_id идентификатор регионального центра
     *         int $culture_id идентификатор культуры
     * @return int идентификатор типа компании
     */
    public static function getType($center_id, $culture_id) {
        $result = 0;
        $res = CIBlockElement::GetList(
            array('ID' => 'DESC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('reg_center_leader'),
                'ACTIVE' => 'Y',
                'PROPERTY_CENTER' => $center_id,
                'PROPERTY_CULTURE' => $culture_id
            ),
            false,
            false,
            array(
                'ID',
                'NAME',
                'PROPERTY_TYPE'
            )
        );
        if ($ob = $res->Fetch()) {
            $result = $ob['PROPERTY_TYPE_VALUE'];
        }

        return $result;
    }

    /**
     * Получение параметров: Биржевая стоимость, Стоимость перевалки в порту, ЖД доставка
     * @param  int $center_id идентификатор регионального центра
     *         int $culture_id идентификатор культуры
     *         int $type_id идентификатор типа компании
     * @return [] массив со списком значений
     */
    public static function getParams($center_id, $culture_id, $type_id) {
        $res = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('market_value'),
                'ACTIVE' => 'Y',
                'PROPERTY_CENTER' => $center_id,
                'PROPERTY_CULTURE' => $culture_id,
                'PROPERTY_TYPE' => $type_id,
            ),
            false,
            false,
            array(
                'ID',
                'NAME',
                'PROPERTY_MODEL',
                'PROPERTY_MARKET_COST',
                'PROPERTY_TRANSFER_COST',
                'PROPERTY_RAILWAY',
            )
        );
        while ($ob = $res->Fetch()) {
            $tmp = array(
                'ID' => $ob['ID'],
                'MODEL' => $ob['PROPERTY_MODEL_VALUE'],
                'MARKET_COST' => $ob['PROPERTY_MARKET_COST_VALUE'],
                'TRANSFER_COST' => $ob['PROPERTY_TRANSFER_COST_VALUE'],
                'RAILWAY_COST' => $ob['PROPERTY_RAILWAY_VALUE'],
            );
            $result[$ob['PROPERTY_MODEL_VALUE']] = $tmp;
        }

        return $result;
    }

    /**
     * Получение параметров: Пошлина, Поправка на качество, Потеря в весе, Маржа трейдера, Объем выработки конечного продукта
     * @param  int $culture_id идентификатор культуры
     *         int $type_id идентификатор типа компании
     * @return [] массив со списком значений
     */
    public static function getCorrections($culture_id, $type_id) {
        $res = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('corrections_value'),
                'ACTIVE' => 'Y',
                'PROPERTY_CULTURE' => $culture_id,
                'PROPERTY_TYPE' => $type_id,
            ),
            false,
            false,
            array(
                'ID',
                'NAME',
                'PROPERTY_MODEL',
                'PROPERTY_TAX',
                'PROPERTY_QUALITY',
                'PROPERTY_WEIGHT_LOSS',
                'PROPERTY_MARGIN_TRADER',
                'PROPERTY_VOLUME',
            )
        );
        while ($ob = $res->Fetch()) {
            $tmp = array(
                'ID' => $ob['ID'],
                'MODEL' => $ob['PROPERTY_MODEL_VALUE'],
                'TAX' => $ob['PROPERTY_TAX_VALUE'],
                'QUALITY' => $ob['PROPERTY_QUALITY_VALUE'],
                'WEIGHT_LOSS' => $ob['PROPERTY_WEIGHT_LOSS_VALUE'],
                'MARGIN_TRADER' => $ob['PROPERTY_MARGIN_TRADER_VALUE'],
                'VOLUME' => $ob['PROPERTY_VOLUME_VALUE'],
            );
            $result[$ob['PROPERTY_MODEL_VALUE']] = $tmp;
        }

        return $result;
    }

    /**
     * Получение параметров: Маржинальность переработчика, Общепроизводственные расходы
     * @param  int $culture_id идентификатор культуры
     *         int $type_id идентификатор типа компании
     * @return [] массив со списком значений
     */
    public static function getMarginality($culture_id, $type_id) {
        $res = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('marginality'),
                'ACTIVE' => 'Y',
                'PROPERTY_CULTURE' => $culture_id,
                'PROPERTY_TYPE' => $type_id,
            ),
            false,
            false,
            array(
                'ID',
                'NAME',
                'PROPERTY_MARGINALITY',
                'PROPERTY_BILL',
            )
        );
        if ($ob = $res->Fetch()) {
            $tmp = array(
                'ID' => $ob['ID'],
                'MARGINALITY' => $ob['PROPERTY_MARGINALITY_VALUE'],
                'BILL' => $ob['PROPERTY_BILL_VALUE'],
            );
            $result = $tmp;
        }

        return $result;
    }

    /**
     * Получение стоимости погрузки в ЖД вагон
     * @param  int $culture_id идентификатор культуры
     * @return number значение стоимости
     */
    public static function getLoadingCost($culture_id) {
        $res = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('cultures'),
                'ACTIVE' => 'Y',
                'ID' => $culture_id,
            ),
            false,
            false,
            array(
                'ID',
                'NAME',
                'PROPERTY_LOADING',
            )
        );
        if ($ob = $res->Fetch()) {
            $result = $ob['PROPERTY_LOADING_VALUE'];
        }

        return $result;
    }

    /**
     * Получение списка типов закупки
     * @param
     * @return [] массив со списком элементов
     */
    public static function getUrgencyList() {
        $res = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('urgency'),
                'ACTIVE' => 'Y'
            ),
            false,
            false,
            array(
                'ID',
                'NAME',
                'CODE',
                'PROPERTY_MARGIN',
            )
        );
        while ($ob = $res->Fetch()) {
            $tmp = array(
                'ID' => $ob['ID'],
                'NAME' => $ob['NAME'],
                'MARGIN' => $ob['PROPERTY_MARGIN_VALUE'],
            );
            $result[$ob['CODE']] = $tmp;
        }

        return $result;
    }

    /**
     * Получение поправки регионального центра
     * @param  int $center_id идентификатор регионального ценра
     * @return [] массив со списком элементов
     */
    public static function getRegionCenterCorrection($center_id) {
        $res = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('regions_centers'),
                'ACTIVE' => 'Y',
                'ID' => $center_id
            ),
            false,
            false,
            array(
                'ID',
                'NAME',
                'PROPERTY_CORRECTION',
            )
        );
        if ($ob = $res->Fetch()) {
            $result = $ob['PROPERTY_CORRECTION_VALUE'];
        }

        return $result;
    }

    /**
     * Сохранение паритетной цены
     * @param  int $center_id идентификатор регионаального центра
     *         int $culture_id идентификатор культуры
     *         [] $data массив со списком цен
     *         array $arrCentersRegions массив привязки региональных центров
     * @return int идентификатор элемента
     */
    public static function saveParityPrice($center_id, $culture_id, $data, $arrCentersRegions = array()) {

        $iRegion = 0;
        if(isset($arrCentersRegions[$center_id])){
            $iRegion = $arrCentersRegions[$center_id];
        }else{
            $iRegion = self::getRegionByCenter($center_id);
        }

        $res = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('parity_price'),
                'ACTIVE' => 'Y',
                'PROPERTY_CENTER' => $center_id,
                'PROPERTY_CULTURE' => $culture_id,
            ),
            false,
            false,
            array('ID', 'NAME')
        );
        if ($ob = $res->Fetch()) {
            $ID = $ob['ID'];
        }

        $el = new CIBlockElement;
        $arLoadProductArray = Array(
            'IBLOCK_ID' => rrsIblock::getIBlockId('parity_price'),
            'PROPERTY_VALUES'=> array(
                'REGION' => $iRegion,
                'CENTER' => $center_id,
                'CULTURE' => $culture_id,
                'PRICE_PASSIVE' => price($data['PRICE_PASSIVE']),
                'PRICE_STANDART' => price($data['PRICE_STANDART']),
                'PRICE_ACTIVE' => price($data['PRICE_ACTIVE'])
            ),
            'NAME'           => date('d.m.Y H:i:s'),
            'ACTIVE'         => 'Y'
        );
        if ($ID > 0) {
            $res = $el->Update($ID, $arLoadProductArray);
        }
        else {
            $ID = $el->Add($arLoadProductArray);
        }

        return $ID;
    }

    /**
     * Получение паритетной цены
     * @param  int $center_id идентификатор регионального центра
     *         int $culture_id идентификатор культуры
     * @return [] массив с данными о ценах
     */
    public static function getParityPrice($center_id, $culture_id) {
        if (!$center_id || !$culture_id)
            return false;
        CModule::IncludeModule('iblock');
        $result = array();
        $res = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('parity_price'),
                'ACTIVE' => 'Y',
                'PROPERTY_CENTER' => $center_id,
                'PROPERTY_CULTURE' => $culture_id
            ),
            false,
            false,
            array('ID', 'NAME', 'PROPERTY_PRICE_PASSIVE', 'PROPERTY_PRICE_STANDART', 'PROPERTY_PRICE_ACTIVE')
        );
        if ($ob = $res->Fetch()) {
            $result['PRICE_PASSIVE'] = $ob['PROPERTY_PRICE_PASSIVE_VALUE'];
            $result['PRICE_STANDART'] = $ob['PROPERTY_PRICE_STANDART_VALUE'];
            $result['PRICE_ACTIVE'] = $ob['PROPERTY_PRICE_ACTIVE_VALUE'];
        }

        return $result;
    }

    /**
     * Получение списка паритетных цен
     * @param  int $center_id идентификатор регионального центра
     *         int $culture_id идентификатор культуры
     * @return [] массив с данными о ценах
     */
    public static function getParityPriceList() {
        CModule::IncludeModule('iblock');
        $result = array();
        $res = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('parity_price'),
                'ACTIVE' => 'Y'
            ),
            false,
            false,
            array(
                'ID',
                'NAME',
                'PROPERTY_CENTER',
                'PROPERTY_CULTURE',
                'PROPERTY_PRICE_PASSIVE',
                'PROPERTY_PRICE_STANDART',
                'PROPERTY_PRICE_ACTIVE'
            )
        );
        while ($ob = $res->Fetch()) {
            $result[$ob['PROPERTY_CENTER_VALUE']][$ob['PROPERTY_CULTURE_VALUE']] = array(
                'PRICE_PASSIVE' => $ob['PROPERTY_PRICE_PASSIVE_VALUE'],
                'PRICE_STANDART' => $ob['PROPERTY_PRICE_STANDART_VALUE'],
                'PRICE_ACTIVE' => $ob['PROPERTY_PRICE_ACTIVE_VALUE']
            );
        }

        return $result;
    }

    /**
     * Получение кода поправки PSA (passive/standart/active)
     * @param  int $id идентификатор элемента
     * @return string код поправки PSA
     */
    public static function getUrgencyById($id) {
        CModule::IncludeModule('iblock');
        $res = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('urgency'),
                'ACTIVE' => 'Y',
                'ID' => $id
            ),
            false,
            false,
            array('ID', 'NAME', 'CODE')
        );
        if ($ob = $res->Fetch()) {
            $result = $ob['CODE'];
        }

        if (!$result)
            $result = 'standart';

        return $result;
    }

    /**
     * Получение новой паритетной цены
     * @param  int $center_id идентификатор регионального центра
     *         int $culture_id идентификатор культуры
     * @return [] массив со списком элемента
     */
    public static function parityPriceCalculation($center_id, $culture_id) {
        if (!$center_id || !$culture_id)
            return false;

        CModule::IncludeModule('iblock');
        $data = array();

        $date = date('d.m.Y H:i:s');
        $dateFrom = date('d.m.Y H:i:s',
            mktime(
                date('H', strtotime($date)),
                date('i', strtotime($date)),
                date('s', strtotime($date)),
                date('m', strtotime($date)),
                date('d', strtotime($date))-1,
                date('Y', strtotime($date))
            )
        );

        $lastLog = log::getLastParityPriceLog($center_id, $culture_id);
        if ($lastLog['ID'] > 0) {
            $lastDate = $lastLog['UF_DATE']->format('d.m.Y H:i:s');

            if ($lastDate > $dateFrom) {
                $dateFrom = $lastDate;
            }
        }

        $res = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('deals_deals'),
                'ACTIVE' => 'Y',
                'PROPERTY_CENTER' => $center_id,
                'PROPERTY_CULTURE' => $culture_id,
                '!PROPERTY_PAIR_STATUS' => false,
                '>ACTIVE_FROM' => $dateFrom
            ),
            false,
            false,
            array(
                'ID',
                'NAME',
                'PROPERTY_PARITY_PRICE',
                'PROPERTY_VOLUME_0',
                'PROPERTY_VOLUME'
            )
        );
        while ($ob = $res->Fetch()) {
            if ($ob['PROPERTY_VOLUME_0_VALUE'] > 0) {
                $tmp = array(
                    'CENTER_ID' => $center_id,
                    'CULTURE_ID' => $culture_id,
                    'PRICE' => 0.001 * $ob['PROPERTY_PARITY_PRICE_VALUE'],
                    'VOLUME_0' => $ob['PROPERTY_VOLUME_0_VALUE'],
                    'VOLUME' => $ob['PROPERTY_VOLUME_VALUE'],
                    'P' => $ob['PROPERTY_VOLUME_VALUE'] / $ob['PROPERTY_VOLUME_0_VALUE']
                );
                $data[] = $tmp;
            }
        }

        $x = $y = array();
        foreach ($data as $d) {
            $x[] = $d['PRICE'];
            $y[] = $d['P'];
        }

        $R2 = self::exponentialRegression($x, $y);
        log::addR2Log($center_id, $culture_id, sizeof($data), $R2);

        if ($R2 > 0.5) {
            //запуск пересчета паритетной цены
            $arParityPrice = self::linearRegression($y, $x);
            return($arParityPrice);
        }

        return false;
    }

    /**
     * Построение экспоненциальной регрессии
     * @param  [] $x, $y массив точек
     * @return number коэффициент детерминации (квадрат индекса корреляции)
     */
    public static function exponentialRegression($tx, $ty) {
        //ищем f(x) = a*exp(b*x), такую, чтобы sum(yi - f(xi)) -> min
        //используется метод наименьших квадратов
        $x = $y = $x2 = $lny = $x_lny = 0;
        $n = sizeof($tx);
        if ($n < 3)
            return 0;

        for ($i = 0; $i < $n; $i++) {
            $x += $tx[$i];
            $y += $ty[$i];
            $lny += log($ty[$i]);
            $x_lny += $tx[$i] * log($ty[$i]);
            $x2 += $tx[$i] * $tx[$i];
        }

        $b1 = $n * $x_lny - $x * $lny;
        $b2 = $n * $x2 - $x * $x;

        if ($b2 == 0)
            return 0;

        $b = $b1/$b2;

        if ($b <= 0)
            return 0;

        $a = exp($lny / $n - $b * $x / $n);

        $y_ = $y / $n;
        $v = $vv = 0;
        for ($i = 0; $i < $n; $i++) {
            $v += ($ty[$i] - f_exp($a, $b, $tx[$i])) * ($ty[$i] - f_exp($a, $b, $tx[$i]));
            $vv += ($ty[$i] - $y_) * ($ty[$i] - $y_);
        }

        if ($vv == 0 || $v > $vv)
            return 0;

        $R2 = sqrt(1 - $v / $vv);

        if($R2 < 0)
            return 0;

        return $R2;
    }

    /**
     * Построение линейной регрессии
     * @param  [] $x, $y массив точек
     * @return number коэффициент детерминации (квадрат индекса корреляции)
     */
    public static function linearRegression($tx, $ty) {
        //ищем f(x) = a + b * x, такую, чтобы sum(yi - f(xi)) -> min
        //используется метод наименьших квадратов
        $n = sizeof($tx);
        if ($n < 1)
            return false;

        $s1 = $s2 = 0;
        for ($i = 0; $i < $n; $i++) {
            $s1 += $tx[$i];
            $s2 += $ty[$i];
        }

        $x_ = $s1 / $n;
        $y_ = $s2 / $n;
        $s1 = $s2 = 0;
        for ($i = 0; $i < $n; $i++) {
            $s1 += ($tx[$i] - $x_) * ($ty[$i] - $y_);
            $s2 += ($tx[$i] - $x_) * ($tx[$i] - $x_);
        }
        if ($s2 == 0)
            return false;

        $b = $s1/$s2;
        $a = $y_ - $b * $x_;

        $pKoef = array(
            'PASSIVE' => 0.01 * rrsIblock::getConst('passive'),
            'STANDART' => 0.01 * rrsIblock::getConst('standart'),
            'ACTIVE' => 0.01 * rrsIblock::getConst('active')
        );

        $result = array();
        foreach ($pKoef as $key => $koef) {
            $result['PRICE_'.$key] = 1000 * f_lin($a, $b, $koef);
        }

        return $result;
    }

    /**
     * Получение тарифа для транспорта (удалить)
     * @param  int $km расстояние
     *         bool $au тариф от Агрохелпер
     * @return int тариф
     */
    public static function getTarif($center_id, $km, $au = false) {
        CModule::IncludeModule('iblock');
        $res = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('tariff'),
                'ACTIVE' => 'Y',
                'PROPERTY_CENTER' => $center_id,
                '<PROPERTY_KM_FROM' => $km,
                '>=PROPERTY_KM_TO' => $km,
            ),
            false,
            false,
            array('ID', 'NAME', 'PROPERTY_TARIF_AU', 'PROPERTY_TARIF')
        );
        if ($ob = $res->Fetch()) {
            $result = $ob['PROPERTY_TARIF_AU_VALUE'];
            if (!$au && intval($ob['PROPERTY_TARIF_VALUE']) > 0) {
                $result = round($ob['PROPERTY_TARIF_VALUE'], 0);
            }
        }

        return $result;
    }

    /**
     * Получение тарифов для транспорта
     * @param  int $km расстояние , 0 - если выводить все тарифы для рег. центра
     * @return int тариф
     */
    public static function getTarifAll($center_id, $km = 0, $au = false) {
        CModule::IncludeModule('iblock');
        $filter = array(
            'IBLOCK_ID' => rrsIblock::getIBlockId('tariff'),
            'ACTIVE' => 'Y',
            'PROPERTY_CENTER' => $center_id,
        );
        if(!empty($km)) {
            $filter['<PROPERTY_KM_FROM'] = $km;
            $filter['>=PROPERTY_KM_TO'] = 0;
        }
        $res = CIBlockElement::GetList(
                array('ID' => 'ASC'),
                $filter,
                false,
                false,
                array('ID', 'NAME', 'PROPERTY_TARIF_AU', 'PROPERTY_TARIF', 'PROPERTY_KM_FROM', 'PROPERTY_KM_TO')
        );
        while ($ob = $res->GetNext()) {
                $result[] = array(
                    "ID" => $ob['ID'],
                    "NAME" => $ob['NAME'],
                    "FROM" => $ob['PROPERTY_KM_FROM_VALUE'],
                    "TO" => $ob['PROPERTY_KM_TO_VALUE'],
                    "TARIF" => $ob['PROPERTY_TARIF_VALUE']
                );
        }
        return $result;
    }

    /**
     * Получение номера группы тарифов
     * @param  int $km расстояние
     * @return int группа тарифа
     */
    public static function getTarifDays($center_id, $km) {
        CModule::IncludeModule('iblock');
        $arFilter = array(
            'IBLOCK_ID' => rrsIblock::getIBlockId('tariff'),
            'ACTIVE' => 'Y',
            'PROPERTY_CENTER' => $center_id,
            '<PROPERTY_KM_FROM' => $km,
            '>=PROPERTY_KM_TO' => $km,
        );
        $arSelect = array('ID', 'NAME', 'PROPERTY_DAYS');
        $res = CIBlockElement::GetList(array('ID' => 'ASC'), $arFilter, false, false, $arSelect);
        if ($ob = $res->Fetch()) {
            $result = $ob['PROPERTY_DAYS_VALUE'];
        }

        return $result;
    }

    /**
     * Вычисление сетки тарифов на основе сделок
     * @param  int $center_id идентификатор регионального центра
     *         int группа тарифов (кол-во дней в рейсе)
     * @return bool
     */
    public static function transportCalculation($center_id, $tarifGroup) {
        CModule::IncludeModule('iblock');
        $min = 100000;
        $max = 0;
        $res = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('tariff'),
                'ACTIVE' => 'Y',
                'PROPERTY_CENTER' => $center_id,
                'PROPERTY_DAYS' => $tarifGroup
            ),
            false,
            false,
            array('ID', 'NAME', 'PROPERTY_TARIF_AU', 'PROPERTY_KM_FROM', 'PROPERTY_KM_TO')
        );
        while ($ob = $res->Fetch()) {
            $tarifList[$ob['ID']] = array(
                'AU' => $ob['PROPERTY_TARIF_AU_VALUE'],
                'MIN' => $ob['PROPERTY_KM_FROM_VALUE'],
                'MAX' => $ob['PROPERTY_KM_TO_VALUE']
            );

            if ($ob['PROPERTY_KM_FROM_VALUE'] <= $min)
                $min = $ob['PROPERTY_KM_FROM_VALUE'];
            if ($ob['PROPERTY_KM_TO_VALUE'] >= $max)
                $max = $ob['PROPERTY_KM_TO_VALUE'];
        }

        $auSum = $dealSum = 0;
        $res = CIBlockElement::GetList(
            array('ID' => 'ASC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('deals_deals'),
                'ACTIVE' => 'Y',
                'PROPERTY_CENTER' => $center_id,
                '>PROPERTY_TARIF' => 0,
                '!PROPERTY_TRANSPORT' => false,
                '>PROPERTY_ROUTE' => $min,
                '<=PROPERTY_KM_TO' => $max,
            ),
            false,
            false,
            array('ID', 'NAME', 'PROPERTY_TARIF', 'PROPERTY_ROUTE', 'PROPERTY_VOLUME')
        );
        while ($ob = $res->Fetch()) {
            foreach ($tarifList as $tarif) {
                if ($ob['PROPERTY_ROUTE_VALUE'] > $tarif['MIN'] && $ob['PROPERTY_ROUTE_VALUE'] <= $tarif['MAX']) {
                    $auSum += $ob['PROPERTY_VOLUME_VALUE'] * $tarif['AU'];
                    $dealSum += $ob['PROPERTY_VOLUME_VALUE'] * $ob['PROPERTY_TARIF_VALUE'];
                    break;
                }
            }
        }

        if ($auSum != 0 && $dealSum != 0) {
            $k = $dealSum / $auSum;
            foreach ($tarifList as $key => $tarif) {
                CIBlockElement::SetPropertyValuesEx(
                    $key,
                    rrsIblock::getIBlockId('tariff'),
                    array(
                        'TARIF' => $k * $tarif['AU']
                    )
                );
            }
        }

        return true;
    }

    /**
     * Получение тарифов АХ
     * @param  bool $recache флаг очистки кеша
     * @return []
     */
    public static function getAgrohelperTariffs($recache = false) {
        CModule::IncludeModule('iblock');
        $obCache = new CPHPCache;
        $life_time = 7200;
        $cache_id = 'getAgrohelperTariffs';
        if ($obCache->InitCache($life_time, $cache_id, "/") && !$recache) {
            $vars = $obCache->GetVars();
            $result = $vars[$cache_id];
        }
        else {

            $res = CIBlockElement::GetList(
                array('PROPERTY_KM_FROM' => 'ASC'),
                array(
                    'IBLOCK_ID' => rrsIblock::getIBlockId('tariffs_agrohelper'),
                    'ACTIVE' => 'Y',
                ),
                false,
                false,
                array('ID', 'PROPERTY_KM_FROM', 'PROPERTY_KM_TO', 'PROPERTY_DAYS', 'PROPERTY_TARIF_AH')
            );
            while ($ob = $res->Fetch()) {
                if ($ob['PROPERTY_KM_FROM_VALUE'] == 0) {
                    $name = 'до ' . $ob['PROPERTY_KM_TO_VALUE'] . ' км';
                }
                else {
                    $name = 'от ' . $ob['PROPERTY_KM_FROM_VALUE'] . ' до ' . $ob['PROPERTY_KM_TO_VALUE'] . ' км';
                }
                $result[$ob['ID']] = array(
                    'NAME' => $name,
                    'FROM' => $ob['PROPERTY_KM_FROM_VALUE'],
                    'TO' => $ob['PROPERTY_KM_TO_VALUE'],
                    'DAYS' => $ob['PROPERTY_DAYS_VALUE'],
                    'TARIF' => $ob['PROPERTY_TARIF_AH_VALUE'],
                );
            }

        }
        if ($recache) $obCache->Clean($cache_id, "/", $basedir = "cache");
        if ($obCache->StartDataCache()) $obCache->EndDataCache(array($cache_id => $result));
        return $result;
    }

    /**
     * Получение региона/регионов по региональному центру/центрам
     * @param array | int $mRegCenters - массив ID региональных центров
     * @return array | int - массив соответствий регионов и региональных центров (если $mRegCenters - массив) или ID региона для указанного регионального центра (если $mRegCenters - целое число)
     */
    public static function getRegionByCenter($mRegCenters) {
        CModule::IncludeModule('iblock');

        $mResult = 0;
        $obRes = CIBlockElement::GetList(
            array('ID' => 'DESC'),
            array(
                'IBLOCK_ID' => rrsIblock::getIBlockId('regions_centers'),
                'ACTIVE' => 'Y',
                'ID' => $mRegCenters,
            ),
            false,
            false,
            array('ID', 'PROPERTY_REGION')
        );
        if(is_array($mRegCenters)){
            $mResult = array();
            while ($arrData = $obRes->Fetch()) {
                $mResult[$arrData['ID']] = $arrData['PROPERTY_REGION_VALUE'];
            }
        }else{
            if ($arrData = $obRes->Fetch()) {
                $mResult = $arrData['PROPERTY_REGION_VALUE'];
            }
        }

        return $mResult;
    }
}