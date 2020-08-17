<?php
/**
 * Created by PhpStorm.
 * User: dmitrd
 * Date: 28.09.18
 * Time: 12:39
 */

require_once('Resource.php');

class HighloadRequestsHash extends Resource {

    function __construct($ib_id)
    {
        if(!is_numeric($ib_id)){
            echo 'Ошибка: Не указан id инфоблока';
            exit;
        }
        else{
            self::$hlIblock = $ib_id;
        }
    }

    /**
     * add new note to highload block (return error if note with new hash exists)
     * @param string $hash_val 32 symbols new note hash data
     * @return bool|int id new note value or false if error
     */
    function AddNewNote($hash_val, $additional_data = '')
    {
        //проверка нет ли дубля хэша
        $arFilter = array('UF_HASH' => $hash_val);
        if(is_array(self::_getEntity($arFilter))){
            return false;
        }

        //добавление данных в таблицу
        $arFields = array(
            'UF_HASH'       => $hash_val,
            'UF_TIMESTAMP'  => time(),
            'UF_ADDIT'      => $additional_data
        );

        return self::_createEntity($arFields);
    }

    /**
     * return note data array by hash
     * @param string $hash_val 32 symbols exist note hash
     * @return bool|int|mixed note data, false if not found, -1 if not counted yet
     */
    function GetNoteData($hash_val, $addit_data_filter = '')
    {
        $answer = -1;

        $arFilter = array('UF_HASH' => $hash_val);

        //получение данных из таблицы (если есть дополнительная проверка на тип запроса, проверяем и это)
        $resource_data = self::_getEntity($arFilter);
        if(!is_array($resource_data)
            || (isset($resource_data['UF_ADDIT'])
                && $addit_data_filter != ''
                && $resource_data['UF_ADDIT'] != $addit_data_filter
            )
        ){
            return false;
        }

        if($resource_data['UF_DATA'] != '')
            $answer = $resource_data['UF_DATA'];

        return $answer;
    }

    /**
     * update note fields by hash
     * @param string $hash_val 32 symbols exist note hash
     * @param string $data new data (json for example)
     * @param int $new_flag status flag
     * @return bool
     */
    function UpdateNote($hash_val, $data, $addit_data = '')
    {
        $arFilter = array('UF_HASH' => $hash_val);

        //получение данных из таблицы
        $resource_data = self::_getEntity($arFilter);
        if(!is_array($resource_data) || mb_strlen(trim($data)) == 0){
            return false;
        }

        //обновление данных
        $arFields = array(
            'UF_DATA' => $data
        );
        if($addit_data != ''){
            $arFields['UF_ADDIT'] = $addit_data;
        }

        return self::_updateEntity($resource_data['ID'], $arFields);
    }

    /**
     * delete one note by hash
     * @param string $hash_val 32 symbols exist note hash
     * @return bool flag if note was deleted
     */
    function DeleteNote($hash_val)
    {
        $arFilter = array('UF_HASH' => $hash_val);

        //получение данных из таблицы
        $resource_data = self::_getEntity($arFilter);
        if(!is_array($resource_data)){
            return false;
        }

        return self::_deleteEntity($resource_data['ID']);
    }

    /**
     * generate 32 symbols random string by md5 algorithm
     * @return string new hash 32 symbols string
     */
    function HashGen()
    {
        return md5('agrohash_' . time() . rand(10000000));
    }

    /**
     * delete old notes at hash table (older than 24 hours)
     * @return bool flag if all notes were deleted
     */
    function deleteOldNotes() {
        $result = true;
        $idArr = array();

        //get old notes id array
        $arr_list = self::_getEntitiesList(array('>UF_TIMESTAMP' => time() + 3600 * 24));
        foreach($arr_list as $cur_note){
            $idArr[$cur_note['ID']] = true;
        }

        //delete old notes
        if(count($idArr > 0)){
            foreach($idArr as $cur_id => $cur_flag){
                if(!self::_deleteEntity($cur_id)){
                    //if any false was once given -> return false
                    $result = false;
                }
            }
        }

        return $result;
    }
}