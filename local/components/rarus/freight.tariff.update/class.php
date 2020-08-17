<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
/**
 * @author: Vitaly Melnik <vitali@rarus.ru>
 */
class CFreightTariffUpdate extends CBitrixComponent {

    private $obElement              = null;
    private $arRegionalCentersId    = null;

    /**
     * Инициализация
     */
    public function init() {

        // Подключаем модули
        $obModule = new CModule;

        // IB
        if(!$obModule->IncludeModule('iblock')) {
            throw Exception('Не удалось подключить модуль "iblock"');
        }
        unset($obModule);


        // Объекты
        $this->obElement = new CIBlockElement;
    }


    /**
     * Отдает список уникальных тарифов
     * @return array
     */
    public function getListUniqueTariff() {

        $arResult = [];

        $rs = $this->obElement->GetList(
            ['SORT' => 'ASC'],
            [
                'ACTIVE' => 'Y',
                'IBLOCK_ID' => getIBlockID('au_model', 'tariff'),
            ],
            false,
            false,
            [
                'ID',
                'PROPERTY_KM_FROM',
                'PROPERTY_KM_TO',
                'PROPERTY_DAYS',
                'PROPERTY_TARIF_AU',
            ]
        );

        while ($arRow = $rs->Fetch()) {

            $sKey = $arRow['PROPERTY_KM_FROM_VALUE']    . '_' .
                    $arRow['PROPERTY_KM_TO_VALUE']      . '_' .
                    $arRow['PROPERTY_DAYS_VALUE']       . '_' .
                    $arRow['PROPERTY_TARIF_AU_VALUE'];

            $iSort = intval($arRow['PROPERTY_KM_FROM_VALUE']);

            $arResult[$sKey] = [
                'KM_FROM'   => $arRow['PROPERTY_KM_FROM_VALUE'],
                'KM_TO'     => $arRow['PROPERTY_KM_TO_VALUE'],
                'DAYS'      => $arRow['PROPERTY_DAYS_VALUE'],
                'TARIFF'    => $arRow['PROPERTY_TARIF_AU_VALUE'],
                'SORT'      => $iSort
            ];
        }

        usort($arResult, function($a, $b){
            return ($a['SORT'] - $b['SORT']);
        });

        return $arResult;
    }


    /**
     * Удаляет тариф с интервалом ['Расстояние от' => $sKeyFrom, 'Расстояние до' => $sKeyTo] для всех рег. центров
     * @param $sKeyFrom
     * @param $sKeyTo
     * @return int
     * @throws Exception
     */
    public function removeTariff($sKeyFrom, $sKeyTo) {

        // Список ID Региональных центров
        $arRegionalCentersId = $this->getListRegionalCentersId();

        $iItem = 0;
        foreach ($arRegionalCentersId as $iRegionalCentersId) {

            $arCurTariff = $this->getTariffByFilter([
                'PROPERTY_CENTER'   => $iRegionalCentersId,
                'PROPERTY_KM_FROM'  => $sKeyFrom,
                'PROPERTY_KM_TO'    => $sKeyTo,
            ]);

            if(!empty($arCurTariff['ID'])) {

                if(!$this->obElement->Delete($arCurTariff['ID'])) {
                    throw new Exception('Не удалось удалить тариф! "' . $this->obElement->LAST_ERROR . '"');
                }
            }

            $iItem++;
        }

        return $iItem;
    }


    /**
     * Обновляет тариф с интервалом ['Расстояние от' => $sKeyFrom, 'Расстояние до' => $sKeyTo] для всех рег. центров
     * @param $sKeyFrom
     * @param $sKeyTo
     * @param $sFrom
     * @param $sTo
     * @param $sDays
     * @param $sTariff
     * @return bool
     * @throws Exception
     */
    public function persistTariff($sKeyFrom, $sKeyTo, $sFrom, $sTo, $sDays, $sTariff) {

        // Список ID Региональных центров
        $arRegionalCentersId = $this->getListRegionalCentersId();

        $iItem = 0;
        foreach ($arRegionalCentersId as $iRegionalCentersId) {

            $arCurTariff = $this->getTariffByFilter([
                'PROPERTY_CENTER'   => $iRegionalCentersId,
                'PROPERTY_KM_FROM'  => $sKeyFrom,
                'PROPERTY_KM_TO'    => $sKeyTo,
            ]);

            // Новые значения тарифа
            $arLoad = [
                'PROPERTY_VALUES' => [
                    'KM_FROM'  => $sFrom,
                    'KM_TO'    => $sTo,
                    'CENTER'   => $iRegionalCentersId,
                    'DAYS'     => $sDays,
                    'TARIF_AU' => $sTariff,
                    'TARIF'    => $sTariff, // #11745 При обновлении тарифов "Новая тарифная ставка" заполняется тарифной ставкой АХ
                ]
            ];

            if(empty($arCurTariff['ID'])) {

                $arLoad['NAME']         = 'Тариф';
                $arLoad['IBLOCK_ID']    = getIBlockID('au_model', 'tariff');

                // Добавляем
                if(!$this->obElement->Add($arLoad)) {
                    throw new Exception('Не удалось добавить тариф! "' . $this->obElement->LAST_ERROR . '"');
                }
            } else {
                // Обновляем
                if(!$this->obElement->Update($arCurTariff['ID'], $arLoad)) {
                    throw new Exception('Не удалось обновить тариф! "' . $this->obElement->LAST_ERROR . '"');
                }
            }

            $iItem++;
        }

        return $iItem;
    }


