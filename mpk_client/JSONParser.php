<?php

include_once 'ParserAbstract.php';
include_once 'FilesLoader.php';

class JSONParser extends ParserAbstract {
    
    public function ParseFromString($jsonString) {
        $array = json_decode($jsonString, true);
        if ($array === null) {
            trigger_error("Ошибка, неверно сформирован полученный файл\nJSON имеет синтаксические ошибки", E_USER_ERROR);
        }
        
        $result = parent::GetTeamsFromArray($array);
        return $result;
    }
    
}