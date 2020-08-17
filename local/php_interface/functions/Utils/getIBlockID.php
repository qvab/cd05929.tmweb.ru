<?php
/**
 *  @author: Vitaly Melnik <vitali@rarus.ru>
 */

/**
 * Возвращает ID ИБ по его Типу и Коду
 *
 * @param string $sType Тип ИБ
 * @param string $sCode Код ИБ
 *
 * @return int Вернет ID ИБ
 * @throws \Exception
 */
function getIBlockID($sType, $sCode) {

    // Проверяем параметры
    if(empty($sType) || empty($sCode))
        return false;

    static $arIBlockId = array();

    if(empty($arIBlockId)) {

        $obModule = new \CModule;

        // Информационный блоки
        if(!$obModule->IncludeModule('iblock'))
            throw new \Exception('Модуль не найден');

        $obCIBlock = new \CIBlock;
        $resIBlock = $obCIBlock->GetList(array(), array());

        while($arResult = $resIBlock->Fetch())
            $arIBlockId[$arResult['IBLOCK_TYPE_ID']][$arResult['CODE']] = $arResult['ID'];

        unset($obModule, $obCIBlock);
    }

    if(empty($arIBlockId[$sType][$sCode]))
        throw new \Exception('С заданными параметрами инфоблок не найден');

    return $arIBlockId[$sType][$sCode];
}
