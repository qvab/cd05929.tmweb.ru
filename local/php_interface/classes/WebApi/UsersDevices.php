<?
/*
 * Класс для работы с экземплярами ресурса UserDevices
 * Атрибуты экземпляра ресурса UserDevices:
 *
 * id:			    <идентификатор записи, число>,
 * user:		    <идентификатор пользователя, GUID>,
 * device:		    <идентификатор устройства, число>,
 * last_request:	<дата последнего запроса с устройства, TIMESTAMP>
 *
 */

require_once('Resource.php');

class UsersDevices extends Resource {
    protected static $hlIblock = 6;


}
?>