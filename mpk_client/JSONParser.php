<?php

include_once 'ParserAbstract.php';
include_once 'FilesLoader.php';

class JSONParser extends ParserAbstract {
    
    public function ParseFromString($jsonString) {
        $result = json_decode($jsonString, true);
        if ($result === null) {
            trigger_error("Ошибка, неверно сформирован полученный файл\nJSON имеет синтаксические ошибки", E_USER_ERROR);
        }
        print_r($result);
        if (!parent::VerifyArray($result)) {
            trigger_error($this->lastError, E_USER_ERROR);
        }
        
        return $result;
    }
    
}