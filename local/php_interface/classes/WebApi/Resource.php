<?
abstract class Resource {
    /**
     * Идентификатор highload-инфоблока
     *
     * @var int
     */

    protected static $hlIblock;

    /**
     * Добавление сущности в highload-инфоблок
     *
     * @access  public
     * @param   [] $data массив полей
     * @return  [] идентификатор сущности
     */
    public static function _createEntity($data) {
        $entityDataClass = Agrohelper::getEntityDataClass(static::$hlIblock);
        $el = new $entityDataClass;

        $res = $el->add($data);
        if ($res->isSuccess())
            $result['ID'] = $res->getId();
        else
            $result['ERROR'] = Agrohelper::getErrorMessage('createEntityError');
        return $result;
    }

    /**
     * Получение всех полей сущности в highload-инфоблоке
     *
     * @access  public
     * @param   [] $filter массив с полями для поиска
     * @return  [] массив с полями сущности
     */
    public static function _getEntity($filter) {
        $entityDataClass = Agrohelper::getEntityDataClass(static::$hlIblock);
        $el = new $entityDataClass;

        $rsData = $el->getList(array(
            'select' => array('*'),
            'filter' => $filter
        ));
        if ($result = $rsData->fetch()) {
            return $result;
        }
        return false;
    }

    /**
     * Изменение сущности в highload-инфоблоке
     *
     * @access  public
     * @param   int $entityId ID сущности
     *          [] $data массив полей
     * @return  [] идентификатор сущности
     */
    public static function _updateEntity($entityId, $data) {
        $entityDataClass = Agrohelper::getEntityDataClass(static::$hlIblock);
        $el = new $entityDataClass;

        $res = $el->update($entityId, $data);
        $result['ID'] = $res->getId();
        return $result;
    }

    /**
     * Получение списка сущностей в highload-инфоблоке
     *
     * @access  public
     * @param   [] $filter массив с полями для поиска
     * @return  [] массив со списком сущностей
     */
    public static function _getEntitiesList($filter, $key = false) {
        $entityDataClass = Agrohelper::getEntityDataClass(static::$hlIblock);
        $el = new $entityDataClass;

        $rsData = $el->getList(array(
            'select' => array('*'),
            'filter' => $filter
        ));
        if ($key) {
            while ($res = $rsData->fetch()) {
                $result[$res[$key]] = $res;
            }
        }
        else {
            while ($res = $rsData->fetch()) {
                $result[] = $res;
            }
        }
        return $result;
    }

    /**
     * Удаление сущности в highload-инфоблоке
     *
     * @access  public
     * @param   int $entityId ID сущности
     * @return  bool
     */
    public static function _deleteEntity($entityId) {
        $entityDataClass = Agrohelper::getEntityDataClass(static::$hlIblock);
        $el = new $entityDataClass;

        $res = $el->delete($entityId);
        if ($res->isSuccess())
            return true;
        else
            return false;
    }

}
?>