    /**
     * Отдает ID тарифа по фильтру
     * @param $arFilter
     * @return mixed
     */
    public function getTariffByFilter($arFilter) {

        $arFilter = array_merge(
            $arFilter,
            ['ACTIVE' => 'Y', 'IBLOCK_ID' => getIBlockID('au_model', 'tariff')]
        );

        return $this->obElement->GetList(
            [],
            $arFilter,
            false,
            false,
            [   'ID',
                'PROPERTY_KM_FROM',
                'PROPERTY_KM_TO',
                'PROPERTY_DAYS',
                'PROPERTY_TARIF',
                'PROPERTY_TARIF_AU',
            ]
        )->Fetch();
    }


    /**
     * Отдает список ID Региональных центров
     * @return array|null
     */
    public function getListRegionalCentersId() {

        if(is_null($this->arRegionalCentersId)) {

            $this->arRegionalCentersId = [];

            $rs =  $rs = $this->obElement->GetList(
                [],
                [
                    'ACTIVE' => 'Y',
                    'IBLOCK_ID' => getIBlockID('directories', 'regions_centers'),
                ],
                false,
                false,
                ['ID',]
            );

            while ($arRow = $rs->Fetch()) {
                $this->arRegionalCentersId[] = $arRow['ID'];
            }
        }

        return $this->arRegionalCentersId;
    }


    /**
     * Отдает HTML элемента тарифа
     * @param $sKmFrom
     * @param $sKmTo
     * @param $sDays
     * @param $sTariff
     * @return string
     */
    public static function getHtmlRowTariff($sKmFrom, $sKmTo, $sDays, $sTariff) {

        $iSort = intval($sKmFrom);

        return <<<HTML
        <div class="tariff-item row" from="$sKmFrom" to="$sKmTo" data-sort="$iSort">
    
            <div class="left">
                <input
                        type="text"
                        autocomplete="off"
                        name="KM_FROM[$sKmFrom][$sKmTo]"
                        value="$sKmFrom"
                        placeholder="Расстояние от"
                        required="required"
                        title="Поле обязательно к заполнению"
                        class="km-from"
                />
            </div>
    
            <div class="left">
                <input
                        type="text"
                        autocomplete="off"
                        name="KM_TO[$sKmFrom][$sKmTo]"
                        value="$sKmTo"
                        placeholder="Расстояние до"
                        required="required"
                        title="Поле обязательно к заполнению"
                        class="km-to"
                />
            </div>
            <div class="left">
                <input
                        type="text"
                        autocomplete="off"
                        name="DAYS[$sKmFrom][$sKmTo]"
                        value="$sDays"
                        placeholder="Кол-во дней в рейсе"
                        required="required"
                        title="Поле обязательно к заполнению"
                        class="days"
                />
            </div>
    
            <div class="left">
                <input
                        type="text"
                        autocomplete="off"
                        name="TARIFF_AU[$sKmFrom][$sKmTo]"
                        value="$sTariff"
                        placeholder="Тарифная ставка"
                        required="required"
                        title="Поле обязательно к заполнению"
                        class="tariff"
                />
            </div>
    
            <div class="left">
                <input class="submit-btn save-tariff" value="Сохранить тариф" type="button">
            </div>
    
            <div class="left">
                <input type="button" class="btn-delete-tariff" value="-" title="Удалить тариф">
            </div>
    
            <div class="clear"></div>
        </div>
HTML;
    }
}
