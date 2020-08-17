<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();?>
<?
if ($_POST['save']) {
    $post = array();
    foreach($arResult['PRICE_LIST'] as $arCulture) {
        foreach ($arCulture['TYPE'] as $key => $arType) {
            foreach ($arType['MODEL'] as $model) {
                if ($model['PRICE'] != $_POST['price'][$arCulture['ID']][$arType['ID']][$model['ID']]) {
                    $post[$arCulture['ID']][$arType['ID']][$model['ID']] = $_POST['price'][$arCulture['ID']][$arType['ID']][$model['ID']];
                }
            }
        }
    }

    if (sizeof($post) > 0) {
        $ib = rrsIblock::getIBlockId('market_value');
        $arFilter = array('IBLOCK_ID' => $ib, 'ACTIVE' => 'Y');
        $tmp = array("LOGIC" => "OR");
        foreach ($post as $k1 => $val1) {
            foreach ($val1 as $k2 => $val2) {
                foreach ($val2 as $k3 => $val3) {
                    $tmp[] = array(
                        'PROPERTY_CULTURE' => $k1,
                        'PROPERTY_TYPE' => $k2,
                        'PROPERTY_MODEL' => $k3,
                    );
                }
            }
        }
        $arFilter[] = $tmp;
        $res = CIblockElement::GetList(
            array('ID' => 'ASC'),
            $arFilter,
            false,
            false,
            array('ID', 'PROPERTY_CULTURE', 'PROPERTY_TYPE', 'PROPERTY_MODEL')
        );

        while ($ob = $res->GetNext()) {
            $val = $post[$ob['PROPERTY_CULTURE_VALUE']][$ob['PROPERTY_TYPE_VALUE']][$ob['PROPERTY_MODEL_VALUE']];
            if (isset($val)) {
                CIBlockElement::SetPropertyValuesEx($ob['ID'], $ib, array('MARKET_COST' => $val));
            }
        }

        $ib = rrsIblock::getIBlockId('model');
        foreach ($post as $k1 => $val1) {
            foreach ($val1 as $k2 => $val2) {
                foreach ($val2 as $k3 => $val3) {
                    CIBlockElement::SetPropertyValuesEx($k3, $ib, array('MARKET_COST' => $val3));
                }
            }
        }

        global $CACHE_MANAGER;
        $CACHE_MANAGER->ClearByTag("iblock_id_25");
    }

    LocalRedirect($APPLICATION->GetCurPage());
}

//p($post);
//p($_POST);
//p($arResult['PRICE_LIST']);
?>