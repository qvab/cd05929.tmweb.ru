<?
/**
 * Отдает ИД группы по "коду роли"
 * @param $sCodeRole - Код роли: 'p', 't', 'f', 'c', 'ag', 'agc', 'a', 'u',
 * @return bool|mixed
 * @throws Exception
 */
function getGroupIdByRole($sCodeRole) {

    static $arGroupByRole = [];

    if(empty($arGroupByRole)) {

        $obGroup = new CGroup;

        $rs = $obGroup->GetList(
            $by = "id",
            $order = "asc",
            ['ACTIVE' => 'Y']
        );

        while ($arRow = $rs->Fetch()) {

            if(empty($arRow['STRING_ID'])) {
                continue;
            }

            switch ($arRow['STRING_ID']) {
                case 'ADMIN':
                    $arGroupByRole['a'] = $arRow['ID'];     // Админ
                    break;
                case 'client':
                    $arGroupByRole['c'] = $arRow['ID'];     // Покупатель
                    break;
                case 'partner':
                    $arGroupByRole['p'] = $arRow['ID'];     // Партнер
                    break;
                case 'farmer':
                    $arGroupByRole['f'] = $arRow['ID'];     // Поставщик
                    break;
                case 'trade_companies':
                    $arGroupByRole['t'] = $arRow['ID'];     // Транспортные компании
                    break;
                case 'agents':
                    $arGroupByRole['ag'] = $arRow['ID'];    // Агенты поставщиков
                    break;
                case 'client_agents':
                    $arGroupByRole['agc'] = $arRow['ID'];   // Агенты покупателей
                    break;
                case 'REGIONAL_MANAGERS':
                    $arGroupByRole['rm'] = $arRow['ID'];    // Региональные менеджеры
                    break;
            }
        }

        unset($obGroup);
    }

    if(empty($arGroupByRole[$sCodeRole])) {
        throw new Exception('Не удалось получить группу с кодом роли "'.$sCodeRole.'"');
    }

    return $arGroupByRole[$sCodeRole];
}