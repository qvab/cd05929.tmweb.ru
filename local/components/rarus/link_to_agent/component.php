<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

/**
 * Компонент выводит для АП информацию об агенте
 * @author: Vitaly Melnik <vitali@rarus.ru>
 */

/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var string $componentPath */
/** @var string $componentName */
/** @var string $componentTemplate */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

/**
 * Параметры
 */
$arParams['CACHE_TIME'] = intval($arParams['CACHE_TIME']);
if(empty($arParams['CACHE_TYPE'])) {
    $arParams['CACHE_TYPE'] = 'A';
}
if(empty($arParams['FARMER_ID'])) {
    $arParams['FARMER_ID'] = CUser::GetID();
}


if ($this->StartResultCache()) {

    /**
     * Результирующий массив
     */
    $arResult = [
        'USER_ID'       => null,
        'AGENT_ID'      => null,
        'AGENT_PROFILE' => [],
        'ERROR_MSG'     => null,
    ];

    try {

        // Объекты
        $obAgent    = new agent;
        $obUser     = new CUser;
        $obGroup    = new CGroup;


        // ИД пользователя
        $arResult['USER_ID'] = intval($arParams['FARMER_ID']);
        if(empty($arResult['USER_ID'])) {
            throw new Exception('Не удалось получить ИД пользователя');
        }


        // Группа Поставщик
        $arGroupFarmer = $obGroup->GetList(
            $by = "c_sort",
            $order = "asc",
            ['STRING_ID' => 'farmer']
        )->Fetch();

        if(empty($arGroupFarmer)) {
            throw new Exception('Не удалось получить группу "Поставщик"');
        }


        // Группы пользователя
        $arGroupUser = $obUser->GetUserGroup($arResult['USER_ID']);

        // Проверяем является ли пользователь поставщиком
        if(!in_array($arGroupFarmer['ID'], $arGroupUser)) {
            throw new Exception('У вас нет привязки к группе "Поставщик"');
        }
        unset($arGroupFarmer, $arGroupUser);


        // Получаем Агента АП
        $arFarmerAgents = $obAgent->getAgentsByFarmers($arResult['USER_ID']);
        if(empty($arFarmerAgents[$arResult['USER_ID']]['ID'])) {
            throw new Exception('У вас нет привязки к Агенту');
        }
        $arResult['AGENT_ID'] = $arFarmerAgents[$arResult['USER_ID']]['ID'];
        unset($arFarmerAgents);


        // Получаем профиль Агента
        $arResult['AGENT_PROFILE'] = $obAgent->getProfile($arResult['AGENT_ID']);
        if(empty($arResult['AGENT_PROFILE'])) {
            throw new Exception('Не удалось получить профиль Агента');
        }

    } catch (Exception $e) {
        $this->AbortResultCache();
        $arResult['ERROR_MSG'] = $e->getMessage();
    }

    $this->IncludeComponentTemplate();
}