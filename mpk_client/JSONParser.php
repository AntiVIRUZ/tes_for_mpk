<?php

/**
 * Класс для парсинга JSON файла
 * 
 * Разбивает переданную строку JSON в массив команд (Team)
 * @author Vasiliy Yatsevitch <zwtdbx@yandex.ru>
 */

include_once 'ParserAbstract.php';
include_once 'FilesLoader.php';

class JSONParser extends ParserAbstract {
    
    /**
     * Парсит JSON строку в массив команд
     * @access public
     * @param string $jsonString
     * @return array Массив экземпляров класса Team
     */
    public function ParseFromString($jsonString) {
        $array = json_decode($jsonString, true);
        if ($array === null) {
            $this->lastError = "Ошибка, неверно сформирован полученный файл. JSON имеет синтаксические ошибки (JSONParser::ParseFromString)";
            return FALSE;
        }
        
        $result = parent::GetTeamsFromArray($array);
        return $result;
    }
    
}